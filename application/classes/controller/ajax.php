<?php

class Controller_Ajax extends Controller {

  public function action_csv() {
    if (!Auth::instance()->logged_in('data')) return $this->response->status(401);

    $vars  = explode('-', $_POST['id']);
    $model = $vars[0];
    $id    = $vars[1];
    $key   = $vars[2];
    $value = $_POST['data'];

    $csv = ORM::factory('CSV', $id);
    if (!$csv->loaded()) return $this->response->status(403);

    try {
      $data = $csv->values;
      $data[$key] = $value;

      $csv->values = $data;
      $csv->status = 'P';

      $csv->save();
    } catch (Exception $e) {
      return;
    }

    $this->response->body($value);
  }
}
