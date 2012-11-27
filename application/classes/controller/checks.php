<?php

class Controller_Checks extends Controller {

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

  public function action_index() {
    $site_ids = DB::select('id', 'name')
      ->from('sites')
      ->order_by('name')
      ->execute()
      ->as_array('id', 'name');

    $form = Formo::form()
      ->add_group('form_type', 'select', SGS::$form_type, NULL, array('label' => 'Type', 'required' => TRUE))
      ->add_group('site_id', 'select', $site_ids, NULL, array('label' => 'Site', 'required' => TRUE))
      ->add('from', 'input', array('label' => 'From', 'attr' => array('class' => 'dpicker', 'id' => 'from-dpicker')))
      ->add('to', 'input', array('label' => 'To', 'attr' => array('class' => 'dpicker', 'id' => 'to-dpicker')))
      ->add('submit', 'submit', 'Run');

    if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
      $form_type = $form->form_type->val();
      $site_id   = $form->site_id->val();
      $from      = $form->from->val();
      $to        = $form->to->val();

      $rejected = 0;
      $accepted = 0;
      $pending  = 0;
      $failure  = 0;

      $records = ORM::factory($form_type)
        ->where('site_id', 'IN', (array) $site_id)
        ->and_where('create_date', 'BETWEEN', SGS::db_range($from, $to))
        ->find_all()
        ->as_array('id');

      foreach ($records as $record) {
        try {
          $record->run_checks();
        } catch (ORM_Validation_Exception $e) {
          foreach ($e->errors('') as $err) Notify::msg(SGS::errorfy($err), 'error', TRUE);
        } catch (Exception $e) {
          Notify::msg('Sorry, unable to run check and queries. Please try again.', 'error');
        }

        switch ($record->status) {
          case 'A': $accepted++; break;
          case 'R': $rejected++; break;
          default:  $pending++; break;
        }

        try {
          $record->save();
        } catch (Exception $e) {
          $failure++;
        }
      }

      if ($accepted) Notify::msg($accepted.' records passed checks and queries.', 'success', TRUE);
      if ($rejected) Notify::msg($rejected.' records failed checks and queries.', 'error', TRUE);
      if ($pending)  Notify::msg($pending.' records still pending.', 'warning', TRUE);
      if ($failure)  Notify::msg($failure.' records could not be accessed.', 'error', TRUE);

      $model     = ORM::factory($form_type);
      $modelname = get_class($model);

      $data   = $modelname::generate_report($records);
      $report = View::factory('report')
        ->set('data', $data)
        ->set('from', $from)
        ->set('to', $to)
        ->set('messages', $modelname::$errors)
        ->set('fields', $modelname::$fields + $model->labels())
        ->render();
    }

    if ($form)   $content .= $form;
    if ($report) $content .= $report;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

}