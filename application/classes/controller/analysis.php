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

  private function download_checks_report($form_type, $records, $info = array()) {
    if (!$records) {
      Notify::msg('No data found. Unable to generate document.', 'warning');
      return FALSE;
    }

    $passed_records = array();
    $failed_records = array();

    foreach ($records as $record) {
      if ($record->status == 'A') $passed_records[] = $record;
      else $failed_records[] = $record;
    }

    extract($info);

    $html .= View::factory('documents/checks')
      ->set('form_type', $form_type)
      ->set('report', $report)
      ->set('checks', $checks)
      ->set('operator', $operator)
      ->set('site', $site)
      ->set('block', $block)
      ->set('specs_info', $specs_info)
      ->set('exp_info', $exp_info)
      ->set('options', array(
        'info'     => TRUE,
        'summary'  => TRUE,
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
      $html .= View::factory('documents/checks')
        ->set('form_type', $form_type)
        ->set('report', $report)
        ->set('checks', $checks)
        ->set('operator', $operator)
        ->set('site', $site)
        ->set('specs_info', $specs_info)
        ->set('block', $block)
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
      $html .= View::factory('documents/checks')
        ->set('form_type', $form_type)
        ->set('report', $report)
        ->set('checks', $checks)
        ->set('operator', $operator)
        ->set('site', $site)
        ->set('specs_info', $specs_info)
        ->set('block', $block)
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

    // generate pdf
    set_time_limit(600);

    // save file
    $ext = 'pdf';
    $newdir = implode(DIRECTORY_SEPARATOR, array(
      'checks',
    ));

    switch ($form_type) {
      case 'SSF':
        $newname = SGS::wordify('SSF_'.$site->name.'_'.$block->name.'_'.SGS::date('now', 'm_d_Y')).'.'.$ext;
        break;

      case 'TDF':
        $newname = SGS::wordify('TDF_'.$site->name.'_'.$block->name.'_'.SGS::date('now', 'm_d_Y')).'.'.$ext;
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
        'footer-html' => View::factory('documents/checks')
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

  private function handle_data_list($form_type, $id = NULL, $command = NULL) {
    if (!Request::$current->query()) Session::instance()->delete('pagination.data');

    $has_block_id   = (bool) (in_array($form_type, array('SSF', 'TDF')));
    $has_site_id    = (bool) (in_array($form_type, array('SSF', 'TDF', 'LDF')));
    $has_specs_info = (bool) (in_array($form_type, array('SPECS')));
    $has_exp_info   = (bool) (in_array($form_type, array('SPECS')));

    if ($id) {
      Session::instance()->delete('pagination.data');
      $item = ORM::factory($form_type, $id);

      if (isset($item->site)) $site = $item->site;
      else if (isset($item->operator)) $operator = $item->operator;

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

      $form = Formo::form();
      if ($has_site_id)  $form = $form->add_group('site_id', 'select', $site_ids, NULL, array('label' => 'Site', 'attr' => array('class' => 'siteopts')));
      else $form = $form->add_group('operator_id', 'select', $operator_ids, NULL, array_merge(array('label' => 'Operator', ), $has_specs_info ? array('attr' => array('class' => 'specs_operatoropts')) : array()));
      if ($has_site_id and $has_block_id) $form = $form->add_group('block_id', 'select', array(), NULL, array('label' => 'Block', 'attr' => array('class' => 'blockopts')));
      if ($has_specs_info) $form = $form->add_group('specs_info', 'select', array(), NULL, array('required' => TRUE, 'label' => 'Shipment Specification', 'attr' => array('class' => 'specsopts')));
      $form = $form
        ->add_group('status', 'checkboxes', SGS::$data_status, NULL, array('label' => 'Status'))
        ->add('search', 'submit', 'Filter');

      if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
        Session::instance()->delete('pagination.data');
        if ($has_site_id) $site_id  = $form->site_id->val();
        else $operator_id = $form->operator_id->val();
        if ($has_site_id and $has_block_id) $block_id = $form->block_id->val();
        if ($has_specs_info) $specs_info = $form->specs_info->val();

        $status = $form->status->val();

        $data = ORM::factory($form_type);

        if ($site_id)     $data->and_where('site_id', 'IN', (array) $site_id);
        if ($operator_id) $data->and_where('operator_id', 'IN', (array) $operator_id);
        if ($block_id)    $data->and_where('block_id', 'IN', (array) $block_id);
        if ($status)      $data->and_where('status', 'IN', (array) $status);

        if (Valid::is_barcode($specs_info))   $data->and_where('specs_barcode_id', '=', SGS::lookup_barcode($specs_info, NULL, TRUE));
        else if (Valid::numeric($specs_info)) $data->and_where('specs_id', '=', SGS::lookup_specs($specs_info, TRUE));

        Session::instance()->set('pagination.data', array(
          'site_id'     => $site_id,
          'operator_id' => $operator_id,
          'block_id'    => $block_id,
          'specs_info'  => $specs_info,
          'status'      => $status,
        ));
      }
      else {
        if ($settings = Session::instance()->get('pagination.data')) {
          if ($has_site_id) $form->site_id->val($site_id = $settings['site_id']);
          else $form->operator_id->val($operator_id = $settings['operator_id']);
          if ($has_site_id and $has_block_id) $form->block_id->val($block_id = $settings['block_id']);
          if ($has_specs_info) $form->specs_info->val($specs_info = $settings['specs_info']);

          $form->status->val($block_id = $settings['block_id']);
        }

        $data = ORM::factory($form_type);

        if ($site_id)     $data->and_where('site_id', 'IN', (array) $site_id);
        if ($operator_id) $data->and_where('operator_id', 'IN', (array) $operator_id);
        if ($block_id)    $data->and_where('block_id', 'IN', (array) $block_id);
        if ($status)      $data->and_where('status', 'IN', (array) $status);

        if (Valid::is_barcode($specs_info))   $data->and_where('specs_barcode_id', '=', SGS::lookup_barcode($specs_info, NULL, TRUE));
        else if (Valid::numeric($specs_info)) $data->and_where('specs_id', '=', SGS::lookup_specs($specs_info, TRUE));
      }

      if ($data) {
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
        ->set('form_type', $form_type)
        ->set('data', $data)
        ->set('operator', $operator->loaded() ? $operator : NULL)
        ->set('site', $site->loaded() ? $site : NULL)
        ->set('block', $block->loaded() ? $block : NULL)
        ->set('specs_info', $info ? array_filter((array) $info['specs']) : NULL)
        ->set('exp_info', $info ? array_filter((array) $info['exp']) : NULL)
        ->render();
    }

    if ($form) $content .= $form->render();
    $content .= $table;
    $content .= $pagination;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  private function handle_data_edit($form_type, $id) {
    $item  = ORM::factory($form_type, $id);

    $form = Formo::form(array('attr' => array('style' => ($id or $_POST) ? '' : 'display: none;')))
      ->orm('load', $item)
      ->add('save', 'submit', array(
        'label' => 'Save'
    ));

    if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
      try {
        $item->status = 'P';
        $item->save();
        $item->reload();
        Notify::msg('Form data saved.', 'success', TRUE);
      } catch (Exception $e) {
        Notify::msg('Sorry, form data update failed. Please try again.', 'error');
      }
    }

    unset($info);
    if ($form_type == 'SPECS') {
      $info['specs'] = array(
        'number'  => $item->specs_number,
        'barcode' => $item->specs_barcode->barcode
      );
      $info['exp'] = array(
        'number'  => $item->exp_number,
        'barcode' => $item->exp_barcode->barcode
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
      ->render();

    if ($form) $content .= $form->render();
    $content .= $table;
    $content .= $pagination;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  private function handle_data_delete($form_type, $id) {
    $item  = ORM::factory($form_type, $id);

    if (!$item->loaded()) {
      Notify::msg('No form data found.', 'warning', TRUE);
      $this->request->redirect('analysis/review/'.strtolower($form_type));
    }

    $form = Formo::form()
      ->add('confirm', 'text', 'Are you sure you want to delete this form data?')
      ->add('delete', 'submit', 'Delete');

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

    $content .= $form->render();

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
    else $form = $form->add_group('operator_id', 'select', $operator_ids, NULL, array_merge(array('required' => TRUE, 'label' => 'Operator'), $has_specs_info ? array('attr' => array('class' => 'specs_operatoropts')) : array()));
    if ($has_site_id and $has_block_id) $form = $form->add_group('block_id', 'select', array(), NULL, array('label' => 'Block', 'attr' => array('class' => 'blockopts')));
    if ($has_specs_info) $form = $form->add_group('specs_info', 'select', array(), NULL, array('required' => TRUE, 'label' => 'Shipment Specification', 'attr' => array('class' => 'specsopts')));

    if (!$has_specs_info and !$has_exp_info) {
      $form = $form
        ->add('from', 'input', array('label' => 'From', 'attr' => array('class' => 'dpicker', 'id' => 'from-dpicker')))
        ->add('to', 'input', array('label' => 'To', 'attr' => array('class' => 'dpicker', 'id' => 'to-dpicker')));
    }

    $form = $form
      ->add_group('status', 'checkboxes', SGS::$data_status, array('P', 'R'), array('label' => 'Status'))
      ->add_group('display', 'checkboxes', SGS::$data_status, array('P', 'A', 'R'), array('label' => 'Display'))
      ->add_group('checks', 'checkboxes', $check_options, array_diff(array_keys($check_options), array('consistency', 'reliability')), array('label' => 'Check'))
      ->add('submit', 'submit', 'Run')
      ->add('download', 'submit', 'Download');

    if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
      Session::instance()->delete('pagination.checks');

      $download = isset($_POST['download']);

      if ($has_site_id) $site_id = $form->site_id->val();
      else $operator_id = $form->operator_id->val();
      if ($has_site_id and $has_block_id) $block_id = $form->block_id->val();
      if ($has_specs_info) $specs_info = $form->specs_info->val();

      if (!$has_specs_info and !$has_exp_info) {
        $from = $form->from->val();
        $to   = $form->to->val();
      }

      $status  = $form->status->val();
      $display = $form->display->val();
      $checks  = $form->checks->val();

      if ($download) {
        $checks  = array_diff(array_keys($check_options), array('consistency', 'reliability'));
        $status  = array('P', 'A', 'R');
        $display = array('P', 'A', 'R');
      }

      $rejected  = 0;
      $accepted  = 0;
      $unchecked = 0;
      $failure   = 0;

      $records = ORM::factory($form_type);
      if ($operator_id) $records = $records->where('operator_id', 'IN', (array) $operator_id);
      if ($site_id)     $records = $records->where('site_id', 'IN', (array) $site_id);
      if ($block_id)    $records = $records->and_where('block_id', 'IN', (array) $block_id);

      if (Valid::is_barcode($specs_info))   $records = $records->and_where('specs_barcode_id', '=', SGS::lookup_barcode($specs_info, NULL, TRUE));
      else if (Valid::numeric($specs_info)) $records = $records->and_where('specs_id', '=', SGS::lookup_specs($specs_info, TRUE));

      if (!$has_specs_info and !$has_exp_info) {
        $records = $records->and_where('create_date', 'BETWEEN', SGS::db_range($from, $to));
      }

      $records = $records
        ->and_where('status', 'IN', (array) $display)
        ->find_all()
        ->as_array('id');

      $data = array(
        'checks' => array(),
        'total'  => array(
          'checked'   => 0,
          'passed'    => 0,
          'failed'    => 0,
          'warned'    => 0,
          'unchecked' => 0
        )
      );

      $unable = 0;
      set_time_limit(600);
      foreach ($records as $record) {
        $errors   = array();
        $warnings = array();

        $total_checked = FALSE;
        if (in_array($record->status, (array) $status)) {
          $total_checked = TRUE;
          try {
            list($raw['errors'], $raw['warnings']) = $record->run_checks();
            foreach ($raw['errors'] as $re) $errors += array_keys($re);
            foreach ($raw['warnings'] as $rw) $warnings += array_keys($rw);
          } catch (ORM_Validation_Exception $e) {
            foreach ($e->errors('') as $err) Notify::msg(SGS::errorify($err), 'error', TRUE);
          } catch (Exception $e) {
            $unable++;
          }
        }

        if ($total_checked) {
          $total_warned = FALSE;
          foreach ($record::$checks as $type => $info) {
            if (!in_array($type, $checks)) continue;
            foreach ($info['checks'] as $check => $array) {
              $check_warned = FALSE;
              if ($type == 'tolerance' and array_intersect(array_keys((array) $record::$checks['traceability']['checks']), $errors)) continue;
              $data['checks'][$type][$check]['checked']++;
              if (in_array($check, $errors)) $data['checks'][$type][$check]['failed']++;
              else {
                if (in_array($check, $warnings)) {
                  if (!$check_warned) {
                    $data['checks'][$type][$check]['warned']++;
                    $check_warned = TRUE;
                  }
                  if (!$toal_warned) $total_warned = TRUE;
                }
                $data['checks'][$type][$check]['passed']++;
              }
            }
          }

          switch ($record->status) {
            case 'A':
              $accepted++;
              $data['total']['checked']++;
              $data['total']['passed']++;
              break;

            case 'R':
              $rejected++;
              $data['total']['checked']++;
              $data['total']['failed']++;
              break;

            default:
              $unchecked++;
              $data['total']['unchecked']++;
              break;
          }

          if ($total_warned) $data['total']['warned']++;
        }

        $data['total']['records']++;

        try {
          $record->save();
        } catch (Exception $e) {
          $failure++;
        }
      }

      if ($unable)    Notify::msg('Sorry, unable to run checks and queries on '.$unable.' records. Please try again.', 'error', TRUE);
      if ($accepted)  Notify::msg($accepted.' records passed checks and queries.', 'success', TRUE);
      if ($rejected)  Notify::msg($rejected.' records failed checks and queries.', 'error', TRUE);
      if ($unchecked) Notify::msg($unchecked.' records unchecked.', 'warning', TRUE);
      if ($failure)   Notify::msg($failure.' records could not be accessed.', 'error', TRUE);

      Session::instance()->set('pagination.checks', array(
        'operator_id' => $operator_id,
        'site_id'     => $site_id,
        'block_id'    => $block_id,
        'specs_info'  => $specs_info,
        'status'      => $status,
        'display'     => $display,
        'checks'      => $checks,
        'form_type'   => $form_type,
        'from'        => $from,
        'to'          => $to,
        'data'        => $data,
      ));
    }
    else if ($settings = Session::instance()->get('pagination.checks')) {
      if ($has_site_id)  $form->site_id->val($site_id = $settings['site_id']);
      else $form->operator_id->val($operator_id = $settings['operator_id']);
      if ($has_site_id and $has_block_id) $form->block_id->val($block_id = $settings['block_id']);
      if ($has_specs_info) $form->specs_info->val($specs_info = $settings['specs_info']);

      $form->status->val($status = $settings['status']);
      $form->display->val($display = $settings['display']);
      $form->checks->val($checks = $settings['checks']);

      if (!$has_specs_info and !$has_exp_info) {
        $form->from->val($from = $settings['from']);
        $form->to->val($to = $settings['to']);
      }

      $data = $settings['data'];
    }

    if ($data) {
      $model  = ORM::factory($form_type);
      foreach ($model::$checks as $type => $info) {
        if (in_array($type, $checks)) $_checks[$type] = $info;
      }

      $_data = ORM::factory($form_type);
      if ($has_site_id and $site_id) $_data = $_data->where('site_id', 'IN', (array) $site_id);
      else if ($operator_id) $_data = $_data->where('operator_id', 'IN', (array) $operator_id);
      if ($has_site_id and $has_block_id and $block_id) $_data = $_data->and_where('block_id', 'IN', (array) $block_id);

      if (Valid::is_barcode($specs_info))   $_data = $_data->and_where('specs_barcode_id', '=', SGS::lookup_barcode($specs_info, NULL, TRUE));
      else if (Valid::numeric($specs_info)) $_data = $_data->and_where('specs_id', '=', SGS::lookup_specs($specs_info, TRUE));

      if (!$has_specs_info and !$has_exp_info)  $_data = $_data->and_where('create_date', 'BETWEEN', SGS::db_range($from, $to));

      $_data = $_data->and_where('status', 'IN', (array) $display);

      if ($download) {
        $_data = $_data
          ->join('barcodes')
          ->on('barcodes.id', '=', 'barcode_id')
          ->order_by('barcode')
          ->find_all()
          ->as_array();

        unset($info);
        if ($specs_info) {
          $sample = reset($_data);
          $info['specs'] = array(
            'number'  => $sample->specs_number,
            'barcode' => $sample->specs_barcode->barcode
          );
          if (Valid::numeric($specs_info)) $info['exp'] = array(
            'number'  => $sample->exp_number,
            'barcode' => $sample->exp_barcode->barcode
          );
        }

        self::download_checks_report($form_type, $_data, array(
          'specs_info' => $info ? array_filter((array) $info['specs']) : NULL,
          'exp_info'   => $info ? array_filter((array) $info['exp']) : NULL,
          'operator'   => $operator_id ? ORM::factory('operator', $operator_id) : NULL,
          'site'       => $site_id ? ORM::factory('site', $site_id) : NULL,
          'block'      => $block_id ? ORM::factory('block', $block_id) : NULL,
          'from'       => $from,
          'to'         => $to,
          'checks'     => $_checks,
          'report'     => $data
        ));
      }
      else {
        $clone = clone($_data);
        $pagination = Pagination::factory(array(
          'items_per_page' => 50,
          'total_items' => $clone->find_all()->count()));

        $_data = $_data
          ->offset($pagination->offset)
          ->limit($pagination->items_per_page);
        if ($sort = $this->request->query('sort')) $_data->order_by($sort);
        $_data = $_data->order_by('status')
          ->find_all()
          ->as_array();

        unset($info);
        if ($specs_info) {
          $sample = reset($_data);
          $info['specs'] = array(
            'number'  => $sample->specs_number,
            'barcode' => $sample->specs_barcode->barcode
          );
          if (Valid::numeric($specs_info)) $info['exp'] = array(
            'number'  => $sample->exp_number,
            'barcode' => $sample->exp_barcode->barcode
          );
        }

        $operator = ORM::factory('operator', $operator_id ?: NULL);
        $site     = ORM::factory('site', $site_id ?: NULL);
        $block    = ORM::factory('block', $block_id ?: NULL);

        $header = View::factory('data')
          ->set('form_type', $form_type)
          ->set('data', $_data)
          ->set('operator', $operator->loaded() ? $operator : NULL)
          ->set('site', $site->loaded() ? $site : NULL)
          ->set('block', $block->loaded() ? $block : NULL)
          ->set('specs_info', $info ? array_filter((array) $info['specs']) : NULL)
          ->set('exp_info', $info ? array_filter((array) $info['exp']) : NULL)
          ->set('options', array(
            'table'   => FALSE,
            'rows'    => FALSE,
            'actions' => FALSE,
            'header'  => TRUE,
            'details' => FALSE,
            'links'   => FALSE
          ))
          ->render();

        $report = View::factory('report')
          ->set('from', $from)
          ->set('to', $to)
          ->set('form_type', $form_type)
          ->set('report', $data)
          ->set('checks', $_checks)
          ->render();

        $table = View::factory('data')
          ->set('classes', array('has-pagination'))
          ->set('form_type', $form_type)
          ->set('data', $_data)
          ->set('operator', $operator->loaded() ? $operator : NULL)
          ->set('site', $site->loaded() ? $site : NULL)
          ->set('block', $block->loaded() ? $block : NULL)
          ->set('specs_info', $info ? array_filter((array) $info['specs']) : NULL)
          ->set('exp_info', $info ? array_filter((array) $info['exp']) : NULL)
          ->set('options', array(
            'hide_header_info' => TRUE,
            'header'  => FALSE,
            'details' => TRUE,
            'links'   => FALSE,
          ))
          ->render();
      }
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
      case 'list':
      default: return self::handle_data_list($form_type, $id);
    }

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  public function action_tolerances() {
    if (!Auth::instance()->logged_in('tolerances')) {
      Notify::msg('Access denied. You must have '.SGS::$roles['tolerances'].' privileges.', 'locked', TRUE);
      $this->request->redirect('analysis');
    }

    if ($values = $this->request->post()) {
      foreach ($values as $key => $value) {
        list($form_type, $check, $type) = explode('-', $key);
        try {
          DB::update('tolerances')
            ->set(array($type => (float) $value))
            ->where('form_type', '=', $form_type)
            ->and_where('check', '=', $check)
            ->execute();
        } catch (Database_Exception $e) {
          Notify::msg('Sorry, tolerance failed to be saved due to input. Please try again.', 'error');
        } catch (Exception $e) {
          Notify::msg('Sorry, tolerance failed to be updated. Please try again.', 'error');
        }
      }

      Notify::msg($success.'Tolerances updated.', 'success');
    }

    $content .= View::factory('tolerances');

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  public function action_checks() {
    $form_type = strtoupper($this->request->param('id'));
    $id        = $this->request->param('command');

    if ($form_type) return self::handle_checks($form_type);

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

}
