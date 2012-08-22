<?php

class Model_SSF extends SGS_Form_ORM {

  const PARSE_START = 13;

  public static $fields = array(
    'create_date'     => 'Date Surveyed',
    'operator_tin'    => 'Operator TIN',
    'site_name'       => 'Site Name',
    'block_name'      => 'Block Name',
    'barcode'         => 'Barcode',
    'tree_map_number' => 'Tree Map Number',
    'survey_line'     => 'Survey Line',
    'cell_number'     => 'Cell Number',
    'species_code'    => 'Species Code',
    'diameter'        => 'Diameter',
    'height'          => 'Height',
    'is_requested'    => 'Is Requested',
    'is_fda_approved' => 'Is FDA Approved',
    'fda_remarks'     => 'FDA Remarks',
  );


  protected $_table_name = 'ssf_data';

  protected $_belongs_to = array(
    'site'     => array(),
    'operator' => array(),
    'block'    => array(),
    'barcode'  => array(),
    'species'  => array(),
    'user'     => array(),
  );

  protected function _initialize()
  {
    parent::_initialize();
    $this->_object_plural = 'ssf';
  }

  public function parse_csv($row, &$csv)
  {
    extract(SGS::parse_site_and_block_info($csv[2][B]));
    $data = array(
      'barcode'           => $row[A],
      'tree_map_number'   => $row[B],
      'survey_line'       => $row[C],
      'cell_number'       => $row[D],
      'species_code'      => $row[E],
      'diameter'          => $row[F],
      'height'            => $row[G],
    );

    if (array_filter($data)) return array(
      'create_date'     => $csv[3][B],
      'operator_tin'    => $csv[2][H],
      'site_name'       => $site_name,
      'block_name'      => $block_name,
    ) + $data + array(
      'is_requested'    => $row[H] == 'NO' ? 'NO' : 'YES',
      'is_fda_approved' => $row[I] == 'NO' ? 'NO' : 'YES',
      'fda_remarks'     => $row[J],
    );
  }

  public function parse_data($data)
  {
    foreach ($data as $key => $value) switch ($key) {
      case 'operator_tin':
        $this->operator = SGS::lookup_operator($value); break;

      case 'site_name':
        $this->site = SGS::lookup_site($value);
        break;

      case 'block_name':
        $this->block = SGS::lookup_block($data['site_name'], $value); break;

      case 'barcode':
        $this->$key = SGS::lookup_barcode($value); break;

      case 'species_code':
        $this->species = SGS::lookup_species($value); break;

      case 'create_date':
        $this->$key = SGS::date($value, SGS::PGSQL_DATE_FORMAT); break;

      default:
        $this->$key = $value; break;
    }
  }

  public function export_data($excel, $row) {
    $excel->getActiveSheet()->SetCellValue('A'.$row, $this->barcode->barcode);
    $excel->getActiveSheet()->SetCellValue('B'.$row, $this->tree_map_number);
    $excel->getActiveSheet()->SetCellValue('C'.$row, $this->survey_line);
    $excel->getActiveSheet()->SetCellValue('D'.$row, $this->cell_number);
    $excel->getActiveSheet()->SetCellValue('E'.$row, $this->species->code);
    $excel->getActiveSheet()->SetCellValue('F'.$row, $this->diameter);
    $excel->getActiveSheet()->SetCellValue('G'.$row, $this->height);
    $excel->getActiveSheet()->SetCellValue('H'.$row, $this->is_requested == FALSE ? 'NO' : 'YES');
    $excel->getActiveSheet()->SetCellValue('I'.$row, $this->is_fda_approved == FALSE ? 'NO' : 'YES');
    $excel->getActiveSheet()->SetCellValue('J'.$row, $this->fda_remarks);
  }

  public function export_headers($excel, $values, $headers = TRUE) {
    if ($headers) {
      $excel->getActiveSheet()->SetCellValue('D1', 'STOCK SURVEY FORM');
      $excel->getActiveSheet()->SetCellValue('J1', 'SOP7-4'); // don't know what this is for
      $excel->getActiveSheet()->SetCellValue('A2', 'Site type and Reference:');
      $excel->getActiveSheet()->SetCellValue('G2', 'Holder TIN:');
      $excel->getActiveSheet()->SetCellValue('A3', 'Date Surveyed:');
      $excel->getActiveSheet()->SetCellValue('G3', 'Enumerator:');
      $excel->getActiveSheet()->SetCellValue('A4', 'UTM Coordinates of the 4 corners of the block map:');
      $excel->getActiveSheet()->SetCellValue('E4', 'Easting');
      $excel->getActiveSheet()->SetCellValue('G4', 'Northing');
      $excel->getActiveSheet()->SetCellValue('A5', 'Origin (0 meter  0 meter)');
      $excel->getActiveSheet()->SetCellValue('A6', 'East from origin');
      $excel->getActiveSheet()->SetCellValue('A7', 'North/South from previous');
      $excel->getActiveSheet()->SetCellValue('A8', 'West from previous');
      $excel->getActiveSheet()->SetCellValue('A9', 'Date entered');
      $excel->getActiveSheet()->SetCellValue('E9', 'Entered by');
      $excel->getActiveSheet()->SetCellValue('A10', 'Date checked');
      $excel->getActiveSheet()->SetCellValue('E10', 'Checked by');
      $excel->getActiveSheet()->SetCellValue('A11', 'Tree ID Number');
      $excel->getActiveSheet()->SetCellValue('B11', 'Tree Map Number');
      $excel->getActiveSheet()->SetCellValue('C11', 'Cell Reference');
      $excel->getActiveSheet()->SetCellValue('E11', 'Species Code');
      $excel->getActiveSheet()->SetCellValue('F11', 'Diameter Class Number (cm)');
      $excel->getActiveSheet()->SetCellValue('G11', "Height (m)");
      $excel->getActiveSheet()->SetCellValue('H11', 'Crop Trees');
      $excel->getActiveSheet()->SetCellValue('J11', 'FDA Remarks/Reason for Rejection');
      $excel->getActiveSheet()->SetCellValue('C12', "Survey Line Number");
      $excel->getActiveSheet()->SetCellValue('D12', 'Cell ID Number');
      $excel->getActiveSheet()->SetCellValue('H12', 'Requested');
      $excel->getActiveSheet()->SetCellValue('I12', 'FDA Approved');
      $excel->getActiveSheet()->SetCellValue('L12', "Barcode Check");
      $excel->getActiveSheet()->SetCellValue('M12', "UPPER SPECIES");
    }

    $excel->getActiveSheet()->SetCellValue('B2', $this->site->name.'/'.$this->block->name);
    $excel->getActiveSheet()->SetCellValue('H2', $this->operator->tin);
    $excel->getActiveSheet()->SetCellValue('B3', SGS::date($values['create_date'], SGS::US_DATE_FORMAT));
    $excel->getActiveSheet()->SetCellValue('H3', ''); // enumerator
    $excel->getActiveSheet()->SetCellValue('B5', ''); // origin
    $excel->getActiveSheet()->SetCellValue('B6', ''); // east from origin
    $excel->getActiveSheet()->SetCellValue('B7', ''); // north/south from previous
    $excel->getActiveSheet()->SetCellValue('B8', ''); // west from previous
    $excel->getActiveSheet()->SetCellValue('B9', ''); // date entered
    $excel->getActiveSheet()->SetCellValue('H9', ''); // entered by
    $excel->getActiveSheet()->SetCellValue('B10', ''); // date checked
    $excel->getActiveSheet()->SetCellValue('F10', ''); // checked by
  }

  public function make_suggestions($values, $errors) {
    $suggestions = array();
    foreach ($errors as $field => $error) {
      switch ($field) {
        case 'barcode':
          $args = array(
            'barcodes.type' => array('P'),
            'sites.id' => SGS::suggest_site($values['site_name'], array(), 'id'),
            'operators.id' => SGS::suggest_operator($values['operator_tin'], array(), 'id')
          );
          $suggest = SGS::suggest_barcode($values[$field], $args, 'barcode');
          break;
      }
      $suggestions[$field] = $suggest;
    }

    return $suggestions;
  }

  public static function fields($display = FALSE)
  {
    foreach (self::$fields as $key => $value) switch ($key) {
      default:
        $fields[$key] = $value;
    }
    return $fields;
  }

  public function rules()
  {
    return array(
      'site_id'         => array(array('not_empty')),
      'operator_id'     => array(array('not_empty')),
      'block_id'        => array(array('not_empty')),
      'species_id'      => array(array('not_empty')),
      'barcode_id'      => array(array('not_empty'),
                                 array('is_unique', array($this->_table_name, ':field', ':value', $this->id))),
      'survey_line'     => array(array('not_empty'),
                                 array('is_survey_line')),
      'cell_number'     => array(array('not_empty'),
                                 array('is_positive_int')),
      'tree_map_number' => array(array('not_empty'),
                                 array('is_positive_int')),
      'diameter'        => array(array('not_empty'),
                                 array('is_measurement_int')),
      'height'          => array(array('not_empty'),
                                 array('is_measurement_float')),
      'is_requested'    => array(array('not_empty'),
                                 array('is_boolean')),
      'is_fda_approved' => array(array('not_empty'),
                                 array('is_boolean')),
      'fda_remarks'     => array(),
      'create_date'     => array(array('not_empty'),
                                 array('is_date')),
      'user_id'         => array(),
      'timestamp'       => array()
    );
  }

  public function other_rules()
  {
    return array(
      'operator_tin'   => array(array('not_empty'),
                                array('is_operator_tin'),
                                array('is_existing_operator')),
      'site_name'      => array(array('is_text_short'),
                                array('is_existing_site')),
      'block_name'     => array(array('not_empty'),
                                array('is_block_name'),
                                array('is_existing_block', array(':validation', 'site_name', 'block_name'))),
      'barcode'        => array(array('not_empty'),
                                array('is_barcode'),
                                array('is_existing_barcode')),
      'species_code'   => array(array('not_empty'),
                                array('is_species_code'),
                                array('is_existing_species'))
    );
  }

  public function __get($column) {
    switch ($column) {
      case 'is_requested':
      case 'is_fda_approved':
        return parent::__get($column) == 't' ? TRUE : FALSE;

      default:
        return parent::__get($column);
    }
  }

}
