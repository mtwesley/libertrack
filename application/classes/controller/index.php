<?php

class Controller_Index extends Controller {

  public function action_index() {
    if (!Auth::instance()->logged_in()) {
      Notify::msg('Please login.', NULL, TRUE);
      $this->request->redirect('login');
    }
    
    $view = View::factory('main');
    $this->response->body($view);
  }

  public function action_login() {
    if (Auth::instance()->logged_in()) {
      $this->request->redirect('logout');
    }

    $form = Formo::form()
      ->add('username', 'input', array(
        'label' => 'Username'
      ))
      ->add('password', 'password', array(
        'label' => 'Password'
      ))
      ->add('search', 'submit', 'Login');

    if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
      $username = $form->username->val();
      $password = $form->password->val();

      if (Auth::instance()->login($username, $password)) {
        $user = Auth::instance()->get_user();

        if (!$_GET['destination'])
        if ($user->last_timestamp) Notify::msg('Welcome back, '.$user->name.'.', NULL, TRUE);
        else Notify::msg('Welcome to LiberTrack', NULL, TRUE);

        $this->request->redirect($_GET['destination'] ? $_GET['destination'] : '');
      }
      else {
        Notify::msg('Invalid username and password combination. Please try again.', 'error');
      }
    }
    $content .= $form->render();

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  public function action_logout() {
    Auth::instance()->logout();
    Session::instance()->destroy();

    Notify::msg('You have been logged out.', NULL, TRUE);
    $this->request->redirect('login');
  }

}