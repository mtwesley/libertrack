<?php

class Model_User extends Model_Auth_User {

  protected $_has_many = array(
    'species' => array(),
    'operators' => array(),
    'sites' => array(),
    'blocks' => array(),
    'printjobs' => array(),
    'barcodes' => array(),
    'files' => array(),
    'csv' => array(),
    'invoices' => array(),
  );

}
