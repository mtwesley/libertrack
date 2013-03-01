<?php

class SGS_Form_ORM extends ORM {

  public static $fields = array();

  public static $type = NULL;

  public static function get_fields($form_type = NULL)
  {
    return call_user_func(array('Model_'.($form_type ? $form_type : static::$type), 'fields'));
  }

  public function process_check($error_test, $warning_test, $field, $check, $params = array(), &$errors = array(), &$warnings = array()) {
    if ($error_test) {
      $this->set_error($field, $error, $params);
      $errors[$field][$check] = (array) $params;
    } else if ($warning_test) {
      $this->set_warning($field, $warning, $params);
      $warnings[$field][$check] = (array) $params;
    } else {
      $this->set_success($field, $error, $params);
    }
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

  public function reset_checks() {
    $query = DB::delete('errors')
      ->where('form_type', '=', static::$type)
      ->and_where('form_data_id', '=', $this->id);
    $query->execute();
  }

  public function get_successes($with_params = FALSE, $by_field = TRUE, $array = TRUE, $args = array()) {
    $query = DB::select()
      ->from('errors')
      ->where('form_type', '=', static::$type)
      ->and_where('form_data_id', '=', $this->id)
      ->and_where('type', '=', 'S');
    foreach ($args as $key => $value) $query->where($key, 'IN', (array) $value);
    foreach ($query->execute() as $result) {
      if ($with_params)
        if ($array) $errors[$result[$by_field ? 'field' : 'error']][$result[$by_field ? 'error' : 'field']] = unserialize($result['params']);
        else $errors[$result[$by_field ? 'field' : 'error']] = unserialize($result['params']);
      else $errors[$result[$by_field ? 'field' : 'error']][] = $result[$by_field ? 'error' : 'field'];
    }
    return (array) $errors;
  }

  public function unset_successes($args = array()) {
    $query = DB::delete('errors')
      ->where('form_type', '=', static::$type)
      ->and_where('form_data_id', '=', $this->id)
      ->and_where('type', '=', 'S');
    foreach ($args as $key => $value) $query->where($key, 'IN', (array) $value);
    $query->execute();
  }

  public function set_success($field, $error, $params = array()) {
    DB::insert('errors', array('form_type', 'form_data_id', 'field', 'error', 'type', 'params'))
      ->values(array(static::$type, $this->id, $field, $error, 'S', $params ? serialize($params) : NULL))
      ->execute();
  }

  public function get_errors($with_params = FALSE, $by_field = TRUE, $array = TRUE, $args = array()) {
    $query = DB::select()
      ->from('errors')
      ->where('form_type', '=', static::$type)
      ->and_where('form_data_id', '=', $this->id)
      ->and_where('type', '=', 'E');
    foreach ($args as $key => $value) $query->where($key, 'IN', (array) $value);
    foreach ($query->execute() as $result) {
      if ($with_params)
        if ($array) $errors[$result[$by_field ? 'field' : 'error']][$result[$by_field ? 'error' : 'field']] = unserialize($result['params']);
        else $errors[$result[$by_field ? 'field' : 'error']] = unserialize($result['params']);
      else $errors[$result[$by_field ? 'field' : 'error']][] = $result[$by_field ? 'error' : 'field'];
    }
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

  public function set_error($field, $error, $params = array()) {
    DB::insert('errors', array('form_type', 'form_data_id', 'field', 'error', 'type', 'params'))
      ->values(array(static::$type, $this->id, $field, $error, 'E', $params ? serialize($params) : NULL))
      ->execute();
  }

  public function get_warnings($with_params = FALSE, $by_field = TRUE, $array = TRUE, $args = array()) {
    $query = DB::select()
      ->from('errors')
      ->where('form_type', '=', static::$type)
      ->and_where('form_data_id', '=', $this->id)
      ->and_where('type', '=', 'W');
    foreach ($args as $key => $value) $query->where($key, 'IN', (array) $value);
    foreach ($query->execute() as $result) {
      if ($with_params)
        if ($array) $errors[$result[$by_field ? 'field' : 'error']][$result[$by_field ? 'error' : 'field']] = unserialize($result['params']);
        else $errors[$result[$by_field ? 'field' : 'error']] = unserialize($result['params']);
      else $warnings[$result[$by_field ? 'field' : 'error']][] = $result[$by_field ? 'error' : 'field'];
    }
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

  public function set_warning($field, $warning, $params = array()) {
    DB::insert('errors', array('form_type', 'form_data_id', 'field', 'error', 'type', 'params'))
      ->values(array(static::$type, $this->id, $field, $warning, 'W', $params ? serialize($params) : NULL))
      ->execute();
  }

  public function is_invoiced($type = NULL) {
    $query = DB::select('invoices.id')
      ->from('invoices')
      ->join('invoice_data')
      ->on('invoices.id', '=', 'invoice_data.invoice_id')
      ->where('form_type', '=', static::$type)
      ->and_where('form_data_id', '=', $this->id)
      ->and_where('number', 'IS NOT', NULL);
    if ($type) $query->and_where('invoices.type', '=', $type);
    return (bool) $query
      ->execute()
      ->get('id', NULL);
  }

}