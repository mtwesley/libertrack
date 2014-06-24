<?php

class Model_Site extends ORM {

  protected $_belongs_to = array(
    'operator' => array(),
    'user' => array()
  );

  protected $_has_many = array(
    'blocks'    => array(),
    'printjobs' => array(),
    'invoices'  => array()
  );

  public function formo() {
    $array = array(
      'operator' => array(
        'orm_primary_val' => 'name',
        'label'           => 'Operator'
      ),
      'id'         => array('render' => FALSE),
      'type'       => array('render' => FALSE),
      'is_deleted' => array('render' => FALSE),
      'site_id'    => array('driver' => 'select')
    );
    foreach (self::fields() as $field => $label) {
      $array[$field]['label'] = $label;
    }
    return $array;
  }

  public static function fields() {
    return array(
      'operator_id' => 'Operator',
      'name'        => 'Name',
      'type'        => 'Type',
      'owner'       => 'Owner'
    );
  }

  public function rules()
  {
    return array(
      'name'        => array(array('not_empty'),
                             array('is_text_short'),
                             array('is_site_name')),
      'operator_id' => array(array('not_empty')),
      'user_id'            => array(),
      'timestamp'          => array()
    );
  }

}
