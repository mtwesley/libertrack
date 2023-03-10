<?php

class Controller_Analysis extends Controller {

  public function before() {
    parent::before();

    if (!Auth::instance()->logged_in()) {
      Notify::msg('Please login.', NULL, TRUE);
      $this->request->redirect('login?destination='.$this->request->uri());
    }
    elseif (!Auth::instance()->logged_in('analysis')) {
      Notify::msg('Access denied. You must have '.SGS::$roles['analysis'].' privileges.', 'locked', TRUE);
      $this->request->redirect();
    }

    Session::instance()->write();
  }

  private function download_checks_report($form_type, $record_ids, $info = array()) {
    if (!$record_ids) {
      Notify::msg('No data found. Unable to generate document.', 'warning');
      return FALSE;
    }

    $passed_records    = array();
    $failed_records    = array();
    $unchecked_records = array();

    foreach ($record_ids as $record_id) {
      $record = ORM::factory($form_type, $record_id);
      if ($record->status == 'A') $passed_records[] = $record;
      else if ($record->status == 'R') $failed_records[] = $record;
      else $unchecked_records[] = $record;
    }

    extract($info);

    $html .= View::factory('reports/checks')
      ->set('form_type', $form_type)
      ->set('report', $report)
      ->set('checks', $checks)
      ->set('operator', $operator)
      ->set('site', $site)
      ->set('block', $block)
      ->set('specs_info', $specs_info)
      ->set('exp_info', $exp_info)
      ->set('wb_info', $wb_info)
      ->set('options', array(
        'info'     => TRUE,
        'summary'  => TRUE,
        'details'  => FALSE,
        'styles'   => TRUE,
        'subtitle' => 'Results Summary'
      ))
      ->render();

    $page_count = 0;
    $page_max   = 4;

    // passed
    $cntr  = 0;
    while ($cntr < count($passed_records)) {
      $max = $page_max;
      $set = array_slice($passed_records, $cntr, $max);
      $html .= View::factory('reports/checks')
        ->set('form_type', $form_type)
        ->set('checks', $checks)
        ->set('data', $set)
        ->set('options', array(
          'info'     => FALSE,
          'details'  => TRUE,
          'styles'   => FALSE,
          'subtitle' => 'Records Where All Checks Passed'
        ))
        ->set('cntr', $cntr)
        ->render();

      $cntr += $max;
    }

    // failed
    $cntr  = 0;
    while ($cntr < count($failed_records)) {
      $max = $page_max;
      $set = array_slice($failed_records, $cntr, $max);
      $html .= View::factory('reports/checks')
        ->set('form_type', $form_type)
        ->set('checks', $checks)
        ->set('data', $set)
        ->set('options', array(
          'info'     => FALSE,
          'details'  => TRUE,
          'styles'   => FALSE,
          'subtitle' => 'Records Where Any Check Failed'
        ))
        ->set('cntr', $cntr)
        ->render();

      $cntr += $max;
    }

    // unchecked
    $cntr  = 0;
    while ($cntr < count($unchecked_records)) {
      $max = $page_max;
      $set = array_slice($unchecked_records, $cntr, $max);
      $html .= View::factory('reports/checks')
        ->set('form_type', $form_type)
        ->set('checks', $checks)
        ->set('data', $set)
        ->set('options', array(
          'info'     => FALSE,
          'details'  => TRUE,
          'styles'   => FALSE,
          'subtitle' => 'Records Not Yet Checked'
        ))
        ->set('cntr', $cntr)
        ->render();

      $cntr += $max;
    }
    
    // generate pdf
    set_time_limit(600);

    // save file
    $ext = 'pdf';
    $newdir = implode(DIRECTORY_SEPARATOR, array(
      'checks',
    ));

    switch ($form_type) {
      case 'SSF':
        $newname = SGS::wordify('SSF_'.$site->name.'_'.($block ? $block->name.'_' : '').SGS::date('now', 'm_d_Y')).'.'.$ext;
        break;

      case 'TDF':
        $newname = SGS::wordify('TDF_'.$site->name.'_'.($block ? $block->name.'_' : '').SGS::date('now', 'm_d_Y')).'.'.$ext;
        break;

      case 'LDF':
        $newname = SGS::wordify('LDF_'.$site->name.'_'.SGS::date('now', 'm_d_Y')).'.'.$ext;
        break;

      case 'SPECS':
        $newname = SGS::wordify('SPECS_'.$operator->name.'_'.SGS::date('now', 'm_d_Y')).'.'.$ext;
        break;
    }

    if ($is_draft) $newname = 'CHECKS_DRAFT_'.$newname;
    else $newname = 'CHECKS_'.$newname;

    $version  = 0;
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
        'orientation' => 'Landscape',
        'disable-smart-shrinking' => TRUE,
        'footer-html' => View::factory('reports/checks')
          ->set('form_type', $form_type)
          ->set('options', array(
            'header' => FALSE,
            'footer' => TRUE,
            'break'  => FALSE))
          ->set('page', $page)
          ->set('page_count', $page_count)
          ->render()
      ));
    } catch (Exception $e) {
      Notify::msg('Sorry, unable to generate check report. If this problem continues, contact the system administrator.', 'error');
      return FALSE;
    }

    try {
      $file = ORM::factory('file');
      $file->name = $newname;
      $file->type = 'application/pdf';
      $file->size = filesize($fullname);
      $file->operation      = 'D';
      $file->operation_type = 'CHECKS';
      $file->content_md5    = md5_file($fullname);
      $file->path = DIRECTORY_SEPARATOR.str_replace(DOCROOT, '', DOCPATH).$newdir.DIRECTORY_SEPARATOR.$newname;

      if ($operator) $file->operator = $operator;
      if ($site) $file->site = $site;
      if ($block) $file->block = $block;

      $file->save();
    } catch (ORM_Validation_Exception $e) {
      foreach ($e->errors('') as $err) Notify::msg(SGS::errorify($err).' ('.$file->name.')', 'error');
      return FALSE;
    }

    Notify::clear();
    $this->response->send_file($fullname);
  }

  private function download_verify_report($form_type, $record_ids, $info = array()) {
    if (!$record_ids) {
      Notify::msg('No data found. Unable to generate document.', 'warning');
      return FALSE;
    }

    $passed_records    = array();
    $failed_records    = array();
    $unchecked_records = array();

    foreach ($record_ids as $record_id) {
      $record = ORM::factory($form_type, $record_id);
      if ($record->status == 'A') $passed_records[] = $record;
      else if ($record->status == 'R') $failed_records[] = $record;
      else $unchecked_records[] = $record;
    }

    extract($info);

    // verification, declaration, variance

    $first  = array();
    $second = array();
    foreach ($checks as $typ => $inf)
      if (in_array($typ, array('verification', 'declaration', 'variance'))) $first[$typ] = $inf;
      else $second[$typ] = $inf;

    $html .= View::factory('reports/verify')
      ->set('form_type', $form_type)
      ->set('report', $report)
      ->set('checks', $first)
      ->set('operator', $operator)
      ->set('site', $site)
      ->set('block', $block)
      ->set('specs_info', $specs_info)
      ->set('exp_info', $exp_info)
      ->set('wb_info', $wb_info)
      ->set('options', array(
        'info'     => TRUE,
        'summary'  => TRUE,
        'details'  => FALSE,
        'styles'   => TRUE,
        'subtitle' => 'Results Summary'
      ))
      ->render();

    $html .= View::factory('reports/verify')
      ->set('form_type', $form_type)
      ->set('report', $report)
      ->set('checks', $second)
      ->set('operator', $operator)
      ->set('site', $site)
      ->set('block', $block)
      ->set('specs_info', $specs_info)
      ->set('exp_info', $exp_info)
      ->set('wb_info', $wb_info)
      ->set('options', array(
        'info'     => TRUE,
        'summary'  => TRUE,
        'summary_total' => TRUE,
        'details'  => FALSE,
        'styles'   => TRUE,
        'subtitle' => 'Results Summary'
      ))
      ->render();

    $page_count = 0;
    $page_max   = 20;

    // passed
    $cntr  = 0;
    while ($cntr < count($passed_records)) {
      $max = $page_max;
      $set = array_slice($passed_records, $cntr, $max);
      $html .= View::factory('reports/verify')
        ->set('form_type', $form_type)
        ->set('checks', $checks)
        ->set('data', $set)
        ->set('options', array(
          'info'     => FALSE,
          'details'  => TRUE,
          'styles'   => FALSE,
          'subtitle' => 'Records Where All Verified as Accurate'
        ))
        ->set('cntr', $cntr)
        ->render();

      $cntr += $max;
    }

    // failed
    $cntr  = 0;
    while ($cntr < count($failed_records)) {
      $max = $page_max;
      $set = array_slice($failed_records, $cntr, $max);
      $html .= View::factory('reports/verify')
        ->set('form_type', $form_type)
        ->set('checks', $checks)
        ->set('data', $set)
        ->set('options', array(
          'info'     => FALSE,
          'details'  => TRUE,
          'styles'   => FALSE,
          'subtitle' => 'Records Where Any Verified as Inaccurate'
        ))
        ->set('cntr', $cntr)
        ->render();

      $cntr += $max;
    }

    // unchecked
    $cntr  = 0;
    while ($cntr < count($unchecked_records)) {
      $max = $page_max;
      $set = array_slice($unchecked_records, $cntr, $max);
      $html .= View::factory('reports/verify')
        ->set('form_type', $form_type)
        ->set('checks', $checks)
        ->set('data', $set)
        ->set('options', array(
          'info'     => FALSE,
          'details'  => TRUE,
          'styles'   => FALSE,
          'subtitle' => 'Records Not Yet Verified'
        ))
        ->set('cntr', $cntr)
        ->render();

      $cntr += $max;
    }

    // generate pdf
    set_time_limit(600);

    // save file
    $ext = 'pdf';
    $newdir = implode(DIRECTORY_SEPARATOR, array(
      'checks',
    ));

    switch ($form_type) {
      case 'SSFV':
        $newname = SGS::wordify('SSFV_'.$site->name.'_'.($block ? $block->name.'_' : '').SGS::date('now', 'm_d_Y')).'.'.$ext;
        break;

      case 'TDFV':
        $newname = SGS::wordify('TDFV_'.$site->name.'_'.($block ? $block->name.'_' : '').SGS::date('now', 'm_d_Y')).'.'.$ext;
        break;

      case 'LDFV':
        $newname = SGS::wordify('LDFV_'.$site->name.'_'.SGS::date('now', 'm_d_Y')).'.'.$ext;
        break;

    }

    if ($is_draft) $newname = 'VERIFY_DRAFT_'.$newname;
    else $newname = 'VERIFY_'.$newname;

    $version  = 0;
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
        'orientation' => 'Landscape',
        'disable-smart-shrinking' => TRUE,
        'footer-html' => View::factory('reports/checks')
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
      $file->operation      = 'D';
      $file->operation_type = 'VERIFY';
      $file->content_md5    = md5_file($fullname);
      $file->path = DIRECTORY_SEPARATOR.str_replace(DOCROOT, '', DOCPATH).$newdir.DIRECTORY_SEPARATOR.$newname;

      if ($operator) $file->operator = $operator;
      if ($site) $file->site = $site;
      if ($block) $file->block = $block;

      $file->save();
    } catch (ORM_Validation_Exception $e) {
      foreach ($e->errors('') as $err) Notify::msg(SGS::errorify($err).' ('.$file->name.')', 'error');
      return FALSE;
    }

    Notify::clear();
    $this->response->send_file($fullname);
  }

  private function handle_data_list($form_type, $id = NULL, $command = NULL) {
    if (!Request::$current->query()) Session::instance()->delete('pagination.data');

    $has_block_id   = (bool) (in_array($form_type, array('SSF', 'TDF')));
    $has_site_id    = (bool) (in_array($form_type, array('SSF', 'TDF', 'LDF')));
    $has_specs_info = (bool) (in_array($form_type, array('SPECS')));
//    $has_exp_info   = (bool) (in_array($form_type, array('SPECS')));
    $has_wb_info = (bool) (in_array($form_type, array('WB')));

    if ($id) {
      Session::instance()->delete('pagination.data');
      $item = ORM::factory($form_type, $id);

      if (isset($item->operator)) $operator = $item->operator;
      if (isset($item->site)) $site = $item->site;
      if (isset($item->block)) $block = $item->block;

      $data = array($item);
    }
    else {
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

      $model = ORM::factory($form_type);

      $check_options = array();
      foreach ($model::$checks as $type => $info) $check_options[$type] = $info['title'];

      $form = Formo::form();
      if ($has_site_id)  $form = $form->add_group('site_id', 'select', $site_ids, NULL, array('label' => 'Site', 'attr' => array('class' => 'siteopts')));
      else $form = $form->add_group('operator_id', 'select', $operator_ids, NULL, array_merge(array('label' => 'Operator'), $has_specs_info ? array('attr' => array('class' => 'specs_operatoropts specs_barcode exp_operatoropts exp_barcode')) : array(), $has_wb_info ? array('attr' => array('class' => 'wb_operatoropts wb_barcode')) : array()));
      if ($has_site_id and $has_block_id) $form = $form->add_group('block_id', 'select', array(), NULL, array('label' => 'Block', 'attr' => array('class' => 'blockopts')));
      if ($has_specs_info) $form = $form->add_group('specs_barcode', 'select', array(), NULL, array('label' => 'Shipment Specification', 'attr' => array('class' => 'specsopts')));
//      if ($has_exp_info) $form = $form->add_group('exp_barcode', 'select', array(), NULL, array('label' => 'Export Permit', 'attr' => array('class' => 'expopts')));
      if ($has_wb_info) $form = $form->add_group('wb_barcode', 'select', array(), NULL, array('label' => 'Waybill', 'attr' => array('class' => 'wbopts')));

      if (!$has_specs_info and !$has_exp_info and !$has_wb_info) {
        $form->add('from', 'input', array('label' => 'From', 'attr' => array('class' => 'dpicker', 'id' => 'from-dpicker')));
        $form->add('to', 'input', array('label' => 'To', 'attr' => array('class' => 'dpicker', 'id' => 'to-dpicker')));
      }
      $form->add_group('status', 'checkboxes', in_array($form_type, array_keys(SGS::$form_verification_type)) ? SGS::$verification_status : SGS::$data_status, NULL, array('label' => 'Status'));
      $form->add_group('errors', 'checkboxes', $check_options, NULL, array('label' => 'Errors'));
      $form->add('search', 'submit', 'Filter');

      if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
        Session::instance()->delete('pagination.data');
        if ($has_site_id) $site_id  = $form->site_id->val();
        else $operator_id = $form->operator_id->val();
        if ($has_site_id and $has_block_id) $block_id = $form->block_id->val();
        if ($has_specs_info) $specs_barcode = $form->specs_barcode->val();
//        if ($has_exp_info) $exp_barcode = $form->exp_barcode->val();
        if ($has_wb_info) $wb_barcode = $form->wb_barcode->val();

        if (!$has_specs_info and !$has_exp_info and !$has_wb_info) {
          $from = $form->from->val();
          $to   = $form->to->val();
        }

        $status = $form->status->val();
        $errors = $form->errors->val();

        $data = ORM::factory($form_type);

        Session::instance()->set('pagination.data', array(
          'site_id'       => $site_id,
          'operator_id'   => $operator_id,
          'block_id'      => $block_id,
          'specs_barcode' => $specs_barcode,
//          'exp_barcode'   => $exp_barcode,
          'status'        => $status,
          'wb_barcode'    => $wb_barcode,
          'errors'        => $errors,
          'from'          => $from,
          'to'            => $to
        ));
      }
      else if ($settings = Session::instance()->get('pagination.data')) {
        if ($has_site_id) $form->site_id->val($site_id = $settings['site_id']);
        else $form->operator_id->val($operator_id = $settings['operator_id']);
        if ($has_site_id and $has_block_id) $form->block_id->val($block_id = $settings['block_id']);
        if ($has_specs_info) $form->specs_barcode->val($specs_barcode = $settings['specs_barcode']);
//          if ($has_exp_info) $form->exp_barcode->val($exp_barcode = $settings['exp_barcode']);
        if ($has_wb_info) $form->wb_barcode->val($wb_barcode = $settings['wb_barcode']);

        if (!$has_specs_info and !$has_exp_info and !$has_wb_info) {
          $form->from->val($from = $settings['from']);
          $form->to->val($to = $settings['to']);
        }

        $form->status->val($status = $settings['status']);
        $form->errors->val($errors = $settings['errors']);

        $data = ORM::factory($form_type);
      }
      
      if ($data) {
        if ($site_id)     $data->and_where('site_id', 'IN', (array) $site_id);
        if ($operator_id) $data->and_where('operator_id', 'IN', (array) $operator_id);
        if ($block_id)    $data->and_where('block_id', 'IN', (array) $block_id);
        if ($status)      $data->and_where('status', 'IN', (array) $status);

        if ($errors) {
          foreach ($model::$checks as $type => $info) if (in_array($type, $errors))
            foreach ($info['checks'] as $check => $array) $checks[] = $check;

          if ($checks) $data->join('checks')
            ->distinct(TRUE)
            ->on(strtolower($form_type).'.id', '=', 'checks.form_data_id')
            ->on('checks.form_type', '=', DB::expr("'".$form_type."'"))
            ->and_where_open()
              ->and_where('checks.check', 'IN', (array) $checks)
              ->and_where('checks.type', '=', 'E')
            ->and_where_close();
        }

        if (Valid::is_barcode($specs_barcode)) $data->and_where('specs_barcode_id', '=', SGS::lookup_barcode($specs_barcode, NULL, TRUE));
//        if (Valid::is_barcode($exp_barcode))   $data->and_where('exp_barcode_id', '=', SGS::lookup_barcode($exp_barcode, NULL, TRUE));
        if (Valid::is_barcode($wb_barcode)) $data->and_where('wb_barcode_id', '=', SGS::lookup_barcode($wb_barcode, NULL, TRUE));
        if (!$has_specs_info and !$has_exp_info and !$has_wb_info) $data->and_where('create_date', 'BETWEEN', SGS::db_range($from, $to));

        $clone = clone($data);
        $pagination = Pagination::factory(array(
          'items_per_page' => 50,
          'total_items' => $clone->find_all()->count()));

        $data = $data
          ->offset($pagination->offset)
          ->limit($pagination->items_per_page);
        if ($sort = $this->request->query('sort')) $data->order_by($sort);
        $data = $data->order_by('status')
          ->find_all()
          ->as_array();

        if ($pagination->total_items == 1) Notify::msg($pagination->total_items.' record found');
        elseif ($pagination->total_items) Notify::msg($pagination->total_items.' records found');
        else Notify::msg('No records found');
      }
    }

    if ($data) {
      if (!$site)     $site     = ORM::factory('site', (int) $site_id);
      if (!$block)    $block    = ORM::factory('block', (int) $block_id);
      if (!$operator) $operator = ORM::factory('operator', (int) $operator_id);

      unset($info);
      if ($form_type == 'SPECS' and $specs_barcode) {
        $sample = reset($data);
        $info['specs'] = array(
          'number'  => $sample->specs_number,
          'barcode' => $sample->specs_barcode->barcode
        );
      }
      if ($form_type == 'WB' and $wb_barcode) {
        $sample = reset($data);
        $info['wb'] = array(
          'barcode' => $sample->wb_barcode->barcode
        );
      }

      $table = View::factory('data')
        ->set('classes', array('has-pagination'))
        ->set('form_type', $form_type)
        ->set('data', $data)
        ->set('operator', $operator->loaded() ? $operator : NULL)
        ->set('site', $site->loaded() ? $site : NULL)
        ->set('block', $block->loaded() ? $block : NULL)
        ->set('specs_info', $info ? array_filter((array) $info['specs']) : NULL)
        ->set('exp_info', $info ? array_filter((array) $info['exp']) : NULL)
        ->set('wb_info', $info ? array_filter((array) $info['wb']) : NULL)
        ->render();
    }

    if ($form) $content .= $form->render();
    $content .= $table;
    $content .= $pagination;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  private function handle_data_hierarchy($form_type, $id) {
    $item     = ORM::factory($form_type, $id);
    $parents  = $item->parents();
    $children = $item->children();
    $siblings = $item->siblings();

    $table = View::factory('data')
      ->set('form_type', $item::$type)
      ->set('data', array($item))
      ->set('operator', (isset($item->operator) and $item->operator->loaded()) ? $item->operator : NULL)
      ->set('site', (isset($item->site) and $item->site->loaded()) ? $item->site : NULL)
      ->set('block', (isset($item->block) and $item->block->loaded()) ? $item->block : NULL)
      ->set('specs_info', $info ? array_filter((array) $info['specs']) : NULL)
      ->set('exp_info', $info ? array_filter((array) $info['exp']) : NULL)
      ->set('wb_info', $info ? array_filter((array) $info['wb']) : NULL)
      ->set('options', array('header' => TRUE))
      ->render();

    if ($parents) foreach (array_reverse($parents) as $parent) {
      $table .= View::factory('data')
        ->set('classes', array('has-section'))
        ->set('form_type', $parent::$type)
        ->set('data', array($parent))
        ->set('operator', (isset($parent->operator) and $parent->operator->loaded()) ? $parent->operator : NULL)
        ->set('site', (isset($parent->site) and $parent->site->loaded()) ? $parent->site : NULL)
        ->set('block', (isset($parent->block) and $parent->block->loaded()) ? $parent->block : NULL)
        ->set('options', array('header' => FALSE, 'hide_header_info' => TRUE))
        ->render();
    }

    $_siblings = array();
    if ($siblings) foreach ($siblings as $sibling) $_siblings[$sibling::$type][] = $sibling;
    foreach ($_siblings as $type => $_sibling) {
      $table .= View::factory('data')
        ->set('classes', array('has-section'))
        ->set('form_type', $type)
        ->set('data', $_sibling)
        ->set('operator', (isset($item->operator) and $item->operator->loaded()) ? $item->operator : NULL)
        ->set('site', (isset($item->site) and $item->site->loaded()) ? $item->site : NULL)
        ->set('block', (isset($item->block) and $item->block->loaded()) ? $item->block : NULL)
        ->set('options', array('header' => FALSE, 'hide_header_info' => TRUE))
        ->render();
    }

    $childrens = array();
    if ($children) foreach ($children as $child) $childrens[$child::$type][] = $child;
    foreach ($childrens as $type => $childs) {
      $table .= View::factory('data')
        ->set('classes', array('has-section'))
        ->set('form_type', $type)
        ->set('data', $childs)
        ->set('operator', (isset($item->operator) and $item->operator->loaded()) ? $item->operator : NULL)
        ->set('site', (isset($item->site) and $item->site->loaded()) ? $item->site : NULL)
        ->set('block', (isset($item->block) and $item->block->loaded()) ? $item->block : NULL)
        ->set('options', array('header' => FALSE, 'hide_header_info' => TRUE))
        ->render();
    }

    $content .= $table;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  private function handle_data_revisions($form_type, $id) {
    $item      = ORM::factory($form_type, $id);
    $revisions = $item->get_revisions();

    $table = View::factory('data')
      ->set('form_type', $item::$type)
      ->set('data', array($item))
      ->set('operator', (isset($item->operator) and $item->operator->loaded()) ? $item->operator : NULL)
      ->set('site', (isset($item->site) and $item->site->loaded()) ? $item->site : NULL)
      ->set('block', (isset($item->block) and $item->block->loaded()) ? $item->block : NULL)
      ->set('specs_info', $info ? array_filter((array) $info['specs']) : NULL)
      ->set('exp_info', $info ? array_filter((array) $info['exp']) : NULL)
      ->set('wb_info', $info ? array_filter((array) $info['wb']) : NULL)
      ->set('options', array('header' => TRUE, 'hide_header_info' => TRUE))
      ->render();

    if ($revisions) $table .= View::factory('data')
      ->set('classes', array('has-section'))
      ->set('form_type', $form_type)
      ->set('data', $revisions)
      ->set('operator', (isset($item->operator) and $item->operator->loaded()) ? $item->operator : NULL)
      ->set('site', (isset($item->site) and $item->site->loaded()) ? $item->site : NULL)
      ->set('block', (isset($item->block) and $item->block->loaded()) ? $item->block : NULL)
      ->set('options', array('header' => FALSE, 'hide_header_info' => TRUE, 'hide_upload_info' => FALSE, 'links' => FALSE, 'dropdown' => FALSE))
      ->render();

    $content .= $table;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  private function handle_data_edit($form_type, $id) {
    if (!(Auth::instance()->get_user()->id == 1)) $this->request->redirect('analysis/review/'.strtolower($form_type).'/'.$id);

    $item = ORM::factory($form_type, $id);

    $form = Formo::form(array('attr' => array('style' => ($id or $_POST) ? '' : 'display: none;')))
      ->orm('load', $item)
      ->add('save', 'submit', array(
        'label' => 'Save'
    ));

    if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
      try {
        $item->status = 'P';
        $item->save();
        $updated = TRUE;
        Notify::msg('Form data saved.', 'success', TRUE);
      } catch (Exception $e) {
        Notify::msg('Sorry, form data update failed. Please try again.', 'error');
      }
    }

    if ($updated) {
      $item->run_checks();
      switch ($item->status) {
        case 'A': Notify::msg('Updated record passed checks and queries.', 'success', TRUE); break;
        case 'R': Notify::msg('Updated record failed checks and queries.', 'error', TRUE); break;
        default:  Notify::msg('Updated could not be accessed.', 'error', TRUE);
      }
    }

    unset($info);
    if ($form_type == 'SPECS') {
      $info['specs'] = array(
        'number'  => $item->specs_number,
        'barcode' => $item->specs_barcode->barcode
      );
    }
    if ($form_type == 'WB' and $wb_barcode) {
      $sample = reset($data);
      $info['wb'] = array(
        'barcode' => $sample->wb_barcode->barcode
      );
    }

    $table = View::factory('data')
      ->set('classes', array('has-pagination'))
      ->set('form_type', $item::$type)
      ->set('data', array($item))
      ->set('site', isset($item->site) ? $item->site : NULL)
      ->set('block', isset($item->block) ? $item->block : NULL)
      ->set('specs_info', $info ? array_filter((array) $info['specs']) : NULL)
      ->set('exp_info', $info ? array_filter((array) $info['exp']) : NULL)
      ->set('wb_info', $info ? array_filter((array) $info['wb']) : NULL)
      ->render();

    if ($form) $content .= $form->render();
    $content .= $table;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  private function handle_data_check($form_type, $id) {
    $item  = ORM::factory($form_type, $id);

    $item->status = 'P';
    $item->run_checks();

    switch ($item->status) {
      case 'A': Notify::msg('Form data passed checks and queries.', 'success', TRUE); break;
      case 'R': Notify::msg('Form data failed checks and queries.', 'error', TRUE); break;
      case 'P': Notify::msg('Form data unchecked.', 'error', TRUE); break;
      default:  Notify::msg('Form data cound not be accessed.', 'error', TRUE);
    }

    $this->request->redirect('analysis/review/'.strtolower($form_type).'/'.$id);
  }

  private function handle_data_delete($form_type, $id) {
    if (!Auth::instance()->logged_in('management')) $this->request->redirect('analysis/review/'.strtolower($form_type).'/'.$id);

    $item  = ORM::factory($form_type, $id);

    if (!$item->loaded()) {
      Notify::msg('No form data found.', 'warning', TRUE);
      $this->request->redirect('analysis/review/'.strtolower($form_type));
    }

    $form = Formo::form()
      ->add('confirm', 'text', 'Are you sure you want to delete this form data?')
      ->add('delete', 'centersubmit', 'Delete');

    if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
      $csv = ORM::factory('CSV')
        ->where('form_type', '=', $form_type)
        ->and_where('form_data_id', '=', $item->id)
        ->find();

      try {
        if ($csv->loaded()) {
          $csv->status = 'D';
          $csv->save();
        }
        $item->delete();
        if ($item->loaded()) throw new Exception();
        Notify::msg('Form data successfully deleted.', 'success', TRUE);
      } catch (Exception $e) {
        Notify::msg('Form data failed to be deleted.', 'error', TRUE);
      }

      $this->request->redirect('analysis/review/'.strtolower($form_type));
    }

    $table = View::factory('data')
      ->set('classes', array('has-pagination'))
      ->set('form_type', $form_type)
      ->set('data', array($item))
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

  private function handle_verify($form_verification_type) {
    if (!Request::$current->query()) Session::instance()->delete('pagination.checks');

    $model = ORM::factory($form_verification_type);
    $form_data_type = $model::$data_type;

    foreach ($model::$checks as $type => $info) $check_options[$type] = $info['title'];

    $has_block_id   = (bool) (in_array($form_verification_type, array('SSFV', 'TDFV')));
    $has_site_id    = (bool) (in_array($form_verification_type, array('SSFV', 'TDFV', 'LDFV')));

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
    if ($has_site_id)  $form = $form->add_group('site_id', 'select', $site_ids, NULL, array('required' => TRUE, 'label' => 'Site', 'attr' => array('class' => 'siteopts')));
    else $form = $form->add_group('operator_id', 'select', $operator_ids, NULL);
    if ($has_site_id and $has_block_id) $form = $form->add_group('block_id', 'select', array(), NULL, array('label' => 'Block', 'attr' => array('class' => 'blockopts')));

    $form = $form
      ->add('from', 'input', array('label' => 'Declared From', 'attr' => array('class' => 'dpicker', 'id' => 'from-dpicker')))
      ->add('to', 'input', array('label' => 'Declared To', 'attr' => array('class' => 'dpicker', 'id' => 'to-dpicker')))
      ->add('inspected_from', 'input', array('label' => 'Inspected From', 'attr' => array('class' => 'dpicker', 'id' => 'inspeced-from-dpicker')))
      ->add('inspected_to', 'input', array('label' => 'Inspected To', 'attr' => array('class' => 'dpicker', 'id' => 'inspected-to-dpicker')))
      ->add_group('status', 'checkboxes', SGS::$verification_status, array('R', 'P'), array('label' => 'Status'))
      ->add_group('checks', 'checkboxes', $check_options, array_diff(array_keys($check_options), array('consistency', 'reliability')), array('label' => 'Check'))
      ->add_group('format', 'radios', array('R' => 'Verify', 'D' => 'Download Report', 'RD' => 'Verify and Download Report'), 'R', array('label' => 'Action'))
      ->add('submit', 'submit', 'Go');

    if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
      Session::instance()->delete('pagination.checks');

      if ($has_site_id) $site_id = $form->site_id->val();
      else $operator_id = $form->operator_id->val();
      if ($has_site_id and $has_block_id) $block_id = $form->block_id->val();

      $from = $form->from->val();
      $to   = $form->to->val();

      $inspected_from = $form->inspected_from->val();
      $inspected_to   = $form->inspected_to->val();

      $status = $form->status->val();
      $checks = $form->checks->val();
      $format = $form->format->val();

      $run      = strpos($format, 'R') !== FALSE;
      $download = strpos($format, 'D') !== FALSE;

      $inaccurate = 0;
      $accurate   = 0;
      $unverified = 0;
      $failure    = 0;

      $model_data = ORM::factory($form_data_type);
      $record_ids = DB::select();

      if ($operator_id) $record_ids->where('operator_id', 'IN', (array) $operator_id);
      if ($site_id)     $record_ids->where('site_id', 'IN', (array) $site_id);
      if ($block_id)    $record_ids->where('block_id', 'IN', (array) $block_id);

      $declared_record_ids  = clone($record_ids);
      $inspected_record_ids = clone($record_ids);

      $declared_record_ids = $declared_record_ids
        ->select($model_data->table_name().'.id', 'barcode')
        ->distinct(TRUE)
        ->from($model_data->table_name())
        ->join('barcodes')
        ->on('barcodes.id', '=', 'barcode_id')
        ->where('create_date', 'BETWEEN', SGS::db_range($from, $to))
        ->order_by('barcode')
        ->execute()
        ->as_array(NULL, 'id');

      if ($status) $inspected_record_ids->where('status', 'IN', (array) $status);
      $inspected_record_ids = $inspected_record_ids
        ->select($model->table_name().'.id', 'barcode')
        ->distinct(TRUE)
        ->from($model->table_name())
        ->join('barcodes')
        ->on('barcodes.id', '=', 'barcode_id')
        ->order_by('barcode')
        ->where('create_date', 'BETWEEN', SGS::db_range($inspected_from, $inspected_to))
        ->execute()
        ->as_array(NULL, 'id');

      $data = array(
        'checks' => array(),
        'total'  => array(
          'records'    => 0,
          'declared'   => count($declared_record_ids),
          'inspected'  => count($inspected_record_ids),
          'verified'   => 0,
          'unverified' => 0,
          'accurate'   => 0,
          'inaccurate' => 0,
        ),
        'variance' => array()
      );

      $unable = 0;
      set_time_limit(0);
      foreach ($inspected_record_ids as $inspected_record_id) {
        $record = ORM::factory($form_verification_type, $inspected_record_id);
        if ((!$data_record = $record->data()) or !$data_record->loaded()) continue;

        $data['total']['records']++;

        $errors   = array();
        $warnings = array();

        try {
          if ($run) list($raw['errors'], $raw['warnings']) = $record->run_checks();
          else $raw = array('errors' => $record->get_errors(TRUE), 'warnings' => $record->get_warnings(TRUE));
          foreach ($raw['errors'] as $re) $errors += array_keys($re);
          foreach ($raw['warnings'] as $rw) $warnings += array_keys($rw);
        } catch (ORM_Validation_Exception $e) {
          foreach ($e->errors('') as $err) Notify::msg(SGS::errorify($err), 'error', TRUE);
        } catch (Exception $e) {
          $unable++;
        }

        $variance_fields = array(
          'diameter',
          'height',
          'length',
          'volume'
        );

        foreach ($variance_fields as $dfield) {
          try {
            $data['variance'][$dfield]['data']['total'] += $data_record->$dfield;
            $data['variance'][$dfield]['data']['count']++;
          } catch (Exception $e) {}
          try {
            $data['variance'][$dfield]['verification']['total'] += $record->$dfield;
            $data['variance'][$dfield]['verification']['count']++;
          } catch (Exception $e) {}
        }

        if (in_array('height', array_keys($data['variance'])) and
            in_array('length', array_keys($data['variance']))) unset($data['variance']['length']);

        /*** $total_warned = FALSE; ***/
        foreach ($record::$checks as $type => $info) {
          if (!in_array($type, $checks)) continue;
          foreach ($info['checks'] as $check => $array) {
            /*** $check_warned = FALSE; ***/
            if ($type == 'tolerance' and array_intersect(array_keys((array) $record::$checks['traceability']['checks']), $errors)) continue;
            if ($type == 'tolerance' and array_intersect(array_keys((array) $record::$checks['declaration']['checks']), $errors)) continue;
            if ($type == 'tolerance' and array_intersect(array_keys((array) $record::$checks['verification']['checks']), $errors)) continue;
            $data['checks'][$type][$check]['records']++;
            if (in_array($check, $errors)) $data['checks'][$type][$check]['inaccurate']++;
            /*** WARNINGS ARE IGNORED
            else if (in_array($check, $warnings)) {
              $data['checks'][$type][$check]['warned']++;
              if (!$check_warned) {
                $check_warned = TRUE;
              }
              if (!$total_warned) $total_warned = TRUE;
              $data['checks'][$type][$check]['inaccurate']++;
            } ***/
            else $data['checks'][$type][$check]['accurate']++;
          }
          /*** if ($total_warned) $data['total']['warned']++; ***/
        }

        switch ($record->status) {
          case 'A':
            $accurate++;
            $data['total']['verified']++;
            $data['total']['accurate']++;
            break;

          case 'R':
            $inaccurate++;
            $data['total']['verified']++;
            $data['total']['inaccurate']++;
            break;

          default:
            $unverified++;
            $data['total']['unverified']++;
            break;
        }

        try {
          $record->save();
        } catch (Exception $e) {
          $failure++;
        }

        unset($record);
        unset($data_record);
      }

      if ($download) {
        foreach ($model::$checks as $type => $info) if (in_array($type, $checks)) $_checks[$type] = $info;

        self::download_verify_report($form_verification_type, $inspected_record_ids, array(
          'operator'      => $operator_id ? ORM::factory('operator', $operator_id) : NULL,
          'site'          => $site_id ? ORM::factory('site', $site_id) : NULL,
          'block'         => $block_id ? ORM::factory('block', $block_id) : NULL,
          'from'          => $from,
          'to'            => $to,
          'checks'        => $_checks,
          'report'        => $data,
        ));
      }

      if ($unable)     Notify::msg('Sorry, unable to verify '.$unable.' records. Please try again.', 'error', TRUE);
      if ($accurate)   Notify::msg($accurate.' records are verified and accurate.', 'success', TRUE);
      if ($inaccurate) Notify::msg($inaccurate.' records are verified and inaccurate.', 'error', TRUE);
      if ($unverified) Notify::msg($unverified.' records could not be verified.', 'warning', TRUE);
      if ($failure)    Notify::msg($failure.' records could not be accessed.', 'error', TRUE);

      Session::instance()->set('pagination.checks', array(
        'operator_id'    => $operator_id,
        'site_id'        => $site_id,
        'block_id'       => $block_id,
        'status'         => $status,
        'format'         => $format,
        'checks'         => $checks,
        'form_type'      => $form_verification_type,
        'from'           => $from,
        'to'             => $to,
        'inspected_from' => $from,
        'inspected_to'   => $to,
        'data'           => $data,
        'declared_record_ids' => $declared_record_ids,
        'inspected_record_ids' => $inspected_record_ids
      ));
    }
    else if ($settings = Session::instance()->get('pagination.checks')) {
      if ($has_site_id) $form->site_id->val($site_id = $settings['site_id']);
      else $form->operator_id->val($operator_id = $settings['operator_id']);
      if ($has_site_id and $has_block_id) $form->block_id->val($block_id = $settings['block_id']);

      $form->status->val($status = $settings['status']);
      $form->format->val($format = $settings['format']);
      $form->checks->val($checks = $settings['checks']);

      $form->from->val($from = $settings['from']);
      $form->to->val($to = $settings['to']);

      $data = $settings['data'];
      $declared_record_ids  = $settings['declared_record_ids'];
      $inspected_record_ids = $settings['inspected_record_ids'];
    }

    if ($data) {
      foreach ($model::$checks as $type => $info) if (in_array($type, $checks)) $_checks[$type] = $info;

      $records = ORM::factory($form_verification_type)
        ->where('id', 'IN', (array) $inspected_record_ids)
        ->and_where('create_date', 'BETWEEN', SGS::db_range($from, $to));

      if ($has_site_id and $site_id) $records = $records->where('site_id', 'IN', (array) $site_id);
      else if ($operator_id) $records = $records->where('operator_id', 'IN', (array) $operator_id);
      if ($has_site_id and $has_block_id and $block_id) $records = $records->where('block_id', 'IN', (array) $block_id);

      $clone = clone($records);
      $pagination = Pagination::factory(array(
        'items_per_page' => 50,
        'total_items' => $clone->find_all()->count()));

      $records = $records
        ->offset($pagination->offset)
        ->limit($pagination->items_per_page);
      if ($sort = $this->request->query('sort')) $records->order_by($sort);
      $records = $records->order_by('status')
        ->find_all()
        ->as_array();

      $operator = ORM::factory('operator', $operator_id ?: NULL);
      $site     = ORM::factory('site', $site_id ?: NULL);
      $block    = ORM::factory('block', $block_id ?: NULL);

      $header = View::factory('data')
        ->set('form_type', $form_verification_type)
        ->set('data', $records)
        ->set('operator', $operator->loaded() ? $operator : NULL)
        ->set('site', $site->loaded() ? $site : NULL)
        ->set('block', $block->loaded() ? $block : NULL)
        ->set('options', array(
          'table'   => FALSE,
          'rows'    => FALSE,
          'actions' => FALSE,
          'header'  => TRUE,
          'details' => FALSE,
          'links'   => FALSE
        ))
        ->render();

      $report = View::factory('reports/verify_summary')
        ->set('form_type', $form_verification_type)
        ->set('report', $data)
        ->set('checks', $_checks)
        ->render();

      $table = View::factory('data')
        ->set('classes', array('has-pagination'))
        ->set('form_type', $form_verification_type)
        ->set('data', $records)
        ->set('operator', $operator->loaded() ? $operator : NULL)
        ->set('site', $site->loaded() ? $site : NULL)
        ->set('block', $block->loaded() ? $block : NULL)
        ->set('options', array(
          'hide_header_info' => TRUE,
          'header'  => FALSE,
          'details' => TRUE,
        ))
        ->render();
    }

    if ($form)   $content .= $form;
    if ($header) $content .= $header;
    if ($report) $content .= $report;
    if ($table)  {
      $content .= $table;
      $content .= $pagination;
    }

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  private function handle_checks($form_type) {
    if (!Request::$current->query()) Session::instance()->delete('pagination.checks');

    $model = ORM::factory($form_type);
    foreach ($model::$checks as $type => $info) $check_options[$type] = $info['title'];

    $has_block_id   = (bool) (in_array($form_type, array('SSF', 'TDF')));
    $has_site_id    = (bool) (in_array($form_type, array('SSF', 'TDF', 'LDF')));
    $has_specs_info = (bool) (in_array($form_type, array('SPECS')));
    $has_exp_info   = (bool) (in_array($form_type, array('SPECS')));
    $has_wb_info    = (bool) (in_array($form_type, array('WB')));
    $has_volume     = (bool) (in_array($form_type, array('SSF', 'TDF', 'LDF', 'SPECS')));

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
    if ($has_site_id)  $form = $form->add_group('site_id', 'select', $site_ids, NULL, array('required' => TRUE, 'label' => 'Site', 'attr' => array('class' => 'siteopts')));
    else $form = $form->add_group('operator_id', 'select', $operator_ids, NULL, array_merge(array('required' => TRUE, 'label' => 'Operator'), $has_specs_info ? array('attr' => array('class' => 'specs_operatoropts specs_barcode')) : array(), $has_wb_info ? array('attr' => array('class' => 'wb_operatoropts wb_barcode')) : array()));
    if ($has_site_id and $has_block_id) $form = $form->add_group('block_id', 'select', array(), NULL, array('label' => 'Block', 'attr' => array('class' => 'blockopts')));
    if ($has_specs_info) $form = $form->add_group('specs_barcode', 'select', array(), NULL, array('required' => TRUE, 'label' => 'Shipment Specification', 'attr' => array('class' => 'specsopts')));
    if ($has_wb_info) $form = $form->add_group('wb_barcode', 'select', array(), NULL, array('required' => TRUE, 'label' => 'Waybill', 'attr' => array('class' => 'wbopts')));

    if (!$has_specs_info and !$has_exp_info and !$has_wb_info) {
      $form = $form
        ->add('from', 'input', array('label' => 'From', 'attr' => array('class' => 'dpicker', 'id' => 'from-dpicker')))
        ->add('to', 'input', array('label' => 'To', 'attr' => array('class' => 'dpicker', 'id' => 'to-dpicker')));
    }

    $form = $form
      ->add_group('limit', 'select', array(
        100   => '100 records',
        500   => '500 records',
        1000  => '1000 records',
        2000  => '2000 records',
        5000  => '5000 records',
        10000 => '10000 records'), NULL, array('label' => 'Limit'))
      ->add_group('status', 'checkboxes', SGS::$data_status, array('P', 'R'), array('label' => 'Status'))
      ->add_group('checks', 'checkboxes', $check_options, array_diff(array_keys($check_options), array('consistency', 'reliability')), array('label' => 'Check'))
      ->add_group('format', 'radios', array('R' => 'Check', 'D' => 'Download Report', 'RD' => 'Check and Download Report'), 'R', array('label' => 'Action'))
      ->add('submit', 'submit', 'Go');

    if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
      Session::instance()->delete('pagination.checks');

      if ($has_site_id) $site_id = $form->site_id->val();
      else $operator_id = $form->operator_id->val();
      if ($has_site_id and $has_block_id) $block_id = $form->block_id->val();
      if ($has_specs_info) $specs_barcode = $form->specs_barcode->val();
      if ($has_wb_info) $wb_barcode = $form->wb_barcode->val();

      if (!$has_specs_info and !$has_exp_info and !$has_wb_info) {
        $from = $form->from->val();
        $to   = $form->to->val();
      }

      $status  = $form->status->val();
      $checks  = $form->checks->val();
      $limit   = $form->limit->val();
      $format  = $form->format->val();

      $run      = strpos($format, 'R') !== FALSE;
      $download = strpos($format, 'D') !== FALSE;

      $rejected  = 0;
      $accepted  = 0;
      $unchecked = 0;
      $failure   = 0;

      $model = ORM::factory($form_type);
      $record_ids = DB::select($model->table_name().'.id')->from($model->table_name());

      if ($operator_id) $record_ids->where('operator_id', 'IN', (array) $operator_id);
      if ($site_id)     $record_ids->where('site_id', 'IN', (array) $site_id);
      if ($block_id)    $record_ids->where('block_id', 'IN', (array) $block_id);
      if ($status)      $record_ids->where('status', 'IN', (array) $status);

      if (Valid::is_barcode($specs_barcode)) $record_ids->where('specs_barcode_id', '=', SGS::lookup_barcode($specs_barcode, NULL, TRUE));
      if (Valid::is_barcode($wb_barcode)) $record_ids->where('wb_barcode_id', '=', SGS::lookup_barcode($wb_barcode, NULL, TRUE));
      if (!$has_specs_info and !$has_exp_info and !$has_wb_info) $record_ids->where('create_date', 'BETWEEN', SGS::db_range($from, $to));

      if ($limit) $record_ids->limit($limit);

      $record_ids = $record_ids
        ->join('barcodes')
        ->on('barcodes.id', '=', 'barcode_id')
        ->order_by('barcode')
        ->execute()
        ->as_array(NULL, 'id');

      $data = array(
        'checks' => array(),
        'total'  => array(
          'checked'   => 0,
          'passed'    => 0,
          'failed'    => 0,
          'warned'    => 0,
          'unchecked' => 0
        ),
        'volume' => array(
          'checked'   => 0,
          'passed'    => 0,
          'failed'    => 0,
          'warned'    => 0,
          'unchecked' => 0
        )
      );

      $unable = 0;
      set_time_limit(0);
      foreach ($record_ids as $record_id) {
        $record = ORM::factory($form_type, $record_id);

        $errors   = array();
        $warnings = array();

        try {
          if ($run) list($raw['errors'], $raw['warnings']) = $record->run_checks();
          else $raw = array('errors' => $record->get_errors(TRUE), 'warnings' => $record->get_warnings(TRUE));
          foreach ($raw['errors'] as $re) $errors += array_keys($re);
          foreach ($raw['warnings'] as $rw) $warnings += array_keys($rw);
        } catch (ORM_Validation_Exception $e) {
          foreach ($e->errors('') as $err) Notify::msg(SGS::errorify($err), 'error', TRUE);
        } catch (Exception $e) {
          $unable++;
        }

        $total_warned = FALSE;
        foreach ($record::$checks as $type => $info) {
          if (!in_array($type, $checks)) continue;
          foreach ($info['checks'] as $check => $array) {
            $check_warned = FALSE;
            if ($type == 'tolerance' and array_intersect(array_keys((array) $record::$checks['traceability']['checks']), $errors)) continue;
            $data['checks'][$type][$check]['checked']++;
            if ($has_volume) $data['volume'][$type][$check]['checked'] += $record->volume;
            if (in_array($check, $errors)) {
              $data['checks'][$type][$check]['failed']++;
              if ($has_volume) $data['volume'][$type][$check]['failed'] += $record->volume;
            } else {
              if (in_array($check, $warnings)) {
                if (!$check_warned) {
                  $data['checks'][$type][$check]['warned']++;
                  if ($has_volume) $data['volume'][$type][$check]['warned'] += $record->volume;
                  $check_warned = TRUE;
                }
                if (!$total_warned) $total_warned = TRUE;
              }
              $data['checks'][$type][$check]['passed']++;
              if ($has_volume) $data['volume'][$type][$check]['passed'] += $record->volume;
            }
          }
          if ($total_warned) $data['total']['warned']++;
          if ($total_warned and $has_volume) $data['volume']['warned'] += $record->volume;
        }

        $data['total']['records']++;
        if ($has_volume) $data['total_volume']['records'] += $record->volume;
        
        switch ($record->status) {
          case 'A':
            $accepted++;
            $data['total']['checked']++;
            $data['total']['passed']++;
            if ($has_volume) {
              $data['total_volume']['checked'] += $record->volume;
              $data['total_volume']['passed'] += $record->volume;
            }
            break;

          case 'R':
            $rejected++;
            $data['total']['checked']++;
            $data['total']['failed']++;
            if ($has_volume) {
              $data['total_volume']['checked'] += $record->volume;
              $data['total_volume']['failed'] += $record->volume;
            }
            break;

          default:
            $unchecked++;
            $data['total']['unchecked']++;
            if ($has_volume) $data['total_volume']['unchecked'] += $record->volume;
            break;
        }

        try {
          if ($run) $record->save();
        } catch (Exception $e) {
          $failure++;
        }

        unset($record);
      }

      if ($download) {
        unset($info);
        if ($specs_barcode) {
          $sample = ORM::factory($form_type, reset($record_ids));
          $info['specs'] = array(
            'number'  => $sample->specs_number,
            'barcode' => $sample->specs_barcode->barcode
          );
        }
        if ($wb_barcode) {
          $sample = reset($data);
          $info['wb'] = array(
            'barcode' => $sample->wb_barcode->barcode
          );
        }

        foreach ($model::$checks as $typ => $inf) if (in_array($typ, $checks)) $_checks[$typ] = $inf;

        self::download_checks_report($form_type, $record_ids, array(
          'specs_info' => $info ? array_filter((array) $info['specs']) : NULL,
          'exp_info'   => $info ? array_filter((array) $info['exp']) : NULL,
          'wb_info'    => $info ? array_filter((array) $info['wb']) : NULL,
          'operator'   => $operator_id ? ORM::factory('operator', $operator_id) : NULL,
          'site'       => $site_id ? ORM::factory('site', $site_id) : NULL,
          'block'      => $block_id ? ORM::factory('block', $block_id) : NULL,
          'from'       => $from,
          'to'         => $to,
          'checks'     => $_checks,
          'report'     => $data,
        ));
      }

      if ($unable)    Notify::msg('Sorry, unable to run checks and queries on '.$unable.' records. Please try again.', 'error', TRUE);
      if ($accepted)  Notify::msg($accepted.' records passed checks and queries.', 'success', TRUE);
      if ($rejected)  Notify::msg($rejected.' records failed checks and queries.', 'error', TRUE);
      if ($unchecked) Notify::msg($unchecked.' records unchecked.', 'warning', TRUE);
      if ($failure)   Notify::msg($failure.' records could not be accessed.', 'error', TRUE);

      Session::instance()->set('pagination.checks', array(
        'operator_id'   => $operator_id,
        'site_id'       => $site_id,
        'block_id'      => $block_id,
        'specs_barcode' => $specs_barcode,
        'wb_barcode'    => $wb_barcode,
        'status'        => $status,
        'format'        => $format,
        'checks'        => $checks,
        'limit'         => $limit,
        'form_type'     => $form_type,
        'from'          => $from,
        'to'            => $to,
        'data'          => $data,
        'record_ids'    => $record_ids
      ));
    }
    else if ($settings = Session::instance()->get('pagination.checks')) {
      if ($has_site_id)  $form->site_id->val($site_id = $settings['site_id']);
      else $form->operator_id->val($operator_id = $settings['operator_id']);
      if ($has_site_id and $has_block_id) $form->block_id->val($block_id = $settings['block_id']);
      if ($has_specs_info) $form->specs_barcode->val($specs_barcode = $settings['specs_barcode']);
      if ($has_wb_info) $form->wb_barcode->val($wb_barcode = $settings['wb_barcode']);

      $form->status->val($status = $settings['status']);
      $form->format->val($format = $settings['format']);
      $form->checks->val($checks = $settings['checks']);

      if (!$has_specs_info and !$has_exp_info and !$has_wb_info) {
        $form->from->val($from = $settings['from']);
        $form->to->val($to = $settings['to']);
      }

      $form->limit->val($limit = $settings['limit']);

      $data        = $settings['data'];
      $record_ids  = $settings['record_ids'];
    }

    if ($data and $record_ids) {
      $model = ORM::factory($form_type);
      foreach ($model::$checks as $type => $info) {
        if (in_array($type, $checks)) $_checks[$type] = $info;
      }

      $records = ORM::factory($form_type);

      if ($record_ids) $records->where('id', 'IN', $record_ids);
      if ($status) $records->where('status', 'IN', (array) $status);

      if ($has_site_id and $site_id) $records = $records->where('site_id', 'IN', (array) $site_id);
      else if ($operator_id) $records = $records->where('operator_id', 'IN', (array) $operator_id);
      if ($has_site_id and $has_block_id and $block_id) $records = $records->where('block_id', 'IN', (array) $block_id);

      if (Valid::is_barcode($specs_barcode))   $records = $records->where('specs_barcode_id', '=', SGS::lookup_barcode($specs_barcode, NULL, TRUE));
      if (Valid::is_barcode($wb_barcode))   $records = $records->where('wb_barcode_id', '=', SGS::lookup_barcode($wb_barcode, NULL, TRUE));
      if (!$has_specs_info and !$has_exp_info and !$has_wb_info) $records = $records->where('create_date', 'BETWEEN', SGS::db_range($from, $to));

      $clone = clone($records);
      $pagination = Pagination::factory(array(
        'items_per_page' => 50,
        'total_items' => $clone->find_all()->count()));

      $records = $records
        ->offset($pagination->offset)
        ->limit($pagination->items_per_page);
      if ($sort = $this->request->query('sort')) $records->order_by($sort);
      $records = $records->order_by('status')
        ->find_all()
        ->as_array();

      unset($info);
      if ($specs_barcode) {
        $sample = reset($records);
        $info['specs'] = array(
          'number'  => $sample->specs_number,
          'barcode' => $sample->specs_barcode->barcode
        );
      }
      if ($wb_barcode) {
        $sample = reset($records);
        $info['wb'] = array(
          'barcode' => $sample->wb_barcode->barcode
        );
      }

      $operator = ORM::factory('operator', $operator_id ?: NULL);
      $site     = ORM::factory('site', $site_id ?: NULL);
      $block    = ORM::factory('block', $block_id ?: NULL);

      $header = View::factory('data')
        ->set('form_type', $form_type)
        ->set('data', $records)
        ->set('operator', $operator->loaded() ? $operator : NULL)
        ->set('site', $site->loaded() ? $site : NULL)
        ->set('block', $block->loaded() ? $block : NULL)
        ->set('specs_info', $info ? array_filter((array) $info['specs']) : NULL)
        ->set('exp_info', $info ? array_filter((array) $info['exp']) : NULL)
        ->set('wb_info', $info ? array_filter((array) $info['wb']) : NULL)
        ->set('options', array(
          'table'   => FALSE,
          'rows'    => FALSE,
          'actions' => FALSE,
          'header'  => TRUE,
          'details' => FALSE,
          'links'   => FALSE
        ))
        ->render();

      $report = View::factory('reports/checks_summary')
        ->set('from', $from)
        ->set('to', $to)
        ->set('form_type', $form_type)
        ->set('report', $data)
        ->set('checks', $_checks)
        ->render();

      $table = View::factory('data')
        ->set('classes', array('has-pagination'))
        ->set('form_type', $form_type)
        ->set('data', $records)
        ->set('operator', $operator->loaded() ? $operator : NULL)
        ->set('site', $site->loaded() ? $site : NULL)
        ->set('block', $block->loaded() ? $block : NULL)
        ->set('specs_info', $info ? array_filter((array) $info['specs']) : NULL)
        ->set('exp_info', $info ? array_filter((array) $info['exp']) : NULL)
        ->set('wb_info', $info ? array_filter((array) $info['wb']) : NULL)
        ->set('options', array(
          'hide_header_info' => TRUE,
          'header'  => FALSE,
          'details' => TRUE,
        ))
        ->render();
    }

    if ($form)   $content .= $form;
    if ($header) $content .= $header;
    if ($report) $content .= $report;
    if ($table)  {
      $content .= $table;
      $content .= $pagination;
    }

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  public function action_index() {
    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  public function action_review() {
    $form_type = strtoupper($this->request->param('id')); // for now this is flipped
    $id        = $this->request->param('command'); // for now this is flipped
    $command   = $this->request->param('subcommand');

    if ($form_type) switch ($command) {
      case 'delete': return self::handle_data_delete($form_type, $id);
      case 'edit': return self::handle_data_edit($form_type, $id);
      case 'check': return self::handle_data_check($form_type, $id);
      case 'hierarchy': return self::handle_data_hierarchy($form_type, $id);
      case 'revisions': return self::handle_data_revisions($form_type, $id);
      case 'list':
      default: return self::handle_data_list($form_type, $id);
    }

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  public function action_checks() {
    $form_type = strtoupper($this->request->param('id'));
    $id        = $this->request->param('command');

    if (in_array($form_type, array_keys(SGS::$form_data_type))) return self::handle_checks($form_type);
    else if (in_array($form_type, array_keys(SGS::$form_verification_type))) return self::handle_verify($form_type);

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  public function action_verify() {
    $form_type = strtoupper($this->request->param('id'));
    $id        = $this->request->param('command');

    if (in_array($form_type, array_keys(SGS::$form_data_type))) return self::handle_checks($form_type);
    else if (in_array($form_type, array_keys(SGS::$form_verification_type))) return self::handle_verify($form_type);

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  public function action_download() {
    $form_type = strtoupper($this->request->param('id'));

    switch ($form_type) {
      case 'SSF':
      case 'TDF':
      case 'LDF':
      case 'SPECS':
      case 'EPR':
      case 'WB':
        break;

      default:
        $view = View::factory('main')->set('content', $content);
        $this->response->body($view);
        return;
    }

    set_time_limit(0);

    $model = ORM::factory($form_type);

    $has_block_id   = (bool) (in_array($form_type, array('SSF', 'TDF')));
    $has_site_id    = (bool) (in_array($form_type, array('SSF', 'TDF', 'LDF')));
    $has_specs_info = (bool) (in_array($form_type, array('SPECS')));
    $has_exp_info   = (bool) (in_array($form_type, array('SPECS')));
    $has_wb_info    = (bool) (in_array($form_type, array('WB')));

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

    if ($has_site_id and $has_block_id) $block_ids = DB::select('id', 'name')
      ->from('blocks')
      ->order_by('name')
      ->execute()
      ->as_array('id', 'name');

    $check_options = array();
    foreach ($model::$checks as $type => $info) $check_options[$type] = $info['title'];

    $form = Formo::form();
    if ($has_site_id) $form = $form->add_group('site_id', 'select', $site_ids, NULL, array('required' => TRUE, 'label' => 'Site', 'attr' => array('class' => 'siteopts')));
    else $form = $form->add_group('operator_id', 'select', $operator_ids, NULL, array_merge(array('required' => TRUE, 'label' => 'Operator'), $has_specs_info ? array('attr' => array('class' => 'specs_operatoropts specs_barcode')) : array(), $has_wb_info ? array('attr' => array('class' => 'wb_operatoropts wb_barcode')) : array()));
    if ($has_site_id and $has_block_id) $form = $form->add_group('block_id', 'select', array(), NULL, array('label' => 'Block', 'attr' => array('class' => 'blockopts')));
    if ($has_specs_info) $form = $form->add_group('specs_barcode', 'select', array(), NULL, array('required' => TRUE, 'label' => 'Shipment Specification', 'attr' => array('class' => 'specsopts')));
    if ($has_wb_info) $form = $form->add_group('wb_barcode', 'select', array(), NULL, array('required' => TRUE, 'label' => 'Waybill', 'attr' => array('class' => 'wbopts')));

    if (!$has_specs_info and !$has_exp_info and !$has_wb_info) {
      $form = $form
        ->add('from', 'input', array('label' => 'From', 'attr' => array('class' => 'dpicker', 'id' => 'from-dpicker')))
        ->add('to', 'input', array('label' => 'To', 'attr' => array('class' => 'dpicker', 'id' => 'to-dpicker')));
    }

    $form = $form
      ->add_group('status', 'checkboxes', in_array($form_type, array_keys(SGS::$form_verification_type)) ? SGS::$verification_status : SGS::$data_status, array('A'), array('label' => 'Status'))
      ->add_group('errors', 'checkboxes', $check_options, NULL, array('label' => 'Errors'))
      ->add('type', 'radios', 'xls', array(
        'options' => array(
          'xls' => SGS::$file_type['xls'],
          'csv' => SGS::$file_type['csv']
        ),
        'label'    => 'Format',
        'required' => TRUE
      ))
      ->add('download', 'submit', array('label' => 'Download'));

    if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
      if ($has_site_id) $site_id = $form->site_id->val();
      else $operator_id = $form->operator_id->val();
      if ($has_site_id and $has_block_id) $block_id = $form->block_id->val();
      if ($has_specs_info) $specs_barcode = $form->specs_barcode->val();
      if ($has_wb_info) $wb_barcode = $form->wb_barcode->val();

      if (!$has_specs_info and !$has_exp_info and !$has_wb_info) {
        $from = $form->from->val();
        $to   = $form->to->val();
      }

      $status = $form->status->val();
      $errors = $form->errors->val();
      $type   = $form->type->val();

      $data_ids = DB::select($model->table_name().'.id')->from($model->table_name());

      if ($has_site_id and $site_id) $data_ids->where('site_id', 'IN', (array) $site_id);
      else $data_ids->where('operator_id', 'IN', (array) $operator_id);
      if ($has_site_id and $has_block_id and $block_id) $data_ids->where('block_id', 'IN', (array) $block_id);

      if ($has_specs_info and $specs_barcode) $data_ids->and_where('specs_barcode_id', '=', SGS::lookup_barcode($specs_barcode, NULL, TRUE));
      else if ($has_wb_info and $wb_barcode) $data_ids->and_where('wb_barcode_id', '=', SGS::lookup_barcode($wb_barcode, NULL, TRUE));
      else $data_ids->and_where('create_date', 'BETWEEN', SGS::db_range($from, $to));

      if ($errors) {
        foreach ($model::$checks as $typ => $inf) if (in_array($typ, $errors))
          foreach ($inf['checks'] as $check => $array) $checks[] = $check;

        if ($checks) $data_ids->join('checks')
          ->distinct(TRUE)
          ->on($model->table_name().'.id', '=', 'checks.form_data_id')
          ->on('checks.form_type', '=', DB::expr("'".$form_type."'"))
          ->and_where_open()
          ->and_where('checks.check', 'IN', (array) $checks)
          ->and_where('checks.type', '=', 'E')
          ->and_where_close();
      }

      if ($status) $data_ids->and_where('status', 'IN', (array) $status);
      $data_ids = $data_ids
        ->execute()
        ->as_array(NULL, 'id');

      if ($data_ids) switch ($type) {
        case 'csv':
          $excel = new PHPExcel();
          $excel->setActiveSheetIndex(0);
          $writer = new PHPExcel_Writer_CSV($excel);
          $headers = TRUE;
          $mime_type = 'text/csv';
          break;
        case 'xls':
          $filename = APPPATH.'/templates/'.$form_type.'.xls';
          try {
            $reader = new PHPExcel_Reader_Excel5;
            if (!$reader->canRead($filename)) $reader = PHPExcel_IOFactory::createReaderForFile($filename);
            $excel = $reader->load($filename);
          } catch (Exception $e) {
            Notify::msg('Unable to load Excel document template. Please try again.', 'error');
          }
          $excel->setActiveSheetIndex(0);
          $writer = new PHPExcel_Writer_Excel5($excel);
          $mime_type = 'application/vnd.ms-excel';
          $headers = FALSE;
          break;
      } else Notify::msg('No data found. Skipping download.', 'warning');

      if ($excel) {
        // data
        $create_date = 0;
        $row = $model::PARSE_START;
        foreach ($data_ids as $data_id) {
          $item = ORM::factory($form_type, $data_id);
          $item->export_data($excel, $row);
          if (strtotime($item->create_date) > strtotime($create_date)) $create_date = $item->create_date;
          $row++;
        }

        // headers
        $item->export_headers($excel, array(
          'create_date' => $create_date = $create_date ?: SGS::date('now', SGS::PGSQL_DATE_FORMAT)
        ), $headers);

        // temporary file
        $tempname = tempnam(sys_get_temp_dir(), strtolower($form_type).'_').'.'.$type;
        $writer->save($tempname);

        // send existing file if possible
        $content_md5 = md5_file($tempname);
        $file = ORM::factory('file')
          ->where('content_md5', '=', $content_md5)
          ->find();
        if ($file->loaded()) $this->response->send_file(preg_replace('/\/$/', '', DOCROOT).$file->path, $file->name, array('mime_type' => $mime_type));

        // info
        if ($operator_id) $operator = ORM::factory('operator', $operator_id);
        if ($site_id) {
          $site = ORM::factory ('site', $site_id);
          if (!$operator) $operator = $site->operator;
        }
        if ($block_id) $block = ORM::factory('block', $block_id);

        // properties
        try {
          $ext = $type;
          switch ($form_type) {
            case 'SSF':
              $newdir = implode(DIRECTORY_SEPARATOR, array(
                'downloads',
                $site->name,
                $form_type,
                $block->name
              ));
              if (!($operator->name and $site->name)) {
                Notify::msg('Sorry, cannot identify required properties to create a file.', 'error');
                throw new Exception();
              }
              $newname = SGS::wordify($site->name.'_SSF_'.($block ? $block->name.'_' : '')).'.'.$ext;
              break;

            case 'TDF':
              $newdir = implode(DIRECTORY_SEPARATOR, array(
                'downloads',
                $site->name,
                $form_type,
                $block->name
              ));

              if (!($operator->name and $site->name)) {
                Notify::msg('Sorry, cannot identify required properties to create a file.', 'error');
                throw new Exception();
              }
              $newname = SGS::wordify($site->name.'_TDF_'.($block ? $block->name.'_' : '').SGS::date($create_date, 'm_d_Y')).'.'.$ext;
              break;

            case 'LDF':
              $newdir = implode(DIRECTORY_SEPARATOR, array(
                'downloads',
                $site->name,
                $form_type
              ));
              if (!($operator->name and $site->name)) {
                Notify::msg('Sorry, cannot identify required properties to create a file.', 'error');
                throw new Exception();
              }
              $newname = SGS::wordify($site->name.'_LDF_'.SGS::date($create_date, 'm_d_Y')).'.'.$ext;
              break;

            case 'SPECS':
              $newdir = implode(DIRECTORY_SEPARATOR, array(
                'downloads',
                'specs',
                $operator->tin
              ));
              if (!($operator->name)) {
                Notify::msg('Sorry, cannot identify required properties to create a file.', 'error');
                throw new Exception();
              }
              $newname = SGS::wordify('SPECS_'.$operator->name.'_'.SGS::date($create_date, 'm_d_Y')).'.'.$ext;
              break;

            case 'WB':
              $newdir = implode(DIRECTORY_SEPARATOR, array(
                'downloads',
                'wb',
                $operator->tin
              ));
              if (!($operator->name)) {
                Notify::msg('Sorry, cannot identify required properties to create a file.', 'error');
                throw new Exception();
              }
              $newname = SGS::wordify('WB_'.$operator->name.'_'.SGS::date($create_date, 'm_d_Y')).'.'.$ext;
              break;
          }

          $version = 0;
          $testname = $newname;
          while (file_exists(DOCPATH.$newdir.DIRECTORY_SEPARATOR.$newname)) {
            $newname = substr($testname, 0, strrpos($testname, '.'.$ext)).'_'.($version++).'.'.$ext;
          }

          if (!is_dir(DOCPATH.$newdir) and !mkdir(DOCPATH.$newdir, 0777, TRUE)) {
            Notify::msg('Sorry, cannot access documents folder. Check file access capabilities with the site administrator and try again.', 'error');
            throw new Exception();
          }
          else if (!(rename($tempname, DOCPATH.$newdir.DIRECTORY_SEPARATOR.$newname) and chmod(DOCPATH.$newdir.DIRECTORY_SEPARATOR.$newname, 0777))) {
            Notify::msg('Sorry, cannot create document. Check file operation capabilities with the site administrator and try again.', 'error');
            throw new Exception();
          }
        } catch (Exception $e) {
          Notify::msg('Sorry, unable to create file.', 'error');
        }

        try {
          $file = ORM::factory('file');
          $file->name = $testname;
          $file->type = $mime_type;
          $file->size = filesize(DOCPATH.$newdir.DIRECTORY_SEPARATOR.$newname);
          $file->operation      = 'D';
          $file->operation_type = $form_type;
          $file->content_md5    = md5_file(DOCPATH.$newdir.DIRECTORY_SEPARATOR.$newname);
          $file->path           = DIRECTORY_SEPARATOR.str_replace(DOCROOT, '', DOCPATH).$newdir.DIRECTORY_SEPARATOR.$newname;

          if ($operator) $file->operator = $operator;
          if ($site)     $file->site     = $site;
          if ($block)    $file->block    = $block;

          $file->save();
          $this->response->send_file(preg_replace('/\/$/', '', DOCROOT).$file->path, $file->name, array('mime_type' => $mime_type));
        } catch (ORM_Validation_Exception $e) {
          foreach ($e->errors('') as $err) Notify::msg(SGS::errorify($err), 'error');
        }
      } else if ($data_ids) Notify::msg('Sorry, unable to process download. Please try again.', 'error');
    }

    $content .= $form->render();

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  public function action_migrate() {
    $migration_type = $this->request->param('id');

    if (in_array($migration_type, array_keys(SGS::$migration_type))) {
      set_time_limit(0);

      $operator_ids = db::select('id', 'name')
        ->from('operators')
        ->order_by('name')
        ->execute()
        ->as_array('id', 'name');

      $site_ids = db::select('id', 'name')
        ->from('sites')
        ->order_by('name')
        ->execute()
        ->as_array('id', 'name');

      $species_ids = db::select('id', 'code')
        ->from('species')
        ->order_by('code')
        ->execute()
        ->as_array('id', 'code');

      //    $block_ids = db::select('id', 'name')
      //      ->from('blocks')
      //      ->order_by('name')
      //      ->execute()
      //      ->as_array('id', 'name');

      $form = Formo::form()
        ->add_group('operator_id', 'select', $operator_ids, NULL, array_merge(array('label' => 'Operator'), array('attr' => array('class' => 'site_operatoropts'))))
        ->add_group('site_id', 'select', $site_ids, NULL, array('label' => 'Site', 'attr' => array('class' => 'siteopts')))
        ->add_group('block_id', 'select', array(), NULL, array('label' => 'Block', 'attr' => array('class' => 'blockopts')))
        ->add_group('species_id', 'select', $species_ids, NULL, array('label' => 'Species'))
        ->add_group('status', 'checkboxes', in_array($migration_type, array_keys(SGS::$form_verification_type)) ? SGS::$verification_status : SGS::$data_status, NULL, array('label' => 'Status'))
        ->add('from', 'input', array('label' => 'From', 'attr' => array('class' => 'dpicker', 'id' => 'from-dpicker')))
        ->add('to', 'input', array('label' => 'To', 'attr' => array('class' => 'dpicker', 'id' => 'to-dpicker')))
        ->add('download', 'submit', array('label' => 'Download CSV'));

      if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
        $operator_id = $form->operator_id->val();
        $site_id = $form->site_id->val();
        $block_id = $form->block_id->val();
        $species_id = $form->species_id->val();

        $from = $form->from->val() ? SGS::date($form->from->val(), SGS::PGSQL_DATE_FORMAT) : '-infinity';
        $to   = $form->to->val() ? SGS::date($form->to->val(), SGS::PGSQL_DATE_FORMAT) : 'infinity';
        $status = $form->status->val();

        switch ($migration_type) {
          case 'barcodes':
            $sql = <<<EOD
select barcodes.id, barcode, (case
  when barcodes.type = 'P' then 'Draft'
  when barcodes.type = 'P' then 'Pending'
  when barcodes.type = 'T' then 'Standing Tree'
  when barcodes.type = 'F' then 'Felled Tree'
  when barcodes.type = 'S' then 'Stump'
  when barcodes.type = 'L' then 'Log'
  when barcodes.type = 'R' then 'Sawnmill Timber'
  when barcodes.type = 'H' then 'Shipment Specification'
  when barcodes.type = 'E' then 'Export Permit'
  when barcodes.type = 'W' then 'Waybill'
end) as type,
sites.id as site_id, sites.name as site,
operators.id as operator_id, operators.name as operator from barcodes
left outer join printjobs on barcodes.printjob_id = printjobs.id
left outer join sites on printjobs.site_id = sites.id
left outer join operators on sites.operator_id = operators.id
where true = true
EOD;
            if ($operator_id) $sql .= ' and operators.id in ('.implode(',', (array) $operator_id).') ';
            if ($site_id) $sql .= ' and sites.id in ('.implode(',', (array) $site_id).') ';
            if ($from and $to) $sql .= " and barcodes.timestamp between '$from' and '$to' ";
            $sql .= 'order by barcode;';
            break;

          case 'operators':
            $sql = <<<EOD
select operators.id, name, tin, contact, address, email, phone from operators
where true = true
EOD;
            if ($operator_id) $sql .= ' and operators.id in ('.implode(',', (array) $operator_id).') ';
            if ($from and $to) $sql .= " and operators.timestamp between '$from' and '$to' ";
            $sql .= 'order by name;';
            break;

          case 'sites':
            $sql = <<<EOD
select sites.id, sites.name, operators.name as operator, sites.operator_id from sites
join operators on sites.operator_id = operators.id
where true = true
EOD;
            if ($operator_id) $sql .= ' and operators.id in ('.implode(',', (array) $operator_id).') ';
            if ($site_id) $sql .= ' and sites.id in ('.implode(',', (array) $site_id).') ';
            if ($from and $to) $sql .= " and sites.timestamp between '$from' and '$to' ";
            $sql .= 'order by operator, sites.name;';
            break;

          case 'blocks':
            $sql = <<<EOD
select blocks.id, blocks.name, sites.name as site, blocks.site_id, operators.name as operator, sites.operator_id from blocks
join sites on blocks.site_id = sites.id
join operators on sites.operator_id = operators.id
where true = true
EOD;
            if ($operator_id) $sql .= ' and operators.id in ('.implode(',', (array) $operator_id).') ';
            if ($site_id) $sql .= ' and sites.id in ('.implode(',', (array) $site_id).') ';
            if ($block_id) $sql .= ' and blocks.id in ('.implode(',', (array) $block_id).') ';
            if ($from and $to) $sql .= " and blocks.timestamp between '$from' and '$to' ";
            $sql .= 'order by operator, site, blocks.name;';
            break;

          case 'species':
            $sql = <<<EOD
select id, code, class, botanic_name, trade_name, fob_price_low, fob_price_high from species
order by code;
EOD;
            break;

          case 'ssf':
            $sql = <<<EOD
select ssf_data.id, blocks.name as block, ssf_data.block_id, sites.name as site, ssf_data.site_id, operators.name as operator, ssf_data.operator_id, barcodes.barcode, ssf_data.barcode_id, species.code as species, survey_line, cell_number, tree_map_number, diameter, height, volume from ssf_data
join blocks on ssf_data.block_id = blocks.id
join sites on ssf_data.site_id = sites.id
join operators on ssf_data.operator_id = operators.id
join barcodes on ssf_data.barcode_id = barcodes.id
join species on ssf_data.species_id = species.id
where true = true
EOD;
            if ($operator_id) $sql .= ' and operators.id in ('.implode(',', (array) $operator_id).') ';
            if ($site_id) $sql .= ' and sites.id in ('.implode(',', (array) $site_id).') ';
            if ($block_id) $sql .= ' and blocks.id in ('.implode(',', (array) $block_id).') ';
            if ($species_id) $sql .= ' and species.id in ('.implode(',', (array) $species_id).') ';
            if ($from and $to) $sql .= " and ssf_data.timestamp between '$from' and '$to' ";
            $sql .= 'order by operator, site, block, cell_number, survey_line, tree_map_number, barcode;';
            break;

          case 'tdf':
            $sql = <<<EOD
select tdf_data.id, blocks.name as block, tdf_data.block_id, sites.name as site, tdf_data.site_id, operators.name as operator, tdf_data.operator_id, barcodes.barcode, tdf_data.barcode_id, tree_barcodes.barcode as tree_barcode, tdf_data.tree_barcode_id, stump_barcodes.barcode as stump_barcode, tdf_data.stump_barcode_id, species.code as species, survey_line, cell_number, top_min, top_max, bottom_min, bottom_max, length, volume from tdf_data
join blocks on tdf_data.block_id = blocks.id
join sites on tdf_data.site_id = sites.id
join operators on tdf_data.operator_id = operators.id
join barcodes on tdf_data.barcode_id = barcodes.id
join barcodes as tree_barcodes on tdf_data.tree_barcode_id = tree_barcodes.id
join barcodes as stump_barcodes on tdf_data.stump_barcode_id = stump_barcodes.id
join species on tdf_data.species_id = species.id
where true = true
EOD;
            if ($operator_id) $sql .= ' and operators.id in ('.implode(',', (array) $operator_id).') ';
            if ($site_id) $sql .= ' and sites.id in ('.implode(',', (array) $site_id).') ';
            if ($block_id) $sql .= ' and blocks.id in ('.implode(',', (array) $block_id).') ';
            if ($species_id) $sql .= ' and species.id in ('.implode(',', (array) $species_id).') ';
            if ($from and $to) $sql .= " and tdf_data.timestamp between '$from' and '$to' ";
            $sql .= 'order by operator, site, block, cell_number, survey_line, barcode;';
            break;

          case 'ldf':
            $sql = <<<EOD
select ldf_data.id, sites.name as site, ldf_data.site_id, operators.name as operator, ldf_data.operator_id, barcodes.barcode, ldf_data.barcode_id, parent_barcodes.barcode as parent_barcode, ldf_data.parent_barcode_id, species.code as species, top_min, top_max, bottom_min, bottom_max, length, volume from ldf_data
join sites on ldf_data.site_id = sites.id
join operators on ldf_data.operator_id = operators.id
join barcodes on ldf_data.barcode_id = barcodes.id
join barcodes as parent_barcodes on ldf_data.parent_barcode_id = parent_barcodes.id
join species on ldf_data.species_id = species.id
where true = true
EOD;
            if ($operator_id) $sql .= ' and operators.id in ('.implode(',', (array) $operator_id).') ';
            if ($site_id) $sql .= ' and sites.id in ('.implode(',', (array) $site_id).') ';
            if ($species_id) $sql .= ' and species.id in ('.implode(',', (array) $species_id).') ';
            if ($from and $to) $sql .= " and ldf_data.timestamp between '$from' and '$to' ";
            $sql .= 'order by operator, site, barcode;';
            break;

          case 'specs':
            $sql = <<<EOD
select specs_data.id, operators.name as operator, specs_data.operator_id, barcodes.barcode, specs_data.barcode_id, specs_barcodes.barcode as specs_barcode, specs_data.specs_barcode_id, species.code as species, top_min, top_max, bottom_min, bottom_max, length, volume from specs_data
join operators on specs_data.operator_id = operators.id
join barcodes on specs_data.barcode_id = barcodes.id
join barcodes as specs_barcodes on specs_data.specs_barcode_id = specs_barcodes.id
join species on specs_data.species_id = species.id
where true = true
EOD;
            if ($operator_id) $sql .= ' and operators.id in ('.implode(',', (array) $operator_id).') ';
            if ($site_id) $sql .= ' and sites.id in ('.implode(',', (array) $site_id).') ';
            if ($species_id) $sql .= ' and species.id in ('.implode(',', (array) $species_id).') ';
            if ($from and $to) $sql .= " and specs_data.timestamp between '$from' and '$to' ";
            $sql .= 'order by operator, barcode;';
            break;

          case 'documents':
            $sql = <<<EOD
select
  documents.id as document_id,
  sites.name as site,
  operators.name as operator,
  documents.operator_id,
  documents.type,
  documents.type || ' ' || lpad(documents.number::text, 6, '0') as number,
  (case
    when documents.is_draft then 'Draft'
    else 'Final'
  end) as status,
  documents.created_date::date as date
from documents
left outer join sites on documents.site_id = sites.id
left outer join operators on documents.operator_id = operators.id
where true = true
EOD;
            if ($operator_id) $sql .= ' and operators.id in ('.implode(',', (array) $operator_id).') ';
            if ($site_id) $sql .= ' and sites.id in ('.implode(',', (array) $site_id).') ';
            if ($from and $to) $sql .= " and documents.timestamp between '$from' and '$to' ";
            $sql .= 'order by operator, site, number;';
            break;

          case 'invoices':
            $sql = <<<EOD
select
  invoices.id as invoice_id,
  sites.name as site,
  invoices.site_id,
  operators.name as operator,
  invoices.operator_id,
  invoices.type,
  invoices.type || ' ' || lpad(invoices.number::text, 6, '0') as number,
  invoices.invnumber as invoice_number,
  (case
    when invoices.is_draft then 'Draft'
    else 'Final'
  end) as status,
  (case
    when invoices.is_paid then 'Paid'
    else 'Not Paid'
  end) as paid_status,
  invoices.created_date::date as date
from invoices
left outer join sites on invoices.site_id = sites.id
left outer join operators on invoices.operator_id = operators.id
where true = true
EOD;
            if ($operator_id) $sql .= ' and operators.id in ('.implode(',', (array) $operator_id).') ';
            if ($site_id) $sql .= ' and sites.id in ('.implode(',', (array) $site_id).') ';
            if ($from and $to) $sql .= " and invoices.timestamp between '$from' and '$to' ";
            $sql .= 'order by operator, site, number;';
            break;
        }

        if ($sql) {
          $result = DB::query(Database::SELECT, $sql)->execute();
          if ($result) {
            $ext = 'csv';
            $excel = new PHPExcel();
            $excel->setActiveSheetIndex(0);
            $excel->getActiveSheet()->fromArray(array_keys($result[0]), NULL, 'A1');
            $row_count = 2;
            foreach ($result as $row) $excel->getActiveSheet()->fromArray($row, NULL, 'A'.$row_count++);
            $mime_type = 'text/csv';

            $tempname = tempnam(sys_get_temp_dir(), $migration_type . '_migration');

            $writer = new PHPExcel_Writer_CSV($excel);
            $writer->save($tempname);

            $this->response->send_file($tempname, strtoupper($migration_type.'_MIGRATION.'). $ext, array('mime_type' => $mime_type, 'delete' => TRUE));
          } else Notify::msg('Sorry, no data found for migration.', 'warning');
        }
      }

      $content .= $form->render();
    }

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

}
