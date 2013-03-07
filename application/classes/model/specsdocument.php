<?php

class Model_SpecsDocument extends ORM {

  protected $_table_name = 'specs';

  protected $_belongs_to = array(
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

}
