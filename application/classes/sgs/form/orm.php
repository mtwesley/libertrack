<?php

class SGS_Form_ORM extends ORM {

  public static $fields = array();

  public static $type = NULL;

  public static function get_fields($form_type = NULL)
  {
    return call_user_func(array('Model_'.($form_type ? $form_type : static::$type), 'fields'));
  }

  public function validate_data($data, $form_type, $return = 'validation')
  {
    $valid = FALSE;
    $validation = new Validation($data);

    foreach ($this->other_rules() as $field => $set) $validation->rules($field, $set);

    try { $valid = $validation->check(); }
    catch (Validation_Exception $e) { return FALSE; }

    if ($return == 'validation')  return $validation;
    else if ($return == 'check')  return $valid;
    else if ($return == 'errors') return $validation->errors('');
    else if ($return == 'pretty_errors') {
      $_data   = array();

      foreach ($data as $key => $value) $_data[static::$fields[$key]] = $value;
      $_validation = new Validation($_data);

      foreach ($this->other_rules() as $field => $set) $_validation->rules(static::$fields[$field], $set);

      try { $valid = $_validation->check(); }
      catch (Validation_Exception $e) { return FALSE; }

      return $_validation->errors('');
    }
  }

  public static function get_messages()
  {
    return array();
  }

  public function get_errors($args = array()) {
    $query = DB::select()
      ->from('errors')
      ->where('form_type', '=', static::$type)
      ->and_where('form_data_id', '=', $this->id)
      ->and_where('type', '=', 'E');
    foreach ($args as $key => $value) $query->where($key, 'IN', (array) $value);
    foreach ($query->execute() as $result) $errors[$result['field']][] = $result['error'];
    return (array) $errors;
  }

  public function unset_errors($args = array()) {
    $query = DB::delete('errors')
      ->where('form_type', '=', static::$type)
      ->and_where('form_data_id', '=', $this->id)
      ->and_where('type', '=', 'E');
    foreach ($args as $key => $value) $query->where($key, 'IN', (array) $value);
    $query->execute();
  }

  public function set_error($field, $error) {
    DB::insert('errors', array('form_type', 'form_data_id', 'field', 'error', 'type'))
      ->values(array(static::$type, $this->id, $field, $error, 'E'))
      ->execute();
  }

  public function get_warnings($args = array()) {
    $query = DB::select()
      ->from('errors')
      ->where('form_type', '=', static::$type)
      ->and_where('form_data_id', '=', $this->id)
      ->and_where('type', '=', 'W');
    foreach ($args as $key => $value) $query->where($key, 'IN', (array) $value);
    foreach ($query->execute() as $result) $warnings[$result['field']][] = $result['error'];
    return (array) $warnings;
  }

  public function unset_warnings($args = array()) {
    $query = DB::delete('errors')
      ->where('form_type', '=', static::$type)
      ->and_where('form_data_id', '=', $this->id)
      ->and_where('type', '=', 'W');
    foreach ($args as $key => $value) $query->where($key, 'IN', (array) $value);
    $query->execute();
  }

  public function set_warning($field, $warning) {
    DB::insert('errors', array('form_type', 'form_data_id', 'field', 'error', 'type'))
      ->values(array(static::$type, $this->id, $field, $warning, 'W'))
      ->execute();
  }
}