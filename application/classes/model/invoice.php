<?php

class Model_Invoice extends ORM {

  protected $_belongs_to = array(
    'site' => array(),
    'file' => array(),
    'user' => array()
  );

}
