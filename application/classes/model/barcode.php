<?php

class Model_Barcode extends ORM {

  protected $_belongs_to = array(
    'printjob' => array(),
    'parent'   => array(
      'model'       => 'barcode',
      'foreign_key' => 'parent_id'
    ),
    'user' => array()
  );

  protected $_has_many = array(
    'children' => array(
      'model' => 'barcode',
      'foreign_key' => 'parent_id'
    )
  );

  public function formo() {
    $array = array(
      'id'        => array('render' => FALSE),
      'parent_id' => array('render' => FALSE),
      'type'      => array('render' => FASLE),
      'printjob'  => array(
        'orm_primary_val' => 'number',
        'label'           => 'Print Job'
      ),
    );
    foreach (self::fields() as $field => $label) {
      $array[$field]['label'] = $label;
    }
    return $array;
  }

  public static function fields() {
    return array(
      'barcode'     => 'Barcode',
      'is_locked'   => 'Locked',
      'printjob_id' => 'Print Job'
    );
  }

  public function __get($column) {
    switch ($column) {
      case 'is_locked':
        return parent::__get($column) == 't' ? TRUE : FALSE;

      default:
        return parent::__get($column);
    }
  }

  public function set_activiy($status) {

  }

}
