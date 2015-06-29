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
      } catch (Database_Exception $e) {
        $problem = TRUE;
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
    
    $corrected = FALSE;
    if ($duplicates) foreach ($duplicates as $field => $duplicate_csv_id) {
      if (($corrected == FALSE) and (count($this->get_corrections()) < 3)) {
        $duplicate_csv = ORM::factory('CSV', $duplicate_csv_id);        
        if ($duplicate_csv->form_data_id and (SGS::datetime($duplicate_csv->timestamp, SGS::EPOCH_DATE_FORMAT) < SGS::datetime($this->timestamp, SGS::EPOCH_DATE_FORMAT))) {
          $this->unset_corrections();
          $this->set_correction($duplicate_csv_id, is_int($field) ? NULL : $field);
          $corrected = TRUE;
        }
      } else $this->set_duplicate($duplicate_csv_id, is_int($field) ? NULL : $field);
    }
    
    if ($corrected) {
      $this->resolve();
      $this->reload();
      
      if ($this->status != 'P') return;
    }
    
    if ($problem or $errors) {
      if ($duplicate) $this->status = 'D';
      else if ($duplicates) $this->status = 'U';
      else $this->status = 'R';
    }
    else $this->status = 'A';

    $this->save();
  }

  public function resolve() {
    $duplicates = ORM::factory('CSV')
      ->where('id', 'IN', array_diff(SGS::flattenify($this->get_duplicates(array(), NULL)), array($this->id)))
      ->find_all()
      ->as_array();

    foreach ($duplicates as $duplicate) {
      if ($duplicate->form_data_id) {
        $data = ORM::factory($duplicate->form_type, $duplicate->form_data_id);
        
        if ($data::$data_type != 'TDF' and $data->is_locked()) continue;
        
        if ($data->loaded()) {
          if ($data->is_locked()) {
            DB::query(NULL, 'BEGIN');
            DB::query(NULL, 'ALTER TABLE '.$data->table_name().' DISABLE TRIGGER t_check_barcode_locks');
          }
          
          $data->parse_data($this->values);
          $data->save();
          
          if ($data->is_locked()) {
            DB::query(NULL, 'ALTER TABLE '.$data->table_name().' ENABLE TRIGGER t_check_barcode_locks');
            DB::query(NULL, 'COMMIT');
          }
        }
        
        $this->form_data_id = $duplicate->form_data_id;
        $this->status = $duplicate->status;
        
        $duplicate->form_data_id = NULL;
        $duplicate->status = 'C';
      }
      else {
        $duplicate->status = 'U';
      }
      $duplicate->save();
    }

    $this->save();
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

  public function get_duplicates($args = array(), $corrections = FALSE) {
    $query = DB::select()
      ->from('csv_duplicates')
      ->where('csv_id', '=', $this->id);
    if ($corrections) $query = $query->where('is_corrected', '=', TRUE);
    else if ($corrections === FALSE) $query = $query->where('is_corrected', '=', FALSE);
    foreach ($args as $key => $value) $query->where($key, 'IN', (array) $value);
    foreach ($query->execute() as $result) $duplicates[$result['field'] ? $result['field'] : 'all'][] = $result['duplicate_csv_id'];

    $query = DB::select()
      ->from('csv_duplicates')
      ->where('duplicate_csv_id', '=', $this->id);
    if ($corrections) $query = $query->where('is_corrected', '=', TRUE);
    else if ($corrections === FALSE) $query = $query->where('is_corrected', '=', FALSE);
    foreach ($args as $key => $value) $query->where($key, 'IN', (array) $value);
    foreach ($query->execute() as $result) $duplicates[$result['field'] ? $result['field'] : 'all'][] = $result['csv_id'];

    return (array) $duplicates;
  }

  public function unset_duplicates($args = array(), $corrections = FALSE) {
    $query = DB::delete('csv_duplicates')
      ->where('csv_id', '=', $this->id)
      ->or_where('duplicate_csv_id', '=', $this->id);
    if ($corrections) $query = $query->where('is_corrected', '=', TRUE);
    else if ($corrections === FALSE) $query = $query->where('is_corrected', '=', FALSE);
    foreach ($args as $key => $value) $query->where($key, 'IN', (array) $value);
    $query->execute();
  }

  public function set_duplicate($duplicate_csv_id, $field = NULL, $is_corrected = FALSE) {
    $ids = array($this->id, $duplicate_csv_id);
    DB::insert('csv_duplicates', array('csv_id', 'duplicate_csv_id', 'field', 'is_corrected'))
      ->values(array(min($ids), max($ids), $field, $is_corrected))
      ->execute();
  }

  public function get_corrections($args = array()) {
    return $this->get_duplicates($args, TRUE);
  }

  public function unset_corrections($args = array()) {
    return $this->unset_duplicates($args, TRUE);
  }

  public function set_correction($duplicate_csv_id, $field = NULL) {
    return $this->set_duplicate($duplicate_csv_id, $field, TRUE);
  }

}
