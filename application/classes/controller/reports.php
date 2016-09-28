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

    $model  = Session::instance()->get('reports.create.model', NULL);
    $fields  = (array) Session::instance()->get('reports.create.fields', array());
    $filters = (array) Session::instance()->get('reports.create.filters', array());
    $order   = (array) Session::instance()->get('reports.create.order', array());
    $limit   = Session::instance()->get('reports.create.limit', NULL);

    if (!$step) {
      unset($model);
      unset($fields);
      unset($filters);
      unset($order);
      unset($limit);

      Session::instance()->delete('reports.create.model');
      Session::instance()->delete('reports.create.fields');
      Session::instance()->delete('reports.create.filters');
      Session::instance()->delete('reports.create.order');
      Session::instance()->delete('reports.create.limit');

      $model_options = array();
      foreach ($report::$types[$report_type]['models'] as $_model)
        $model_options[$_model] = $report::$models[$_model]['name'];

      $model_form = Formo::form()
        ->add_group('model', 'select', $model_options, NULL, array('required' => TRUE, 'label' => 'Model'))
        ->add('submit', 'submit', 'Save');

      if ($model_form->sent($_REQUEST) and $model_form->load($_REQUEST)->validate()) {
        Session::instance()->set('reports.create.model', $model_form->model->val());
        $this->request->redirect('reports/create/'.strtolower($report_type).'/fields');
      }
    } else {
      if (!$model) $this->request->redirect('/reports/create/'.strtolower($report_type));
      $report_header .= View::factory('reportheader')
        ->set('step', $step)
        ->set('report', $report)
        ->set('report_type', $report_type)
        ->set('model', $model)
        ->set('fields', $fields)
        ->render();
    }

    if ($step == 'fields') {
      $fields_enabled = TRUE;

      foreach ($report::$models[$model]['fields'] as $_field => $_field_properties)
        $field_options[$report::$models[$model]['name']]["$model.$_field"] = $_field_properties['name'];

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

        $fields[] = array(
          'model'  => $_model,
          'field'  => $_field,
          'name'   => $_name,
          'value'  => $report::field_values($model, $_field, $_model, TRUE)
        );

        Session::instance()->set('reports.create.fields', $fields);
      }

      if ($_REQUEST['save']) foreach ($_REQUEST as $k => $v) if (strpos($k, 'field') === 0) {
        $reorder_fields = TRUE;

        list($x, $i, $m, $f, $t) = explode('-', $k);
        if (!$x or $x != 'field') break;
        if (!is_numeric($i)) break;
        if (!$m or !$f or !$t) break;

        $_fields[$i]['model']  = $m;
        $_fields[$i]['field']  = $f;
        $_fields[$i]['values'] = $report::field_values($model, $f, $m);
        if (in_array($t, array('name', 'value', 'index'))) $_fields[$i][$t] = $v;
      }

      foreach ($_REQUEST as $k => $v) if (strpos($k, 'remove')) {
        $reorder_fields = TRUE;
        $vars = explode('-', $k);

        $_fields = $_fields ?: $fields;
        if (($_fields[$vars[1]]['model'] == $vars[2]) and
            ($_fields[$vars[1]]['field'] == $vars[3])) unset($_fields[$vars[1]]);
      }

      if ($reorder_fields and is_array($_fields)) {
        usort($_fields, function ($field1, $field2) {
          return $field1['index'] - $field2['index'];
        });
        Session::instance()->set('reports.create.fields', $_fields);
        $fields = $_fields;
      }

      if ($_REQUEST['filters']) $this->request->redirect('/reports/create/'.strtolower($report_type).'/filters');
    }

    if ($step == 'filters') {
      $filters_enabled = TRUE;

      foreach ($report::$models[$model]['fields'] as $_field => $_field_properties)
        $field_options[$report::$models[$model]['name']]["$model.$_field"] = $_field_properties['name'];

      foreach ($report::$models[$model]['models'] as $_model => $_model_properties)
        foreach ($report::$models[$_model]['fields'] as $_field => $_field_properties)
          $field_options[$report::$models[$_model]['name']]["$_model.$_field"] = $_field_properties['name'];

      $filters_form = Formo::form()
        ->add_group('field', 'select', $field_options, NULL, array('required' => TRUE, 'label' => 'Field'))
        ->add_group('filter', 'select', $report::$filters, NULL, array('required' => TRUE, 'label' => 'Filter'))
        ->add('values', 'input', array('required' => TRUE, 'label' => 'Values'))
        ->add('submit', 'submit', 'Add Filter');

      if ($filters_form->sent($_REQUEST) and $filters_form->load($_REQUEST)->validate()) {
        list($_model, $_field) = explode('.', $filters_form->field->val());
        $_filter = $filters_form->filter->val();
        $_values = explode(',', $filters_form->values->val());

        $filters[] = array(
          'model'  => $_model,
          'field'  => $_field,
          'filter' => $_filter,
          'values' => $_values
        );

        Session::instance()->set('reports.create.filters', $filters);
      }

      if ($_REQUEST['save']) foreach ($_REQUEST as $k => $v) if (strpos($k, 'filter') === 0) {
        $reorder_filters = TRUE;

        list($x, $i, $m, $f, $s, $t) = explode('-', $k);
        if (!$x or $x != 'filter') break;
        if (!is_numeric($i)) break;
        if (!$m or !$f or !$t) break;

        $_filters[$i]['model'] = $m;
        $_filters[$i]['field'] = $f;
        if ($t == 'filter') $_filters[$i][$t] = $v;
        if ($t == 'values') $_filters[$i][$t] = explode(',', $v);
      }

      foreach ($_REQUEST as $k => $v) if (strpos($k, 'remove')) {
        $reorder_filters = TRUE;
        $vars = explode('-', $k);

        $_filters = $_filters ?: $filters;
        if (($_filters[$vars[1]]['model']  == $vars[2]) and
            ($_filters[$vars[1]]['field']  == $vars[3]) and
            ($_filters[$vars[1]]['filter'] == $vars[4])) unset($_filters[$vars[1]]);
      }

      if ($reorder_filters and is_array($_filters)) {
        usort($_filters, function ($filter1, $filter2) {
          return $filter1['index'] - $filter2['index'];
        });
        Session::instance()->set('reports.create.filters', $_filters);
        $filters = $_filters;
      }

      if ($_REQUEST['preview']) $this->request->redirect('/reports/create/'.strtolower($report_type).'/preview');
    }

    if ($step == 'limits') {
      $filters_enabled = TRUE;

      $limit_options = array(
        1     =>  1,
        10    => 10,
        25    => 25,
        50    => 50,
        100   => 100,
        250   => 250,
        500   => 500,
        1000  => 1000,
        2500  => 2500,
        5000  => 5000,
        10000 => 10000
      );

      foreach ($fields as $index => $field)
        $sort_options[$report::$models[$field['model']]['name']]["$index.{$field['model']}.{$field['field']}"] = $field['name'];

      $direction_options = array(
        'ASC'  => 'Lowest to Highest',
        'DESC' => 'Highest to Lowest'
      );

      $limits_form = Formo::form()
        ->add_group('sort', 'select', $sort_options, $order['sort'], array('label' => 'Sort'))
        ->add_group('direction', 'select', $direction_options, $order['direction'], array('label' => 'Direction'))
        ->add_group('limit', 'select', $limit_options, $limit, array('label' => 'Limit'))
        ->add('submit', 'submit', 'Save');

      if ($limits_form->sent($_REQUEST) and $limits_form->load($_REQUEST)->validate()) {
        if ($limits_form->sort->val()) {
          $order = array(
            'sort'      => $limits_form->sort->val(),
            'direction' => $limits_form->direction->val()
          );
        }

        $limit = $limits_form->limit->val();

        Session::instance()->set('reports.create.order', $order);
        Session::instance()->set('reports.create.limit', $limit);
      }
    }

    if (in_array($step, array('preview', 'csv', 'xls'))) {
      $field_array = array();
      foreach ($fields as $index => $field) {
        if (($field['model'] == $model) and ($field['value'] != 'value')) $not_group_by_fields[$index] = $field;
        else if ((in_array($report::$models[$model]['models'][$field['model']]['type'], array('one_to_one', 'many_to_one'))) and ($field['value'] != 'value')) $not_group_by_fields[$index] = $field;
        $field_array[] = array($report::field_query($model, $field, $filters), $field['name']);
      }

      $query = DB::select_array($field_array)
        ->from(array($report::$models[$model]['table'], 'model'));

      foreach ($filters as $filter) if ($filter['model'] == $model) {
        $filter_field = $report::field_query($model, $filter);
        switch ($filter['filter']) {
          case 'equals':
            $query->where($filter_field, 'IN', (array) $filter['values']); break;

          case 'not_equals':
            $query->where($filter_field, 'NOT IN', (array) $filter['values']); break;

          case 'contains':
            $query->where($filter_field, 'ILIKE', "%".implode(',', (array) $filter['values'])."%"); break;

          case 'not_contains':
            $query->where($filter_field, 'NOT ILIKE', "%".implode(',', (array) $filter['values'])."%"); break;

          case 'begins':
            $query->where($filter_field, 'ILIKE', implode(',', (array) $filter['values'])."%"); break;

          case 'ends':
            $query->where($filter_field, 'ILIKE', "%".implode(',', (array) $filter['values'])); break;

          case 'between':
            $query->where($filter_field, 'BETWEEN', (array) $filter['values']); break;

          case 'greater_than':
            $query->where($filter_field, '>', reset($filter['values'])); break;

          case 'less_than':
            $query->where($filter_field, '<', reset($filter['values'])); break;

          case 'true':
            $query->where($filter_field, '=', TRUE); break;

          case 'false':
            $query->where($filter_field, '=', FALSE); break;

          case 'null':
            $query->where($filter_field, 'IS', NULL); break;

          case 'not_null':
            $query->where($filter_field, 'IS NOT', NULL); break;
        }
      }

      $no_field_filters = $filters;
      foreach ($no_field_filters as $index => $filter)
        foreach ($fields as $field)
          if (($no_field_filters['field'] == $field['field']) and ($no_field_filters['model'] == $field['model'])) unset($no_field_filters[$index]);
          else if ($filter['model'] == $model) unset($no_field_filters[$index]);

      foreach ($no_field_filters as $filter) {
        $field_sql = $report::field_query($model, $filter);

        switch ($filter['filter']) {
          case 'equals':
            $query->where($field_sql, 'IN', (array) $filter['values']); break;

          case 'not_equals':
            $query->where($field_sql, 'NOT IN', (array) $filter['values']); break;

          case 'contains':
            $query->where($field_sql, 'ILIKE', "%".implode(',', (array) $filter['values'])."%"); break;

          case 'not_contains':
            $query->where($field_sql, 'NOT ILIKE', "%".implode(',', (array) $filter['values'])."%"); break;

          case 'begins':
            $query->where($field_sql, 'ILIKE', implode(',', (array) $filter['values'])."%"); break;

          case 'ends':
            $query->where($field_sql, 'ILIKE', "%".implode(',', (array) $filter['values'])); break;

          case 'between':
            $query->where($field_sql, 'BETWEEN', (array) $filter['values']); break;

          case 'greater_than':
            $query->where($field_sql, '>', reset($filter['values'])); break;

          case 'less_than':
            $query->where($field_sql, '<', reset($filter['values'])); break;

          case 'true':
            $query->where($field_sql, '=', TRUE); break;

          case 'false':
            $query->where($field_sql, '=', FALSE); break;

          case 'null':
            $query->where($field_sql, 'IS', NULL); break;

          case 'not_null':
            $query->where($field_sql, 'IS NOT', NULL); break;
        }
      }

      $group_by_fields = array_diff_key((array) $field_array, (array) $not_group_by_fields);
      foreach ($group_by_fields as $group_by_field) $query->group_by(is_array($group_by_field) ? $group_by_field[1] : $group_by_field['name']);

      if ($order) {
        list($i, $m, $f) = explode('.', $order['sort']);
        $query->order_by($fields[$i]['name'], $order['direction'] ?: NULL);
      }
      if ($limit) $query->limit($limit);

      try {
        $results = $query->execute()->as_array();
        $headers = array_keys(reset($results));

        if ($step == 'preview') $results_table .= View::factory('reporttable')
          ->set('results', $results)
          ->set('headers', $headers)
          ->set('report_type', $report_type)
          ->render();
      } catch (Database_Exception $e) {
        Notify::msg('This query cannot be executed. Please check field and filter values and try again.', 'warning');
      } catch (Exception $e) {
        Notify::msg('Sorry, unable to preview report. Please try again.', 'error');
      }
    }

    if (in_array($step, array('csv', 'xls')) and $results) {
      $excel = new PHPExcel();
      $excel->setActiveSheetIndex(0);

      $type = $step;
      switch ($type) {
        case 'csv':
          $writer = new PHPExcel_Writer_CSV($excel);
          $mime_type = 'text/csv';
          break;
        case 'xls':
          $writer = new PHPExcel_Writer_Excel5($excel);
          $mime_type = 'application/vnd.ms-excel';
          break;
      }

      $excel->getActiveSheet()->fromArray((array) array_keys(reset($results)));
      $excel->getActiveSheet()->fromArray((array) $results, '', 'A2');

      $tempname = tempnam(sys_get_temp_dir(), 'report_').'.'.$type;
      $fullname = strtoupper("report_$report_type.").$type;

      $writer->save($tempname);
      $this->response->send_file($tempname, $fullname, array('mime_type' => $mime_type, 'delete' => TRUE));
    }

    if ($fields) $fields_table .= View::factory('reportfields')
      ->set('enabled', $fields_enabled)
      ->set('report', $report)
      ->set('report_type', $report_type)
      ->set('model', $model)
      ->set('fields', $fields)
      ->render();

    if ($filters) $filters_table .= View::factory('reportfilters')
      ->set('enabled', $filters_enabled)
      ->set('report', $report)
      ->set('report_type', $report_type)
      ->set('model', $model)
      ->set('filters', $filters)
      ->render();

    if (!$step) $content .= $model_form;
    else if ($report_header) $content .= $report_header;

    if ($step == 'fields') {
      if ($fields_table) $content .= $fields_table;
      $content .= $fields_form;
    }

    if ($step == 'filters') {
      if ($filters_table) $content .= $filters_table;
      $content .= $filters_form;
    }

    if ($step == 'limits') $content .= $limits_form;

    if ($step == 'preview') $content .= $results_table;

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

  public function handle_report_create_monthly() {
    set_time_limit(0);

    $form = Formo::form()
      ->add_group('report_type', 'select', SGS::$monthly_report, NULL, array('label' => 'Report', 'required' => TRUE))
      ->add_group('form_type', 'select', SGS::$form_data_type, NULL, array('label' => 'Form'))
      ->add_group('status', 'checkboxes', SGS::$data_status, array_keys(SGS::$data_status), array('label' => 'Status'))
      ->add('from', 'input', array('label' => 'From', 'attr' => array('class' => 'dpicker', 'id' => 'from-dpicker'), 'required' => TRUE))
      ->add('to', 'input', array('label' => 'To', 'attr' => array('class' => 'dpicker', 'id' => 'to-dpicker'), 'required' => TRUE));

    $form->add('search', 'submit', 'Download Report');

    if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
      $report_type = $form->report_type->val();
      $form_type = $form->form_type->val();
      $status = $form->status->val();
      $from = SGS::date($form->from->val(), SGS::PGSQL_DATE_FORMAT);
      $to   = SGS::date($form->to->val(), SGS::PGSQL_DATE_FORMAT);

      $status_split = "'" . implode("','", (array) $status) . "'";

      switch ($report_type) {
        case 'upload':
          if (in_array($form_type, array('SSF', 'TDF', 'LDF'))) $sql = <<<EOD
select
  site_name,
  form_type,
  sum((pi() * pow(((top_min + top_max + bottom_min + bottom_max) / 4 / 2 / 100), 2) * length)) as volume
from (select
  form_type,
  (regexp_matches((csv.values::text), E's:\\\\d+:"top_min";s:\\\\d+:"(-?[\\\\d.]+)";'))[1]::numeric as top_min,
  (regexp_matches((csv.values::text), E's:\\\\d+:"top_max";s:\\\\d+:"(-?[\\\\d.]+)";'))[1]::numeric as top_max,
  (regexp_matches((csv.values::text), E's:\\\\d+:"bottom_min";s:\\\\d+:"(-?[\\\\d.]+)";'))[1]::numeric as bottom_min,
  (regexp_matches((csv.values::text), E's:\\\\d+:"bottom_max";s:\\\\d+:"(-?[\\\\d.]+)";'))[1]::numeric as bottom_max,
  (regexp_matches((csv.values::text), E's:\\\\d+:"length";s:\\\\d+:"(-?[\\\\d.]+)";'))[1]::numeric as length,
  sites.name as site_name
from csv
join sites on site_id = sites.id
where form_type in ('$form_type') and csv.timestamp between '$from' and '$to' and status in ($status_split)) as result
group by site_name, form_type
order by form_type, site_name;
EOD;

          elseif (in_array($form_type, array('SPECS'))) $sql = <<<EOD
select 
  operator_name,
  form_type,
  sum((pi() * pow(((top_min + top_max + bottom_min + bottom_max) / 4 / 2 / 100), 2) * length)) as volume
from (select
  form_type, 
  (regexp_matches((csv.values::text), E's:\\\\d+:"top_min";s:\\\\d+:"(-?[\\\\d.]+)";'))[1]::numeric as top_min,
  (regexp_matches((csv.values::text), E's:\\\\d+:"top_max";s:\\\\d+:"(-?[\\\\d.]+)";'))[1]::numeric as top_max,
  (regexp_matches((csv.values::text), E's:\\\\d+:"bottom_min";s:\\\\d+:"(-?[\\\\d.]+)";'))[1]::numeric as bottom_min,
  (regexp_matches((csv.values::text), E's:\\\\d+:"bottom_max";s:\\\\d+:"(-?[\\\\d.]+)";'))[1]::numeric as bottom_max,
  (regexp_matches((csv.values::text), E's:\\\\d+:"length";s:\\\\d+:"(-?[\\\\d.]+)";'))[1]::numeric as length,
  operators.name as operator_name
from csv 
join operators on operator_id = operators.id
where form_type in ('$form_type') and csv.timestamp between '$from' and '$to' and status in ($status_split)) as result
group by operator_name, form_type
order by form_type, operator_name;
EOD;
          $fullname = SGS::wordify(strtoupper('DATA_UPLOAD_REPORT'));
          break;

        case 'processing':
          $sql = <<<EOD
select 
  form_type,
  year_month,
  status,
  sum((pi() * pow(((top_min + top_max + bottom_min + bottom_max) / 4 / 2 / 100), 2) * length)) as volume
from (select
  form_type, 
  (regexp_matches((csv.values::text), E's:\\\\d+:"top_min";s:\\\\d+:"(-?[\\\\d]+(\\\\.[\\\\d]+)?)";'))[1]::numeric as top_min,
  (regexp_matches((csv.values::text), E's:\\\\d+:"top_max";s:\\\\d+:"(-?[\\\\d]+(\\\\.[\\\\d]+)?)";'))[1]::numeric as top_max,
  (regexp_matches((csv.values::text), E's:\\\\d+:"bottom_min";s:\\\\d+:"(-?[\\\\d]+(\\\\.[\\\\d]+)?)";'))[1]::numeric as bottom_min,
  (regexp_matches((csv.values::text), E's:\\\\d+:"bottom_max";s:\\\\d+:"(-?[\\\\d]+(\\\\.[\\\\d]+)?)";'))[1]::numeric as bottom_max,
  (regexp_matches((csv.values::text), E's:\\\\d+:"length";s:\\\\d+:"(-?[\\\\d]+(\\\\.[\\\\d]+)?)";'))[1]::numeric as length,
  to_char(csv.timestamp, 'MM-YYYY') as year_month,
  csv.status as status
from csv 
where csv.form_type in ('TDF','LDF', 'SPECS') and csv.timestamp > '2013-09-01') as result
group by form_type, year_month, status
order by form_type, year_month, status;
EOD;
          $fullname = SGS::wordify(strtoupper('DATA_PROCESSING_REPORT'));
          break;

        case 'harvest':
          $sql = <<<EOD
select distinct
  sites.name as site_name,
  operators.name as operator_name,
  species.code as species_code,
  species.trade_name as species_name,
  sum(tdf_data.volume) as volume
from tdf_data
join species on tdf_data.species_id = species.id
join sites on tdf_data.site_id = sites.id
join operators on tdf_data.operator_id = operators.id
where tdf_data.timestamp  between '$from' and '$to'
group by sites.name, operators.name, species.code, species.trade_name;
EOD;
          $fullname = SGS::wordify(strtoupper('HARVESTING_REPORT'));
          break;
        
        case 'export':
          $sql = <<<EOD
select distinct
  sites.name as site_name,
  operators.name as operator_name,
  species.code as species_code,
  species.trade_name as species_name,
  sum(specs_data.volume) as volume
from specs_data
join ldf_data on specs_data.barcode_id = ldf_data.barcode_id
join species on specs_data.species_id = species.id
join sites on ldf_data.site_id = sites.id
join operators on sites.operator_id = operators.id
where specs_data.timestamp between '2016-07-01' and '2016-08-01'
group by sites.name, operators.name, species.code, species.trade_name;
EOD;
          $fullname = SGS::wordify(strtoupper('EXPORTING_REPORT'));
          break;
      }

      if ($sql) {
        $result = DB::query(Database::SELECT, $sql)->execute();
        if ($result) {
          $ext = 'csv';
          $excel = new PHPExcel();
          $excel->setActiveSheetIndex(0);
          $excel->getActiveSheet()->fromArray((array) array_keys((array) $result[0]), NULL, 'A1');
          $row_count = 2;
          foreach ($result as $row) $excel->getActiveSheet()->fromArray((array) $row, NULL, 'A'.$row_count++);
          $mime_type = 'text/csv';

          $tempname = tempnam(sys_get_temp_dir(), $report_type . '_report');

          $writer = new PHPExcel_Writer_CSV($excel);
          $writer->save($tempname);

          $this->response->send_file($tempname, $fullname.'.'. $ext, array('mime_type' => $mime_type, 'delete' => TRUE));
        } else Notify::msg('Sorry, no data found for report.', 'warning');
      }
    }

    if ($form) $content .= $form->render();

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }


  public function handle_report_create_export() {
    set_time_limit(0);

    $form = Formo::form()
        ->add_group('report_type', 'select', SGS::$export_report, NULL, array('label' => 'Report', 'required' => TRUE))
        ->add('from', 'input', array('label' => 'From', 'attr' => array('class' => 'dpicker', 'id' => 'from-dpicker'), 'required' => TRUE))
        ->add('to', 'input', array('label' => 'To', 'attr' => array('class' => 'dpicker', 'id' => 'to-dpicker'), 'required' => TRUE));

    $form->add('search', 'submit', 'Download Report');

    if ($form->sent($_REQUEST) and $form->load($_REQUEST)->validate()) {
      $report_type = $form->report_type->val();
      $from = SGS::date($form->from->val(), SGS::PGSQL_DATE_FORMAT);
      $to   = SGS::date($form->to->val(), SGS::PGSQL_DATE_FORMAT);

      switch ($report_type) {
        case 'exp':
        case 'specs':
          $document_type = strtoupper($report_type);
          $sql = <<<EOD
select
  documents.type || ' ' || lpad(documents.number::text, 6, '0') as number,
  documents.operator_id,
  operators.name as operator,
  documents.site_id,
  sites.name as site,
  specs_data.create_date as date,
  specs_data.barcode_id,
  barcodes.barcode as barcode,
  specs_data.species_id,
  species.code as species_code,
  specs_data.diameter,
  specs_data.length,
  specs_data.volume
from documents
join document_data on documents.id = document_data.document_id
join specs_data on document_data.form_data_id = specs_data.id and document_data.form_type = 'SPECS'
join barcodes on specs_data.barcode_id = barcodes.id
join species on specs_data.species_id = species.id
join operators on documents.operator_id = operators.id
left outer join sites on documents.site_id = sites.id
where documents.type = '$document_type' and documents.timestamp between '$from' and '$to';
EOD;
          $fullname = SGS::wordify(strtoupper($report_type.'_REPORT'));
          break;

        case 'cert':
          $sql = <<<EOD
select
  documents.type || ' ' || lpad(documents.number::text, 6, '0') as number,
  'EXP ' || lpad((regexp_matches((documents.values::text), E's:\\d+:"exp_number";s:\\d+:"(-?[\\d.]+)";'))[1]::text, 6, '0') as exp_number,
  documents.operator_id,
  operators.name as operator,
  documents.site_id,
  sites.name as site,
  specs_data.create_date as date,
  specs_data.barcode_id,
  barcodes.barcode as barcode,
  specs_data.species_id,
  species.code as species_code,
  specs_data.diameter,
  specs_data.length,
  specs_data.volume
from documents
join document_data on documents.id = document_data.document_id
join specs_data on document_data.form_data_id = specs_data.id and document_data.form_type = 'SPECS'
join barcodes on specs_data.barcode_id = barcodes.id
join species on specs_data.species_id = species.id
join operators on documents.operator_id = operators.id
left outer join sites on documents.site_id = sites.id
where documents.type = 'CERT' and documents.timestamp between '$from' and '$to';
EOD;
          $fullname = SGS::wordify(strtoupper('CERT_REPORT'));
          break;
      }

      if ($sql) {
        $result = DB::query(Database::SELECT, $sql)->execute();
        if ($result) {
          $ext = 'csv';
          $excel = new PHPExcel();
          $excel->setActiveSheetIndex(0);
          $excel->getActiveSheet()->fromArray((array) array_keys((array) $result[0]), NULL, 'A1');
          $row_count = 2;
          foreach ($result as $row) $excel->getActiveSheet()->fromArray((array) $row, NULL, 'A'.$row_count++);
          $mime_type = 'text/csv';

          $tempname = tempnam(sys_get_temp_dir(), $report_type . '_report');

          $writer = new PHPExcel_Writer_CSV($excel);
          $writer->save($tempname);

          $this->response->send_file($tempname, $fullname.'.'. $ext, array('mime_type' => $mime_type, 'delete' => TRUE));
        } else Notify::msg('Sorry, no data found for report.', 'warning');
      }
    }

    if ($form) $content .= $form->render();

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

    Notify::msg('No reports found.', 'notice');
    return self::handle_report_list($id);
  }

  public function action_create() {
    $report_type = $this->request->param('id');

    switch ($report_type) {
      case 'monthly': return self::handle_report_create_monthly();
      case 'csv':  return self::handle_report_create('CSV');
      case 'data':  return self::handle_report_create('DATA');
      case 'summary': return self::handle_report_create('SUMMARY');
      case 'export': return self::handle_report_create_export();
    }

    $view = View::factory('main')->set('content', $content);
    $this->response->body($view);
  }

}