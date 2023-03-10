<?php

class Model_Invoice extends ORM {

  protected $_belongs_to = array(
    'operator' => array(),
    'site'     => array(),
    'file'     => array(),
    'user'     => array()
  );

  protected $_has_many = array(
    'payments' => array()
  );

  public static function create_invoice_number($type) {
    return DB::query(Database::SELECT, "SELECT nextval('s_invoices_{$type}_number') number")
      ->execute()
      ->get('number');
  }

  public function get_data($args = array()) {
    $query = DB::select('form_data_id')
      ->from('invoice_data')
      ->where('invoice_id', '=', $this->id);
    foreach ($args as $key => $value) $query->where($key, 'IN', (array) $value);
    return $query->execute()->as_array(NULL, 'form_data_id');
  }

  public function unset_data($args = array()) {
    $query = DB::delete('invoice_data')
      ->where('invoice_id', '=', $this->id);
    foreach ($args as $key => $value) $query->where($key, 'IN', (array) $value);
    $query->execute();
  }

  public function set_data($form_type, $form_data_id) {
    DB::insert('invoice_data', array('invoice_id', 'form_type', 'form_data_id'))
      ->values(array($this->id, $form_type, $form_data_id))
      ->execute();
  }

  public function set($column, $value) {
    switch ($column) {
      case 'values':
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
      case 'values':
        $value = parent::__get($column);
        return is_string($value) ? unserialize($value) : $value;

      case 'values':
        $value = parent::__get($column);
        return is_string($value) ? unserialize($value) : $value;

      case 'is_paid':
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
