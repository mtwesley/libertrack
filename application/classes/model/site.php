<?php

class Model_Site extends ORM {

  protected $_belongs_to = array(
    'operator' => array(),
    'user' => array()
  );

  protected $_has_many = array(
    'blocks'    => array(),
    'printjobs' => array(),
    'invoices'  => array()
  );

  public function formo() {
    return array(
      'operator' => array(
        'orm_primary_val' => 'name'
      ),
      'id' => array(
        'render' => false
      )
    );
  }

}
