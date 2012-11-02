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

//  public function export_data($excel, $row) {
//    $excel->getActiveSheet()->SetCellValue('A'.$row, $this->barcode->barcode);
//    $excel->getActiveSheet()->SetCellValue('B'.$row, $this->tree_map_number);
//    $excel->getActiveSheet()->SetCellValue('C'.$row, $this->survey_line);
//    $excel->getActiveSheet()->SetCellValue('D'.$row, $this->cell_number);
//    $excel->getActiveSheet()->SetCellValue('E'.$row, $this->species->code);
//    $excel->getActiveSheet()->SetCellValue('F'.$row, $this->diameter);
//    $excel->getActiveSheet()->SetCellValue('G'.$row, $this->height);
//    $excel->getActiveSheet()->SetCellValue('H'.$row, $this->is_requested == FALSE ? 'NO' : 'YES');
//    $excel->getActiveSheet()->SetCellValue('I'.$row, $this->is_fda_approved == FALSE ? 'NO' : 'YES');
//    $excel->getActiveSheet()->SetCellValue('J'.$row, $this->fda_remarks);
//  }
//
//  public function export_headers($excel, $args, $headers = TRUE) {
//    if ($headers) {
//      $excel->getActiveSheet()->SetCellValue('D1', 'STOCK SURVEY FORM');
//      $excel->getActiveSheet()->SetCellValue('J1', 'SOP7-4'); // don't know what this is for
//      $excel->getActiveSheet()->SetCellValue('A2', 'Site type and Reference:');
//      $excel->getActiveSheet()->SetCellValue('G2', 'Holder TIN:');
//      $excel->getActiveSheet()->SetCellValue('A3', 'Date Surveyed:');
//      $excel->getActiveSheet()->SetCellValue('G3', 'Enumerator:');
//      $excel->getActiveSheet()->SetCellValue('A4', 'UTM Coordinates of the 4 corners of the block map:');
//      $excel->getActiveSheet()->SetCellValue('E4', 'Easting');
//      $excel->getActiveSheet()->SetCellValue('G4', 'Northing');
//      $excel->getActiveSheet()->SetCellValue('A5', 'Origin (0 meter  0 meter)');
//      $excel->getActiveSheet()->SetCellValue('A6', 'East from origin');
//      $excel->getActiveSheet()->SetCellValue('A7', 'North/South from previous');
//      $excel->getActiveSheet()->SetCellValue('A8', 'West from previous');
//      $excel->getActiveSheet()->SetCellValue('A9', 'Date entered');
//      $excel->getActiveSheet()->SetCellValue('E9', 'Entered by');
//      $excel->getActiveSheet()->SetCellValue('A10', 'Date checked');
//      $excel->getActiveSheet()->SetCellValue('E10', 'Checked by');
//      $excel->getActiveSheet()->SetCellValue('A11', 'Tree Barcode');
//      $excel->getActiveSheet()->SetCellValue('B11', 'Tree Map Number');
//      $excel->getActiveSheet()->SetCellValue('C11', 'Cell Reference');
//      $excel->getActiveSheet()->SetCellValue('E11', 'Species Code');
//      $excel->getActiveSheet()->SetCellValue('F11', 'Diameter Class Number (cm)');
//      $excel->getActiveSheet()->SetCellValue('G11', "Height (m)");
//      $excel->getActiveSheet()->SetCellValue('H11', 'Crop Trees');
//      $excel->getActiveSheet()->SetCellValue('J11', 'FDA Remarks/Reason for Rejection');
//      $excel->getActiveSheet()->SetCellValue('C12', "Survey Line");
//      $excel->getActiveSheet()->SetCellValue('D12', 'Cell ID Number');
//      $excel->getActiveSheet()->SetCellValue('H12', 'Requested');
//      $excel->getActiveSheet()->SetCellValue('I12', 'FDA Approved');
//    }
//
//    $excel->getActiveSheet()->SetCellValue('B2', $this->site->type.'/'.$this->site->name.'/'.$this->block->name);
//    $excel->getActiveSheet()->SetCellValue('H2', $this->operator->tin);
//    $excel->getActiveSheet()->SetCellValue('B3', SGS::date($args['create_date'], SGS::US_DATE_FORMAT));
//    $excel->getActiveSheet()->SetCellValue('H3', ''); // enumerator
//    $excel->getActiveSheet()->SetCellValue('B5', ''); // origin
//    $excel->getActiveSheet()->SetCellValue('B6', ''); // east from origin
//    $excel->getActiveSheet()->SetCellValue('B7', ''); // north/south from previous
//    $excel->getActiveSheet()->SetCellValue('B8', ''); // west from previous
//    $excel->getActiveSheet()->SetCellValue('B9', ''); // date entered
//    $excel->getActiveSheet()->SetCellValue('H9', ''); // entered by
//    $excel->getActiveSheet()->SetCellValue('B10', ''); // date checked
//    $excel->getActiveSheet()->SetCellValue('F10', ''); // checked by
//  }
//
//  public function download_data($values, $errors, $suggestions, $duplicates, $excel, $row) {
//    $excel->getActiveSheet()->SetCellValue('A'.$row, $values['barcode']);
//    $excel->getActiveSheet()->SetCellValue('B'.$row, $values['tree_map_number']);
//    $excel->getActiveSheet()->SetCellValue('C'.$row, $values['survey_line']);
//    $excel->getActiveSheet()->SetCellValue('D'.$row, $values['cell_number']);
//    $excel->getActiveSheet()->SetCellValue('E'.$row, $values['species_code']);
//    $excel->getActiveSheet()->SetCellValue('F'.$row, $values['diameter']);
//    $excel->getActiveSheet()->SetCellValue('G'.$row, $values['height']);
//    $excel->getActiveSheet()->SetCellValue('H'.$row, $values['is_requested']);
//    $excel->getActiveSheet()->SetCellValue('I'.$row, $values['is_fda_approved']);
//    $excel->getActiveSheet()->SetCellValue('J'.$row, $values['fda_remarks']);
//
//    if ($errors) {
//      $excel->getActiveSheet()->SetCellValue('L'.$row, implode(" \n", (array) $errors));
//      $excel->getActiveSheet()->getStyle('L'.$row)->getAlignment()->setWrapText(true);
//    }
//
//    if ($suggestions) {
//      $text = array();
//      foreach ($suggestions as $field => $suggestion) {
//        $text[] = 'Suggested values for '.self::$fields[$field].': '.implode(', ', $suggestion);
//      }
//      $excel->getActiveSheet()->SetCellValue('M'.$row, implode(" \n", (array) $text));
//      $excel->getActiveSheet()->getStyle('M'.$row)->getAlignment()->setWrapText(true);
//    }
//
//    if ($duplicates) {
//      $excel->getActiveSheet()->SetCellValue('N'.$row, 'Duplicate found');
//    }
//  }
//
//  public function download_headers($values, $excel, $args, $headers = TRUE) {
//    if ($headers) {
//      $excel->getActiveSheet()->SetCellValue('D1', 'STOCK SURVEY FORM');
//      $excel->getActiveSheet()->SetCellValue('J1', 'SOP7-4'); // don't know what this is for
//      $excel->getActiveSheet()->SetCellValue('A2', 'Site type and Reference:');
//      $excel->getActiveSheet()->SetCellValue('G2', 'Holder TIN:');
//      $excel->getActiveSheet()->SetCellValue('A3', 'Date Surveyed:');
//      $excel->getActiveSheet()->SetCellValue('G3', 'Enumerator:');
//      $excel->getActiveSheet()->SetCellValue('A4', 'UTM Coordinates of the 4 corners of the block map:');
//      $excel->getActiveSheet()->SetCellValue('E4', 'Easting');
//      $excel->getActiveSheet()->SetCellValue('G4', 'Northing');
//      $excel->getActiveSheet()->SetCellValue('A5', 'Origin (0 meter  0 meter)');
//      $excel->getActiveSheet()->SetCellValue('A6', 'East from origin');
//      $excel->getActiveSheet()->SetCellValue('A7', 'North/South from previous');
//      $excel->getActiveSheet()->SetCellValue('A8', 'West from previous');
//      $excel->getActiveSheet()->SetCellValue('A9', 'Date entered');
//      $excel->getActiveSheet()->SetCellValue('E9', 'Entered by');
//      $excel->getActiveSheet()->SetCellValue('A10', 'Date checked');
//      $excel->getActiveSheet()->SetCellValue('E10', 'Checked by');
//      $excel->getActiveSheet()->SetCellValue('A11', 'Tree Barcode');
//      $excel->getActiveSheet()->SetCellValue('B11', 'Tree Map Number');
//      $excel->getActiveSheet()->SetCellValue('C11', 'Cell Reference');
//      $excel->getActiveSheet()->SetCellValue('E11', 'Species Code');
//      $excel->getActiveSheet()->SetCellValue('F11', 'Diameter Class Number (cm)');
//      $excel->getActiveSheet()->SetCellValue('G11', "Height (m)");
//      $excel->getActiveSheet()->SetCellValue('H11', 'Crop Trees');
//      $excel->getActiveSheet()->SetCellValue('J11', 'FDA Remarks/Reason for Rejection');
//      $excel->getActiveSheet()->SetCellValue('C12', "Survey Line");
//      $excel->getActiveSheet()->SetCellValue('D12', 'Cell ID Number');
//      $excel->getActiveSheet()->SetCellValue('H12', 'Requested');
//      $excel->getActiveSheet()->SetCellValue('I12', 'FDA Approved');
//    }
//
//    $excel->getActiveSheet()->SetCellValue('B2', substr($values['site_name'], 0 , 3).'/'.$values['site_name'].'/'.$values['block_name']);
//    $excel->getActiveSheet()->SetCellValue('H2', $values['operator_tin']);
//    $excel->getActiveSheet()->SetCellValue('B3', SGS::date($args['create_date'], SGS::US_DATE_FORMAT));
//    $excel->getActiveSheet()->SetCellValue('H3', ''); // enumerator
//    $excel->getActiveSheet()->SetCellValue('B5', ''); // origin
//    $excel->getActiveSheet()->SetCellValue('B6', ''); // east from origin
//    $excel->getActiveSheet()->SetCellValue('B7', ''); // north/south from previous
//    $excel->getActiveSheet()->SetCellValue('B8', ''); // west from previous
//    $excel->getActiveSheet()->SetCellValue('B9', ''); // date entered
//    $excel->getActiveSheet()->SetCellValue('H9', ''); // entered by
//    $excel->getActiveSheet()->SetCellValue('B10', ''); // date checked
//    $excel->getActiveSheet()->SetCellValue('F10', ''); // checked by
//  }

  public function make_suggestions($values, $errors) {
    $suggestions = array();
    foreach ($errors as $field => $error) {
      $suggest = NULL;
      switch ($field) {
        case 'barcode':
          $args = array(
            'barcodes.type' => array('P', 'L'),
            'operators.id' => SGS::suggest_operator($values['operator_tin'], array(), 'id')
          );
          $suggest = SGS::suggest_barcode($values[$field], $args, 'barcode');
          break;
        case 'specs_barcode':
          $args = array(
            'barcodes.type' => array('P', 'H'),
            'operators.id' => SGS::suggest_operator($values['operator_tin'], array(), 'id')
          );
          $suggest = SGS::suggest_barcode($values[$field], $args, 'barcode');
          break;
        case 'epr_barcode':
          $args = array(
            'barcodes.type' => array('P', 'E'),
            'operators.id' => SGS::suggest_operator($values['operator_tin'], array(), 'id')
          );
          $suggest = SGS::suggest_barcode($values[$field], $args, 'barcode');
          break;
        case 'operator_tin':
          $args = array(
            'sites.id' => SGS::suggest_site($values['site_name'], array(), 'id'),
          );
          $suggest = SGS::suggest_operator($values[$field], $args, 'tin');
          break;
        case 'species_code':
          $suggest = SGS::suggest_species($values[$field], array(), 'code');
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

//  operator_id d_id not null,
//  specs_barcode_id d_id not null,
//  epr_barcode_id d_id not null,
//  contract_numbuer d_text_short,
//  barcode_id d_id unique not null,
//  species_id d_id not null,
//  top_min d_measurement_int not null,
//  top_max d_measurement_int not null,
//  bottom_min d_measurement_int not null,
//  bottom_max d_measurement_int not null,
//  length d_measurement_float not null,
//  grade d_grade not null,
//  volume d_measurement_float not null,
//  origin d_text_short,
//  destination d_text_short,
//  create_date d_date not null,
//  status d_data_status default 'P' not null,

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
