<?php

class Model_Site extends ORM {

  protected $_belongs_to = array(
    'operator' => array()
  );

  protected $_has_many = array(
    'blocks'    => array(),
    'printjobs' => array(),
    'invoices'  => array()
  );

}

?>