<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Declaration extends Controller {

  public function before() {
    parent::before();

    if (!Auth::instance()->logged_in()) {
      Notify::msg('Please login.', NULL, TRUE);
      $this->request->redirect('login?destination='.$this->request->uri());
    }
    elseif (!Auth::instance()->logged_in('data')) {
      Notify::msg('Access denied. You must have '.SGS::$roles['data'].' privileges.', 'locked', TRUE);
      $this->request->redirect();
    }

    Session::instance()->write();
  }

  private function handle_file_list($id = NULL) {
    if (!Request::$current->query()) Session::instance()->delete('pagination.file.list');
    if ($id) {
      Session::instance()->delete('pagination.file.list');

      $files = ORM::factory('file')
        ->where('operation', '=', 'U')
        ->and_where('operation_type', 'IN', array_keys(SGS::$form_data_type))
        ->and_where('id', '=', $id)
        ->find_all()
        ->as_array();
    }
    else {
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

      $form = Formo::form()
        ->add_group('operation_type', 'checkboxes', SGS::$form_data_type, NULL, array('label' => 'Type'))
        ->add_group('operator_id', 'select', $operator_ids, NULL, array('label' => 'Operator', 'attr' => array('class' => 'site_operatoropts')))
        ->add_group('site_id', 'select', $site_ids, NULL, array('label' => 'Site', 'attr' => array('class' => 'siteopts')))
        ->add_group('block_id', 'select', array(), NULL, array('label' => 'Block', 'attr' => array('class' => 'blockopts')))
        ->add('search', 'submit', 'Filter');

      if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
        Session::instance()->delete('pagination.file.list');

        $operation_type = $form->operation_type->val();
        $operator_id    = $form->operator_id->val();
        $site_id        = $form->site_id->val();
        $block_id       = $form->block_id->val();

        $files = ORM::factory('file')
          ->where('operation', '=', 'U')
          ->and_where('operation_type', 'IN', array_keys(SGS::$form_data_type));

        if ($operation_type) $files->and_where('operation_type', 'IN', (array) $operation_type);
        if ($operator_id)    $files->and_where('operator_id', 'IN', (array) $operator_id);
        if ($site_id)        $files->and_where('site_id', 'IN', (array) $site_id);
        if ($block_id)       $files->and_where('block_id', 'IN', (array) $block_id);

        Session::instance()->set('pagination.file.list', array(
          'form_type'   => $operation_type,
          'operator_id' => $operator_id,
          'site_id'     => $site_id,
          'block_id'    => $block_id,
        ));
      }
      else {
        if ($settings = Session::instance()->get('pagination.file.list')) {
          $form->operation_type->val($operation_type = $settings['form_type']);
          $form->operator_id->val($operator_id = $settings['operator_id']);
          $form->site_id->val($site_id = $settings['site_id']);
          $form->block_id->val($block_id = $settings['block_id']);
        }

        $files = ORM::factory('file')
          ->where('operation', '=', 'U')
          ->and_where('operation_type', 'IN', array_keys(SGS::$form_data_type));

        if ($operation_type) $files->and_where('operation_type', 'IN', (array) $operation_type);
        if ($operator_id)    $files->and_where('operator_id', 'IN', (array) $operator_id);
        if ($site_id)        $files->and_where('site_id', 'IN', (array) $site_id);
        if ($block_id)       $files->and_where('block_id', 'IN', (array) $block_id);
      }

      if ($files) {
        $clone = clone($files);
        $pagination = Pagination::factory(array(
          'items_per_page' => 20,
          'total_items' => $clone->find_all()->count()));

        $files = $files
          ->offset($pagination->offset)
          ->limit($pagination->items_per_page);
        if ($sort = $this->request->query('sort')) $files->order_by($sort);
        $files = $files->order_by('timestamp', 'DESC')
          ->find_all()
          ->as_array();
      }
    }

    if ($files) {
      $table = View::factory('files')
        ->set('classes', array('has-pagination'))
        ->set('files', $files)
        ->render();
      if ($pagination->total_items == 1) Notify::msg($pagination->total_items.' file found');
      elseif ($pagination->total_items) Notify::msg($pagination->total_items.' files found');
    }
    else Notify::msg('No files found');

    if ($form) $content .= $form->render();
    $content .= $table;
    $content .= $pagination;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  private function handle_file_delete($id) {
    if (Auth::instance()->get_user()->id != 1) {
      Notify::msg('Access denied. You must be the superuser to delete files.', 'locked', TRUE);
      $this->request->redirect();
    }

    $file = ORM::factory('file', $id);
    if (!$file->loaded()) {
      Notify::msg('No file found.', 'warning', TRUE);
      $this->request->redirect('declaration/files');
    }

    Notify::msg('Deleting this file will delete all declaration and related form data.', 'warning');
    $form = Formo::form()
      ->add('confirm', 'text', 'Are you sure you want to delete this file?')
      ->add('delete', 'centersubmit', 'Delete');

    $success = 0;
    $error   = 0;

    if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
      foreach ($file->csv->find_all()->as_array() as $csv) {
        try {
          $csv->delete();
          if ($csv->loaded()) throw new Exception();
          $success++;
        } catch (Exception $e) {
          $error++;
        }
      }

      if ($success) Notify::msg($success.' records deleted.', 'success', TRUE);
      if ($error)   Notify::msg($error.' records failed to be deleted.', 'error', TRUE);

      try {
        $filename = DOCROOT.preg_replace('/^\//', '', $file->path);
        if (is_file($filename) and file_exists($filename)) {
          if (!unlink($filename)) Notify::msg('Unable to delete local file. Check file access capabilities with the site administrator and try again.', 'warning', TRUE);
        }
        $file->delete();
        Notify::msg('File deleted.', 'success', TRUE);
      } catch (Exception $e) {
        Notify::msg('Sorry, unable to delete file.', 'error', TRUE);
      }

      $this->request->redirect('declaration/files');
    }

    $table = View::factory('files')
      ->set('files', array($file))
      ->set('options', array(
        'links' => FALSE
      ))
      ->render();

    if ($form) $content .= $form->render();
    $content .= $table;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  private function handle_file_review($id = NULL) {
    if (!Request::$current->query()) Session::instance()->delete('pagination.file.review');
    if (!$id) $id = $this->request->param('id');

    $file = ORM::factory('file', $id);
    if (!$file->loaded()) {
      Notify::msg('No file found.', 'warning', TRUE);
      $this->request->redirect('declaration/files');
    }

    $csvs = $file->csv
      ->order_by('status');
    if ($sort = $this->request->query('sort')) $csvs->order_by($sort);
    $csvs = $csvs->order_by('timestamp', 'DESC');

    $form = Formo::form()
      ->add_group('status', 'checkboxes', SGS::$csv_status, NULL, array('label' => 'Status'))
      ->add('search', 'submit', 'Filter');

    if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
      Session::instance()->delete('pagination.file.review');

      $status = $form->status->val();
      if ($status) $csvs->and_where('status', 'IN', (array) $status);

      Session::instance()->set('pagination.file.review', array('status' => $status));
    }
    else {
      if ($settings = Session::instance()->get('pagination.file.review')) {
        $form->status->val($status = $settings['status']);
      }

      if ($status) $csvs->and_where('status', 'IN', (array) $status);
    }

    if ($csvs) {
      $clone = clone($csvs);
      $pagination = Pagination::factory(array(
        'items_per_page' => 50,
        'total_items' => $total_items = $clone->find_all()->count()));

      $csvs = $csvs
        ->offset($pagination->offset)
        ->limit($pagination->items_per_page)
        ->find_all()
        ->as_array();

      $table = View::factory('csvs')
        ->set('classes', array('has-pagination'))
        ->set('csvs', $csvs)
        ->set('fields', SGS_Form_ORM::get_fields($file->operation_type))
        ->set('operator', $file->operator->loaded() ? $file->operator : NULL)
        ->set('site', $file->site->loaded() ? $file->site : NULL)
        ->set('block', $file->block->loaded() ? $file->block : NULL)
        ->set('total_items', $total_items)
        ->set('options', array('header' => TRUE))
        ->render();
      if ($pagination->total_items == 1) Notify::msg($pagination->total_items.' record found');
      elseif ($pagination->total_items) Notify::msg($pagination->total_items.' records found');
      else Notify::msg('No records found');
    }

    if ($form) $content .= $form->render();
    $content .= $table;
    $content .= $pagination;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  private function handle_file_download($id = NULL) {
    set_time_limit(600);
    if (!$id) $id = $this->request->param('id');

    $file = ORM::factory('file', $id);

    $status = is_array($_REQUEST['status']) ? $_REQUEST['status'] : array();
    $type   = (isset($_REQUEST['type']) and in_array($_REQUEST['type'], array('csv', 'xls'))) ? $_REQUEST['type'] : 'xls';
    $name   = isset($_REQUEST['name']) ? $_REQUEST['name'] : NULL;

    $csvs   = $file->csv;
    if ($status) $csvs->where('status', 'IN', $status);
    $csvs = $csvs->find_all();

    switch ($type) {
      case 'csv':
        $excel = new PHPExcel();
        $excel->setActiveSheetIndex(0);
        $writer = new PHPExcel_Writer_CSV($excel);
        $headers = TRUE;
        $mime_type = 'text/csv';
        break;
      case 'xls':
        $filename = APPPATH.'/templates/'.$file->operation_type.'.xls';
        try {
          $reader = new PHPExcel_Reader_Excel5;
          if (!$reader->canRead($filename)) $reader = PHPExcel_IOFactory::createReaderForFile($filename);
          $excel = $reader->load($filename);
        } catch (Exception $e) {
          Notify::msg('Unable to load Excel document template. Please try again.', 'error', TRUE);
        }
        $excel->setActiveSheetIndex(0);
        $writer = new PHPExcel_Writer_Excel5($excel);
        $headers = FALSE;
        $mime_type = 'application/vnd.ms-excel';
        break;
    }

    if ($excel) {
      // data
      $create_date = 0;
      $row   = constant('Model_'.$file->operation_type.'::PARSE_START');
      $model = ORM::factory($file->operation_type);

      foreach ($csvs as $csv) {
        $model->download_data($csv->values, $csv->get_errors(), $excel, $row);
        if (strtotime($csv->values['create_date']) > strtotime($create_date)) $create_date = $csv->values['create_date'];
        $row++;
        unset($csv);
      }

      // headers
      $model->download_headers($csv->values, $excel, array(
        'create_date' => $create_date ? $create_date : $create_date = SGS::date ('now', SGS::PGSQL_DATE_FORMAT)
      ), $headers);

      // file
      $tempname  = tempnam(sys_get_temp_dir(), strtolower($file->operation_type).'_download_').'.'.$type;
      $fullname  = $name ? $name.'.'.$type : substr($file->name, 0, strrpos($file->name, '.')).'.'.$type;

      $writer->save($tempname);
      $this->response->send_file($tempname, $fullname, array('mime_type' => $mime_type, 'delete' => TRUE));
    }
  }

  private function handle_file_process($id = NULL) {
    set_time_limit(600);

    if (!$id) $id = $this->request->param('id');
    $file = ORM::factory('file', $id);
    if (!$file->loaded()) {
      Notify::msg('No file found.', 'warning', TRUE);
      $this->request->redirect('declaration/files');
    }

    // pending
    $csv_ids = DB::select('csv.id')
      ->from('files')
      ->join('csv')
      ->on('files.id', '=', 'csv.file_id')
      ->where('files.id', '=', $file->id)
      ->and_where('csv.status', 'IN', array('P', 'R', 'U'))
      ->execute()
      ->as_array(NULL, 'id');

    $accepted   = 0;
    $rejected   = 0;
    $duplicated = 0;
    $deleted    = 0;
    $failure    = 0;

    foreach ($csv_ids as $csv_id) {
      $csv = ORM::factory('CSV', $csv_id);
      $csv->process();
      switch ($csv->status) {
        case 'A': $accepted++; break;
        case 'R': $rejected++; break;
        case 'U': $duplicated++; break;
        case 'D': $deleted++; break;
        default:  $failure++;
      }
      unset($csv);
    }

    if ($accepted) Notify::msg($accepted.' records accepted as form data.', 'success', TRUE);
    if ($rejected) Notify::msg($rejected.' records rejected as form data.', 'error', TRUE);
    if ($duplicated) Notify::msg($duplicated.' records marked as duplicates of form data.', 'warning', TRUE);
    if ($deleted)  Notify::msg($deleted.' records marked as deleted.', 'error', TRUE);
    if ($failure)  Notify::msg($failure.' records failed to be processed.', 'error', TRUE);

    $this->request->redirect('declaration/files/'.$id.'/review');
  }

  private function handle_csv_process($id) {
    $id = $this->request->param('id');

    $csv = ORM::factory('csv', $id);
    if ($csv->status == 'A') {
      Notify::msg('Sorry, data that has been processed and accepted cannot be re-processed. Please edit the form data instead.', 'warning', TRUE);
      $this->request->redirect('declaration/data/'.$id.'/list');
    }

    if ($csv->status == 'C') {
      Notify::msg('Sorry, data that has been corrected cannot be re-processed.', 'warning', TRUE);
      $this->request->redirect('declaration/data/'.$id.'/list');
    }

    $form_type = $csv->form_type;
    $fields    = SGS_Form_ORM::get_fields($form_type);

    $csv->status = 'P';
    $csv->process();
    switch ($csv->status) {
      case 'A': Notify::msg('Updated data accepted as form data.', 'success', TRUE); break;
      case 'R': Notify::msg('Updated data rejected as form data.', 'error', TRUE); break;
      case 'U': Notify::msg('Updated data is a duplicate of existing form data.', 'error', TRUE); break;
      case 'C': Notify::msg('Updated data has corrected existing form data.', 'error', TRUE); break;
      case 'D': Notify::msg('Updated data deleted.', 'error', TRUE); break;
      default:  Notify::msg('Updated data failed to be processed.', 'error', TRUE);
    }

    $this->request->redirect('declaration/data/'.$id);
  }

  private function handle_csv_edit($id) {
    if (!Auth::instance()->logged_in('management')) $this->request->redirect('declaration/data/'.$id);

    $id = $this->request->param('id');

    $csv = ORM::factory('csv', $id);
    if ($csv->status == 'A') {
      Notify::msg('Sorry, data that has been processed and accepted cannot be edited. Please edit the form data instead.', 'warning', TRUE);
      $this->request->redirect('declaration/data/'.$id);
    }

    if ($csv->status == 'A') {
      Notify::msg('Sorry, data that has been corrected cannot be edited.', 'warning', TRUE);
      $this->request->redirect('declaration/data/'.$id);
    }

    $form_type = $csv->form_type;
    $fields    = SGS_Form_ORM::get_fields($form_type);

    $form = Formo::form();
    foreach ($fields as $key => $value) {
      $form->add(array(
        'alias' => $key,
        'value' => $csv->values[$key],
        'label' => $value
      ));
    }
    $form->add(array(
      'alias'  => 'save',
      'driver' => 'submit',
      'value'  => 'Save'
    ));

    if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
      foreach ($csv->values as $key => $value) {
        if ($form->$key) $data[$key] = $form->$key->val();
      }

      $csv->values = $data;
      $csv->status = 'P';
      try {
        $csv->save();
        $updated = true;
      } catch (Exception $e) {
        Notify::msg('Sorry, declaration data update failed. Please try again.', 'error');
      }

      if ($updated) {
        $csv->process();
        switch ($csv->status) {
          case 'A': Notify::msg('Updated data accepted as form data.', 'success', TRUE); break;
          case 'R': Notify::msg('Updated data rejected as form data.', 'error', TRUE); break;
          case 'U': Notify::msg('Updated data is a duplicate of existing form data.', 'error', TRUE); break;
          case 'C': Notify::msg('Updated data has corrected existing form data.', 'error', TRUE); break;
          default:  Notify::msg('Updated data failed to be processed.', 'error', TRUE);
        }
      }

      $this->request->redirect('declaration/data/'.$csv->id);
    }

    $csvs = array($csv);
    $table = View::factory('csvs')
      ->set('csvs', $csvs)
      ->set('fields', SGS_Form_ORM::get_fields($csv->form_type))
      ->set('operator', $csv->operator->loaded() ? $csv->operator : NULL)
      ->set('site', $csv->site->loaded() ? $csv->site : NULL)
      ->set('block', $csv->block->loaded() ? $csv->block : NULL)
      ->render();

    $content .= $form->render();
    $content .= $table;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  private function handle_csv_delete($id) {
    if (!Auth::instance()->logged_in('management')) $this->request->redirect('declaration/data/'.$id);

    $id = $this->request->param('id');
    $csv = ORM::factory('csv', $id);
    if (!$csv->loaded()) {
      Notify::msg('No data found.', 'warning', TRUE);
      $this->request->redirect('declaration/data');
    }

    if ($csv->status == 'A') Notify::msg('Deleting this data will delete related form data.', 'warning');

    $form = Formo::form()
      ->add('confirm', 'text', 'Are you sure you want to delete this data?')
      ->add('delete', 'centersubmit', 'Delete');

    if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
      try {
        $csv->delete();
        if ($csv->loaded()) throw new Exception();
        Notify::msg('Data successfully deleted.', 'success', TRUE);
      } catch (Exception $e) {
        Notify::msg('Data failed to be deleted.', 'error', TRUE);
      }

      $this->request->redirect('declaration/data');
    }

    $table = View::factory('csvs')
      ->set('csvs', array($csv))
      ->set('fields', SGS_Form_ORM::get_fields($csv->form_type))
      ->set('options', array(
        'header'  => FALSE,
        'links'   => FALSE,
        'details' => FALSE
      ))
      ->render();

    $content .= $form->render();
    $content .= $table;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  private function handle_csv_list($form_type = NULL, $id = NULL) {
    if (!Request::$current->query()) Session::instance()->delete('pagination.csv');

    $has_block_id   = (bool) (in_array($form_type, array('SSF', 'TDF')));
    $has_site_id    = (bool) (in_array($form_type, array('SSF', 'TDF', 'LDF')));
    $has_specs_info = (bool) (in_array($form_type, array('SPECS')));
    $has_exp_info   = (bool) (in_array($form_type, array('SPECS')));
    $has_wb_info    = (bool) (in_array($form_type, array('WB')));

    if ($id) {
      Session::instance()->delete('pagination.csv');

      $csvs = ORM::factory('csv')
        ->where('operation', '=', 'U')
        ->and_where('form_type', 'IN', array_keys(SGS::$form_data_type))
        ->and_where('id', '=', $id)
        ->find_all()
        ->as_array();

      $csv = reset($csvs);

      $operator = $csv->operator->loaded() ? $csv->operator : NULL;
      $site     = $csv->site->loaded() ? $csv->site : NULL;
      $block    = $csv->block->loaded() ? $csv->block : NULL;

      $form_type = reset($csvs)->form_type;
    }
    elseif ($form_type) {
      if ($has_site_id) $site_ids = DB::select('id', 'name')
        ->from('sites')
        ->order_by('name')
        ->execute()
        ->as_array('id', 'name');
      else $operator_ids = DB::select('id', 'name')
        ->from('operators')
        ->order_by('name')
        ->execute()
        ->as_array('id', 'name');

      $form = Formo::form();
      if ($has_site_id) $form = $form->add_group('site_id', 'select', $site_ids, NULL, array('label' => 'Site', 'attr' => array('class' => 'siteopts')));
      else $form = $form->add_group('operator_id', 'select', $operator_ids, NULL, array_merge(array('label' => 'Operator'), $has_specs_info ? array('attr' => array('class' => 'specs_operatoropts specs_barcode exp_operatoropts exp_barcode')) : array(), $has_wb_info ? array('attr' => array('class' => 'wb_operatoropts wb_barcode')) : array()));
      if ($has_site_id and $has_block_id) $form = $form->add_group('block_id', 'select', array(), NULL, array('label' => 'Block', 'attr' => array('class' => 'blockopts')));
      if ($has_specs_info) $form = $form->add_group('specs_barcode', 'select', array(), NULL, array('label' => 'Shipment Specification', 'attr' => array('class' => 'specsopts')));
//      if ($has_exp_info) $form->add_group('exp_barcode', 'select', array(), NULL, array('label' => 'Export Permit', 'attr' => array('class' => 'expopts')));
      if ($has_wb_info) $form = $form->add_group('wb_barcode', 'select', array(), NULL, array('label' => 'Waybill', 'attr' => array('class' => 'wbopts')));
      $form = $form
        ->add_group('status', 'checkboxes', SGS::$csv_status, NULL, array('label' => 'Status'))
        ->add('format', 'radios', 'filter', array(
          'options' => array(
            'filter' => 'Filter',
            'csv'   => 'Download CSV',
            'xls'   => 'Download XLS'),
          'label' => 'Options',
          'required' => TRUE,
          ))
//        ->add('download_csv', 'submit', 'Download '.SGS::$file_type['csv'])
//        ->add('download_xls', 'submit', 'Download '.SGS::$file_type['xls'])
        ->add('from', 'input', array('label' => 'From', 'attr' => array('class' => 'dpicker', 'id' => 'from-dpicker')))
        ->add('to', 'input', array('label' => 'To', 'attr' => array('class' => 'dpicker', 'id' => 'to-dpicker')))
        ->add('search', 'submit', 'Go');

      if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
        Session::instance()->delete('pagination.csv');

        set_time_limit(600);
        
        $csvs = ORM::factory('csv')
          ->where('operation', '=', 'U')
          ->and_where('form_type', 'IN', array_keys(SGS::$form_data_type))
          ->and_where('form_type', '=', $form_type)
          ->order_by('timestamp', 'DESC');

        if ($has_site_id) $site_id = $form->site_id->val();
        else $operator_id = $form->operator_id->val();

        if ($has_site_id and $has_block_id) $block_id = $form->block_id->val();
        if ($has_specs_info) $specs_barcode = $form->specs_barcode->val();
        if ($has_exp_info) $exp_barcode = $form->exp_barcode->val();
        if ($has_wb_info) $wb_barcode = $form->wb_barcode->val();
        
        $status = $form->status->val();
        $format = $form->format->val();
        $from = $form->from->val();
        $to   = $form->to->val();

        if ($status)      $csvs->and_where('status', 'IN', (array) $status);
        if ($from or $to) $csvs->and_where('timestamp', 'BETWEEN', SGS::db_range($from, $to));
        if ($operator_id) $csvs->and_where('operator_id', 'IN', (array) $operator_id);
        if ($site_id)     $csvs->and_where('site_id', 'IN', (array) $site_id);
        if ($block_id)    $csvs->and_where('block_id', 'IN', (array) $block_id);

        if (Valid::is_barcode($specs_barcode)) $csvs->and_where('values', 'LIKE', '%"specs_barcode";s:'.strlen($specs_barcode).':"'.$specs_barcode.'"%');
        if (Valid::is_barcode($exp_barcode))   $csvs->and_where('values', 'LIKE', '%"exp_barcode";s:'.strlen($exp_barcode).':"'.$exp_barcode.'"%');
        if (Valid::is_barcode($wb_barcode))    $csvs->and_where('values', 'LIKE', '%"wb_barcode";s:'.strlen($wb_barcode).':"'.$wb_barcode.'"%');

        if (in_array($format, array('csv', 'xls'))) {
          $csvs = $csvs->find_all();

          switch ($format) {
            case 'csv':
              $ext = 'csv';
              $excel = new PHPExcel();
              $excel->setActiveSheetIndex(0);
              $writer = new PHPExcel_Writer_CSV($excel);
              $headers = TRUE;
              $mime_type = 'text/csv';
              break;
            case 'xls':
              $ext = 'xls';
              $filename = APPPATH.'/templates/'.$form_type.'.xls';
              try {
                $reader = new PHPExcel_Reader_Excel5;
                if (!$reader->canRead($filename)) $reader = PHPExcel_IOFactory::createReaderForFile($filename);
                $excel = $reader->load($filename);
              } catch (Exception $e) {
                Notify::msg('Unable to load Excel document template. Please try again.', 'error', TRUE);
              }
              $excel->setActiveSheetIndex(0);
              $writer = new PHPExcel_Writer_Excel5($excel);
              $headers = FALSE;
              $mime_type = 'application/vnd.ms-excel';
              break;
          }

          if ($excel) {
            // data
            $create_date = 0;
            $row = constant('Model_'.$form_type.'::PARSE_START');
            $model = ORM::factory($form_type);

            foreach ($csvs as $csv) {
              $model->download_data($csv->values, $csv->get_errors(), $excel, $row);
              if (strtotime($csv->values['create_date']) > strtotime($create_date)) $create_date = $csv->values['create_date'];
              $row++;
              unset($csv);
            }

            if (!$site)     $site     = ORM::factory('site', (int) $site_id);
            if (!$block)    $block    = ORM::factory('block', (int) $block_id);
            if (!$operator) $operator = ORM::factory('operator', (int) $operator_id);

            // headers
            $model->download_headers($csv->values, $excel, array(
              'create_date' => $create_date ? $create_date : $create_date = SGS::date ('now', SGS::PGSQL_DATE_FORMAT)
            ), $headers);

            // file
            $tempname = tempnam(sys_get_temp_dir(), strtolower($form_type).'_download_').'.'.$type;

            switch ($form_type) {
              case 'SSF': $fullname = SGS::wordify(($site->name ? $site->name.'_' : '').'SSF'.($block->name ? '_'.$block->name : '')).'.'.$ext; break;
              case 'TDF': $fullname = SGS::wordify(($site->name ? $site->name.'_' : '').'TDF_'.($block->name ? $block->name.'_' : '').SGS::date($create_date, 'm_d_Y')).'.'.$ext; break;
              case 'LDF': $fullname = SGS::wordify(($site->name ? $site->name.'_' : '').'LDF_'.SGS::date($create_date, 'm_d_Y')).'.'.$ext; break;
              case 'SPECS': $fullname = SGS::wordify(($operator->tin ? $operator->tin.'_' : '').'SPECS_'.SGS::date($create_date, 'm_d_Y')).'.'.$ext; break;
            }

            $writer->save($tempname);
            $this->response->send_file($tempname, $fullname, array('mime_type' => $mime_type, 'delete' => TRUE));
          }
        }

        Session::instance()->set('pagination.csv', array(
          'site_id'       => $site_id,
          'block_id'      => $block_id,
          'specs_barcode' => $specs_barcode,
          'exp_barcode'   => $exp_barcode,
          'wb_barcode'    => $wb_barcode,
          'status'        => $status,
          'from'          => $from,
          'to'            => $to,
        ));

      }
      elseif ($settings = Session::instance()->get('pagination.csv')) {
        $csvs = ORM::factory('csv')
          ->where('operation', '=', 'U')
          ->and_where('form_type', 'IN', array_keys(SGS::$form_data_type))
          ->and_where('form_type', '=', $form_type)
          ->order_by('timestamp', 'DESC');
        
        if ($has_site_id) $form->site_id->val($site_id = $settings['site_id']);
        else $form->operator_id->val($operator_id = $settings['operator_id']);
        if ($has_site_id and $has_block_id) $form->block_id->val($block_id = $settings['block_id']);
        if ($has_specs_info) $form->specs_barcode->val($specs_barcode = $settings['specs_barcode']);
        if ($has_wb_info) $form->wb_barcode->val($wb_barcode = $settings['wb_barcode']);
        if ($has_exp_info) $form->exp_barcode->val($exp_barcode = $settings['exp_barcode']);
        $form->status->val($status = $settings['status']);
        $form->from->val($from = $settings['from']);
        $form->to->val($to = $settings['to']);

        if ($site_id)     $csvs->and_where('site_id', 'IN', (array) $site_id);
        if ($operator_id) $csvs->and_where('operator_id', 'IN', (array) $operator_id);
        if ($block_id)    $csvs->and_where('block_id', 'IN', (array) $block_id);
        if ($status)      $csvs->and_where('status', 'IN', (array) $status);
        if ($from or $to) $csvs->and_where('timestamp', 'BETWEEN', SGS::db_range($from, $to));

        if (Valid::is_barcode($specs_barcode)) $csvs->and_where('data', 'LIKE', '"specs_barcode";s:'.strlen($specs_barcode).':"'.$specs_barcode.'"');
        if (Valid::is_barcode($exp_barcode))   $csvs->and_where('data', 'LIKE', '"exp_barcode";s:'.strlen($exp_barcode).':"'.$exp_barcode.'"');
        if (Valid::is_barcode($wb_barcode))   $csvs->and_where('data', 'LIKE', '"wb_barcode";s:'.strlen($wb_barcode).':"'.$wb_barcode.'"');
      }

      if ($csvs) {
        $clone = clone($csvs);
        $pagination = Pagination::factory(array(
          'items_per_page' => 50,
          'total_items' => $total_items = $clone->find_all()->count()));

        $csvs = $csvs
          ->offset($pagination->offset)
          ->limit($pagination->items_per_page)
          ->find_all()
          ->as_array();

        if ($pagination->total_items == 1) Notify::msg($pagination->total_items.' record found');
        elseif ($pagination->total_items) Notify::msg($pagination->total_items.' records found');
        else Notify::msg('No records found');
      }
    }

    if ($csvs) {
      if (!$site)     $site     = ORM::factory('site', (int) $site_id);
      if (!$block)    $block    = ORM::factory('block', (int) $block_id);
      if (!$operator) $operator = ORM::factory('operator', (int) $operator_id);

      $table = View::factory('csvs')
        ->set('classes', array('has-pagination'))
        ->set('csvs', $csvs)
        ->set('fields', SGS_Form_ORM::get_fields($form_type))
        ->set('total_items', $total_items)
        ->set('operator', $operator->loaded() ? $operator : NULL)
        ->set('site', $site->loaded() ? $site : NULL)
        ->set('block', $block->loaded() ? $block : NULL)
        ->render();
    }

    if ($form) $content .= $form->render();
    $content .= $table;
    $content .= $pagination;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  public function action_index() {
    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  public function action_search() {
    if (!Request::$current->query()) Session::instance()->delete('pagination.csv.search');

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
      ->add('search', 'input', array('label' => 'Keywords', 'rules' => array(array('min_length', array(':value', 3)))))
      ->add_group('operator_id', 'select', $operator_ids, NULL, array('label' => 'Operator', 'attr' => array('class' => 'site_operatoropts')))
      ->add_group('site_id', 'select', $site_ids, NULL, array('label' => 'Site', 'attr' => array('class' => 'siteopts')))
      ->add_group('block_id', 'select', array(), NULL, array('label' => 'Block', 'attr' => array('class' => 'blockopts')))
      ->add('submit', 'submit', 'Search')
      ->add_group('form_type', 'select', SGS::$form_data_type, NULL, array('label' => 'Form', 'required' => TRUE))
      ->add_group('status', 'checkboxes', SGS::$csv_status, NULL, array('label' => 'Status'));

    if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
      Session::instance()->delete('pagination.csv.search');

      $search      = explode(' ', $form->search->val());
      $form_type   = $form->form_type->val();
      $operator_id = $form->operator_id->val();
      $site_id     = $form->site_id->val();
      $block_id    = $form->block_id->val();
      $status      = $form->status->val();

      Session::instance()->set('pagination.csv.search', array(
        'search'      => $search,
        'form_type'   => $form_type,
        'operator_id' => $operator_id,
        'site_id'     => $site_id,
        'block_id'    => $block_id,
        'status'      => $status,
      ));
    }
    elseif ($settings = Session::instance()->get('pagination.csv.search')) {
      $form->search->val($search = $settings['search']);
      $form->form_type->val($form_type = $settings['form_type']);
      $form->operator_id->val($operator_id = $settings['operator_id']);
      $form->site_id->val($site_id = $settings['site_id']);
      $form->block_id->val($block_id = $settings['block_id']);
      $form->status->val($status = $settings['status']);
    }

    if ($search) {
      $csvs = ORM::factory('csv')
        ->where('operation', '=', 'U')
        ->and_where('form_type', 'IN', array_keys(SGS::$form_data_type))
        ->order_by('timestamp', 'DESC');

      foreach ($search as $keyword) $csvs->and_where('values', 'ILIKE', '%'.trim($keyword).'%');

      if ($form_type)   $csvs->and_where('form_type', 'IN', (array) $form_type);
      if ($operator_id) $csvs->and_where('operator_id', 'IN', (array) $operator_id);
      if ($site_id)     $csvs->and_where('site_id', 'IN', (array) $site_id);
      if ($block_id)    $csvs->and_where('block_id', 'IN', (array) $block_id);
      if ($status)      $csvs->and_where('status', 'IN', (array) $status);

      $clone = clone($csvs);
      $pagination = Pagination::factory(array(
        'items_per_page' => 50,
        'total_items' => $total_items = $clone->find_all()->count()));

      $csvs = $csvs
        ->offset($pagination->offset)
        ->limit($pagination->items_per_page)
        ->find_all()
        ->as_array();

      if ($pagination->total_items == 1) Notify::msg($pagination->total_items.' record found');
      elseif ($pagination->total_items) Notify::msg($pagination->total_items.' records found');
      else Notify::msg('No records found');

      $table = View::factory('csvs')
        ->set('classes', array('has-pagination'))
        ->set('csvs', $csvs)
        ->set('fields', SGS_Form_ORM::get_fields($form_type))
        ->set('total_items', $total_items)
        ->set('operator', $operator_id ? ORM::factory('operator', $operator_id) : NULL)
        ->set('site', $site_id ? ORM::factory('site', $site_id) : NULL)
        ->set('block', $block_id ? ORM::factory('block', $block_id) : NULL)
        ->render();
    }

    if ($form) $content .= $form->render();
    $content .= $table;
    $content .= $pagination;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  public function action_upload() {
    set_time_limit(600);
    $form = Formo::form()
      ->add('upload[]', 'file', array(
        'label' => 'File',
        'required' => TRUE,
        'attr'  => array('multiple' => 'multiple')
      ))
      ->add('submit', 'submit', 'Upload');

    if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
      $csv_error   = 0;
      $csv_success = 0;
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
        $ext  = $info['extension'];

        switch ($ext) {
          case 'csv':  $reader = new PHPExcel_Reader_CSV; break;
          case 'xls':  $reader = new PHPExcel_Reader_Excel5; break;
          case 'xlsx': $reader = new PHPExcel_Reader_Excel2007; break;
          default:
            if (array_filter($info)) Notify::msg('Sorry, uploaded file not supported. Please try again. If you continue to receive this error, ensure that the uploaded file is a CSV or Excel document.', 'error');
            else Notify::msg('Sorry, no upload found or there is an error in the system. Please try again.', 'error');
        }

        if ($reader) {
          try {
            if (!$reader->canRead($upload['tmp_name'])) {
              $reader = PHPExcel_IOFactory::createReaderForFile($upload['tmp_name']);
            }

            if ($reader instanceof PHPExcel_Reader_IReader) {
              $excel = $reader->load($upload['tmp_name'])->setActiveSheetIndex(0)->toArray(NULL, TRUE, TRUE, TRUE);
            }
          } catch (Exception $e) {
            Notify::msg('Sorry, upload processing failed. Please try again. If you continue to receive this error, ensure that the uploaded file contains no formulas or macros.', 'error');
          }

          if     (strpos(strtoupper($excel[1][D]), 'STOCK SURVEY FORM') !== FALSE) $form_type = 'SSF';
          elseif (strpos(strtoupper($excel[1][C]), 'TREE FELLING')      !== FALSE) $form_type = 'TDF';
          elseif (strpos(strtoupper($excel[1][C]), 'TREE DATA FORM')    !== FALSE) $form_type = 'TDF';
          elseif (strpos(strtoupper($excel[1][C]), 'LOG DATA FORM')     !== FALSE) $form_type = 'LDF';
          elseif (strpos(strtoupper($excel[1][A]), 'SHIPMENT SPECIFICATION') !== FALSE) $form_type = 'SPECS';
          elseif (strpos(strtoupper($excel[1][A]), 'LOG WAYBILL') !== FALSE) $form_type = 'WB';
          else   Notify::msg('Sorry, the form type cannot be determined from the uploaded file. Please check the form title for errors and try again.', 'error', TRUE);

          if ($form_type) {
            $form_model = ORM::factory($form_type);

            // detect file properties
            $start = constant('Model_'.$form_type.'::PARSE_START');
            $properties = $form_model->parse_csv($excel[$start], $excel);

            // upload file
            $file = ORM::factory('file');
            $file->name = $upload['name'];
            $file->type = $upload['type'];
            $file->size = $upload['size'];
            $file->operation      = 'U';
            $file->operation_type = $form_type;
            $file->content_md5    = md5_file($upload['tmp_name']);

            if (isset($properties['operator_tin'])) $file->operator = SGS::lookup_operator($properties['operator_tin']);
            if (isset($properties['site_name']))    $file->site = SGS::lookup_site($properties['site_name']);
            if (isset($properties['block_name']))   $file->block = SGS::lookup_block($properties['site_name'], $properties['block_name']);
            if (isset($properties['create_date']))  $create_date = $properties['create_date'];

            try {
              $file->save();

              $tempname = $upload['tmp_name'];
              switch ($file->operation_type) {
                case 'SSF':
                  $newdir = implode(DIRECTORY_SEPARATOR, array(
                    'uploads',
                    $file->site->name,
                    $file->operation_type,
                    $file->block->name
                  ));
                  if (!($file->operator->name and $file->site->name and $file->block->name and $file->operation_type)) {
                    Notify::msg('Sorry, cannot identify required properties of the file '.$file->name.'.', 'error', TRUE);
                    throw new Exception();
                  }
                  if (!($file->operator->id == $file->site->operator_id)) {
                    Notify::msg('Sorry, site operator does not match operator in properties of the file '.$file->name.'.', 'error', TRUE);
                    throw new Exception();
                  }
                  if (!($file->site->id == $file->block->site_id)) {
                    Notify::msg('Sorry, block site does not match site in properties of the file '.$file->name.'.', 'error', TRUE);
                    throw new Exception();
                  }
                  $newname = SGS::wordify($file->site->name.'_SSF_'.$file->block->name).'.'.$ext;
                  break;

                case 'TDF':
                  $newdir = implode(DIRECTORY_SEPARATOR, array(
                    'uploads',
                    $file->site->name,
                    $file->operation_type,
                    $file->block->name
                  ));
                  if (!($file->operator->name and $file->site->name and $file->block->name and $file->operation_type)) {
                    Notify::msg('Sorry, cannot identify required properties of the file '.$file->name.'.', 'error', TRUE);
                    throw new Exception();
                  }
                  if (!($file->operator->id == $file->site->operator_id)) {
                    Notify::msg('Sorry, site operator does not match operator in properties of the file '.$file->name.'.', 'error', TRUE);
                    throw new Exception();
                  }
                  if (!($file->site->id == $file->block->site_id)) {
                    Notify::msg('Sorry, block site does not match site in properties of the file '.$file->name.'.', 'error', TRUE);
                    throw new Exception();
                  }
                  $newname = SGS::wordify($file->site->name.'_TDF_'.$file->block->name.'_'.SGS::date($create_date, 'm_d_Y')).'.'.$ext;
                  break;

                case 'LDF':
                  $newdir = implode(DIRECTORY_SEPARATOR, array(
                    'uploads',
                    $file->site->name,
                    $file->operation_type
                  ));
                  if (!($file->operator->name and $file->site->name and $file->operation_type)) {
                    Notify::msg('Sorry, cannot identify required properties of the file '.$file->name.'.', 'error', TRUE);
                    throw new Exception();
                  }
                  if (!($file->operator->id == $file->site->operator_id)) {
                    Notify::msg('Sorry, site operator does not match operator in properties of the file '.$file->name.'.', 'error', TRUE);
                    throw new Exception();
                  }
                  $newname = SGS::wordify($file->site->name.'_LDF_'.SGS::date($create_date, 'm_d_Y')).'.'.$ext;
                  break;

                case 'SPECS':
                  $newdir = implode(DIRECTORY_SEPARATOR, array(
                    'specs',
                    $file->operator->tin
                  ));
                  if (!($file->operator->name and $file->operation_type)) {
                    Notify::msg('Sorry, cannot identify required properties of the file '.$file->name.'.', 'error', TRUE);
                    throw new Exception();
                  }
                  $newname = SGS::wordify('SPECS_'.$file->operator->name.'_'.SGS::date($create_date, 'm_d_Y')).'.'.$ext;
                  break;

                case 'WB':
                  $newdir = implode(DIRECTORY_SEPARATOR, array(
                    'wb',
                    $file->operator->tin
                  ));
                  if (!($file->operator->name and $file->operation_type)) {
                    Notify::msg('Sorry, cannot identify required properties of the file '.$file->name.'.', 'error', TRUE);
                    throw new Exception();
                  }
                  $newname = SGS::wordify('WB_'.$file->operator->name.'_'.SGS::date($create_date, 'm_d_Y')).'.'.$ext;
                  break;
              }

              if ($newname !== $file->name) {
                $name_changed = TRUE;
                $name_changed_properties = TRUE;
              }

              $version = 0;
              $testname = $newname;
              while (file_exists(DOCPATH.$newdir.DIRECTORY_SEPARATOR.$newname)) {
                $newname = substr($testname, 0, strrpos($testname, '.'.$ext)).'_'.($version++).'.'.$ext;
                $name_changed = TRUE;
                $name_changed_duplicate = TRUE;
              }

              if ($name_changed) {
                $msg = 'Uploaded file name has been changed from "'.$file->name.'" to "'.$newname.'"';
                $due = array();
                if ($name_changed_duplicate) $due[] = 'an already existing file with the same name';
                if ($name_changed_properties) $due[] = 'detected properties of the file';
                if ($due) $msg .= ' due to '.implode(' and ', $due);
                $msg .= '.';
                Notify::msg($msg, 'warning', TRUE);
              }

              if (!is_dir(DOCPATH.$newdir) and !mkdir(DOCPATH.$newdir, 0777, TRUE)) {
                Notify::msg('Sorry, cannot access documents folder. Check file access capabilities with the site administrator and try again.', 'error', TRUE);
                throw new Exeption();
              }
              else if (!(rename($tempname, DOCPATH.$newdir.DIRECTORY_SEPARATOR.$newname) and chmod(DOCPATH.$newdir.DIRECTORY_SEPARATOR.$newname, 0777))) {
                Notify::msg('Sorry, cannot create document. Check file operation capabilities with the site administrator and try again.', 'error', TRUE);
                throw new Exception();
              }

              $file->path = DIRECTORY_SEPARATOR.str_replace(DOCROOT, '', DOCPATH).$newdir.DIRECTORY_SEPARATOR.$newname;

              try {
                $file->save();
                Notify::msg($file->name.' successfully uploaded.', 'success', TRUE);
              } catch (ORM_Validation_Exception $e) {
                foreach ($e->errors('') as $err) Notify::msg(SGS::errorify($err).' ('.$file->name.')', 'error', TRUE);
              }

            } catch (ORM_Validation_Exception $e) {
                foreach ($e->errors('') as $err) Notify::msg(SGS::errorify($err).' ('.$file->name.')', 'error', TRUE);
            } catch (Exception $e) {
              try {
                $file->delete();
              } catch (Exception $f) {
                Notify::msg('Sorry, attempting to delete an non-existing file failed.', 'warning', TRUE);
              }
              Notify::msg('Sorry, unable to save uploaded file.', 'error', TRUE);
            }

            if ($file->id) {
              // parse csv
              for ($i = $start; $i <= count($excel); $i++) {
                $row = $excel[$i];
                if ( ! $data = $form_model->parse_csv($row, $excel)) continue;

                // save CSV
                $csv = ORM::factory('csv');
                $csv->file_id     = $file->id;
                $csv->operation   = 'U';
                $csv->form_type   = $form_type;
                $csv->values      = $data;
                try {
                  $csv->save();
                  $csv_success++;
                } catch (Exception $e) {
                  $csv_error++;
                }
                unset($csv);
              }
            }
          }
        }
      }
      if ($csv_success) Notify::msg($csv_success.' records successfully parsed.', 'success', TRUE);
      if ($csv_serror) Notify::msg($csv_error.' records failed to be parsed.', 'error', TRUE);

      $this->request->redirect('declaration/files');
    }

    $content .= $form->render();

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  private function handle_csv_revisions($id) {
    $csv      = ORM::factory('CSV', $id);
    $revisions = $csv->get_revisions();

    $table = View::factory('csvs')
      ->set('csvs', array($csv))
      ->set('fields', SGS_Form_ORM::get_fields($csv->form_type))
      ->set('operator', $csv->operator->loaded() ? $csv->operator : NULL)
      ->set('site', $csv->site->loaded() ? $csv->site : NULL)
      ->set('block', $csv->block->loaded() ? $csv->block : NULL)
      ->set('options', array('header' => TRUE))
      ->render();

    if ($revisions) $table .= View::factory('csvs')
      ->set('classes', array('has-section'))
      ->set('csvs', $revisions)
      ->set('fields', SGS_Form_ORM::get_fields($csv->form_type))
      ->set('operator', $csv->operator->loaded() ? $csv->operator : NULL)
      ->set('site', $csv->site->loaded() ? $csv->site : NULL)
      ->set('block', $csv->block->loaded() ? $csv->block : NULL)
      ->set('options', array('header' => FALSE, 'hide_header_info' => TRUE, 'hide_upload_info' => FALSE, 'links' => FALSE, 'dropdown' => FALSE))
      ->render();

    $content .= $table;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  public function action_files() {
    $id      = $this->request->param('id');
    $command = $this->request->param('command');

    if (!$command && !is_numeric($id)) {
      $command = $id;
      $id      = NULL;
    }

    switch ($command) {
      case 'review': return self::handle_file_review($id);
      case 'delete': return self::handle_file_delete($id);
      case 'process': return self::handle_file_process($id);
      case 'download': return self::handle_file_download($id);

      default:
      case 'list': return self::handle_file_list($id);
    }
  }

  public function action_data() {
    $id      = $this->request->param('id');
    $command = $this->request->param('command');

    if (!$command && !is_numeric($id)) {
      $command = $id;
      $id      = NULL;
    }

    switch ($command) {
      case 'edit': return self::handle_csv_edit($id);
      case 'delete': return self::handle_csv_delete($id);
      case 'process': return self::handle_csv_process($id);
      case 'revisions': return self::handle_csv_revisions($id);

      case 'ssf': return self::handle_csv_list('SSF');
      case 'tdf': return self::handle_csv_list('TDF');
      case 'ldf': return self::handle_csv_list('LDF');
      case 'specs': return self::handle_csv_list('SPECS');

      default:
      case 'list': return self::handle_csv_list(NULL, $id);
    }
  }

}

