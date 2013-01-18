<?php

class Controller_Export extends Controller {

  public function before() {
    parent::before();

    if (!Auth::instance()->logged_in()) {
      Notify::msg('Please login.', NULL, TRUE);
      $this->request->redirect('login?destination='.$this->request->uri());
    }
    elseif (!Auth::instance()->logged_in('data')) {
      Notify::msg('Access denied. You must have '.SGS::$roles['data'].' privileges.', 'locked', TRUE);
      $this->request->redirect();
    }
  }

  private function handle_file_list($id = NULL) {
    if (!Request::$current->query()) Session::instance()->delete('pagination.file.list');
    if ($id) {
      Session::instance()->delete('pagination.file.list');

      $files = ORM::factory('file')
        ->where('operation', '=', 'U')
        ->and_where('id', '=', $id)
        ->find_all()
        ->as_array();
    }
    else {

      $site_ids = DB::select('id', 'name')
        ->from('sites')
        ->order_by('name')
        ->execute()
        ->as_array('id', 'name');

      $form = Formo::form()
        ->add_group('operation_type', 'checkboxes', SGS::$form_type, NULL, array('label' => 'Type'))
        ->add_group('site_id', 'select', $site_ids, NULL, array('label' => 'Site', 'attr' => array('class' => 'siteopts')))
        ->add_group('block_id', 'select', array(), NULL, array('label' => 'Block', 'attr' => array('class' => 'blockopts')))
        ->add('search', 'submit', 'Filter');

      if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
        Session::instance()->delete('pagination.file.list');

        $operation_type = $form->operation_type->val();
        $site_id        = $form->site_id->val();
        $block_id       = $form->block_id->val();

        $files = ORM::factory('file')->where('operation', '=', 'U');

        if ($operation_type) $files->and_where('operation_type', 'IN', (array) $operation_type);
        if ($site_id)        $files->and_where('site_id', 'IN', (array) $site_id);
        if ($block_id)       $files->and_where('block_id', 'IN', (array) $block_id);

        Session::instance()->set('pagination.file.list', array(
          'form_type'   => $operation_type,
          'site_id'     => $site_id,
          'block_id'    => $block_id,
        ));
      }
      else {
        if ($settings = Session::instance()->get('pagination.file.list')) {
          $form->operation_type->val($operation_type = $settings['form_type']);
          $form->site_id->val($site_id = $settings['site_id']);
          $form->block_id->val($block_id = $settings['block_id']);
        }

        $files = ORM::factory('file')
          ->where('operation', '=', 'E');

        if ($operation_type) $files->and_where('operation_type', 'IN', (array) $operation_type);
        if ($site_id)        $files->and_where('site_id', 'IN', (array) $site_id);
        if ($block_id)       $files->and_where('block_id', 'IN', (array) $block_id);
      }

      if ($files) {
        $clone = clone($files);
        $pagination = Pagination::factory(array(
          'items_per_page' => 20,
          'total_items' => $clone->find_all()->count()));

        $files = $files
          ->offset($pagination->offset)
          ->limit($pagination->items_per_page);
        if ($sort = $this->request->query('sort')) $files->order_by($sort);
        $files = $files->order_by('timestamp', 'DESC')
          ->find_all()
          ->as_array();
      }
    }

    if ($files) {
      $table = View::factory('files')
        ->set('classes', array('has-pagination'))
        ->set('mode', 'export')
        ->set('files', $files)
        ->render();
      if ($pagination->total_items == 1) Notify::msg($pagination->total_items.' file found');
      elseif ($pagination->total_items) Notify::msg($pagination->total_items.' files found');
    }
    else Notify::msg('No files found');

    if ($form) $content .= $form->render();
    $content .= $table;
    $content .= $pagination;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  private function handle_file_review($id = NULL) {
    if (!$id) $id = $this->request->param('id');

    $file = ORM::factory('file', $id);

    $csvs = $file->csv
      ->where('status', '=', 'P')
      ->find_all()
      ->as_array();

    if ($csvs) $table .= View::factory('csvs')
      ->set('title', 'Pending')
      ->set('csvs', $csvs)
      ->set('fields', SGS_Form_ORM::get_fields($file->operation_type))
      ->render();
    else Notify::msg('No records found.');

    $content .= $table;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  private function handle_download($form_type) {
    set_time_limit(600);

    $model = ORM::factory($form_type);

    $has_block_id = (bool) (in_array($form_type, array('SSF', 'TDF')));
    $has_site_id  = (bool) (in_array($form_type, array('SSF', 'TDF', 'LDF')));

    if ($has_site_id) $site_ids = DB::select('id', 'name')
      ->from('sites')
      ->order_by('name')
      ->execute()
      ->as_array('id', 'name');
    else $operator_ids = DB::select('id', 'name')
      ->from('operators')
      ->order_by('name')
      ->execute()
      ->as_array('id', 'name');

    if ($has_site_id and $has_block_id) $block_ids = DB::select('id', 'name')
      ->from('blocks')
      ->order_by('name')
      ->execute()
      ->as_array('id', 'name');

    $form = Formo::form();
    if ($has_site_id) $form = $form->add_group('site_id', 'select', $site_ids, NULL, array('label' => 'Site', 'attr' => array('class' => 'siteopts')));
    else $form = $form->add_group('operator_id', 'select', $operator_ids, NULL, array('label' => 'Operator'));
    if ($has_site_id and $has_block_id) $form = $form->add_group('block_id', 'select', array(), NULL, array('label' => 'Block', 'attr' => array('class' => 'blockopts')));
    $form = $form
      ->add('from', 'input', array('label' => 'From', 'attr' => array('class' => 'dpicker', 'id' => 'from-dpicker')))
      ->add('to', 'input', array('label' => 'To', 'attr' => array('class' => 'dpicker', 'id' => 'to-dpicker')))
      ->add_group('status', 'checkboxes', SGS::$data_status, array('A'), array('label' => 'Status'))
      ->add('type', 'radios', 'xls', array(
        'options' => array(
          'xls' => SGS::$file_type['xls'],
          'csv' => SGS::$file_type['csv']
        ),
        'label'    => 'Format',
        'required' => TRUE
      ))
      ->add('download', 'submit', array('label' => 'Download'));

    if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
      if ($has_site_id) $site_id = $form->site_id->val();
      else $operator_id = $form->operator_id->val();
      if ($has_site_id and $has_block_id) $block_id = $form->block_id->val();

      $status = $form->status->val();
      $type   = $form->type->val();
      $from   = $form->from->val();
      $to     = $form->to->val();

      $data = ORM::factory($form_type);

      if ($has_site_id) $data->where('site_id', 'IN', (array) $site_id);
      else $data->where('operator_id', 'IN', (array) $operator_id);
      if ($has_site_id and $has_block_id) $data->where('block_id', 'IN', (array) $block_id);

      $data = $data
        ->where('create_date', 'BETWEEN', SGS::db_range($from, $to))
        ->and_where('status', 'IN', (array) $status)
        ->find_all()
        ->as_array();

      switch ($type) {
        case 'csv':
          $excel = new PHPExcel();
          $excel->setActiveSheetIndex(0);
          $writer = new PHPExcel_Writer_CSV($excel);
          $headers = TRUE;
          $mime_type = 'text/csv';
          break;
        case 'xls':
          $filename = APPPATH.'/templates/'.$form_type.'.xls';
          try {
            $reader = new PHPExcel_Reader_Excel5;
            if (!$reader->canRead($filename)) $reader = PHPExcel_IOFactory::createReaderForFile($filename);
            $excel = $reader->load($filename);
          } catch (Exception $e) {
            Notify::msg('Unable to load Excel document template. Please try again.', 'error', TRUE);
          }
          $excel->setActiveSheetIndex(0);
          $writer = new PHPExcel_Writer_Excel5($excel);
          $mime_type = 'application/vnd.ms-excel';
          $headers = FALSE;
          break;
      }

      if ($excel) {
        // data
        $create_date = 0;
        $row = $model::PARSE_START;
        foreach ($data as $item) {
          $item->export_data($excel, $row);
          if (strtotime($item->create_date) > strtotime($create_date)) $create_date = $item->create_date;
          $row++;
        }

        // headers
        $item->export_headers($excel, array(
          'create_date' => $create_date = $create_date ?: SGS::date('now', SGS::PGSQL_DATE_FORMAT)
        ), $headers);

        // temporary file
        $tempname = tempnam(sys_get_temp_dir(), strtolower($form_type).'_').'.'.$type;
        $writer->save($tempname);

        // info
        if ($operator_id) $operator = ORM::factory('operator', $operator_id);
        if ($site_id) {
          $site = ORM::factory ('site', $site_id);
          if (!$operator) $operator = $site->operator;
        }
        if ($block_id) $block = ORM::factory('block', $block_id);

        // properties
        try {
          $ext = $type;
          switch ($form_type) {
            case 'SSF':
              $newdir = implode(DIRECTORY_SEPARATOR, array(
                'export',
                $site->name,
                $form_type,
                $block->name
              ));
              if (!($operator->name and $site->name and $block->name)) {
                Notify::msg('Sorry, cannot identify required properties to create a file.', 'error', TRUE);
                throw new Exception();
              }
              $newname = SGS::wordify($site->name.'_SSF_'.$block->name).'.'.$ext;
              break;

            case 'TDF':
              $newdir = implode(DIRECTORY_SEPARATOR, array(
                'export',
                $site->name,
                $form_type,
                $block->name
              ));
              if (!($operator->name and $site->name and $block->name)) {
                Notify::msg('Sorry, cannot identify required properties to create a file.', 'error', TRUE);
                throw new Exception();
              }
              $newname = SGS::wordify($site->name.'_TDF_'.$block->name.'_'.SGS::date($create_date, 'm_d_Y')).'.'.$ext;
              break;

            case 'LDF':
              $newdir = implode(DIRECTORY_SEPARATOR, array(
                'export',
                $site->name,
                $form_type
              ));
              if (!($operator->name and $site->name)) {
                Notify::msg('Sorry, cannot identify required properties to create a file.', 'error', TRUE);
                throw new Exception();
              }
              $newname = SGS::wordify($site->name.'_LDF_'.SGS::date($create_date, 'm_d_Y')).'.'.$ext;
              break;

            case 'SPECS':
              $newdir = implode(DIRECTORY_SEPARATOR, array(
                'export',
                'specs',
                $operator->tin
              ));
              if (!($operator->name)) {
                Notify::msg('Sorry, cannot identify required properties to create a file.', 'error', TRUE);
                throw new Exception();
              }
              $newname = SGS::wordify('SPECS_'.$operator->name.'_'.SGS::date($create_date, 'm_d_Y')).'.'.$ext;
              break;
          }

          $version = 0;
          $testname = $newname;
          while (file_exists(DOCPATH.$newdir.DIRECTORY_SEPARATOR.$newname)) {
            $newname = substr($testname, 0, strrpos($testname, '.'.$ext)).'_'.($version++).'.'.$ext;
          }

          if (!is_dir(DOCPATH.$newdir) and !mkdir(DOCPATH.$newdir, 0777, TRUE)) {
            Notify::msg('Sorry, cannot access documents folder. Check file access capabilities with the site administrator and try again.', 'error', TRUE); break;
          }
          else if (!(rename($tempname, DOCPATH.$newdir.DIRECTORY_SEPARATOR.$newname) and chmod(DOCPATH.$newdir.DIRECTORY_SEPARATOR.$newname, 0777))) {
            Notify::msg('Sorry, cannot create document. Check file operation capabilities with the site administrator and try again.', 'error', TRUE); break;
          }

          $file = ORM::factory('file');
          $file->name = $testname;
          $file->type = $mime_type;
          $file->size = filesize(DOCPATH.$newdir.DIRECTORY_SEPARATOR.$newname);
          $file->operation      = 'E';
          $file->operation_type = $form_type;
          $file->content_md5    = md5_file(DOCPATH.$newdir.DIRECTORY_SEPARATOR.$newname);
          $file->path           = DIRECTORY_SEPARATOR.str_replace(DOCROOT, '', DOCPATH).$newdir.DIRECTORY_SEPARATOR.$newname;

          if ($operator) $file->operator = $operator;
          if ($site)     $file->site     = $site;
          if ($block)    $file->block    = $block;

          $file->save();
          Notify::msg($file->name.' successfully created.', 'success', TRUE);
        } catch (ORM_Validation_Exception $e) {
          foreach ($e->errors('') as $err) Notify::msg(SGS::errorify($err), 'error', TRUE);
        } catch (Exception $e) {
          Notify::msg('Sorry, unable to save uploaded file.', 'error', TRUE);
        }

        $this->response->send_file(preg_replace('/\/$/', '', DOCROOT).$file->path, $file->name, array('mime_type' => $mime_type));
      }
    }

    $content .= $form->render();

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  public function action_index() {
    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
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

      default:
      case 'list': return self::handle_file_list($id);
    }
  }

  public function action_download() {
    if ($form_type = $this->request->param('id')) return self::handle_download(strtoupper($form_type));

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

}