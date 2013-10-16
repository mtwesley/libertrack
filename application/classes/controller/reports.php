<?php

class Controller_Reports extends Controller {

  public function before() {
    parent::before();

    if (!Auth::instance()->logged_in()) {
      Notify::msg('Please login.', NULL, TRUE);
      $this->request->redirect('login?destination='.$this->request->uri());
    }
    elseif (!Auth::instance()->logged_in('reports')) {
      Notify::msg('Access denied. You must have '.SGS::$roles['reports'].' privileges.', 'locked', TRUE);
      $this->request->redirect();
    }

    Session::instance()->write();
  }

  private function handle_report_list($id) {
    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  private function handle_report_create($report_type) {
    $step   = $this->request->param('command');
    $report = ORM::factory('report');

    if (!$step) {
      Session::instance()->delete('reports.create.model');
      Session::instance()->delete('reports.create.fields');

      $report_type_models = array(
        'CSV' => array('csv'),
        'DATA' => array('ssf', 'tdf', 'ldf'),
        'SUMMARY' => array('site', 'operator')
      );

      $model_options = array();
      foreach ($report_type_models[$report_type] as $type) $model_options[$type] = $report::$models[$type]['name'];

      $model_form = Formo::form()
        ->add_group('model', 'select', $model_options, NULL, array('required' => TRUE, 'label' => 'Base'))
        ->add('submit', 'submit', 'Setup Fields');

      if ($model_form->sent($_REQUEST) and $model_form->load($_REQUEST)->validate()) {
        Session::instance()->set('reports.create.model', $model_form->model->val());
        $this->request->redirect('reports/create/'.strtolower($report_type).'/fields');
      }
    }

    if ($step == 'fields') {
      $model  = Session::instance()->get('reports.create.model');
      $fields = (array) Session::instance()->get('reports.create.fields');

      foreach ($report::$models[$model]['fields'] as $_field => $_field_properties)
        $field_options[$report::$models[$model]['name']]["$model.$_field"] = $_field_properties['name'];

      $model_fields[$model] = (array) $report::$models[$model]['fields'];
      foreach ($report::$models[$model]['models'] as $_model => $_model_properties)
        foreach ($report::$models[$_model]['fields'] as $_field => $_field_properties)
          $field_options[$report::$models[$_model]['name']]["$_model.$_field"] = $_field_properties['name'];

      $fields_form = Formo::form()
        ->add('name', 'input', array('required' => TRUE, 'label' => 'Name'))
        ->add_group('field', 'select', $field_options, NULL, array('required' => TRUE, 'label' => 'Field'))
        ->add('submit', 'submit', 'Add Field');

      if ($fields_form->sent($_REQUEST) and $fields_form->load($_REQUEST)->validate()) {
        $_name = $fields_form->name->val();
        list($_model, $_field) = explode('.', $fields_form->field->val());

        if ($model == $_model) $_values = array('value');
        else switch ($report::$models[$model]['models'][$_model]['type']) {
          case 'many_to_one':
          case 'one_to_one':
            $_values = array('value');
            break;

          case 'many_to_many':
          case 'one_to_many':
            $aggregates = $report::$models[$_model]['fields'][$field]['aggregates'];
            if ($aggregates and is_array($aggregates)) $aggrs = (array) $aggregates;
            else if ($aggregates) $aggrs = array('sum', 'min', 'max', 'avg');
            $_values = array_merge((array) $aggrs, array('count', 'array_agg'));
            break;
        }

        $fields[] = array(
          'model'  => $_model,
          'field'  => $_field,
          'name'   => $_name,
          'values' => $_values
        );

        Session::instance()->set('reports.create.fields', $fields);

        $fields_table .= View::factory('reportfields')
          ->set('report', $report)
          ->set('report_type', $report_type)
          ->set('model', $model)
          ->set('fields', $fields)
          ->render();
      }
    }

    if ($step == 'preview') {
      $sql = <<<EOF
select sites.name as site_name,
sites.type as site_type,
(select name from operators where operators.id = sites.operator_id) as operator_name,
(select sum(volume) from tdf_data where tdf_data.site_id = sites.id) as tdf_volume,
(select sum(volume) from ldf_data where ldf_data.site_id = sites.id) as ldf_volume
from sites
order by site_name;
EOF;
      $results = DB::query(Database::SELECT, $sql)->execute()->as_array();
      $headers = array(
        'Site Name',
        'Site Type',
        'Operator Name',
        'TDF Volume',
        'LDF Volume'
      );

      $results_table .= View::factory('reporttable')
        ->set('results', $results)
        ->set('headers', $headers)
        ->render();
    }

    if (!$step) $content .= $model_form;
    if ($step == 'fields') {
      $content .= $fields_table;
      $content .= $fields_form;
    }
    if ($step == 'preview') $content .= $results_table;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  public function action_index() {
    $id      = $this->request->param('id');
    $command = $this->request->param('command');

    switch ($command) {
      case 'download': return self::handle_report_download($id);
      case 'finalize': return self::handle_report_finalize($id);
      case 'delete': return self::handle_report_delete($id);
      case 'list': default: return self::handle_report_list($id);
    }
  }

  public function action_list() {
    $id = $this->request->param('id');

    return self::handle_report_list($id);
  }

  public function action_create() {
    $report_type = $this->request->param('id');

    switch ($report_type) {
      case 'csv':  return self::handle_report_create('CSV');
      case 'data':  return self::handle_report_create('DATA');
      case 'summary': return self::handle_report_create('SUMMARY');
    }

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

}