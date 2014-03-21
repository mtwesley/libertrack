<?php

class SGS_Form_ORM extends ORM {

  public static $fields = array();

  public static $type = NULL;

  public static $data_type = NULL;

  public static $verification_type = NULL;

  public static function get_fields($form_type = NULL)
  {
    return call_user_func(array('Model_'.($form_type ? $form_type : static::$type), 'fields'));
  }

  public function csv() {
    return ORM::factory('CSV')
      ->where('form_type', '=', static::$type)
      ->and_where('form_data_id', '=', $this->id)
      ->find();
  }

  public function data() {
    return ORM::factory(static::$data_type)
      ->where('barcode_id', '=', $this->barcode->id)
      ->find();
  }

  public function verification() {
    return ORM::factory(static::$verification_type)
      ->where('barcode_id', '=', $this->barcode->id)
      ->find();
  }

  public function is_locked() {
    if (isset($this->barcode) and $this->barcode->loaded()) return $this->barcode->is_locked ? TRUE : FALSE;
  }

  public function is_accurate() {
    $verification = $this->verification();
    return ($verification and $verification->loaded() and $verification->status == 'A') ? TRUE : FALSE;
  }

  public function is_verified() {
    $verification = $this->verification();
    return ($verification and $verification->loaded() and $verification->status != 'P') ? TRUE : FALSE;
  }

  public function is_verification() {
    if (in_array(static::$type, array_keys(SGS::$form_verification_type))) return TRUE;
    else if (in_array(static::$type, array_keys(SGS::$form_data_type))) return FALSE;
    else return NULL;
  }

  public function is_invoiced($type = NULL, $is_paid = NULL) {
    $query = DB::select('invoices.id')
      ->from('invoice_data')
      ->join('invoices')
      ->on('invoice_data.invoice_id', '=', 'invoices.id')
      ->where('invoice_data.form_type', '=', static::$type)
      ->and_where('invoice_data.form_data_id', '=', $this->id)
      ->and_where('invoices.is_draft', '=', FALSE);
    if ($type) $query->and_where('invoices.type', '=', $type);
    if ($is_paid !== NULL) $query->and_where('is_paid', '=', (bool) $is_paid);
    if (!$invoice_id = $query->execute()->as_array('id', NULL)) {
      $parent = $this->parent();
      if ($parent and $parent->loaded()) $invoice_id = $parent->is_invoiced($type, $is_paid);
    }
    if (!$invoice_id and isset($this->barcode) and $this->barcode->id) switch ($type) {
      case 'ST':  return $this->barcode->get_activity('T');
      case 'EXF': return $this->barcode->get_activity('X');
      default:    return $this->barcode->get_activity(array('T', 'X'));
    }
    return $invoice_id;
  }

  public function has_problem() {
    if (isset($this->barcode) and $this->barcode->loaded()) $problems['barcode_activity'] = $this->barcode->get_activity(array('H'), FALSE);
    return array_unique(array_filter($problems));
  }

  public function process_check($error_test, $warning_test, $field, $check, $params = array(), &$errors = array(), &$warnings = array()) {
    if ($error_test) {
      $this->set_error($field, $error, $params);
      $errors[$field][$check] = (array) $params;
    } else if ($warning_test) {
      $this->set_warning($field, $warning, $params);
      $warnings[$field][$check] = (array) $params;
    } else {
      $this->set_success($field, $error, $params);
    }
  }

  public function validate_data($data, $form_type, $return = 'validation')
  {
    $valid = FALSE;
    $validation = new Validation($data);

    foreach ($this->csv_rules() as $field => $set) $validation->rules($field, $set);

    try { $valid = $validation->check(); }
    catch (Validation_Exception $e) { return FALSE; }

    if ($return == 'validation')  return $validation;
    else if ($return == 'check')  return $valid;
    else if ($return == 'errors') return $validation->errors('');
    else if ($return == 'pretty_errors') {
      $_data   = array();

      foreach ($data as $key => $value) $_data[static::$fields[$key]] = $value;
      $_validation = new Validation($_data);

      foreach ($this->csv_rules() as $field => $set) $_validation->rules(static::$fields[$field], $set);

      try { $valid = $_validation->check(); }
      catch (Validation_Exception $e) { return FALSE; }

      return $_validation->errors('');
    }
  }

  public static function get_messages()
  {
    return array();
  }

  public function reset_checks() {
    $query = DB::delete($this->is_verification() ? 'verification_checks' : 'checks')
      ->where('form_type', '=', static::$type)
      ->and_where($this->is_verification() ? 'form_verification_id' : 'form_data_id', '=', $this->id);
    $query->execute();
  }

  public function get_successes($with_params = FALSE, $by_field = TRUE, $array = TRUE, $args = array()) {
    $query = DB::select()
      ->from($this->is_verification() ? 'verification_checks' : 'checks')
      ->where('form_type', '=', static::$type)
      ->and_where($this->is_verification() ? 'form_verification_id' : 'form_data_id', '=', $this->id)
      ->and_where('type', '=', 'S');
    foreach ($args as $key => $value) $query->where($key, 'IN', (array) $value);
    foreach ($query->execute() as $result) {
      if ($with_params) {
        if ($array) $successes[$result[$by_field ? 'field' : 'check']][$result[$by_field ? 'check' : 'field']] = unserialize($result['params']);
        else $successes[$result[$by_field ? 'field' : 'check']] = unserialize($result['params']);
      } else $successes[$result[$by_field ? 'field' : 'check']][] = $result[$by_field ? 'check' : 'field'];
    }
    return (array) $successes;
  }

  public function unset_successes($args = array()) {
    $query = DB::delete($this->is_verification() ? 'verification_checks' : 'checks')
      ->where('form_type', '=', static::$type)
      ->and_where($this->is_verification() ? 'form_verification_id' : 'form_data_id', '=', $this->id)
      ->and_where('type', '=', 'S');
    foreach ($args as $key => $value) $query->where($key, 'IN', (array) $value);
    $query->execute();
  }

  public function set_success($field, $check, $params = array()) {
    DB::insert($this->is_verification() ? 'verification_checks' : 'checks', array('form_type', $this->is_verification() ? 'form_verification_id' : 'form_data_id', 'field', 'check', 'type', 'params'))
      ->values(array(static::$type, $this->id, $field, $check, 'S', $params ? serialize($params) : NULL))
      ->execute();
  }

  public function get_errors($with_params = FALSE, $by_field = TRUE, $array = TRUE, $args = array()) {
    $query = DB::select()
      ->from($this->is_verification() ? 'verification_checks' : 'checks')
      ->where('form_type', '=', static::$type)
      ->and_where($this->is_verification() ? 'form_verification_id' : 'form_data_id', '=', $this->id)
      ->and_where('type', '=', 'E');
    foreach ($args as $key => $value) $query->where($key, 'IN', (array) $value);
    foreach ($query->execute() as $result) {
      if ($with_params) {
        if ($array) $errors[$result[$by_field ? 'field' : 'check']][$result[$by_field ? 'check' : 'field']] = unserialize($result['params']);
        else $errors[$result[$by_field ? 'field' : 'check']] = unserialize($result['params']);
      } else $errors[$result[$by_field ? 'field' : 'check']][] = $result[$by_field ? 'check' : 'field'];
    }
    return (array) $errors;
  }

  public function unset_errors($args = array()) {
    $query = DB::delete($this->is_verification() ? 'verification_checks' : 'checks')
      ->where('form_type', '=', static::$type)
      ->and_where($this->is_verification() ? 'form_verification_id' : 'form_data_id', '=', $this->id)
      ->and_where('type', '=', 'E');
    foreach ($args as $key => $value) $query->where($key, 'IN', (array) $value);
    $query->execute();
  }

  public function set_error($field, $check, $params = array()) {
    DB::insert($this->is_verification() ? 'verification_checks' : 'checks', array('form_type', $this->is_verification() ? 'form_verification_id' : 'form_data_id', 'field', 'check', 'type', 'params'))
      ->values(array(static::$type, $this->id, $field, $check, 'E', $params ? serialize($params) : NULL))
      ->execute();
  }

  public function get_warnings($with_params = FALSE, $by_field = TRUE, $array = TRUE, $args = array()) {
    $query = DB::select()
      ->from($this->is_verification() ? 'verification_checks' : 'checks')
      ->where('form_type', '=', static::$type)
      ->and_where($this->is_verification() ? 'form_verification_id' : 'form_data_id', '=', $this->id)
      ->and_where('type', '=', 'W');
    foreach ($args as $key => $value) $query->where($key, 'IN', (array) $value);
    foreach ($query->execute() as $result) {
      if ($with_params) {
        if ($array) $warnings[$result[$by_field ? 'field' : 'check']][$result[$by_field ? 'check' : 'field']] = unserialize($result['params']);
        else $warnings[$result[$by_field ? 'field' : 'check']] = unserialize($result['params']);
      } else $warnings[$result[$by_field ? 'field' : 'check']][] = $result[$by_field ? 'check' : 'field'];
    }
    return (array) $warnings;
  }

  public function unset_warnings($args = array()) {
    $query = DB::delete($this->is_verification() ? 'verification_checks' : 'checks')
      ->where('form_type', '=', static::$type)
      ->and_where($this->is_verification() ? 'form_verification_id' : 'form_data_id', '=', $this->id)
      ->and_where('type', '=', 'W');
    foreach ($args as $key => $value) $query->where($key, 'IN', (array) $value);
    $query->execute();
  }

  public function set_warning($field, $warning, $params = array()) {
    DB::insert($this->is_verification() ? 'verification_checks' : 'checks', array('form_type', $this->is_verification() ? 'form_verification_id' : 'form_data_id', 'field', 'check', 'type', 'params'))
      ->values(array(static::$type, $this->id, $field, $warning, 'W', $params ? serialize($params) : NULL))
      ->execute();
  }

  public function status($status, $comment) {
    if ($this->is_verification())
      if (!in_array($status, array_keys(SGS::$verification_status))) return;
      else if (!in_array($status, array_keys(SGS::$data_status))) return;

    $_status = $this->status;
    $this->status = $status;

    DB::insert('status_activity', array('form_type', 'form_data_id', 'old_status', 'new_status', 'comment', 'user_id'))
      ->values(array(static::$type, $this->id, $_status, $status, $comment, Auth::instance()->get_user()->id ?: 1))
      ->execute();
  }

  public function parent($types = array()) {
    $arr = $this->parents(1, $types);
    return reset($arr) ?: NULL;
  }

  public function parents($max_hops = NULL, $types = array()) {
    $query = DB::select('barcode_hops.parent_id', 'type')
      ->from('barcode_hops')
      ->join('barcodes')
      ->on('barcode_hops.parent_id', '=', 'barcodes.id')
      ->where('barcode_hops.barcode_id', '=', $this->barcode->id);
    if ($max_hops) $query->and_where('hops', '<=', $max_hops);
    $results = $query
      ->order_by('hops', 'ASC')
      ->execute()
      ->as_array();

    foreach ($results as $result) {
      $_types = $types ?: SGS::barcode_to_form_type($result['type']);
      $_types = (array) $_types;
      foreach (array_filter($_types) as $_type) {
        $parent = ORM::factory($_type)
          ->where('barcode_id', '=', $result['parent_id'])
          ->find();
        if ($parent->loaded()) {
          $parents[] = $parent;
          break;
        }
      }
    }

    return array_filter((array) $parents);
  }

  public function children($types = array()) {
    return $this->childrens(1, $type);
  }

  public function childrens($max_hops = NULL, $types = array()) {
    $query = DB::select('barcode_hops.barcode_id', 'type')
      ->from('barcode_hops')
      ->join('barcodes')
      ->on('barcode_hops.barcode_id', '=', 'barcodes.id')
      ->where('barcode_hops.parent_id', '=', $this->barcode->id);
    if ($max_hops) $query->and_where('hops', '<=', $max_hops);
    $results = $query
      ->order_by('hops', 'ASC')
      ->execute()
      ->as_array();

    foreach ($results as $result) {
      $_types = $types ?: SGS::barcode_to_form_type($result['type']);
      $_types = (array) $_types;
      foreach (array_filter($_types) as $_type) {
        $child = ORM::factory($_type)
          ->where('barcode_id', '=', $result['barcode_id'])
          ->find();
        if ($child->loaded()) {
          $children[] = $child;
          break;
        }
      }
    }

    return array_filter((array) $children);
  }

  public function siblings($type = NULL) {
    if ($parent = $this->parent()) return $parent->children($type);
  }

}