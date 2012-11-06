<?php

class Controller_Barcodes extends Controller {

  public function before() {
    parent::before();

    if (!Auth::instance()->logged_in()) {
      Notify::msg('Please login.', NULL, TRUE);
      $this->request->redirect('login?destination='.$this->request->uri());
    }
    elseif (!Auth::instance()->logged_in('barcodes')) {
      Notify::msg('Sorry, access denied. You must have '.SGS::$roles['barcodes'].' privileges.', 'locked', TRUE);
      $this->request->redirect();
    }
  }

  public function action_index() {
    $id = $this->request->param('id');
    $command = $this->request->param('command');

    if (!$command && !is_numeric($id)) {
      $id      = NULL;
      $command = $id;
    }

    if ($id) {
      Session::instance()->delete('pagination.printjob.list');

      $printjob = ORM::factory('printjob', $id);
      $printjobs = array($printjob);

      if ($command == 'list') {
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
      elseif ($command == 'edit') {
        $printjob = ORM::factory('printjob', $id);
        $form = Formo::form()
          ->orm('load', $printjob, array('site_id'))
          ->add('save', 'submit', 'Update Print Job');

        if ($form->sent($_POST) and $form->load($_POST)->validate()) {
          try {
            $printjob->save();
            Notify::msg('Print job successfully updated.', 'success', TRUE);
            $this->request->redirect('barcodes/'.$printjob->id);
          } catch (Database_Exception $e) {
            Notify::msg('Sorry, unable to save print job due to incorrect or missing input. Please try again.', 'error');
          } catch (Exception $e) {
            Notify::msg('Sorry, print job failed to be saved. Please try again.', 'error');
          }
        }
      }
      elseif ($command == 'download') {
        return $this->action_download($id);
      }
    }
    else {
      if (!Request::$current->query('page')) Session::instance()->delete('pagination.printjob.list');

      $site_ids = DB::select('id', 'name')
        ->from('sites')
        ->order_by('name')
        ->execute()
        ->as_array('id', 'name');

      $form = Formo::form()
        ->add_group('site_id', 'select', $site_ids, NULL, array('label' => 'Site'))
        ->add('search', 'submit', 'Filter');

      if ($form->sent($_POST) and $form->load($_POST)->validate()) {
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

  function action_list() {
    return $this->action_index();
  }

  function action_upload() {
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
            foreach ($e->errors('') as $err) Notify::msg(SGS::errorfy($err), 'error');
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

      if ($_printjob->loaded()) $this->request->redirect('barcodes');
    }

    if ($form) $content .= $form->render();

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  function action_fix() {
    $form = Formo::form()
      ->add('import[]', 'file', array(
        'label'    => 'File',
        'required' => TRUE,
        'attr'  => array('multiple' => 'multiple')
      ))
      ->add('save', 'submit', 'Fix');

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

          // lookup printjob
          for ($i = 0; $i < 10; $i++) {
            $matches = array();
            if (preg_match('/Print\sJob(\sID)?\:\s*(\d+).*/i', $array[$i], $matches)) {
              $number = $matches[2];
              break;
            }
          }

          if (!$number) Notify::msg('Cannot parse print job number from this file', 'error');
          else $printjob = ORM::factory('printjob')
            ->where('number', '=', $number)
            ->find();

          if ($printjob->loaded()) {
            $existing = DB::select('barcode')
              ->from('barcodes')
              ->where('printjob_id', '=', $printjob->id)
              ->execute()
              ->as_array(NULL, 'barcode');

            // prase barcodes
            $start = Model_Printjob::PARSE_START;
            $count = count($array);
            for ($i = $start; $i < ($count - 1); $i++) {
              $line = $array[$i];
              if (! $data = $printjob->parse_txt($line, $array)) continue;
              if (in_array($data['barcode'], $existing)) continue;

              $barcode = ORM::factory('barcode');
              $barcode->printjob = $printjob;
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
      if ($barcode_success) Notify::msg($barcode_success.' barcodes successfully parsed.', 'success');
      if ($barcode_error) Notify::msg($barcode_error.' barcodes failed to be parsed.', 'error');
    }

    if ($form) $content .= $form->render();

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  function action_download($id = NULL) {
    if (!$id) $id = $this->request->param('id');

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

      if ($form->sent($_POST) and $form->load($_POST)->validate()) {
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

}
