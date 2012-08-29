<?php

class Model_File extends ORM {

  protected $_belongs_to = array(
    'operator' => array(),
    'site'     => array(),
    'block'    => array(),
    'user' => array()
  );

  protected $_has_many = array(
    'csv'      => array('model' => 'csv'),
    'invoices' => array()
  );

  public function rules()
  {
    return array(
      'name'           => array(array('not_empty')),
//                                array('is_unique', array($this->_table_name, ':field', ':value', $this->id))),
      'type'           => array(array('not_empty')),
      'size'           => array(array('not_empty')),
      'operation'      => array(array('not_empty')),
      'operation_type' => array(array('not_empty')),
      'content_md5'    => array(array('is_unique', array($this->_table_name, ':field', ':value', $this->id))),
    );
  }
}
