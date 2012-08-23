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
      $duplicates  = $form_model->find_duplicates($csv->values, $errors);

      $csv->errors      = self::cleanup_errors($errors);
      $csv->suggestions = $suggestions;
      $csv->duplicates  = $duplicates;

      if ($duplicates) $csv->status = 'U';
      else $csv->status = 'R';

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

  private function handle_file_list($id = NULL) {
    if (!Request::$current->query('page')) Session::instance()->delete('pagination.files');
    if ($id) {
      Session::instance()->delete('pagination.csv');

      $files = ORM::factory('file')
        ->where('operation', '=', 'I')
        ->and_where('id', '=', $id)
        ->find_all()
        ->as_array();
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

      $form = Formo::form(array('attr' => array('action' => URL::site('import/files'))))
        ->add_group('form_type', 'select', SGS::$form_type, NULL, array(
          'label' => 'Type',
          'required' => TRUE
        ))
        ->add_group('operator_id', 'select', $operator_ids, NULL, array('label' => 'Operator'))
        ->add_group('site_id', 'select', $site_ids, NULL, array('label' => 'Site'))
        ->add_group('block_id', 'select', $block_ids, NULL, array('label' => 'Block'))
        ->add('search', 'submit', 'Search');

      if ($form->sent($_POST) and $form->load($_POST)->validate()) {
        Session::instance()->delete('pagination.files');

        $operation_type = $form->form_type->val();
        $operator_id    = $form->operator_id->val();
        $site_id        = $form->site_id->val();
        $block_id       = $form->block_id->val();

        $files = ORM::factory('file')
          ->where('operation', '=', 'I')
          ->and_where('operation_type', '=', $operation_type);

        if ($operator_id) $files->and_where('operator_id', 'IN', (array) $operator_id);
        if ($site_id)     $files->and_where('site_id', 'IN', (array) $site_id);
        if ($block_id)    $files->and_where('block_id', 'IN', (array) $block_id);

        Session::instance()->set('pagination.csv', array(
          'form_type'   => $operation_type,
          'operator_id' => $operator_id,
          'site_id'     => $site_id,
          'block_id'    => $block_id,
        ));

        $search = TRUE;
      }
      else {
        if ($settings = Session::instance()->get('pagination.files')) {
          $operation_type = $settings['form_type'];
          $operator_id    = $settings['operator_id'];
          $site_id        = $settings['site_id'];
          $block_id       = $settings['block_id'];
        }

        $files = ORM::factory('file')
          ->where('operation', '=', 'I');

        if ($operation_type) $files->and_where('operation_type', '=', $operation_type);
        if ($operator_id)    $files->and_where('operator_id', 'IN', (array) $operator_id);
        if ($site_id)        $files->and_where('site_id', 'IN', (array) $site_id);
        if ($block_id)       $files->and_where('block_id', 'IN', (array) $block_id);
      }

      if ($files) {
        $clone = clone($files);
        $pagination = Pagination::factory(array(
          'items_per_page' => 50,
          'total_items' => $clone->find_all()->count()));

        $files = $files
          ->offset($pagination->offset)
          ->limit($pagination->items_per_page)
          ->find_all()
          ->as_array();
      }
    }

    if ($files) $table = View::factory('files')
      ->set('classes', array('has-pagination'))
      ->set('mode', 'import')
      ->set('files', $files)
      ->render();
    else Notify::msg('No files found.');

    if ($form) $content .= $form->render();
    $content .= $table;
    $content .= $pagination;

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
    set_time_limit(0);
    if (!$id) $id = $this->request->param('id');
    $file = ORM::factory('file', $id);

    // pending
    $pending = $file->csv
      ->where('status', 'IN', array('P','R'))
      ->find_all()
      ->as_array();

    $accepted   = 0;
    $rejected   = 0;
    $duplicated = 0;
    $failure    = 0;

    foreach ($pending as $csv) {
      $result = self::process_csv($csv);
      if     ($result == 'A') $accepted++;
      elseif ($result == 'R') $rejected++;
      elseif ($result == 'U') $duplicated++;
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
    elseif ($result == 'U') Notify::msg('Updated data is a duplicate of existing form data.', 'error', TRUE);
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
        elseif ($result == 'U') Notify::msg('Updated data is a duplicate of existing form data.', 'error', TRUE);
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

  private function handle_csv_list($id = NULL, $form_type = NULL) {
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

      $form = Formo::form(array('attr' => array('action' => URL::site('import/data'))))
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
        ->add_group('operator_id', 'select', $operator_ids, NULL, array('label' => 'Operator'))
        ->add_group('site_id', 'select', $site_ids, NULL, array('label' => 'Site'))
        ->add_group('block_id', 'select', $block_ids, NULL, array('label' => 'Block'))
        ->add('search', 'submit', 'Search');

      if ($form->sent($_POST) and $form->load($_POST)->validate()) {
        Session::instance()->delete('pagination.csv');

        $form_type = $form->form_type->val();
        $status    = $form->status->val();

        $operator_id = $form->operator_id->val();
        $site_id     = $form->site_id->val();
        $block_id    = $form->block_id->val();

        $csvs = ORM::factory('csv')
          ->where('operation', '=', 'I')
          ->and_where('form_type', '=', $form_type)
          ->and_where('status', 'IN', $status)
          ->order_by('timestamp', 'desc');

        if ($operator_id) $csvs->and_where('operator_id', 'IN', (array) $operator_id);
        if ($site_id)     $csvs->and_where('site_id', 'IN', (array) $site_id);
        if ($block_id)    $csvs->and_where('block_id', 'IN', (array) $block_id);

        Session::instance()->set('pagination.csv', array(
          'form_type'   => $form_type,
          'operator_id' => $operator_id,
          'site_id'     => $site_id,
          'block_id'    => $block_id,
          'status'      => $status,
        ));

        $search = TRUE;
      }
      elseif ($settings = Session::instance()->get('pagination.csv')) {
        $form_type   = $settings['form_type'];
        $status      = $settings['status'];
        $operator_id = $settings['operator_id'];
        $site_id     = $settings['site_id'];
        $block_id    = $settings['block_id'];

        $csvs = ORM::factory('csv')
          ->where('operation', '=', 'I')
          ->and_where('form_type', '=', $form_type)
          ->and_where('status', 'IN', $status)
          ->order_by('timestamp', 'desc');
      }

      if ($csvs) {
        $clone = clone($csvs);
        $pagination = Pagination::factory(array(
          'items_per_page' => 50,
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
    set_time_limit(0);
    $form = Formo::form()
      ->add('import[]', 'file', array(
        'label' => 'File',
        'required' => TRUE,
        'attr'  => array('multiple' => 'multiple')
      ))
      ->add('upload', 'submit', 'Upload');

    if ($form->sent($_POST) and $form->load($_POST)->validate()) {
      $csv_error   = 0;
      $csv_success = 0;
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
              Notify::msg($file->name.' document successfully uploaded.', 'success', TRUE);
            } catch (ORM_Validation_Exception $e) {
              foreach ($e->errors('') as $err) Notify::msg($err, 'error');
            }

            if ($file->id) {
              // parse CSV
              $form_model = ORM::factory($form_type);
              $start = constant('Model_'.$form_type.'::PARSE_START');
              $count = count($excel);
              for ($i = $start; $i <= $count; $i++) {
                $row = $excel[$i];
                if ( ! $data = $form_model->parse_csv($row, $excel)) continue;

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

              try {
                $file->operator_id = $csv->operator_id;
                $file->site_id     = $csv->site_id;
                $file->block_id    = $csv->block_id;
                $file->save();

                try {
                  $tmpname = $import['tmp_name'];
                  switch ($file->operation_type) {
                    case 'SSF':
                      if (!($file->operator->tin && $file->site->name and $file->operation_type)) throw new Exception();
                      $newdir = DOCPATH.implode(DIRECTORY_SEPARATOR, array(
                        $file->operator->tin,
                        $file->site->name,
                        $file->operation_type,
                        $file->block->name
                      ));
                      $newname = $newdir.DIRECTORY_SEPARATOR.$file->site->name.'_SSF_'.$file->block->name.'.'.$ext;
                      break;

                    case 'TDF':
                      if (!($file->operator->tin && $file->site->name and $file->operation_type)) throw new Exception();
                      $newdir = DOCPATH.implode(DIRECTORY_SEPARATOR, array(
                        $file->operator->tin,
                        $file->site->name,
                        $file->operation_type,
                        $file->block->name
                      ));
                      $newname = $newdir.DIRECTORY_SEPARATOR.$file->site->name.'_TDF_'.$file->block->name.'_'.Date::formatted_time(SGS::date($csv->create_date), 'Y_m_d').'.'.$ext;
                      break;

                    case 'LDF':
                      if (!($file->operator->tin && $file->site->name and $file->operation_type)) throw new Exception();
                      $newdir = DOCPATH.implode(DIRECTORY_SEPARATOR, array(
                        $file->operator->tin,
                        $file->site->name,
                        $file->operation_type
                      ));
                      $newname = $newdir.DIRECTORY_SEPARATOR.$file->site->name.'_LDF_'.Date::formatted_time(SGS::date($csv->create_date), 'Y_m_d').'.'.$ext;
                      break;
                  }
                  if (!is_dir($newdir)) mkdir($newdir, 0777, TRUE);
                  if (!(rename($tmpname, $newname) and chmod($newname, 0777))) throw new Exception();
                } catch (Exception $e) {
                  Notify::msg('Sorry, unable to store uploaded file', 'error');
                }
              } catch (Exception $e) {
                Notify::msg('Sorry, unable to save file properties.', 'error');
              }
            }
          }
        }
      }
      if ($csv_success) Notify::msg($csv_success.' records successfully parsed.', 'success', TRUE);
      if ($csv_error) Notify::msg($csv_error.' records failed to be parsed.', 'error', TRUE);

      $this->request->redirect('import/files');
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

