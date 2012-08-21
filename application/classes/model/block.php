<?php

class Model_Block extends ORM {

  protected $_belongs_to = array(
    'site' => array(),
    'user' => array()
  );

  public function formo() {
    $array = array(
      'site' => array(
        'orm_primary_val' => 'name',
        'label'           => 'Site'
      ),
      'id'   => array('render' => FALSE)
    );
    foreach (self::fields() as $field => $label) {
      $array[$field]['label'] = $label;
    }
    return $array;
  }

  public static function fields() {
    return array(
      'site_id'     => 'Site',
      'name' => 'Name'
    );
  }

  public function rules()
  {
    return array(
      'name'      => array(array('not_empty'),
                           array('is_block_name')),
      'site_id'   => array(array('not_empty')),
      'user_id'   => array(),
      'timestamp' => array()
    );
  }

}
