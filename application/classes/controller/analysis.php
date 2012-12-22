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

  private function handle_data_list($form_type, $id = NULL, $command = NULL) {
    if (!Request::$current->query()) Session::instance()->delete('pagination.data');

    $has_block_id = (bool) (in_array($form_type, array('SSF', 'TDF')));
    $has_site_id  = (bool) (in_array($form_type, array('SSF', 'TDF', 'LDF')));

    if ($id) {
      Session::instance()->delete('pagination.data');
      $item = ORM::factory($form_type, $id);
      $site = $item->site;
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

      if ($has_site_id and $has_block_id) $block_ids = DB::select('id', 'name')
        ->from('blocks')
        ->order_by('name')
        ->execute()
        ->as_array('id', 'name');

      $form = Formo::form();
      if ($has_site_id)  $form = $form->add_group('site_id', 'select', $site_ids, NULL, array('label' => 'Site', 'attr' => array('class' => 'siteopts')));
      else $form = $form->add_group('operator_id', 'select', $operator_ids, NULL, array('label' => 'Operator'));
      if ($has_site_id and $has_block_id) $form = $form->add_group('block_id', 'select', $block_ids, NULL, array('label' => 'Block', 'attr' => array('class' => 'blockopts')));
      $form = $form
        ->add_group('status', 'checkboxes', SGS::$data_status, NULL, array('label' => 'Status'))
        ->add('search', 'submit', 'Filter');

      if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
        Session::instance()->delete('pagination.data');
        if ($has_site_id)  $site_id  = $form->site_id->val();
        else $operator_id = $form->operator_id->val();
        if ($has_site_id and $has_block_id) $block_id = $form->block_id->val();
        $status   = $form->status->val();

        $data = ORM::factory($form_type);

        if ($site_id)     $data->and_where('site_id', 'IN', (array) $site_id);
        if ($operator_id) $data->and_where('operator_id', 'IN', (array) $operator_id);
        if ($block_id)    $data->and_where('block_id', 'IN', (array) $block_id);
        if ($status)      $data->and_where('status', 'IN', (array) $status);

        Session::instance()->set('pagination.data', array(
          'site_id'     => $site_id,
          'operator_id' => $operator_id,
          'block_id'    => $block_id,
          'status'      => $status,
        ));
      }
      else {
        if ($settings = Session::instance()->get('pagination.data')) {
          if ($has_site_id)  $form->site_id->val($site_id = $settings['site_id']);
          else $form->operator_id->val($operator_id = $settings['operator_id']);
          if ($has_site_id and $has_block_id) $form->block_id->val($block_id = $settings['block_id']);
          $form->status->val($block_id = $settings['block_id']);
        }

        $data = ORM::factory($form_type);

        if ($site_id)     $data->and_where('site_id', 'IN', (array) $site_id);
        if ($operator_id) $data->and_where('operator_id', 'IN', (array) $operator_id);
        if ($block_id)    $data->and_where('block_id', 'IN', (array) $block_id);
        if ($status)      $data->and_where('status', 'IN', (array) $status);
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

      $table = View::factory('data')
        ->set('classes', array('has-pagination'))
        ->set('form_type', $form_type)
        ->set('data', $data)
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

    $table = View::factory('data')
      ->set('classes', array('has-pagination'))
      ->set('form_type', $item::$type)
      ->set('data', array($item))
      ->set('site', isset($item->site) ? $item->site : NULL)
      ->set('block', isset($item->block) ? $item->block : NULL)
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

    $has_block_id = (bool) (in_array($form_type, array('SSF', 'TDF')));
    $has_site_id  = (bool) (in_array($form_type, array('SSF', 'TDF', 'LDF')));

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
    else $form = $form->add_group('operator_id', 'select', $operator_ids, NULL, array('label' => 'Operator'));
    if ($has_site_id and $has_block_id) $form = $form->add_group('block_id', 'select', array(), NULL, array('label' => 'Block', 'attr' => array('class' => 'blockopts')));
    $form = $form
      ->add('from', 'input', array('label' => 'From', 'attr' => array('class' => 'dpicker', 'id' => 'from-dpicker')))
      ->add('to', 'input', array('label' => 'To', 'attr' => array('class' => 'dpicker', 'id' => 'to-dpicker')))
      ->add_group('status', 'checkboxes', SGS::$data_status, array('P', 'R'), array('label' => 'Status'))
      ->add('submit', 'submit', 'Run');

    if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
      Session::instance()->delete('pagination.checks');
      if ($has_site_id) $site_id = $form->site_id->val();
      else $operator_id = $form->operator_id->val();
      if ($has_site_id and $has_block_id) $block_id = $form->block_id->val();

      $status    = $form->status->val();
      $from      = $form->from->val();
      $to        = $form->to->val();

      $rejected  = 0;
      $accepted  = 0;
      $unchecked = 0;
      $failure   = 0;

      $records = ORM::factory($form_type);
      if ($operator_id) $records  = $records->where('operator_id', 'IN', (array) $operator_id);
      if ($site_id)     $records  = $records->where('site_id', 'IN', (array) $site_id);
      if ($block_id)    $records = $records->and_where('block_id', 'IN', (array) $block_id);
      $records = $records
        ->and_where('create_date', 'BETWEEN', SGS::db_range($from, $to))
        ->and_where('status', 'IN', (array) $status)
        ->find_all()
        ->as_array('id');

      $data = array(
        'checks' => array(),
        'total'  => array(
          'checked'   => count($records),
          'passed'    => 0,
          'failed'    => 0,
          'warned'    => 0,
          'unchecked' => 0
        )
      );

      set_time_limit(600);
      foreach ($records as $record) {
        $errors   = array();
        $warnings = array();

        try {
          list($errors, $warnings) = $record->run_checks();
        } catch (ORM_Validation_Exception $e) {
          foreach ($e->errors('') as $err) Notify::msg(SGS::errorify($err), 'error', TRUE);
        } catch (Exception $e) {
          Notify::msg('Sorry, unable to run checks and queries. Please try again.', 'error');
        }

        $errors   = SGS::flattenify($errors);
        $warnings = SGS::flattenify($warnings);

        $check_warned = FALSE;
        $total_warned = FALSE;
        foreach ($record::$checks as $type => $info) {
          foreach ($info['checks'] as $check => $array) {
            if ($type == 'tolerance' and array_intersect(array_keys((array) $record::$checks['traceability']['checks']), $errors)) continue;
            $data['checks'][$type][$check]['checked']++;
            if (in_array($check, $errors)) $data['checks'][$type][$check]['failed']++;
            else {
              if (in_array($check, $warnings) and !$check_warned) {
                $check_warned = TRUE;
                $data['checks'][$type][$check]['warned']++;
                if (!$total_warned) {
                  $warned = TRUE;
                  $data['total']['warned']++;
                }
              }
              $data['checks'][$type][$check]['passed']++;
            }
          }
        }

        switch ($record->status) {
          case 'A': $accepted++;  $data['total']['passed']++; break;
          case 'R': $rejected++;  $data['total']['failed']++; break;
          default:  $unchecked++; $data['total']['unchecked']++; break;
        }

        try {
          $record->save();
        } catch (Exception $e) {
          $failure++;
        }
      }

      if ($accepted)  Notify::msg($accepted.' records passed checks and queries.', 'success', TRUE);
      if ($rejected)  Notify::msg($rejected.' records failed checks and queries.', 'error', TRUE);
      if ($unchecked) Notify::msg($unchecked.' records unchecked.', 'warning', TRUE);
      if ($failure)   Notify::msg($failure.' records could not be accessed.', 'error', TRUE);

      Session::instance()->set('pagination.checks', array(
        'operator_id' => $operator_id,
        'site_id'     => $site_id,
        'block_id'    => $block_id,
        'status'      => $status,
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
      $form->status->val($status = $settings['status']);
      $form->form_type->val($form_type = $settings['form_type']);
      $form->from->val($from = $settings['from']);
      $form->to->val($to = $settings['to']);

      $has_block_id = (bool) (in_array($form_type, array('SSF', 'TDF')));
      $has_site_id  = (bool) (in_array($form_type, array('SSF', 'TDF', 'LDF')));

      $data = $settings['data'];
    }

    if ($data) {
      $model  = ORM::factory($form_type);
      $report = View::factory('report')
        ->set('from', $from)
        ->set('to', $to)
        ->set('form_type', $form_type)
        ->set('report', $data)
        ->set('checks', $model::$checks)
        ->render();

      $_data = ORM::factory($form_type);
      if ($has_site_id) $_data = $_data->where('site_id', 'IN', (array) $site_id);
      else $_data = $_data->where('operator_id', 'IN', (array) $operator_id);
      if ($has_site_id and $has_block_id) $_data = $_data->and_where('block_id', 'IN', (array) $block_id);
      $_data = $_data
        ->and_where('create_date', 'BETWEEN', SGS::db_range($from, $to))
        ->and_where('status', 'IN', (array) $status);

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

      $operator = ORM::factory('operator', $operator_id ?: NULL);
      $site     = ORM::factory('site', $site_id ?: NULL);
      $block    = ORM::factory('block', $block_id ?: NULL);

      $header = View::factory('data')
        ->set('form_type', $form_type)
        ->set('data', $_data)
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

      $table = View::factory('data')
        ->set('classes', array('has-pagination'))
        ->set('form_type', $form_type)
        ->set('data', $_data)
        ->set('operator', $operator->loaded() ? $operator : NULL)
        ->set('site', $site->loaded() ? $site : NULL)
        ->set('block', $block->loaded() ? $block : NULL)
        ->set('options', array(
          'hide_header_info' => TRUE,
          'header'  => FALSE,
          'details' => TRUE,
          'links'   => FALSE,
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

    $id = $this->request->param('id');

//    $tolerance = ORM::factory('tolerance', $id);
//    $form = Formo::form(array('attr' => array('style' => ($id or $_POST) ? '' : 'display: none;')))
//      ->orm('load', $tolerance, array('user_id', 'timestamp'), true)
//      ->add('save', 'submit', array(
//        'label' => $id ? 'Update Tolerance' : 'Add a New Tolerance'
//      ))
//      ->order(array('name' => 0));
//
//    if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
//      try {
//        $tolerance->save();
//        if ($id) Notify::msg('Tolerance successfully updated.', 'success', TRUE);
//        else Notify::msg('Tolerance successfully added.', 'success', TRUE);
//
//        $this->request->redirect('admin/tolerances');
//      } catch (Database_Exception $e) {
//        Notify::msg('Sorry, unable to save tolerance due to incorrect or missing input. Please try again.', 'error');
//      } catch (Exception $e) {
//        Notify::msg('Sorry, tolerance failed to be saved. Please try again.', 'error');
//      }
//    }

    $id = NULL;

    if ($id === null) {
//      $pagination = Pagination::factory(array(
//        'items_per_page' => 20,
//        'total_items' => $tolerance->find_all()->count()));

      $tolerances = ORM::factory('tolerance')
        ->offset($pagination->offset)
        ->limit($pagination->items_per_page);
      if ($sort = $this->request->query('sort')) $tolerances->order_by($sort);
      $tolerances = $tolerances->order_by('form_type')
        ->find_all()
        ->as_array();

      $table = View::factory('tolerances')
        ->set('classes', array('has-pagination'))
        ->set('tolerances', $tolerances);

//      if ($pagination->total_items == 1) Notify::msg($pagination->total_items.' tolerance found');
//      elseif ($pagination->total_items) Notify::msg($pagination->total_items.' tolerances found');
//      else Notify::msg('No tolerances found');
    }

//    $content .= ($id or $_POST) ? $form->render() : SGS::render_form_toggle($form->save->get('label')).$form->render();
    $content .= $table;
//    $content .= $pagination;

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