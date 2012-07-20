<?php

class Model_File extends ORM {

  protected $_has_many = array(
    'csv'      => array('model' => 'csv'),
    'invoices' => array()
  );

}

?>