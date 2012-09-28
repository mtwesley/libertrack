<?php

class Controller_Index extends Controller {

  public function action_index() {
    if (!Auth::instance()->logged_in()) {
      Notify::msg('Please login.', NULL, TRUE);
      $this->request->redirect('login?destination='.$this->request->uri());
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

    if ($form->sent($_POST) and $form->load($_POST)->validate()) {
      $username = $form->username->val();
      $password = $form->password->val();

      if (Auth::instance()->login($username, $password)) {
        $user = Auth::instance()->get_user();

        if ($user->last_timestamp AND !$_GET['destination']) Notify::msg('Welcome back, '.$user->name.'.', NULL, TRUE);
        else Notify::msg('Welcome to the LiberFor database.');

        $this->request->redirect($_GET['destination'] ? $_GET['destination'] : NULL);
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