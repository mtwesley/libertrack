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

  public function set($column, $value) {
    switch ($column) {
      case 'values':
        if (is_array($value)) {
          $this->operator_id = ($operator_id = SGS::lookup_operator($value['operator_tin'], TRUE)) ? $operator_id : NULL;
          $this->site_id     = ($site_id     = SGS::lookup_site($value['site_type'], $value['site_reference'], $value['site_name'], TRUE)) ? $site_id : NULL;
          $this->block_id    = ($block_id    = SGS::lookup_block($value['site_type'], $value['site_reference'], $value['site_name'], $value['name'], TRUE)) ? $block_id : NULL;
        }
      case 'errors':
      case 'suggestions':
        if ($value) $value = is_string($value) ? $value : serialize($value);
        else $value = NULL;
      default:
        parent::set($column, $value);
    }
  }

  public function __get($column) {
    switch ($column) {
      case 'values':
      case 'errors':
      case 'suggestions':
        $value = parent::__get($column);
        return is_string($value) ? unserialize($value) : $value;
      default:
        return parent::__get($column);
    }
  }

}
