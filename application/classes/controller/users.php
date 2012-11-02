<?php

class Controller_Users extends Controller {

  public function before() {
    parent::before();

    if (!Auth::instance()->logged_in()) {
      Notify::msg('Please login.', NULL, TRUE);
      $this->request->redirect('login?destination='.$this->request->uri());
    }
    elseif (!Auth::instance()->logged_in('users')) {
      Notify::msg('Sorry, access denied. You must have '.SGS::$roles['users'].' privileges.', 'locked', TRUE);
      $this->request->redirect();
    }
  }

  public function action_index() {
    return self::action_list();
  }

  public function action_list() {
    $id = $this->request->param('id');
    $command = $this->request->param('command');

    $user = ORM::factory('user', $id);

    if ($command == 'password') {
      $form = Formo::form()
        ->add('old_password', 'password', array(
          'label'    => 'Old Password',
          'order'    => array('after', 'password'),
          'value'    => NULL,
          'required' => TRUE
        ))
        ->add('password', 'password', array(
          'label'    => 'New Password',
          'order'    => array('after', 'password'),
          'value'    => NULL,
          'required' => TRUE
        ))
        ->add('password_confirm', 'password', array(
          'label'    => 'Confirm New Password',
          'order'    => array('after', 'password'),
          'value'    => NULL,
          'required' => TRUE
        ))
        ->add('save', 'submit', array(
          'label' => 'Change Password'
        ));
    }
    else {
      $form = Formo::form(array('attr' => array('style' => $id || $_POST ? '' : 'display: none;')))
        ->orm('load', $user, array_filter(array('username', 'name', 'email', 'roles', $id ? '' : 'password')))
        ->add('save', 'submit', array(
          'label' => $id ? 'Update user' : 'Add a New user'
        ));

      if (!$id) {
        $form = $form->add('password_confirm', 'password', array(
          'label' => 'Confirm Password',
          'order' => array('after', 'password'),
          'value' => NULL
        ));
      }

      $form = $form->order(array(
          'username' => 0,
          'name' => 1
        ));
    }

    if ($form->sent($_POST) and $form->load($_POST)->validate()) {
      if ($command == 'password') {
        try {
          if ($user->password !== Auth::instance()->hash($form->old_password->val())) {
            Notify::msg('"Old password" provided is incorrect.', 'error');
            throw new Exception();
          }
          $user->update_user($_POST, array(
            'password',
          ));
          Notify::msg('Password changed.', 'success', TRUE);

          $this->request->redirect('users');
        } catch (Exception $e) {
          Notify::msg('Sorry, unable to update password. Please try again.', 'error');
        }
      }
      else {
        try {
          if (!$user->id) $user->create_user($_POST, array(
            'username',
            'name',
            'email',
            'password',
          ));

          $roles = $form->roles->val();

          $user->remove('roles');
          $user->add('roles', $roles);
          $user->save();

          if ($id) Notify::msg('User successfully updated.', 'success', TRUE);
          else Notify::msg('User successfully added.', 'success', TRUE);

          $this->request->redirect('users');
        } catch (Database_Exception $e) {
          Notify::msg('Sorry, unable to save user due to incorrect or missing input. Please try again.', 'error');
        }
        catch (Exception $e) {
          Notify::msg('Sorry, user failed to be saved. Please try again.', 'error');
        }
      }
    }

    if ($id === null) {
      $pagination = Pagination::factory(array(
        'items_per_page' => 20,
        'total_items' => $user->find_all()->count()));

      $users = ORM::factory('user')
        ->order_by('name')
        ->offset($pagination->offset)
        ->limit($pagination->items_per_page)
        ->find_all()
        ->as_array();

      $table .= View::factory('users')
        ->set('classes', array('has-pagination'))
        ->set('users', $users);

      if ($pagination->total_items == 1) Notify::msg($pagination->total_items.' user found');
      elseif ($pagination->total_items) Notify::msg($pagination->total_items.' users found');
      else Notify::msg('No users found');
    }

    $content .= $id || $_POST ? $form->render() : SGS::render_form_toggle($form->save->get('label')).$form->render();
    $content .= $table;
    $content .= $pagination;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

}
