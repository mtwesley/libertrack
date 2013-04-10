<?php

class Controller_Index extends Controller {

  public function action_index() {
    if (!Auth::instance()->logged_in()) {
      Notify::msg('Please login.', NULL, TRUE);
      $this->request->redirect('login');
    }

//define('FPDF_FONTPATH', 'font/');
//require_once('PDF_Label.php');

/*-------------------------------------------------
To create the object, 2 possibilities:
either pass a custom format via an array
or use a built-in AVERY name
-------------------------------------------------*/

// Example of custom format; we start at the second column
//$pdf = new PDF_Label(array('name'=>'perso1', 'paper-size'=>'A4', 'marginLeft'=>1, 'marginTop'=>1, 'NX'=>2, 'NY'=>7, 'SpaceX'=>0, 'SpaceY'=>0, 'width'=>99.1, 'height'=>38.1, 'metric'=>'mm', 'font-size'=>14), 1, 2);
// Standard format
//$pdf = new Label('L7163', 'mm', 1, 2);
//
//$pdf->Open();
//$pdf->AddPage();
//
//// Print labels
//for($i=1;$i<=40;$i++)
//    $pdf->Add_Label(sprintf("%s\n%s\n%s\n%s, %s, %s", "Laurent $i", 'Immeuble Titi', 'av. fragonard', '06000', 'NICE', 'FRANCE'));
//
//$pdf->Output('download.pdf', 'D');


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