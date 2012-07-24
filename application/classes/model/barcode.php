<?php

class Model_Barcode extends ORM {

  protected $_belongs_to = array(
    'printjob' => array(),
    'parent'   => array(
      'model'       => 'barcode',
      'foreign_key' => 'parent_id'
    ),
    'user' => array()
  );

  protected $_has_many = array(
    'children' => array(
      'model' => 'barcode',
      'foreign_key' => 'parent_id'
    )
  );

}
