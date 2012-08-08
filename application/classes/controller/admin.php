<?php

class Controller_Admin extends Controller {

  public function action_index() {
    $this->response->body(View::factory('header'));
  }

  public function action_operators() {
    $id = $this->request->param('id');

    $model = ORM::factory('operator', $id);
    $form = Formo::form()
      ->orm('load', $model, array('sites', 'user_id', 'timestamp'), true)
      ->add('save', 'submit', array(
        'label' => $id ? 'Update' : 'Save'
      ));

    if ($form->sent($_POST) and $form->load($_POST)) {
      try {
        if ($id) {
          $model->update();
          Notify::msg('Operator successfully updated.', 'success', TRUE);
        }
        else  {
          $model->save();
          Notify::msg('Operator successfully saved.', 'success', TRUE);
        }
        $this->request->redirect('admin/operators');
      }
      catch (Exception $e) {
        Notify::msg('Sorry, operator failed to be saved. Please try again.', 'error');
      }
    }

    if ($id === null) {
      $operators = ORM::factory('operator')
        ->find_all()
        ->as_array();

      $_body .= View::factory('operators')
        ->set('operators', $operators);
    }

    $body .= View::factory('header')->render();
    $body .= $form->render();
    $body .= $_body;

    $this->response->body($body);
  }

  public function action_sites() {
    $id = $this->request->param('id');

    $model = ORM::factory('site', $id);
    $form = Formo::form()
      ->orm('load', $model, array('blocks', 'printjobs', 'invoices', 'user_id', 'timestamp'), true)
      ->add('save', 'submit', array(
        'label' => $id ? 'Update' : 'Save'
      ));

    if ($form->sent($_POST) and $form->load($_POST)) {
      try {
        if ($id) {
          $model->update();
          Notify::msg('Site successfully updated.', 'success', TRUE);
        }
        else {
          $model->save();
          Notify::msg('Site successfully saved.', 'success', TRUE);
        }
        $this->request->redirect('admin/sites');
      } catch (Exception $e) {
        Notify::msg('Sorry, site failed to be saved. Please try again.', 'error');
      }
    }

    if ($id === null) {
      $sites = ORM::factory('site')
        ->find_all()
        ->as_array();

      $_body .= View::factory('sites')
        ->set('sites', $sites);
    }

    $body .= View::factory('header')->render();
    $body .= $form->render();
    $body .= $_body;

    $this->response->body($body);
  }

  public function action_blocks() {
    $id = $this->request->param('id');

    $model = ORM::factory('block', $id);
    $form = Formo::form()
      ->orm('load', $model, array('user_id', 'timestamp'), true)
      ->add('save', 'submit', array(
        'label' => $id ? 'Update' : 'Save'
      ));

    if ($form->sent($_POST) and $form->load($_POST)) {
      try {
        if ($id) {
          $model->update();
          Notify::msg('Block successfully updated.', 'success', TRUE);
        }
        else {
          $model->save();
          Notify::msg('Block successfully saved.', 'success', TRUE);
        }
        $this->request->redirect('admin/blocks');
      } catch (Exception $e) {
        Notify::msg('Sorry, block failed to be saved. Please try again.', 'error');
      }
    }

    if ($id === null) {
      $blocks = ORM::factory('block')
        ->find_all()
        ->as_array();

      $_body = View::factory('blocks')
        ->set('blocks', $blocks);
    }

    $body .= View::factory('header')->render();
    $body .= $form->render();
    $body .= $_body;
    $this->response->body($body);
  }

  public function action_printjobs() {
    $id = $this->request->param('id');

    $model = ORM::factory('printjob', $id);
    $form = Formo::form()
      ->orm('load', $model, array('site_id'))
      ->add('import', 'file', array(
        'label' => 'File'
      ))
      ->add('save', 'submit', array(
        'label' => $id ? 'Update' : 'Save'
      ));

    if ($form->sent($_POST) and $form->load($_POST)) {
      $import = $form->import->val();
      $array = file($import['tmp_name']);

      // upload file
      $file = ORM::factory('file', $id);
      $file->name = $import['name'];
      $file->type = $import['type'];
      $file->size = $import['size'];
      $file->content_md5 = md5_file($import['tmp_name']);

      try {
        $file->save();
        Notify::msg($file->name.' successfully uploaded.', 'success', TRUE);
      } catch (ORM_Validation_Exception $e) {
        foreach ($e->errors('') as $err) Notify::msg($err, 'error');
      } catch (Exception $e) {
        Notify::msg('Sorry, file upload failed. Please try again.', 'error');
      }

      if ($file->id) {
        // save printjob
        $matches = array();
        preg_match('/Print\sJob\:\s*(\d+).*/', $array[2], $matches);
        $model->allocation_date = Date::formatted_time('now', SGS::PGSQL_DATE_FORMAT);
        $model->number = $matches[1];

        try {
          $model->save();
          Notify::msg('Printjob successfully saved.', 'success', TRUE);
        } catch (Exception $e) {
          Notify::msg('Sorry, printjob failed to be saved. Please try again.', 'error', TRUE);
        }

        // prase barcodes
        $barcode_success = 0;
        $barcode_errors = 0;

        $start = Model_Printjob::PARSE_START;
        $count = count($array);
        for ($i = $start; $i < ($count - 1); $i++) {
          $line = $array[$i];
          if (! $data = $model->parse_txt($line, $array)) continue;

          $barcode = ORM::factory('barcode');
          $barcode->printjob = $model;
          $barcode->barcode = $data['barcode'];
          try {
            $barcode->save();
            $barcode_success++;
          } catch (Exception $e) {
            $barcode_errors++;
          }
        }

        if ($barcode_success) Notify::msg($barcode_success.' barcodes successfully parsed.', 'success', TRUE);
        if ($barcode_error) Notify::msg($barcode_error.' barcodes failed to be parsed.', 'error', TRUE);

        $this->request->redirect('admin/printjobs');
      }
    }

    if ($id === null) {
      $printjobs = ORM::factory('printjob')
        ->find_all()
        ->as_array();

      $_body = View::factory('printjobs')
        ->set('printjobs', $printjobs);
    }

    $body .= View::factory('header')->render();
    $body .= $form->render();
    $body .= $_body;
    $this->response->body($body);
  }

}
