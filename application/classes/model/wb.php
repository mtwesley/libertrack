<?php

class Model_WB extends SGS_Form_ORM {

  const PARSE_START = 12;

  protected $_table_name = 'wb_data';

  protected $_belongs_to = array(
    'log_operator'  => array(
      'model'       => 'operator',
      'foreign_key' => 'log_operator_id'
    ),
    'transport_operator'  => array(
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

    parent::save($validation);
  }

  public static $type      = 'WB';
  public static $data_type = 'WB';
  public static $verification_type = 'LDFV';

  public static $fields = array(
    'create_date'      => 'Date',
    'log_operator_tin' => 'Log Owner TIN',
    'transport_operator_tin' => 'Transportation Company TIN',
    'origin'           => 'Port of Origin',
    'destination'      => 'Port of Destination',
    'origin_date'      => 'Leaving Date',
    'destination_date' => 'Arrival Date',
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
    'volume'           => 'Volume',
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
      'wb_barcode'         => array('render' => FALSE),
      'barcode'            => array('render' => FALSE),
      'log_operator'       => array('render' => FALSE),
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
    $data = array(
      'barcode'         => SGS::barcodify(trim($row[B] ?: $row[C])),
      'species_code'    => trim($row[D]),
      'bottom_max'      => trim($row[E]),
      'bottom_min'      => trim($row[F]),
      'top_max'         => trim($row[G]),
      'top_min'         => trim($row[H]),
      'length'          => trim($row[I]),
      'grade'           => $grade,
      'volume'          => trim($row[K]),
    );

    if (array_filter($data)) return SGS::cleanify(array(
      'create_date'     => SGS::date(trim($csv[7][I] ?: $csv[7][J] ?: $csv[7][K]), SGS::US_DATE_FORMAT, TRUE, TRUE),
      'operator_tin'    => trim($csv[4][C] ?: $csv[4][D]),
      'loading_date'    => SGS::date(trim($csv[5][I] ?: $csv[5][J] ?: $csv[5][K]), SGS::US_DATE_FORMAT, TRUE, TRUE),
      'buyer'           => trim($csv[6][I] ?: $csv[6][J] ?: $csv[6][K]),
      'submitted_by'    => trim($csv[7][C] ?: $csv[7][D]),
      'origin'          => trim($csv[5][C] ?: $csv[5][D]),
      'destination'     => trim($csv[6][C] ?: $csv[6][D]),
      'wb_barcode'   => SGS::barcodify(trim($csv[2][C] ?: $csv[2][D])),
      'exp_barcode'     => SGS::barcodify(trim($csv[3][C] ?: $csv[3][D])),
    ) + $data);
  }

  public function parse_data($data)
  {
    foreach ($data as $key => $value) switch ($key) {
      case 'operator_tin':
        $this->operator = SGS::lookup_operator($value); break;

      case 'barcode':
      case 'wb_barcode':
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
    $excel->getActiveSheet()->SetCellValue('D'.$row, $this->species->code);
    $excel->getActiveSheet()->SetCellValue('E'.$row, $this->bottom_max);
    $excel->getActiveSheet()->SetCellValue('F'.$row, $this->bottom_min);
    $excel->getActiveSheet()->SetCellValue('G'.$row, $this->top_max);
    $excel->getActiveSheet()->SetCellValue('H'.$row, $this->top_min);
    $excel->getActiveSheet()->SetCellValue('I'.$row, $this->length);
    $excel->getActiveSheet()->SetCellValue('J'.$row, $grade);
    $excel->getActiveSheet()->SetCellValue('K'.$row, $this->volume);
  }

  public function export_headers($excel, $args, $headers = TRUE) {
    if ($headers) {
      $excel->getActiveSheet()->SetCellValue('A1', 'Export Shipment Specification Form - Logs');
      $excel->getActiveSheet()->SetCellValue('I1', 'SF19C-1'); // don't know what this is for
      $excel->getActiveSheet()->SetCellValue('A2', 'Shipment Specification Number');
      $excel->getActiveSheet()->SetCellValue('A3', 'Export Permit Number');
      $excel->getActiveSheet()->SetCellValue('E3', 'Contract Number');
      $excel->getActiveSheet()->SetCellValue('A4', 'Exporter TIN');
      $excel->getActiveSheet()->SetCellValue('E4', 'Exporter Company Name');
      $excel->getActiveSheet()->SetCellValue('A5', 'Port of origin');
      $excel->getActiveSheet()->SetCellValue('E5', 'Expecting loading date:');
      $excel->getActiveSheet()->SetCellValue('A6', 'Port of Destination');
      $excel->getActiveSheet()->SetCellValue('E6', 'Buyer');
      $excel->getActiveSheet()->SetCellValue('A7', 'Submitted by');
      $excel->getActiveSheet()->SetCellValue('E7', 'Date');
      $excel->getActiveSheet()->SetCellValue('A8', 'PRODUCT SPECIFICATION - LOGS');
      $excel->getActiveSheet()->SetCellValue('B9', 'Log Barcode');
      $excel->getActiveSheet()->SetCellValue('D9', 'Species Code');
      $excel->getActiveSheet()->SetCellValue('E9', 'Diameter (underbark to nearest cm)');
      $excel->getActiveSheet()->SetCellValue('I9', 'Length (m) to nearest 0.1m');
      $excel->getActiveSheet()->SetCellValue('J9', 'ATIBT Grade');
      $excel->getActiveSheet()->SetCellValue('K9', 'Volume');
    }

    $excel->getActiveSheet()->SetCellValue('C2', $this->wb_barcode->barcode);
    $excel->getActiveSheet()->SetCellValue('C3', $this->exp_barcode->barcode);
//    $excel->getActiveSheet()->SetCellValue('I3', $this->contract_number);
    $excel->getActiveSheet()->SetCellValue('I2', $this->wb_number);
    $excel->getActiveSheet()->SetCellValue('I3', $this->exp_number);
    $excel->getActiveSheet()->SetCellValue('C4', $this->operator->tin);
    $excel->getActiveSheet()->SetCellValue('I4', $this->operator->name);
    $excel->getActiveSheet()->SetCellValue('C5', $this->origin);
    $excel->getActiveSheet()->SetCellValue('I5', $this->loading_date);
    $excel->getActiveSheet()->SetCellValue('C6', $this->destination);
    $excel->getActiveSheet()->SetCellValue('I6', $this->buyer);
    $excel->getActiveSheet()->SetCellValue('C7', $this->submitted_by);
    $excel->getActiveSheet()->SetCellValue('I7', SGS::date($args['create_date'], SGS::US_DATE_FORMAT));
  }

  public function download_data($values, $errors, $excel, $row) {
    switch ($values['grade']) {
      case 'LM':
      case 'A':
      case 'AB':
      case 'B':
      case 'BC':
      case 'C':
      case 'D':
        $grade = 'Logs/'.$values['grade']; break;

      case '1':
      case '2':
      case '3':
      case 'FAS':
      case 'CG':
        $grade = 'Sawnwood/'.$values['grade']; break;
    }

    $excel->getActiveSheet()->SetCellValue('B'.$row, $values['barcode']);
    $excel->getActiveSheet()->SetCellValue('D'.$row, $values['species_code']);
    $excel->getActiveSheet()->SetCellValue('E'.$row, $values['bottom_max']);
    $excel->getActiveSheet()->SetCellValue('F'.$row, $values['bottom_min']);
    $excel->getActiveSheet()->SetCellValue('G'.$row, $values['top_max']);
    $excel->getActiveSheet()->SetCellValue('H'.$row, $values['top_min']);
    $excel->getActiveSheet()->SetCellValue('I'.$row, $values['length']);
    $excel->getActiveSheet()->SetCellValue('J'.$row, $grade);
    $excel->getActiveSheet()->SetCellValue('K'.$row, $values['volume']);

    if ($errors) {
      foreach ($errors as $field => $array) foreach ((array) $array as $error) $text[] = SGS::decode_error($field, $error, array(':field' => $fields[$field]));
      $excel->getActiveSheet()->SetCellValue('L'.$row, implode(" \n", (array) $errors));
      $excel->getActiveSheet()->getStyle('L'.$row)->getAlignment()->setWrapText(true);
    }

  }

  public function download_headers($values, $excel, $args, $headers = TRUE) {
    if ($headers) {
      $excel->getActiveSheet()->SetCellValue('A1', 'Export Shipment Specification Form - Logs');
      $excel->getActiveSheet()->SetCellValue('I1', 'SF19C-1'); // don't know what this is for
      $excel->getActiveSheet()->SetCellValue('A2', 'Shipment Specification Number');
      $excel->getActiveSheet()->SetCellValue('A3', 'Export Permit Number');
      $excel->getActiveSheet()->SetCellValue('E3', 'Contract Number');
      $excel->getActiveSheet()->SetCellValue('A4', 'Exporter TIN');
      $excel->getActiveSheet()->SetCellValue('E4', 'Exporter Company Name');
      $excel->getActiveSheet()->SetCellValue('A5', 'Port of origin');
      $excel->getActiveSheet()->SetCellValue('E5', 'Expecting loading date:');
      $excel->getActiveSheet()->SetCellValue('A6', 'Port of Destination');
      $excel->getActiveSheet()->SetCellValue('E6', 'Buyer');
      $excel->getActiveSheet()->SetCellValue('A7', 'Submitted by');
      $excel->getActiveSheet()->SetCellValue('E7', 'Date');
      $excel->getActiveSheet()->SetCellValue('A8', 'PRODUCT SPECIFICATION - LOGS');
      $excel->getActiveSheet()->SetCellValue('B9', 'Log Barcode');
      $excel->getActiveSheet()->SetCellValue('D9', 'Species Code');
      $excel->getActiveSheet()->SetCellValue('E9', 'Diameter (underbark to nearest cm)');
      $excel->getActiveSheet()->SetCellValue('I9', 'Length (m) to nearest 0.1m');
      $excel->getActiveSheet()->SetCellValue('J9', 'ATIBT Grade');
      $excel->getActiveSheet()->SetCellValue('K9', 'Volume');
    }

    $excel->getActiveSheet()->SetCellValue('C2', $values['wb_barcode']);
    $excel->getActiveSheet()->SetCellValue('C3', $values['barcode']);
//    $excel->getActiveSheet()->SetCellValue('I3', $values['contract_number']);
    $excel->getActiveSheet()->SetCellValue('I2', $values['wb_number']);
    $excel->getActiveSheet()->SetCellValue('I3', $values['exp_number']);
    $excel->getActiveSheet()->SetCellValue('C4', $values['operator_tin']);
    $excel->getActiveSheet()->SetCellValue('I4', SGS::lookup_operator($values['operator_tin'])->name);
    $excel->getActiveSheet()->SetCellValue('C5', $values['origin']);
    $excel->getActiveSheet()->SetCellValue('I5', ''); // expected loading date
    $excel->getActiveSheet()->SetCellValue('C6', $values['destination']);
    $excel->getActiveSheet()->SetCellValue('I6', ''); // buyer
    $excel->getActiveSheet()->SetCellValue('C7', ''); // submitted by
    $excel->getActiveSheet()->SetCellValue('I7', SGS::date($args['create_date'], SGS::US_DATE_FORMAT));
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
        case 'log_operator_tin':
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
    if ($log_operator_id = SGS::lookup_operator($values['log_operator_tin'], TRUE)) $query->and_where('log_operator_id', '=', $log_operator_id);
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
    if (!($this->log_operator_id == $this->barcode->printjob->site->operator_id)) $warnings['barcode_id']['is_consistent_operator'] = array('value' => $this->log_operator->tin, 'comparison' => $this->barcode->printjob->site->operator->tin);
    if (!($this->transport_operator_id == $this->wb_barcode->printjob->site->operator_id)) $warnings['wb_barcode_id']['is_consistent_operator'] = array('value' => $this->transport_operator->tin, 'comparison' => $this->wb_barcode->printjob->site->operator->tin);
    if (!(in_array('is_consistent_operator', SGS::flattenify($errors + $warnings)))) {
      $successes['log_operator_id']['is_consistent_operator'] = array('value' => $this->log_operator->tin, 'comparison' => $this->operator->tin);
      $successes['transport_operator_id']['is_consistent_operator'] = array('value' => $this->transport_operator->tin, 'comparison' => $this->operator->tin);
    }

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
      if (!($this->log_operator_id == $ldf->operator_id)) $warnings['operator_id']['is_matching_operator'] = array('value' => $this->log_operator->tin, 'comparison' => $ldf->operator->tin);

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
      if (!(in_array('is_matching_operator', SGS::flattenify($errors + $warnings)))) $successes['log_operator_id']['is_matching_operator'] = array('value' => $this->log_operator->tin, 'comparison' => $ldf->operator->tin);
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
      'log_operator_id'       => array(array('not_empty')),
      'transport_operator_id' => array(array('not_empty')),
      'species_id'            => array(array('not_empty')),
      'barcode_id'            => array(array('not_empty'),
                                       array('is_unique_fields', array($this->_table_name, array('barcode_id', 'wb_barcode_id'), array('barcode_id' => $this->barcode->id, 'wb_barcode_id' => $this->wb_barcode->id), $this->id))),
      'wb_barcode_id'         => array(array('not_empty')),
      'diameter'              => array(array('not_empty'),
                                       array('is_measurement_int')),
      'length'                => array(array('not_empty'),
                                       array('is_measurement_float')),
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
      'user_id'               => array(),
      'timestamp'             => array()
    );
  }

  public function other_rules()
  {
    return array(
      'log_operator_tin'       => array(array('not_empty'),
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
      'species_id'       => 'Species',
      'barcode_id'       => self::$fields['barcode'],
      'wb_barcode_id' => self::$fields['wb_barcode'],
      'exp_barcode_id'   => self::$fields['exp_barcode'],
//      'contract_number'  => self::$fields['contract_number'],
      'loading_date'     => self::$fields['loading_date'],
      'buyer'            => self::$fields['buyer'],
      'submitted_by'     => self::$fields['submitted_by'],
      'origin'           => self::$fields['origin'],
      'destination'      => self::$fields['destination'],
      'bottom_max'       => self::$fields['bottom_max'],
      'bottom_min'       => self::$fields['bottom_min'],
      'top_max'          => self::$fields['top_max'],
      'top_min'          => self::$fields['top_min'],
      'length'           => self::$fields['length'],
      'grade'            => self::$fields['grade'],
      'volume'           => self::$fields['volume'],
//      'user_id'          => self::$fields['user_id'],
//      'timestamp'        => self::$fields['timestamp'],
    );
  }

}
