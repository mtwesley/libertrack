<?php

class Model_CSV extends ORM {

  protected $_table_name = 'csv';

  protected $_belongs_to = array(
    'file'     => array(),
    'operator' => array(),
    'site'     => array(),
    'block'    => array(),
    'user'     => array()
  );

  protected $_ignored_columns = array(
    'data_type',
  );

  protected function _initialize() {
    parent::_initialize();
    $this->_object_plural = 'csv';
  }

  public function set($column, $value) {
    switch ($column) {
      case 'values':
      case 'original_values':
        if (is_array($value)) {
          // set properties
          $this->operator_id = ($operator_id = SGS::lookup_operator($value['operator_tin'], TRUE)) ? $operator_id : NULL;
          $this->site_id     = ($site_id     = SGS::lookup_site($value['site_name'], TRUE)) ? $site_id : NULL;
          $this->block_id    = ($block_id    = SGS::lookup_block($value['site_name'], $value['block_name'], TRUE)) ? $block_id : NULL;

          // set md5
          $_value = $value;
          sort($_value);
          ksort($_value);
          $this->content_md5 = md5(serialize($_value));

          // prepare for db
          $value = serialize($value);
        }
        else if (!is_string($value)) $value = NULL;
      default:
        parent::set($column, $value);
    }
  }

  public function __get($column) {
    switch ($column) {
      case 'data_type':
        if (in_array($this->form_type, array_keys(SGS::$form_data_type))) return 'declaration';
        else if (in_array($this->form_type, array_keys(SGS::$form_verification_type))) return 'verification';
        else return NULL;

      case 'values':
      case 'original_values':
        $value = parent::__get($column);
        return is_string($value) ? unserialize($value) : $value;
      default:
        return parent::__get($column);
    }
  }

  public function create(Validation $validation = NULL) {
    $this->original_values = $this->values;
    parent::create($validation);
  }

  public function delete() {
    if ($this->form_type and $this->form_data_id) {
      $data = ORM::factory($this->form_type, $this->form_data_id);
      if ($data->loaded()) $data->delete();
    }

    parent::delete();
  }

  public function process() {
    $errors     = array();
    $duplicates = array();

    $this->unset_errors();
    $this->unset_duplicates();

    $model = ORM::factory($this->form_type, $this->form_data_id);
    if (!$model->loaded()) $model = ORM::factory($this->form_type);
    $model->parse_data($this->values);

    $validation = new Validation($this->values);
    foreach ($model->csv_rules() as $field => $set) $validation->rules($field, $set);

    $validation->check();
    if (!$errors = $validation->errors()) {
      try {
        $model->save();
        if (!$model->loaded()) throw new Exception();
        $this->form_data_id = $model->id;
      } catch (ORM_Validation_Exception $e) {
        $errors = $e->errors();
      }
    }

    $errors = array_filter($errors);
    if ($errors) foreach ($errors as $field => $array) {
      list($error, $params) = $array;

      $params = array_filter($params);
      $table  = $params[0];
      $fields = (array) $params[1];
      $values = (array) $params[2];

      foreach (array_keys($params) as $key) if (is_object($params[$key])) unset($params[$key]);

      if (strpos($error, 'is_unique') !== FALSE) {
        foreach ((array) $field as $_field) {
          $query = DB::select('csv.id')
            ->from('csv')
            ->join($table)
            ->on('form_data_id', '=', $table.'.id');
          if (($count = count($fields)) == count($values))
            for ($i = 0; $i < $count; $i++) $query->where($table.'.'.$fields[$i], '=', $values[$i]);
          $duplicates[$_field] = $query
            ->and_where('form_type', '=', $this->form_type)
            ->and_where('csv.id', '!=', $this->id)
            ->execute()
            ->get('id');
        }
      }

      $this->set_error($field, $error);
    }

    foreach (DB::select('id')
      ->from('csv')
      ->where('content_md5', '=', $this->content_md5)
      ->and_where('content_md5', 'IS NOT', NULL)
      ->and_where('id', '!=', $this->id)
      ->execute() as $dup) {
        $duplicate = TRUE;
        $duplicates[] = $dup['id'];
      }

    // FIXME: handle issues with NO duplicate CSV id found !!!
    // $duplicates = array_filter($duplicates);

    if ($duplicates) foreach ($duplicates as $field => $duplicate_csv_id) {
      $this->set_duplicate($duplicate_csv_id, is_int($field) ? NULL : $field);
    }

    if ($duplicate) $this->status = 'D';
    else if ($duplicates) $this->status = 'U';
    else if ($errors) $this->status = 'R';
    else $this->status = 'A';

    $this->save();
  }

  public function resolve($id) {
    $new = ORM::factory('CSV', $id);

    $duplicates = ORM::factory('CSV')
      ->where('id', 'IN', array_diff(array_merge(array($csv->id), SGS::flattenify($csv->get_duplicates())), array($id)))
      ->find_all()
      ->as_array();

    foreach ($duplicates as $duplicate) {
      if ($form_data_id = $duplicate->form_data_id) {
        $data = ORM::factory($duplicate->form_type, $duplicate->form_data_id);

        if ($data->loaded()) $data->parse_data($new->values);
        $duplicate->form_data_id = NULL;
        $new->form_data_id = $form_data_id;
      }

      $duplicate->status = 'D';
      $duplicate->save();
    }

    $new->process();
  }

  public function get_errors($args = array()) {
    $query = DB::select()
      ->from('csv_errors')
      ->where('csv_id', '=', $this->id);
    foreach ($args as $key => $value) $query->where($key, 'IN', (array) $value);
    foreach ($query->execute() as $result) $errors[$result['field']][] = $result['error'];
    return (array) $errors;
  }

  public function unset_errors($args = array()) {
    $query = DB::delete('csv_errors')
      ->where('csv_id', '=', $this->id);
    foreach ($args as $key => $value) $query->where($key, 'IN', (array) $value);
    $query->execute();
  }

  public function set_error($field, $error) {
    DB::insert('csv_errors', array('csv_id', 'field', 'error'))
      ->values(array($this->id, $field, $error))
      ->execute();
  }

  public function get_duplicates($args = array()) {
    $query = DB::select()
      ->from('csv_duplicates')
      ->where('csv_id', '=', $this->id);
    foreach ($args as $key => $value) $query->where($key, 'IN', (array) $value);
    foreach ($query->execute() as $result) $duplicates[$result['field'] ? $result['field'] : 'all'][] = $result['duplicate_csv_id'];

    $query = DB::select()
      ->from('csv_duplicates')
      ->where('duplicate_csv_id', '=', $this->id);
    foreach ($args as $key => $value) $query->where($key, 'IN', (array) $value);
    foreach ($query->execute() as $result) $duplicates[$result['field'] ? $result['field'] : 'all'][] = $result['csv_id'];

    return (array) $duplicates;
  }

  public function unset_duplicates($args = array()) {
    $query = DB::delete('csv_duplicates')
      ->where('csv_id', '=', $this->id)
      ->or_where('duplicate_csv_id', '=', $this->id);
    foreach ($args as $key => $value) $query->where($key, 'IN', (array) $value);
    $query->execute();
  }

  public function set_duplicate($duplicate_csv_id, $field = NULL) {
    $ids = array($this->id, $duplicate_csv_id);
    DB::insert('csv_duplicates', array('csv_id', 'duplicate_csv_id', 'field'))
      ->values(array(min($ids), max($ids), $field))
      ->execute();
  }


}
