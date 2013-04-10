<?php

class Controller_OldExport extends Controller {

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
  }

  private function handle_file_list($id = NULL) {
    if (!Request::$current->query()) Session::instance()->delete('pagination.file.list');
    if ($id) {
      Session::instance()->delete('pagination.file.list');

      $files = ORM::factory('file')
        ->where('operation', '=', 'U')
        ->and_where('id', '=', $id)
        ->find_all()
        ->as_array();
    }
    else {
      $site_ids = DB::select('id', 'name')
        ->from('sites')
        ->order_by('name')
        ->execute()
        ->as_array('id', 'name');

      $form = Formo::form()
        ->add_group('operation_type', 'checkboxes', SGS::$form_type, NULL, array('label' => 'Type'))
        ->add_group('site_id', 'select', $site_ids, NULL, array('label' => 'Site', 'attr' => array('class' => 'siteopts')))
        ->add_group('block_id', 'select', array(), NULL, array('label' => 'Block', 'attr' => array('class' => 'blockopts')))
        ->add('search', 'submit', 'Filter');

      if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
        Session::instance()->delete('pagination.file.list');

        $operation_type = $form->operation_type->val();
        $site_id        = $form->site_id->val();
        $block_id       = $form->block_id->val();

        $files = ORM::factory('file')->where('operation', '=', 'U');

        if ($operation_type) $files->and_where('operation_type', 'IN', (array) $operation_type);
        if ($site_id)        $files->and_where('site_id', 'IN', (array) $site_id);
        if ($block_id)       $files->and_where('block_id', 'IN', (array) $block_id);

        Session::instance()->set('pagination.file.list', array(
          'form_type'   => $operation_type,
          'site_id'     => $site_id,
          'block_id'    => $block_id,
        ));
      }
      else {
        if ($settings = Session::instance()->get('pagination.file.list')) {
          $form->operation_type->val($operation_type = $settings['form_type']);
          $form->site_id->val($site_id = $settings['site_id']);
          $form->block_id->val($block_id = $settings['block_id']);
        }

        $files = ORM::factory('file')
          ->where('operation', '=', 'E');

        if ($operation_type) $files->and_where('operation_type', 'IN', (array) $operation_type);
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
        ->set('mode', 'export')
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
      ->set('fields', SGS_Form_ORM::get_fields($file->operation_type))
      ->render();
    else Notify::msg('No records found.');

    $content .= $table;

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

}