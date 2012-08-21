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

    if (!$errors = $form_model->validate_data($csv->values, 'errors')) {
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
    $pagination = Pagination::factory(array(
      'items_per_page' => 50,
    ));

    $files = ORM::factory('file')
      ->where('operation', '=', 'I')
      ->order_by('timestamp', 'desc')
      ->offset($pagination->offset)
      ->limit($pagination->items_per_page)
      ->find_all()
      ->as_array();

    if ($files) $content .= View::factory('files')
      ->set('mode', 'import')
      ->set('files', $files)
      ->render();
    else Notify::msg('No files found.');

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
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

    if ($pending) $table .= View::factory('csvs')
      ->set('title', 'Pending')
      ->set('csvs', $pending)
      ->set('fields', SGS_Form_ORM::get_fields($file->operation_type, TRUE))
      ->render();
    else Notify::msg('No pending records found.');

    if ($accepted) $table .= View::factory('csvs')
      ->set('title', 'Accepted')
      ->set('csvs', $accepted)
      ->set('fields', SGS_Form_ORM::get_fields($file->operation_type, TRUE))
      ->render();
    else Notify::msg('No accepted records found.');

    if ($rejected) $table .= View::factory('csvs')
      ->set('title', 'Rejected')
      ->set('csvs', $rejected)
      ->set('fields', SGS_Form_ORM::get_fields($file->operation_type, TRUE))
      ->render();
    else Notify::msg('No rejected records found.');

    $content .= $table;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
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

    if ($accepted) Notify::msg($accepted.' records accepted as form data.', 'success', TRUE);
    if ($rejected) Notify::msg($rejected.' records rejected as form data.', 'error', TRUE);
    if ($failure)  Notify::msg($failure.' records failed to be processed.', 'error', TRUE);

    $this->request->redirect('import/files/'.$id.'/review');
  }

  private function handle_csv_process($id) {
    $id = $this->request->param('id');

    $csv = ORM::factory('csv', $id);
    if ($csv->status == 'A') {
      Notify::msg('Sorry, import data that has already been processed and accepted cannot be re-processed. Please edit the form data instead.', 'warning', TRUE);
      $this->request->redirect('import/data/'.$id.'/list');
    }

    $form_type = $csv->form_type;
    $fields    = SGS_Form_ORM::get_fields($form_type, TRUE);

    $csv->status = 'P';
    $result = self::process_csv($csv);
    if     ($result == 'A') Notify::msg('Updated data accepted as form data.', 'success', TRUE);
    elseif ($result == 'R') Notify::msg('Updated data rejected as form data.', 'error', TRUE);
    else    Notify::msg('Updated data failed to be processed.', 'error', TRUE);

    $this->request->redirect('import/data/'.$id);
  }

  private function handle_csv_edit($id) {
    $id = $this->request->param('id');

    $csv = ORM::factory('csv', $id);
    if ($csv->status == 'A') {
      Notify::msg('Sorry, import data that has already been processed and accepted cannot be edited. Please edit the form data instead.', 'warning', TRUE);
      $this->request->redirect('import/data/'.$id.'/list');
    }

    $form_type = $csv->form_type;
    $fields    = SGS_Form_ORM::get_fields($form_type, TRUE);

    $form = Formo::form();
    foreach ($fields as $key => $value) {
      $form->add(array(
        'alias' => $key,
        'value' => $csv->values[$key],
        'label' => $value
      ));
    }
    $form->add(array(
      'alias'  => 'save',
      'driver' => 'submit',
      'value'  => 'Save'
    ));

    if ($form->sent($_POST) and $form->load($_POST)->validate()) {
      foreach ($csv->values as $key => $value) {
        if ($form->$key) $data[$key] = $form->$key->val();
      }

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

    $csvs = array($csv);
    $table = View::factory('csvs')
      ->set('mode', 'import')
      ->set('csvs', $csvs)
      ->set('fields', SGS_Form_ORM::get_fields($csv->form_type, TRUE))
      ->render();

    $content .= $form->render();
    $content .= $table;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  private function handle_csv_list($id = NULL, $form_type = NULL, $range = array()) {
    if (!Request::$current->query('page')) Session::instance()->delete('pagination.csv');
    if ($id) {
      Session::instance()->delete('pagination.csv');

      $csvs = ORM::factory('csv')
        ->where('operation', '=', 'I')
        ->and_where('id', '=', $id)
        ->find_all()
        ->as_array();

      $form_type = reset($csvs)->form_type;
    }
    else {
      $operator_ids = DB::select('id', 'name')
        ->from('operators')
        ->execute()
        ->as_array('id', 'name');

      $site_ids = DB::select('id', 'name')
        ->from('sites')
        ->execute()
        ->as_array('id', 'name');

      $block_ids = DB::select('id', 'name')
        ->from('blocks')
        ->execute()
        ->as_array('id', 'name');

      $form = Formo::form(array(
        'attr' => array('action' => URL::site('import/data'))
      ))
        ->add_group('form_type', 'select', SGS::$form_type, NULL, array(
          'label' => 'Data',
          'required' => TRUE
        ))
        ->add_group('status', 'checkboxes', array(
          'P' => 'Pending',
          'A' => 'Accepted',
          'R' => 'Rejected',
        ), NULL, array(
          'label'    => 'Status',
          'required' => TRUE
        ))
        ->add_group('operator_id', 'select', $operator_ids, NULL, array(
          'label' => 'Operator'
        ))
        ->add_group('site_id', 'select', $site_ids, NULL, array(
          'label' => 'Site'
        ))
        ->add_group('block_id', 'select', $block_ids, NULL, array(
          'label' => 'Block'
        ))
        ->add('from', 'input', array(
          'label' => 'From'
        ))
        ->add('to', 'input', array(
          'label' => 'To'
        ))
        ->add('search', 'submit', 'Search');

      if ($form->sent($_POST) and $form->load($_POST)->validate()) {
        Session::instance()->delete('pagination.csv');

        $form_type = $form->form_type->val();
        $status    = $form->status->val();
        $from      = $form->from->val();
        $to        = $form->to->val();

        $operator_id = $form->operator_id->val();
        $site_id     = $form->site_id->val();
        $block_id    = $form->block_id->val();

        $csvs = ORM::factory('csv')
          ->where('operation', '=', 'I')
          ->and_where('form_type', '=', $form_type)
          ->and_where('status', 'IN', $status)
          ->and_where('timestamp', 'BETWEEN', SGS::db_range($from, $to))
          ->order_by('timestamp', 'desc');

        if ($operator_id) $csvs->and_where('operator_id', 'IN', (array) $operator_id);
        if ($site_id)     $csvs->and_where('site_id', 'IN', (array) $site_id);
        if ($block_id)    $csvs->and_where('block_id', 'IN', (array) $block_id);

        Session::instance()->set('pagination.csv', array(
          'form_type' => $form_type,
          'status'    => $status,
          'from'      => $from,
          'to'        => $to
        ));

        $search = TRUE;
      }
      elseif ($settings = Session::instance()->get('pagination.csv')) {
        $form_type = $settings['form_type'];
        $status    = $settings['status'];
        $from      = $settings['from'];
        $to        = $settings['to'];

        $csvs = ORM::factory('csv')
          ->where('operation', '=', 'I')
          ->and_where('form_type', '=', $form_type)
          ->and_where('status', 'IN', $status)
          ->and_where('timestamp', 'BETWEEN', SGS::db_range($from, $to))
          ->order_by('timestamp', 'desc');
      }

      if ($csvs) {
        $clone = clone($csvs);
        $pagination = Pagination::factory(array(
          'items_per_page' => 2,
          'total_items' => $clone->find_all()->count()));

        $csvs = $csvs
          ->offset($pagination->offset)
          ->limit($pagination->items_per_page)
          ->find_all()
          ->as_array();
      }
    }

    if ($csvs) $table = View::factory('csvs')
      ->set('classes', array('has-pagination'))
      ->set('mode', 'import')
      ->set('csvs', $csvs)
      ->set('fields', SGS_Form_ORM::get_fields($form_type, TRUE))
      ->render();
    elseif ($search) Notify::msg('Search returned an empty set of results.');

    if ($form) $content .= $form->render();
    $content .= $table;
    $content .= $pagination;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  public function action_index() {
    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  public function action_upload() {
    $form = Formo::form()
      ->add('import', 'file', array(
        'label' => 'File'
      ))
      ->add('upload', 'submit', 'Upload');

    if ($form->sent($_POST) and $form->load($_POST)->validate()) {
      $import = $form->import->val();
      $info = pathinfo($import['name']);
      $ext  = $info['extension'];

      switch ($ext) {
        case 'csv':  $reader = new PHPExcel_Reader_CSV; break;
        case 'xls':  $reader = new PHPExcel_Reader_Excel5; break;
        case 'xlsx': $reader = new PHPExcel_Reader_Excel2007; break;
        default:
          if (array_filter($info)) Notify::msg('Sorry, uploaded file not supported. Please try again. If you continue to receive this error, ensure that the uploaded file is a CSV or Excel document.', 'error');
          else Notify::msg('Sorry, no upload found or there is an error in the system. Please try again.', 'error');
      }

      if ($reader) {
        try {
          if (!$reader->canRead($import['tmp_name'])) {
            $reader = PHPExcel_IOFactory::createReaderForFile($import['tmp_name']);
          }

          if ($reader instanceof PHPExcel_Reader_IReader) {
            $excel = $reader->load($import['tmp_name'])->setActiveSheetIndex(0)->toArray(NULL, FALSE, TRUE, TRUE);
          }
        } catch (Exception $e) {
          Notify::msg('Sorry, upload processing failed. Please try again. If you continue to receive this error, ensure that the uploaded file contains no formulas or macros.', 'error');
        }

        if ($excel && ($form_type = self::detect_form_type($excel))) {

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

              $item = reset($data);

              // save CSV
              $csv = ORM::factory('csv');
              $csv->file_id     = $file->id;
              $csv->operation   = 'I';
              $csv->form_type   = $form_type;
              $csv->values      = $data;
              try {
                $csv->save();
                $csv_success++;
              } catch (Exception $e) {
                $csv_error++;
              }
            }

            if ($csv_success) Notify::msg($csv_success.' records successfully parsed.', 'success', TRUE);
            if ($csv_error) Notify::msg($csv_error.' records failed to be parsed.', 'error', TRUE);

            Notify::msg('Next, click '.HTML::anchor('import/files/'.$file->id.'/process', 'process').
                        ' to validate uploaded data and import it as form data or '.HTML::anchor('import/files/'.$file->id.'/review', 'review').
                        ' to review uploaded data.', TRUE);

            $this->request->redirect('import/files/'.$file->id.'/process');
          }
        }
      }
    }

    $content .= $form->render();

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  public function action_files() {
    $id      = $this->request->param('id');
    $command = $this->request->param('command');

    if (!$command && !is_numeric($id)) {
      $command = $id;
      $id      = NULL;
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
      $command = $id;
      $id      = NULL;
    }

    switch ($command) {
      case 'edit':    return self::handle_csv_edit($id);
      case 'process': return self::handle_csv_process($id);

      default:
      case 'list':    return self::handle_csv_list($id);
    }
  }

}

