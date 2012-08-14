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
        $block_options[$site->name.' ('.$site->operator->name.')'][$block->id] = $block->coordinates;
      }
    }

    $form = Formo::form()
      ->add('from', 'input', array('label' => 'From'))
      ->add('to', 'input', array('label' => 'To'))
      ->add('block_id', 'select', array(
        'options' => $block_options,
        'label'   => 'Block'
      ))
      ->add('download', 'submit', array('label' => 'Download'));

    if ($form->sent($_POST) and $form->load($_POST)->validate()) {
      $block    = ORM::factory('block', $form->block_id->val());
      $site     = $block->site;
      $operator = $site->operator;
      $from     = $form->from->val();
      $to       = $form->to->val();

      $ssf_data = ORM::factory('ssf')
        ->where('block_id', '=', $block->id)
        ->where('create_date', 'BETWEEN', SGS::db_range($from, $to))
        ->find_all()
        ->as_array();

      $excel = new PHPExcel();
      $excel->setActiveSheetIndex(0);

      // headers
      $excel->getActiveSheet()->SetCellValue('D1', 'STOCK SURVEY FORM');
      $excel->getActiveSheet()->SetCellValue('J1', 'SOP7-4'); // don't know what this is for
      $excel->getActiveSheet()->SetCellValue('A2', 'Site type and Reference:');
      $excel->getActiveSheet()->SetCellValue('B2', $site->name.'/'.$block->coordinates);
      $excel->getActiveSheet()->SetCellValue('G2', 'Holder TIN:');
      $excel->getActiveSheet()->SetCellValue('H2', $operator->tin);
      $excel->getActiveSheet()->SetCellValue('A3', 'Date Surveyed:');
      $excel->getActiveSheet()->SetCellValue('B3', $create_date);
      $excel->getActiveSheet()->SetCellValue('G3', 'Enumerator:');
      $excel->getActiveSheet()->SetCellValue('H3', ''); // enumerator
      $excel->getActiveSheet()->SetCellValue('A4', 'UTM Coordinates of the 4 corners of the block map:');
      $excel->getActiveSheet()->SetCellValue('E4', 'Easting');
      $excel->getActiveSheet()->SetCellValue('G4', 'Northing');
      $excel->getActiveSheet()->SetCellValue('A5', 'Origin (0 meter  0 meter)');
      $excel->getActiveSheet()->SetCellValue('B5', ''); // origin
      $excel->getActiveSheet()->SetCellValue('A6', 'East from origin');
      $excel->getActiveSheet()->SetCellValue('B6', ''); // east from origin
      $excel->getActiveSheet()->SetCellValue('A7', 'North/South from previous');
      $excel->getActiveSheet()->SetCellValue('B7', ''); // north/south from previous
      $excel->getActiveSheet()->SetCellValue('A8', 'West from previous');
      $excel->getActiveSheet()->SetCellValue('B8', ''); // west from previous
      $excel->getActiveSheet()->SetCellValue('A9', 'Date entered');
      $excel->getActiveSheet()->SetCellValue('B9', ''); // date entered
      $excel->getActiveSheet()->SetCellValue('E9', 'Entered by');
      $excel->getActiveSheet()->SetCellValue('H9', ''); // entered by
      $excel->getActiveSheet()->SetCellValue('A10', 'Date checked');
      $excel->getActiveSheet()->SetCellValue('B10', ''); // date checked
      $excel->getActiveSheet()->SetCellValue('E10', 'Checked by');
      $excel->getActiveSheet()->SetCellValue('F10', ''); // checked by
      $excel->getActiveSheet()->SetCellValue('A11', 'Tree ID Number');
      $excel->getActiveSheet()->SetCellValue('B11', 'Tree Map Number');
      $excel->getActiveSheet()->SetCellValue('C11', 'Cell Reference');
      $excel->getActiveSheet()->SetCellValue('E11', 'Species Code');
      $excel->getActiveSheet()->SetCellValue('F11', 'Diameter Class Number (cm)');
      $excel->getActiveSheet()->SetCellValue('G11', "Height \n\r(m)");
      $excel->getActiveSheet()->SetCellValue('H11', 'Crop Trees');
      $excel->getActiveSheet()->SetCellValue('J11', 'FDA Remarks/Reason for Rejection');
      $excel->getActiveSheet()->SetCellValue('C12', "Survey Line \n\rNumber");
      $excel->getActiveSheet()->SetCellValue('D12', 'Cell ID Number');
      $excel->getActiveSheet()->SetCellValue('H12', 'Requested');
      $excel->getActiveSheet()->SetCellValue('I12', 'FDA Approved');
      $excel->getActiveSheet()->SetCellValue('L12', "Barcode \n\rCheck");
      $excel->getActiveSheet()->SetCellValue('M12', "UPPER \n\rSPECIES");

      // data
      $count = Model_SSF::PARSE_START;
      foreach ($ssf_data as $ssf) {
        $excel->getActiveSheet()->SetCellValue('A'.$count, $ssf->barcode->barcode);
        $excel->getActiveSheet()->SetCellValue('B'.$count, $ssf->tree_map_number);
        $excel->getActiveSheet()->SetCellValue('C'.$count, $ssf->survey_line);
        $excel->getActiveSheet()->SetCellValue('D'.$count, $ssf->cell_number);
        $excel->getActiveSheet()->SetCellValue('E'.$count, $ssf->species->code);
        $excel->getActiveSheet()->SetCellValue('F'.$count, $ssf->diameter);
        $excel->getActiveSheet()->SetCellValue('G'.$count, $ssf->height);
        $excel->getActiveSheet()->SetCellValue('H'.$count, $ssf->is_requested == FALSE ? 'NO' : 'YES');
        $excel->getActiveSheet()->SetCellValue('I'.$count, $ssf->is_fda_approved == FALSE ? 'NO' : 'YES');
        $excel->getActiveSheet()->SetCellValue('J'.$count, $ssf->fda_remarks);
        $count++;
      }

      $tempname  = tempnam(sys_get_temp_dir(), 'ssf_').'.csv';
      $fullname  = str_replace('/','_',$site->name).'_'.$block->coordinates.'_'.Date::formatted_time('now', 'Ymd_Hi').'.csv';
      $objWriter = new PHPExcel_Writer_CSV($excel);
      $objWriter->save($tempname);

      $this->response->send_file($tempname, $fullname, array('delete' => TRUE));
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
        $block_options[$site->name.' ('.$site->operator->name.')'][$block->id] = $block->coordinates;
      }
    }

    $form = Formo::form()
      ->add('from', 'input', array('label' => 'From'))
      ->add('to', 'input', array('label' => 'To'))
      ->add('block_id', 'select', array(
        'options' => $block_options,
        'label'   => 'Block'
      ))
      ->add('download', 'submit', array('label' => 'Download'));

    if ($form->sent($_POST) and $form->load($_POST)->validate()) {
      $block    = ORM::factory('block', $form->block_id->val());
      $site     = $block->site;
      $operator = $site->operator;
      $from     = $form->from->val();
      $to       = $form->to->val();

      $tdf_data = ORM::factory('tdf')
        ->where('block_id', '=', $block->id)
        ->where('create_date', 'BETWEEN', SGS::db_range($from, $to))
        ->find_all()
        ->as_array();

      $excel = new PHPExcel();
      $excel->setActiveSheetIndex(0);

      // headers
      $excel->getActiveSheet()->SetCellValue('C1', 'Tree Felling & Stump Registration');
      $excel->getActiveSheet()->SetCellValue('K1', 'SOP10-5'); // don't know
      $excel->getActiveSheet()->SetCellValue('A2', 'Site type and Reference:');
      $excel->getActiveSheet()->SetCellValue('B2', $site->name);
      $excel->getActiveSheet()->SetCellValue('F2', 'Holder TIN:');
      $excel->getActiveSheet()->SetCellValue('G2', $operator->tin);
      $excel->getActiveSheet()->SetCellValue('A3', 'Date Registered:');
      $excel->getActiveSheet()->SetCellValue('B3', $create_date);
      $excel->getActiveSheet()->SetCellValue('F3', 'Log Measurer:');
      $excel->getActiveSheet()->SetCellValue('G3', ''); // log measurer
      $excel->getActiveSheet()->SetCellValue('F4', 'Signed:');
      $excel->getActiveSheet()->SetCellValue('G4', ''); // signed
      $excel->getActiveSheet()->SetCellValue('A5', 'Date Entered in to CoCIS:');
      $excel->getActiveSheet()->SetCellValue('B5', ''); // date entered into CoCIS
      $excel->getActiveSheet()->SetCellValue('F5', 'Entered By:');
      $excel->getActiveSheet()->SetCellValue('G5', ''); // entered by
      $excel->getActiveSheet()->SetCellValue('A6', 'Block Map Cell');
      $excel->getActiveSheet()->SetCellValue('C6', 'Tree ID No');
      $excel->getActiveSheet()->SetCellValue('D6', 'Species Code');
      $excel->getActiveSheet()->SetCellValue('E6', 'New Long Log Tag No');
      $excel->getActiveSheet()->SetCellValue('F6', 'Diameter (cm underbark to the nearest cm)');
      $excel->getActiveSheet()->SetCellValue('J6', 'Length (m) to the nearest 0.1m');
      $excel->getActiveSheet()->SetCellValue('K6', 'Stump Tag No');
      $excel->getActiveSheet()->SetCellValue('L6', 'Action');
      $excel->getActiveSheet()->SetCellValue('M6', 'Comment');
      $excel->getActiveSheet()->SetCellValue('O6', 'Tree ID No');
      $excel->getActiveSheet()->SetCellValue('P6', 'New Long Log Tag No');
      $excel->getActiveSheet()->SetCellValue('Q6', 'Stump Tag No');
      $excel->getActiveSheet()->SetCellValue('R6', 'Tree ID No  Unique');
      $excel->getActiveSheet()->SetCellValue('S6', 'New Long Log Tag No  Unique');
      $excel->getActiveSheet()->SetCellValue('T6', 'Stump Tag No Unique');
      $excel->getActiveSheet()->SetCellValue('F7', 'Butt end');
      $excel->getActiveSheet()->SetCellValue('H7', 'Top');
      $excel->getActiveSheet()->SetCellValue('A8', " Survey Line \n\rNumber");
      $excel->getActiveSheet()->SetCellValue('B8', 'Distance Number');
      $excel->getActiveSheet()->SetCellValue('F8', 'Max');
      $excel->getActiveSheet()->SetCellValue('G8', 'Min');
      $excel->getActiveSheet()->SetCellValue('H8', 'Max');
      $excel->getActiveSheet()->SetCellValue('I8', 'Min');

      // data
      $count = Model_TDF::PARSE_START;
      foreach ($tdf_data as $tdf) {
        $excel->getActiveSheet()->SetCellValue('A'.$count, $tdf->survey_line);
        $excel->getActiveSheet()->SetCellValue('B'.$count, $tdf->cell_number);
        $excel->getActiveSheet()->SetCellValue('C'.$count, $tdf->tree_barcode->barcode);
        $excel->getActiveSheet()->SetCellValue('D'.$count, $tdf->species->code);
        $excel->getActiveSheet()->SetCellValue('E'.$count, $tdf->barcode->barcode);
        $excel->getActiveSheet()->SetCellValue('F'.$count, $tdf->bottom_max);
        $excel->getActiveSheet()->SetCellValue('G'.$count, $tdf->bottom_min);
        $excel->getActiveSheet()->SetCellValue('H'.$count, $tdf->top_max);
        $excel->getActiveSheet()->SetCellValue('I'.$count, $tdf->top_min);
        $excel->getActiveSheet()->SetCellValue('J'.$count, $tdf->length);
        $excel->getActiveSheet()->SetCellValue('K'.$count, $tdf->stump_barcode->barcode);
        $excel->getActiveSheet()->SetCellValue('L'.$count, $tdf->action);
        $excel->getActiveSheet()->SetCellValue('M'.$count, $tdf->comment);
        $count++;
      }

      $tempname  = tempnam(sys_get_temp_dir(), 'tdf_').'.csv';
      $fullname  = str_replace('/','_',$site->name).'_'.Date::formatted_time('now', 'Ymd_Hi').'.csv';
      $objWriter = new PHPExcel_Writer_CSV($excel);
      $objWriter->save($tempname);

      $this->response->send_file($tempname, $fullname, array('delete' => TRUE));
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
      ->add('download', 'submit', array('label' => 'Download'));

    if ($form->sent($_POST) and $form->load($_POST)->validate()) {
      $site     = ORM::factory('site', $form->site_id->val());
      $operator = $site->operator;
      $from     = $form->from->val();
      $to       = $form->to->val();

      $ldf_data = ORM::factory('ldf')
        ->where('site_id', '=', $site->id)
        ->where('create_date', 'BETWEEN', SGS::db_range($from, $to))
        ->find_all()
        ->as_array();

      $excel = new PHPExcel();
      $excel->setActiveSheetIndex(0);

      // headers
      $excel->getActiveSheet()->SetCellValue('C1', '    LOG DATA FORM');
      $excel->getActiveSheet()->SetCellValue('K1', 'SOP13-6'); // don't know
      $excel->getActiveSheet()->SetCellValue('A2', 'Site type and Reference:');
      $excel->getActiveSheet()->SetCellValue('B2', $site->name);
      $excel->getActiveSheet()->SetCellValue('F2', 'Site Holder Name:');
      $excel->getActiveSheet()->SetCellValue('G2', $operator->name); // site holder name
      $excel->getActiveSheet()->SetCellValue('A3', 'Date Registered:');
      $excel->getActiveSheet()->SetCellValue('B3', $create_date);
      $excel->getActiveSheet()->SetCellValue('F3', 'Form Reference No.:');
      $excel->getActiveSheet()->SetCellValue('G3', ''); // form reference number ?
      $excel->getActiveSheet()->SetCellValue('A4', 'Site TIN:');
      $excel->getActiveSheet()->SetCellValue('B4', $operator->tin);
      $excel->getActiveSheet()->SetCellValue('F4', 'Log Measurer:');
      $excel->getActiveSheet()->SetCellValue('G4', ''); // log measurer
      $excel->getActiveSheet()->SetCellValue('A5', 'Date Entered in to CoCIS:');
      $excel->getActiveSheet()->SetCellValue('B5', ''); // date entered into CoCIS
      $excel->getActiveSheet()->SetCellValue('F5', 'Entered By:');
      $excel->getActiveSheet()->SetCellValue('G5', ''); // entered by
      $excel->getActiveSheet()->SetCellValue('A6', 'Original Log ID #');
      $excel->getActiveSheet()->SetCellValue('B6', 'Species Code');
      $excel->getActiveSheet()->SetCellValue('C6', 'New Cross Cut Log Tag #');
      $excel->getActiveSheet()->SetCellValue('D6', 'Diameter (cm underbark to the nearest cm)');
      $excel->getActiveSheet()->SetCellValue('H6', 'Length (m) to the nearest 0.1m');
      $excel->getActiveSheet()->SetCellValue('I6', 'Volume declared (m3)');
      $excel->getActiveSheet()->SetCellValue('J6', 'Action');
      $excel->getActiveSheet()->SetCellValue('K6', 'Comment');
      $excel->getActiveSheet()->SetCellValue('M6', 'Log 1D #');
      $excel->getActiveSheet()->SetCellValue('N6', 'New Cross Cut Log Tag #');
      $excel->getActiveSheet()->SetCellValue('O6', 'Spec1es Code - Upper Case');
      $excel->getActiveSheet()->SetCellValue('D7', 'Butt end');
      $excel->getActiveSheet()->SetCellValue('F7', 'Top');
      $excel->getActiveSheet()->SetCellValue('D8', 'Max');
      $excel->getActiveSheet()->SetCellValue('E8', 'Min');
      $excel->getActiveSheet()->SetCellValue('F8', 'Max');
      $excel->getActiveSheet()->SetCellValue('G8', 'Min');

      // data
      $count = Model_LDF::PARSE_START;
      foreach ($ldf_data as $ldf) {
        $excel->getActiveSheet()->SetCellValue('A'.$count, $ldf->parent_barcode->barcode);
        $excel->getActiveSheet()->SetCellValue('B'.$count, $ldf->species->code);
        $excel->getActiveSheet()->SetCellValue('C'.$count, $ldf->barcode->barcode);
        $excel->getActiveSheet()->SetCellValue('D'.$count, $ldf->bottom_max);
        $excel->getActiveSheet()->SetCellValue('E'.$count, $ldf->bottom_min);
        $excel->getActiveSheet()->SetCellValue('F'.$count, $ldf->top_max);
        $excel->getActiveSheet()->SetCellValue('G'.$count, $ldf->top_min);
        $excel->getActiveSheet()->SetCellValue('H'.$count, $ldf->length);
        $excel->getActiveSheet()->SetCellValue('I'.$count, $ldf->volume);
        $excel->getActiveSheet()->SetCellValue('J'.$count, $ldf->action);
        $excel->getActiveSheet()->SetCellValue('K'.$count, $ldf->comment);
//        $excel->getActiveSheet()->SetCellValue('L'.$count, $ldf->coc_status);
        $count++;
      }

      $tempname  = tempnam(sys_get_temp_dir(), 'ldf_').'.csv';
      $fullname  = str_replace('/','_',$site->name).'_'.Date::formatted_time('now', 'Ymd_Hi').'.csv';
      $objWriter = new PHPExcel_Writer_CSV($excel);
      $objWriter->save($tempname);

      $this->response->send_file($tempname, $fullname, array('delete' => TRUE));
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

    switch ($command) {
      case 'ssf': return self::handle_download_ssf();
      case 'tdf': return self::handle_download_tdf();
      case 'ldf': return self::handle_download_ldf();
    }

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

}