<?php

class SGS {

  const PI = 3.1426;
  
  const NEVERTHELESS   = 'tHe k1nG s8iD tHe7 p3oPlE w1lL hAv3 t#e v0iCe aNd We Of 2aMu$l ReFuZeD b^t To Ob3y A n@y oVeR uZ';
  const NEVER_THE_LESS = 'the king said they people will have the voice and we of samuel refused but to obey a nay over us';
  const NEVER_THELESS  = 'the k1nG said tHe7 people w1lL have t#e voice aNd we Of samuel ReFuZeD but To obey A nay oVeR us';

  const UTM_ZONE_DEFAULT = '29N';

  const DATE_FORMAT = 'j M Y';
  const DATETIME_FORMAT = 'j M Y g:i A';
  const PRETTY_DATE_FORMAT = 'F j, Y';
  const CERTIFICATE_DATE_FORMAT = 'd.m.Y';
  const EPOCH_DATE_FORMAT = 'U';

  const PGSQL_DATE_FORMAT = 'Y-m-d';
  const PGSQL_DATETIME_FORMAT = 'Y-m-d H:i:s';

  const US_DATE_FORMAT = 'n/j/Y';
  const US_DATE_CORRECT_FORMAT = 'm/d/Y';

  const PGSQL_TRUE  = 't';
  const PGSQL_FALSE = 'f';

  const FEE_SGS_RATE = 0.348;
  const FEE_GOL_RATE = 0.652;

  const FEE_SGS_CONTRACT_RATE = 0.014;

  const INSPECTION_RATE = 0.05;
  
  const TAG_PRICE = 0.10;

  const CONDITION_MATCHING = 'is_matching';
  const CONDITION_EXISTING = 'is_existing';
  const CONDITION_VALID = 'is_valid';

  const PENDING_BARCODE = 'P';
  const STANDING_TREE_BARCODE = 'T';
  const FELLED_TREE_BARCODE = 'F';
  const STUMP_BARCODE = 'S';
  const LOG_BARCODE = 'L';
  const SAWNMILL_TIMBER_BARCODE = 'R';
  const SHIPMENT_SPECIFICATION_BARCODE = 'H';
  const EXPORT_PERMIT_BARCODE = 'E';

  public static $path = array(
    'index'           => 'Home',

    'login'           => 'Login',
    'logout'          => 'Logout',

    'documents'       => 'Documents',

    'declaration'          => 'Declaration',
    'declaration/upload'   => 'Upload Files',
    'declaration/files'    => 'Manage Files',
    'declaration/search'   => 'Search Data',
    'declaration/data'     => 'Manage Data',

    'declaration/data/ssf'   => 'Stock Survey Form',
    'declaration/data/tdf'   => 'Tree Data Form',
    'declaration/data/ldf'   => 'Log Data Form',
    'declaration/data/specs' => 'Shipment Specification Form',

    'verification'          => 'Verification',
    'verification/upload'   => 'Upload Files',
    'verification/files'    => 'Manage Files',
    'verification/search'   => 'Search Data',
    'verification/data'     => 'Manage Data',

    'verification/data/ssfv'   => 'Stock Survey Verification Form',
    'verification/data/tdfv'   => 'Tree Data Verification Form',
    'verification/data/ldfv'   => 'Log Data Verification Form',
//    'verification/data/specsv' => 'Shipment Specification Verification Form',

    'config'            => 'Configuration',
//    'config/files'      => 'File Management',
    'config/operators'  => 'Operator Registration',
    'config/buyers'     => 'Buyer Registration',
    'config/sites'      => 'Site Registration',
    'config/blocks'     => 'Block Registration',
    'config/species'    => 'Species Configuration',

    'settings'            => 'Settings',
    'settings/tolerances' => 'Accuracy and Tolerance',

    'manage'                    => 'Management',
    'manage/printjobs'          => 'Print Job Management',
    'manage/printjobs/list'     => 'List Print Jobs',
    'manage/printjobs/upload'   => 'Upload Print Jobs',
    'manage/printjobs/download' => 'Download Print Jobs',

    'manage/barcodes'       => 'Barcode Management',
    'manage/barcodes/query' => 'Query Barcodes',
    'manage/barcodes/list'  => 'List Barcodes',

    'users'           => 'Users',
    'users/list'      => 'List Users',

    'analysis'              => 'Analysis',

    'analysis/review'       => 'Review Data',
    'analysis/review/ssf'   => 'Stock Survey',
    'analysis/review/tdf'   => 'Tree Data',
    'analysis/review/ldf'   => 'Log Data',
    'analysis/review/specs' => 'Shipment Specification',

    'analysis/checks'       => 'Check Data',
    'analysis/checks/ssf'   => 'Stock Survey',
    'analysis/checks/tdf'   => 'Tree Data',
    'analysis/checks/ldf'   => 'Log Data',
    'analysis/checks/specs' => 'Shipment Specification',

    'analysis/verify'       => 'Verify Data',
    'analysis/verify/ssfv'  => 'Stock Survey',
    'analysis/verify/tdfv'  => 'Tree Data',
    'analysis/verify/ldfv'  => 'Log Data',

    'analysis/download'       => 'Download Data',
    'analysis/download/ssf'   => 'Stock Survey',
    'analysis/download/tdf'   => 'Tree Data',
    'analysis/download/ldf'   => 'Log Data',
    'analysis/download/specs' => 'Shipment Specification',

    'invoices'            => 'Invoices',
    'invoices/list'       => 'List Invoices',
    'invoices/create'     => 'Create Invoice',
    'invoices/create/st'  => 'Stumpage Invoice',
    'invoices/create/exf' => 'Export Fee Invoice',
    'invoices/create/tag' => 'Barcode Tag Invoice',

    'exports'                    => 'Exports',
    'exports/documents'          => 'List Documents',
    'exports/documents/validate' => 'Validate Documents',
    'exports/documents/create'   => 'Create Document',

    'exports/documents/create/specs' => 'Shipment Specification',
    'exports/documents/create/exp'   => 'Export Permit',
    'exports/documents/create/cert'  => 'Certificate of Origin',

    'reports'         => 'Reports',
    'reports/list'    => 'List Reports',
    'reports/create'  => 'Create Report',

    'reports/create/csv'     => 'Declaration and Verification Report',
    'reports/create/data'    => 'Data Analysis Report',
    'reports/create/summary' => 'Summary Report',
  );

  public static $tips = array(
    'all' => array(
      'is_unique' => 'Certain fields must be unique in order to maintain a consistent system
                      and prevent duplicates from entering the tracability and monitoring
                      system. If you are reviewing this error, please check if the record
                      is flagged as a duplicate, and if so, resolve this error by choosing
                      which of the marked duplicate is the correct data. if the record is not
                      flagged as a duplicate, edit the field in question to prevent it from
                      conflicting with an existing record.',
      'not_empty' => 'Certain fields are required and cannot be left empty. If you are reviewing
                      is error, make sure that the field is not empty. In some rare situations,
                      the field may not be empty, but the "resolved" value may be. For example,
                      entering "ABC" into a barcode field may result in an empty value since
                      the format is invalid and the "resolved" barcode cannot be found.',
    ),
    'barcode_id' => array(
      'not_empty' => 'In most situations, when this error occurs with a barcode field, it
                      means that the barcode has not been registered. You can register a
                      barcode by either either creating a print job or uploading an existing
                      print job text file containing the barcode. In some rare situations,
                      the barcode field may actually be empty.'
    ),
    'tree_barcode_id' => array(
      'not_empty' => 'In most situations, when this error occurs with a barcode field, it
                      means that the barcode has not been registered. You can register a
                      barcode by either either creating a print job or uploading an existing
                      print job text file containing the barcode. In some rare situations,
                      the barcode field may actually be empty.'
    ),
    'stump_barcode_id' => array(
      'not_empty' => 'In most situations, when this error occurs with a barcode field, it
                      means that the barcode has not been registered. You can register a
                      barcode by either either creating a print job or uploading an existing
                      print job text file containing the barcode. In some rare situations,
                      the barcode field may actually be empty.'
    ),
    'parent_barcode_id' => array(
      'not_empty' => 'In most situations, when this error occurs with a barcode field, it
                      means that the barcode has not been registered. You can register a
                      barcode by either either creating a print job or uploading an existing
                      print job text file containing the barcode. In some rare situations,
                      the barcode field may actually be empty.'
    ),
    'species_id' => array(
      'not_empty' => 'In most situations, when this error occurs with a species field, it
                      means that no species exists for a species code. In some rare situations,
                      the species field may actually be empty.'
    ),
  );

  public static $errors = array(
    // specific fields
    'content_md5' => array(
      'is_unique' => 'Identical file already uploaded, duplicate found'
    ),

    // all fields
    'all' => array(

      // default messages
      'alpha'         => ':field must contain only letters',
      'alpha_dash'    => ':field must contain only numbers, letters and dashes',
      'alpha_numeric' => ':field must contain only letters and numbers',
      'color'         => ':field must be a color',
      'credit_card'   => ':field must be a credit card number',
      'date'          => ':field must be a date',
      'decimal'       => ':field must be a decimal with :param2 places',
      'digit'         => ':field must be a digit',
      'email'         => ':field must be a email address',
      'email_domain'  => ':field must contain a valid email domain',
      'equals'        => ':field must equal ":param2"',
      'exact_length'  => ':field must be exactly :param2 characters long',
      'in_array'      => ':field must be one of the available options',
      'ip'            => ':field must be an IP address',
      'matches'       => ':field must be the same as ":param2"',
      'min_length'    => ':field must be at least :param2 characters long',
      'max_length'    => ':field must not exceed :param2 characters long',
      'not_empty'     => ':field must not be empty',
      'numeric'       => ':field must be numeric',
      'phone'         => ':field must be a phone number',
      'range'         => ':field must be within the range of :param2 to :param3',
      'regex'         => ':field does not match the required format',
      'url'           => ':field must be a URL',

      // additional messages
      'is_unique'            => ':field must be unique',
      'is_existing_barcode'  => ':field must be an existing barcode',
      'is_existing_operator' => ':field must reference an existing operator',
      'is_existing_site'     => ':field must reference and existing site',
      'is_existing_block'    => ':field must reference an existing block',
      'is_existing_species'  => ':field must reference an existing species',
      'is_int'               => ':field must be a number with no decimal places (for example, "1985")',
      'is_float'             => ':field must be a number (for example, "19.85")',
      'is_char'              => ':field must be exactly one letter (for example, "A")',
      'is_varchar'           => ':field must contain only numbers, letters, and valid characters',
      'is_text_short'        => ':field must be of short-length',
      'is_text_medium'       => ':field must be of medium-length',
      'is_text_long'         => ':field must be of long-length',
      'is_boolean'           => ':field must be an affirmative (for example, "YES" or "NO")',
      'is_money'             => ':field must be a valid monetary amount in USD with no dollar sign (for example, "10.12")',
      'is_date'              => ':field must be a valid date (for example, "2012-09-27")',
      'is_timestamp'         => ':field must be a valid date and time (for example, "2012-09-27 22:30")',
      'is_positive_int'      => ':field must be a positive number with no decimal places',
      'is_measurement_int'   => ':field must be positive number with no decimal places',
      'is_measurement_float' => ':field must be a positive number',
      'is_file_type'         => ':field must be a file mime type (for example, "text/css")',
      'is_species_code'      => ':field must match the required species code format',
      'is_species_class'     => ':field must be a correct species class (for example, "A", "B" or "C")',
      'is_site_name'         => ':field must match the required site format (for example, "ABC123" or "ABC 123")',
      'is_operator_tin'      => ':field must match the required operator TIN format',
      'is_survey_line'       => ':field must be a number from 1 to 20',
      'is_operation'         => ':field must be either (U)pload or (D)ownload',
      'is_operation_type'    => ':field must be a correct type of form or print job (for example, "PJ")',
      'is_form_type'         => ':field must not be a correct type of form (for example, "SSF")',
      'is_grade'             => ':field must not be a correct grade (for example, "A", "B" or "C")',
      'is_barcode'           => ':field must match the required barcode format',
      'is_barcode_type'      => ':field must be a correct barcode type to match the data',
      'is_conversion_factor' => ':field must be a fraction or decimal number between 0 and 1',
      'is_block_name'        => ':field must match the required block name format',
      'is_status'            => ':field must be a correct status (for example, "Accepted")',
      'is_barcode_activity'  => ':field must be a CoC status or activity (for example, "Exported")',
    )
  );

  public static $roles = array(
    'login'      => 'Login',
    'data'       => 'Data Entry',
    'analysis'   => 'Data Analysis',
    'reports'    => 'Reporting',
//    'management' => 'Project Management',
    'admin'      => 'Site and Operator Configuration',
    'users'      => 'User Management',
    'barcodes'   => 'Barcode Management',
    'invoices'   => 'Invoice Management',
    'exports'    => 'Export Management',
    'tolerances' => 'Accuracy and Tolerance Management',
  );

  public static $file_type = array(
    'csv'  => 'CSV Document',
    'xls'  => 'Microsoft Excel Spreadsheet',
    'xlsx' => 'Microsoft Excel Spreadsheet',
    'txt'  => 'Plain Text Document',
    'pdf'  => 'Adobe PDF Document'
  );

  public static $operation = array(
    'U' => 'Upload',
    'E' => 'Download',
  );

  public static $operation_type = array(
    'SSF'    => 'Stock Survey',
    'SSFV'   => 'Stock Survey Verification',
    'TDF'    => 'Tree Data',
    'TDFV'   => 'Tree Data Verification',
    'LDF'    => 'Log Data',
    'LDFV'   => 'Log Data Verification',
    'MIF'    => 'Mill Input',
    'MIFV'   => 'Mill Input Verification',
    'MOF'    => 'Mill Output',
    'MOFV'   => 'Mill Output Verification',
    'SPECS'  => 'Shipping Specification',
//    'SPECSV' => 'Shipping Specification Verification',
    'WB'     => 'Waybill',
    'EXP'    => 'Export Permit',
    'CERT'   => 'Certificate of Origin',
    'CHECKS' => 'Checks and Queries Report',
    'INV'    => 'Invoice',
    'PJ'     => 'Print Job',
    'UNKWN'  => 'Unknown'
  );

  public static $species_code = array(
    'A' => 'A',
    'B' => 'B',
    'C' => 'C'
  );

  public static $grade = array(
    'FAS' => 'FAS',
    '1'   => '1',
    '2'   => '2',
    'CG'  => 'CG',
    'LM'  => 'LM',
    'A'   => 'A',
    'AB'  => 'AB',
    'B'   => 'B',
    'BC'  => 'BC',
    'C'   => 'C',
    'D'   => 'D'
  );

  public static $form_data_type = array(
    'SSF'   => 'Stock Survey Form',
    'TDF'   => 'Tree Data Form',
    'LDF'   => 'Log Data Form',
//    'MIF'   => 'Mill Input Form',
//    'MOF'   => 'Mill Output Form',
    'SPECS' => 'Shipping Specification Form',
    'WB'    => 'Waybill',
  );

  public static $form_verification_type = array(
    'SSFV'   => 'Stock Survey Verification Form',
    'TDFV'   => 'Tree Data Verification Form',
    'LDFV'   => 'Log Data Verification Form',
//    'MIFV'   => 'Mill Input Verification Form',
//    'MOFV'   => 'Mill Output Verification Form',
//    'SPECSV' => 'Shipping Specification Verification Form',
  );

  public static $barcode_type = array(
    'P' => 'Pending',
    'T' => 'Standing Tree',
    'F' => 'Felled Tree',
    'S' => 'Stump',
    'L' => 'Log',
    'R' => 'Sawnmill Timber',
    'H' => 'Shipment Specification',
    'E' => 'Export Permit',
    'W' => 'Waybill',
  );

  public static $barcode_activity = array(
    'P' => 'Pending',
    'I' => 'In Progress',
    'H' => 'On Hold / Pending Investigation',
    'T' => 'Stumpage Invoiced',
    'X' => 'Export Fee Invoiced',
    'D' => 'Declared for Export',
    'N' => 'Inspected',
    'E' => 'Exported',
    'O' => 'Loaded on Vessel',
    'S' => 'Short-Shipped',
    'Y' => 'Sold Locally',
    'A' => 'Abandoned',
    'L' => 'Lost',
    'Z' => 'Seized',
    'C' => 'Commented'
  );

  public static $barcode_locks = array(
    'ADMIN'  => 'Administrative',
    'INV'    => 'Invoice',
    'DOC'    => 'Document',
    'BRCODE' => 'Barcode',
    'VERIFY' => 'Verification'
  );

  public static $invoice_type = array(
    'ST'  => 'Stumpage Invoice',
    'EXF' => 'Export Fee Invoice',
    'TAG' => 'Barcode Tag Invoice'
  );

  public static $document_type = array(
    'SPECS' => 'Shipment Specification',
    'EXP'   => 'Export Permit',
    'CERT'  => 'Certificate of Origin'
  );

  public static $error_type = array(
    'S' => 'Success',
    'E' => 'Error',
    'W' => 'Warning'
  );

  public static $schedule_type = array(
    'O' => 'Once',
    'D' => 'Daily',
    'W' => 'Weekly',
    'M' => 'Monthly',
    'Q' => 'Quarterly',
    'Y' => 'Yearly'
  );

  public static $csv_status = array(
    'A' => 'Accepted',
    'R' => 'Rejected',
    'U' => 'Duplicated',
    'C' => 'Corrected',
//    'D' => 'Deleted',
    'P' => 'Pending',
  );

  public static $data_status = array(
    'A' => 'Passed',
    'R' => 'Failed',
    'P' => 'Unchecked',
//    'D' => 'Deleted',
  );

  public static $block_status = array(
    'A' => 'Approved',
    'R' => 'Rejected',
    'I' => 'Inspected',
    'P' => 'Pending',
  );

  public static $verification_status = array(
    'A' => 'Accurate',
    'R' => 'Inaccurate',
    'P' => 'Unverified',
//    'D' => 'Deleted',
  );

  public static $access_level = array(
    'A' => 'Administrator',
    'M' => 'Manager',
    'D' => 'Data entry clerk'
  );

  public static $species_fee_rate = array(
    'A' => 0.10,
    'B' => 0.05,
    'C' => 0.025
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

  public static function date($date = 'now', $format = SGS::DATE_FORMAT, $is_us_date = FALSE, $fix = TRUE)
  {
    if ($is_us_date AND ($test = self::internationalify($date))) $date = $test;

    try {
      $d = Date::formatted_time($date, $format);
    } catch (Exception $e) {
      $d = date($format, is_int($date) ? $date : strtotime($date));
    }

    if (Valid::is_date($d)) return $d;
    elseif ($fix) return self::date(self::fix_date($date), $format, FALSE, FALSE);
    else return $d;
  }

  public static function datetime($datetime = 'now', $format = SGS::DATETIME_FORMAT, $is_us_date = FALSE, $fix = TRUE)
  {
    if ($is_us_date AND ($test = self::internationalify($date))) $date = $test;

    try {
      $dt = Date::formatted_time($datetime, $format);
    } catch (Exception $e) {
      $dt = date($format, is_int($datetime) ? $datetime : strtotime($datetime));
    }

    if ($dt) return $dt;
    elseif ($fix) self::date(self::fix_date($dt), $format, FALSE, FALSE);
  }

  public static function db_range($from = NULL, $to = NULL, $is_us_date = FALSE, $fix = TRUE)
  {
    $from = $from ? self::datetime($from, self::PGSQL_DATETIME_FORMAT, $is_us_date, $fix) : '-infinity';
    $to = $to ? self::datetime($to, self::PGSQL_DATETIME_FORMAT, $is_us_date, $fix) : 'infinity';

    return array(
      $from,
      $to,
      'from' => $from,
      'to' => $to
    );
  }

  public static function variance_range($value, $variance)
  {
    return array(
      $value - $variance,
      $value + $variance
    );
  }

  public static function accuracy($form_type, $check)
  {
    return DB::select('accuracy_range')
      ->from('tolerances')
      ->where('form_type', '=', $form_type)
      ->and_where('check', '=', $check)
      ->execute()
      ->get('accuracy_range');
  }

  public static function tolerance($form_type, $check)
  {
    return DB::select('tolerance_range')
      ->from('tolerances')
      ->where('form_type', '=', $form_type)
      ->and_where('check', '=', $check)
      ->execute()
      ->get('tolerance_range');
  }

  public static function check($condition, $field)
  {
    return implode('_', array_filter(array($condition, $field)));
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

  public static function create_or_lookup_barcode($barcode, $type, $returning_id = FALSE)
  {
    if ($barcode = self::lookup_barcode($barcode, $type, $returning_id)) return $barcode;
    else if ($existing_barcode = self::lookup_barcode($barcode)) {

    }
  }

  public static function lookup_barcode($barcode, $type = array(), $returning_id = FALSE, $returning_multiple = FALSE)
  {
    $id = DB::select('id')
      ->from('barcodes')
      ->where('barcode', '=', (string) $barcode);
    if ($type) {
      $id->and_where('type', 'IN', (array) $type);
      if (is_array($type)) foreach ($type as $typ) $id->order_by(DB::expr("(type = '$typ')"), 'DESC');
    }
    $id = $id->execute()->as_array(NULL, 'id');

    if (count($id) == 1 or $returning_multiple == FALSE) return $returning_id ? reset($id) : ORM::factory('barcode', reset($id));
    else return $returning_id ? $id : ORM::factory('barcode')
      ->where('id', 'IN', $id)
      ->find_all();
  }

  public static function lookup_document($type, $number, $returning_id = FALSE)
  {
    $id = DB::select('id')
      ->from('documents')
      ->where('type', '=', $type)
      ->and_where('number', '=', (string) $number)
      ->execute()
      ->get('id');

    return $returning_id ? $id : ORM::factory('document', $id);
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

  public static function suggest($search, $table, $model, $match, $fields, $args, $query_args, $return, $match_exact, $min_length, $min_similarity, $max_distance, $limit, $offset)
  {
    try {
      DB::query(Database::SELECT, "SELECT similarity('a'::text, 'a'::text)")->execute();
      $similarity = TRUE;
    } catch (Exception $e) {
      $similarity = FALSE;
    }

    try {
      DB::query(Database::SELECT, "SELECT levenshtein('a'::text, 'a'::text)")->execute();
      $distance = TRUE;
    } catch (Exception $e) {
      $distance = FALSE;
    }

    $ids      = array();
    $objects  = array();
    $fetches  = array();
    $count    = 0;
    $grab     = 0;
    $max_grab = strlen($search);

    if (strlen($search) > $min_length) while ($grab < $max_grab) {
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
              ->from($table);
            if ($similarity) {
              $query = $query
                ->where(DB::expr("similarity(regexp_replace(upper($match::text), E'[^0-9A-Z]', '')::text, '".preg_replace('/[^0-9A-Z]/', '', strtoupper($search))."'::text)"), '>=', $min_similarity)
                ->order_by(DB::expr("similarity(regexp_replace(upper($match::text), E'[^0-9A-Z]', '')::text, '".preg_replace('/[^0-9A-Z]/', '', strtoupper($search))."'::text)"), 'DESC');
            }

            if ($distance) {
              $query = $query
                ->where(DB::expr("levenshtein(regexp_replace(upper($match::text), E'[^0-9A-Z]', '')::text, '".preg_replace('/[^0-9A-Z]/', '', strtoupper($search))."'::text)"), '<=', $max_distance)
                ->order_by(DB::expr("levenshtein(regexp_replace(upper($match::text), E'[^0-9A-Z]', '')::text, '".preg_replace('/[^0-9A-Z]/', '', strtoupper($search))."'::text)"), 'ASC');
            }

            if ($similarity or $distance) {
              $query = $query
                ->offset($offset)
                ->limit($limit);
            } else {
              $query = $query->where(DB::expr("regexp_replace(upper($match::text), E'[^0-9A-Z]', '')"), 'LIKE', '%'.preg_replace('/[^0-9A-Z]/', '', strtoupper($str)).'%');
            }

            foreach ((array) $query_args as $_query_args) {
              foreach ($_query_args as $key => $value) $query = call_user_func_array(array($query, $key), $value);
            }

            foreach (array_filter((array) $args) as $key => $value) if ($value) $query = $query->and_where($key, 'IN', (array) $value);
            if ($ids) $query = $query->and_where($table.'.id', 'NOT IN', $ids);

            foreach ($query->execute() as $result) if (!in_array($result['id'], $ids)) {
              $count++;
              $ids[] = $result['id'];
              if ($offset < $count && $return) $fetches[$count] = $return ? $result[$return] : $result['id'];
              if ($match_exact && $search == $result[is_array($match) ? reset($match) : $match]) break 4;
              if ($limit && ($count == $limit)) break 4;
            }
            if ($similarity) break 3;
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

  public static function suggest_barcode($barcode, $args = array('type' => 'P'), $return = FALSE, $match_exact = TRUE, $min_length = 5, $min_similarity = 0.3, $max_distance = 3, $limit = 10, $offset = 0)
  {
    $table  = 'barcodes';
    $model  = 'barcode';
    $match  = 'barcode';
    $fields = 'barcode';

    $query_args = array();

    // FIXME: fix for operator using two sets of printjobs
    $FMC_I = SGS::lookup_operator(406824004, TRUE);
    $PUP_2 = SGS::lookup_operator(406820009, TRUE);
    
    // FIXME: fix for Atlantic and Alpha sharing printjobs
    $ARL = SGS::lookup_operator(413236002, TRUE);
    $ALP = SGS::lookup_operator(406805000, TRUE);
    
    if (is_array($args['operators.id'])) {
      if (in_array($FMC_I, $args['operators.id']) or in_array($PUP_2, $args['operators.id'])) $args['operators.id'] = array($FMC_I, $PUP_2);
      if (in_array($ARL, $args['operators.id']) or in_array($ALP, $args['operators.id'])) $args['operators.id'] = array($ARL, $ALP);
    }
    
    if ($args['operators.id']) {
      $query_args[] = array('join' => array('printjobs'));
      $query_args[] = array('on' => array('barcodes.printjob_id', '=', 'printjobs.id'));
      $query_args[] = array('join' => array('sites'));
      $query_args[] = array('on' => array('printjobs.site_id', '=', 'sites.id'));
      $query_args[] = array('join' => array('operators'));
      $query_args[] = array('on' => array('sites.operator_id', '=', 'operators.id'));
    }
    if (strlen($barcode) >= 10) $query_args[] = array('where' => array(DB::expr('character_length(barcodes.barcode)'), '=', 13));
    else $query_args[] = array('where' => array(DB::expr('character_length(barcodes.barcode)'), '=', 8));

    return self::suggest($barcode, $table, $model, $match, $fields, $args, $query_args, $return, $match_exact, $min_length, $min_similarity, $max_distance, $limit, $offset);
  }

  public static function suggest_operator($tin, $args = array(), $return = FALSE, $match_exact = TRUE, $min_length = 5, $min_similarity = 0.3, $max_distance = 3, $limit = 10, $offset = 0)
  {
    $table  = 'operators';
    $model  = 'operator';
    $match  = 'tin';
    $fields = 'tin';

    $query_args = array();
    if ($args['sites.id']) {
      $query_args[] = array('join' => array('sites'));
      $query_args[] = array('on' => array('operators.id', '=', 'sites.operator_id'));
    }
    return self::suggest($tin, $table, $model, $match, $fields, $args, $query_args, $return, $match_exact, $min_length, $min_similarity, $max_distance, $limit, $offset);
  }

  public static function suggest_site($name, $args = array(), $return = FALSE, $match_exact = TRUE, $min_length = 5, $min_similarity = 0.3, $max_distance = 3, $limit = 10, $offset = 0)
  {
    $table  = 'sites';
    $model  = 'site';
    $match  = 'sites.name';
    $fields = 'sites.name';
    $strlen     = strlen($name);
    $min_length = ($strlen > 10) ? $strlen - 10 : $strlen;

    $query_args = array();
    if ($args['operators.id']) {
      $query_args[] = array('join' => array('operators'));
      $query_args[] = array('on' => array('sites.operator_id', '=', 'operators.id'));
    }

    return self::suggest($name, $table, $model, $match, $fields, $args, $query_args, $return, $match_exact, $min_length, $min_similarity, $max_distance, $limit, $offset);
  }

  public static function suggest_species($code, $args = array(), $return = FALSE, $match_exact = TRUE, $min_length = 2, $min_similarity = 0.3, $max_distance = 3, $limit = 10, $offset = 0)
  {
    $table  = 'species';
    $model  = 'species';
    $match  = 'code';
    $fields = 'code';

    $query_args = array();

    return self::suggest($code, $table, $model, $match, $fields, $args, $query_args, $return, $match_exact, $min_length, $min_similarity, $max_distance, $limit, $offset);
  }

  public static function decode_error($field, $error, $values = array(), $errors = array())
  {
    @$message = $errors[$field][$error] ?: $errors['all'][$error] ?: self::$errors[$field][$error] ?: self::$errors['all'][$error];
    return strtr((string) $message, $values);
  }

  public static function decode_tip($field, $error, $tips = array())
  {
    return $tips[$field][$error] ?: $tips['all'][$error] ?: self::$tips[$field][$error] ?: self::$tips['all'][$error];
  }

  public static function parse_site_and_block($text)
  {
    $matches = array();
    preg_match('/((RESOURCE\sAREA\/)?(([A-Z]+)\/)?([A-Z0-9\s-_]+)?)(\/([A-Z0-9]+))?/i', $text, $matches);

    return array(
      'site_name'  => $matches[5],
      'block_name' => $matches[7]
    );
  }

  public static function parse_grade($text)
  {
    $matches = array();
    preg_match('/^\w+\/([(LM|A|AB|B|BC|C|D|FAS|CG|1|2|3)]+)/', $text, $matches);

    return array(
      'grade'  => $matches[1]
    );
  }

  public static function parse_specs_number($text)
  {
    $matches = array();
    preg_match('/^SPEC\D+(\d+)/', $text, $matches);

    return array(
      'specs_number'  => str_pad($matches[1], 6, '0', STR_PAD_LEFT)
    );
  }

  public static function parse_exp_number($text)
  {
    $matches = array();
    preg_match('/^EP\D+(\d+)/', $text, $matches);

    return array(
      'exp_number'  => str_pad($matches[1], 6, '0', STR_PAD_LEFT)
    );
  }

  public static function odd_even(&$odd) {
    return ($odd ? ($odd = false) : ($odd = true)) ? 'odd' : 'even';
  }

  public static function wordify($string, $allowed_characters = array())
  {
    return preg_replace('/[^\w'.preg_quote(implode('', $allowed_characters)).']+/', '_', $string);
  }

  public static function fieldify($string)
  {
    if (strpos($string, 'barcode') !== FALSE) return self::unfieldify($string).'_id';
    else return $string;
  }

  public static function unfieldify($string)
  {
    if ($pos = strrpos($string, '_id')) return substr($string, 0, $pos);
    else return $string;
  }

  public static function errorify($string)
  {
    $string = preg_replace('/('.preg_quote(strtolower(implode('|', array_keys(self::$form_data_type)))).'_data)/', 'form', $string);
    $string = str_replace('content_md5', 'file', $string);
    // $string = preg_replace('/\b(tin)\b/', 'TIN', $string);
    // $string = str_replace('_id', '', $string);
    $string = str_replace('_', ' ', $string);
    $string = preg_replace('/\b(\w)/e', 'strtoupper("$1")', $string, 1);
    return trim($string);
  }

  public static function cleanify($array)
  {
    foreach ($array as $key => $value) {
      $value = trim($value);
    }
    return $array;
  }

  public static function flattenify($array)
  {
    $return = array();
    array_walk_recursive($array, function($a) use (&$return) {$return[] = $a;});
    return array_unique(array_filter($return));
  }

  public static function breakify($string)
  {
    return nl2br($string);
  }

  public static function booleanify($value)
  {
    if (is_string($value)) $value = strtolower($value);
    switch ($value) {
      case 'yes': case 'y': case 't': case '1': return TRUE;
      case 'no':  case 'n': case 'f': case '0': return FALSE;
      default: return (bool) $value;
    }
  }

  public static function barcodify($string)
  {
    $string = preg_replace('/[^0123456789ACEFHJKLMNPRYXW]/', '', strtoupper($string));
    if (strlen($string) > 8) $string = substr($string, 0, 8).'-'.substr($string, 8);
    return trim($string);
  }

  public static function numberify($number)
  {
    return str_pad($number, 6, '0', STR_PAD_LEFT);
  }

  public static function implodify($array)
  {
    return implode(' and ', array_filter(array_merge(array(implode(', ', array_slice($array, 0, -1))), array_slice($array, -1))));
  }

  public static function floatify($float, $precision = 0)
  {
    return (string) number_format(floor($float * pow(10, $precision)) / pow(10, $precision), $precision);
  }

  public static function quantitify($quantity, $precision = 3)
  {
    return (string) number_format(floor($quantity * pow(10, $precision)) / pow(10, $precision), $precision);
  }

  public static function amountify($amount, $precision = 2)
  {
    return (string) number_format(floor($amount * pow(10, $precision)) / pow(10, $precision), $precision);
  }

  public static function volumify($diameter, $height)
  {
    return self::quantitify(SGS::PI * pow(($diameter / 2), 2) * $height);
  }

  public static function locationify($string, $slash = '/')
  {
    $pos = strrpos($string, $slash);
    return trim(substr($string, $pos ? $pos + 1 : 0) ?: $string);
  }

  public static function utmify($easting, $northing, $zone = SGS::UTM_ZONE_DEFAULT)
  {
    list($easting, $northing) = preg_replace('/[\D]/', '', array((int) $easting, (int) $northing));
    if ($zone and $easting and ($northing !== FALSE)) return trim($zone.' '.$easting.'E '.$northing.'N');
  }

  public static function internationalify($string)
  {
    $matches = array();
    preg_match('/(\d{1,2})[\-\/](\d{1,2})[\-\/](\d{2,4})/', $string, $matches);

    if (!($matches[1] and $matches[2] and $matches[3])) return FALSE;

    if (strlen($matches[1]) == 1) $matches[1] = '0'.$matches[1];
    if (strlen($matches[2]) == 1) $matches[2] = '0'.$matches[2];
    if (strlen($matches[3]) == 2) $matches[3] = '20'.$matches[3];

    if (((int) $matches[1]) > 12) return $matches[3].'-'.$matches[2].'-'.$matches[1];
    else return $matches[3].'-'.$matches[1].'-'.$matches[2];
  }

  public static function render_classes($classes)
  {
    return implode(' ', self::wordify(array_filter((array) $classes), array('-')));
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

  public static function render_form_toggle($title) {
    return '<div class="toggle-form"><span>'.$title.'</span></div>';
  }

  public static function barcode_to_form_type($type) {
    switch ($type) {
      case 'T': return 'SSF';
      case 'F': return 'TDF';
      case 'L': return 'LDF';
    }
  }

}