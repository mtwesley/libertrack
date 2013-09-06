<?php

class Model_WB extends SGS_Form_ORM {

  const PARSE_START = 12;

  protected $_table_name = 'wb_data';

  protected $_belongs_to = array(
    'operator'  => array(),
    'transport_operator' => array(
      'model'       => 'operator',
      'foreign_key' => 'transport_operator_id'
    ),
    'barcode'  => array(),
    'wb_barcode'  => array(
      'model'       => 'barcode',
      'foreign_key' => 'wb_barcode_id'),
    'species'  => array(),
    'user'     => array(),
  );

  protected $_ignored_columns = array();

  protected function _initialize()
  {
    parent::_initialize();
    $this->_object_plural = 'wb';
  }

  public function __get($column) {
    switch ($column) {
      case 'volume':
        return SGS::volumify(($this->diameter / 100), $this->length);

      default:
        return parent::__get($column);
    }
  }

  public function set($column, $value) {
    switch ($column) {
      case 'volume':
        if ($this->original_volume == NULL) $this->original_volume = $value;
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

    foreach ($this->_object as $field => $value) switch ($field) {
      case 'volume':
        if ($value == NULL) $this->$field = $this->$field;
    }

    parent::save($validation);
  }

  public static $type      = 'WB';
  public static $data_type = 'WB';
  public static $verification_type = 'LDFV';

  public static $fields = array(
    'create_date'      => 'Date',
    'operator_tin'     => 'Operator TIN',
    'transport_operator_tin' => 'Transportor TIN',
    'origin'           => 'Port of Origin',
    'destination'      => 'Port of Destination',
    'origin_date'      => 'Leaving Date',
    'destination_date' => 'Arrival Date',
    'unloading_date'   => 'Unloading Date',
    'loading_supervised_by' => 'Loading Supervisor',
    'receiving_supervised_by' => 'Receiving Supervisor',
    'driver'           => 'Driver',
    'truck_number'     => 'Truck License Number',
    'entered_by'       => 'Entered By',
    'wb_barcode'       => 'Waybill Barcode',
    'barcode'          => 'Log Barcode',
    'species_code'     => 'Species Code',
    'diameter'         => 'Average Diameter',
    'length'           => 'Length',
    'grade'            => 'Grade',
    'volume'           => 'Volume',
    'comment'          => 'Comment',
  );

 public static $checks = array(
    'consistency' => array(
      'title'  => 'Data Consistency',
      'checks' => array(
        'is_valid_barcode' => array(
          'name'    => 'Log Barcode Assignment',
          'title'   => 'Log barcode assignment is valid',
          'error'   => 'Log barcode assignment is invalid',
          'warning' => 'Log barcode is not yet assigned',
         ),
        'is_valid_wb_barcode' => array(
          'name'    => 'Waybill Barcode Assignment',
          'title'   => 'Waybill barcode assignment is valid',
          'error'   => 'Waybill barcode assignment is invalid',
          'warning' => 'Waybill barcode is not yet assigned',
         ),
    )),
    'reliability' => array(
      'title'  => 'Data Reliability',
      'checks' => array(
        'is_consistent_operator' => array(
          'name'    => 'Log Operator Assignments',
          'title'   => 'Log operator assignments are consistent',
          'warning' => 'Log operator assignments are inconsistent'
        )
    )),
    'traceability' => array(
      'title'  => 'Traceability',
      'checks' => array(
        'is_existing_parent' => array(
          'name'  => 'Tracebale Log',
          'title' => 'Traceable to LDF',
          'error' => 'Not tracable to LDF'
        ),
        'is_valid_parent' => array(
          'name'  => 'Log Status',
          'title' => 'LDF record passed checks and queries',
          'error' => 'LDF record failed checks and queries'
        )
    )),
    'tolerance' => array(
      'title'  => 'Tolerance',
      'checks' => array(
        'is_matching_species' => array(
          'name'    => 'Species',
          'title'   => 'Species matches LDF record data',
          'error'   => 'Species does not match LDF record data',
          'warning' => 'Species class matches LDF record data, but species code does not'
        ),
        'is_matching_length' => array(
          'name'    => 'Length',
          'title'   => 'Length matches LDF record data',
          'error'   => 'Length does not match LDF record data',
          'warning' => 'Length matches LDF record data, but is inaccurate'
        ),
        'is_matching_diameter' => array(
          'name'    => 'Diameter',
          'title'   => 'Diameter matches LDF record data',
          'error'   => 'Diameter does not match LDF record data',
          'warning' => 'Diameter matches LDF record data, but is inaccurate'
        ),
        'is_matching_volume' => array(
          'name'    => 'Volume',
          'title'   => 'Volume matches LDF record data',
          'error'   => 'Volume does not match LDF record data',
          'warning' => 'Volume matches LDF record data, but is inaccurate'
        ),
        'is_matching_operator' => array(
          'name'    => 'Log Operator',
          'title'   => 'Log operator matches LDF record data',
          'error'   => 'Log operator does not match LDF record data',
          'warning' => 'Log operator does not match LDF record data',
        ),
    )),
  );

  public static function fields()
  {
    return (array) self::$fields;
  }

  public function formo() {
    $array = array(
      'id'                 => array('render' => FALSE),
      'create_date'        => array('order' => 0, 'attr' => array('class' => 'dpicker')),
      'origin_date'        => array('attr' => array('class' => 'dpicker')),
      'destination_date'   => array('attr' => array('class' => 'dpicker')),
      'unloading_date'     => array('attr' => array('class' => 'dpicker')),
      'wb_barcode'         => array('render' => FALSE),
      'barcode'            => array('render' => FALSE),
      'operator'           => array('render' => FALSE),
      'transport_operator' => array('render' => FALSE),
      'original_volume'    => array('render' => FALSE),
      'status'             => array('render' => FALSE),
      'user'               => array('render' => FALSE),
      'timestamp'          => array('render' => FALSE),
      'species'            => array(
        'orm_primary_val' => 'code',
        'label' => 'Species'
      ),
      'grade' => array(
        'driver'  => 'forceselect',
        'options' => SGS::$grade
      ),
    );
    foreach (self::fields() as $field => $label) {
      $array[$field]['label'] = $label;
    }
    return $array;
  }

  public function parse_csv($row, &$csv)
  {
    extract(SGS::parse_grade(trim($row[F])));
    $data = array(
      'barcode'         => SGS::barcodify(trim($row[B])),
      'species_code'    => trim($row[C]),
      'diameter'        => trim($row[D]),
      'length'          => trim($row[E]),
      'grade'           => $grade,
      'volume'          => trim($row[G]),
    );

    if (array_filter($data)) return SGS::cleanify(array(
      'create_date'     => SGS::date(trim($csv[8][C] ?: $csv[8][D]), SGS::US_DATE_FORMAT, TRUE, TRUE),
      'operator_tin'    => trim($csv[2][G] ?: $csv[2][H]),
      'origin'          => trim($csv[3][C] ?: $csv[3][D]),
      'destination'     => trim($csv[4][C] ?: $csv[4][D]),
      'transport_operator_tin'  => trim($csv[3][G] ?: $csv[3][H]),
      'origin_date'      => SGS::date(trim($csv[4][G] ?: $csv[4][H]), SGS::US_DATE_FORMAT, TRUE, TRUE),
      'destination_date' => SGS::date(trim($csv[5][G] ?: $csv[5][H]), SGS::US_DATE_FORMAT, TRUE, TRUE),
      'unloading_date'   => SGS::date(trim($csv[6][G] ?: $csv[6][H]), SGS::US_DATE_FORMAT, TRUE, TRUE),
      'loading_supervised_by'   => trim($csv[5][C] ?: $csv[5][D]),
      'receiving_supervised_by' => trim($csv[6][C] ?: $csv[6][D]),
      'entered_by'      => trim($csv[8][G] ?: $csv[8][H]),
      'wb_barcode'      => SGS::barcodify(trim($csv[2][C] ?: $csv[2][D])),
      'exp_barcode'     => SGS::barcodify(trim($csv[3][C] ?: $csv[3][D])),
    ) + $data + array(
      'comment'         => trim($row[G]),
    ));
  }

  public function parse_data($data)
  {
    foreach ($data as $key => $value) switch ($key) {
      case 'operator_tin':
        $this->operator = SGS::lookup_operator($value); break;

      case 'transport_operator_tin':
        $this->transport_operator = SGS::lookup_operator($value); break;

      case 'barcode':
      case 'wb_barcode':
        $this->$key = SGS::lookup_barcode(SGS::barcodify($value)); break;

      case 'species_code':
        $this->species = SGS::lookup_species($value); break;

      case 'create_date':
        $this->$key = SGS::date($value, SGS::PGSQL_DATE_FORMAT); break;

      case 'diameter':
        $this->$key = SGS::floatify($data['diameter']); break;

      case 'length':
        $this->$key = SGS::floatify($value, 1); break;

      case 'volume':
        $this->$key = SGS::quantitify($value); break;

      default:
        try { $this->$key = $value; } catch (Exception $e) {} break;
    }
  }

  public function export_data($excel, $row) {
    switch ($this->grade) {
      case 'LM':
      case 'A':
      case 'AB':
      case 'B':
      case 'BC':
      case 'C':
      case 'D':
        $grade = 'Logs/'.$this->grade; break;

      case '1':
      case '2':
      case '3':
      case 'FAS':
      case 'CG':
        $grade = 'Sawnwood/'.$this->grade; break;
    }

    $excel->getActiveSheet()->SetCellValue('B'.$row, $this->barcode->barcode);
    $excel->getActiveSheet()->SetCellValue('C'.$row, $this->species->code);
    $excel->getActiveSheet()->SetCellValue('D'.$row, $this->diameter);
    $excel->getActiveSheet()->SetCellValue('E'.$row, $this->length);
    $excel->getActiveSheet()->SetCellValue('F'.$row, $grade);
    $excel->getActiveSheet()->SetCellValue('G'.$row, $this->volume);
    $excel->getActiveSheet()->SetCellValue('H'.$row, $this->comment);
  }

  public function export_headers($excel, $args, $headers = TRUE) {
    if ($headers) {
      $excel->getActiveSheet()->SetCellValue('A1', 'LOG WAYBILL FORM');
      $excel->getActiveSheet()->SetCellValue('A2', 'Waybill Barcode:');
      $excel->getActiveSheet()->SetCellValue('E2', 'Log Owner TIN:');
      $excel->getActiveSheet()->SetCellValue('A3', 'Location of Origin:');
      $excel->getActiveSheet()->SetCellValue('E3', 'Transportation Company TIN:');
      $excel->getActiveSheet()->SetCellValue('A4', 'Location of Destination:');
      $excel->getActiveSheet()->SetCellValue('E4', 'Date Leaving Origin:');
      $excel->getActiveSheet()->SetCellValue('A5', 'Loading Supervisor Name:');
      $excel->getActiveSheet()->SetCellValue('E5', 'Intended Arrival Date at Destination:');
      $excel->getActiveSheet()->SetCellValue('A6', 'Receiving Supervisor Name:');
      $excel->getActiveSheet()->SetCellValue('E6', 'Date Unloaded:');
      $excel->getActiveSheet()->SetCellValue('A7', 'Truck License No.:');
      $excel->getActiveSheet()->SetCellValue('E7', 'Driver:');
      $excel->getActiveSheet()->SetCellValue('A8', 'Date Entered:');
      $excel->getActiveSheet()->SetCellValue('A1', 'LOG WAYBILL FORM');
      $excel->getActiveSheet()->SetCellValue('E8', 'Entered By:');
      $excel->getActiveSheet()->SetCellValue('A9', 'LOGS INCLUDED IN THE LOAD');
      $excel->getActiveSheet()->SetCellValue('A10', 'No.');
      $excel->getActiveSheet()->SetCellValue('B10', 'Log Barcode');
      $excel->getActiveSheet()->SetCellValue('C10', 'Species Code');
      $excel->getActiveSheet()->SetCellValue('D10', 'Average Diameter');
      $excel->getActiveSheet()->SetCellValue('E10', 'Length (m)');
      $excel->getActiveSheet()->SetCellValue('F10', 'ATIBT Grade');
      $excel->getActiveSheet()->SetCellValue('G10', 'Volume');
      $excel->getActiveSheet()->SetCellValue('H10', 'Comments');
    }

    $excel->getActiveSheet()->SetCellValue('C2', $this->wb_barcode->barcode);
    $excel->getActiveSheet()->SetCellValue('G2', $this->operator->tin);
    $excel->getActiveSheet()->SetCellValue('C3', $this->origin);
    $excel->getActiveSheet()->SetCellValue('G3', $this->transport_operator_tin);
    $excel->getActiveSheet()->SetCellValue('C4', $this->destination);
    $excel->getActiveSheet()->SetCellValue('G4', SGS::date($this->origin_date, SGS::US_DATE_FORMAT));
    $excel->getActiveSheet()->SetCellValue('C5', $this->loading_supervised_by);
    $excel->getActiveSheet()->SetCellValue('G5', SGS::date($this->destination_date, SGS::US_DATE_FORMAT));
    $excel->getActiveSheet()->SetCellValue('C6', $this->receiving_supervised_by);
    $excel->getActiveSheet()->SetCellValue('G6', SGS::date($this->unloading_date, SGS::US_DATE_FORMAT));
    $excel->getActiveSheet()->SetCellValue('C7', $this->truck_number);
    $excel->getActiveSheet()->SetCellValue('G7', $this->driver);
    $excel->getActiveSheet()->SetCellValue('C8', SGS::date($args['create_date'], SGS::US_DATE_FORMAT));
    $excel->getActiveSheet()->SetCellValue('G8', $this->entered_by);
  }

  public function download_data($values, $errors, $excel, $row) {
    switch ($this->grade) {
      case 'LM':
      case 'A':
      case 'AB':
      case 'B':
      case 'BC':
      case 'C':
      case 'D':
        $grade = 'Logs/'.$this->grade; break;

      case '1':
      case '2':
      case '3':
      case 'FAS':
      case 'CG':
        $grade = 'Sawnwood/'.$this->grade; break;
    }

    $excel->getActiveSheet()->SetCellValue('B'.$row, $values['barcode']);
    $excel->getActiveSheet()->SetCellValue('C'.$row, $values['species_code']);
    $excel->getActiveSheet()->SetCellValue('D'.$row, $values['diameter']);
    $excel->getActiveSheet()->SetCellValue('E'.$row, $values['length']);
    $excel->getActiveSheet()->SetCellValue('F'.$row, $values['grade']);
    $excel->getActiveSheet()->SetCellValue('G'.$row, $values['volume']);
    $excel->getActiveSheet()->SetCellValue('H'.$row, $values['comment']);
  }

  public function download_headers($values, $excel, $args, $headers = TRUE) {
    if ($headers) {
      $excel->getActiveSheet()->SetCellValue('A1', 'LOG WAYBILL FORM');
      $excel->getActiveSheet()->SetCellValue('A2', 'Waybill Barcode:');
      $excel->getActiveSheet()->SetCellValue('E2', 'Log Owner TIN:');
      $excel->getActiveSheet()->SetCellValue('A3', 'Location of Origin:');
      $excel->getActiveSheet()->SetCellValue('E3', 'Transportation Company TIN:');
      $excel->getActiveSheet()->SetCellValue('A4', 'Location of Destination:');
      $excel->getActiveSheet()->SetCellValue('E4', 'Date Leaving Origin:');
      $excel->getActiveSheet()->SetCellValue('A5', 'Loading Supervisor Name:');
      $excel->getActiveSheet()->SetCellValue('E5', 'Intended Arrival Date at Destination:');
      $excel->getActiveSheet()->SetCellValue('A6', 'Receiving Supervisor Name:');
      $excel->getActiveSheet()->SetCellValue('E6', 'Date Unloaded:');
      $excel->getActiveSheet()->SetCellValue('A7', 'Truck License No.:');
      $excel->getActiveSheet()->SetCellValue('E7', 'Driver:');
      $excel->getActiveSheet()->SetCellValue('A8', 'Date Entered:');
      $excel->getActiveSheet()->SetCellValue('A1', 'LOG WAYBILL FORM');
      $excel->getActiveSheet()->SetCellValue('E8', 'Entered By:');
      $excel->getActiveSheet()->SetCellValue('A9', 'LOGS INCLUDED IN THE LOAD');
      $excel->getActiveSheet()->SetCellValue('A10', 'No.');
      $excel->getActiveSheet()->SetCellValue('B10', 'Log Barcode');
      $excel->getActiveSheet()->SetCellValue('C10', 'Species Code');
      $excel->getActiveSheet()->SetCellValue('D10', 'Average Diameter');
      $excel->getActiveSheet()->SetCellValue('E10', 'Length (m)');
      $excel->getActiveSheet()->SetCellValue('F10', 'ATIBT Grade');
      $excel->getActiveSheet()->SetCellValue('G10', 'Volume');
      $excel->getActiveSheet()->SetCellValue('H10', 'Comments');
    }

    $excel->getActiveSheet()->SetCellValue('C2', $values['wb_barcode']);
    $excel->getActiveSheet()->SetCellValue('G2', $values['operator_tin']);
    $excel->getActiveSheet()->SetCellValue('C3', $values['origin']);
    $excel->getActiveSheet()->SetCellValue('G3', $values['transport_operator_tin']);
    $excel->getActiveSheet()->SetCellValue('C4', $values['destination']);
    $excel->getActiveSheet()->SetCellValue('G4', SGS::date($values['origin_date'], SGS::US_DATE_FORMAT));
    $excel->getActiveSheet()->SetCellValue('C5', $values['loading_supervised_by']);
    $excel->getActiveSheet()->SetCellValue('G5', SGS::date($values['destination_date'], SGS::US_DATE_FORMAT));
    $excel->getActiveSheet()->SetCellValue('C6', $values['receiving_supervised_by']);
    $excel->getActiveSheet()->SetCellValue('G6', SGS::date($values['unloading_date'], SGS::US_DATE_FORMAT));
    $excel->getActiveSheet()->SetCellValue('C7', $values['truck_number']);
    $excel->getActiveSheet()->SetCellValue('G7', $values['driver']);
    $excel->getActiveSheet()->SetCellValue('C8', SGS::date($args['create_date'], SGS::US_DATE_FORMAT));
    $excel->getActiveSheet()->SetCellValue('G8', $values['entered_by']);
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
          $suggest = SGS::suggest_barcode($values[$field], $args, 'barcode', TRUE, $min_length ?: 5, $min_similarity ?: 0.3, $max_distance ?: 3, $limit ?: 5, $offset ?: 0, $min_length ?: 2, $limit ?: 20, $offset ?: 0);
          break;
        case 'wb_barcode':
          $args = array(
            'barcodes.type' => array('W', 'P'),
            'operators.id' => SGS::suggest_operator($values['operator_tin'], array(), 'id')
          );
          $suggest = SGS::suggest_barcode($values[$field], $args, 'barcode', TRUE, $min_length ?: 5, $min_similarity ?: 0.3, $max_distance ?: 3, $limit ?: 5, $offset ?: 0, $min_length ?: 2, $limit ?: 20, $offset ?: 0);
          break;
        case 'operator_tin':
        case 'transport_operator_tin':
          $suggest = SGS::suggest_operator($values[$field], $args, 'tin', TRUE, $min_length ?: 5, $min_similarity ?: 0.3, $max_distance ?: 3, $limit ?: 10, $offset ?: 0, $min_length ?: 5, $limit ?: 10, $offset ?: 0);
          break;
        case 'species_code':
          $suggest = SGS::suggest_species($values[$field], array(), 'code', TRUE, $min_length ?: 2, $min_similarity ?: 0.3, $max_distance ?: 3, $limit ?: 10, $offset ?: 0, $min_length ?: 2, $limit ?: 10, $offset ?: 0);
          break;
      }
      if ($suggest) $suggestions[$field] = $suggest;
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
            ->where($field.'_id', '=', ($val = SGS::lookup_barcode($values['barcode'], NULL, TRUE)) ? $val : NULL)
            ->and_where('wb_barcode_id', '=', ($val = SGS::lookup_barcode($values['wb_barcode'], NULL, TRUE)) ? $val : NULL);

          if ($operator_id = SGS::lookup_operator($values['operator_tin'], TRUE)) $query->and_where('operator_id', '=', $operator_id);

          if ($duplicate = $query->execute()->get('id')) $duplicates[$field] = $duplicate;
          break;
      }
    }

    // everything else
    $query = DB::select('id')
      ->from($this->_table_name)
      ->where('diameter', 'BETWEEN', SGS::variance_range(SGS::floatify($values['diameter']), SGS::accuracy(self::$type, 'is_matching_diameter')))
      ->and_where('length', 'BETWEEN', SGS::variance_range(SGS::floatify($values['length'], 1), SGS::accuracy(self::$type, 'is_matching_length')))
      ->and_where('volume', 'BETWEEN', SGS::variance_range(SGS::quantitify($values['volume']), SGS::accuracy(self::$type, 'is_matching_volume')));

    if ($species_id  = SGS::lookup_species($values['species_code'], TRUE)) $query->and_where('species_id', '=', $species_id);
    if ($operator_id = SGS::lookup_operator($values['operator_tin'], TRUE)) $query->and_where('operator_id', '=', $operator_id);
    if ($transport_operator_id = SGS::lookup_operator($values['transport_operator_tin'], TRUE)) $query->and_where('transport_operator_id', '=', $transport_operator_id);

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
    if (!($this->operator_id == $this->wb_barcode->printjob->site->operator_id)) $warnings['wb_barcode_id']['is_consistent_operator'] = array('value' => $this->operator->tin, 'comparison' => $this->wb_barcode->printjob->site->operator->tin);
    if (!(in_array('is_consistent_operator', SGS::flattenify($errors + $warnings)))) $successes['operator_id']['is_consistent_operator'] = array('value' => $this->operator->tin, 'comparison' => $this->operator->tin);

    // consistency
    switch ($this->barcode->type) {
      case 'L': $successes['barcode_id']['is_valid_barcode'] = array('value' => SGS::$barcode_type[$this->barcode->type], 'comparison' => SGS::$barcode_type['L']); break;
      default:  $warnings['barcode_id']['is_valid_barcode'] = array('value' => SGS::$barcode_type[$this->barcode->type], 'comparison' => SGS::$barcode_type['L']); break;
    }

    switch ($this->wb_barcode->type) {
      case 'W': $successes['wb_barcode_id']['is_valid_wb_barcode'] = array('value' => SGS::$barcode_type[$this->wb_barcode->type], 'comparison' => SGS::$barcode_type['W']); break;
      default:  $warnings['wb_barcode_id']['is_valid_wb_barcode'] = array('value' => SGS::$barcode_type[$this->wb_barcode->type], 'comparison' => SGS::$barcode_type['W']); break;
    }

    $ldf = ORM::factory('LDF')
      ->where('barcode_id', '=', $this->barcode->id)
      ->find();

    if ($ldf and $ldf->loaded()) {
      if ($ldf->status == 'P') $ldf->run_checks();
      if ($ldf->status != 'A') $errors['barcode_id']['is_valid_parent'] = array('value' => SGS::$data_status[$this->status], 'comparison' => SGS::$data_status[$ldf->status]);
      else $successes['barcode_id']['is_valid_parent'] = array('value' => SGS::$data_status[$this->status], 'comparison' => SGS::$data_status[$ldf->status]);

      if (!(ord($this->species->class) <= ord($ldf->species->class))) $errors['species_id']['is_matching_species'] = array('value' => $this->species->class, 'comparison' => $ldf->species->class);
      if (!($this->species->code == $ldf->species->code)) $warnings['species_id']['is_matching_species'] = array('value' => $this->species->code, 'comparison' => $ldf->species->code);
      if (!($this->operator_id == $ldf->operator_id)) $warnings['operator_id']['is_matching_operator'] = array('value' => $this->operator->tin, 'comparison' => $ldf->operator->tin);

      if (!Valid::is_accurate($this->volume, $ldf->volume, SGS::tolerance('SPECS', 'is_matching_volume'), FALSE)) $errors['volume']['is_matching_volume'] = array('value' => $this->volume, 'comparison' => $ldf->volume);
      else if (!Valid::is_accurate($this->volume, $ldf->volume, SGS::accuracy('SPECS', 'is_matching_volume'))) $warnings['volume']['is_matching_volume'] = array('value' => $this->volume, 'comparison' => $ldf->volume);

      if (!Valid::is_accurate($this->length, $ldf->length, SGS::tolerance('SPECS', 'is_matching_length'), FALSE)) $errors['length']['is_matching_length'] = array('value' => $this->length, 'comparison' => $ldf->length);
      else if (!Valid::is_accurate($this->length, $ldf->length, SGS::accuracy('SPECS', 'is_matching_length'))) $warnings['length']['is_matching_length'] = array('value' => $this->length, 'comparison' => $ldf->length);

      if (!Valid::is_accurate($this->diameter, $ldf->diameter, SGS::tolerance('SPECS', 'is_matching_diameter'))) $errors['diameter']['is_matching_diameter'] = array('value' => $this->diameter, 'comparison' => $ldf->diameter);
      else if (!Valid::is_accurate($this->diameter, $ldf->diameter, SGS::accuracy('SPECS', 'is_matching_diameter'))) $warnings['diameter']['is_matching_diameter'] = array('value' => $this->diameter, 'comparison' => $ldf->diameter);

      $successes['barcode_id']['is_existing_parent'] = array('value' => 'Found', 'comparison' => 'Found');
    }
    else {
      $errors['barcode_id']['is_existing_parent'] = array('value' => 'Found', 'comparison' => 'Not Found');
      $errors['barcode_id']['is_valid_parent'] = array('value' => 'Found', 'comparison' => 'Not Found');
    }

    /*** all tolerance checks fail if any traceability checks fail
    foreach ($errors as $array) if (array_intersect(array_keys($array), array_keys(self::$checks['traceability']['checks']))) {
      foreach (self::$checks['tolerance']['checks'] as $check => $array) $errors['barcode_id'][$check] = array();
      break;
    } ***/

    if (is_object($ldf) and $ldf->loaded()) {
      if (!(in_array('is_matching_operator', SGS::flattenify($errors + $warnings)))) $successes['operator_id']['is_matching_operator'] = array('value' => $this->operator->tin, 'comparison' => $ldf->operator->tin);
      if (!(in_array('is_matching_species', SGS::flattenify($errors + $warnings)))) $successes['species_id']['is_matching_species'] = array('value' => $this->species->code, 'comparison' => $ldf->species->code);
      if (!(in_array('is_matching_length', SGS::flattenify($errors + $warnings)))) $successes['length']['is_matching_length'] = array('value' => $this->length, 'comparison' => $ldf->length);
      if (!(in_array('is_matching_volume', SGS::flattenify($errors + $warnings)))) $successes['volume']['is_matching_volume'] = array('value' => $this->volume, 'comparison' => $ldf->volume);
      if (!(in_array('is_matching_diameter', SGS::flattenify($errors + $warnings)))) $successes['diameter']['is_matching_diameter'] = array('value' => $this->diameter, 'comparison' => $ldf->diameter);
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

  public function rules()
  {
    return array(
      'operator_id'           => array(array('not_empty')),
      'transport_operator_id' => array(array('not_empty')),
      'species_id'            => array(array('not_empty')),
      'barcode_id'            => array(array('not_empty'),
                                       array('is_barcode_type', array($this->barcode->type, array('L', 'P'))),
                                       array('is_unique_fields', array($this->_table_name, array('barcode_id', 'wb_barcode_id'), array('barcode_id' => $this->barcode->id, 'wb_barcode_id' => $this->wb_barcode->id), $this->id))),
      'wb_barcode_id'         => array(array('not_empty'),
                                       array('is_barcode_type', array($this->wb_barcode->type, array('W', 'P')))),
      'diameter'              => array(array('not_empty'),
                                       array('is_measurement_int')),
      'length'                => array(array('not_empty'),
                                       array('is_measurement_float')),
      'grade'                 => array(array('not_empty'),
                                       array('is_grade')),
      'volume'                => array(array('not_empty'),
                                       array('is_measurement_float')),
      'origin'                => array('not_empty'),
      'destination'           => array('not_empty'),
      'create_date'           => array(array('not_empty'),
                                       array('is_date')),
      'origin_date'           => array(array('not_empty'),
                                       array('is_date')),
      'destination_date'      => array(array('not_empty'),
                                       array('is_date')),
      'unloading_date'        => array(),
      'comment'               => array(),
      'user_id'               => array(),
      'timestamp'             => array()
    );
  }

  public function csv_rules()
  {
    return array(
      'operator_tin'           => array(array('not_empty'),
                                        array('is_operator_tin'),
                                        array('is_existing_operator')),
      'transport_operator_tin' => array(array('not_empty'),
                                        array('is_operator_tin'),
                                        array('is_existing_operator')),
      'barcode'                => array(array('not_empty'),
                                        array('is_barcode', array(':value', TRUE)),
                                        array('is_existing_barcode')),
      'wb_barcode'             => array(array('is_barcode', array(':value', TRUE)),
                                        array('is_existing_barcode')),
      'species_code'           => array(array('not_empty'),
                                        array('is_species_code'),
                                        array('is_existing_species'))
    );
  }

  public function labels()
  {
    return array(
      'create_date'      => self::$fields['create_date'],
      'operator_id'      => 'Operator',
      'transport_operator_id' => 'Transporter',
      'origin'           => self::$fields['origin'],
      'destination'      => self::$fields['destination'],
      'origin_date'      => self::$fields['origin_date'],
      'destination_date' => self::$fields['destination_date'],
      'loading_supervised_by' => self::$fields['loading_supervised_by'],
      'receiving_supervised_by' => self::$fields['receiving_supervised_by'],
      'driver'           => self::$fields['driver'],
      'truck_number'     => self::$fields['truck_number'],
      'entered_by'       => self::$fields['entered_by'],
      'wb_barcode_id'    => self::$fields['wb_barcode'],
      'barcode_id'       => self::$fields['barcode'],
      'species_id'       => 'Species',
      'diameter'         => self::$fields['diameter'],
      'length'           => self::$fields['length'],
      'grade'            => self::$fields['grade'],
      'volume'           => self::$fields['volume'],
      'comment'          => self::$fields['comment'],
//      'user_id'          => self::$fields['user_id'],
//      'timestamp'        => self::$fields['timestamp'],
    );
  }

}
