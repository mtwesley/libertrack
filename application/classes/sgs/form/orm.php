<?php

class SGS_Form_ORM extends ORM {

  public static function get_fields($form_type)
  {
    return call_user_func(array('Model_'.$form_type, 'fields'));
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
    return array(
//      'site_id'          => array(array('not_empty')),
//      'operator_id'      => array(array('not_empty')),
//      'block_id'         => array(array('not_empty')),
//      'species_id'       => array(array('not_empty')),
//      'barcode_id'       => array(array('not_empty'),
//                                  array('is_unique', array($this->_table_name, ':field', ':value'))),
//      'tree_barcode_id'  => array(array('not_empty')),
//      'stump_barcode_id' => array(array('not_empty'),
//                                  array('is_unique', array($this->_table_name, ':field', ':value'))),
//      'survey_line'      => array(array('not_empty')),
//      'cell_number'      => array(array('not_empty')),
//      'top_min'          => array(array('not_empty')),
//      'top_max'          => array(array('not_empty')),
//      'bottom_min'       => array(array('not_empty')),
//      'bottom_max'       => array(array('not_empty')),
//      'length'           => array(array('not_empty')),
    );
  }

}