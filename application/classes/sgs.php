<?php

class SGS {

  const DATE_FORMAT = 'j F Y';
  const DATETIME_FORMAT = 'j F Y H:i';

  const PGSQL_DATE_FORMAT = 'Y-m-d';
  const PGSQL_DATETIME_FORMAT = 'Y-m-d H:i:s';

  public static $path = array(
    'index'           => 'Home',

    'import'          => 'Import',
    'import/upload'   => 'Upload Documents',
    'import/files'    => 'File Management',
    'import/data'     => 'Data Management',

    'export'          => 'Export',
    'export/download' => 'Download Documents',
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

  public static function date($date, $pgsql = FALSE)
  {
    try {
      return Date::formatted_time($date, $pgsql ? self::PGSQL_DATE_FORMAT : self::DATE_FORMAT);
    } catch (Exception $e) {
      return date($pgsql ? self::PGSQL_DATE_FORMAT : self::DATE_FORMAT, strtotime($date));
    }
  }

  public static function datetime($datetime, $pgsql = FALSE)
  {
    try {
      return Date::formatted_time($datetime, $pgsql ? self::PGSQL_DATETIME_FORMAT : self::DATETIME_FORMAT);
    } catch (Exception $e) {
      return date($pgsql ? self::PGSQL_DATETIME_FORMAT : self::DATETIME_FORMAT, strtotime($datetime));
    }
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
      ->where('tin', '=', $tin)
      ->execute()
      ->get('id');

    return $returning_id ? $id : ORM::factory('operator', $id);
  }

  public static function lookup_site($type, $reference, $returning_id = FALSE)
  {
    $id = DB::select('id')
      ->from('sites')
      ->where('type', '=', $type)
      ->and_where('reference', '=', $reference)
      ->execute()
      ->get('id');

    return $returning_id ? $id : ORM::factory('site', $id);
  }

  public static function lookup_site_by_name($name, $returning_id = FALSE)
  {
    $id = DB::select('id')
      ->from('sites')
      ->where('name', '=', $name)
      ->execute()
      ->get('id');

    return $returning_id ? $id : ORM::factory('site', $id);
  }

  public static function lookup_block($site_type, $site_reference, $coordinates, $returning_id = FALSE)
  {
    $id = DB::select(array('blocks.id', 'block_id'))
      ->from('sites')
      ->join('blocks')
      ->on('blocks.site_id', '=', 'sites.id')
      ->where('sites.type', '=', $site_type)
      ->and_where('sites.reference', '=', $site_reference)
      ->and_where('blocks.coordinates', '=', $coordinates)
      ->execute()
      ->get('block_id');

    return $returning_id ? $id : ORM::factory('block', $id);
  }

  public static function lookup_block_by_site_name($site_name, $coordinates, $returning_id = FALSE)
  {
    $id = DB::select(array('blocks.id', 'block_id'))
      ->from('sites')
      ->join('blocks')
      ->on('blocks.site_id', '=', 'sites.id')
      ->where('sites.name', '=', $site_name)
      ->and_where('blocks.coordinates', '=', $coordinates)
      ->execute()
      ->get('block_id');

    return $returning_id ? $id : ORM::factory('block', $id);
  }

  public static function lookup_barcode($barcode, $returning_id = FALSE)
  {
    $id = DB::select('id')
      ->from('barcodes')
      ->where('barcode', '=', $barcode)
      ->execute()
      ->get('id');

    return $returning_id ? $id : ORM::factory('barcode', $id);
  }

  public static function lookup_printjob($number, $returning_id = FALSE)
  {
    $id = DB::select('id')
      ->from('printjobs')
      ->where('number', '=', $number)
      ->execute()
      ->get('id');

    return $returning_id ? $id : ORM::factory('printjob', $id);
  }

  public static function lookup_species($code, $returning_id = FALSE)
  {
    $id = DB::select('id')
      ->from('species')
      ->where('code', '=', $code)
      ->execute()
      ->get('id');

    return $returning_id ? $id : ORM::factory('species', $id);
  }

  public static function suggest($search, $table, $model, $match, $fields, $args, $query_args, $return, $match_exact, $min_length, $limit, $offset)
  {
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
            $query = call_user_func_array(array('DB', 'select'), array_merge(array(array($table.'.id', 'id')), (array) $fields))
              ->from($table)
              ->where($match, 'LIKE', '%'.strtoupper($str).'%');
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

  public static function parse_site_and_block_info($text) {
    $matches = array();
    preg_match('/((([A-Z]+)\/)?([A-Z1-9\s-_]+)?)(\/([A-Z1-9]+))?/', $text, $matches);

    return array(
      'site_name'         => $matches[1],
      'site_type'         => $matches[3],
      'site_reference'    => $matches[4],
      'block_coordinates' => $matches[6]
    );
  }

  public static function odd_even(&$odd) {
    return ($odd ? ($odd = false) : ($odd = true)) ? 'odd' : 'even';
  }

  public static function wordify($string)
  {
    return preg_replace('/[^\w-]+/', '_', $string);
  }

  public static function render_classes($classes)
  {
    return implode(' ', (array) self::wordify($classes));
  }

  public static function render_styles($styles) {
    foreach ((array) $styles as $style) {
      $return .= HTML::style('css/'.$style.'.css');
    }
    return $return;
  }

}