<?php defined('SYSPATH') or die('No direct script access.');

class Valid extends Kohana_Valid {

  public static function is_unique($table, $field, $value, $id = array())
  {
    $query = DB::select($field)
      ->from($table)
      ->where($field, '=', $value)
    ;
    if ($id) $query->and_where('id', 'NOT IN', (array) $id);

    return ! (bool) $query->execute()->get($field);
  }

  public static function is_unique_fields($table, $fields, $values, $id = array())
  {
    $query = DB::select_array($fields)->from($table);
    foreach ($values as $field => $value) $query->where($field, '=', $value);
    if ($id) $query->and_where('id', 'NOT IN', (array) $id);

    return ! (bool) $query->execute()->as_array();
  }

  public static function is_existing_barcode($value, $type = NULL)
  {
    return (bool) SGS::lookup_barcode($value, $type, TRUE);
  }

  public static function is_existing_operator($value)
  {
    return (bool) SGS::lookup_operator($value, TRUE);
  }

  public static function is_existing_site($value)
  {
    return (bool) SGS::lookup_site($value, TRUE);
  }

  public static function is_existing_block($array, $site_name_field, $name_field)
  {
    return (bool) SGS::lookup_block($array[$site_name_field], $array[$name_field], TRUE);
  }

  public static function is_existing_species($value)
  {
    return (bool) SGS::lookup_species($value, TRUE);
  }

  public static function is_int($value)
  {
    return (bool) preg_match('/^-?[0-9]+$/', (string) $value);
  }

  public static function is_float($value)
  {
    return (bool) preg_match('/^-?[0-9]+(\.[0-9]+)?$/', (string) $value);
  }

  public static function is_char($value, $length = 1)
  {
    return (bool) (self::exact_length($value, $length));
  }

  public static function is_varchar($value, $max_length = NULL)
  {
    return (bool) ($max_length ? self::max_length($value, $max_length) : TRUE);
  }

  public static function is_text_tiny($value)
  {
    return (bool) (self::is_varchar($value, 25));
  }

  public static function is_text_short($value)
  {
    return (bool) (self::is_varchar($value, 50));
  }

  public static function is_text_medium($value)
  {
    return (bool) (self::is_varchar($value, 500));
  }

  public static function is_text_long($value)
  {
    return (bool) (self::is_varchar($value));
  }

  public static function is_boolean($value)
  {
    return (bool) preg_match('/^yes|no|y|n|1|0|true|false|t|f$/i', strtolower((string) $value));
  }

  public static function is_money($value)
  {
    return (bool) preg_match('/^-?[0-9]+(\.[0-9]{2})?$/', (string) $value);
  }

  public static function is_date($value)
  {
    return (bool) self::is_timestamp($value);
  }

  public static function is_timestamp($value)
  {
    return (bool) (strtotime($value) > 0);
  }

  public static function is_positive_int($value)
  {
    return (bool) (self::is_int($value) AND $value > 0);
  }

  public static function is_measurement_int($value)
  {
    return (bool) (self::is_int($value) AND $value >= 0);
  }

  public static function is_measurement_float($value)
  {
    return (bool) (self::is_float($value) AND $value >= 0);
  }

  public static function is_file_type($value)
  {
    return (bool) (self::is_varchar($value, 100));
  }

  public static function is_species_code($value)
  {
    return (bool) (self::is_varchar($value, 5) AND preg_match('/^[A-Z]{3,5}$/', (string) $value));
  }

  public static function is_species_class($value, $class = array())
  {
    if ($class) $class = (array) $class;
    return (bool) (self::is_char($value) AND preg_match('/^['.($class ? implode('', $class) : 'ABC').']$/', (string) $value));
  }

  public static function is_site_name($value)
  {
    return (bool) (self::is_varchar($value, 15) AND preg_match('/^[A-Z]{3,4}[\s_-]*[A-Z0-9]{1,10}$/', (string) $value));
  }

  public static function is_operator_tin($value)
  {
    return (bool) self::is_positive_int($value);
  }

  public static function is_survey_line($value)
  {
    return (bool) (self::is_positive_int($value) AND ($value <= 20));
  }

  public static function is_operation($value)
  {
    return (bool) (self::is_char($value) AND preg_match('/^[UD]$/', (string) $value));
  }

  public static function is_operation_type($value, $type = array())
  {
    if ($type) $type = (array) $type;
    return (bool) (self::is_char($value) AND preg_match('/^('.($type ? implode('|', $type) : 'SSF|TDF|LDF|MIF|MOF|SPECS|WB|EXP|PJ|UNKWN').'$/', (string) $value));
  }

  public static function is_form_type($value, $type = array())
  {
    if ($type) $type = (array) $type;
    return (bool) (self::is_varchar($value, 5) AND preg_match('/^('.($type ? implode('|', $type) : 'SSF|TDF|LDF|MIF|MOF|SPECS|WB|EXP').')$/', (string) $value));
  }

  public static function is_grade($value, $grade = array())
  {
    if ($grade) $grade = (array) $grade;
    return (bool) (self::is_varchar($value, 3) AND preg_match('/^('.($grade ? implode('|', $grade) : 'LM|A|AB|B|BC|C|D|FAS|CG|1|2|3').')$/', (string) $value));
  }

  public static function is_barcode($value, $barcodify = FALSE)
  {
    if ($barcodify) $value = SGS::barcodify($value);
    return (bool) (self::is_varchar($value) AND preg_match('/^[0123456789ABCDEFGHJKLMNPQRSTVWXYZ]{8}(-[0123456789ACEFHJKLMNPRYXW]{4})?$/', (string) $value));
  }

  public static function is_barcode_type($value, $type = array())
  {
    if ($type) $type = (array) $type;
    return (bool) (self::is_char($value) AND preg_match('/^['.($type ? implode('', $type) : 'PTFSLRHEW').']$/', (string) $value));
  }

  public static function is_conversion_factor($value)
  {
    return (bool) (self::is_float($value) AND ($value > 0) AND ($value < 1));
  }

  public static function is_block_name($value)
  {
    return (bool) (self::is_varchar($value, 7) AND preg_match('/^[A-Z]{1,4}[0-9]{1,3}$/', (string) $value));
  }

  public static function is_status($value, $status = array())
  {
    if ($status) $status = (array) $status;
    return (bool) (self::is_char($value) AND preg_match('/^['.($status ? implode('', $status) : 'PARDU').']$/', (string) $value));
  }

  public static function is_barcode_activity($value, $activity)
  {
    if ($activity) $activity = (array) $activity;
    return (bool) (self::is_char($value) AND preg_match('/^['.($activity ? implode('', $activity) : 'PIHTXDNEOSYALZC').']$/', (string) $value));
  }

  public static function is_username($value)
  {
    return (bool) (self::is_char($value) AND preg_match('/^[0-9A-Za-z_]{3,24}$/', (string) $value));
  }

  public static function is_utm($value)
  {
    return (bool) (self::is_varchar($value, 19) AND preg_match('/^[0-9]{1,2} [0-9]{6}E [0-9]{1,8}N$/', (string) $value));
  }

  public static function is_accurate($value, $test, $tolerance = 0, $lower_bound = TRUE, $upper_bound = TRUE)
  {
    return (bool) ((($lower_bound ? ($test - $tolerance) : $value) <= $value) AND (($upper_bound ? ($test + $tolerance) : $value) >= $value));
  }
}
