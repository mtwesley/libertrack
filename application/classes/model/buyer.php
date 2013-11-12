<?php

class Model_Buyer extends ORM {

  protected $_belongs_to = array(
    'user' => array()
  );

  public function formo() {
    $array = array(
      'id'         => array('render' => FALSE),
      'address'    => array('driver' => 'textarea'),
    );
    foreach (self::fields() as $field => $label) {
      $array[$field]['label'] = $label;
    }
    return $array;
  }

  public static function fields() {
    return array(
      'name'    => 'Name',
      'contact' => 'Contact',
      'address' => 'Address',
      'email'   => 'E-mail',
      'phone'   => 'Phone Number',
    );
  }

  public function rules()
  {
    return array(
      'name'      => array(array('not_empty'),
                           array('is_text_short'),
                           array('is_unique', array($this->_table_name, ':field', ':value', $this->id))),
      'contact'   => array(array('is_text_short')),
      'address'   => array(array('is_text_medium')),
      'email'     => array(array('is_text_short')),
      'phone'     => array(array('is_text_short')),
      'user_id'   => array(),
      'timestamp' => array()
    );
  }

}
