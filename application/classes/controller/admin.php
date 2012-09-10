<?php

class Controller_Admin extends Controller {

  public function before() {
    parent::before();

    if (!Auth::instance()->logged_in()) {
      Notify::msg('Please login.', NULL, TRUE);
      $this->request->redirect('login');
    }
    elseif (!Auth::instance()->logged_in('admin')) {
      Notify::msg('Sorry, access denied. You must have '.SGS::$roles['admin'].' privileges.', 'locked', TRUE);
      $this->request->redirect();
    }
  }

  public function action_index() {
    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  public function action_users() {
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

          $this->request->redirect('admin/users');
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

          $this->request->redirect('admin/users');
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

  public function action_operators() {
    $id = $this->request->param('id');

    $operator = ORM::factory('operator', $id);
    $form = Formo::form(array('attr' => array('style' => $id || $_POST ? '' : 'display: none;')))
      ->orm('load', $operator, array('sites', 'user_id', 'timestamp'), true)
      ->add('save', 'submit', array(
        'label' => $id ? 'Update Operator' : 'Add a New Operator'
      ));

    if ($form->sent($_POST) and $form->load($_POST)->validate()) {
      try {
        $operator->save();
        if ($id) Notify::msg('Operator successfully updated.', 'success', TRUE);
        else Notify::msg('Operator successfully added.', 'success', TRUE);

        $this->request->redirect('admin/operators');
      } catch (Database_Exception $e) {
        Notify::msg('Sorry, unable to save operator due to incorrect or missing input. Please try again.', 'error');
      }
      catch (Exception $e) {
        Notify::msg('Sorry, operator failed to be saved. Please try again.', 'error');
      }
    }

    if ($id === null) {
      $pagination = Pagination::factory(array(
        'items_per_page' => 20,
        'total_items' => $operator->find_all()->count()));

      $operators = ORM::factory('operator')
        ->order_by('name')
        ->offset($pagination->offset)
        ->limit($pagination->items_per_page)
        ->find_all()
        ->as_array();

      $table .= View::factory('operators')
        ->set('classes', array('has-pagination'))
        ->set('operators', $operators);

      if ($pagination->total_items == 1) Notify::msg($pagination->total_items.' operator found');
      elseif ($pagination->total_items) Notify::msg($pagination->total_items.' operators found');
      else Notify::msg('No operators found');
    }

    $content .= $id || $_POST ? $form->render() : SGS::render_form_toggle($form->save->get('label')).$form->render();
    $content .= $table;
    $content .= $pagination;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  public function action_sites() {
    $id = $this->request->param('id');

    $site = ORM::factory('site', $id);
    $form = Formo::form(array('attr' => array('style' => $id || $_POST ? '' : 'display: none;')))
      ->orm('load', $site, array('blocks', 'printjobs', 'invoices', 'user_id', 'timestamp'), true)
      ->add('save', 'submit', array(
        'label' => $id ? 'Update Site' : 'Add a New Site'
      ));

    if ($form->sent($_POST) and $form->load($_POST)->validate()) {
      try {
        $site->save();
        if ($id) Notify::msg('Site successfully updated.', 'success', TRUE);
        else Notify::msg('Site successfully added.', 'success', TRUE);

        $this->request->redirect('admin/sites');
      } catch (Database_Exception $e) {
        Notify::msg('Sorry, unable to save site due to incorrect or missing input. Please try again.', 'error');
      } catch (Exception $e) {
        Notify::msg('Sorry, site failed to be saved. Please try again.', 'error');
      }
    }

    if ($id === null) {
      $pagination = Pagination::factory(array(
        'items_per_page' => 20,
        'total_items' => $site->find_all()->count()));

      $sites = ORM::factory('site')
        ->order_by('name')
        ->offset($pagination->offset)
        ->limit($pagination->items_per_page)
        ->find_all()
        ->as_array();

      $table .= View::factory('sites')
        ->set('classes', array('has-pagination'))
        ->set('sites', $sites);

      if ($pagination->total_items == 1) Notify::msg($pagination->total_items.' site found');
      elseif ($pagination->total_items) Notify::msg($pagination->total_items.' sites found');
      else Notify::msg('No sites found');
    }

    $content .= $id || $_POST ? $form->render() : SGS::render_form_toggle($form->save->get('label')).$form->render();
    $content .= $table;
    $content .= $pagination;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  public function action_blocks() {
    $id = $this->request->param('id');

    $block = ORM::factory('block', $id);
    $form = Formo::form(array('attr' => array('style' => $id || $_POST ? '' : 'display: none;')))
      ->orm('load', $block, array('user_id', 'timestamp'), true)
      ->add('save', 'submit', array(
        'label' => $id ? 'Update Block' : 'Add a New Block'
      ))
      ->order(array('name' => 0));

    if ($form->sent($_POST) and $form->load($_POST)->validate()) {
      try {
        $block->save();
        if ($id) Notify::msg('Block successfully updated.', 'success', TRUE);
        else Notify::msg('Block successfully added.', 'success', TRUE);

        $this->request->redirect('admin/blocks');
      } catch (Database_Exception $e) {
        Notify::msg('Sorry, unable to save block due to incorrect or missing input. Please try again.', 'error');
      } catch (Exception $e) {
        Notify::msg('Sorry, block failed to be saved. Please try again.', 'error');
      }
    }

    if ($id === null) {
      $pagination = Pagination::factory(array(
        'items_per_page' => 20,
        'total_items' => $block->find_all()->count()));

      $blocks = ORM::factory('block')
        ->order_by('name')
        ->offset($pagination->offset)
        ->limit($pagination->items_per_page)
        ->find_all()
        ->as_array();

      $table = View::factory('blocks')
        ->set('classes', array('has-pagination'))
        ->set('blocks', $blocks);

      if ($pagination->total_items == 1) Notify::msg($pagination->total_items.' block found');
      elseif ($pagination->total_items) Notify::msg($pagination->total_items.' blocks found');
      else Notify::msg('No blocks found');
    }

    $content .= $id || $_POST ? $form->render() : SGS::render_form_toggle($form->save->get('label')).$form->render();
    $content .= $table;
    $content .= $pagination;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  public function action_printjobs() {
    $id = $this->request->param('id');
    $command = $this->request->param('command');

    if (!$command && !is_numeric($id)) {
      $id      = NULL;
      $command = $id;
    }

    if ($id) {
      $printjob = ORM::factory('printjob', $id);
      $printjobs = array($printjob);

      if ($command == 'barcodes') {
        $pagination = Pagination::factory(array(
          'items_per_page' => 50,
          'total_items' => $printjob->barcodes->find_all()->count()));

        $barcodes = $printjob->barcodes
          ->offset($pagination->offset)
          ->limit($pagination->items_per_page)
          ->order_by('timestamp')
          ->find_all()
          ->as_array();

        if ($pagination->total_items == 1) Notify::msg($pagination->total_items.' barcode found');
      elseif ($pagination->total_items) Notify::msg($pagination->total_items.' barcodes found');
        else Notify::msg('No barcodes found');
      }

    } else {
      $printjob = ORM::factory('printjob');
      $form = Formo::form()
        ->orm('load', $printjob, array('site_id'))
        ->add('import[]', 'file', array(
          'label'    => 'File',
          'required' => TRUE,
          'attr'  => array('multiple' => 'multiple')
        ))
        ->add('save', 'submit', 'Upload');

      if ($form->sent($_POST) and $form->load($_POST)->validate()) {
        $barcode_success = 0;
        $barcode_error = 0;

        $num_files = count(reset($_FILES['import']));
        for ($j = 0; $j < $num_files; $j++) {
          $import = array(
            'name'     => $_FILES['import']['name'][$j],
            'type'     => $_FILES['import']['type'][$j],
            'tmp_name' => $_FILES['import']['tmp_name'][$j],
            'error'    => $_FILES['import']['error'][$j],
            'size'     => $_FILES['import']['size'][$j]
          );

          $info = pathinfo($import['name']);
          if (!array_filter($info)) Notify::msg('Sorry, no upload found or there is an error in the system. Please try again.', 'error');
          else {

            $array = file($import['tmp_name']);

            // upload file
            $file = ORM::factory('file');
            $file->name = $import['name'];
            $file->type = $import['type'];
            $file->size = $import['size'];
            $file->operation = 'A';
            $file->operation_type = 'PJ';
            $file->content_md5 = md5_file($import['tmp_name']);

            try {
              $file->save();
              Notify::msg($file->name.' print job successfully uploaded.', 'success', TRUE);
            } catch (ORM_Validation_Exception $e) {
              foreach ($e->errors('') as $err) Notify::msg($err, 'error');
            } catch (Exception $e) {
              Notify::msg('Sorry, print job upload failed. Please try again.', 'error');
            }

            if ($file->id) {
              // save printjob
              $matches = array();
              preg_match('/Print\sJob\:\s*(\d+).*/', $array[2], $matches);

              $_printjob = clone($printjob);
              $_printjob->allocation_date = SGS::date('now', SGS::PGSQL_DATE_FORMAT);
              $_printjob->number = $matches[1];

              try {
                $_printjob->save();
              } catch (Exception $e) {
                Notify::msg('Sorry, print job failed to be saved. Please try again.', 'error', TRUE);
              }

              // prase barcodes
              $start = Model_Printjob::PARSE_START;
              $count = count($array);
              for ($i = $start; $i < ($count - 1); $i++) {
                $line = $array[$i];
                if (! $data = $_printjob->parse_txt($line, $array)) continue;

                $barcode = ORM::factory('barcode');
                $barcode->printjob = $_printjob;
                $barcode->barcode = $data['barcode'];
                try {
                  $barcode->save();
                  $barcode_success++;
                } catch (Exception $e) {
                  $barcode_error++;
                }
              }
            }
          }
        }
        if ($barcode_success) Notify::msg($barcode_success.' barcodes successfully parsed.', 'success', TRUE);
        if ($barcode_error) Notify::msg($barcode_error.' barcodes failed to be parsed.', 'error', TRUE);

        $this->request->redirect('admin/printjobs');
      }

      $pagination = Pagination::factory(array(
        'items_per_page' => 20,
        'total_items' => ORM::factory('printjob')->find_all()->count()));

      $printjobs = ORM::factory('printjob')
        ->order_by('number')
        ->offset($pagination->offset)
        ->limit($pagination->items_per_page)
        ->find_all()
        ->as_array();

      if ($pagination->total_items == 1) Notify::msg($pagination->total_items.' print job found');
      elseif ($pagination->total_items) Notify::msg($pagination->total_items.' print jobs found');
      else Notify::msg('No print jobs found');
    }

    if ($barcodes)  $table .= View::factory('barcodes')
      ->set('classes', array('has-pagination'))
      ->set('barcodes', $barcodes);

    elseif ($printjobs) $table .= View::factory('printjobs')
      ->set('classes', array('has-pagination'))
      ->set('printjobs', $printjobs);

    if ($form) $content .= $form->render();
    $content .= $table;
    $content .= $pagination;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  public function action_species() {
    $id = $this->request->param('id');

    $species = ORM::factory('species', $id);
    $form = Formo::form(array('attr' => array('style' => $id || $_POST ? '' : 'display: none;')))
      ->orm('load', $species, array('user_id', 'timestamp'), true)
      ->add('save', 'submit', array(
        'label' => $id ? 'Update Species' : 'Add a New Species'
      ));

    if ($form->sent($_POST) and $form->load($_POST)->validate()) {
      try {
        $species->save();
        if ($id) Notify::msg('Species successfully updated.', 'success', TRUE);
        else Notify::msg('Species successfully added.', 'success', TRUE);

        $this->request->redirect('admin/species');
      } catch (Database_Exception $e) {
        Notify::msg('Sorry, unable to save species due to incorrect or missing input. Please try again.', 'error');
      } catch (Exception $e) {
        Notify::msg('Sorry, species failed to be saved. Please try again.', 'error');
      }
    }

    if ($id === null) {
      $pagination = Pagination::factory(array(
        'items_per_page' => 20,
        'total_items' => $species->find_all()->count()));

      $speciess = ORM::factory('species')
        ->order_by('trade_name')
        ->offset($pagination->offset)
        ->limit($pagination->items_per_page)
        ->find_all()
        ->as_array();

      $table .= View::factory('species')
        ->set('classes', array('has-pagination'))
        ->set('species', $speciess);

      if ($pagination->total_items) Notify::msg($pagination->total_items.' species found');
      else Notify::msg('No species found');
    }

    $content .= $id || $_POST ? $form->render() : SGS::render_form_toggle($form->save->get('label')).$form->render();
    $content .= $table;
    $content .= $pagination;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

}
