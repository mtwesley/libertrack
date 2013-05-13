<?php

class Controller_Manage extends Controller {

  public function before() {
    parent::before();

    if (!Auth::instance()->logged_in()) {
      Notify::msg('Please login.', NULL, TRUE);
      $this->request->redirect('login?destination='.$this->request->uri());
    }
    elseif (!Auth::instance()->logged_in('admin')) {
      Notify::msg('Access denied. You must have '.SGS::$roles['admin'].' privileges.', 'locked', TRUE);
      $this->request->redirect();
    }
  }

  public function action_index() {
    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  public function action_printjobs() {
    if (!Auth::instance()->logged_in()) {
      Notify::msg('Please login.', NULL, TRUE);
      $this->request->redirect('login?destination='.$this->request->uri());
    }
    elseif (!Auth::instance()->logged_in('barcodes')) {
      Notify::msg('Access denied. You must have '.SGS::$roles['barcodes'].' privileges.', 'locked', TRUE);
      $this->request->redirect();
    }

    $id        = $this->request->param('id');
    $command   = $this->request->param('command');

    if (!$command && !is_numeric($id)) {
      $command = $id;
      $id      = NULL;
    }

    switch ($command) {
      case 'upload': return self::handle_printjob_upload();
      case 'download': return self::handle_printjob_download();
      case 'label': return self::handle_printjob_labels($id);
      case 'edit': return self::handle_printjob_edit($id);
      case 'list': return self::handle_printjob_list($id);
      default: if ($id) return self::handle_printjob_list($id);
    }

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  private function handle_printjob_edit($id) {
    $printjob  = ORM::factory('printjob', $id);
    $printjobs = array($printjob);

    $form = Formo::form()
      ->orm('load', $printjob, array('site_id'))
      ->add('save', 'submit', 'Update Print Job');

    if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
      try {
        $printjob->save();
        Notify::msg('Print job successfully updated.', 'success', TRUE);
        $this->request->redirect('printjobs/'.$printjob->id);
      } catch (Database_Exception $e) {
        Notify::msg('Sorry, unable to save print job due to incorrect or missing input. Please try again.', 'error');
      } catch (Exception $e) {
        Notify::msg('Sorry, print job failed to be saved. Please try again.', 'error');
      }
    }

    if ($printjobs) $table .= View::factory('printjobs')
      ->set('classes', array('has-pagination'))
      ->set('printjobs', $printjobs);

    if ($form) $content .= $form->render();
    $content .= $table;
    $content .= $pagination;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  private function handle_printjob_list($id) {
    if ($id) {
      Session::instance()->delete('pagination.printjob.list');

      $printjob = ORM::factory('printjob', $id);
      $printjobs = array($printjob);

      $pagination = Pagination::factory(array(
        'items_per_page' => 50,
        'total_items' => $printjob->barcodes->find_all()->count()));

      $barcodes = $printjob->barcodes
        ->offset($pagination->offset)
        ->limit($pagination->items_per_page);
      if ($sort = $this->request->query('sort')) $barcodes->order_by($sort);
      $barcodes = $barcodes->order_by('printjob_id')
        ->order_by('barcode')
        ->find_all()
        ->as_array();

      if ($pagination->total_items == 1) Notify::msg($pagination->total_items.' barcode found');
      elseif ($pagination->total_items) Notify::msg($pagination->total_items.' barcodes found');
      else Notify::msg('No barcodes found');
    }
    else {
      if (!Request::$current->query()) Session::instance()->delete('pagination.printjob.list');

      $site_ids = DB::select('id', 'name')
        ->from('sites')
        ->order_by('name')
        ->execute()
        ->as_array('id', 'name');

      $form = Formo::form()
        ->add_group('site_id', 'select', $site_ids, NULL, array('label' => 'Site'))
        ->add('search', 'submit', 'Filter');

      if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
        Session::instance()->delete('pagination.printjob.list');
        $site_id = $form->site_id->val();

        $printjobs = ORM::factory('printjob');
        if ($site_id) $printjobs->and_where('site_id', 'IN', (array) $site_id);

        Session::instance()->set('pagination.printjob.list', array(
          'site_id' => $site_id,
        ));
      }
      else {
        if ($settings = Session::instance()->get('pagination.printjob.list')) {
          $form->site_id->val($site_id = $settings['site_id']);
        }

        $printjobs = ORM::factory('printjob');
        if ($site_id) $printjobs->and_where('site_id', 'IN', (array) $site_id);
      }

      if ($printjobs) {
        $clone = clone($printjobs);
        $pagination = Pagination::factory(array(
          'items_per_page' => 20,
          'total_items' => $clone->find_all()->count()));

        $printjobs = $printjobs
          ->offset($pagination->offset)
          ->limit($pagination->items_per_page);
        if ($sort = $this->request->query('sort')) $printjobs->order_by($sort);
        $printjobs = $printjobs->order_by('number')
          ->find_all()
          ->as_array();

        if ($pagination->total_items == 1) Notify::msg($pagination->total_items.' print job found');
        elseif ($pagination->total_items) Notify::msg($pagination->total_items.' print jobs found');
        else Notify::msg('No print jobs found');
      }
    }

    if ($printjobs) $table .= View::factory('printjobs')
      ->set('classes', array('has-pagination'))
      ->set('printjobs', $printjobs);

    if ($barcodes) $table .= View::factory('barcodes')
      ->set('classes', array('has-pagination'))
      ->set('barcodes', $barcodes);

    if ($form) $content .= $form->render();
    $content .= $table;
    $content .= $pagination;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  private function handle_printjob_upload() {
    $printjob = ORM::factory('printjob');
    $form = Formo::form()
      ->orm('load', $printjob, array('site_id'))
      ->add('upload[]', 'file', array(
        'label'    => 'File',
        'required' => TRUE,
        'attr'  => array('multiple' => 'multiple')
      ))
      ->add('save', 'submit', 'Upload');

    if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
      $barcode_success = 0;
      $barcode_error = 0;

      $num_files = count(reset($_FILES['upload']));
      for ($j = 0; $j < $num_files; $j++) {
        $upload = array(
          'name'     => $_FILES['upload']['name'][$j],
          'type'     => $_FILES['upload']['type'][$j],
          'tmp_name' => $_FILES['upload']['tmp_name'][$j],
          'error'    => $_FILES['upload']['error'][$j],
          'size'     => $_FILES['upload']['size'][$j]
        );

        $info = pathinfo($upload['name']);
        if (!array_filter($info)) Notify::msg('Sorry, no upload found or there is an error in the system. Please try again.', 'error');
        else {

          $array = file($upload['tmp_name']);

          // upload file
          $file = ORM::factory('file');
          $file->name = $upload['name'];
          $file->type = $upload['type'];
          $file->size = $upload['size'];
          $file->operation = 'U';
          $file->operation_type = 'PJ';
          $file->content_md5 = md5_file($upload['tmp_name']);

          try {
            $file->save();
            Notify::msg($file->name.' print job successfully uploaded.', 'success', TRUE);
          } catch (ORM_Validation_Exception $e) {
            foreach ($e->errors('') as $err) Notify::msg(SGS::errorify($err), 'error');
          } catch (Exception $e) {
            Notify::msg('Sorry, print job upload failed. Please try again.', 'error');
          }

          if ($file->id) {
            // save printjob
            $_printjob = clone($printjob);
            $_printjob->allocation_date = SGS::date('now', SGS::PGSQL_DATE_FORMAT);
            for ($i = 0; $i < 10; $i++) {
              $matches = array();
              if (preg_match('/Print\sJob(\sID)?\:\s*(\d+).*/i', $array[$i], $matches)) {
                $_printjob->number = $matches[2];
                break;
              }
            }

            try {
              $_printjob->save();
            } catch (Exception $e) {
              Notify::msg('Sorry, print job failed to be saved. Please try again.', 'error', TRUE);
              try {
                $file->delete();
              } catch (Exception $e) {
                Notify::msg('Sorry, attempting to delete an non-existing file failed.', 'warning', TRUE);
              }
            }

            // prase barcodes
            $start = Model_Printjob::PARSE_START;
            $count = count($array);
            for ($i = $start; $i < ($count - 1); $i++) {
              $line = $array[$i];
              if (! $data = $_printjob->parse_txt($line, $array)) continue;

              $barcode = ORM::factory('barcode');
              $barcode->printjob = $_printjob;
              $barcode->barcode = $data['barcode'];
              try {
                $barcode->save();
                $barcode_success++;
              } catch (Exception $e) {
                $barcode_error++;
              }
            }
          }
        }
      }
      if ($barcode_success) Notify::msg($barcode_success.' barcodes successfully parsed.', 'success', TRUE);
      if ($barcode_error) Notify::msg($barcode_error.' barcodes failed to be parsed.', 'error', TRUE);

      if (isset($_printjob) and $_printjob->loaded()) $this->request->redirect('barcodes');
    }

    if ($form) $content .= $form->render();

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  private function handle_printjob_download($id = NULL) {
    if ($id) {
      $barcodes = DB::select('barcode')
        ->from('barcodes')
        ->where('printjob_id', '=', $id);

      $printjob = ORM::factory('printjob', $id);
    }
    else {
      $site_ids = DB::select('id', 'name')
        ->from('sites')
        ->order_by('name')
        ->execute()
        ->as_array('id', 'name');

      $form = Formo::form()
        ->add_group('site_id', 'select', $site_ids, NULL, array('label' => 'Site'))
        ->add('download', 'submit', 'Download');

      if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
        $site_id = $form->site_id->val();

        $site_name = DB::select('id', 'name')
          ->from('sites')
          ->where('id', 'IN', (array) $site_id)
          ->execute()
          ->as_array('id', 'name');

        $barcodes = DB::select('barcode')
          ->from('barcodes')
          ->join('printjobs')
          ->on('barcodes.printjob_id', '=', 'printjobs.id')
          ->where('printjobs.site_id', 'IN', (array) $site_id);
      }
    }

    if (!is_null($barcodes)) {
      if ($barcodes = $barcodes->execute()->as_array()) {
        $text = "% Total barcodes: ".count($barcodes);
        if ($id) $text .= "\n% Print Job: ".$id;
        $text .= "\n% Requested by: ".Auth::instance()->get_user()->name;
        $text .= "\n% Role: LiberTrack, Barcode Management";
        $text .= "\n% Requested from IP: ".Request::$client_ip;
        $text .= "\n% Generated at: ".SGS::datetime('now', 'm/d/Y H:i:s T,(\G\M\TP)');

        foreach ($barcodes as $barcode) $text .= "\n".$barcode['barcode'];
        if ($id) $text .= "\n% End of Print Job: ";

        try {
          $tempname = tempnam(sys_get_temp_dir(), 'PJ_download_').'.txt';

          if ($printjob) $fullname = 'PrintJob_'.$printjob->number;
          elseif ($site_name) $fullname = 'Masterlist_'.preg_replace('/\W/', '_', implode('_', (array) $site_name));
          else $fullname = 'Masterlist';
          $fullname .= '.txt';

          file_put_contents($tempname, $text);

          $this->response->send_file($tempname, $fullname, array('mime_type' => 'text/plain', 'delete' => TRUE));
        } catch (Exception $e) {
          Notify::msg('Sorry, unable to download barcode masterlist. Please try again.', 'error');
        }
      }
      else {
        Notify::msg('No barcodes match criteria for download.', 'warning');
      }
    }

    if ($form) $content .= $form->render();

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  private function handle_printjob_labels($id) {
    $barcodes = array();
    $printjob = ORM::factory('printjob', $id);

    if ($printjob->loaded()) $barcodes = DB::select('barcode')
      ->distinct(TRUE)
      ->from('barcodes')
      ->where('printjob_id', '=', (array) $id)
      ->execute()
      ->as_array(NULL, 'barcode');

    $pdf = new Label('L7159');
    $pdf->Open();

    foreach($barcodes as $barcode) {
      $tempname = tempnam(sys_get_temp_dir(), 'br_');
      Barcode::png($barcode, $tempname);
      for ($i = 0; $i < 5; $i++) {
        $pdf->Add_Barcode_Label($barcode, $tempname, 'PNG');
      }
      $pdf->Add_Barcode_Label(NULL, NULL, NULL);
    }

    die($pdf->Output('PRINTJOB_LABELS_'.$printjob->number.'.pdf', 'D'));
  }

  public function action_barcodes() {
    if (!Auth::instance()->logged_in()) {
      Notify::msg('Please login.', NULL, TRUE);
      $this->request->redirect('login?destination='.$this->request->uri());
    }
    elseif (!Auth::instance()->logged_in('barcodes')) {
      Notify::msg('Access denied. You must have '.SGS::$roles['barcodes'].' privileges.', 'locked', TRUE);
      $this->request->redirect();
    }

    $id        = $this->request->param('id');
    $command   = $this->request->param('command');

    if (!$command && !is_numeric($id)) {
      $command = $id;
      $id      = NULL;
    }

    switch ($command) {
      case 'edit': return self::handle_barcode_edit($id);
      case 'query': return self::handle_barcode_query();
      case 'list': return self::handle_barcode_list($id);
      default: if ($id) return self::handle_barcode_list($id);
    }

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  private function handle_barcode_edit($id) {
    $barcode = ORM::factory('barcode', $id);
    $barcodes = array($barcode);

    $form = Formo::form()
      ->orm('load', $barcode)
      ->add('save', 'submit', 'Update Print Job');

    if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
      try {
        $barcode->save();
        Notify::msg('Print job successfully updated.', 'success', TRUE);
        $this->request->redirect('manage/barcodes/'.$barcode->id);
      } catch (Database_Exception $e) {
        Notify::msg('Sorry, unable to save print job due to incorrect or missing input. Please try again.', 'error');
      } catch (Exception $e) {
        Notify::msg('Sorry, print job failed to be saved. Please try again.', 'error');
      }
    }

    if ($barcodes) $table .= View::factory('barcodes')
      ->set('classes', array('has-pagination'))
      ->set('barcodes', $barcodes);

    if ($form) $content .= $form->render();
    $content .= $table;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  private function handle_barcode_list($id = NULL) {
    if ($id) {
      Session::instance()->delete('pagination.barcode.list');

      $barcode = ORM::factory('barcode', $id);
      $barcodes = array($barcode);

      $ids  = array();
      $data = array();

      // data

      foreach (array('SSF', 'TDF', 'LDF', 'SPECS') as $key) {
        $search = ORM::factory($key);
        foreach (array_keys($search->table_columns()) as $field) if (strpos($field, 'barcode') !== FALSE) $search->or_where($field, '=', $barcode->id);
        foreach ($search = $search->find_all() as $item) $data[$key][] = $item;
      }

      foreach ($data as $type => $items) switch ($type) {
        case 'SSF':
        case 'TDF':
        case 'LDF':
        case 'SPECS':
          foreach ($items as $item) $ids[$type][] = $item->id;
          $data_table .= View::factory('data')
            ->set('classes', array('has-section'))
            ->set('form_type', $type)
            ->set('data', $items)
            ->set('options', array('header' => FALSE, 'hide_header_info' => TRUE))
            ->render();
      }

      // invoices

      $invoices = ORM::factory('invoice')
        ->join('invoice_data')
        ->on('invoice_id', '=', 'invoices.id');
      foreach ($ids as $type => $id) if ($id) {
        $invoices = $invoices
          ->or_where_open()
          ->where('invoice_data.form_type', '=', $type)
          ->and_where('invoice_data.form_data_id', '=', $id)
          ->or_where_close();
      }
      $invoices = $invoices->find_all();

      $data_table .= View::factory('invoices')
        ->set('classes', array('has-section'))
        ->set('invoices', $invoices)
        ->render();

      // documents

      $documents = ORM::factory('document')
        ->join('document_data')
        ->on('document_id', '=', 'documents.id');
      foreach ($ids as $type => $id) if ($id) {
        $documents = $documents
          ->or_where_open()
          ->where('document_data.form_type', '=', $type)
          ->and_where('document_data.form_data_id', '=', $id)
          ->or_where_close();
      }
      $documents = $documents->find_all();

      $data_table .= View::factory('documents')
        ->set('classes', array('has-section'))
        ->set('documents', $documents)
        ->render();
    }
    else {
      if (!Request::$current->query()) Session::instance()->delete('pagination.barcode.list');

      $site_ids = DB::select('id', 'name')
        ->from('sites')
        ->order_by('name')
        ->execute()
        ->as_array('id', 'name');

      $form = Formo::form()
        ->add_group('site_id', 'select', $site_ids, NULL, array('label' => 'Site'))
        ->add_group('type', 'checkboxes', SGS::$barcode_type, NULL, array('label' => 'Type'))
        ->add('search', 'submit', 'Filter');

      if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
        Session::instance()->delete('pagination.barcode.list');
        $site_id = $form->site_id->val();
        $type    = $form->type->val();

        $barcodes = ORM::factory('barcode');
        if ($site_id) {
          $barcodes = $barcodes
            ->join('printjobs')
            ->on('printjob_id', '=', 'printjobs.id')
            ->and_where('printjobs.site_id', 'IN', (array) $site_id);
        }
        if ($type) $barcodes->where('type', 'IN', (array) $type);

        Session::instance()->set('pagination.barcode.list', array(
          'site_id' => $site_id,
          'type'    => $type
        ));

      }
      else {
        if ($settings = Session::instance()->get('pagination.barcode.list')) {
          $form->site_id->val($site_id = $settings['site_id']);
          $form->type->val($type = $settings['type']);
        }

        $barcodes = ORM::factory('barcode');
        if ($site_id) {
          $barcodes = $barcodes
            ->join('printjobs')
            ->on('printjob_id', '=', 'printjobs.id')
            ->and_where('printjobs.site_id', 'IN', (array) $site_id);
        }
        if ($type) $barcodes->where('type', 'IN', (array) $type);
      }

      if ($barcodes) {
        $clone = clone($barcodes);
        $pagination = Pagination::factory(array(
          'items_per_page' => 50,
          'total_items' => $clone->find_all()->count()));

        $barcodes = $barcodes
          ->offset($pagination->offset)
          ->limit($pagination->items_per_page);
        if ($sort = $this->request->query('sort')) $barcodes->order_by($sort);
        $barcodes = $barcodes->order_by('barcode')
          ->find_all()
          ->as_array();

        if ($pagination->total_items == 1) Notify::msg($pagination->total_items.' barcodes found');
        elseif ($pagination->total_items) Notify::msg($pagination->total_items.' barcodes found');
        else Notify::msg('No barcodes found');
      }
    }

    if ($barcodes) $table .= View::factory('barcodes')
      ->set('classes', array('has-pagination'))
      ->set('barcodes', $barcodes);

    if ($form) $content .= $form->render();
    $content .= $table;
    $content .= $data_table;
    $content .= $pagination;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  private function handle_barcode_query() {
    $operator_ids = DB::select('id', 'name')
      ->from('operators')
      ->order_by('name')
      ->execute()
      ->as_array('id', 'name');

    $site_ids = DB::select('id', 'name')
      ->from('sites')
      ->order_by('name')
      ->execute()
      ->as_array('id', 'name');

    $form = Formo::form(array('attr' => array('class' => 'search')))
      ->add('barcode', 'input', array('label' => 'Barcode', 'rules' => array(array('min_length', array(':value', 3))), 'attr' => array('class' => 'barcode-text autocomplete-barcode-barcode')))
      ->add_group('operator_id', 'select', $operator_ids, NULL, array('label' => 'Operator', 'attr' => array('class' => 'site_operatoropts')))
      ->add_group('site_id', 'select', $site_ids, NULL, array('label' => 'Site', 'attr' => array('class' => 'siteopts')))
      ->add_group('similarity', 'select', array('exact' => 'Exact', 'high' => 'High', 'medium' => 'Medium', 'low' => 'Low'), 'medium', array('label' => 'Similarity'))
      ->add('submit', 'submit', 'Query');

    if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
      $barcode     = trim(strtoupper($form->barcode->val()));
      $similarity  = $form->similarity->val();
      $operator_id = $form->operator_id->val();
      $site_id     = $form->site_id->val();

      if ($site_id)     $args['sites.id'] = $site_id;
      if ($operator_id) $args['operators.id'] = $operator_id;

      switch ($similarity) {
        case 'exact': $suggestions = SGS::suggest_barcode($barcode, $args ?: array(), 'id', TRUE, 2, 0.8, 1, 25, 0); break;
        case 'high': $suggestions = SGS::suggest_barcode($barcode, $args ?: array(), 'id', FALSE, 2, 0.5, 5, 25, 0); break;
        case 'low': $suggestions = SGS::suggest_barcode($barcode, $args ?: array(), 'id', FALSE, 2, 0.1, 10, 25, 0); break;

        case 'medium':
        default: $suggestions = SGS::suggest_barcode($barcode, $args ?: array(), 'id', FALSE, 2, 0.3, 7, 25, 0); break;
      }

      if ($suggestions) {
        Notify::msg(count($suggestions).' results found.', 'success');

        $barcodes = ORM::factory('barcode')
          ->where('id', 'IN', (array) $suggestions)
          ->find_all()
          ->as_array();
      } else Notify::msg('No results found.');

      if ($barcodes) $table = View::factory('barcodes')
        ->set('classes', array('has-pagination'))
        ->set('barcodes', $barcodes);
    }

    $content .= $form->render();
    $content .= $table;
    $content .= $pagination;

    $view = View::factory('main')
      ->set('content', $content)
      ->set('left', $left);

    $this->response->body($view);
  }

}
