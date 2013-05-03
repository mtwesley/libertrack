<?php

class Model_Invoice extends ORM {

  protected $_belongs_to = array(
    'operator' => array(),
    'site'     => array(),
    'file'     => array(),
    'user'     => array()
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

  public function check_payment() {
    if ($this->is_draft or !$this->invnumber) return;

    $ledger  = Database::instance('ledger');
    $account = DB::select('amount', 'netamount', 'paid')
      ->from('ar')
      ->where('invnumber', '=', $this->invnumber)
      ->execute($ledger)
      ->as_array();

    extract($account);

    if ($amount == $netamount)
    if ($amount == $paid) return TRUE;
    else return FALSE;
  }

  public function set($column, $value) {
    switch ($column) {
      case 'values':
        if (is_array($value)) {
          // set properties
          $this->operator_id = ($operator_id = SGS::lookup_operator($value['operator_tin'], TRUE)) ? $operator_id : NULL;
          $this->site_id     = ($site_id     = SGS::lookup_site($value['site_name'], TRUE)) ? $site_id : NULL;
          $this->barcode_id  = $this->barcode_id  ?: ($barcode_id  = SGS::lookup_barcode($value['barcode'], NULL, TRUE)) ? $site_id : NULL;

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
