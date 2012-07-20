<?php

class Model_Block extends ORM {

  protected $_belongs_to = array(
    'site' => array(),
    'user' => array()
  );

}

?>