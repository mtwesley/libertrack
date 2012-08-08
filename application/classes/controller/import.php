<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Import extends Controller {

  private static function cleanup_errors($errors) {
    foreach ($errors as $field => $error) {
      $array  = explode('.', $error);
      $type   = array_pop($array);
      $source = array_pop($array);
      $errors[$field ? $field : $source] = $type;
    }

    return $errors;
  }

  private static function process_csv($csv) {
    $errors    = array();
    $form_type = strtolower($csv->form_type);

    $form_model = ORM::factory($form_type);
    $form_model->parse_data($csv->values);

    if ( ! $errors = $form_model->validate_data($csv->values, 'errors')) {
      try {
        $form_model->save();
      } catch (ORM_Validation_Exception $e) {
        $errors = $e->errors('');
      }
    }

    if ($errors) {
      $suggestions = $form_model->make_suggestions($csv->values, $errors);

      $csv->suggestions = $suggestions;
      $csv->errors = self::cleanup_errors($errors);
      $csv->status = 'R';
    } else {
      $csv->errors = NULL;
      $csv->suggestions = NULL;
      $csv->form_data_id = $form_model->id;
      $csv->status = 'A';
    }

    try {
      $csv->save();
      return $csv->status;
    } catch (Exception $e) {
      return FALSE;
    }
  }

  private static function detect_form_type($excel) {
    if     (strpos(strtoupper($excel[1][D]), 'STOCK SURVEY FORM') !== false) return 'SSF';
    elseif (strpos(strtoupper($excel[1][C]), 'TREE FELLING')      !== false) return 'TDF';
    elseif (strpos(strtoupper($excel[1][C]), 'LOG DATA FORM')     !== false) return 'LDF';

    else Notify::msg('Unknown template format.', 'error');
  }

  private function handle_file_list() {
    $files = ORM::factory('file')
      ->where('operation', '=', 'I')
      ->order_by('timestamp', 'desc')
      ->find_all()
      ->as_array();

    $body .= View::factory('header')->render();
    $body .= View::factory('files')
      ->set('mode', 'import')
      ->set('files', $files)
      ->render();

    $this->response->body($body);
  }

  private function handle_file_upload() {
    $form = Formo::form()
      ->add('import', 'file')
      ->add('upload', 'submit', 'Upload');

    if ($form->sent($_POST) and $form->load($_POST)) {
      $import = $form->import->val();
      try {
        $excel = PHPExcel_IOFactory::load($import['tmp_name'])->getActiveSheet()->toArray(null,true,true,true);
      } catch (Exception $e) {
        Notify::msg('No file uploaded or unable to find file.', 'error');
      }

      if ($excel) {
        // detect type of file
        $form_type = self::detect_form_type($excel);

        // upload file
        $file = ORM::factory('file');
        $file->name = $import['name'];
        $file->type = $import['type'];
        $file->size = $import['size'];
        $file->operation      = 'I';
        $file->operation_type = $form_type;
        $file->content_md5    = md5_file($import['tmp_name']);

        try {
          $file->save();
          Notify::msg($file->name.' successfully uploaded.', 'success', TRUE);
        } catch (ORM_Validation_Exception $e) {
          foreach ($e->errors('') as $err) Notify::msg($err, 'error');
        } catch (Exception $e) {
          Notify::msg('Sorry, file upload failed. Please try again.', 'error');
        }

        if ($file->id) {
          // file has been saved
          $csv_error   = 0;
          $csv_succsss = 0;

          // parse CSV
          $form_model = ORM::factory($form_type);
          $start = constant('Model_'.$form_type.'::PARSE_START');
          $count = count($excel);
          for ($i = $start; $i <= $count; $i++) {
            $row = $excel[$i];
            if ( ! $data = $form_model->parse_csv($row, $excel)) continue;

            // save CSV
            $csv = ORM::factory('csv');
            $csv->file_id = $file->id;
            $csv->operation = 'I';
            $csv->form_type = $form_type;
            $csv->values = $data;
            try {
              $csv->save();
              $csv_success++;
            } catch (Exception $e) {
              $csv_error++;
            }
          }

          if ($csv_success) Notify::msg($csv_success.' rows successfully parsed.', 'success', TRUE);
          if ($csv_error) Notify::msg($csv_error.' rows failed to be parsed.', 'error', TRUE);

          Notify::msg('Next, click '.HTML::anchor('import/files/'.$file->id.'/process', 'process').
                      ' to validate uploaded data and import it as form data or '.HTML::anchor('import/files/'.$file->id.'/review', 'review').
                      ' to review uploaded data.', TRUE);

          $this->request->redirect('import/files');
        }
      }
    }

    $body .= View::factory('header')->render();
    $body .= $form->render();

    $this->response->body($body);
  }

  private function handle_file_review($id = NULL) {
    if (!$id) $id = $this->request->param('id');

    $file = ORM::factory('file', $id);

    // pending
    $pending = $file->csv
      ->where('status', '=', 'P')
      ->find_all()
      ->as_array();

    // accepted
    $accepted = $file->csv
      ->where('status', '=', 'A')
      ->find_all()
      ->as_array();

    // rejected
    $rejected = $file->csv
      ->where('status', '=', 'R')
      ->find_all()
      ->as_array();

    if ($pending) $_body .= View::factory('csvs')
      ->set('title', 'Pending')
      ->set('csvs', $pending)
      ->set('fields', SGS_Form_ORM::get_fields($file->operation_type))
      ->render();
    else Notify::msg('No pending records found.');

    if ($accepted) $_body .= View::factory('csvs')
      ->set('title', 'Accepted')
      ->set('csvs', $accepted)
      ->set('fields', SGS_Form_ORM::get_fields($file->operation_type))
      ->render();
    else Notify::msg('No accepted records found.');

    if ($rejected) $_body .= View::factory('csvs')
      ->set('title', 'Rejected')
      ->set('csvs', $rejected)
      ->set('fields', SGS_Form_ORM::get_fields($file->operation_type))
      ->render();
    else Notify::msg('No rejected records found.');

    $body .= View::factory('header');
    $body .= $_body;
    $this->response->body($body);
  }

  private function handle_file_process($id = NULL) {
    if (!$id) $id = $this->request->param('id');
    $file = ORM::factory('file', $id);

    // pending
    $pending = $file->csv
      ->where('status', 'IN', array('P','R'))
      ->find_all()
      ->as_array();

    $accepted = 0;
    $rejected = 0;
    $failure  = 0;

    foreach ($pending as $csv) {
      $result = self::process_csv($csv);
      if     ($result == 'A') $accepted++;
      elseif ($result == 'R') $rejected++;
      else    $failure++;
    }

    if ($accepted) Notify::msg($accepted.' rows accepted as form data.', 'success', TRUE);
    if ($rejected) Notify::msg($rejected.' rows rejected as form data.', 'error', TRUE);
    if ($failure)  Notify::msg($failure.' rows failed to be processed.', 'error', TRUE);

    $this->request->redirect('import/files/'.$id.'/review');
  }

  private function handle_csv_edit($id) {
    $id = $this->request->param('id');

    $csv  = ORM::factory('csv', $id);
    $form_type = $csv->form_type;
    $fields    = SGS_Form_ORM::get_fields($form_type);

    $form = Formo::form();
    foreach ($fields as $key => $value) {
      $form->add(array(
        'alias' => $key,
        'value' => $csv->values[$key]
      ));
    }
    $form->add(array(
      'alias'  => 'save',
      'driver' => 'submit',
      'value'  => 'Save'
    ));

    if ($form->sent($_POST) and $form->load($_POST)) {
      foreach ($csv->values as $key => $value) $data[$key] = $form->$key->val();
      $csv->values = $data;
      $csv->status = 'P';
      try {
        $csv->save();
        $updated = true;
      } catch (Exception $e) {
        Notify::msg('Sorry, update failed. Please try again.', 'error');
      }

      if ($updated) {
        $result = self::process_csv($csv);
        if     ($result == 'A') Notify::msg('Updated data accepted as form data.', 'success', TRUE);
        elseif ($result == 'R') Notify::msg('Updated data rejected as form data.', 'error', TRUE);
        else    Notify::msg('Updated data failed to be processed.', 'error', TRUE);
      }

      $this->request->redirect('import/data/'.$csv->id);
    }

    $body .= View::factory('header')->render();
    $body .= $form->render();

    $this->response->body($body);
  }

  private function handle_csv_list($id = NULL, $form_type = NULL, $range = array()) {
    if ($id) {
      $csvs = ORM::factory('csv')
        ->where('operation', '=', 'I')
        ->and_where('id', '=', $id)
        ->find_all()
        ->as_array();

      $form_type = reset($csvs)->form_type;
    }
    else {
      $form = Formo::form()
        ->add_group('form_type', 'select', SGS::$form_type, NULL, array(
          'label' => 'Form',
          'required' => TRUE
        ))
        ->add('from', 'input', array(
          'label' => 'From'
        ))
        ->add('to', 'input', array(
          'label' => 'To'
        ))
        ->add('search', 'submit', 'Search');

      if ($form->sent($_POST) and $form->load($_POST)) {
        $form_type = $form->form_type->val();
        $from      = $form->from->val();
        $to        = $form->to->val();


        $csvs = ORM::factory('csv')
          ->where('operation', '=', 'I')
          ->and_where('form_type', '=', $form_type)
          ->and_where('timestamp', 'BETWEEN', SGS::db_range($from, $to))
          ->order_by('timestamp', 'desc')
          ->find_all()
          ->as_array();
      }
    }

    if ($csvs) $results = View::factory('csvs')
      ->set('mode', 'import')
      ->set('csvs', $csvs)
      ->set('fields', SGS_Form_ORM::get_fields($form_type))
      ->render();
    else Notify::msg('Search returned an empty set of results.');

    $body .= View::factory('header')->render();
    if ($form) $body .= $form->render();
    $body .= $results;

    $this->response->body($body);
  }

  public function action_index() {
    $body .= View::factory('header');

    $this->response->body($body);
  }

  public function action_upload() {
    $form = Formo::form()
      ->add('import', 'file', array(
        'label' => 'File'
      ))
      ->add('upload', 'submit', 'Upload');

    if ($form->sent($_POST) and $form->load($_POST)) {
      $import = $form->import->val();
      try {
        $excel = PHPExcel_IOFactory::load($import['tmp_name'])->getActiveSheet()->toArray(null,true,true,true);
      } catch (Exception $e) {
        Notify::msg('No file uploaded or unable to find file.', 'error');
      }

      if ($excel) {
        // detect type of file
        $form_type = self::detect_form_type($excel);

        // upload file
        $file = ORM::factory('file');
        $file->name = $import['name'];
        $file->type = $import['type'];
        $file->size = $import['size'];
        $file->operation      = 'I';
        $file->operation_type = $form_type;
        $file->content_md5    = md5_file($import['tmp_name']);

        try {
          $file->save();
          Notify::msg($file->name.' successfully uploaded.', 'success', TRUE);
        } catch (ORM_Validation_Exception $e) {
          foreach ($e->errors('') as $err) Notify::msg($err, 'error');
        } catch (Exception $e) {
          Notify::msg('Sorry, file upload failed. Please try again.', 'error');
        }

        if ($file->id) {
          // file has been saved
          $csv_error   = 0;
          $csv_succsss = 0;

          // parse CSV
          $form_model = ORM::factory($form_type);
          $start = constant('Model_'.$form_type.'::PARSE_START');
          $count = count($excel);
          for ($i = $start; $i <= $count; $i++) {
            $row = $excel[$i];
            if ( ! $data = $form_model->parse_csv($row, $excel)) continue;

            // save CSV
            $csv = ORM::factory('csv');
            $csv->file_id = $file->id;
            $csv->operation = 'I';
            $csv->form_type = $form_type;
            $csv->values = $data;
            try {
              $csv->save();
              $csv_success++;
            } catch (Exception $e) {
              $csv_error++;
            }
          }

          if ($csv_success) Notify::msg($csv_success.' rows successfully parsed.', 'success', TRUE);
          if ($csv_error) Notify::msg($csv_error.' rows failed to be parsed.', 'error', TRUE);

          Notify::msg('Next, click '.HTML::anchor('import/files/'.$file->id.'/process', 'process').
                      ' to validate uploaded data and import it as form data or '.HTML::anchor('import/files/'.$file->id.'/review', 'review').
                      ' to review uploaded data.', TRUE);

          $this->request->redirect('import/files');
        }
      }
    }

    $body .= View::factory('header')->render();
    $body .= $form->render();

    $this->response->body($body);
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
      case 'process': return self::handle_file_process($id);

      default:
      case 'list': return self::handle_file_list($id);
    }
  }

  public function action_data() {
    $id      = $this->request->param('id');
    $command = $this->request->param('command');

    if (!$command && !is_numeric($id)) {
      $id      = NULL;
      $command = $id;
    }

    switch ($command) {
      case 'edit': return self::handle_csv_edit($id);

      default:
      case 'list': return self::handle_csv_list($id);
    }
  }

}

