<?php

class Model_SPECS extends SGS_Form_ORM {

  const PARSE_START = 12;

  public static $type = 'SPECS';

  public static $fields = array(
    'create_date'     => 'Date Surveyed',
    'operator_tin'    => 'Operator TIN',
    'contract_number' => 'Contract Summary Number',
    'origin'          => 'Port of Origin',
    'destination'     => 'Port of Destination',
    'specs_barcode'   => 'Shipment Specification Barcode',
    'epr_barcode'     => 'Export Permit Request Barcode',
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

  public static $errors = array();

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

  public static function generate_report($records) {
    return array();
  }

  public static function fields()
  {
    return (array) self::$fields;
  }

  protected function _initialize()
  {
    parent::_initialize();
    $this->_object_plural = 'specs';
  }

  public function formo() {
    $array = array(
      'id'            => array('render' => FALSE),
      'barcode'       => array('render' => FALSE),
      'specs_barcode' => array('render' => FALSE),
      'epr_barcode'   => array('render' => FALSE),
      'operator'      => array('render' => FALSE),
      'status'        => array('render' => FALSE),
      'user'          => array('render' => FALSE),
      'timestamp'     => array('render' => FALSE),
      'species'       => array(
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
      'contract_number' => trim($csv[3][I] ?: $csv[3][J] ?: $csv[3][K]),
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
    $excel->getActiveSheet()->SetCellValue('I3', $this->contract_number);
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
    $excel->getActiveSheet()->SetCellValue('I3', $values['contract_number']);
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
          $args = array(
            'sites.id' => SGS::suggest_site($values['site_name'], array(), 'id'),
          );
          $suggest = SGS::suggest_operator($values[$field], $args, 'tin', $options['min_length'], $options['limit'], $options['offset']);
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

  public function run_checks() {}

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
      'create_date'     => self::$fields['create_date'],
      'operator_id'     => 'Operator',
      'site_id'         => 'Site',
      'block_id'        => 'Block',
      'species_id'      => 'Species',
      'barcode_id'      => self::$fields['barcode'],
      'survey_line'     => self::$fields['survey_line'],
      'cell_number'     => self::$fields['cell_number'],
      'tree_map_number' => self::$fields['tree_map_number'],
      'diameter'        => self::$fields['diameter'],
      'height'          => self::$fields['height'],
      'is_requested'    => self::$fields['is_requested'],
      'is_fda_approved' => self::$fields['is_fda_approved'],
      'fda_remarks'     => self::$fields['fda_remarks'],
//      'user_id'         => self::$fields['user_id'],
//      'timestamp'       => self::$fields['timestamp'],
    );
  }

}