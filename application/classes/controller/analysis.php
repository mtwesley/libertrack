<?php

class Controller_Analysis extends Controller {

  public function before() {
    parent::before();

    if (!Auth::instance()->logged_in()) {
      Notify::msg('Please login.', NULL, TRUE);
      $this->request->redirect('login');
    }
    elseif (!Auth::instance()->logged_in('analysis')) {
      Notify::msg('Sorry, access denied. You must have '.SGS::$roles['analysis'].' privileges.', 'locked', TRUE);
      $this->request->redirect();
    }

    Session::instance()->write();
  }

  private function handle_data_list($form_type, $id = NULL) {
    if (!Request::$current->query('page')) Session::instance()->delete('pagination.data');
    if ($id) {
      Session::instance()->delete('pagination.data');

      $data = ORM::factory($form_type)
        ->where('operation', '=', 'I')
        ->and_where('id', '=', $id)
        ->find_all()
        ->as_array();

      $form_type = reset($data)->form_type;
    }
    else {
      $operator_ids = DB::select('id', 'name')
        ->from('operators')
        ->execute()
        ->as_array('id', 'name');

      $site_ids = DB::select('id', 'name')
        ->from('sites')
        ->execute()
        ->as_array('id', 'name');

      if (in_array($form_type, array('SSF', 'TDF'))) $block_ids = DB::select('id', 'name')
        ->from('blocks')
        ->execute()
        ->as_array('id', 'name');

      $form = Formo::form()
        ->add_group('operator_id', 'select', $operator_ids, NULL, array('label' => 'Operator'))
        ->add_group('site_id', 'select', $site_ids, NULL, array('label' => 'Site'));

      if (in_array($form_type, array('SSF', 'TDF'))) $form = $form->add_group('block_id', 'select', $block_ids, NULL, array('label' => 'Block'));

      $form = $form->add('search', 'submit', 'Filter');

      if ($form->sent($_POST) and $form->load($_POST)->validate()) {
        Session::instance()->delete('pagination.data');

        $operator_id = $form->operator_id->val();
        $site_id     = $form->site_id->val();

        if (in_array($form_type, array('SSF', 'TDF'))) $block_id = $form->block_id->val();

        $data = ORM::factory($form_type)->order_by('timestamp', 'desc');

        if ($operator_id) $data->and_where('operator_id', 'IN', (array) $operator_id);
        if ($site_id)     $data->and_where('site_id', 'IN', (array) $site_id);
        if ($block_id)    $data->and_where('block_id', 'IN', (array) $block_id);

        Session::instance()->set('pagination.data', array(
          'operator_id' => $operator_id,
          'site_id'     => $site_id,
          'block_id'    => $block_id,
          'status'      => $status,
        ));

        $search = TRUE;
      }
      else {
        if ($settings = Session::instance()->get('pagination.data')) {
          $form->operator_id->val($operator_id = $settings['operator_id']);
          $form->site_id->val($site_id = $settings['site_id']);
          $form->block_id->val($block_id = $settings['block_id']);
        }

        $data = ORM::factory($form_type)->order_by('timestamp', 'desc');

        if ($operator_id) $data->and_where('operator_id', 'IN', (array) $operator_id);
        if ($site_id)     $data->and_where('site_id', 'IN', (array) $site_id);
        if ($block_id)    $data->and_where('block_id', 'IN', (array) $block_id);
      }

      if ($data) {
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
      }
    }

    if ($data) {
      $table = View::factory('data')
        ->set('classes', array('has-pagination'))
        ->set('form_type', $form_type)
        ->set('data', $data)
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

  public function action_ssf() {
    return self::handle_data_list('SSF');
  }

  public function action_tdf() {
    return self::handle_data_list('TDF');
  }

  public function action_ldf() {
    return self::handle_data_list('LDF');
  }

}