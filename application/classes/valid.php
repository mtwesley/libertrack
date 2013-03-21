<?php defined('SYSPATH') or die('No direct script access.');

class Valid extends Kohana_Valid {

  public static function is_unique($table, $field, $value, $id = array())
  {
    $query = DB::select($field)
      ->from($table)
      ->where($field, '=', $value)
    ;
    if ($id) $query->and_where('id', 'NOT IN', (array) $id);

    return ! (bool) $value = $query->execute()->get($field);
  }

  public static function is_existing_barcode($value)
  {
    return (bool) SGS::lookup_barcode($value, NULL, TRUE);
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
    $block = SGS::lookup_block($array[$site_name_field], $array[$name_field], TRUE);
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

  public static function is_species_class($value)
  {
    return (bool) (self::is_char($value) AND preg_match('/^[ABC]$/', (string) $value));
  }

  public static function is_site_name($value)
  {
    return (bool) (self::is_varchar($value, 15) AND preg_match('/^[A-Z]{3}[\s_-]*[A-Z0-9]{1,10}$/', (string) $value));
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
    return (bool) (self::is_char($value) AND preg_match('/^[IEUP]$/', (string) $value));
  }

  public static function is_operation_type($value)
  {
    return (bool) (self::is_char($value) AND preg_match('/^(SSF|TDF|LDF|MIF|MOF|SPECS|EXP|PJ|UNKWN)$/', (string) $value));
  }

  public static function is_form_type($value)
  {
    return (bool) (self::is_varchar($value, 5) AND preg_match('/^(SSF|TDF|LDF|MIF|MOF|SPECS|EXP)$/', (string) $value));
  }

  public static function is_grade($value)
  {
    return (bool) (self::is_varchar($value, 3) AND preg_match('/^(LM|A|AB|B|BC|C|D|FAS|CG|1|2|3)$/', (string) $value));
  }

  public static function is_barcode($value, $barcodify = FALSE)
  {
    if ($barcodify) $value = SGS::barcodify($value);
    return (bool) (self::is_varchar($value) AND preg_match('/^[0123456789ACEFHJKLMNPRYXW]{8}(-[0123456789ACEFHJKLMNPRYXW]{4})?$/', (string) $value));
  }

  public static function is_barcode_type($value)
  {
    return (bool) (self::is_char($value) AND preg_match('/^[PTFSLR]$/', (string) $value));
  }

  public static function is_conversion_factor($value)
  {
    return (bool) (self::is_float($value) AND ($value > 0) AND ($value < 1));
  }

  public static function is_block_name($value)
  {
    return (bool) (self::is_varchar($value, 7) AND preg_match('/^[A-Z]{1,4}[0-9]{1,3}$/', (string) $value));
  }

  public static function is_status($value)
  {
    return (bool) (self::is_char($value) AND preg_match('/^[PARDU]$/', (string) $value));
  }

  public static function is_coc_status($value)
  {
    return (bool) (self::is_char($value) AND preg_match('/^[PIHTXDESYALZ]$/', (string) $value));
  }

  public static function is_username($value)
  {
    return (bool) (self::is_char($value) AND preg_match('/^[0-9A-Za-z_]{3,24}$/', (string) $value));
  }

  public static function is_accurate($value, $test, $tolerance = 0, $lower_bound = TRUE, $upper_bound = TRUE)
  {
    return (bool) ((($lower_bound ? ($value - $tolerance) : $test) <= $test) AND (($upper_bound ? ($value + $tolerance) : $test) >= $test));
  }
}
