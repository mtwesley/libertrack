<?php

class Controller_Exporting extends Controller {

  public function before() {
    parent::before();

    if (!Auth::instance()->logged_in()) {
      Notify::msg('Please login.', NULL, TRUE);
      $this->request->redirect('login?destination='.$this->request->uri());
    }
    elseif (!Auth::instance()->logged_in('invoices')) {
      Notify::msg('Access denied. You must have '.SGS::$roles['invoices'].' privileges.', 'locked', TRUE);
      $this->request->redirect();
    }

    Session::instance()->write();
  }

  private function handle_specs_create() {
    if (!Request::$current->query()) Session::instance()->delete('pagination.specs.data');

    $operator_ids = DB::select('id', 'name')
      ->from('operators')
      ->order_by('name')
      ->execute()
      ->as_array('id', 'name');

    $form = Formo::form()
      ->add_group('operator_id', 'select', $operator_ids, NULL, array('label' => 'Operator', 'attr' => array('class' => 'specs_operatoropts')))
      ->add_group('specs_info', 'select', array(), NULL, array('required' => TRUE, 'label' => 'Shipment Specification', 'attr' => array('class' => 'specsopts')))
      ->add('format', 'radios', 'preview', array(
        'options' => array(
          'preview' => 'Preview',
          'draft'   => 'Draft Copy',
          'final'   => 'Final Copy'),
        'label' => '&nbsp;',
        'required' => TRUE,
        ))
      ->add('submit', 'submit', 'Generate');

    if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
      Session::instance()->delete('pagination.data');
      $format      = $form->format->val();
      $operator_id = $form->operator_id->val();
      $specs_info  = $form->specs_info->val();

      Session::instance()->set('pagination.specs.data', array(
        'operator_id' => $operator_id,
        'specs_info'  => $specs_info,
        'format'      => $format
      ));
    }
    else if ($settings = Session::instance()->get('pagination.specs.data')) {
      $form->operator_id->val($operator_id = $settings['operator_id']);
      $form->specs_info->val($specs_info = $settings['specs_info']);

      $format = $settings['format'];
    }

    if ($specs_info) {
      $data = ORM::factory('SPECS')
        ->where('specs_id', '=', NULL)
        ->where('status', '=', 'A')
        ->join('barcodes')
        ->on('barcode_id', '=', 'barcodes.id')
        ->order_by('barcode');

      if (Valid::is_barcode($specs_info))   $data->where('specs_barcode_id', '=', SGS::lookup_barcode($specs_info, TRUE));
      else if (Valid::numeric($specs_info)) $data->where('specs_id', '=', SGS::lookup_specs($specs_info, TRUE));

      if ($data) {
        switch ($format) {
          case 'preview':
            $clone = clone($data);
            $pagination = Pagination::factory(array(
              'items_per_page' => 50,
              'total_items' => $clone->find_all()->count()));

            $data = $data
              ->offset($pagination->offset)
              ->limit($pagination->items_per_page)
              ->find_all()
              ->as_array();

            if ($pagination->total_items == 1) Notify::msg($pagination->total_items.' record found');
            elseif ($pagination->total_items) Notify::msg($pagination->total_items.' records found');
            else Notify::msg('No records found');

            $operator = ORM::factory('operator', $operator_id);

            unset($info);
            if ($specs_info) {
              $sample = reset($data);
              $info['specs'] = array(
                'number'  => $sample->specs_number,
                'barcode' => $sample->specs_barcode->barcode
              );
              if (Valid::numeric($specs_info)) $info['exp'] = array(
                'number'  => $sample->exp_number,
                'barcode' => $sample->exp_barcode->barcode
              );
            }

            $table = View::factory('data')
              ->set('classes', array('has-pagination'))
              ->set('form_type', 'SPECS')
              ->set('data', $data)
              ->set('operator', $operator)
              ->set('specs_info', $info ? array_filter((array) $info['specs']) : NULL)
              ->set('exp_info', $info ? array_filter((array) $info['exp']) : NULL)
              ->set('options', array(
                'links'  => FALSE,
                'header' => TRUE
              ))
              ->render();
            break;

          case 'draft':
            $is_draft = TRUE;

          case 'final':
            set_time_limit(600);
            $data = $data->find_all()->as_array();

            $model = ORM::factory('SPECS');

            $is_draft = $is_draft ? TRUE : FALSE;
            $number   = $is_draft ? NULL : $model::create_specs_number();
            $user_id  = Auth::instance()->get_user()->id ?: 1;
            $file_id  = self::generate_specs($data, array(
              'is_draft'     => $is_draft,
              'specs_number' => $number
            ));

            if (!$file_id) Notify::msg('Sorry, file failed to be generated. Please try again.', 'error');
            else {
              Notify::msg('File successfully generated.', NULL, TRUE);
              try {
                $result = DB::insert('specs', array('number', 'is_draft', 'file_id', 'user_id'))
                  ->values(array($number, (bool) $is_draft, $file_id, $user_id))
                  ->execute();

                $specs_id = $result[0];

                foreach ($data as $item) {
                  $item->specs_id = $specs_id;
                  $item->save();
                }

                Notify::msg(($invoice->is_draft ? 'Draft document' : 'Document') . ' created.', 'success', TRUE);
                $this->request->redirect('exporting/specs/'.$specs_id);
              } catch (Exception $e) {
                Notify::msg('Sorry, unable to create document. Please try again.', 'error');
              }
              break;
            }
        }
      } else Notify::msg('No data found. Skipping document.', 'warning');
    }

    if ($form) $content .= $form;
    $content .= $table;
    $content .= $pagination;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  private function handle_specs_finalize($id) {
    $invoice = ORM::factory('invoice', $id);
    if (!($invoice->loaded() and $invoice->is_draft)) {
      Notify::msg('Invoice already finalized.', 'warning', TRUE);
      $this->request->redirect('invoices/'.$id);
    }

    $invoice->is_draft = FALSE;
    $invoice->number = $invoice::create_number();

    switch ($invoice->type) {
      case 'ST': $invoice->file_id = self::generate_st_invoice($invoice, array_keys($invoice->get_data()));
    }

    if ($invoice->file_id) Notify::msg('Invoice file successfully generated.', NULL, TRUE);
    else Notify::msg('Sorry, invoice file failed to be generated. Please try again.', 'error', TRUE);

    try {
      $invoice->save();

      Notify::msg('Invoice finalized.', 'success', TRUE);
      $this->request->redirect('invoices/'.$invoice->id);
    } catch (Exception $e) {
      Notify::msg('Sorry, unable to create invoice. Please try again.', 'error');
      $this->request->redirect('invoices/'.$invoice->id);
    }

  }

  private function handle_specs_list($id = NULL) {
    if (!Request::$current->query()) Session::instance()->delete('pagination.specs.list');
    if ($id) {
      Session::instance()->delete('pagination.specs.list');

      $specs = DB::select()
        ->from('specs')
        ->where('id', '=', $id);

      if (!$specs) $this->request->redirect('exporting/specs');
    }
    else {
      $operator_ids = DB::select('id', 'name')
        ->from('operators')
        ->order_by('name')
        ->execute()
        ->as_array('id', 'name');

      $form = Formo::form()
        ->add_group('operator_id', 'select', $operator_ids, NULL, array('label' => 'Operator', 'attr' => array('class' => 'specs_operatoropts')))
        ->add_group('specs_info', 'select', array(), NULL, array('required' => TRUE, 'label' => 'Shipment Specification', 'attr' => array('class' => 'specsopts')))
        ->add('submit', 'submit', 'Filter');

      if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
        Session::instance()->delete('pagination.data');
        $operator_id = $form->operator_id->val();
        $specs_info  = $form->specs_info->val();

        Session::instance()->set('pagination.specs.data', array(
          'operator_id' => $site_id,
          'specs_info'  => $type,
        ));
      }
      else if ($settings = Session::instance()->get('pagination.specs.data')) {
        $form->operator_id->val($operator_id = $settings['operator_id']);
        $form->specs_info->val($specs_info = $settings['specs_info']);
      }

      if ($specs_info) {
        $ids = DB::select('id')
          ->from('specs_data');
        if (Valid::is_barcode($specs_info))   $ids->where('specs_barcode_id', '=', SGS::lookup_barcode($specs_info, TRUE));
        else if (Valid::numeric($specs_info)) $ids->where('specs_id', '=', SGS::lookup_specs($specs_info, TRUE));
        $ids = $ids->compile(Database::instance());

        $specs = DB::select()
          ->from('specs')
          ->where('id', 'IN', DB::expr('('.$ids.')'));
      } else {
        $specs = DB::select()->from('specs');
      }
    }

    if ($specs) {
      $clone = clone($specs);
      $pagination = Pagination::factory(array(
        'items_per_page' => 20,
        'total_items' => $clone->execute()->count()));

      $specs = $specs
        ->offset($pagination->offset)
        ->limit($pagination->items_per_page);
      if ($sort = $this->request->query('sort')) $specs->order_by($sort);
      $specs = $specs
        ->execute()
        ->as_array();

      $table = View::factory('finalspecs')
        ->set('classes', array('has-pagination'))
        ->set('specs', $specs)
        ->render();
      if ($pagination->total_items == 1) Notify::msg($pagination->total_items.' final shipment specifications found');
      elseif ($pagination->total_items) Notify::msg($pagination->total_items.' final shipment specifications found');
    }
    else Notify::msg('No final shipment specifications found');

    if ($form) $content .= $form->render();
    $content .= $table;
    $content .= $pagination;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  private function handle_specs_delete($id) {
    $invoice  = ORM::factory('invoice', $id);

    if (!$invoice->loaded()) {
      Notify::msg('No invoice found.', 'warning', TRUE);
      $this->request->redirect('invoices');
    }

    if (!$invoice->is_draft) {
      Notify::msg('Sorry, cannot delete final invoices.', 'warning', TRUE);
      $this->request->redirect('invoices/'.$invoice->id);
    }

    $form = Formo::form()
      ->add('confirm', 'text', 'Are you sure you want to delete this draft invoice?')
      ->add('delete', 'submit', 'Delete');

    if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
      try {
        $invoice->delete();
        if ($invoice->loaded()) throw new Exception();
        Notify::msg('Draft invoice successfully deleted.', 'success', TRUE);
      } catch (Exception $e) {
        Notify::msg('Draft invoice failed to be deleted.', 'error', TRUE);
      }

      $this->request->redirect('invoices');
    }

    $content .= $form->render();

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  private function generate_specs($records, $info = array()) {
    extract($info);

    if (!$records) {
      Notify::msg('No data found. Unable to generate document.', 'warning');
      return FALSE;
    }

    $item = reset($records);

    $page_count = 0;
    $page_max   = 30;

    $total = 0;
    foreach ($records as $record) $total += $record->volume;

    $cntr   = 0;
    $styles = TRUE;
    while ($cntr < count($records)) {
      $max = $page_max;
      $set = array_slice($records, $cntr, $max);
      $html .= View::factory('documents/specs')
        ->set('data', $set)
        ->set('options', array(
          'info'    => TRUE,
          'details' => TRUE,
          'styles'  => $styles ? TRUE : FALSE,
          'total'   => count($records) > ($cntr + $max) ? FALSE : TRUE
        ))
        ->set('info', array(
          'is_draft'      => $is_draft,
          'specs_barcode' => $item->specs_barcode->barcode,
          'specs_number'  => $specs_number,
          'exp_barcode'   => $item->exp_barcode->barcode,
          'exp_number'    => $item->exp_number,
          'operator_tin'  => $item->operator->tin,
          'operator_name' => $item->operator->name,
          'origin'        => $item->origin,
          'destination'   => $item->destination,
          // 'loading_date'  => $item->loading_date,
          // 'buyer'         => $item->buyer,
          // 'submitted_by'  => $item->submitted_by,
          'create_date'   => $item->create_date,
          'total'         => $total
        ))
        ->set('cntr', $cntr)
        ->set('total', $total)
        ->render();

      $cntr += $max;
      $styles = FALSE;
    }

    // generate pdf
    set_time_limit(600);

    // save file
    $ext = 'pdf';
    $newdir = implode(DIRECTORY_SEPARATOR, array(
      'specs',
    ));

    if ($is_draft) $newname = 'SPECS_DRAFT_'.SGS::date('now', 'Y_m_d').'.'.$ext;
    else $newname = 'SPECS_'.$specs_number.'.'.$ext;

    $version = 0;
    $testname = $newname;
    while (file_exists(DOCPATH.$newdir.DIRECTORY_SEPARATOR.$newname)) {
      $newname = substr($testname, 0, strrpos($testname, '.'.$ext)).'_'.($version++).'.'.$ext;
    }

    if (!is_dir(DOCPATH.$newdir) and !mkdir(DOCPATH.$newdir, 0777, TRUE)) {
      Notify::msg('Sorry, cannot access documents folder. Check file access capabilities with the site administrator and try again.', 'error');
      return FALSE;
    }

    $fullname = DOCPATH.$newdir.DIRECTORY_SEPARATOR.$newname;

    try {
      $snappy = new \Knp\Snappy\Pdf();
      $snappy->generateFromHtml($html, $fullname, array(
        'load-error-handling' => 'ignore',
        'margin-bottom' => 22,
        'margin-left' => 0,
        'margin-right' => 0,
        'margin-top' => 0,
        'lowquality' => TRUE,
        'disable-smart-shrinking' => TRUE,
        'footer-html' => View::factory('documents/specs')
          ->set('options', array(
            'header' => FALSE,
            'footer' => TRUE,
            'break'  => FALSE))
          ->set('page', $page)
          ->set('page_count', $page_count)
          ->render()
      ));
    } catch (Exception $e) {
      Notify::msg('Sorry, unable to generate invoice document. If this problem continues, contact the system administrator.', 'error');
      return FALSE;
    }

    try {
      $file = ORM::factory('file');
      $file->name = $newname;
      $file->type = 'application/pdf';
      $file->size = filesize($fullname);
      $file->operation      = 'A';
      $file->operation_type = 'SPECS';
      $file->content_md5    = md5_file($fullname);
      $file->path = DIRECTORY_SEPARATOR.str_replace(DOCROOT, '', DOCPATH).$newdir.DIRECTORY_SEPARATOR.$newname;
      $file->save();
      return $file->id;
    } catch (ORM_Validation_Exception $e) {
      foreach ($e->errors('') as $err) Notify::msg(SGS::errorify($err).' ('.$file->name.')', 'error');
      return FALSE;
    }
  }

  public function action_index() {
    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  public function action_specs() {
    $id      = $this->request->param('id');
    $command = $this->request->param('command');

    if (!is_numeric($id)) $command = $id;

    switch ($command) {
      case 'create': return self::handle_specs_create($id);
      case 'finalize': return self::handle_specs_finalize($id);
      case 'delete': return self::handle_specs_delete($id);
      case 'list':
      default: return self::handle_specs_list($id);
    }

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

}