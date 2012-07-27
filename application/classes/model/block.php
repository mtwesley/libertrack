<?php

class Model_Block extends ORM {

  protected $_belongs_to = array(
    'site' => array(),
    'user' => array()
  );

  public function formo() {
    return array(
      'site' => array(
        'orm_primary_val' => 'name'
      ),
      'id' => array(
        'render' => false
      )
    );
  }

}
