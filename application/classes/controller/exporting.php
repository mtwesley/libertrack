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

  private function handle_specs_finalize($id) {
    $invoice = ORM::factory('invoice', $id);
    if (!($invoice->loaded() and $invoice->is_draft)) {
      Notify::msg('Invoice already finalized.', 'warning', TRUE);
      $this->request->redirect('invoices/'.$id);
    }

    $invoice->is_draft = FALSE;
    $invoice->reference_number = $invoice::create_reference_number();

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

  private function handle_invoice_list($id = NULL) {
    if (!Request::$current->query()) Session::instance()->delete('pagination.invoice.list');
    if ($id) {
      Session::instance()->delete('pagination.invoice.list');

      $invoices = array(ORM::factory('invoice', $id));
      if (!$invoices) $this->request->redirect ('invoices');
    }
    else {

      $site_ids = DB::select('id', 'name')
        ->from('sites')
        ->order_by('name')
        ->execute()
        ->as_array('id', 'name');

      $form = Formo::form()
        ->add_group('type', 'checkboxes', SGS::$invoice_type, NULL, array('label' => 'Type'))
        ->add_group('site_id', 'select', $site_ids, NULL, array('label' => 'Site'))
        ->add('search', 'submit', 'Filter');

      if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
        Session::instance()->delete('pagination.invoice.list');

        $type    = $form->type->val();
        $site_id = $form->site_id->val();

        $invoices = ORM::factory('invoice');

        if ($type)    $invoices->and_where('type', 'IN', (array) $type);
        if ($site_id) $invoices->and_where('site_id', 'IN', (array) $site_id);

        Session::instance()->set('pagination.invoice.list', array(
          'form_type'   => $type,
          'site_id'     => $site_id,
        ));
      }
      else {
        if ($settings = Session::instance()->get('pagination.invoice.list')) {
          $form->type->val($type = $settings['form_type']);
          $form->site_id->val($site_id = $settings['site_id']);
        }

        $invoices = ORM::factory('invoice');

        if ($type)    $invoices->and_where('type', 'IN', (array) $type);
        if ($site_id) $invoices->and_where('site_id', 'IN', (array) $site_id);
      }

      if ($invoices) {
        $clone = clone($invoices);
        $pagination = Pagination::factory(array(
          'items_per_page' => 20,
          'total_items' => $clone->find_all()->count()));

        $invoices = $invoices
          ->offset($pagination->offset)
          ->limit($pagination->items_per_page);
        if ($sort = $this->request->query('sort')) $invoices->order_by($sort);
        $invoices = $invoices->order_by('created_date', 'DESC')
          ->find_all()
          ->as_array();
      }
    }

    if ($invoices) {
      $table = View::factory('invoices')
        ->set('classes', array('has-pagination'))
        ->set('invoices', $invoices)
        ->render();
      if ($pagination->total_items == 1) Notify::msg($pagination->total_items.' invoice found');
      elseif ($pagination->total_items) Notify::msg($pagination->total_items.' invoices found');
    }
    else Notify::msg('No invoices found');

    if ($form) $content .= $form->render();
    $content .= $table;
    $content .= $pagination;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  private function handle_invoice_delete($id) {
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

  public function action_index() {
    $id      = $this->request->param('id');
    $command = $this->request->param('command');

    switch ($command) {
      case 'download': return self::handle_invoice_download($id);
      case 'finalize': return self::handle_invoice_finalize($id);
      case 'delete': return self::handle_invoice_delete($id);
      case 'list': return self::handle_invoice_list($id);
    }

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  public function action_list() {
    $id = $this->request->param('id');

    return self::handle_invoice_list($id);
  }

  private function generate_st_preview($data_ids) {
    $data = DB::select(array('code', 'species_code'), array('class', 'species_class'), 'fob_price', array(DB::expr('sum(volume)'), 'volume'))
      ->from('ldf_data')
      ->join('species')
      ->on('species_id', '=', 'species.id')
      ->where('ldf_data.id', 'IN', (array) $data_ids)
      ->group_by('species_code', 'species_class', 'fob_price')
      ->execute()
      ->as_array();

    foreach ($data as $record) {
      foreach ($record as $key => $value) $total[$key] += $value;
      $total['total'] += $record['volume'] * $record['fob_price'] * SGS::$species_fee_rate[$record['species_class']];
    }

    return View::factory('invoices/st_summary')
      ->set('data', $data)
      ->set('total', array('summary' => $total))
      ->render();
  }

  private function generate_specs($records) {
    if (!$records) {
      Notify::msg('No data found. Unable to generate document.', 'warning');
      return FALSE;
    }

    $item = reset($records);

    $details_page_count = 0;
    $details_page_max   = 25;

    $page_count = $details_page_count;
    $max        = $details_page_max;

    $page = 1;
    $cntr = 0;
    while ($cntr < count($records)) {
      $set = array_slice($records, $cntr, $max);
      $html .= View::factory('documents/specs')
        ->set('data', $set)
        ->set('options', array(
          'info'    => TRUE,
          'details' => TRUE,
          'styles'  => $page == 1 ? TRUE : FALSE,
          'total'   => count($records) > ($cntr + $max) ? FALSE : TRUE
        ))
        ->set('info', array(
          'specs_barcode' => $item->specs_barcode->barcode,
          // 'specs_number'  => $item->specs_number,
          'epr_barcode'   => $item->epr_barcode->barcode,
          // 'epr_number'    => $item->epr_number,
          'operator_tin'  => $item->operator->tin,
          'operator_name' => $item->operator->name,
          'origin'        => $item->origin,
          'destination'   => $item->destination,
          // 'loading_date'  => $item->loading_date,
          // 'buyer'         => $item->buyer,
          // 'submitted_by'  => $item->submitted_by,
          'create_date'   => $item->create_date
        ))
        ->set('cntr', $cntr)
        ->set('page', $page)
        ->set('page_count', $page_count)
        ->set('total', array('details' => $details_total))
        ->render();
      $cntr += $max;
      $page++;
    }

//    die($html);

    // generate pdf
    set_time_limit(600);

    // save file
    $ext = 'pdf';
    $newdir = implode(DIRECTORY_SEPARATOR, array(
      'specs',
    ));

//    if ($invoice->is_draft) $newname = 'DRAFT_'.SGS::date($invoice->created_date, 'Y_m_d').'.'.$ext;
//    else $newname = 'ST_'.$invoice->reference_number.'.'.$ext;

    $newname = 'specs_download'.'.'.$ext;

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
//    if (!(file_put_contents($fullname, $output) and chmod($fullname, 0777))) {
//      Notify::msg('Sorry, cannot create invoice. Check file operation capabilities with the site administrator and try again.', 'error');
//      return FALSE;
//    }

    try {
      $snappy = new \Knp\Snappy\Pdf();
      $snappy->generateFromHtml($html, $fullname, array(
        'load-error-handling' => 'ignore',
        'margin-bottom' => 25,
        'margin-left' => 0,
        'margin-right' => 0,
        'margin-top' => 0,
        'lowquality' => TRUE,
        'disable-smart-shrinking' => TRUE,
        'footer-html' => View::factory('documents/specs')
          ->set('invoice', $invoice)
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

    $this->response->send_file($fullname);

//    try {
//      $file = ORM::factory('file');
//      $file->name = $newname;
//      $file->type = 'application/pdf';
//      $file->size = filesize($fullname);
//      $file->operation      = 'A';
//      $file->operation_type = 'INV';
//      $file->content_md5    = md5_file($fullname);
//      $file->path = DIRECTORY_SEPARATOR.str_replace(DOCROOT, '', DOCPATH).$newdir.DIRECTORY_SEPARATOR.$newname;
//      $file->save();
//      return $file->id;
//    } catch (ORM_Validation_Exception $e) {
//      foreach ($e->errors('') as $err) Notify::msg(SGS::errorify($err).' ('.$file->name.')', 'error');
//      return FALSE;
//    }
  }




  public function action_create() {
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
//          'draft'   => 'Draft Copy',
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
        'operator_id' => $site_id,
        'specs_info'  => $type,
      ));

      $data = ORM::factory('SPECS');
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

            $table = View::factory('data')
              ->set('classes', array('has-pagination'))
              ->set('form_type', 'SPECS')
              ->set('data', $data)
              ->set('operator', $operator)
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
            $file_id  = self::generate_specs($data);
            $user_id  = Auth::instance()->get_user()->id ?: 1;

            if ($file_id) Notify::msg('File successfully generated.', NULL, TRUE);
            else Notify::msg('Sorry, file failed to be generated. Please try again.', 'error');

            try {
              $sql = "INSERT INTO specs (number, is_draft, file_id, user_id)
                      VALUES (".$number.",".$is_draft.",".$file_id.",".$user_id.")
                        RETURNING id";

              $specs_id = DB::query(Database::INSERT, $sql)
                ->execute()
                ->get('id');

              foreach ($data as $item) {
                $item->specs_id = $specs_id;
                $item->save();
              }

              Notify::msg(($invoice->is_draft ? 'Draft document' : 'Document') . ' created.', 'success', TRUE);
              $this->request->redirect('invoices/'.$invoice->id);
            } catch (Exception $e) {
              Notify::msg('Sorry, unable to create document. Please try again.', 'error');
            }
            break;
        }
      } else Notify::msg('No data found. Skipping document.', 'warning');
    }
    else if ($settings = Session::instance()->get('pagination.specs.data')) {
      $form->operator_id->val($operator_id = $settings['operator_id']);
      $form->specs_info->val($specs_info = $settings['specs_info']);
    }

    if ($form) $content .= $form;
    $content .= $table;
    $content .= $pagination;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

}