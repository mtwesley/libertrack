<?php

class SGS_Form_ORM extends ORM {

  public static $fields = array();

  public static $type = NULL;

  public static function get_fields($form_type = NULL, $display = FALSE)
  {
//     return call_user_func_array(array('Model_'.($form_type ? $form_type : static::$type), 'fields'), array($display));
    return call_user_func_array(array('Model_'.$form_type, 'fields'), array($display));
  }

  public function validate_data($data, $return = 'validation')
  {
    $valid = FALSE;
    $data  = array();
    $validation = new Validation($data);

    foreach ($this->other_rules() as $field => $set) $validation->rules($field, $set);

    try { $valid = $validation->check(); }
    catch (Validation_Exception $e) { return FALSE; }

    if ($return == 'validation')  return $validation;
    else if ($return == 'check')  return $valid;
    else if ($return == 'errors') return $validation->errors('');
    else if ($return == 'pretty_errors') {
      return $validation->errors('');
//
//    TODO: replace when PHP 5.3 is being used
//
//      $_data = array();
//      foreach ($data as $key => $value) $_data[static::$fields[$key]] = $value;
//      $_validation = new Validation($_data);
//
//      foreach ($this->other_rules() as $field => $set) $_validation->rules(static::$fields[$field], $set);
//
//      try { $valid = $_validation->check(); }
//      catch (Validation_Exception $e) { return FALSE; }
//
//      return $_validation->errors('');
    }
  }

  public static function get_messages()
  {
    return array();
  }

}