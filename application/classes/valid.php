<?php defined('SYSPATH') or die('No direct script access.');

class Valid extends Kohana_Valid {

  public static function is_unique($table, $field, $value)
  {
    return ! (bool) DB::select($field)
      ->from($table)
      ->where($field, '=', $value)
      ->execute()
      ->get($field);
  }

  public static function is_existing_barcode($value)
  {
    return (bool) SGS::lookup_barcode($value, TRUE);
  }

  public static function is_existing_operator($value)
  {
    return (bool) SGS::lookup_operator($value, TRUE);
  }

  public static function is_existing_site($array, $type_field, $reference_field)
  {
    return (bool) SGS::lookup_site($array[$type_field], $array[$reference_field], TRUE);
  }

  public static function is_existing_site_by_name($value)
  {
    return (bool) SGS::lookup_site_by_name($value, TRUE);
  }

  public static function is_existing_block($array, $site_type_field, $site_reference_field, $coordinates_field)
  {
    return (bool) SGS::lookup_block($array[$site_type_field], $array[$site_reference_field], $array[$coordinates_field], TRUE);
  }

  public static function is_existing_block_by_site_name($array, $site_name_field, $coordinates_field)
  {
    return (bool) SGS::lookup_block($array[$site_name_field], $array[$coordinates_field], TRUE);
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

  public static function is_positive_int($value)
  {
    return (bool) (self::is_int($value) AND $value > 0);
  }

  public static function is_text_short($value)
  {
    return (bool) (UTF8::strlen($value) <= 50);
  }

  public static function is_text_medium($value)
  {
    return (bool) (UTF8::strlen($value) <= 500);
  }

  public static function is_boolean($value)
  {
    return (bool) preg_match('/^yes|no|1|0|true|false$/i', (string) $value);
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

  public static function is_measurement_int($value)
  {
    return (bool) (self::is_int($value) AND $value >= 0);
  }

  public static function is_measurement_float($value)
  {
    return (bool) (self::is_float($value) AND $value >= 0);
  }

  public static function is_file_data_type($value)
  {
    return (bool) (self::is_char($value) AND preg_match('/^[FPU]$/', (string) $value));
  }

  public static function is_species_code($value)
  {
    return (bool) (self::is_varchar($value, 5) AND preg_match('/^[A-Z]{3,5}$/', (string) $value));
  }

  public static function is_species_class($value)
  {
    return (bool) (self::is_char($value) AND preg_match('/^[ABC]$/', (string) $value));
  }

  public static function is_site_type($value)
  {
    return (bool) (self::is_char($value, 3) AND preg_match('/^[A-Z]{3}$/', (string) $value));
  }

  public static function is_site_reference($value)
  {
    return (bool) (self::is_varchar($value, 7) AND preg_match('/^[A-Z1-9]{2,7}$/', (string) $value));
  }

  public static function is_operator_tin($value)
  {
    return (bool) self::is_positive_int($value);
  }

  public static function is_survey_line($value)
  {
    return (bool) (self::is_positive_int($value) AND ($value <= 20));
  }

  public static function is_csv_type($value)
  {
    return (bool) (self::is_char($value) AND preg_match('/^[IE]$/', (string) $value));
  }

  public static function is_form_type($value)
  {
    return (bool) (self::is_varchar($value, 5) AND preg_match('/^(SSF|TDF|LDF|MIF|MOF|SPECS|EPR)$/', (string) $value));
  }

  public static function is_grade($value)
  {
    return (bool) (self::is_char($value) AND preg_match('/^[ABC]$/', (string) $value));
  }

  public static function is_barcode($value)
  {
    return (bool) (self::is_varchar($value) AND preg_match('/^[0-9A-Z]{8}(-[0-9A-Z]{4})?$/', (string) $value));
  }

  public static function is_barcode_type($value)
  {
    return (bool) (self::is_char($value) AND preg_match('/^[PTFSLR]$/', (string) $value));
  }

  public static function is_conversion_factor($value)
  {
    return (bool) (self::is_float($value) AND ($value > 0) AND ($value < 1));
  }

  public static function is_block_coordinates($value)
  {
    return (bool) (self::is_varchar($value, 7) AND preg_match('/^[A-Z]{1,4}[0-9]{1,3}$/', (string) $value));
  }

  public static function is_status($value)
  {
    return (bool) (self::is_char($value) AND preg_match('/^[PAR]$/', (string) $value));
  }

  public static function is_coc_status($value)
  {
    return (bool) (self::is_char($value) AND preg_match('/^[PESLAHN]$/', (string) $value));
  }

}