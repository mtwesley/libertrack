<?php

class Model_File extends ORM {

  protected $_belongs_to = array(
    'user' => array()
  );

  protected $_has_many = array(
    'csv'      => array('model' => 'csv'),
    'invoices' => array()
  );

}
