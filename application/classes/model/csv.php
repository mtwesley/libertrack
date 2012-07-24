<?php

class Model_CSV extends ORM {

  protected $_table_name = 'csv';

  protected $_belongs_to = array(
    'file' => array(),
    'user' => array()
  );

  protected function _initialize() {
    parent::_initialize();

    $this->_object_plural = 'csv';
  }

}
