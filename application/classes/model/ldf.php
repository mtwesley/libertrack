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

  protected $_ignored_columns = array(
    'diameter',
    'top_diameter',
    'bottom_diameter'
  );

  protected function _initialize() {
    parent::_initialize();
    $this->_object_plural = 'ldf';
  }

  public function __get($column) {
    switch ($column) {
      case 'volume':
        return SGS::volumify(($this->diameter / 100), $this->length);

      case 'top_diameter':
        return SGS::floatify(($this->top_min + $this->top_max) / 2);

      case 'bottom_diameter':
        return SGS::floatify(($this->bottom_min + $this->bottom_max) / 2);

      case 'diameter':
        return SGS::floatify(($this->top_min + $this->top_max + $this->bottom_min + $this->bottom_max) / 4);

      default:
        return parent::__get($column);
    }
  }

  public function set($column, $value) {
    switch ($column) {
      case 'volume':
        $this->original_volume = $value;
        parent::set($column, $this->volume);

      default:
        parent::set($column, $value);
    }
  }

  public function save(Validation $validation = NULL) {
    if ($this->barcode->type == 'F') {
      if ($barcode = SGS::lookup_barcode($this->barcode->barcode, array('L', 'P')) and $barcode->loaded()) $this->barcode = $barcode;
      else {
        $barcode = ORM::factory('barcode')->values($this->barcode->as_array());
        $barcode->id = NULL;
        $barcode->parent_id = NULL;
        $barcode->type = 'L';
        $barcode->save();
        $this->barcode = $barcode;
      }
    }

    if (($this->barcode->barcode == $this->parent_barcode->barcode) and ($this->parent_barcode->type == 'L')) {
      if ($parent_barcode = SGS::lookup_barcode($this->barcode->barcode, array('F', 'P')) and $parent_barcode->loaded()) $this->parent_barcode = $parent_barcode;
      else {
        $parent_barcode = ORM::factory('barcode')->values($this->barcode->as_array());
        $parent_barcode->id = NULL;
        $parent_barcode->parent_id = NULL;
        $parent_barcode->type = 'F';
        $parent_barcode->save();
        $this->parent_barcode = $parent_barcode;
      }
    }

    if (($this->barcode->barcode != $this->parent_barcode->barcode) and ($this->parent_barcode->type == 'F') and ($parent_barcode = SGS::lookup_barcode($this->parent_barcode->barcode, 'L') and $parent_barcode->loaded())) $this->parent_barcode = $parent_barcode;

    parent::save($validation);
  }

  public static $type      = 'LDF';
  public static $data_type = 'LDF';
  public static $verification_type = 'LDFV';

  public static $fields = array(
    'create_date'      => 'Date',
    'operator_tin'     => 'Operator TIN',
    'site_name'        => 'Site Name',
    'measured_by'      => 'Log Measurer',
    'entered_by'       => 'Entered By',
    'form_number'      => 'Form Reference No.',
    'parent_barcode'   => 'Original Log Barcode',
    'species_code'     => 'Species Code',
    'barcode'          => 'New Cross Cut Barcode',
    'bottom_max'       => 'Butt Max',
    'bottom_min'       => 'Butt Min',
    'top_max'          => 'Top Max',
    'top_min'          => 'Top Min',
    'length'           => 'Length',
    'volume'           => 'Volume',
    'action'           => 'Action',
    'comment'          => 'Comment',
  );

  public static $checks = array(
    'consistency' => array(
      'title'  => 'Data Consistency',
      'checks' => array(
        'is_valid_barcode' => array(
          'name'    => 'New Cross Cut Barcode',
          'title'   => 'New cross cut barcode assignment is valid',
          'error'   => 'New cross cut barcode assignment is invalid',
          'warning' => 'New cross cut barcode is not yet assigned',
         ),
        'is_valid_parent_barcode' => array(
          'name'    => 'Original Log Barcode',
          'title'   => 'Original log barcode assignment is valid',
          'error'   => 'Original log barcode assignment is invalid',
          'warning' => 'Original log barcode is not yet assigned',
         )
    )),
    'reliability' => array(
      'title'  => 'Data Reliability',
      'checks' => array(
        'is_consistent_operator' => array(
          'name'    => 'Operator Assignments',
          'title'   => 'Operator assignments are consistent',
          'warning' => 'Operator assignments are inconsistent'
        ),
        'is_consistent_site' => array(
          'name'    => 'Site Assignments',
          'title'   => 'Site assignments are consistent',
          'warning' => 'Site assignments are inconsistent'
        )
    )),
    'traceability' => array(
      'title'  => 'Traceability',
      'checks' => array(
        'is_existing_parent' => array(
          'name'  => 'Traceable Parent',
          'title' => 'Traceable to parent log',
          'error' => 'Not tracable to parent log'
        ),
        'is_valid_parent' => array(
          'name'  => 'Parent Status',
          'title' => 'Parent log passed checks and queries',
          'error' => 'Parent log failed checks and queries'
        )
    )),
    'tolerance' => array(
      'title'  => 'Tolerance',
      'checks' => array(
        'is_matching_species' => array(
          'name'    => 'Species',
          'title'   => 'Species matches parent log data',
          'error'   => 'Species does not match parent log data',
          'warning' => 'Species class matches parent log data, but species code does not'
        ),
        'is_matching_diameter' => array(
          'name'    => 'Diameter',
          'title'   => 'Diameters of all siblings match each other and parent log data',
          'error'   => 'Diameters of all siblings does not match each other and parent log data',
          'warning' => 'Diameters of all siblings match each other and parent log data, but are inaccurate'
        ),
        'is_matching_length' => array(
          'name'    => 'Length',
          'title'   => 'Lengths of all siblings match parent log data',
          'error'   => 'Lengths of all siblings do not match parent log data',
          'warning' => 'Lengths of all siblings match parent log data, but are inaccurate'
        ),
        'is_matching_volume' => array(
          'name'    => 'Volume',
          'title'   => 'Volumes of all siblings match parent log data',
          'error'   => 'Volumes of all siblings do not match parent log data',
          'warning' => 'Volumes of all siblings match parent log data, but are inaccurate'
        ),
        'is_matching_operator' => array(
          'name'    => 'Operator',
          'title'   => 'Operator matches parent log data',
          'error'   => 'Operator does not match parent log data',
          'warning' => 'Operator does not match parent log data',
        ),
        'is_matching_site' => array(
          'name'    => 'Site',
          'title'   => 'Site matches parent log data',
          'error'   => 'Site does not match parent log data',
          'warning' => 'Site does not match SSF record data',
        )
    )),
  );

  public function formo() {
    $array = array(
      'id'              => array('render' => FALSE),
      'create_date'     => array('order' => 0, 'attr' => array('class' => 'dpicker')),
      'barcode'         => array('render' => FALSE),
      'parent_barcode'  => array('render' => FALSE),
      'operator'        => array('render' => FALSE),
      'site'            => array('render' => FALSE),
      'original_volume' => array('render' => FALSE),
      'status'          => array('render' => FALSE),
      'user'            => array('render' => FALSE),
      'timestamp'       => array('render' => FALSE),
      'species'         => array(
        'orm_primary_val' => 'code',
        'label' => 'Species'
      ),
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
      'form_number'    => trim($csv[2][G] ?: $csv[2][H] ?: $csv[2][I] ?: $csv[2][J] ?: $csv[2][K]),
      'operator_tin'   => trim($csv[4][B] ?: $csv[4][C] ?: $csv[4][D] ?: $csv[4][E]),
      'site_name'      => $site_name,
      'measured_by'    => trim($csv[4][G] ?: $csv[4][H] ?: $csv[4][I] ?: $csv[4][J] ?: $csv[4][K]),
      'entered_by'     => trim($csv[5][G] ?: $csv[5][H] ?: $csv[5][I] ?: $csv[5][J] ?: $csv[5][K]),
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
        $this->site = SGS::lookup_site($value); break;

      case 'barcode':
      case 'parent_barcode':
        $this->$key = SGS::lookup_barcode(SGS::barcodify($value)); break;

      case 'species_code':
        $this->species = SGS::lookup_species($value); break;

      case 'create_date':
        $this->$key = SGS::date($value, SGS::PGSQL_DATE_FORMAT); break;

      case 'bottom_min':
        $this->$key = SGS::floatify(min(array($data['bottom_min'],$data['bottom_max']))); break;

      case 'bottom_max':
        $this->$key = SGS::floatify(max(array($data['bottom_min'],$data['bottom_max']))); break;

      case 'top_min':
        $this->$key = SGS::floatify(min(array($data['top_min'],$data['top_max']))); break;

      case 'top_max':
        $this->$key = SGS::floatify(max(array($data['top_min'],$data['top_max']))); break;

      case 'length':
        $this->$key = SGS::floatify($value, 1); break;

      case 'volume':
        $this->$key = SGS::quantitify($value); break;

      default:
        try { $this->$key = $value; } catch (Exception $e) {} break;
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
      $excel->getActiveSheet()->SetCellValue('F2', 'Operator Name:');
      $excel->getActiveSheet()->SetCellValue('A3', 'Date Registered:');
      $excel->getActiveSheet()->SetCellValue('F3', 'Form Reference No.:');
      $excel->getActiveSheet()->SetCellValue('A4', 'Operator TIN:');
      $excel->getActiveSheet()->SetCellValue('F4', 'Log Measurer:');
      $excel->getActiveSheet()->SetCellValue('A5', 'Date Entered:');
      $excel->getActiveSheet()->SetCellValue('F5', 'Entered By:');
      $excel->getActiveSheet()->SetCellValue('A6', 'Original Log Barcode');
      $excel->getActiveSheet()->SetCellValue('B6', 'Species Code');
      $excel->getActiveSheet()->SetCellValue('C6', 'New Cross Cut Barcode');
      $excel->getActiveSheet()->SetCellValue('D6', 'Diameter (cm underbark to the nearest cm)');
      $excel->getActiveSheet()->SetCellValue('H6', 'Length (m) to the nearest 0.1m');
      $excel->getActiveSheet()->SetCellValue('I6', 'Volume (m3)');
      $excel->getActiveSheet()->SetCellValue('J6', 'Action');
      $excel->getActiveSheet()->SetCellValue('K6', 'Comment');
      $excel->getActiveSheet()->SetCellValue('D7', 'Butt');
      $excel->getActiveSheet()->SetCellValue('F7', 'Top');
      $excel->getActiveSheet()->SetCellValue('D8', 'Max');
      $excel->getActiveSheet()->SetCellValue('E8', 'Min');
      $excel->getActiveSheet()->SetCellValue('F8', 'Max');
      $excel->getActiveSheet()->SetCellValue('G8', 'Min');
    }

    $excel->getActiveSheet()->SetCellValue('B2', $this->site->type.'/'.$this->site->name);
    $excel->getActiveSheet()->SetCellValue('G2', $this->operator->name);
    $excel->getActiveSheet()->SetCellValue('B3', SGS::date($args['create_date'], SGS::US_DATE_FORMAT));
    $excel->getActiveSheet()->SetCellValue('G3', $this->form_number);
    $excel->getActiveSheet()->SetCellValue('B4', $this->operator->tin);
    $excel->getActiveSheet()->SetCellValue('G4', $this->measured_by);
    $excel->getActiveSheet()->SetCellValue('B5', SGS::date($this->timestamp, SGS::US_DATE_FORMAT));
    $excel->getActiveSheet()->SetCellValue('G5', $this->entered_by);
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
      $excel->getActiveSheet()->SetCellValue('F2', 'Operator Name:');
      $excel->getActiveSheet()->SetCellValue('A3', 'Date Registered:');
      $excel->getActiveSheet()->SetCellValue('F3', 'Form Reference No.:');
      $excel->getActiveSheet()->SetCellValue('A4', 'Operator TIN:');
      $excel->getActiveSheet()->SetCellValue('F4', 'Log Measurer:');
      $excel->getActiveSheet()->SetCellValue('A5', 'Date Entered:');
      $excel->getActiveSheet()->SetCellValue('F5', 'Entered By:');
      $excel->getActiveSheet()->SetCellValue('A6', 'Original Log Barcode');
      $excel->getActiveSheet()->SetCellValue('B6', 'Species Code');
      $excel->getActiveSheet()->SetCellValue('C6', 'New Cross Cut Barcode');
      $excel->getActiveSheet()->SetCellValue('D6', 'Diameter (cm underbark to the nearest cm)');
      $excel->getActiveSheet()->SetCellValue('H6', 'Length (m) to the nearest 0.1m');
      $excel->getActiveSheet()->SetCellValue('I6', 'Volume (m3)');
      $excel->getActiveSheet()->SetCellValue('J6', 'Action');
      $excel->getActiveSheet()->SetCellValue('K6', 'Comment');
      $excel->getActiveSheet()->SetCellValue('D7', 'Butt');
      $excel->getActiveSheet()->SetCellValue('F7', 'Top');
      $excel->getActiveSheet()->SetCellValue('D8', 'Max');
      $excel->getActiveSheet()->SetCellValue('E8', 'Min');
      $excel->getActiveSheet()->SetCellValue('F8', 'Max');
      $excel->getActiveSheet()->SetCellValue('G8', 'Min');
    }

    $excel->getActiveSheet()->SetCellValue('B2', substr($values['site_name'], 0 , 3).'/'.$values['site_name']);
    $excel->getActiveSheet()->SetCellValue('G2', $values['operator_name']);
    $excel->getActiveSheet()->SetCellValue('B3', SGS::date($args['create_date'], SGS::US_DATE_FORMAT));
    $excel->getActiveSheet()->SetCellValue('G3', $values['form_number']);
    $excel->getActiveSheet()->SetCellValue('B4', $values['operator_tin']);
    $excel->getActiveSheet()->SetCellValue('G4', $values['measured_by']);
    $excel->getActiveSheet()->SetCellValue('B5', SGS::date('now', SGS::US_DATE_FORMAT));
    $excel->getActiveSheet()->SetCellValue('G5', $values['entered_by']);
  }

  public function make_suggestions($values, $errors) {
    $suggestions = array();
    foreach ($errors as $field => $options) {
      extract($options);
      switch ($field) {
        case 'barcode':
          $args = array(
            'barcodes.type' => array('F', 'L', 'P'), // could be either type, but must be assigned correct one later
            'operators.id' => SGS::suggest_operator($values['operator_tin'], array(), 'id')
          );
          $suggest = SGS::suggest_barcode($values[$field], $args, 'barcode', TRUE, $min_length ?: 5, $min_similarity ?: 0.3, $max_distance ?: 3, $limit ?: 5, $offset ?: 0, $min_length ?: 2, $limit ?: 20, $offset ?: 0);
          break;
        case 'parent_barcode':
          $args = array(
            'barcodes.type' => array('F', 'L', 'P'), // could be either type, but must be assigned correct one later
            'operators.id' => SGS::suggest_operator($values['operator_tin'], array(), 'id')
          );
          $suggest = SGS::suggest_barcode($values[$field], $args, 'barcode', TRUE, $min_length ?: 5, $min_similarity ?: 0.3, $max_distance ?: 3, $limit ?: 5, $offset ?: 0, $min_length ?: 2, $limit ?: 20, $offset ?: 0);
          break;
        case 'operator_tin':
          $args = array(
            'sites.id' => SGS::suggest_site($values['site_name'], array(), 'id'),
          );
          $suggest = SGS::suggest_operator($values[$field], $args, 'tin', TRUE, $min_length ?: 5, $min_similarity ?: 0.3, $max_distance ?: 3, $limit ?: 10, $offset ?: 0, $min_length ?: 5, $limit ?: 10, $offset ?: 0);
          break;
        case 'site_name':
          $args = array(
            'operators.id' => SGS::suggest_operator($values['operator_tin'], array(), 'id')
          );
          $suggest = SGS::suggest_site($values[$field], $args, 'name', TRUE, $min_length ?: 2, $min_similarity ?: 0.3, $max_distance ?: 3, $limit ?: 10, $offset ?: 0, $min_length ?: 5, $limit ?: 10, $offset ?: 0);
          break;
        case 'species_code':
          $suggest = SGS::suggest_species($values[$field], array(), 'code', TRUE, $min_length ?: 2, $min_similarity ?: 0.3, $max_distance ?: 3, $limit ?: 10, $offset ?: 0, $min_length ?: 2, $limit ?: 10, $offset ?: 0);
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

    // everything else
    $query = DB::select('id')
      ->from($this->_table_name)
      ->where('bottom_min', 'BETWEEN', SGS::variance_range(SGS::floatify($values['bottom_min']), SGS::accuracy(self::$type, 'is_matching_diameter')))
      ->and_where('bottom_max', 'BETWEEN', SGS::variance_range(SGS::floatify($values['bottom_max']), SGS::accuracy(self::$type, 'is_matching_diameter')))
      ->and_where('top_min', 'BETWEEN', SGS::variance_range(SGS::floatify($values['top_min']), SGS::accuracy(self::$type, 'is_matching_diameter')))
      ->and_where('top_max', 'BETWEEN', SGS::variance_range(SGS::floatify($values['top_max']), SGS::accuracy(self::$type, 'is_matching_diameter')))
      ->and_where('length', 'BETWEEN', SGS::variance_range(SGS::floatify($values['length'], 1), SGS::accuracy(self::$type, 'is_matching_length')))
      ->and_where('volume', 'BETWEEN', SGS::variance_range(SGS::quantitify($values['volume']), SGS::accuracy(self::$type, 'is_matching_volume')));

    if ($species_id  = SGS::lookup_species($values['species_code'], TRUE)) $query->and_where('species_id', '=', $species_id);
    if ($operator_id = SGS::lookup_operator($values['operator_tin'], TRUE)) $query->and_where('operator_id', '=', $operator_id);
    if ($site_id     = SGS::lookup_site($values['site_name'], TRUE)) $query->and_where('site_id', '=', $site_id);

    if ($results = $query->execute()->as_array(NULL, 'id')) foreach (array_filter(array_unique($results)) as $duplicate) $duplicates[] = $duplicate;
    return $duplicates;
  }

  public function run_checks() {
    $this->reset_checks();

    $errors    = array();
    $warnings  = array();
    $successes = array();

    // reliability
    if (!($this->operator_id == $this->barcode->printjob->site->operator_id)) $warnings['barcode_id']['is_consistent_operator'] = array('value' => $this->operator->tin, 'comparison' => $this->barcode->printjob->site->operator->tin);
    if (!($this->operator_id == $this->parent_barcode->printjob->site->operator_id)) $warnings['parent_barcode_id']['is_consistent_operator'] = array('value' => $this->operator->tin, 'comparison' => $this->parent_barcode->printjob->site->operator->tin);
    if (!($this->operator_id == $this->site->operator_id)) $warnings['site_id']['is_consistent_operator'] = array('value' => $this->operator->tin, 'comparison' => $this->site->operator->tin);
    if (!(in_array('is_consistent_operator', SGS::flattenify($errors + $warnings)))) $successes['operator_id']['is_consistent_operator'] = array('value' => $this->operator->tin, 'comparison' => $this->operator->tin);

    if (!($this->site_id == $this->barcode->printjob->site_id)) $warnings['barcode_id']['is_consistent_site'] = array('value' => $this->site->name, 'comparison' => $this->barcode->printjob->site->name);
    if (!($this->site_id == $this->parent_barcode->printjob->site_id)) $warnings['parent_barcode_id']['is_consistent_site'] = array('value' => $this->site->name, 'comparison' => $this->parent_barcode->printjob->site->name);
    if (!(in_array($this->site, $this->operator->sites->find_all()->as_array()))) $warnings['operator_id']['is_consistent_site'] = array('value' => $this->site->name);
    if (!(in_array('is_consistent_site', SGS::flattenify($errors + $warnings)))) $successes['site_id']['is_consistent_site'] = array('value' => $this->site->name, 'comparison' => $this->site->name);

    // consistency
    switch ($this->barcode->type) {
      case 'L': $successes['barcode_id']['is_valid_barcode'] = array('value' => SGS::$barcode_type[$this->barcode->type], 'comparison' => SGS::$barcode_type['L']); break;
      default:  $warnings['barcode_id']['is_valid_barcode'] = array('value' => SGS::$barcode_type[$this->barcode->type], 'comparison' => SGS::implodify(array(SGS::$barcode_type['F'], SGS::$barcode_type['L']))); break;
    }

    switch ($this->parent_barcode->type) {
      case 'F': $successes['parent_barcode_id']['is_valid_parent_barcode'] = array('value' => SGS::$barcode_type[$this->parent_barcode->type], 'comparison' => SGS::$barcode_type['F']); break;
      default:  $warnings['parent_barcode_id']['is_valid_parent_barcode'] = array('value' => SGS::$barcode_type[$this->barcode->type], 'comparison' => SGS::implodify(array(SGS::$barcode_type['F'], SGS::$barcode_type['L']))); break;
    }

    // traceability
    $parent = $this->parent(array('TDF','LDF'));

    if ($parent and $parent->loaded()) {
      if ($parent->status == 'P') $parent->run_checks();
      if ($parent->status != 'A') $errors['parent_barcode_id']['is_valid_parent'] = array('value' => SGS::$data_status[$this->status], 'comparison' => SGS::$data_status[$parent->status]);
      else $successes['parent_barcode_id']['is_valid_parent'] = array('value' => SGS::$data_status[$this->status], 'comparison' => SGS::$data_status[$parent->status]);

      if (!(ord($this->species->class) <= ord($parent->species->class))) $errors['species_id']['is_matching_species'] = array('value' => $this->species->class, 'comparison' => $parent->species->class);
      else if (!($this->species->code == $parent->species->code)) $warnings['species_id']['is_matching_species'] = array('value' => $this->species->code, 'comparison' => $parent->species->code);

      if (!($this->operator_id == $parent->operator_id)) $warnings['operator_id']['is_matching_operator'] = array('value' => $this->operator->tin, 'comparison' => $parent->operator->tin);
      if (!($this->site_id == $parent->site_id)) $warnings['site_id']['is_matching_site'] = array('value' => $this->site->name, 'comparison' => $parent->site->name);
      if ($warnings['operator_id']['is_matching_operator'] and $warnings['site_id']['is_matching_site']) {
        $errors['site_id']['is_matching_site'] = $warnings['site_id']['is_matching_site'];
        $errors['operator_id']['is_matching_operator'] = $warnings['operator_id']['is_matching_operator'];
        unset($warnings['site_id']['is_matching_site']);
        unset($warnings['operator_id']['is_matching_operator']);
      }

      $siblings = array(
        'length'   => 0,
        'diameter' => 0,
        'volume'   => 0
      );

      $siblngs = $this->siblings('LDF');
      if ($siblngs) {
        foreach ($siblngs as $child) {
          $siblings['length']   += $child->length;
          $siblings['diameter'] += $child->diameter;
          $siblings['volume']   += $child->volume;
          if (($child->bottom_diameter > $siblings['bottom_diameter']) or !$siblings['top_diameter']) $siblings['bottom_diameter'] = $child->bottom_diameter;
          if (($child->top_diameter < $siblings['top_diameter']) or !$siblings['top_diameter']) $siblings['top_diameter'] = $child->top_diameter;
        }

        $siblings['diameter'] = (float) SGS::floatify($siblings['diameter'] / count($siblngs));
        $siblings['volume']   = (float) SGS::quantitify($siblings['volume'] / count($siblngs));

        if (!Valid::is_accurate($siblings['volume'], $parent->volume, SGS::tolerance('LDF', 'is_matching_volume'), FALSE)) $errors['volume']['is_matching_volume'] = array('value' => $siblings['volume'], 'comparison' => $parent->volume);
        else if (!Valid::is_accurate($siblings['volume'], $parent->volume, SGS::accuracy('LDF', 'is_matching_volume'))) $warnings['volume']['is_matching_volume'] = array('value' => $siblings['volume'], 'comparison' => $parent->volume);

        if (!Valid::is_accurate($siblings['length'], $parent->length, SGS::tolerance('LDF', 'is_matching_length'), FALSE)) $errors['length']['is_matching_length'] = array('value' => $siblings['length'], 'comparison' => $parent->length);
        else if (!Valid::is_accurate($siblings['length'], $parent->length, SGS::accuracy('LDF', 'is_matching_length'))) $warnings['length']['is_matching_length'] = array('value' => $siblings['length'], 'comparison' => $parent->length);

        if (!Valid::is_accurate($siblings['diameter'], $parent->diameter, SGS::tolerance('LDF', 'is_matching_diameter'))) {
          $errors['top_min']['is_matching_diameter'] = array('value' => $siblings['diameter'], 'comparison' => $parent->diameter);
          $errors['top_max']['is_matching_diameter'] = array('value' => $siblings['diameter'], 'comparison' => $parent->diameter);
          $errors['bottom_min']['is_matching_diameter'] = array('value' => $siblings['diameter'], 'comparison' => $parent->diameter);
          $errors['bottom_max']['is_matching_diameter'] = array('value' => $siblings['diameter'], 'comparison' => $parent->diameter);
        }
        else if (count($siblngs) > 1) {
          usort($siblngs, function($a, $b) { return $a->bottom_diameter < $b->bottom_diameter ? 1 : -1; });
          for ($s = 1; $s < count($siblngs); $s++)
          if (!Valid::is_accurate($siblngs[$s-1]->top_diameter, $siblngs[$s]->bottom_diameter, 10, TRUE, FALSE))
            if ($siblngs[$s-1]->id == $this->id) {
              $errors['top_min']['is_matching_diameter'] = array('value' => $siblngs[$s-1]->top_diameter, 'comparison' => $siblngs[$s]->bottom_diameter);
              $errors['top_max']['is_matching_diameter'] = array('value' => $siblngs[$s-1]->top_diameter, 'comparison' => $siblngs[$s]->bottom_diameter);
              $errors['bottom_min']['is_matching_diameter'] = array('value' => $siblngs[$s-1]->top_diameter, 'comparison' => $siblngs[$s]->bottom_diameter);
              $errors['bottom_max']['is_matching_diameter'] = array('value' => $siblngs[$s-1]->top_diameter, 'comparison' => $siblngs[$s]->bottom_diameter);
            } else if ($siblngs[$s]->id == $this->id) {
              $errors['top_min']['is_matching_diameter'] = array('value' => $siblngs[$s]->bottom_diameter, 'comparison' => $siblngs[$s-1]->top_diameter);
              $errors['top_max']['is_matching_diameter'] = array('value' => $siblngs[$s]->bottom_diameter, 'comparison' => $siblngs[$s-1]->top_diameter);
              $errors['bottom_min']['is_matching_diameter'] = array('value' => $siblngs[$s]->bottom_diameter, 'comparison' => $siblngs[$s-1]->top_diameter);
              $errors['bottom_max']['is_matching_diameter'] = array('value' => $siblngs[$s]->bottom_diameter, 'comparison' => $siblngs[$s-1]->top_diameter);
            }
        }
        else if (!Valid::is_accurate($siblings['diameter'], $parent->diameter, SGS::accuracy('LDF', 'is_matching_diameter'))) {
          $warnings['top_min']['is_matching_diameter'] = array('value' => $siblings['diameter'], 'comparison' => $parent->diameter);
          $warnings['top_max']['is_matching_diameter'] = array('value' => $siblings['diameter'], 'comparison' => $parent->diameter);
          $warnings['bottom_min']['is_matching_diameter'] = array('value' => $siblings['diameter'], 'comparison' => $parent->diameter);
          $warnings['bottom_max']['is_matching_diameter'] = array('value' => $siblings['diameter'], 'comparison' => $parent->diameter);
        }
      }
      $successes['parent_barcode_id']['is_existing_parent'] = array('value' => 'Found', 'comparison' => 'Found');
    }
    else {
      $errors['parent_barcode_id']['is_existing_parent'] = array('value' => 'Found', 'comparison' => 'Not Found');
      $errors['parent_barcode_id']['is_valid_parent'] = array('value' => 'Found', 'comparison' => 'Not Found');
    }

    /*** all tolerance checks fail if any traceability checks fail
    foreach ($errors as $array) if (array_intersect(array_keys($array), array_keys(self::$checks['traceability']['checks']))) {
      foreach (self::$checks['tolerance']['checks'] as $check => $array) $errors['parent_barcode_id'][$check] = array();
      break;
    } ***/

    // tolerance successes checks
    if (is_object($parent) and $parent->loaded()) {
      if (!(in_array('is_matching_operator', SGS::flattenify($errors + $warnings)))) $successes['operator_id']['is_matching_operator'] = array('value' => $this->operator->tin, 'comparison' => $parent->operator->tin);
      if (!(in_array('is_matching_site', SGS::flattenify($errors + $warnings)))) $successes['site_id']['is_matching_site'] = array('value' => $this->site->name, 'comparison' => $parent->site->name);
      if (!(in_array('is_matching_species', SGS::flattenify($errors + $warnings)))) $successes['species_id']['is_matching_species'] = array('value' => $this->species->code, 'comparison' => $parent->species->code);

      if (!(in_array('is_matching_length', SGS::flattenify($errors + $warnings)))) $successes['length']['is_matching_length'] = array('value' => $siblings['length'], 'comparison' => $parent->length);
      if (!(in_array('is_matching_volume', SGS::flattenify($errors + $warnings)))) $successes['volume']['is_matching_volume'] = array('value' => $siblings['volume'], 'comparison' => $parent::$type == 'LDF' ? $parent->volume : 'N/A');

      if (!(in_array('is_matching_diameter', SGS::flattenify($errors + $warnings)))) {
        $successes['top_min']['is_matching_diameter'] = array('value' => $siblings['diameter'], 'comparison' => $parent->diameter);
        $successes['top_max']['is_matching_diameter'] = array('value' => $siblings['diameter'], 'comparison' => $parent->diameter);
        $successes['bottom_min']['is_matching_diameter'] = array('value' => $siblings['diameter'], 'comparison' => $parent->diameter);
        $successes['bottom_max']['is_matching_diameter'] = array('value' => $siblings['diameter'], 'comparison' => $parent->diameter);
      }
    }

    if ($successes) foreach ($successes as $field => $array) {
      foreach ($array as $success => $params) $this->set_success($field, $success, $params);
    }

    if ($warnings) foreach ($warnings as $field => $array) {
      foreach ($array as $warning => $params) $this->set_warning($field, $warning, $params);
    }

    if ($errors) {
      $this->status = 'R';
      foreach ($errors as $field => $array) {
        foreach ($array as $error => $params) $this->set_error($field, $error, $params);
      }
    } else $this->status = 'A';

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
      'measured_by'        => self::$fields['measured_by'],
      'entered_by'         => self::$fields['entered_by'],
      'form_number'        => self::$fields['form_number'],
      'bottom_min'         => self::$fields['bottom_min'],
      'bottom_max'         => self::$fields['bottom_max'],
      'top_min'            => self::$fields['top_min'],
      'top_max'            => self::$fields['top_max'],
      'length'             => self::$fields['length'],
      'volume'             => self::$fields['volume'],
      'action'             => self::$fields['action'],
      'comment'            => self::$fields['comment'],
//      'user_id'         => self::$fields['user_id'],
//      'timestamp'       => self::$fields['timestamp'],
    );
  }

}