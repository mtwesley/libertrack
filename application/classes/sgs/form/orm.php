<?php

class SGS_Form_ORM extends ORM {

  public static function get_fields($form_type, $display = FALSE)
  {
    return call_user_func_array(array('Model_'.$form_type, 'fields'), array($display));
  }

  public function validate_data($data, $return = 'validation')
  {
    $valid      = FALSE;
    $validation = new Validation($data);

    foreach ($this->other_rules() as $field => $set) {
      $validation->rules($field, $set);
    }

    try {
      $valid = $validation->check();
    }
    catch (Validation_Exception $e) {}

    if ($return == 'validation')  return $validation;
    else if ($return == 'errors') return $validation->errors('');
    else if ($return == 'check')  return $valid;
  }

  public static function get_messages()
  {
    return array();
  }

}