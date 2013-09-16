<?php

class Model_Report extends ORM {

  protected $_belongs_to = array(
    'user'  => array()
  );

  protected $_has_many = array(
    'files' => array()
  );

  public static $models = array(
    'operator' => 'Operator',
    'sites'    => 'Site',
    'ssf'      => 'Stock Survey',
    'tdf'      => 'Tree Data',
    'ldf'      => 'Log Data',
    'specs'    => 'Shipment Specification',
    'barcode'  => 'Barcode',
  );

  public static function create_report_number($type) {
    return DB::query(Database::SELECT, "SELECT nextval('s_reports_{$type}_number') number")
      ->execute()
      ->get('number');
  }

  public function set($column, $value) {
    switch ($column) {
      case 'tables':
      case 'fields':
      case 'filters':
        if (is_array($value)) {
          // prepare for db
          $_value = $value;
          sort($_value);
          ksort($_value);
          $value = serialize($value);
        }
        else if (!is_string($value)) $value = NULL;
      default:
        parent::set($column, $value);
    }
  }

  public function __get($column) {
    switch ($column) {
      case 'number':
        $value = parent::__get($column);
        return $value ? SGS::numberify($value) : NULL;

      case 'tables':
      case 'fields':
      case 'filters':
        $value = parent::__get($column);
        return is_string($value) ? unserialize($value) : $value;

      case 'is_draft':
        return parent::__get($column) == 't' ? TRUE : FALSE;

      default:
        return parent::__get($column);
    }
  }

  public function delete() {
    $this->unset_data();
    parent::delete();
  }

}
