<?php

class Model_Operator extends ORM {

  protected $_belongs_to = array(
    'user' => array()
  );

  protected $_has_many = array(
    'sites' => array()
  );

  public function formo() {
    $array = array(
      'id'         => array('render' => FALSE),
      'is_deleted' => array('render' => FALSE),
      'address'    => array('driver' => 'textarea'),
    );
    foreach (self::fields() as $field => $label) {
      $array[$field]['label'] = $label;
    }
    return $array;
  }

  public static function fields() {
    return array(
      'tin'     => 'TIN',
      'name'    => 'Name',
      'short_name' => 'Short Name',
      'contact' => 'Contact',
      'address' => 'Address',
      'email'   => 'E-mail',
      'phone'   => 'Phone Number',
    );
  }

  public function rules()
  {
    return array(
      'tin'       => array(array('not_empty'),
                           array('is_operator_tin'),
                           array('is_unique', array($this->_table_name, ':field', ':value', $this->id))),
      'name'      => array(array('not_empty'),
                           array('is_text_short')),
      'short_name' => array(array('is_text_tiny')),
      'contact'   => array(array('is_text_short')),
      'address'   => array(array('is_text_medium')),
      'email'     => array(array('is_text_short')),
      'phone'     => array(array('is_text_short')),
      'user_id'   => array(),
      'timestamp' => array()
    );
  }

}
