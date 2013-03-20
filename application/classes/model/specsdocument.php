<?php

class Model_SpecsDocument extends ORM {

  protected $_table_name = 'specs';

  protected $_belongs_to = array(
    'operator' => array(),
    'specs_barcode'  => array(
      'model'       => 'barcode',
      'foreign_key' => 'specs_barcode_id'),
    'exp_barcode'  => array(
      'model'       => 'barcode',
      'foreign_key' => 'exp_barcode_id'),
    'exp' => array(
      'model'       => 'expdocument',
      'foreign_key' => 'exp_id'),
    'file'     => array(),
    'user'     => array()
  );

  public function __get($column) {
    switch ($column) {
      case 'is_draft':
        return parent::__get($column) == 't' ? TRUE : FALSE;
      default:
        return parent::__get($column);
    }
  }

  public function delete() {
    $this->unset_data();
    parent::delete();
  }

}
