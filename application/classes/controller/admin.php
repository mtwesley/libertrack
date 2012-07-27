<?php

class Controller_Admin extends Controller {

  public function action_index() {
    $this->response->body(View::factory('header'));
  }

  public function action_operators() {
    $id = $this->request->param('id');
    $body = View::factory('header')->render();

    $model = ORM::factory('operator', $id);
    $form = Formo::form()
      ->orm('load', $model, array('sites', 'user_id', 'timestamp'), true)
      ->add($id ? 'update' : 'save', 'submit');

    if ($form->sent($_POST) and $form->load($_POST)) {
      try {
        $id ? $model->update() : $model->save();
        $body .= '<p>Operator saved.</p>';
      } catch (Exception $e) {
        $body .= '<p>Unable to save operator.</p>';
      }
    }

    $body .= $form->render();

    if ($id === null) {

      $operators = ORM::factory('operator')->find_all()->as_array();
      $body .= '<table border="1">';
      $body .= '<tr>';
      $body .= '<td><strong>tin</strong></td>';
      $body .= '<td><strong>name</strong></td>';
      $body .= '<td><strong>contact</strong></td>';
      $body .= '<td><strong>address</strong></td>';
      $body .= '<td><strong>email</strong></td>';
      $body .= '<td><strong>phone</strong></td>';
      $body .= '<td></td>';
      $body .= '</tr>';
      foreach ($operators as $operator) {
        $body .= '<tr>';
        $body .= '<td>'.$operator->tin.'</td>';
        $body .= '<td>'.$operator->name.'</td>';
        $body .= '<td>'.$operator->contact.'</td>';
        $body .= '<td>'.$operator->address.'</td>';
        $body .= '<td>'.$operator->email.'</td>';
        $body .= '<td>'.$operator->phone.'</td>';
        $body .= '<td>'.HTML::anchor('/admin/operators/'.$operator->id, 'edit').'</td>';
        $body .= '</tr>';
      }
      $body .= '</table>';

    }

    $this->response->body($body);
  }

  public function action_sites() {
    $id = $this->request->param('id');
    $body = View::factory('header')->render();

    $model = ORM::factory('site', $id);
    $form = Formo::form()
      ->orm('load', $model, array('blocks', 'printjobs', 'invoices', 'user_id', 'timestamp'), true)
      ->add($id ? 'update' : 'save', 'submit');

    if ($form->sent($_POST) and $form->load($_POST)) {
      try {
        $id ? $model->update() : $model->save();
        $body .= '<p>Site saved.</p>';
      } catch (Exception $e) {
        $body .= '<p>Unable to save site.</p>';
      }
    }

    $body .= $form->render();

    if ($id === null) {

      $sites = ORM::factory('site')->find_all()->as_array();
      $body .= '<table border="1">';
      $body .= '<tr>';
      $body .= '<td><strong>type</strong></td>';
      $body .= '<td><strong>reference</strong></td>';
      $body .= '<td><strong>name</strong></td>';
      $body .= '<td><strong>operator</strong></td>';
      $body .= '<td></td>';
      $body .= '</tr>';
      foreach ($sites as $site) {
        $body .= '<tr>';
        $body .= '<td>'.$site->type.'</td>';
        $body .= '<td>'.$site->reference.'</td>';
        $body .= '<td>'.$site->name.'</td>';
        $body .= '<td>'.$site->operator->name.'</td>';
        $body .= '<td>'.HTML::anchor('/admin/sites/'.$site->id, 'edit').'</td>';
        $body .= '</tr>';
      }
      $body .= '</table>';

    }

    $this->response->body($body);
  }

  public function action_blocks() {
    $id = $this->request->param('id');
    $body = View::factory('header')->render();

    $model = ORM::factory('block', $id);
    $form = Formo::form()
      ->orm('load', $model, array('user_id', 'timestamp'), true)
      ->add($id ? 'update' : 'save', 'submit');

    if ($form->sent($_POST) and $form->load($_POST)) {
      try {
        $id ? $model->update() : $model->save();
        $body .= '<p>Block saved.</p>';
      } catch (Exception $e) {
        $body .= '<p>Unable to save block.</p>';
      }
    }

    $body .= $form->render();

    if ($id === null) {

      $blocks = ORM::factory('block')->find_all()->as_array();
      $body .= '<table border="1">';
      $body .= '<tr>';
      $body .= '<td><strong>opeator</strong></td>';
      $body .= '<td><strong>site</strong></td>';
      $body .= '<td><strong>coordinates</strong></td>';
      $body .= '<td></td>';
      $body .= '</tr>';
      foreach ($blocks as $block) {
        $body .= '<tr>';
        $body .= '<td>'.$block->site->operator->name.'</td>';
        $body .= '<td>'.$block->site->name.'</td>';
        $body .= '<td>'.$block->coordinates.'</td>';
        $body .= '<td>'.HTML::anchor('/admin/blocks/'.$block->id, 'edit').'</td>';
        $body .= '</tr>';
      }
      $body .= '</table>';

    }

    $this->response->body($body);
  }

  public function action_printjobs() {
    $id = $this->request->param('id');
    $body = View::factory('header')->render();

    $model = ORM::factory('printjob', $id);
    $form = Formo::form()
      ->orm('load', $model, array('site_id'))
      ->add('import', 'file')
      ->add($id ? 'update' : 'save', 'submit');

    if ($form->sent($_POST) and $form->load($_POST)) {
      $import = $form->import->val();
      $array = file($import['tmp_name']);

//      $body .= Debug::vars($array);

      $file = ORM::factory('file', $id);
      $file->name = $import['name'];
      $file->type = $import['type'];
      $file->size = $import['size'];
      $file->content_md5 = md5_file($import['tmp_name']);
      try {
        $file->save();
        $body .= '<p>File uploaded.</p>';
      } catch (Exception $e) {
        $body .= '<p>Unable to upload file.</p>';
      }

      $matches = array();
      preg_match('/Print\sJob\:\s*(\d+).*/', $array[2], $matches);
      $model->allocation_date = Date::formatted_time('now', 'Y-m-d');
      $model->number = $matches[1];
      try {
        $model->save();
        $body .= '<p>Printjob saved.</p>';
      } catch (Exception $e) {
        $body .= '<p>Unable to save printjob.</p>';
      }

      $barcode_ids = array();
      $barcode_errors = 0;

      $file_data = array();

      $start = Model_Printjob::PARSE_START;
      $count = count($array);
      for ($i = $start; $i < ($count - 1); $i++) {
        $line = $array[$i];
        $data = $model->parse_txt($line, $array);
        if (!$data) continue;
        $file_data[] = $data;

        $barcode = ORM::factory('barcode');
        $barcode->printjob = $model;
        $barcode->barcode = $data['barcode'];
        try {
          $barcode->save();
          $barcode_ids[] = $barcode->id;
        } catch (Exception $e) {
          $barcode_errors++;
        }
      }

      $body .= '<p>'.count($barcode_ids).' barcodes parsed from file.</p>';
      if ($barcode_errors) {
        $body .= '<p>'.$barcode_errors.' barcodes not parsed due to errors.</p>';
      }


//      try {
//        $id ? $model->update() : $model->save();
//        $body .= '<p>Printjob saved.</p>';
//      } catch (Exception $e) {
//        $body .= '<p>Unable to save printjob.</p>';
//      }
    }

    $body .= $form->render();

    if ($id === null) {

      $printjobs = ORM::factory('printjob')->find_all()->as_array();
      $body .= '<table border="1">';
      $body .= '<tr>';
      $body .= '<td><strong>opeator</strong></td>';
      $body .= '<td><strong>site</strong></td>';
      $body .= '<td><strong>number</strong></td>';
      $body .= '<td></td>';
      $body .= '</tr>';
      foreach ($printjobs as $printjob) {
        $body .= '<tr>';
        $body .= '<td>'.$printjob->site->operator->name.'</td>';
        $body .= '<td>'.$printjob->site->name.'</td>';
        $body .= '<td>'.$printjob->number.'</td>';
        $body .= '<td>'.HTML::anchor('/admin/printjobs/'.$printjob->id, 'edit').'</td>';
        $body .= '</tr>';
      }
      $body .= '</table>';

    }

    $this->response->body($body);
  }

}
