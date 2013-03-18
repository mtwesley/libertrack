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
      $csv->process();
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
    $details = $this->request->post('details');
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
        'details' => $details ? TRUE: FALSE,
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

    $suggestions = array_filter($model->make_suggestions($csv->values, array($field => array(
      'error' => $error,
    ))));

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
      ->where('form_type', '=', $csv->form_type)
      ->and_where('id', 'IN', SGS::flattenify($csv->get_duplicates()))
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
    $csv->resolve($new_id);
  }

  public function action_siteopts() {
    if (!Auth::instance()->logged_in('data')) return $this->response->status(401);

    $hide_all    = $this->request->post('hide_all') ? TRUE : FALSE;
    $operator_id = $this->request->post('operator_id');

    if ($hide_all and !$operator_id) return;

    $query = DB::select('id', 'name')
      ->from('sites')
      ->order_by('name');
    if ($operator_id) $query->where('operator_id', '=', $operator_id);

    foreach (array('' => '') + $query
      ->execute()
      ->as_array('id', 'name') as $id => $name) $output .= '<option value="'.$id.'">'.$name.'</option>';

    $this->response->body($output);
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

  public function action_specsopts() {
    if (!Auth::instance()->logged_in('data')) return $this->response->status(401);

    $operator_id  = $this->request->post('operator_id');
    $numbers_only = $this->request->post('numbers_only');

    if ($operator_id) {
      $output = '<optgroup label=""><option value=""></option>';

      if (!$numbers_only) {
        $sql = "SELECT distinct barcode
                FROM barcodes
                JOIN specs_data ON specs_data.specs_barcode_id = barcodes.id
                ORDER BY barcode";

        if ($barcodes = array_filter(DB::query(Database::SELECT, $sql)
          ->execute()
          ->as_array(NULL, 'barcode'))) {
          $output .= '<optgroup label="Shipment Specification Barcode">';
          foreach ($barcodes as $barcode) $output .= '<option value="'.$barcode.'">'.$barcode.'<option>';
          $output .= '</optgroup>';
        }
      }

      $sql = "SELECT distinct number
              FROM specs
              ORDER BY number";

      if ($numbers = array_filter(DB::query(Database::SELECT, $sql)
        ->execute()
        ->as_array(NULL, 'number'))) {
        $output .= '<optgroup label="Shipment Specification Number">';
        foreach ($numbers as $number) $output .= '<option value="'.$number.'">SPEC '.$number.'<option>';
        $output .= '</optgroup>';
      }

      $this->response->body($output);
    }
  }

  public function action_autocompletebarcode() {
    $term = $this->request->post('term') ?: $this->request->query('term');

    print json_encode(
      array_filter(
        array_values(SGS::suggest_barcode(strtoupper($term), array(), 'barcode', FALSE, 2, 0.5, 6, 5, 0))
    ));
  }

}
