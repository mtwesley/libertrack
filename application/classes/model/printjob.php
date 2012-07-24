<?php

class Model_Printjob extends ORM {

  protected $_object_plural = 'printjobs';

  protected $_belongs_to = array(
    'site' => array(),
    'user' => array()
  );

  protected $_has_many = array(
    'barcodes' => array()
  );

}
