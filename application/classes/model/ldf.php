<?php

class Model_LDF extends SGS_Form_ORM {

  const PARSE_START = 9;

  protected $_table_name = 'ldf_data';

  protected $_belongs_to = array(
    'site'     => array(),
    'operator' => array(),
    'barcode'  => array(),
    'parent_barcode' => array(
      'model'        => 'barcode',
      'foreign_key'  => 'parent_barcode_id'),
    'species'  => array(),
    'user'     => array(),
  );

  protected function _initialize() {
    parent::_initialize();
    $this->_object_plural = 'ldf';
  }

  public static $type = 'LDF';

  public static $fields = array(
    'create_date'    => 'Date Registered',
    'operator_tin'   => 'Operator TIN',
    'site_name'      => 'Site Name',
    'parent_barcode' => 'Original Log Barcode',
    'species_code'   => 'Species Code',
    'barcode'        => 'New Cross Cut Barcode',
    'bottom_max'     => 'Butt Max',
    'bottom_min'     => 'Butt Min',
    'top_max'        => 'Top Max',
    'top_min'        => 'Top Min',
    'length'         => 'Length',
    'volume'         => 'Volume',
    'action'         => 'Action',
    'comment'        => 'Comment',
  );

  public static $checks = array(
    'consistency' => array(
      'title'  => 'Data Consistency',
      'checks' => array(
        'is_valid_barcode' => array(
          'title'   => 'New cross cut barcode assignment is valid',
          'error'   => 'New cross cut barcode assignment is invalid',
          'warning' => 'New cross cut barcode is not yet assigned',
         ),
        'is_valid_parent_barcode' => array(
          'title'   => 'Original log barcode assignment is valid',
          'error'   => 'Original log barcode assignment is invalid',
          'warning' => 'Original log barcode is not yet assigned',
         )
    )),
    'traceability' => array(
      'title'  => 'Traceability',
      'checks' => array(
        'is_existing_parent' => array(
          'title' => 'Traceable to parent log',
          'error' => 'Not tracable to parent log'
        ),
        'is_valid_parent' => array(
          'title' => 'Parent log passed checks and queries',
          'error' => 'Parent log failed checks and queries'
        )
    )),
    'tolerance' => array(
      'title'  => 'Tolerance',
      'checks' => array(
        'is_matching_species' => array(
          'title'   => 'Species matches data for parent log',
          'error'   => 'Species does not match data for parent log',
          'warning' => 'Species class matches data for parent log but species code does not'
        ),
        'is_matching_diameter' => array(
          'title'   => 'Diameter matches data for parent log',
          'error'   => 'Diameter does not match data for parent log',
          'warning' => 'Diameter matches data for parent log but is inaccurate'
        ),
        'is_matching_length' => array(
          'title'   => 'Length matches data for parent log',
          'error'   => 'Length does not match data for parent log',
          'warning' => 'Length matches data for parent log but is inaccurate'
        ),
        'is_matching_volume' => array(
          'title'   => 'Volume matches data for parent log',
          'error'   => 'Volume does not match data for parent log',
          'warning' => 'Volume matches data for parent log but is inaccurate'
        ),
        'is_matching_operator' => array(
          'title' => 'Operator matches data for parent log',
          'error' => 'Operator does not match data for parent log',
        ),
        'is_matching_site' => array(
          'title' => 'Site matches data for parent log',
          'error' => 'Site does not match data for parent log',
        )
    )),
//    'reliability' => array(
//      'title'  => 'Data Reliability',
//      'checks' => array(
//        'is_consistent_operator' => array(
//          'title'   => 'Operator assignments are consistent',
//          'warning' => 'Operator assignments are inconsistent'
//        ),
//        'is_consistent_site' => array(
//          'title'   => 'Site assignments are consistent',
//          'warning' => 'Site assignments are inconsistent'
//        )
//    )),
  );

  public function formo() {
    $array = array(
      'id'             => array('render' => FALSE),
      'barcode'        => array('render' => FALSE),
      'parent_barcode' => array('render' => FALSE),
      'operator'       => array('render' => FALSE),
      'site'           => array('render' => FALSE),
      'status'         => array('render' => FALSE),
      'user'           => array('render' => FALSE),
      'timestamp'      => array('render' => FALSE),
      'species'        => array(
        'orm_primary_val' => 'code',
        'label' => 'Species'
      ),
      'create_date' => array('order' => 0),
    );
    foreach (self::fields() as $field => $label) {
      $array[$field]['label'] = $label;
    }
    return $array;
  }

  public function parse_csv($row, &$csv)
  {
    extract(SGS::parse_site_and_block(trim($csv[2][B] ?: $csv[2][C] ?: $csv[2][D] ?: $csv[2][E])));
    $data = array(
      'parent_barcode' => SGS::barcodify(trim($row[A])),
      'species_code'   => trim($row[B]),
      'barcode'        => SGS::barcodify(trim($row[C])),
      'bottom_max'     => trim($row[D]),
      'bottom_min'     => trim($row[E]),
      'top_max'        => trim($row[F]),
      'top_min'        => trim($row[G]),
      'length'         => trim($row[H]),
      'volume'         => trim($row[I]),
    );

    if (array_filter($data)) return SGS::cleanify(array(
      'create_date'    => SGS::date(trim($csv[3][B] ?: $csv[3][C] ?: $csv[3][D] ?: $csv[3][E]), SGS::US_DATE_FORMAT, TRUE, TRUE),
      'operator_tin'   => trim($csv[4][B] ?: $csv[4][C] ?: $csv[4][D] ?: $csv[4][E]),
      'site_name'      => $site_name,
    ) + $data + array(
      'action'         => trim($row[J]),
      'comment'        => trim($row[K]),
    ));
  }

  public function parse_data($data)
  {
    foreach ($data as $key => $value) switch ($key) {
      case 'operator_tin':
        $this->operator = SGS::lookup_operator($value); break;

      case 'site_name':
        $this->site = SGS::lookup_site($value);
        break;

      case 'barcode':
      case 'parent_barcode':
        $this->$key = SGS::lookup_barcode(SGS::barcodify($value)); break;

      case 'species_code':
        $this->species = SGS::lookup_species($value); break;

      case 'create_date':
        $this->$key = SGS::date($value, SGS::PGSQL_DATE_FORMAT); break;

      default:
        try { $this->$key = $value; } catch (Exception $e) {}
    }
  }

  public function export_data($excel, $row) {
    $excel->getActiveSheet()->SetCellValue('A'.$row, $this->parent_barcode->barcode);
    $excel->getActiveSheet()->SetCellValue('B'.$row, $this->species->code);
    $excel->getActiveSheet()->SetCellValue('C'.$row, $this->barcode->barcode);
    $excel->getActiveSheet()->SetCellValue('D'.$row, $this->bottom_max);
    $excel->getActiveSheet()->SetCellValue('E'.$row, $this->bottom_min);
    $excel->getActiveSheet()->SetCellValue('F'.$row, $this->top_max);
    $excel->getActiveSheet()->SetCellValue('G'.$row, $this->top_min);
    $excel->getActiveSheet()->SetCellValue('H'.$row, $this->length);
    $excel->getActiveSheet()->SetCellValue('I'.$row, $this->volume);
    $excel->getActiveSheet()->SetCellValue('J'.$row, $this->action);
    $excel->getActiveSheet()->SetCellValue('K'.$row, $this->comment);
  }

  public function export_headers($excel, $args, $headers = TRUE) {
    if ($headers) {
      $excel->getActiveSheet()->SetCellValue('C1', 'LOG DATA FORM');
      $excel->getActiveSheet()->SetCellValue('K1', 'SOP13-6'); // don't know
      $excel->getActiveSheet()->SetCellValue('A2', 'Site type and Reference:');
      $excel->getActiveSheet()->SetCellValue('F2', 'Site Holder Name:');
      $excel->getActiveSheet()->SetCellValue('A3', 'Date Registered:');
      $excel->getActiveSheet()->SetCellValue('F3', 'Form Reference No.:');
      $excel->getActiveSheet()->SetCellValue('A4', 'Site TIN:');
      $excel->getActiveSheet()->SetCellValue('F4', 'Log Measurer:');
      $excel->getActiveSheet()->SetCellValue('A5', 'Date Entered in to CoCIS:');
      $excel->getActiveSheet()->SetCellValue('F5', 'Entered By:');
      $excel->getActiveSheet()->SetCellValue('A6', 'Original Log Barcode');
      $excel->getActiveSheet()->SetCellValue('B6', 'Species Code');
      $excel->getActiveSheet()->SetCellValue('C6', 'New Cross Cut Barcode');
      $excel->getActiveSheet()->SetCellValue('D6', 'Diameter (cm underbark to the nearest cm)');
      $excel->getActiveSheet()->SetCellValue('H6', 'Length (m) to the nearest 0.1m');
      $excel->getActiveSheet()->SetCellValue('I6', 'Volume declared (m3)');
      $excel->getActiveSheet()->SetCellValue('J6', 'Action');
      $excel->getActiveSheet()->SetCellValue('K6', 'Comment');
      $excel->getActiveSheet()->SetCellValue('D7', 'Butt end');
      $excel->getActiveSheet()->SetCellValue('F7', 'Top');
      $excel->getActiveSheet()->SetCellValue('D8', 'Max');
      $excel->getActiveSheet()->SetCellValue('E8', 'Min');
      $excel->getActiveSheet()->SetCellValue('F8', 'Max');
      $excel->getActiveSheet()->SetCellValue('G8', 'Min');
    }

    $excel->getActiveSheet()->SetCellValue('B2', $this->site->type.'/'.$this->site->name);
    $excel->getActiveSheet()->SetCellValue('G2', $this->operator->tin); // site holder name
    $excel->getActiveSheet()->SetCellValue('B3', SGS::date($args['create_date'], SGS::US_DATE_FORMAT));
    $excel->getActiveSheet()->SetCellValue('G3', ''); // form reference number ?
    $excel->getActiveSheet()->SetCellValue('B4', $this->operator->tin);
    $excel->getActiveSheet()->SetCellValue('G4', ''); // log measurer
    $excel->getActiveSheet()->SetCellValue('B5', ''); // date entered into CoCIS
    $excel->getActiveSheet()->SetCellValue('G5', ''); // entered by
  }

  public function download_data($values, $errors, $excel, $row) {
    $excel->getActiveSheet()->SetCellValue('A'.$row, $values['parent_barcode']);
    $excel->getActiveSheet()->SetCellValue('B'.$row, $values['species_code']);
    $excel->getActiveSheet()->SetCellValue('C'.$row, $values['barcode']);
    $excel->getActiveSheet()->SetCellValue('D'.$row, $values['bottom_max']);
    $excel->getActiveSheet()->SetCellValue('E'.$row, $values['bottom_min']);
    $excel->getActiveSheet()->SetCellValue('F'.$row, $values['top_max']);
    $excel->getActiveSheet()->SetCellValue('G'.$row, $values['top_min']);
    $excel->getActiveSheet()->SetCellValue('H'.$row, $values['length']);
    $excel->getActiveSheet()->SetCellValue('I'.$row, $values['volume']);
    $excel->getActiveSheet()->SetCellValue('J'.$row, $values['action']);
    $excel->getActiveSheet()->SetCellValue('K'.$row, $values['comment']);

    if ($errors) {
      foreach ($errors as $field => $array) foreach ((array) $array as $error) $text[] = SGS::decode_error($field, $error, array(':field' => $fields[$field]));
      $excel->getActiveSheet()->SetCellValue('M'.$row, implode(" \n", (array) $text));
      $excel->getActiveSheet()->getStyle('M'.$row)->getAlignment()->setWrapText(true);
    }
  }

  public function download_headers($values, $excel, $args, $headers = TRUE) {
    if ($headers) {
      $excel->getActiveSheet()->SetCellValue('C1', 'LOG DATA FORM');
      $excel->getActiveSheet()->SetCellValue('K1', 'SOP13-6'); // don't know
      $excel->getActiveSheet()->SetCellValue('A2', 'Site type and Reference:');
      $excel->getActiveSheet()->SetCellValue('F2', 'Site Holder Name:');
      $excel->getActiveSheet()->SetCellValue('A3', 'Date Registered:');
      $excel->getActiveSheet()->SetCellValue('F3', 'Form Reference No.:');
      $excel->getActiveSheet()->SetCellValue('A4', 'Site TIN:');
      $excel->getActiveSheet()->SetCellValue('F4', 'Log Measurer:');
      $excel->getActiveSheet()->SetCellValue('A5', 'Date Entered in to CoCIS:');
      $excel->getActiveSheet()->SetCellValue('F5', 'Entered By:');
      $excel->getActiveSheet()->SetCellValue('A6', 'Original Log Barcode');
      $excel->getActiveSheet()->SetCellValue('B6', 'Species Code');
      $excel->getActiveSheet()->SetCellValue('C6', 'New Cross Cut Barcode');
      $excel->getActiveSheet()->SetCellValue('D6', 'Diameter (cm underbark to the nearest cm)');
      $excel->getActiveSheet()->SetCellValue('H6', 'Length (m) to the nearest 0.1m');
      $excel->getActiveSheet()->SetCellValue('I6', 'Volume declared (m3)');
      $excel->getActiveSheet()->SetCellValue('J6', 'Action');
      $excel->getActiveSheet()->SetCellValue('K6', 'Comment');
      $excel->getActiveSheet()->SetCellValue('D7', 'Butt end');
      $excel->getActiveSheet()->SetCellValue('F7', 'Top');
      $excel->getActiveSheet()->SetCellValue('D8', 'Max');
      $excel->getActiveSheet()->SetCellValue('E8', 'Min');
      $excel->getActiveSheet()->SetCellValue('F8', 'Max');
      $excel->getActiveSheet()->SetCellValue('G8', 'Min');
    }

    $excel->getActiveSheet()->SetCellValue('B2', substr($values['site_name'], 0 , 3).'/'.$values['site_name']);
    $excel->getActiveSheet()->SetCellValue('G2', $values['operator_tin']); // site holder name
    $excel->getActiveSheet()->SetCellValue('B3', SGS::date($args['create_date'], SGS::US_DATE_FORMAT));
    $excel->getActiveSheet()->SetCellValue('G3', ''); // form reference number ?
    $excel->getActiveSheet()->SetCellValue('B4', $values['operator_tin']);
    $excel->getActiveSheet()->SetCellValue('G4', ''); // log measurer
    $excel->getActiveSheet()->SetCellValue('B5', ''); // date entered into CoCIS
    $excel->getActiveSheet()->SetCellValue('G5', ''); // entered by
  }

  public function make_suggestions($values, $errors) {
    $suggestions = array();
    foreach ($errors as $field => $options) {
      extract($options);
      switch ($field) {
        case 'barcode':
          $args = array(
            'barcodes.type' => array('L', 'P'),
            'operators.id' => SGS::suggest_operator($values['operator_tin'], array(), 'id')
          );
          $suggest = SGS::suggest_barcode($values[$field], $args, 'barcode', TRUE, $min_length ?: 2, $limit ?: 10, $offset ?: 0, $min_length ?: 2, $limit ?: 20, $offset ?: 0);
          break;
        case 'parent_barcode':
          $args = array(
            'barcodes.type' => array('P', 'F', 'L'),
            'operators.id' => SGS::suggest_operator($values['operator_tin'], array(), 'id')
          );
          $suggest = SGS::suggest_barcode($values[$field], $args, 'barcode', TRUE, $min_length ?: 2, $limit ?: 10, $offset ?: 0, $min_length ?: 2, $limit ?: 20, $offset ?: 0);
          break;
        case 'operator_tin':
          $args = array(
            'sites.id' => SGS::suggest_site($values['site_name'], array(), 'id'),
          );
          $suggest = SGS::suggest_operator($values[$field], $args, 'tin', TRUE, $min_length ?: 2, $limit ?: 10, $offset ?: 0, $min_length ?: 5, $limit ?: 10, $offset ?: 0);
          break;
        case 'site_name':
          $args = array(
            'operators.id' => SGS::suggest_operator($values['operator_tin'], array(), 'id')
          );
          $suggest = SGS::suggest_site($values[$field], $args, 'name', TRUE, $min_length ?: 2, $limit ?: 10, $offset ?: 0, $min_length ?: 5, $limit ?: 10, $offset ?: 0);
          break;
        case 'species_code':
          $suggest = SGS::suggest_species($values[$field], array(), 'code', TRUE, $min_length ?: 2, $limit ?: 10, $offset ?: 0, $min_length ?: 2, $limit ?: 10, $offset ?: 0);
          break;
      }
      $suggestions[$field] = $suggest;
    }

    return $suggestions;
  }

  public function find_duplicates($values, $errors) {
    $duplicates = array();

    foreach ($this->fields() as $field => $label) {
      $duplicate = NULL;
      switch ($field) {
        case 'barcode':
          $query = DB::select('id')
            ->from($this->_table_name)
            ->where($field.'_id', '=', ($val = SGS::lookup_barcode($values[$field], TRUE)) ? $val : NULL);

          if ($operator_id = SGS::lookup_operator($values['operator_tin'], TRUE)) $query->and_where('operator_id', '=', $operator_id);
          if ($site_id     = SGS::lookup_site($values['site_name'], TRUE)) $query->and_where('site_id', '=', $site_id);

          if ($duplicate = $query->execute()->get('id')) $duplicates[$field] = $duplicate;
          break;
      }
    }

    return $duplicates;
  }

  public function run_checks() {
    $errors   = array();
    $warnings = array();

    $this->unset_errors();
    $this->unset_warnings();

    // warnings
    if (!($this->operator_id == $this->barcode->printjob->site->operator_id)) $warnings['barcode_id'][] = 'is_consistent_operator';
    if (!($this->operator_id == $this->parent_barcode->printjob->site->operator_id)) $warnings['parent_barcode_id'][] = 'is_consistent_operator';
    if (!($this->operator_id == $this->site->operator_id)) $warnings['site_id'][] = 'is_consistent_operator';

    if (!($this->site_id == $this->barcode->printjob->site_id)) $warnings['barcode_id'][] = 'is_consistent_site';
    if (!($this->site_id == $this->parent_barcode->printjob->site_id)) $warnings['parent_barcode_id'][] = 'is_consistent_site';

    if (!(in_array($this->site, $this->operator->sites->find_all()->as_array()))) $warnings['operator_id'][] = 'is_consistent_site';

    // errors
    switch ($this->barcode->type) {
      case 'L': break;
      default:  $errors['barcode_id'][] = 'is_valid_barcode'; break;
    }

    switch ($this->parent_barcode->type) {
      case 'F': break;
      case 'L': break;
      default:  $errors['parent_barcode_id'][] = 'is_valid_parent_barcode'; break;
    }

    $parent = $this->parent();

    if ($parent and $parent->loaded()) {
      if ($parent->status != 'A') $errors['parent_barcode_id'][] = 'is_valid_parent';

      if (!(ord($this->species->class) >= ord($parent->species->class))) $errors['species_id'][] = 'is_matching_species';
      if (!($this->species->code  == $parent->species->code))  $warnings['species_id'][] = 'is_matching_species';

      if (!($this->operator_id == $parent->operator_id)) $errors['operator_id'][] = 'is_matching_operator';
      if (!($this->site_id     == $parent->site_id))     $errors['site_id'][]     = 'is_matching_site';

      $length   = 0;
      $diameter = 0;
      $volume   = 0;

      if ($siblings = $this->siblings()) {
        foreach ($siblings as $child) {
          $length   += $child->length;
          $diameter += (($child->top_min + $child->top_max + $child->bottom_min + $child->bottom_max) / 4);
          $volume   += $child->volume;
        }

        $diameter = $diameter / count($siblings);
        $volume   = $volume / count($siblings);

        if ($parent::$type == 'LDF') {
          if (!Valid::meets_tolerance($volume, $parent->volume, SGS::LDF_VOLUME_TOLERANCE)) $errors['volume'][] = 'is_matching_volume';
          else if (!Valid::meets_tolerance($volume, $parent->volume, SGS::LDF_VOLUME_ACCURACY)) $warnings['volume'][] = 'is_matching_volume';
        }

        if (!Valid::meets_tolerance($length, $parent->length, SGS::LDF_LENGTH_TOLERANCE)) $errors['length'][] = 'is_matching_length';
        else if (!Valid::meets_tolerance($length, $parent->length, SGS::LDF_LENGTH_ACCURACY)) $warnings['length'][] = 'is_matching_length';

        if (!Valid::meets_tolerance($diameter, (($parent->top_min + $parent->top_max + $parent->bottom_min + $parent->bottom_max) / 4), SGS::LDF_DIAMETER_TOLERANCE)) {
          $errors['top_min'][] = 'is_matching_diameter';
          $errors['top_max'][] = 'is_matching_diameter';
          $errors['bottom_min'][] = 'is_matching_diameter';
          $errors['bottom_max'][] = 'is_matching_diameter';
        }
        else if (!Valid::meets_tolerance($diameter, (($parent->top_min + $parent->top_max + $parent->bottom_min + $parent->bottom_max) / 4), SGS::LDF_DIAMETER_ACCURACY)) {
          $warnings['top_min'][] = 'is_matching_diameter';
          $warnings['top_max'][] = 'is_matching_diameter';
          $warnings['bottom_min'][] = 'is_matching_diameter';
          $warnings['bottom_max'][] = 'is_matching_diameter';
        }
      }
    }
    else {
      $errors['parent_barcode_id'][] = 'is_existing_parent';
      $errors['parent_barcode_id'][] = 'is_valid_parent';
    }

    // all tolerance checks fail if any traceability checks fail
    if (array_intersect(SGS::flattenify($errors), array_keys(self::$checks['traceability']['checks']))) {
      foreach (self::$checks['tolerance']['checks'] as $check => $array) $errors['parent_barcode_id'][] = $check;
    }

    if ($warnings) foreach ($warnings as $field => $array) {
      foreach (array_filter(array_unique($array)) as $warning) $this->set_warning($field, $warning);
    }

    if ($errors) {
      $this->status = 'R';
      foreach ($errors as $field => $array) {
        foreach (array_filter(array_unique($array)) as $error) $this->set_error($field, $error);
      }
    }
    else $this->status = 'A';

    $this->save();

    return array($errors, $warnings);
  }

  public static function fields()
  {
    return (array) self::$fields;
  }

  public function rules()
  {
    return array(
      'site_id'            => array(array('not_empty')),
      'operator_id'        => array(array('not_empty')),
      'species_id'         => array(array('not_empty')),
      'barcode_id'         => array(array('not_empty'),
                                    array('is_unique', array($this->_table_name, ':field', ':value', $this->id))),
      'parent_barcode_id'  => array(array('not_empty')),
      'top_min'            => array(array('not_empty'),
                                    array('is_measurement_int')),
      'top_max'            => array(array('not_empty'),
                                    array('is_measurement_int')),
      'bottom_min'         => array(array('not_empty'),
                                    array('is_measurement_int')),
      'bottom_max'         => array(array('not_empty'),
                                    array('is_measurement_int')),
      'length'             => array(array('not_empty'),
                                    array('is_measurement_float')),
      'volume'             => array(array('not_empty'),
                                    array('is_measurement_float')),
      'action'             => array(),
      'comment'            => array(),
      'create_date'        => array(array('not_empty'),
                                    array('is_date')),
      'user_id'            => array(),
      'timestamp'          => array()
    );
  }

  public function other_rules()
  {
    return array(
      'operator_tin'      => array(array('not_empty'),
                                   array('is_operator_tin'),
                                   array('is_existing_operator')),
      'site_name'         => array(array('is_text_short'),
                                   array('is_existing_site')),
      'barcode'           => array(array('not_empty'),
                                   array('is_barcode', array(':value', TRUE)),
                                   array('is_existing_barcode')),
      'parent_barcode'    => array(array('not_empty'),
                                   array('is_barcode', array(':value', TRUE)),
                                   array('is_existing_barcode')),
      'species_code'      => array(array('not_empty'),
                                   array('is_species_code'),
                                   array('is_existing_species'))
    );
  }

  public function labels()
  {
    return array(
      'create_date'        => self::$fields['create_date'],
      'operator_id'        => 'Operator',
      'site_id'            => 'Site',
      'species_id'         => 'Species',
      'barcode_id'         => self::$fields['barcode'],
      'parent_barcode_id'  => self::$fields['parent_barcode'],
      'top_min'            => self::$fields['top_min'],
      'top_max'            => self::$fields['top_max'],
      'bottom_min'         => self::$fields['bottom_min'],
      'bottom_max'         => self::$fields['bottom_max'],
      'length'             => self::$fields['length'],
      'volume'             => self::$fields['volume'],
      'action'             => self::$fields['action'],
      'comment'            => self::$fields['comment'],
//      'user_id'         => self::$fields['user_id'],
//      'timestamp'       => self::$fields['timestamp'],
    );
  }

  public function siblings() {
    $sql = "SELECT barcode_id
            FROM barcode_hops_cached
            WHERE parent_id = $this->parent_barcode_id
              AND hops = 1";

    return ORM::factory('LDF')
      ->where('barcode_id', 'IN', DB::expr("($sql)"))
      ->find_all()
      ->as_array();
  }

  public function children($hops = array()) {
    $sql = "SELECT barcode_id
            FROM barcode_hops_cached
            WHERE parent_id = $this->barcode_id
              AND hops = 1";

    return ORM::factory('LDF')
      ->where('barcode_id', 'IN', DB::expr("($sql)"))
      ->find_all()
      ->as_array();
  }

  public function childrens($hops = array()) {
    $sql = "SELECT barcode_id
            FROM barcode_hops_cached
            WHERE parent_id = $this->barcode_id";
    if ($hops) $sql .= " AND hops IN (".implode(',', $hops).")";

    return ORM::factory('LDF')
      ->where('barcode_id', 'IN', DB::expr("($sql)"))
      ->find_all()
      ->as_array();
  }

  public function parents($hops = array()) {
    $sql = "SELECT parent_id
            FROM barcode_hops_cached
            WHERE barcode_id = $this->barcode_id";
    if ($hops) $sql .= " AND hops IN (".implode(',', $hops).")";

    return ORM::factory('LDF')
        ->where('barcode_id', 'IN', DB::expr("($sql)"))
        ->find_all()
        ->as_array() +
      ORM::facotry('TDF')
        ->where('barcode_id', 'IN', DB::expr("($sql)"))
        ->find_all()
        ->as_array();
  }

  public function parent() {
    switch ($this->parent_barcode->type) {
      case 'F': $form_type = 'TDF'; break;
      case 'L': $form_type = 'LDF'; break;
    }

    return $form_type ? ORM::factory('TDF')
      ->where('barcode_id', '=', $this->parent_barcode->id)
      ->find() : NULL;
  }

}