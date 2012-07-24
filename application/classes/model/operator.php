<?php

class Model_Operator extends ORM {

  protected $_belongs_to = array(
    'user' => array()
  );

  protected $_has_many = array(
    'sites' => array()
  );

  public function formo() {
    return array(
      'id' => array(
        'render' => false
      )
    );
  }

}
