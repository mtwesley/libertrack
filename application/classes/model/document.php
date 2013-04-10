<?php

class Model_Document extends ORM {

  protected $_belongs_to = array(
    'operator' => array(),
    'site'     => array(),
    'barcode'  => array(),
    'file'     => array(),
    'user'     => array()
  );

  public static function create_document_number($type) {
    return DB::query(Database::SELECT, "SELECT nextval('s_documents_{$type}_number') number")
      ->execute()
      ->get('number');
  }

  public function get_data($args = array()) {
    $query = DB::select('form_data_id')
      ->from('document_data')
      ->where('document_id', '=', $this->id);
    foreach ($args as $key => $value) $query->where($key, 'IN', (array) $value);
    return $query->execute()->as_array(NULL, 'form_data_id');
  }

  public function unset_data($args = array()) {
    $query = DB::delete('document_data')
      ->where('document_id', '=', $this->id);
    foreach ($args as $key => $value) $query->where($key, 'IN', (array) $value);
    $query->execute();
  }

  public function set_data($form_type, $form_data_id) {
    DB::insert('document_data', array('document_id', 'form_type', 'form_data_id'))
      ->values(array($this->id, $form_type, $form_data_id))
      ->execute();
  }

  public function set($column, $value) {
    switch ($column) {
      case 'values':
        if (is_array($value)) {
          // set properties
          $this->operator_id = $this->operator_id ?: ($operator_id = SGS::lookup_operator($value['operator_tin'], TRUE)) ? $operator_id : NULL;
          $this->site_id     = $this->site_id     ?: ($site_id     = SGS::lookup_site($value['site_name'], TRUE)) ? $site_id : NULL;
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
      case 'number':
        return str_pad(parent::__get($column), 6, '0', STR_PAD_LEFT);

      case 'values':
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
