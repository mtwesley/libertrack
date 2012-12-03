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

  public function action_details2() {}

  public function action_update() {
    if (!Auth::instance()->logged_in('data')) return $this->response->status(401);

    $id      = $this->request->post('id');
    $actions = $this->request->post('actions');
    $header  = $this->request->post('header');

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
        'header'  => FALSE,
        'actions' => $actions ? TRUE : FALSE,
        'hide_header_info' => $header ? TRUE : FALSE
      ))
      ->render());
  }

  public function action_tips() {
    if (!Auth::instance()->logged_in('data')) return $this->response->status(401);

    $vars  = explode('-', $this->request->post('id'));

    $model = $vars[0];
    $id    = $vars[1];
    $field = $vars[2];
    $error = $vars[3];

    $csv    = ORM::factory('CSV', $id);
    $model  = ORM::factory($csv->form_type, $csv->form_data_id);
    $fields = SGS_Form_ORM::get_fields($csv->form_type) + $model->labels();

    $tips = array_filter(array($field => SGS::decode_tip($field, $error)));

    $content .= View::factory('tips')
      ->set('tips', $tips)
      ->set('csv', $csv)
      ->set('fields', $fields)
      ->render();

    $this->response->body($content);
  }

  public function action_suggestions() {
    if (!Auth::instance()->logged_in('data')) return $this->response->status(401);

    $vars  = explode('-', $this->request->post('id'));

    $model = $vars[0];
    $id    = $vars[1];
    $field = $vars[2];
    $error = $vars[3];

    $csv    = ORM::factory('CSV', $id);
    $model  = ORM::factory($csv->form_type, $csv->form_data_id);
    $fields = SGS_Form_ORM::get_fields($csv->form_type) + $model->labels();

    $suggestions = array_filter($model->make_suggestions($csv->values, array($field => $error)));

    $content .= View::factory('suggestions')
      ->set('suggestions', $suggestions)
      ->set('csv', $csv)
      ->set('fields', $fields)
      ->render();

    $this->response->body($content);
  }

  public function action_resolutions() {
    if (!Auth::instance()->logged_in('data')) return $this->response->status(401);

    $vars  = explode('-', $this->request->post('id'));

    $model = $vars[0];
    $id    = $vars[1];
    $field = $vars[2];
    $error = $vars[3];

    $csv    = ORM::factory('CSV', $id);
    $model  = ORM::factory($csv->form_type, $csv->form_data_id);
    $fields = SGS_Form_ORM::get_fields($csv->form_type) + $model->labels();

    $duplicates = array_merge(array($csv), ORM::factory('CSV')
      ->where('id', 'IN', SGS::flattenify($csv->get_duplicates()))
      ->find_all()
      ->as_array());

    $content .= View::factory('resolutions')
      ->set('duplicates', $duplicates)
      ->set('csv', $csv)
      ->set('fields', $fields)
      ->render();

    $this->response->body($content);
  }

  public function action_process() {
    if (!Auth::instance()->logged_in('data')) return $this->response->status(401);

    $vars  = explode('-', $this->request->post('id'));

    $model = $vars[0];
    $id    = $vars[1];

    $csv = ORM::factory('CSV', $id);
    $csv->process();

    $this->response->body($csv->status);
  }

  public function action_resolve() {
    if (!Auth::instance()->logged_in('data')) return $this->response->status(401);

    $vars  = explode('-', $this->request->post('id'));

    $model  = $vars[0];
    $id     = $vars[1];
    $new_id = $vars[3];

    $csv = ORM::factory('CSV', $id);

    $duplicates = ORM::factory('CSV')
      ->where('id', 'IN', array_diff(array_merge(array($csv->id), SGS::flattenify($csv->get_duplicates())), array($new_id)))
      ->find_all()
      ->as_array();

    foreach ($duplicates as $duplicate) {
      if ($duplicate->form_data_id) {
        $data = ORM::factory($duplicate->form_type, $duplicate->form_data_id);
        if ($data->loaded()) $data->delete();
        $duplicate->form_data_id = NULL;
      }

      $duplicate->status = 'D';
      $duplicate->save();
    }

    $new = ORM::factory('CSV', $new_id);
    $new->process();
  }

  public function action_blockopts() {
    if (!Auth::instance()->logged_in('data')) return $this->response->status(401);

    $site_id = $this->request->post('site_id');

    if ($site_id) foreach (array('' => '') + DB::select('id', 'name')
      ->from('blocks')
      ->where('site_id', '=', $site_id)
      ->order_by('name')
      ->execute()
      ->as_array('id', 'name') as $id => $name) $output .= '<option value="'.$id.'">'.$name.'</option>';

    $this->response->body($output);
  }

}
