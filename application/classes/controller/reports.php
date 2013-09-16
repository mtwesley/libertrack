<?php

class Controller_Reports extends Controller {

  public function before() {
    parent::before();

    if (!Auth::instance()->logged_in()) {
      Notify::msg('Please login.', NULL, TRUE);
      $this->request->redirect('login?destination='.$this->request->uri());
    }
    elseif (!Auth::instance()->logged_in('reports')) {
      Notify::msg('Access denied. You must have '.SGS::$roles['reports'].' privileges.', 'locked', TRUE);
      $this->request->redirect();
    }

    Session::instance()->write();
  }

  private function handle_report_list($id) {
    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  private function handle_report_create($type) {
    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  public function action_index() {
    $id      = $this->request->param('id');
    $command = $this->request->param('command');

    switch ($command) {
      case 'download': return self::handle_report_download($id);
      case 'finalize': return self::handle_report_finalize($id);
      case 'delete': return self::handle_report_delete($id);
      case 'list': default: return self::handle_report_list($id);
    }
  }

  public function action_list() {
    $id = $this->request->param('id');

    return self::handle_report_list($id);
  }

  public function action_create() {
    $report_type = $this->request->param('id');

    switch ($report_type) {
      case 'csv':  return self::handle_report_create('CSV');
      case 'data':  return self::handle_report_create('DATA');
      case 'summary': return self::handle_report_create('SUMMARY');
    }

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

}