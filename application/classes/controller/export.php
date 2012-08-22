<?php

class Controller_Export extends Controller {

  private function handle_file_list() {
    $files = ORM::factory('file')
      ->where('operation', '=', 'E')
      ->order_by('timestamp', 'desc')
      ->find_all()
      ->as_array();

    if ($files) $content .= View::factory('files')
      ->set('mode', 'export')
      ->set('files', $files)
      ->render();
    else Notify::msg('No files found.');

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  private function handle_file_review($id = NULL) {
    if (!$id) $id = $this->request->param('id');

    $file = ORM::factory('file', $id);

    $csvs = $file->csv
      ->where('status', '=', 'P')
      ->find_all()
      ->as_array();

    if ($csvs) $table .= View::factory('csvs')
      ->set('title', 'Pending')
      ->set('csvs', $csvs)
      ->set('fields', SGS_Form_ORM::get_fields($file->operation_type, TRUE))
      ->render();
    else Notify::msg('No records found.');

    $content .= $table;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  private function handle_csv_list($id = NULL, $form_type = NULL, $range = array()) {
    if ($id) {
      $csvs = ORM::factory('csv')
        ->where('operation', '=', 'E')
        ->and_where('id', '=', $id)
        ->find_all()
        ->as_array();

      $form_type = reset($csvs)->form_type;
      $display = TRUE;
    }
    else {
      $form = Formo::form()
        ->add_group('form_type', 'select', SGS::$form_type, NULL, array(
          'label' => 'Data',
          'required' => TRUE
        ))
        ->add('from', 'input', array(
          'label' => 'From'
        ))
        ->add('to', 'input', array(
          'label' => 'To'
        ))
        ->add('search', 'submit', 'Search');

      if ($form->sent($_POST) and $form->load($_POST)->validate()) {
        $form_type = $form->form_type->val();
        $from      = $form->from->val();
        $to        = $form->to->val();


        $csvs = ORM::factory('csv')
          ->where('operation', '=', 'E')
          ->and_where('form_type', '=', $form_type)
          ->and_where('timestamp', 'BETWEEN', SGS::db_range($from, $to))
          ->order_by('timestamp', 'desc')
          ->find_all()
          ->as_array();

        $display = TRUE;
      }
    }

    if ($csvs) $table = View::factory('csvs')
      ->set('mode', 'import')
      ->set('csvs', $csvs)
      ->set('fields', SGS_Form_ORM::get_fields($form_type, TRUE))
      ->render();
    elseif ($display) Notify::msg('Search returned an empty set of results.');

    if ($form) $content .= $form->render();
    $content .= $table;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  private function handle_download_ssf() {
    $sites = ORM::factory('site')
      ->find_all()
      ->as_array();

    foreach ($sites as $site) {
      foreach ($site->blocks->find_all()->as_array() as $block) {
        $block_options[$site->name.' ('.$site->operator->name.')'][$block->id] = $block->name;
      }
    }

    $form = Formo::form()
      ->add('from', 'input', array('label' => 'From'))
      ->add('to', 'input', array('label' => 'To'))
      ->add('block_id', 'select', array(
        'options' => $block_options,
        'label'   => 'Block'
      ))
      ->add('type', 'radios', array(
        'options' => array(
          'xls' => 'Excel Spreadsheet',
          'csv' => 'CSV Document'
        ),
        'label' => 'Document Type'
      ))
      ->add('download', 'submit', array('label' => 'Download'));

    if ($form->sent($_POST) and $form->load($_POST)->validate()) {
      $block    = ORM::factory('block', $form->block_id->val());
      $site     = $block->site;
      $operator = $site->operator;
      $type     = $form->type->val();
      $from     = $form->from->val();
      $to       = $form->to->val();

      $ssf_data = ORM::factory('ssf')
        ->where('block_id', '=', $block->id)
        ->where('create_date', 'BETWEEN', SGS::db_range($from, $to))
        ->find_all()
        ->as_array();

      switch ($type) {
        case 'csv':
          $excel = new PHPExcel();
          $excel->setActiveSheetIndex(0);
          $writer = new PHPExcel_Writer_CSV($excel);
          $headers = TRUE;
          break;
        case 'xls':
          $filename = APPPATH.'/templates/SSF.xls';
          try {
            $reader = new PHPExcel_Reader_Excel5;
            if (!$reader->canRead($filename)) $reader = PHPExcel_IOFactory::createReaderForFile($filename);
            $excel = $reader->load($filename);
          } catch (Exception $e) {
            Notify::msg('Unable to load Excel document template. Please try again.', 'error', TRUE);
          }
          $excel->setActiveSheetIndex(0);
          $writer = new PHPExcel_Writer_Excel2007($excel);
        $headers = FALSE;
          break;
      }

      if ($excel) {
        // data
        $create_date = 0;
        $row = Model_SSF::PARSE_START;
        foreach ($ssf_data as $ssf) {
          $ssf->export_data($excel, $row);
          if (strtotime($ssf->create_date) > strtotime($create_date)) $create_date = $ssf->create_date;
          $row++;
        }

        // headers
        $ssf->export_headers($excel, array(
          'create_date' => $create_date ? $create_date : $create_date = SGS::date ('now', SGS::PGSQL_DATE_FORMAT)
        ), $headers);

        // file
        $tempname  = tempnam(sys_get_temp_dir(), 'ssf_').'.'.$type;
        $fullname  = $site->name.'_SSF_'.$block->name.'.'.$type;
        $writer->save($tempname);

        $this->response->send_file($tempname, $fullname, array('delete' => TRUE));
      }
    }

    $content .= $form->render();

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  private function handle_download_tdf() {

    $sites = ORM::factory('site')
      ->find_all()
      ->as_array();

    foreach ($sites as $site) {
      foreach ($site->blocks->find_all()->as_array() as $block) {
        $block_options[$site->name.' ('.$site->operator->name.')'][$block->id] = $block->name;
      }
    }

    $form = Formo::form()
      ->add('from', 'input', array('label' => 'From'))
      ->add('to', 'input', array('label' => 'To'))
      ->add('block_id', 'select', array(
        'options' => $block_options,
        'label'   => 'Block'
      ))
      ->add('type', 'radios', array(
        'options' => array(
          'xls' => 'Excel Spreadsheet',
          'csv' => 'CSV Document'
        ),
        'label' => 'Document Type'
      ))
      ->add('download', 'submit', array('label' => 'Download'));

    if ($form->sent($_POST) and $form->load($_POST)->validate()) {
      $block    = ORM::factory('block', $form->block_id->val());
      $site     = $block->site;
      $operator = $site->operator;
      $type     = $form->type->val();
      $from     = $form->from->val();
      $to       = $form->to->val();

      $tdf_data = ORM::factory('tdf')
        ->where('block_id', '=', $block->id)
        ->where('create_date', 'BETWEEN', SGS::db_range($from, $to))
        ->find_all()
        ->as_array();

      switch ($type) {
        case 'csv':
          $excel = new PHPExcel();
          $excel->setActiveSheetIndex(0);
          $headers = TRUE;
          break;
        case 'xls':
          $filename = APPPATH.'/templates/SSF.xls';
          try {
            $reader = new PHPExcel_Reader_Excel5;
            if (!$reader->canRead($filename)) $reader = PHPExcel_IOFactory::createReaderForFile($filename);
            $excel = $reader->load($filename);
          } catch (Exception $e) {
            Notify::msg('Unable to load Excel document template. Please try again.', 'error', TRUE);
          }
          $excel->setActiveSheetIndex(0);
          $headers = FALSE;
          break;
      }

      if ($excel) {
        // data
        $create_date = 0;
        $row = Model_TDF::PARSE_START;
        foreach ($tdf_data as $tdf) {
          $tdf->export_data($excel, $row);
          if (strtotime($tdf->create_date) > strtotime($create_date)) $create_date = $tdf->create_date;
          $row++;
        }

        // headers
        $tdf->export_headers($excel, array(
          'create_date' => $create_date ? $create_date : $create_date = SGS::date ('now', SGS::PGSQL_DATE_FORMAT)
        ), $headers);

        // file
        $tempname  = tempnam(sys_get_temp_dir(), 'tdf_').'.'.$type;
        $fullname  = $site->name.'_TDF_'.$block->name.'_'.Date::formatted_time('now', 'Y_m_d').'.'.$type;
        $objWriter = new PHPExcel_Writer_CSV($excel);
        $objWriter->save($tempname);

        $this->response->send_file($tempname, $fullname, array('delete' => TRUE));
      }
    }

    $content .= $form->render();

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  private function handle_download_ldf() {
    $sites = ORM::factory('site')
      ->find_all()
      ->as_array();

    foreach ($sites as $site) {
      $site_options[$site->id] = $site->name.' ('.$site->operator->name.')';
    }

    $form = Formo::form()
      ->add('from', 'input', array('label' => 'From'))
      ->add('to', 'input', array('label' => 'To'))
      ->add('site_id', 'select', array(
        'options' => $site_options,
        'label'   => 'Site'
      ))
      ->add('type', 'radios', array(
        'options' => array(
          'xls' => 'Excel Spreadsheet',
          'csv' => 'CSV Document'
        ),
        'label' => 'Document Type'
      ))
      ->add('download', 'submit', array('label' => 'Download'));

    if ($form->sent($_POST) and $form->load($_POST)->validate()) {
      $site     = ORM::factory('site', $form->site_id->val());
      $operator = $site->operator;
      $type     = $form->type->val();
      $from     = $form->from->val();
      $to       = $form->to->val();

      $ldf_data = ORM::factory('ldf')
        ->where('site_id', '=', $site->id)
        ->where('create_date', 'BETWEEN', SGS::db_range($from, $to))
        ->find_all()
        ->as_array();

      switch ($type) {
        case 'csv':
          $excel = new PHPExcel();
          $excel->setActiveSheetIndex(0);
          $headers = TRUE;
          break;
        case 'xls':
          $filename = APPPATH.'/templates/SSF.xls';
          try {
            $reader = new PHPExcel_Reader_Excel5;
            if (!$reader->canRead($filename)) $reader = PHPExcel_IOFactory::createReaderForFile($filename);
            $excel = $reader->load($filename);
          } catch (Exception $e) {
            Notify::msg('Unable to load Excel document template. Please try again.', 'error', TRUE);
          }
          $excel->setActiveSheetIndex(0);
          $headers = FALSE;
          break;
      }

      if ($excel) {
        // data
        $create_date = 0;
        $row = Model_LDF::PARSE_START;
        foreach ($ldf_data as $ldf) {
          $ldf->export_data($excel, $row);
          if (strtotime($ldf->create_date) > strtotime($create_date)) $create_date = $ldf->create_date;
          $row++;
        }

        // headers
        $ldf->export_headers($excel, array(
          'create_date' => $create_date ? $create_date : $create_date = SGS::date ('now', SGS::PGSQL_DATE_FORMAT)
        ), $headers);

        // file
        $tempname  = tempnam(sys_get_temp_dir(), 'ldf_').'.csv';
        $fullname  = $site->name.'_LDF_'.Date::formatted_time('now', 'Y_m_d').'.csv';
        $objWriter = new PHPExcel_Writer_CSV($excel);
        $objWriter->save($tempname);

        $this->response->send_file($tempname, $fullname, array('delete' => TRUE));
      }
    }

    $content .= $form->render();

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  public function action_index() {
    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  public function action_files() {
    $id      = $this->request->param('id');
    $command = $this->request->param('command');

    if (!$command && !is_numeric($id)) {
      $id      = NULL;
      $command = $id;
    }

    switch ($command) {
      case 'review': return self::handle_file_review($id);

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

      default:
      case 'list': return self::handle_csv_list($id);
    }
  }

  public function action_download() {
    $id      = $this->request->param('id');
    $command = $this->request->param('command');

    if (!$command && !is_numeric($id)) {
      $command = $id;
      $id      = NULL;
    }

    set_time_limit(0);
    switch ($command) {
      case 'ssf': return self::handle_download_ssf();
      case 'tdf': return self::handle_download_tdf();
      case 'ldf': return self::handle_download_ldf();
    }

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

}