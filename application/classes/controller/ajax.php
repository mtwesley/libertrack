<?php

class Controller_Ajax extends Controller {

  public function action_csv() {
    if (!Auth::instance()->logged_in('data')) return $this->response->status(401);

    $vars    = explode('-', $this->request->post('id'));
    $model   = $vars[0];
    $id      = $vars[1];
    $key     = $vars[2];
    $value   = $this->request->post('data');
    $process = $this->request->post('process');

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

    if ($process) $csv->process();

    $this->response->body($value);
  }

  public function action_details() {
    if (!Auth::instance()->logged_in('data')) return $this->response->status(401);
    $id = $this->request->post('id');

    $csv   = ORM::factory('CSV', $id);
    $model = ORM::factory($csv->form_type, $csv->form_data_id);

    $fields = SGS_Form_ORM::get_fields($csv->form_type) + $model->labels();

    $view = View::factory('details')
      ->set('csv', $csv)
      ->set('errors', $csv->get_errors())
      ->set('fields', $fields);

    $this->response->body($view);
  }

  public function action_update() {
    if (!Auth::instance()->logged_in('data')) return $this->response->status(401);
    $id = $this->request->post('id');

    $csv   = ORM::factory('CSV', $id);
    $model = ORM::factory($csv->form_type, $csv->form_data_id);

    $fields = SGS_Form_ORM::get_fields($csv->form_type);

    $this->response->body(View::factory('csvs')
      ->set('mode', 'import')
      ->set('csvs', array($csv))
      ->set('fields', $fields)
      ->set('options', array(
        'table'   => FALSE,
        'details' => FALSE,
        'header'  => FALSE
      ))
      ->render());
  }

  public function action_suggestions() {
    $vars  = explode('-', $this->request->post('id'));

    $model = $vars[0];
    $id    = $vars[1];
    $field = $vars[2];
    $error = $vars[3];

    $csv    = ORM::factory('CSV', $id);
    $model  = ORM::factory($csv->form_type, $csv->form_data_id);
    $fields = SGS_Form_ORM::get_fields($csv->form_type) + $model->labels();

    $tips = array_filter(array($field => SGS::decode_tip($field, $error)));
    $suggestions = array_filter($model->make_suggestions($csv->values, array($field => $error)));

    $content .= View::factory('tips')
      ->set('tips', $tips)
      ->set('csv', $csv)
      ->set('fields', $fields)
      ->render();

    $content .= View::factory('suggestions')
      ->set('suggestions', $suggestions)
      ->set('csv', $csv)
      ->set('fields', $fields)
      ->render();

    $this->response->body($content);
  }

  public function action_process() {
    $vars  = explode('-', $this->request->post('id'));

    $model = $vars[0];
    $id    = $vars[1];

    $csv = ORM::factory('CSV', $id);
    $csv->process();

    $this->response->body($csv->status);
  }
}
