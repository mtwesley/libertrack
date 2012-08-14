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
    $array = array(
      'id' => array('render' => FALSE),
      'site' => array(
        'orm_primary_val' => 'name',
        'label'           => 'Site'
      ),
    );
    foreach (self::fields() as $field => $label) {
      $array[$field]['label'] = $label;
    }
    return $array;
  }

  public static function fields() {
    return array(
      'number'          => 'Print Job',
      'site_id'         => 'Site',
      'allocation_date' => 'Allocation Date'
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

  public function rules()
  {
    return array(
      'site_id'         => array(array('not_empty')),
      'number'          => array(array('is_positive_int')),
      'allocation_date' => array('is_date'),
      'user_id'         => array(),
      'timestamp'       => array()
    );
  }

}
