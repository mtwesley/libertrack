<?php

class SGS_Form_ORM extends ORM {

//
//    TODO: replace $_fields with static::$fields when PHP 5.3 is being used
//    TODO: replace $_type with static::$type when PHP 5.3 is being used
//

  public static $fields = array();

  public static $type = NULL;

  public static function get_fields($form_type = NULL)
  {
    return call_user_func(array('Model_'.$form_type, 'fields'));
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
      $_fields = call_user_func(array('Model_'.$form_type, 'fields'));

      foreach ($data as $key => $value) $_data[$_fields[$key]] = $value;
      $_validation = new Validation($_data);

      foreach ($this->other_rules() as $field => $set) $_validation->rules($_fields[$field], $set);

      try { $valid = $_validation->check(); }
      catch (Validation_Exception $e) { return FALSE; }

      return $_validation->errors('');
    }
  }

  public static function get_messages()
  {
    return array();
  }

}