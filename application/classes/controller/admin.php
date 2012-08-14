<?php

class Controller_Admin extends Controller {

  public function action_index() {
    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  public function action_operators() {
    $id = $this->request->param('id');

    $model = ORM::factory('operator', $id);
    $form = Formo::form()
      ->orm('load', $model, array('sites', 'user_id', 'timestamp'), true)
      ->add('save', 'submit', array(
        'label' => $id ? 'Update Operator' : 'Add a New Operator'
      ));

    if ($form->sent($_POST) and $form->load($_POST)->validate()) {
      try {
        if ($id) {
          $model->update();
          Notify::msg('Operator successfully updated.', 'success', TRUE);
        }
        else  {
          $model->save();
          Notify::msg('Operator successfully added.', 'success', TRUE);
        }
        $this->request->redirect('admin/operators');
      } catch (Database_Exception $e) {
        Notify::msg('Sorry, unable to save operator due to incorrect or missing input. Please try again.', 'error');
      }
      catch (Exception $e) {
        Notify::msg('Sorry, operator failed to be saved. Please try again.', 'error');
      }
    }

    if ($id === null) {
      $operators = ORM::factory('operator')
        ->find_all()
        ->as_array();

      $table .= View::factory('operators')
        ->set('operators', $operators);
    }

    $content .= $form->render();
    $content .= $table;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  public function action_sites() {
    $id = $this->request->param('id');

    $model = ORM::factory('site', $id);
    $form = Formo::form()
      ->orm('load', $model, array('blocks', 'printjobs', 'invoices', 'user_id', 'timestamp'), true)
      ->add('save', 'submit', array(
        'label' => $id ? 'Update Site' : 'Add a New Site'
      ));

    if ($form->sent($_POST) and $form->load($_POST)->validate()) {
      try {
        if ($id) {
          $model->update();
          Notify::msg('Site successfully updated.', 'success', TRUE);
        }
        else {
          $model->save();
          Notify::msg('Site successfully added.', 'success', TRUE);
        }
        $this->request->redirect('admin/sites');
      } catch (Database_Exception $e) {
        Notify::msg('Sorry, unable to save site due to incorrect or missing input. Please try again.', 'error');
      } catch (Exception $e) {
        Notify::msg('Sorry, site failed to be saved. Please try again.', 'error');
      }
    }

    if ($id === null) {
      $sites = ORM::factory('site')
        ->find_all()
        ->as_array();

      $table .= View::factory('sites')
        ->set('sites', $sites);
    }

    $content .= $form->render();
    $content .= $table;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  public function action_blocks() {
    $id = $this->request->param('id');

    $model = ORM::factory('block', $id);
    $form = Formo::form()
      ->orm('load', $model, array('user_id', 'timestamp'), true)
      ->add('save', 'submit', array(
        'label' => $id ? 'Update Block' : 'Add a New Block'
      ))
      ->order(array('coordinates' => 0));

    if ($form->sent($_POST) and $form->load($_POST)->validate()) {
      try {
        if ($id) {
          $model->update();
          Notify::msg('Block successfully updated.', 'success', TRUE);
        }
        else {
          $model->save();
          Notify::msg('Block successfully added.', 'success', TRUE);
        }
        $this->request->redirect('admin/blocks');
      } catch (Database_Exception $e) {
        Notify::msg('Sorry, unable to save block due to incorrect or missing input. Please try again.', 'error');
      } catch (Exception $e) {
        Notify::msg('Sorry, block failed to be saved. Please try again.', 'error');
      }
    }

    if ($id === null) {
      $blocks = ORM::factory('block')
        ->find_all()
        ->as_array();

      $table = View::factory('blocks')
        ->set('blocks', $blocks);
    }

    $content .= $form->render();
    $content .= $table;

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
      $printjobs = ORM::factory('printjob')
        ->and_where('id', '=', $id)
        ->find_all()
        ->as_array();

      if ($command == 'barcodes') {
        $printjob = reset($printjobs);
        $barcodes = $printjob->barcodes->find_all()->as_array();
      }

    } else {
      $model = ORM::factory('printjob');
      $form = Formo::form()
        ->orm('load', $model, array('site_id'))
        ->add('import', 'file', array(
          'label'    => 'File',
          'required' => TRUE
        ))
        ->add('save', 'submit', 'Upload Print Job');

      if ($form->sent($_POST) and $form->load($_POST)->validate()) {
        $import = $form->import->val();

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
            Notify::msg($file->name.' successfully uploaded.', 'success', TRUE);
          } catch (ORM_Validation_Exception $e) {
            foreach ($e->errors('') as $err) Notify::msg($err, 'error');
          } catch (Exception $e) {
            Notify::msg('Sorry, print job file upload failed. Please try again.', 'error');
          }

          if ($file->id) {
            // save printjob
            $matches = array();
            preg_match('/Print\sJob\:\s*(\d+).*/', $array[2], $matches);
            $model->allocation_date = SGS::date('now', TRUE);
            $model->number = $matches[1];

            try {
              $model->save();
              Notify::msg('Print job successfully saved.', 'success', TRUE);
            } catch (Exception $e) {
              Notify::msg('Sorry, print job failed to be saved. Please try again.', 'error', TRUE);
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
      }

      $printjobs = ORM::factory('printjob')
        ->find_all()
        ->as_array();

      $display = TRUE;
    }

    if ($printjobs) $table .= View::factory('printjobs')
      ->set('printjobs', $printjobs);

    if ($barcodes)  $table .= View::factory('barcodes')
      ->set('barcodes', $barcodes);

    if ($form) $content .= $form->render();
    $content .= $table;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

}
