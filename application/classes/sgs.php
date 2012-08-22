<?php

class SGS {

  const DATE_FORMAT = 'j F Y';
  const DATETIME_FORMAT = 'j F Y H:i';

  const PGSQL_DATE_FORMAT = 'Y-m-d';
  const PGSQL_DATETIME_FORMAT = 'Y-m-d H:i:s';

  const US_DATE_FORMAT = 'm/d/Y';

  public static $path = array(
    'index'           => 'Home',

    'import'          => 'Import',
    'import/upload'   => 'Upload Document',
    'import/files'    => 'File Management',
    'import/data'     => 'Data Management',

    'export'          => 'Export',
    'export/download' => 'Download Document',
    'export/files'    => 'File Management',
    'export/data'     => 'Data Management',

    'export/download/ssf' => 'Stock Survey Form',
    'export/download/tdf' => 'Tree Data Form',
    'export/download/ldf' => 'Log Data Form',

    'admin'           => 'Administration',
    'admin/files'     => 'File Management',
    'admin/operators' => 'Operator Configuration',
    'admin/sites'     => 'Site Configuration',
    'admin/blocks'    => 'Block Registration',
    'admin/species'   => 'Species Configuration',
    'admin/printjobs' => 'Print Job Management',
    'admin/barcodes'  => 'Barcode Management',

    'analysis'        => 'Analysis, Checks, and Queries',
    'analysis/ssf'    => 'Stock Survey Form',
    'analysis/tdf'    => 'Tree Data Form',
    'analysis/ldf'    => 'Log Data Form',

    'reports'         => 'Reports',
    'reports/ssf'     => 'Stock Survey Form',
    'reports/tdf'     => 'Tree Data Form',
    'reports/ldf'     => 'Log Data Form',
  );

  public static $errors = array(
    // default messages
    'alpha'         => '":field" must contain only letters',
    'alpha_dash'    => '":field" must contain only numbers, letters and dashes',
    'alpha_numeric' => '":field" must contain only letters and numbers',
    'color'         => '":field" must be a color',
    'credit_card'   => '":field" must be a credit card number',
    'date'          => '":field" must be a date',
    'decimal'       => '":field" must be a decimal with :param2 places',
    'digit'         => '":field" must be a digit',
    'email'         => '":field" must be a email address',
    'email_domain'  => '":field" must contain a valid email domain',
    'equals'        => '":field" must equal ":param2"',
    'exact_length'  => '":field" must be exactly :param2 characters long',
    'in_array'      => '":field" must be one of the available options',
    'ip'            => '":field" must be an IP address',
    'matches'       => '":field" must be the same as ":param2"',
    'min_length'    => '":field" must be at least :param2 characters long',
    'max_length'    => '":field" must not exceed :param2 characters long',
    'not_empty'     => '":field" must not be empty',
    'numeric'       => '":field" must be numeric',
    'phone'         => '":field" must be a phone number',
    'range'         => '":field" must be within the range of :param2 to :param3',
    'regex'         => '":field" does not match the required format',
    'url'           => '":field" must be a URL',

    // additional messages
    'is_unique'            => '":field" must be unique among :param1 data, possible duplicate found',
    'is_existing_barcode'  => '":field" must be an existing barcode',
    'is_existing_operator' => '":field" does not reference an existing operator',
    'is_existing_site'     => '":field" does not reference and existing site',
    'is_existing_block'    => '":field" does not reference an existing block',
    'is_existing_species'  => '":field" does not reference an existing species',
    'is_int'               => '":field" must be a number with no decimal places (for example, "1985")',
    'is_float'             => '":field" must be a number (for example, "19.85")',
    'is_char'              => '":field" must be exactly one letter (for example, "A")',
    'is_varchar'           => '":field" must contain only numbers, letters, and valid characters',
    'is_text_short'        => '":field" must be of short-length',
    'is_text_medium'       => '":field" must be of medium-length',
    'is_text_long'         => '":field" too long',
    'is_boolean'           => '":field" must be an affirmative (for example, "YES" or "NO")',
    'is_money'             => '":field" must be a valid monetary amount in USD with no dollar sign (for example, "10.12")',
    'is_date'              => '":field" must be a valid date (for example, "2012-09-27")',
    'is_timestamp'         => '":field" must be a valid date and time (for example, "2012-09-27 22:30")',
    'is_positive_int'      => '":field" must be a positive number with no decimal places',
    'is_measurement_int'   => '":field" must be positive number with no decimal places',
    'is_measurement_float' => '":field" must be a positive number',
    'is_file_type'         => '":field" must be a file mime type (for example, "text/css")',
    'is_species_code'      => '":field" does not match the required species code format',
    'is_species_class'     => '":field" must be a species class (for example, "A", "B" or "C")',
    'is_site_name'         => '":field" does not match the required site format (for example, "ABC123" or "ABC 123")',
    'is_operator_tin'      => '":field" does not match the required operator TIN format',
    'is_survey_line'       => '":field" must be a number from 1 to 20',
    'is_operation'         => '":field" must be either (I)mport or (E)xport',
    'is_operation_type'    => '":field" must be a type of form or print job (for example, "SSF")',
    'is_form_type'         => '":field" must not be a type of form (for example, "SSF")',
    'is_grade'             => '":field" must not be a grade (for example, "A", "B" or "C")',
    'is_barcode'           => '":field" does not match the required barcode format',
    'is_barcode_type'      => '":field" must be a barcode type (for example, "Pending")',
    'is_conversion_factor' => '":field" must be a fraction or decimal number between 0 and 1',
    'is_block_name'        => '":field" does not match the required block name format',
    'is_status'            => '":field" must be either (P)ending, (A)ctive or (R)ejected',
    'is_coc_status'        => '":field" must be a CoC status (for example, "Pending")',
  );

  public static $operation = array(
    'I' => 'Import',
    'E' => 'Export',
    'A' => 'Administration',
    'U' => 'Unknown'
  );

  public static $operation_type = array(
    'SSF'   => 'Stock Survey Form',
    'TDF'   => 'Tree Data Form',
    'LDF'   => 'Log Data Form',
    'MIF'   => 'Mill Input Form',
    'MOF'   => 'Mill Output Form',
    'SPECS' => 'Shipping Specification Form',
    'EPR'   => 'Export Permit Request Form',
    'PJ'    => 'Print Job',
    'UNKWN' => 'Unknown'
  );

  public static $species_code = array(
    'A' => 'A',
    'B' => 'B',
    'C' => 'C'
  );

  public static $grade = array(
    'A' => 'A',
    'B' => 'B',
    'C' => 'C'
  );

  public static $form_type = array(
    'SSF'   => 'Stock Survey Form',
    'TDF'   => 'Tree Data Form',
    'LDF'   => 'Log Data Form',
    'MIF'   => 'Mill Input Form',
    'MOF'   => 'Mill Output Form',
    'SPECS' => 'Shipping Specification Form',
    'EPR'   => 'Export Permit Request Form'
  );

  public static $barcode_type = array(
    'P' => 'Pending',
    'T' => 'Standing Tree',
    'F' => 'Felled Tree',
    'S' => 'Stump',
    'L' => 'Log',
    'R' => 'Sawnmill Timber'
  );

  public static $status = array(
    'P' => 'Pending',
    'A' => 'Accpeted',
    'R' => 'Rejected'
  );

  public static $coc_status = array(
    'P' => 'Pending',
    'I' => 'In Progress',
    'H' => 'On Hold',
    'E' => 'Exported',
    'S' => 'Short-Shipped',
    'Y' => 'Sold Locally',
    'A' => 'Abandoned',
    'L' => 'Lost',
    'Z' => 'Seized'
  );

  public static $access_level = array(
    'A' => 'Administrator',
    'M' => 'Manager',
    'D' => 'Data entry clerk'
  );

  public static function value($key, $array, $default = NULL)
  {
    if (is_string($array)) $array = self::$$array;
    $keys = array_keys($array);

    if (in_array($key, $keys)) return $array[$key];
    else if (in_array($default, $keys)) return $array[$default];
    else return $default;
  }

  public static function path($path)
  {
    return self::value(preg_replace ('`^'.preg_quote(URL::base()).'`', '', $path), 'path');
  }

  public static function title($path)
  {
    while (($title == NULL) and ($path)) {
      $title = self::path($path);
      $path  = substr($path, 0, strrpos($path, '/'));
    }

    return $title ? $title : 'Home';
  }

  public static function fix_date($text)
  {
    if ($text !== $fix = preg_replace('/^(\d{1,2})-(\d{1,2})-(\d{2,4})$/', '$2-$1-$3', trim($text))) return $fix;
  }

  public static function date($date, $format = SGS::DATE_FORMAT, $fix = TRUE)
  {
    try {
      $d = Date::formatted_time($date, $format);
    } catch (Exception $e) {
      $d = date($format, strtotime($date));
    }

    if (Valid::is_date($d)) return $d;
    elseif ($fix) return self::date(self::fix_date($date), $pgsql, FALSE);
    else return $d;
  }

  public static function datetime($datetime, $format = SGS::DATETIME_FORMAT, $fix = TRUE)
  {
    try {
      $dt = Date::formatted_time($datetime, $format);
    } catch (Exception $e) {
      $dt = date($format, strtotime($datetime));
    }

    if ($dt) return $dt;
    elseif ($fix) self::date(self::fix_date($dt), $pgsql, FALSE);
  }

  public static function db_range($from = NULL, $to = NULL)
  {
    if ($from) $from = self::datetime($from, TRUE);
    else $from = '-infinity';

    if ($to) $to = self::datetime($to, TRUE);
    else $to = 'infinity';

    return array(
      $from,
      $to,
      'from' => $from,
      'to' => $to
    );
  }

  public static function lookup_operator($tin, $returning_id = FALSE)
  {
    $id = DB::select('id')
      ->from('operators')
      ->where('tin', '=', (int) $tin)
      ->execute()
      ->get('id');

    return $returning_id ? $id : ORM::factory('operator', $id);
  }

  public static function lookup_site($name, $returning_id = FALSE)
  {
    $id = DB::select('id')
      ->from('sites')
      ->where('name', '=', (string) $name)
      ->execute()
      ->get('id');

    return $returning_id ? $id : ORM::factory('site', $id);
  }

  public static function lookup_block($site_name, $name, $returning_id = FALSE)
  {
    $id = DB::select(array('blocks.id', 'block_id'))
      ->from('sites')
      ->join('blocks')
      ->on('blocks.site_id', '=', 'sites.id')
      ->where('sites.name', '=', (string) $site_name)
      ->and_where('blocks.name', '=', (string) $name)
      ->execute()
      ->get('block_id');

    return $returning_id ? $id : ORM::factory('block', $id);
  }

  public static function lookup_barcode($barcode, $returning_id = FALSE)
  {
    $id = DB::select('id')
      ->from('barcodes')
      ->where('barcode', '=', (string) $barcode)
      ->execute()
      ->get('id');

    return $returning_id ? $id : ORM::factory('barcode', $id);
  }

  public static function lookup_printjob($number, $returning_id = FALSE)
  {
    $id = DB::select('id')
      ->from('printjobs')
      ->where('number', '=', (int) $number)
      ->execute()
      ->get('id');

    return $returning_id ? $id : ORM::factory('printjob', $id);
  }

  public static function lookup_species($code, $returning_id = FALSE)
  {
    $id = DB::select('id')
      ->from('species')
      ->where('code', '=', (string) $code)
      ->execute()
      ->get('id');

    return $returning_id ? $id : ORM::factory('species', $id);
  }

  public static function suggest($search, $table, $model, $match, $fields, $args, $query_args, $return, $match_exact, $min_length, $limit, $offset)
  {
    try {
      DB::query(Database::SELECT, "SELECT similarity('a'::text, 'a'::text)");
      $similiarty = TRUE;
    } catch (Exception $e) {
      $similarity = FALSE;
    }

    $objects  = array();
    $ids      = array();
    $fetches  = array();
    $count    = 0;
    $grab     = 0;
    $max_grab = strlen($search);

    while ($grab < $max_grab) {
      $left = 0;
      $right = $grab;

      while ($left <= $grab) {
        $strs   = array();
        $strs[] = $right
               ? substr($search, $left, "-$right")
               : substr($search, $left);
        $strs[] = strrev(reset($strs));

        foreach ($strs as $str) {
          if (strlen($str) >= $min_length) {
            if ($similarity) {
              $query = DB::select('id')
                ->from($table)
                ->offset($offset)
                ->limit($limit)
                ->order_by(DB::expr("similarity($match::text, '$search'::text)"));
            }
            else {
              $query = call_user_func_array(array('DB', 'select'), array_merge(array(array($table.'.id', 'id')), (array) $fields))
                ->from($table)
                ->where($match, 'LIKE', '%'.strtoupper($str).'%');
            }

            foreach ((array) $query_args as $_query_args) {
              foreach ($_query_args as $key => $value) call_user_func_array(array($query, $key), $value);
            }

            foreach (array_filter((array) $args) as $key => $value) if ($value) $query->and_where($key, 'IN', (array) $value);
            if ($ids) $query->and_where($table.'.id', 'NOT IN', $ids);

            foreach ($query->execute() as $result) {
              $count++;
              $ids[] = $result['id'];
              if ($offset < $count && $return) $fetches[$count] = $return ? $result[$return] : $result['id'];
              if ($match_exact && $search == $result[is_array($match) ? reset($match) : $match]) break 4;
              if ($limit && ($count == $limit)) break 4;
              if ($similarity) break 4;
            }
          }
        }

        $left++;
        $right--;
      }

      $grab++;
    }

    if ($return) return $fetches;
    else {
      foreach ($ids as $id) $objects[] = ORM::factory($model, $id);
      return $objects;
    }
  }

  public static function suggest_barcode($barcode, $args = array('type' => 'P'), $return = FALSE, $match_exact = TRUE, $min_length = 2, $limit = 10, $offset = 0)
  {
    $table  = 'barcodes';
    $model  = 'barcode';
    $match  = 'barcode';
    $fields = 'barcode';

    $query_args = array();
    if ($args['sites.id']) {
      $query_args[] = array('join' => array('printjobs'));
      $query_args[] = array('on' => array('barcodes.printjob_id', '=', 'printjobs.id'));
      $query_args[] = array('join' => array('sites'));
      $query_args[] = array('on' => array('printjobs.site_id', '=', 'sites.id'));
    }
    if ($args['operators.id']) {
      if ( ! $args['sites.id']) {
        $query_args[] = array('join' => array('printjobs'));
        $query_args[] = array('on' => array('barcodes.printjob_id', '=', 'printjobs.id'));
        $query_args[] = array('join' => array('sites'));
        $query_args[] = array('on' => array('printjobs.site_id', '=', 'sites.id'));
      }
      $query_args[] = array('join' => array('operators'));
      $query_args[] = array('on' => array('sites.operator_id', '=', 'operators.id'));
    }
    return self::suggest($barcode, $table, $model, $match, $fields, $args, $query_args, $return, $match_exact, $min_length, $limit, $offset);
  }

  public static function suggest_operator($tin, $args = array(), $return = FALSE, $match_exact = TRUE, $min_length = 5, $limit = 10, $offset = 0)
  {
    $table  = 'operators';
    $model  = 'operator';
    $match  = array(
      '__value' => 'tin',
      '__cast'  => 'text'
    );
    $fields = array(
      'tin'
    );

    $query_args = array();
    if ($args['sites.id']) {
      $query_args[] = array('join' => array('sites'));
      $query_args[] = array('on' => array('operators.id', '=', 'sites.operator_id'));
    }
    return self::suggest($tin, $table, $model, $match, $fields, $args, $query_args, $return, $match_exact, $min_length, $limit, $offset);
  }

  public static function suggest_site($name, $args = array(), $return = FALSE, $match_exact = TRUE, $min_length = 5, $limit = 10, $offset = 0)
  {
    $table  = 'sites';
    $model  = 'site';
    $match  = 'sites.name';
    $fields = array(
      'sites.name'
    );
    $strlen     = strlen($name);
    $min_length = ($strlen > 10) ? $strlen - 10 : $strlen;

    $query_args = array();
    if ($args['operators.id']) {
      $query_args[] = array('join' => array('operators'));
      $query_args[] = array('on' => array('sites.operator_id', '=', 'operators.id'));
    }
    return self::suggest($name, $table, $model, $match, $fields, $args, $query_args, $return, $match_exact, $min_length, $limit, $offset);
  }

  public static function suggest_species($code, $args = array(), $return = FALSE, $match_exact = TRUE, $min_length = 2, $limit = 10, $offset = 0)
  {
    $table  = 'species';
    $model  = 'species';
    $match  = 'code';
    $fields = array(
      'code'
    );

    $query_args = array();
    return self::suggest($code, $table, $model, $match, $fields, $args, $query_args, $return, $match_exact, $min_length, $limit, $offset);
  }

  public static function decode_error($field, $error)
  {
    return self::$errors[$error];
  }

  public static function parse_site_and_block_info($text)
  {
    $matches = array();
    preg_match('/((([A-Z]+)\/)?([A-Z1-9\s-_]+)?)(\/([A-Z1-9]+))?/', $text, $matches);

    return array(
      'site_name'  => $matches[4],
      'block_name' => $matches[6]
    );
  }

  public static function odd_even(&$odd) {
    return ($odd ? ($odd = false) : ($odd = true)) ? 'odd' : 'even';
  }

  public static function wordify($string)
  {
    return preg_replace('/[^\w-]+/', '_', $string);
  }

  public static function errorfy($string)
  {
    $string = preg_replace('/('.preg_quote(implode('|', array_keys(self::$form_type))).'_data)/', 'form', $string);
    $string = preg_replace('/\b(tin)\b/', 'TIN', $string);
    $string = str_replace('_id', '', $string);
    $string = str_replace('_', ' ', $string);
    $string = preg_replace('/\b(\w)/e', 'strtoupper("$1")', $string, 1);
    return $string;
  }

  public static function render_classes($classes)
  {
    return implode(' ', self::wordify(array_filter((array) $classes)));
  }

  public static function render_styles($styles) {
    foreach ((array) $styles as $style) {
      $return .= HTML::style('css/'.$style.'.css');
    }
    return $return;
  }

  public static function render_scripts($scripts) {
    foreach ((array) $scripts as $script) {
      $return .= HTML::script('js/'.$script.'.js');
    }
    return $return;
  }

}