<?php

class Model_SPECS extends SGS_Form_ORM {

  const PARSE_START = 12;

  protected $_table_name = 'specs_data';

  protected $_belongs_to = array(
    'operator' => array(),
    'barcode'  => array(),
    'specs_barcode'  => array(
      'model'       => 'barcode',
      'foreign_key' => 'specs_barcode_id'),
    'epr_barcode'  => array(
      'model'       => 'barcode',
      'foreign_key' => 'epr_barcode_id'),
    'species'  => array(),
    'user'     => array(),
  );

  protected $_ignored_columns = array(
    'specs_number',
    'epr_number',
    'diameter'
  );

  protected function _initialize()
  {
    parent::_initialize();
    $this->_object_plural = 'specs';
  }

  public function __get($column) {
    switch ($column) {
      case 'diameter':
        return (($this->top_min + $this->top_max + $this->bottom_min + $this->bottom_max) / 4);

      case 'specs_number':
        if ($result = reset(DB::select('id', 'number')
          ->from('specs')
          ->where('id', '=', $this->specs_id)
          ->execute()
          ->as_array())) return $result['number'] ? 'SPECS '.$result['number'] : 'DRAFT'; break;

      case 'epr_number':
        if ($result = reset(DB::select('id', 'number')
          ->from('epr')
          ->where('id', '=', $this->epr_id)
          ->execute()
          ->as_array())) return $result['number'] ? 'EP '.$result['number'] : 'DRAFT'; break;

      default:
        return parent::__get($column);
    }
  }

  public static $type = 'SPECS';

  public static $fields = array(
    'create_date'     => 'Date Surveyed',
    'operator_tin'    => 'Operator TIN',
    'specs_number'    => 'Shipment Specification Number',
    'epr_number'      => 'Permit Request Number',
//    'contract_number' => 'Contract Summary Number',
    'origin'          => 'Port of Origin',
    'destination'     => 'Port of Destination',
    'specs_barcode'   => 'Shipment Specification Barcode',
    'epr_barcode'     => 'Permit Request Barcode',
    'barcode'         => 'Log Barcode',
    'species_code'    => 'Species Code',
    'bottom_max'      => 'Butt Max',
    'bottom_min'      => 'Butt Min',
    'top_max'         => 'Top Max',
    'top_min'         => 'Top Min',
    'length'          => 'Length',
    'grade'           => 'Grade',
    'volume'          => 'Volume',
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
        'is_valid_specs_barcode' => array(
          'name'    => 'Shipment Specification Barcode Assignment',
          'title'   => 'Shipment specification barcode assignment is valid',
          'error'   => 'Shipment specification barcode assignment is invalid',
          'warning' => 'Shipment specification barcode is not yet assigned',
         ),
        'is_valid_epr_barcode' => array(
          'name'    => 'Export Permit Request Barcode Assignment',
          'title'   => 'Export permit request barcode assignment is valid',
          'error'   => 'Export permit request barcode assignment is invalid',
          'warning' => 'Export permit request barcode is not yet assigned',
         )
    )),
    'reliability' => array(
      'title'  => 'Data Reliability',
      'checks' => array(
        'is_consistent_operator' => array(
          'name'    => 'Operator Assignments',
          'title'   => 'Operator assignments are consistent',
          'warning' => 'Operator assignments are inconsistent'
        )
    )),
    'traceability' => array(
      'title'  => 'Traceability',
      'checks' => array(
        'is_existing_parent' => array(
          'name'  => 'Tracebale Parent',
          'title' => 'Traceable to LDF',
          'error' => 'Not tracable to LDF'
        ),
        'is_valid_parent' => array(
          'name'  => 'Parent Status',
          'title' => 'LDF record passed checks and queries',
          'error' => 'LDF record failed checks and queries'
        )
    )),
    'tolerance' => array(
      'title'  => 'Tolerance',
      'checks' => array(
        'is_matching_species' => array(
          'name'    => 'Species',
          'title'   => 'Species matches data for LDF',
          'error'   => 'Species does not match data for LDF',
          'warning' => 'Species class matches data for LDF but species code does not'
        ),
        'is_matching_length' => array(
          'name'    => 'Length',
          'title'   => 'Length matches data for LDF',
          'error'   => 'Length does not match data for LDF',
          'warning' => 'Length matches data for LDF but is inaccurate'
        ),
        'is_matching_diameter' => array(
          'name'    => 'Diameter',
          'title'   => 'Diameter matches data for LDF',
          'error'   => 'Diameter does not match data for LDF',
          'warning' => 'Diameter matches data for LDF but is inaccurate'
        ),
        'is_matching_volume' => array(
          'name'    => 'Volume',
          'title'   => 'Volume matches data for LDF',
          'error'   => 'Volume does not match data for LDF',
          'warning' => 'Volume matches data for LDF but is inaccurate'
        ),
        'is_matching_operator' => array(
          'name'  => 'Operator',
          'title' => 'Operator matches data for LDF',
          'error' => 'Operator does not match data for LDF',
        ),
    )),
    'payment' => array(
      'title'  => 'Payment',
      'checks' => array(
        'is_invoiced_st' => array(
          'name'    => 'Stumpage Fee Invoiced',
          'title'   => 'Stumpage fee invoiced',
          'error'   => 'Stumpage fee has not been invoiced',
        ),
      )),
  );

  public static function generate_report($records) {
    return array();
  }

  public static function fields()
  {
    return (array) self::$fields;
  }

  public static function create_specs_number($force = FALSE) {
    return DB::query(Database::SELECT, "SELECT to_char(nextval('s_specs_number'), 'FM000000') specs_number")
      ->execute()
      ->get('specs_number');
  }

  public function formo() {
    $array = array(
      'id'              => array('render' => FALSE),
      'barcode'         => array('render' => FALSE),
      'contract_number' => array('render' => FALSE),
      'specs_barcode'   => array('render' => FALSE),
      'epr_barcode'     => array('render' => FALSE),
      'specs_id'        => array('render' => FALSE),
      'epr_id'          => array('render' => FALSE),
      'operator'        => array('render' => FALSE),
      'status'          => array('render' => FALSE),
      'user'            => array('render' => FALSE),
      'timestamp'       => array('render' => FALSE),
      'species'         => array(
        'orm_primary_val' => 'code',
        'label' => 'Species'
      ),
      'grade' => array(
        'driver'  => 'forceselect',
        'options' => SGS::$grade
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
    extract(SGS::parse_grade(trim($row[J])));
    extract(SGS::parse_specs_number(trim($csv[2][I] ?: $csv[2][J] ?: $csv[2][K])));
    extract(SGS::parse_epr_number(trim($csv[3][I] ?: $csv[3][J] ?: $csv[3][K])));
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
//      'contract_number' => trim($csv[3][I] ?: $csv[3][J] ?: $csv[3][K]),
      'specs_number'    => $specs_number,
      'epr_number'      => $epr_number,
      'origin'          => trim($csv[5][C] ?: $csv[5][D]),
      'destination'     => trim($csv[6][C] ?: $csv[6][D]),
      'specs_barcode'   => SGS::barcodify(trim($csv[2][C] ?: $csv[2][D])),
      'epr_barcode'     => SGS::barcodify(trim($csv[3][C] ?: $csv[3][D])),
    ) + $data);
  }

  public function parse_data($data)
  {
    foreach ($data as $key => $value) switch ($key) {
      case 'operator_tin':
        $this->operator = SGS::lookup_operator($value); break;

      case 'barcode':
      case 'specs_barcode':
      case 'epr_barcode':
        $this->$key = SGS::lookup_barcode(SGS::barcodify($value)); break;

      case 'specs_number':
        $this->specs_id = SGS::lookup_specs($value, TRUE); break;

      case 'epr_number':
        $this->epr_id = SGS::lookup_epr($value, TRUE); break;

      case 'species_code':
        $this->species = SGS::lookup_species($value); break;

      case 'create_date':
        $this->$key = SGS::date($value, SGS::PGSQL_DATE_FORMAT); break;

      default:
        try { $this->$key = $value; } catch (Exception $e) {}
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
      $excel->getActiveSheet()->SetCellValue('A3', 'Permit Request Number');
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

    $excel->getActiveSheet()->SetCellValue('C2', $this->specs_barcode->barcode);
    $excel->getActiveSheet()->SetCellValue('C3', $this->epr_barcode->barcode);
//    $excel->getActiveSheet()->SetCellValue('I3', $this->contract_number);
    $excel->getActiveSheet()->SetCellValue('I2', $this->specs_number);
    $excel->getActiveSheet()->SetCellValue('I3', $this->epr_number);
    $excel->getActiveSheet()->SetCellValue('C4', $this->operator->tin);
    $excel->getActiveSheet()->SetCellValue('I4', $this->operator->name);
    $excel->getActiveSheet()->SetCellValue('C5', $this->origin);
    $excel->getActiveSheet()->SetCellValue('I5', ''); // expected loading date
    $excel->getActiveSheet()->SetCellValue('C6', $this->destination);
    $excel->getActiveSheet()->SetCellValue('I6', ''); // buyer
    $excel->getActiveSheet()->SetCellValue('C7', ''); // submitted by
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
      $excel->getActiveSheet()->SetCellValue('A3', 'Permit Request Number');
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

    $excel->getActiveSheet()->SetCellValue('C2', $values['specs_barcode']);
    $excel->getActiveSheet()->SetCellValue('C3', $values['barcode']);
//    $excel->getActiveSheet()->SetCellValue('I3', $values['contract_number']);
    $excel->getActiveSheet()->SetCellValue('I2', $values['specs_number']);
    $excel->getActiveSheet()->SetCellValue('I3', $values['epr_number']);
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
      $suggest = NULL;
      switch ($field) {
        case 'barcode':
          $args = array(
            'barcodes.type' => array('P', 'L'),
            'operators.id' => SGS::suggest_operator($values['operator_tin'], array(), 'id')
          );
          $suggest = SGS::suggest_barcode($values[$field], $args, 'barcode', $options['min_length'], $options['limit'], $options['offset']);
          break;
        case 'specs_barcode':
          $args = array(
            'barcodes.type' => array('P', 'H'),
            'operators.id' => SGS::suggest_operator($values['operator_tin'], array(), 'id')
          );
          $suggest = SGS::suggest_barcode($values[$field], $args, 'barcode', $options['min_length'], $options['limit'], $options['offset']);
          break;
        case 'epr_barcode':
          $args = array(
            'barcodes.type' => array('P', 'E'),
            'operators.id' => SGS::suggest_operator($values['operator_tin'], array(), 'id')
          );
          $suggest = SGS::suggest_barcode($values[$field], $args, 'barcode', $options['min_length'], $options['limit'], $options['offset']);
          break;
        case 'operator_tin':
          $suggest = SGS::suggest_operator($values[$field], array(), 'tin', $options['min_length'], $options['limit'], $options['offset']);
          break;
        case 'species_code':
          $suggest = SGS::suggest_species($values[$field], array(), 'code', $options['min_length'], $options['limit'], $options['offset']);
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
            ->where($field.'_id', '=', ($val = SGS::lookup_barcode($values[$field], TRUE)) ? $val : NULL);

          if ($operator_id = SGS::lookup_operator($values['operator_tin'], TRUE)) $query->and_where('operator_id', '=', $operator_id);

          if ($duplicate = $query->execute()->get('id')) $duplicates[$field] = $duplicate;
          break;
      }
    }

    return $duplicates;
  }

  public function run_checks() {
    $this->reset_checks();

    $errors    = array();
    $warnings  = array();
    $successes = array();

    // reliability
    if (!($this->operator_id == $this->barcode->printjob->site->operator_id)) $warnings['barcode_id']['is_consistent_operator'] = array('value' => $this->operator->tin, 'comparison' => $this->barcode->printjob->site->operator->tin);
    if (!($this->operator_id == $this->specs_barcode->printjob->site->operator_id)) $warnings['specs_barcode_id']['is_consistent_operator'] = array('value' => $this->operator->tin, 'comparison' => $this->specs_barcode->printjob->site->operator->tin);
    if (!($this->operator_id == $this->epr_barcode->printjob->site->operator_id)) $warnings['epr_barcode_id']['is_consistent_operator'] = array('value' => $this->operator->tin, 'comparison' => $this->epr_barcode->printjob->site->operator->tin);
    if (!(in_array('is_consistent_operator', SGS::flattenify($errors + $warnings)))) $successes['operator_id']['is_consistent_operator'] = array('value' => $this->operator->tin, 'comparison' => $this->operator->tin);

    // consistency
    switch ($this->barcode->type) {
      case 'L': $successes['barcode_id']['is_valid_barcode'] = array('value' => SGS::$barcode_type[$this->barcode->type], 'comparison' => SGS::$barcode_type['L']); break;
      default:  $warnings['barcode_id']['is_valid_barcode'] = array('value' => SGS::$barcode_type[$this->barcode->type], 'comparison' => SGS::$barcode_type['L']); break;
    }

    switch ($this->specs_barcode->type) {
      case 'H': $successes['specs_barcode_id']['is_valid_specs_barcode'] = array('value' => SGS::$barcode_type[$this->specs_barcode->type], 'comparison' => SGS::$barcode_type['H']); break;
      default:  $warnings['specs_barcode_id']['is_valid_specs_barcode'] = array('value' => SGS::$barcode_type[$this->specs_barcode->type], 'comparison' => SGS::$barcode_type['H']); break;
    }

    switch ($this->epr_barcode->type) {
      case 'E': $successes['epr_barcode_id']['is_valid_epr_barcode'] = array('value' => SGS::$barcode_type[$this->epr_barcode->type], 'comparison' => SGS::$barcode_type['E']); break;
      default:  $warnings['epr_barcode_id']['is_valid_epr_barcode'] = array('value' => SGS::$barcode_type[$this->epr_barcode->type], 'comparison' => SGS::$barcode_type['E']); break;
    }

    $parent = ORM::factory('LDF')
      ->where('barcode_id', '=', $this->barcode->id)
      ->find();

    if ($parent and $parent->loaded()) {
      if ($parent->status != 'A') $errors['barcode_id']['is_valid_parent'] = array('comparison' => SGS::$data_status[$parent->status]);
      else $successes['barcode_id']['is_valid_parent'] = array('comparison' => SGS::$data_status[$parent->status]);

      if (!(ord($this->species->class) >= ord($parent->species->class))) $errors['species_id']['is_matching_species'] = array('value' => $this->species->class, 'comparison' => $parent->species->class);
      if (!($this->species->code == $parent->species->code)) $warnings['species_id']['is_matching_species'] = array('value' => $this->species->code, 'comparison' => $parent->species->code);
      if (!($this->operator_id == $parent->operator_id)) $errors['operator_id']['is_matching_operator'] = array('value' => $this->operator->tin, 'comparison' => $parent->operator->tin);

      if (!Valid::meets_tolerance($this->volume, $parent->volume, SGS::tolerance('SPECS', 'is_matching_volume'))) $errors['volume']['is_matching_volume'] = array('value' => $this->volume, 'comparison' => $parent->volume);
      else if (!Valid::meets_tolerance($this->volume, $parent->volume, SGS::accuracy('SPECS', 'is_matching_volume'))) $warnings['volume']['is_matching_volume'] = array('value' => $this->volume, 'comparison' => $parent->volume);

      if (!Valid::meets_tolerance($this->length, $parent->length, SGS::tolerance('SPECS', 'is_matching_length'))) $errors['length']['is_matching_length'] = array('value' => $this->length, 'comparison' => $parent->length);
      else if (!Valid::meets_tolerance($this->length, $parent->length, SGS::accuracy('SPECS', 'is_matching_length'))) $warnings['length']['is_matching_length'] = array('value' => $this->length, 'comparison' => $parent->length);

      if (!Valid::meets_tolerance($this->diameter, $parent->diameter, SGS::tolerance('SPECS', 'is_matching_diameter'))) {
        $errors['top_min']['is_matching_diameter'] = array('value' => $this->diameter, 'comparison' => $parent->diameter);
        $errors['top_max']['is_matching_diameter'] = array('value' => $this->diameter, 'comparison' => $parent->diameter);
        $errors['bottom_min']['is_matching_diameter'] = array('value' => $this->diameter, 'comparison' => $parent->diameter);
        $errors['bottom_max']['is_matching_diameter'] = array('value' => $this->diameter, 'comparison' => $parent->diameter);
      }
      else if (!Valid::meets_tolerance($this->diameter, $parent->diameter, SGS::accuracy('SPECS', 'is_matching_diameter'))) {
        $warnings['top_min']['is_matching_diameter'] = array('value' => $this->diameter, 'comparison' => $parent->diameter);
        $warnings['top_max']['is_matching_diameter'] = array('value' => $this->diameter, 'comparison' => $parent->diameter);
        $warnings['bottom_min']['is_matching_diameter'] = array('value' => $this->diameter, 'comparison' => $parent->diameter);
        $warnings['bottom_max']['is_matching_diameter'] = array('value' => $this->diameter, 'comparison' => $parent->diameter);
      }
      $successes['barcode_id']['is_existing_parent'] = array('value' => 'Found', 'comparison' => 'Found');
    }
    else {
      $errors['barcode_id']['is_existing_parent'] = array('value' => 'Found', 'comparison' => 'Not Found');
      $errors['barcode_id']['is_valid_parent'] = array('value' => 'Found', 'comparison' => 'Not Found');
    }

    // payment
    if (!$parent->is_invoiced()) $errors['barcode_id']['is_invoiced_st'] = array('value' => 'Not Invoiced', 'comparison' => 'N/A');
    else $successes['barcode_id']['is_invoiced_st'] = array('value' => 'Invoiced', 'comparison' => 'N/A');

    // all tolerance checks fail if any traceability checks fail
    if (array_intersect(SGS::flattenify($errors), array_keys(self::$checks['traceability']['checks']))) {
      foreach (self::$checks['tolerance']['checks'] as $check => $array) $errors['barcode_id'][$check] = array();
    }

    if (is_object($parent) and $parent->loaded()) {
      if (!(in_array('is_matching_operator', SGS::flattenify($errors + $warnings)))) $successes['operator_id']['is_matching_operator'] = array('value' => $this->operator->tin, 'comparison' => $parent->operator->tin);
      if (!(in_array('is_matching_species', SGS::flattenify($errors + $warnings)))) $successes['species_id']['is_matching_species'] = array('value' => $this->species->code, 'comparison' => $parent->species->code);
      if (!(in_array('is_matching_length', SGS::flattenify($errors + $warnings)))) $successes['length']['is_matching_length'] = array('value' => $this->length, 'comparison' => $parent->length);
      if (!(in_array('is_matching_volume', SGS::flattenify($errors + $warnings)))) $successes['volume']['is_matching_volume'] = array('value' => $this->volume, 'comparison' => $parent->volume);

      if (!(in_array('is_matching_diameter', SGS::flattenify($errors + $warnings)))) {
        $successes['top_min']['is_matching_diameter'] = array('value' => $this->diameter, 'comparison' => $parent->diameter);
        $successes['top_max']['is_matching_diameter'] = array('value' => $this->diameter, 'comparison' => $parent->diameter);
        $successes['bottom_min']['is_matching_diameter'] = array('value' => $this->diameter, 'comparison' => $parent->diameter);
        $successes['bottom_max']['is_matching_diameter'] = array('value' => $this->diameter, 'comparison' => $parent->diameter);
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

  public function rules()
  {
    return array(
      'operator_id'        => array(array('not_empty')),
      'species_id'         => array(array('not_empty')),
      'barcode_id'         => array(array('not_empty'),
                                    array('is_unique', array($this->_table_name, ':field', ':value', $this->id))),
      'specs_barcode_id'   => array(array('not_empty')),
      'epr_barcode_id'     => array(array('not_empty')),
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
      'grade'              => array(array('not_empty'),
                                    array('is_grade')),
      'volume'             => array(array('not_empty'),
                                    array('is_measurement_float')),
      'origin'             => array(),
      'destination'        => array(),
      'create_date'        => array(array('not_empty'),
                                    array('is_date')),
      'user_id'            => array(),
      'timestamp'          => array()
    );
  }

  public function other_rules()
  {
    return array(
      'operator_tin'   => array(array('not_empty'),
                                array('is_operator_tin'),
                                array('is_existing_operator')),
      'barcode'        => array(array('not_empty'),
                                array('is_barcode', array(':value', TRUE)),
                                array('is_existing_barcode')),
      'specs_barcode'  => array(array('is_barcode', array(':value', TRUE)),
                                array('is_existing_barcode')),
      'epr_barcode'    => array(array('is_barcode', array(':value', TRUE)),
                                array('is_existing_barcode')),
      'species_code'   => array(array('not_empty'),
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
      'specs_barcode_id' => self::$fields['specs_barcode'],
      'epr_barcode_id'   => self::$fields['epr_barcode'],
//      'contract_number'  => self::$fields['contract_number'],
      'specs_id'         => self::$fields['specs_number'],
      'epr_id'           => self::$fields['epr_number'],
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
