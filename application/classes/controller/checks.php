<?php

class Controller_Checks extends Controller {

  public function before() {
    parent::before();

    if (!Auth::instance()->logged_in()) {
      Notify::msg('Please login.', NULL, TRUE);
      $this->request->redirect('login?destination='.$this->request->uri());
    }
    elseif (!Auth::instance()->logged_in('analysis')) {
      Notify::msg('Sorry, access denied. You must have '.SGS::$roles['analysis'].' privileges.', 'locked', TRUE);
      $this->request->redirect();
    }

    Session::instance()->write();
  }

  public function action_index() {
    $site_ids = DB::select('id', 'name')
      ->from('sites')
      ->execute()
      ->as_array('id', 'name');

    $form = Formo::form()
      ->add_group('site_id', 'select', $site_ids, NULL, array(
        'label' => 'Site',
        'required' => TRUE
      ))
      ->add('from', 'input', array('label' => 'From'))
      ->add('to', 'input', array('label' => 'To'))
      ->add('search', 'submit', 'Run Checks and Queries');

    if ($form->sent($_POST) and $form->load($_POST)->validate()) {
      $site_id = $form->site_id->val();

       // TODO: should eventually be all the forms from SGS::$form_type
      $form_types = array(
        'SSF',
        'TDF',
        // TODO: 'LDF'
      );

      $rejected = 0;
      $accepted = 0;
      $failure  = 0;

      foreach ($form_types as $form_type) {
        $objects = ORM::factory($form_type)
          ->where('site_id', 'IN', (array) $site_id)
          ->and_where('create_date', 'BETWEEN', SGS::db_range($from, $to))
          ->and_where('status', 'IN', array('P', 'R'))
          ->find_all()
          ->as_array();

        foreach ($objects as $object) {
          $errors = $object->run_checks('errors');
          if ($errors) {
            $object->errors = $errors;
            $object->status = 'R';
            $rejected++;
          } else {
            $object->status = 'A';
            $accepted++;
          }

          try {
            $object->save();
          } catch (Exception $e) {
            $failure++;
          }
        }
      }

      if ($accepted) Notify::msg($accepted.' records passed checks and queries.', 'success', TRUE);
      if ($rejected) Notify::msg($rejected.' records failed checks and queries.', 'error', TRUE);
      if ($failure)  Notify::msg($failure.' records could not be checked.', 'error', TRUE);

      $table .= '<strong>'.SGS::$form_type[$form_type].'</strong>';
      $table .= View::factory('data')
        ->set('form_type', $form_type)
        ->set('data', $objects)
        ->render();

    }

    if ($form)  $content .= $form;
    if ($table) $content .= $table;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

}