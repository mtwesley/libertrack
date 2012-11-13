<?php

class Model_Tolerance extends ORM {

  protected $_belongs_to = array(
    'user' => array()
  );

  public function formo() {
    $array = array(
      'id' => array('render' => FALSE),
    );
    foreach (self::fields() as $field => $label) {
      $array[$field]['label'] = $label;
    }
    return $array;
  }

  public static function fields() {
    return array(
      'form_type'       => 'Form',
      'form_fields'     => 'Field',
      'accuracy_range'  => 'Accuracy Range',
      'tolerance_range' => 'Tolerance Range'
    );
  }

  public function rules()
  {
    return array(
      'form_type'       => array(array('not_empty')),
      'form_fields'     => array(array('not_empty')),
      'accuracy_range'  => array(array('not_empty'),
                           array('is_measurement_float')),
      'tolerance_range' => array(array('not_empty'),
                           array('is_measurement_float')),
      'user_id'   => array(),
      'timestamp' => array()
    );
  }

  public function __get($column) {
    switch ($column) {
      case 'form_fields':
        $value = parent::__get($column);
        $value = is_string($value) ? explode(',', $value) : $value; // remove when serialization is used
        return is_string($value) ? unserialize($value) : $value;
      default:
        return parent::__get($column);
    }
  }

}
