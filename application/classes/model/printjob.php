<?php

class Model_Printjob extends ORM {

  const PARSE_START = 7;

  protected $_object_plural = 'printjobs';

  protected $_belongs_to = array(
    'site' => array(),
    'user' => array()
  );

  protected $_has_many = array(
    'barcodes' => array()
  );

  public function formo() {
    return array(
      'site' => array(
        'orm_primary_val' => 'name'
      )
    );
  }
  public function parse_txt($line, &$txt) {
    $matches = array();
    preg_match('/Print\sJob\:\s*(\d+).*/', $txt[2], $matches);

    $number = $matches[1];

    return (trim($line)) ? array(
      'printjob_number' => $number,
      'barcode'         => trim($line),
    ) : null;
  }

}
