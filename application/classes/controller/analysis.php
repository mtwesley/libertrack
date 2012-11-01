<?php

class Controller_Analysis extends Controller {

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

  private function handle_data_list($form_type, $id = NULL) {
    if (!Request::$current->query('page')) Session::instance()->delete('pagination.data');

    $has_block_id = (bool) (in_array($form_type, array('SSF', 'TDF')));

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
      $site_ids = DB::select('id', 'name')
        ->from('sites')
        ->execute()
        ->as_array('id', 'name');

      if ($has_block_id) $block_ids = DB::select('id', 'name')
        ->from('blocks')
        ->execute()
        ->as_array('id', 'name');

      $form = Formo::form()
        ->add_group('site_id', 'select', $site_ids, NULL, array('label' => 'Site'));
      if ($has_block_id) $form = $form->add_group('block_id', 'select', $block_ids, NULL, array('label' => 'Block'));
      $form = $form
        ->add_group('status', 'checkboxes', SGS::$data_status, NULL, array('label' => 'Status'))
        ->add('search', 'submit', 'Filter');

      if ($form->sent($_POST) and $form->load($_POST)->validate()) {
        Session::instance()->delete('pagination.data');
        $site_id  = $form->site_id->val();
        if ($has_block_id) $block_id = $form->block_id->val;
        $status   = $form->status->val();

        $data = ORM::factory($form_type)->order_by('create_date', 'DESC');

        if ($site_id)     $data->and_where('site_id', 'IN', (array) $site_id);
        if ($block_id)    $data->and_where('block_id', 'IN', (array) $block_id);
        if ($status)      $data->and_where('status', 'IN', (array) $status);

        Session::instance()->set('pagination.data', array(
          'site_id'     => $site_id,
          'block_id'    => $block_id,
          'status'      => $status,
        ));
      }
      else {
        if ($settings = Session::instance()->get('pagination.data')) {
          $form->site_id->val($site_id = $settings['site_id']);
          if ($has_block_id) $form->block_id->val($block_id = $settings['block_id']);
          $form->status->val($block_id = $settings['block_id']);
        }

        $data = ORM::factory($form_type)->order_by('timestamp', 'DESC');

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

  private function handle_data_edit($id) {
    $id = $this->request->param('id');

    $csv = ORM::factory('csv', $id);
    if ($csv->status == 'A') {
      Notify::msg('Sorry, import data that has already been processed and accepted cannot be edited. Please edit the form data instead.', 'warning', TRUE);
      $this->request->redirect('import/data/'.$id.'/list');
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

    if ($form->sent($_POST) and $form->load($_POST)->validate()) {
      foreach ($csv->values as $key => $value) {
        if ($form->$key) $data[$key] = $form->$key->val();
      }

      $csv->values = $data;
      $csv->status = 'P';
      try {
        $csv->save();
        $updated = true;
      } catch (Exception $e) {
        Notify::msg('Sorry, update failed. Please try again.', 'error');
      }

      if ($updated) {
        $result = self::process_csv($csv);
        if     ($result == 'A') Notify::msg('Updated data accepted as form data.', 'success', TRUE);
        elseif ($result == 'R') Notify::msg('Updated data rejected as form data.', 'error', TRUE);
        elseif ($result == 'U') Notify::msg('Updated data is a duplicate of existing form data.', 'error', TRUE);
        else    Notify::msg('Updated data failed to be processed.', 'error', TRUE);
      }

      $this->request->redirect('import/data/'.$csv->id);
    }

    $csvs = array($csv);
    $table = View::factory('csvs')
      ->set('mode', 'import')
      ->set('csvs', $csvs)
      ->set('fields', SGS_Form_ORM::get_fields($csv->form_type))
      ->render();

    $content .= $form->render();
    $content .= $table;

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