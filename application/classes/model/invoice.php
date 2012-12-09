<?php

class Model_Invoice extends ORM {

  protected $_belongs_to = array(
    'site' => array(),
    'file' => array(),
    'user' => array()
  );

  public function get_data($args = array()) {
    $query = DB::select('form_type', 'form_data_id')
      ->from('invoice_data')
      ->where('invoice_id', '=', $this->id);
    foreach ($args as $key => $value) $query->where($key, 'IN', (array) $value);
    return $query->execute()->as_array('form_data_id');
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

  public function __get($column) {
    switch ($column) {
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
