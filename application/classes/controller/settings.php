<?php

class Controller_Settings extends Controller {

  public function before() {
    parent::before();

    if (!Auth::instance()->logged_in()) {
      Notify::msg('Please login.', NULL, TRUE);
      $this->request->redirect('login?destination='.$this->request->uri());
    }
    elseif (!Auth::instance()->logged_in('admin')) {
      Notify::msg('Access denied. You must have '.SGS::$roles['admin'].' privileges.', 'locked', TRUE);
      $this->request->redirect();
    }
  }

  public function action_index() {
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

}
