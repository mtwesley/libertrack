<?php

class Controller_Config extends Controller {

  public function before() {
    parent::before();

    if (!Auth::instance()->logged_in()) {
      Notify::msg('Please login.', NULL, TRUE);
      $this->request->redirect('login?destination='.$this->request->uri());
    }
    elseif (!Auth::instance()->logged_in('admin')) {
      Notify::msg('Access denied. You must have '.SGS::$roles['admin'].' privileges.', 'locked', TRUE);
      $this->request->redirect();
    }
  }

  public function action_index() {
    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  public function action_tolerances() {
    if (!Auth::instance()->logged_in('tolerances')) {
      Notify::msg('Access denied. You must have '.SGS::$roles['tolerances'].' privileges.', 'locked', TRUE);
      $this->request->redirect('analysis');
    }

    if ($values = $this->request->post()) {
      foreach ($values as $key => $value) {
        list($form_type, $check, $type) = explode('-', $key);
        try {
          DB::update('tolerances')
            ->set(array($type => (float) $value))
            ->where('form_type', '=', $form_type)
            ->and_where('check', '=', $check)
            ->execute();
        } catch (Database_Exception $e) {
          Notify::msg('Sorry, tolerance failed to be saved due to input. Please try again.', 'error');
        } catch (Exception $e) {
          Notify::msg('Sorry, tolerance failed to be updated. Please try again.', 'error');
        }
      }

      Notify::msg($success.'Tolerances updated.', 'success');
    }

    $content .= View::factory('tolerances');

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  public function action_operators() {
    $id = $this->request->param('id');

    $operator = ORM::factory('operator', $id);
    $form = Formo::form(array('attr' => array('style' => ($id or $_POST) ? '' : 'display: none;')))
      ->orm('load', $operator, array('sites', 'user_id', 'timestamp'), true)
      ->add('save', 'submit', array(
        'label' => $id ? 'Update Operator' : 'Add a New Operator'
      ));

    if ($id) $form->remove('tin');

    if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
      try {
        $operator->save();
        if ($id) Notify::msg('Operator successfully updated.', 'success', TRUE);
        else Notify::msg('Operator successfully added.', 'success', TRUE);

        $this->request->redirect('config/operators');
      } catch (Database_Exception $e) {
        Notify::msg('Sorry, unable to save operator due to incorrect or missing input. Please try again.', 'error');
      } catch (Exception $e) {
        Notify::msg('Sorry, operator failed to be saved. Please try again.', 'error');
      }
    }

    if ($id === null) {
      $pagination = Pagination::factory(array(
        'items_per_page' => 20,
        'total_items' => $operator->find_all()->count()));

      $operators = ORM::factory('operator')
        ->offset($pagination->offset)
        ->limit($pagination->items_per_page);
      if ($sort = $this->request->query('sort')) $operators->order_by($sort);
      $operators = $operators->order_by('name')
        ->find_all()
        ->as_array();

      $table .= View::factory('operators')
        ->set('classes', array('has-pagination'))
        ->set('operators', $operators);

      if ($pagination->total_items == 1) Notify::msg($pagination->total_items.' operator found');
      elseif ($pagination->total_items) Notify::msg($pagination->total_items.' operators found');
      else Notify::msg('No operators found');
    }

    $content .= ($id or $_POST) ? $form->render() : SGS::render_form_toggle($form->save->get('label')).$form->render();
    $content .= $table;
    $content .= $pagination;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  public function action_sites() {
    if (!Request::$current->query()) Session::instance()->delete('pagination.sites.list');

    $id   = $this->request->param('id');
    $form = $this->request->post('form');

    $site = ORM::factory('site', $id);
    $add_form = Formo::form(array('attr' => array('style' => ($id or $form == 'add_form') ? '' : 'display: none;')))
      ->orm('load', $site, array('blocks', 'printjobs', 'invoices', 'user_id', 'timestamp'), true)
      ->add('form', 'hidden', 'add_form')
      ->add('save', 'submit', array(
        'label' => $id ? 'Update Site' : 'Add a New Site'
      ));

    if ($id) $add_form->remove('name');

    $operator_ids = DB::select('id', 'name')
      ->from('operators')
      ->order_by('name')
      ->execute()
      ->as_array('id', 'name');

    $filter_form = Formo::form()
      ->add_group('operator_id', 'select', $operator_ids, NULL, array('label' => 'Operator'))
      ->add('form', 'hidden', 'filter_form')
      ->add('filter', 'submit', 'Filter');

    if ($form == 'add_form' and $add_form->sent($_REQUEST) and $add_form->load($_REQUEST)->validate()) {
      try {
        $site->save();
        if ($id) Notify::msg('Site successfully updated.', 'success', TRUE);
        else Notify::msg('Site successfully added.', 'success', TRUE);

        $this->request->redirect('config/sites');
      } catch (Database_Exception $e) {
        Notify::msg('Sorry, unable to save site due to incorrect or missing input. Please try again.', 'error');
      } catch (Exception $e) {
        Notify::msg('Sorry, site failed to be saved. Please try again.', 'error');
      }
    } elseif ($form == 'filter_form' and $filter_form->sent($_REQUEST) and $filter_form->load($_REQUEST)->validate()) {
      Session::instance()->delete('pagination.sites.list');

      $operator_id = $filter_form->operator_id->val();

      Session::instance()->set('pagination.sites.list', array(
        'operator_id' => $operator_id,
      ));
    } elseif ($settings = Session::instance()->get('pagination.sites.list')) {
      $filter_form->operator_id->val($operator_id = $settings['operator_id']);
    }

    if ($id) {
      $sites = array_filter(array(ORM::factory('site', $id)));
    } else {
      $sites = ORM::factory('site');
      if ($operator_id) $sites = $sites->where('operator_id', '=', $operator_id);

      $clone = clone($sites);
      $pagination = Pagination::factory(array(
        'items_per_page' => 20,
        'total_items' => $clone->find_all()->count()));

      $sites = $sites
        ->offset($pagination->offset)
        ->limit($pagination->items_per_page);
      if ($sort = $this->request->query('sort')) $sites->order_by($sort);
      $sites = $sites->order_by('name')
        ->find_all()
        ->as_array();
    }

    $table .= View::factory('sites')
      ->set('classes', array('has-pagination'))
      ->set('sites', $sites);

    if ($pagination->total_items == 1) Notify::msg($pagination->total_items.' site found');
    elseif ($pagination->total_items) Notify::msg($pagination->total_items.' sites found');
    else Notify::msg('No sites found');

    $content .= ($id or $form == 'add_form') ? $add_form->render() : SGS::render_form_toggle($add_form->save->get('label')).$add_form->render();
    if (!$id) $content .= $filter_form->render();
    $content .= $table;
    $content .= $pagination;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  public function action_blocks() {
    if (!Request::$current->query()) Session::instance()->delete('pagination.blocks.list');

    $id   = $this->request->param('id');
    $form = $this->request->post('form');

    $block = ORM::factory('block', $id);
    $add_form = Formo::form(array('attr' => array('style' => ($id or $form == 'add_form') ? '' : 'display: none;')))
      ->orm('load', $block, array('user_id', 'timestamp'), true)
      ->add('form', 'hidden', 'add_form')
      ->add('save', 'submit', array(
        'label' => $id ? 'Update Block' : 'Add a New Block'
      ))
      ->order(array('name' => 0));

    if ($id) $add_form->remove('name');

    $site_ids = DB::select('id', 'name')
      ->from('sites')
      ->order_by('name')
      ->execute()
      ->as_array('id', 'name');

    $filter_form = Formo::form()
      ->add_group('site_id', 'select', $site_ids, NULL, array('label' => 'Site'))
      ->add('form', 'hidden', 'filter_form')
      ->add('filter', 'submit', 'Filter');

    if ($add_form->sent($_REQUEST) and $add_form->load($_REQUEST)->validate()) {
      try {
        $block->save();
        if ($id) Notify::msg('Block successfully updated.', 'success', TRUE);
        else Notify::msg('Block successfully added.', 'success', TRUE);

        $this->request->redirect('config/blocks');
      } catch (Database_Exception $e) {
        Notify::msg('Sorry, unable to save block due to incorrect or missing input. Please try again.', 'error');
      } catch (Exception $e) {
        Notify::msg('Sorry, block failed to be saved. Please try again.', 'error');
      }
    } elseif ($form == 'filter_form' and $filter_form->sent($_REQUEST) and $filter_form->load($_REQUEST)->validate()) {
      Session::instance()->delete('pagination.blocks.list');

      $site_id = $filter_form->site_id->val();

      Session::instance()->set('pagination.blocks.list', array(
        'site_id' => $site_id,
      ));
    } elseif ($settings = Session::instance()->get('pagination.blocks.list')) {
      $filter_form->site_id->val($site_id = $settings['site_id']);
    }

    if ($id) {
      $blocks = array_filter(array(ORM::factory('block', $id)));
    } else {
      $blocks = ORM::factory('block');
      if ($site_id) $blocks = $blocks->where('site_id', '=', $site_id);

      $clone = clone($blocks);
      $pagination = Pagination::factory(array(
        'items_per_page' => 20,
        'total_items' => $clone->find_all()->count()));

      $blocks = $blocks
        ->offset($pagination->offset)
        ->limit($pagination->items_per_page);
      if ($sort = $this->request->query('sort')) $blocks->order_by($sort);
      $blocks = $blocks->order_by('site_id')
        ->order_by('name')
        ->find_all()
        ->as_array();
    }

    $table = View::factory('blocks')
      ->set('classes', array('has-pagination'))
      ->set('blocks', $blocks);

    if ($pagination->total_items == 1) Notify::msg($pagination->total_items.' block found');
    elseif ($pagination->total_items) Notify::msg($pagination->total_items.' blocks found');
    else Notify::msg('No blocks found');

    $content .= ($id or $form == 'add_form') ? $add_form->render() : SGS::render_form_toggle($add_form->save->get('label')).$add_form->render();
    if (!$id) $content .= $filter_form->render();
    $content .= $table;
    $content .= $pagination;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  public function action_species() {
    $id = $this->request->param('id');

    $species = ORM::factory('species', $id);
    $form = Formo::form(array('attr' => array('style' => ($id or $_POST) ? '' : 'display: none;')))
      ->orm('load', $species, array('user_id', 'timestamp'), true)
      ->add('save', 'submit', array(
        'label' => $id ? 'Update Species' : 'Add a New Species'
      ));

    if ($id) $add_form->remove('code');

    if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
      try {
        $species->save();
        if ($id) Notify::msg('Species successfully updated.', 'success', TRUE);
        else Notify::msg('Species successfully added.', 'success', TRUE);

        $this->request->redirect('config/species');
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
        ->offset($pagination->offset)
        ->limit($pagination->items_per_page);
      if ($sort = $this->request->query('sort')) $speciess->order_by($sort);
      $speciess = $speciess->order_by('trade_name')
        ->find_all()
        ->as_array();

      $table .= View::factory('species')
        ->set('classes', array('has-pagination'))
        ->set('species', $speciess);

      if ($pagination->total_items) Notify::msg($pagination->total_items.' species found');
      else Notify::msg('No species found');
    }

    $content .= ($id or $_POST) ? $form->render() : SGS::render_form_toggle($form->save->get('label')).$form->render();
    $content .= $table;
    $content .= $pagination;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  public function action_printjobs() {
    if (!Auth::instance()->logged_in()) {
      Notify::msg('Please login.', NULL, TRUE);
      $this->request->redirect('login?destination='.$this->request->uri());
    }
    elseif (!Auth::instance()->logged_in('barcodes')) {
      Notify::msg('Access denied. You must have '.SGS::$roles['barcodes'].' privileges.', 'locked', TRUE);
      $this->request->redirect();
    }

    $id        = $this->request->param('id');
    $command   = $this->request->param('command');

    if (!$command && !is_numeric($id)) {
      $command = $id;
      $id      = NULL;
    }

    switch ($command) {
      case 'upload': return self::handle_printjob_upload();
      case 'download': return self::handle_printjob_download();
      case 'label': return self::handle_printjob_labels($id);

      case 'edit': return self::handle_printjob_edit($id);
      case 'list':
      default: return self::handle_printjob_list($id);
    }

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  private function handle_printjob_edit($id) {
    $printjob  = ORM::factory('printjob', $id);
    $printjobs = array($printjob);

    $form = Formo::form()
      ->orm('load', $printjob, array('site_id'))
      ->add('save', 'submit', 'Update Print Job');

    if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
      try {
        $printjob->save();
        Notify::msg('Print job successfully updated.', 'success', TRUE);
        $this->request->redirect('printjobs/'.$printjob->id);
      } catch (Database_Exception $e) {
        Notify::msg('Sorry, unable to save print job due to incorrect or missing input. Please try again.', 'error');
      } catch (Exception $e) {
        Notify::msg('Sorry, print job failed to be saved. Please try again.', 'error');
      }
    }

    if ($printjobs) $table .= View::factory('printjobs')
      ->set('classes', array('has-pagination'))
      ->set('printjobs', $printjobs);

    if ($form) $content .= $form->render();
    $content .= $table;
    $content .= $pagination;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  private function handle_printjob_list($id) {
    if ($id) {
      Session::instance()->delete('pagination.printjob.list');

      $printjob = ORM::factory('printjob', $id);
      $printjobs = array($printjob);

      if ($command == 'barcodes') {
        $pagination = Pagination::factory(array(
          'items_per_page' => 50,
          'total_items' => $printjob->barcodes->find_all()->count()));

        $barcodes = $printjob->barcodes
          ->offset($pagination->offset)
          ->limit($pagination->items_per_page);
        if ($sort = $this->request->query('sort')) $barcodes->order_by($sort);
        $barcodes = $barcodes->order_by('printjob_id')
          ->order_by('barcode')
          ->find_all()
          ->as_array();

        if ($pagination->total_items == 1) Notify::msg($pagination->total_items.' barcode found');
        elseif ($pagination->total_items) Notify::msg($pagination->total_items.' barcodes found');
        else Notify::msg('No barcodes found');
      }
    }
    else {
      if (!Request::$current->query()) Session::instance()->delete('pagination.printjob.list');

      $site_ids = DB::select('id', 'name')
        ->from('sites')
        ->order_by('name')
        ->execute()
        ->as_array('id', 'name');

      $form = Formo::form()
        ->add_group('site_id', 'select', $site_ids, NULL, array('label' => 'Site'))
        ->add('search', 'submit', 'Filter');

      if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
        Session::instance()->delete('pagination.printjob.list');
        $site_id = $form->site_id->val();

        $printjobs = ORM::factory('printjob');
        if ($site_id) $printjobs->and_where('site_id', 'IN', (array) $site_id);

        Session::instance()->set('pagination.printjob.list', array(
          'site_id' => $site_id,
        ));
      }
      else {
        if ($settings = Session::instance()->get('pagination.printjob.list')) {
          $form->site_id->val($site_id = $settings['site_id']);
        }

        $printjobs = ORM::factory('printjob');
        if ($site_id) $printjobs->and_where('site_id', 'IN', (array) $site_id);
      }

      if ($printjobs) {
        $clone = clone($printjobs);
        $pagination = Pagination::factory(array(
          'items_per_page' => 20,
          'total_items' => $clone->find_all()->count()));

        $printjobs = $printjobs
          ->offset($pagination->offset)
          ->limit($pagination->items_per_page);
        if ($sort = $this->request->query('sort')) $printjobs->order_by($sort);
        $printjobs = $printjobs->order_by('number')
          ->find_all()
          ->as_array();

        if ($pagination->total_items == 1) Notify::msg($pagination->total_items.' print job found');
        elseif ($pagination->total_items) Notify::msg($pagination->total_items.' print jobs found');
        else Notify::msg('No print jobs found');
      }
    }

    if ($printjobs) $table .= View::factory('printjobs')
      ->set('classes', array('has-pagination'))
      ->set('printjobs', $printjobs);

    if ($barcodes) $table .= View::factory('barcodes')
      ->set('classes', array('has-pagination'))
      ->set('barcodes', $barcodes);

    if ($form) $content .= $form->render();
    $content .= $table;
    $content .= $pagination;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  private function handle_printjob_upload() {
    $printjob = ORM::factory('printjob');
    $form = Formo::form()
      ->orm('load', $printjob, array('site_id'))
      ->add('upload[]', 'file', array(
        'label'    => 'File',
        'required' => TRUE,
        'attr'  => array('multiple' => 'multiple')
      ))
      ->add('save', 'submit', 'Upload');

    if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
      $barcode_success = 0;
      $barcode_error = 0;

      $num_files = count(reset($_FILES['upload']));
      for ($j = 0; $j < $num_files; $j++) {
        $upload = array(
          'name'     => $_FILES['upload']['name'][$j],
          'type'     => $_FILES['upload']['type'][$j],
          'tmp_name' => $_FILES['upload']['tmp_name'][$j],
          'error'    => $_FILES['upload']['error'][$j],
          'size'     => $_FILES['upload']['size'][$j]
        );

        $info = pathinfo($upload['name']);
        if (!array_filter($info)) Notify::msg('Sorry, no upload found or there is an error in the system. Please try again.', 'error');
        else {

          $array = file($upload['tmp_name']);

          // upload file
          $file = ORM::factory('file');
          $file->name = $upload['name'];
          $file->type = $upload['type'];
          $file->size = $upload['size'];
          $file->operation = 'U';
          $file->operation_type = 'PJ';
          $file->content_md5 = md5_file($upload['tmp_name']);

          try {
            $file->save();
            Notify::msg($file->name.' print job successfully uploaded.', 'success', TRUE);
          } catch (ORM_Validation_Exception $e) {
            foreach ($e->errors('') as $err) Notify::msg(SGS::errorify($err), 'error');
          } catch (Exception $e) {
            Notify::msg('Sorry, print job upload failed. Please try again.', 'error');
          }

          if ($file->id) {
            // save printjob
            $_printjob = clone($printjob);
            $_printjob->allocation_date = SGS::date('now', SGS::PGSQL_DATE_FORMAT);
            for ($i = 0; $i < 10; $i++) {
              $matches = array();
              if (preg_match('/Print\sJob(\sID)?\:\s*(\d+).*/i', $array[$i], $matches)) {
                $_printjob->number = $matches[2];
                break;
              }
            }

            try {
              $_printjob->save();
            } catch (Exception $e) {
              Notify::msg('Sorry, print job failed to be saved. Please try again.', 'error', TRUE);
              try {
                $file->delete();
              } catch (Exception $e) {
                Notify::msg('Sorry, attempting to delete an non-existing file failed.', 'warning', TRUE);
              }
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

      if (isset($_printjob) and $_printjob->loaded()) $this->request->redirect('barcodes');
    }

    if ($form) $content .= $form->render();

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  private function handle_printjob_download($id = NULL) {
    if ($id) {
      $barcodes = DB::select('barcode')
        ->from('barcodes')
        ->where('printjob_id', '=', $id);

      $printjob = ORM::factory('printjob', $id);
    }
    else {
      $site_ids = DB::select('id', 'name')
        ->from('sites')
        ->order_by('name')
        ->execute()
        ->as_array('id', 'name');

      $form = Formo::form()
        ->add_group('site_id', 'select', $site_ids, NULL, array('label' => 'Site'))
        ->add('download', 'submit', 'Download');

      if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
        $site_id = $form->site_id->val();

        $site_name = DB::select('id', 'name')
          ->from('sites')
          ->where('id', 'IN', (array) $site_id)
          ->execute()
          ->as_array('id', 'name');

        $barcodes = DB::select('barcode')
          ->from('barcodes')
          ->join('printjobs')
          ->on('barcodes.printjob_id', '=', 'printjobs.id')
          ->where('printjobs.site_id', 'IN', (array) $site_id);
      }
    }

    if (!is_null($barcodes)) {
      if ($barcodes = $barcodes->execute()->as_array()) {
        $text = "% Total barcodes: ".count($barcodes);
        if ($id) $text .= "\n% Print Job: ".$id;
        $text .= "\n% Requested by: ".Auth::instance()->get_user()->name;
        $text .= "\n% Role: LiberTrack, Barcode Management";
        $text .= "\n% Requested from IP: ".Request::$client_ip;
        $text .= "\n% Generated at: ".SGS::datetime('now', 'm/d/Y H:i:s T,(\G\M\TP)');

        foreach ($barcodes as $barcode) $text .= "\n".$barcode['barcode'];
        if ($id) $text .= "\n% End of Print Job: ";

        try {
          $tempname = tempnam(sys_get_temp_dir(), 'PJ_download_').'.txt';

          if ($printjob) $fullname = 'PrintJob_'.$printjob->number;
          elseif ($site_name) $fullname = 'Masterlist_'.preg_replace('/\W/', '_', implode('_', (array) $site_name));
          else $fullname = 'Masterlist';
          $fullname .= '.txt';

          file_put_contents($tempname, $text);

          $this->response->send_file($tempname, $fullname, array('mime_type' => 'text/plain', 'delete' => TRUE));
        } catch (Exception $e) {
          Notify::msg('Sorry, unable to download barcode masterlist. Please try again.', 'error');
        }
      }
      else {
        Notify::msg('No barcodes match criteria for download.', 'warning');
      }
    }

    if ($form) $content .= $form->render();

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  private function handle_printjob_labels($id) {
    $barcodes = array();
    $printjob = ORM::factory('printjob', $id);

    if ($printjob->loaded()) $barcodes = DB::select('barcode')
      ->distinct(TRUE)
      ->from('barcodes')
      ->where('printjob_id', '=', (array) $id)
      ->execute()
      ->as_array(NULL, 'barcode');

    $pdf = new Label('L7159');
    $pdf->Open();

    foreach($barcodes as $barcode) {
      $tempname = tempnam(sys_get_temp_dir(), 'br_');
      Barcode::png($barcode, $tempname);
      for ($i = 0; $i < 5; $i++) {
        $pdf->Add_Barcode_Label($barcode, $tempname, 'PNG');
      }
      $pdf->Add_Barcode_Label(NULL, NULL, NULL);
    }

    die($pdf->Output('PRINTJOB_LABELS_'.$printjob->number.'.pdf', 'D'));
  }

  public function action_barcodes() {
    if (!Auth::instance()->logged_in()) {
      Notify::msg('Please login.', NULL, TRUE);
      $this->request->redirect('login?destination='.$this->request->uri());
    }
    elseif (!Auth::instance()->logged_in('barcodes')) {
      Notify::msg('Access denied. You must have '.SGS::$roles['barcodes'].' privileges.', 'locked', TRUE);
      $this->request->redirect();
    }

    $id        = $this->request->param('id');
    $command   = $this->request->param('command');

    if (!$command && !is_numeric($id)) {
      $command = $id;
      $id      = NULL;
    }

    switch ($command) {
      case 'edit': return self::handle_barcode_edit($id);
      case 'query': return self::handle_barcode_query();
      case 'list':
      default: return self::handle_barcode_list($id);
    }

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  private function handle_barcode_edit($id) {
    $barcode = ORM::factory('barcode', $id);
    $barcodes = array($barcode);

    $form = Formo::form()
      ->orm('load', $barcode)
      ->add('save', 'submit', 'Update Print Job');

    if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
      try {
        $barcode->save();
        Notify::msg('Print job successfully updated.', 'success', TRUE);
        $this->request->redirect('config/barcodes/'.$barcode->id);
      } catch (Database_Exception $e) {
        Notify::msg('Sorry, unable to save print job due to incorrect or missing input. Please try again.', 'error');
      } catch (Exception $e) {
        Notify::msg('Sorry, print job failed to be saved. Please try again.', 'error');
      }
    }

    if ($barcodes) $table .= View::factory('barcodes')
      ->set('classes', array('has-pagination'))
      ->set('barcodes', $barcodes);

    if ($form) $content .= $form->render();
    $content .= $table;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  private function handle_barcode_list($id = NULL) {
    if ($id) {
      Session::instance()->delete('pagination.barcode.list');

      $barcode = ORM::factory('barcode', $id);
      $barcodes = array($barcode);

      $data = array();

      foreach (array('SSF', 'TDF', 'LDF', 'SPECS') as $key) {
        $search = ORM::factory($key);
        foreach (array_keys($search->table_columns()) as $field) if (strpos($field, 'barcode') !== FALSE) $search->or_where($field, '=', $barcode->id);
        foreach ($search = $search->find_all() as $item) $data[$key][] = $item;
      }

      foreach ($data as $type => $items) switch ($type) {
        case 'SSF':
        case 'TDF':
        case 'LDF':
        case 'SPECS':
          $data_table .= View::factory('data')
            ->set('classes', array('has-section'))
            ->set('form_type', $type)
            ->set('data', $items)
            ->set('options', array('header' => FALSE, 'hide_header_info' => TRUE))
            ->render();
      }
    }
    else {
      if (!Request::$current->query()) Session::instance()->delete('pagination.barcode.list');

      $site_ids = DB::select('id', 'name')
        ->from('sites')
        ->order_by('name')
        ->execute()
        ->as_array('id', 'name');

      $form = Formo::form()
        ->add_group('site_id', 'select', $site_ids, NULL, array('label' => 'Site'))
        ->add_group('type', 'checkboxes', SGS::$barcode_type, NULL, array('label' => 'Type'))
        ->add('search', 'submit', 'Filter');

      if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
        Session::instance()->delete('pagination.barcode.list');
        $site_id = $form->site_id->val();
        $type    = $form->type->val();

        $barcodes = ORM::factory('barcode');
        if ($site_id) {
          $barcodes = $barcodes
            ->join('printjobs')
            ->on('printjob_id', '=', 'printjobs.id')
            ->and_where('printjobs.site_id', 'IN', (array) $site_id);
        }
        if ($type) $barcodes->where('type', 'IN', (array) $type);

        Session::instance()->set('pagination.barcode.list', array(
          'site_id' => $site_id,
          'type'    => $type
        ));

      }
      else {
        if ($settings = Session::instance()->get('pagination.barcode.list')) {
          $form->site_id->val($site_id = $settings['site_id']);
          $form->type->val($type = $settings['type']);
        }

        $barcodes = ORM::factory('barcode');
        if ($site_id) {
          $barcodes = $barcodes
            ->join('printjobs')
            ->on('printjob_id', '=', 'printjobs.id')
            ->and_where('printjobs.site_id', 'IN', (array) $site_id);
        }
        if ($type) $barcodes->where('type', 'IN', (array) $type);
      }

      if ($barcodes) {
        $clone = clone($barcodes);
        $pagination = Pagination::factory(array(
          'items_per_page' => 50,
          'total_items' => $clone->find_all()->count()));

        $barcodes = $barcodes
          ->offset($pagination->offset)
          ->limit($pagination->items_per_page);
        if ($sort = $this->request->query('sort')) $barcodes->order_by($sort);
        $barcodes = $barcodes->order_by('barcode')
          ->find_all()
          ->as_array();

        if ($pagination->total_items == 1) Notify::msg($pagination->total_items.' barcodes found');
        elseif ($pagination->total_items) Notify::msg($pagination->total_items.' barcodes found');
        else Notify::msg('No barcodes found');
      }
    }

    if ($barcodes) $table .= View::factory('barcodes')
      ->set('classes', array('has-pagination'))
      ->set('barcodes', $barcodes);

    if ($form) $content .= $form->render();
    $content .= $table;
    $content .= $data_table;
    $content .= $pagination;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  private function handle_barcode_query() {
    $operator_ids = DB::select('id', 'name')
      ->from('operators')
      ->order_by('name')
      ->execute()
      ->as_array('id', 'name');

    $site_ids = DB::select('id', 'name')
      ->from('sites')
      ->order_by('name')
      ->execute()
      ->as_array('id', 'name');

    $form = Formo::form(array('attr' => array('class' => 'search')))
      ->add('barcode', 'input', array('label' => 'Barcode', 'rules' => array(array('min_length', array(':value', 3))), 'attr' => array('class' => 'barcode-text autocomplete-barcode-barcode')))
      ->add_group('operator_id', 'select', $operator_ids, NULL, array('label' => 'Operator', 'attr' => array('class' => 'site_operatoropts')))
      ->add_group('site_id', 'select', $site_ids, NULL, array('label' => 'Site', 'attr' => array('class' => 'siteopts')))
      ->add_group('similarity', 'select', array('exact' => 'Exact', 'high' => 'High', 'medium' => 'Medium', 'low' => 'Low'), 'medium', array('label' => 'Similarity'))
      ->add('submit', 'submit', 'Query');

    if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
      $barcode     = trim(strtoupper($form->barcode->val()));
      $similarity  = $form->similarity->val();
      $operator_id = $form->operator_id->val();
      $site_id     = $form->site_id->val();

      if ($site_id)     $args['sites.id'] = $site_id;
      if ($operator_id) $args['operators.id'] = $operator_id;

      switch ($similarity) {
        case 'exact': $suggestions = SGS::suggest_barcode($barcode, $args ?: array(), 'id', TRUE, 2, 0.8, 1, 25, 0); break;
        case 'high': $suggestions = SGS::suggest_barcode($barcode, $args ?: array(), 'id', FALSE, 2, 0.5, 5, 25, 0); break;
        case 'low': $suggestions = SGS::suggest_barcode($barcode, $args ?: array(), 'id', FALSE, 2, 0.1, 10, 25, 0); break;

        case 'medium':
        default: $suggestions = SGS::suggest_barcode($barcode, $args ?: array(), 'id', FALSE, 2, 0.3, 7, 25, 0); break;
      }

      if ($suggestions) {
        Notify::msg(count($suggestions).' results found.', 'success');

        $barcodes = ORM::factory('barcode')
          ->where('id', 'IN', (array) $suggestions)
          ->find_all()
          ->as_array();
      } else Notify::msg('No results found.');

      if ($barcodes) $table = View::factory('barcodes')
        ->set('classes', array('has-pagination'))
        ->set('barcodes', $barcodes);
    }

    $content .= $form->render();
    $content .= $table;
    $content .= $pagination;

    $view = View::factory('main')
      ->set('content', $content)
      ->set('left', $left);

    $this->response->body($view);
  }

}
