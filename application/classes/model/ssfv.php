<?php

class Model_SSFV extends SGS_Form_ORM {

  const PARSE_START = 11;

  protected $_table_name = 'ssf_verification';

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
    $this->_object_plural = 'ssfv';
  }

  public static $type = 'SSFV';
  public static $verification_type = 'SSFV';

  public static $fields = array(
    'create_date'     => 'Date',
    'operator_tin'    => 'Operator TIN',
    'site_name'       => 'Site Name',
    'block_name'      => 'Block Name',
    'barcode'         => 'Tree Barcode',
    'survey_line'     => 'Survey Line',
    'cell_number'     => 'Cell Number',
    'species_code'    => 'Species Code',
    'diameter'        => 'Diameter',
    'height'          => 'Height',
//    'inspection_date' => 'Inspection Date',
    'inspected_by'    => 'Inspector',
  );

  public static $checks = array();

  public static function fields()
  {
    return (array) self::$fields;
  }

  public function formo() {
    $array = array(
      'id'        => array('render' => FALSE),
      'create_date' => array('order' => 0, 'attr' => array('class' => 'dpicker')),
      'barcode'   => array('render' => FALSE),
      'operator'  => array('render' => FALSE),
      'site'      => array('render' => FALSE),
      'block'     => array('render' => FALSE),
      'status'    => array('render' => FALSE),
      'user'      => array('render' => FALSE),
      'timestamp' => array('render' => FALSE),
      'species'   => array(
        'orm_primary_val' => 'code',
        'label' => 'Species'
      )
    );
    foreach (self::fields() as $field => $label) {
      $array[$field]['label'] = $label;
    }
    return $array;
  }

  public function parse_csv($row, &$csv)
  {
    extract(SGS::parse_site_and_block(trim($csv[2][B] ?: $csv[2][C] ?: $csv[2][D])));
    $data = array(
      'barcode'      => SGS::barcodify(trim($row[A] ?: $row[B])),
      'survey_line'  => trim($row[C]),
      'cell_number'  => trim($row[D]),
      'species_code' => trim($row[E]),
      'diameter'     => trim($row[F]),
      'height'       => trim($row[G] ?: $row[H]),
    );

    if (array_filter($data)) return SGS::cleanify(array(
      'create_date'     => SGS::date(trim($csv[3][B] ?: $csv[3][C] ?: $csv[3][D]), SGS::US_DATE_FORMAT, TRUE, TRUE),
      'operator_tin'    => trim($csv[2][F] ?: $csv[2][G] ?: $csv[2][H]),
      'site_name'       => $site_name,
      'block_name'      => $block_name,
      'inspection_date' => SGS::date(trim($csv[3][B] ?: $csv[3][C] ?: $csv[3][D]), SGS::US_DATE_FORMAT, TRUE, TRUE),
      'inspected_by'    => trim($csv[9][F] ?: $csv[9][G] ?: $csv[9][H]),
    ) + $data);
  }

  public function parse_data($data)
  {
    foreach ($data as $key => $value) switch ($key) {
      case 'operator_tin':
        $this->operator = SGS::lookup_operator($value); break;

      case 'site_name':
        $this->site = SGS::lookup_site($value); break;

      case 'block_name':
        $this->block = SGS::lookup_block($data['site_name'], $value); break;

      case 'barcode':
        $this->$key = SGS::lookup_barcode(SGS::barcodify($value)); break;

      case 'species_code':
        $this->species = SGS::lookup_species($value); break;

      case 'create_date':
        $this->$key = SGS::date($value, SGS::PGSQL_DATE_FORMAT); break;

      case 'diameter':
        $this->$key = SGS::floatify($value); break;

      case 'height':
        $this->$key = SGS::floatify($value, 1); break;

      default:
        try { $this->$key = $value; } catch (Exception $e) {} break;
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

  public function export_headers($excel, $args, $headers = TRUE) {
    if ($headers) {
      $excel->getActiveSheet()->SetCellValue('D1', 'STOCK SURVEY VERIFICATION FORM');
      $excel->getActiveSheet()->SetCellValue('J1', 'SOP7-4'); // don't know what this is for
      $excel->getActiveSheet()->SetCellValue('A2', 'Site Type and Reference:');
      $excel->getActiveSheet()->SetCellValue('E2', 'Operator TIN:');
      $excel->getActiveSheet()->SetCellValue('A3', 'Date Inspected:');
      $excel->getActiveSheet()->SetCellValue('E3', 'Inspector:');
      $excel->getActiveSheet()->SetCellValue('A4', 'UTM Coordinates of the 4 Corners of the Block Map:');
      $excel->getActiveSheet()->SetCellValue('E4', 'Easting');
      $excel->getActiveSheet()->SetCellValue('G4', 'Northing');
      $excel->getActiveSheet()->SetCellValue('A5', 'Origin (0 meter 0 meter)');
      $excel->getActiveSheet()->SetCellValue('A6', 'East from Origin');
      $excel->getActiveSheet()->SetCellValue('A7', 'North/South from Previous');
      $excel->getActiveSheet()->SetCellValue('A8', 'West from Previous');
      $excel->getActiveSheet()->SetCellValue('A9', 'Tree Barcode');
      $excel->getActiveSheet()->SetCellValue('C9', 'Cell Reference');
      $excel->getActiveSheet()->SetCellValue('E9', 'Species Code');
      $excel->getActiveSheet()->SetCellValue('F9', 'Diameter (cm)');
      $excel->getActiveSheet()->SetCellValue('G9', "Height (m)");
      $excel->getActiveSheet()->SetCellValue('C10', "Survey Line");
      $excel->getActiveSheet()->SetCellValue('D10', 'Cell Number');
    }

    $excel->getActiveSheet()->SetCellValue('B2', $this->site->type.'/'.$this->site->name.'/'.$this->block->name);
    $excel->getActiveSheet()->SetCellValue('F2', $this->operator->tin);
    $excel->getActiveSheet()->SetCellValue('B3', SGS::date($args['inspection_date'], SGS::US_DATE_FORMAT));
    $excel->getActiveSheet()->SetCellValue('F3', $this->inspected_by);
    $excel->getActiveSheet()->SetCellValue('B5', ''); // origin
    $excel->getActiveSheet()->SetCellValue('B6', ''); // east from origin
    $excel->getActiveSheet()->SetCellValue('B7', ''); // north/south from previous
    $excel->getActiveSheet()->SetCellValue('B8', ''); // west from previous
  }

  public function download_data($values, $errors, $excel, $row) {
    $excel->getActiveSheet()->SetCellValue('A'.$row, $values['barcode']);
    $excel->getActiveSheet()->SetCellValue('C'.$row, $values['survey_line']);
    $excel->getActiveSheet()->SetCellValue('D'.$row, $values['cell_number']);
    $excel->getActiveSheet()->SetCellValue('E'.$row, $values['species_code']);
    $excel->getActiveSheet()->SetCellValue('F'.$row, $values['diameter']);
    $excel->getActiveSheet()->SetCellValue('G'.$row, $values['height']);
  }

  public function download_headers($values, $excel, $args, $headers = TRUE) {
    if ($headers) {
      $excel->getActiveSheet()->SetCellValue('D1', 'STOCK SURVEY VERIFICATION FORM');
      $excel->getActiveSheet()->SetCellValue('J1', 'SOP7-4'); // don't know what this is for
      $excel->getActiveSheet()->SetCellValue('A2', 'Site Type and Reference:');
      $excel->getActiveSheet()->SetCellValue('F2', 'Operator TIN:');
      $excel->getActiveSheet()->SetCellValue('A3', 'Date Inspected:');
      $excel->getActiveSheet()->SetCellValue('F3', 'Inspector:');
      $excel->getActiveSheet()->SetCellValue('A4', 'UTM Coordinates of the 4 Corners of the Block Map:');
      $excel->getActiveSheet()->SetCellValue('E4', 'Easting');
      $excel->getActiveSheet()->SetCellValue('G4', 'Northing');
      $excel->getActiveSheet()->SetCellValue('A5', 'Origin (0 meter 0 meter)');
      $excel->getActiveSheet()->SetCellValue('A6', 'East from Origin');
      $excel->getActiveSheet()->SetCellValue('A7', 'North/South from Previous');
      $excel->getActiveSheet()->SetCellValue('A8', 'West from Previous');
      $excel->getActiveSheet()->SetCellValue('A9', 'Tree Barcode');
      $excel->getActiveSheet()->SetCellValue('C9', 'Cell Reference');
      $excel->getActiveSheet()->SetCellValue('E9', 'Species Code');
      $excel->getActiveSheet()->SetCellValue('F9', 'Diameter (cm)');
      $excel->getActiveSheet()->SetCellValue('G9', "Height (m)");
      $excel->getActiveSheet()->SetCellValue('C10', "Survey Line");
      $excel->getActiveSheet()->SetCellValue('D10', 'Cell Number');
    }

    $excel->getActiveSheet()->SetCellValue('B2', substr($values['site_name'], 0 , 3).'/'.$values['site_name'].'/'.$values['block_name']);
    $excel->getActiveSheet()->SetCellValue('F2', $values['operator_tin']);
    $excel->getActiveSheet()->SetCellValue('B3', SGS::date($args['inspection_date'], SGS::US_DATE_FORMAT));
    $excel->getActiveSheet()->SetCellValue('F3', $values['inspected_by']);
    $excel->getActiveSheet()->SetCellValue('B5', ''); // origin
    $excel->getActiveSheet()->SetCellValue('B6', ''); // east from origin
    $excel->getActiveSheet()->SetCellValue('B7', ''); // north/south from previous
    $excel->getActiveSheet()->SetCellValue('B8', ''); // west from previous
  }

  public function make_suggestions($values, $errors) {
    $suggestions = array();
    foreach ($errors as $field => $options) {
      extract($options);
      $suggest = NULL;
      switch ($field) {
        case 'barcode':
          $args = array(
            'barcodes.type' => array('T', 'P'),
            'operators.id' => SGS::suggest_operator($values['operator_tin'], array(), 'id')
          );
          $suggest = SGS::suggest_barcode($values[$field], $args, 'barcode', TRUE, $min_length ?: 5, $min_similarity ?: 0.3, $max_distance ?: 3, $limit ?: 5, $offset ?: 0);
          break;
        case 'operator_tin':
          $args = array(
            'sites.id' => SGS::suggest_site($values['site_name'], array(), 'id'),
          );
          $suggest = SGS::suggest_operator($values[$field], $args, 'tin', TRUE, $min_length ?: 5, $min_similarity ?: 0.3, $max_distance ?: 3, $limit ?: 10, $offset ?: 0);
          break;
        case 'site_name':
          $args = array(
            'operators.id' => SGS::suggest_operator($values['operator_tin'], array(), 'id')
          );
          $suggest = SGS::suggest_site($values[$field], $args, 'name', TRUE, $min_length ?: 5, $min_similarity ?: 0.3, $max_distance ?: 3, $limit ?: 10, $offset ?: 0);
          break;
        case 'species_code':
          $suggest = SGS::suggest_species($values[$field], array(), 'code', TRUE, $min_length ?: 2, $min_similarity ?: 0.3, $max_distance ?: 3, $limit ?: 10, $offset ?: 0);
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
            ->where($field.'_id', '=', SGS::lookup_barcode($values[$field], 'T', TRUE) ?: NULL);

          if ($operator_id = SGS::lookup_operator($values['operator_tin'], TRUE)) $query->and_where('operator_id', '=', $operator_id);
          if ($site_id     = SGS::lookup_site($values['site_name'], TRUE)) $query->and_where('site_id', '=', $site_id);
          if ($block_id    = SGS::lookup_block($values['site_name'], $values['block_name'], TRUE)) $query->and_where('block_id', '=', $block_id);

          if ($duplicate = $query->execute()->get('id')) $duplicates[$field] = $duplicate;
          break;
      }
    }

    // everything else
    $query = DB::select('id')
      ->from($this->_table_name)
      ->where('survey_line', '=', (int) $values['survey_line'])
      ->and_where('cell_number', '=', (int) $values['cell_number'])
      ->and_where('diameter', 'BETWEEN', SGS::deviation_range(SGS::floatify($values['diameter']), SGS::accuracy('TDF', 'is_matching_diameter')))
      ->and_where('length', 'BETWEEN', SGS::deviation_range(SGS::floatify($values['length'], 1), SGS::accuracy('TDF', 'is_matching_length')));

    if ($species_id  = SGS::lookup_species($values['species_code'], TRUE)) $query->and_where('species_id', '=', $species_id);
    if ($operator_id = SGS::lookup_operator($values['operator_tin'], TRUE)) $query->and_where('operator_id', '=', $operator_id);
    if ($site_id     = SGS::lookup_site($values['site_name'], TRUE)) $query->and_where('site_id', '=', $site_id);
    if ($block_id    = SGS::lookup_block($values['site_name'], $values['block_name'], TRUE)) $query->and_where('block_id', '=', $block_id);

    if ($results = $query->execute()->as_array(NULL, 'id')) foreach (array_filter(array_unique($results)) as $duplicate) $duplicates[] = $duplicate;
    return $duplicates;
  }

  public function run_checks() {}

  public function rules()
  {
    return array(
      'create_date'     => array(array('not_empty'),
                                 array('is_date')),
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
      'diameter'        => array(array('not_empty'),
                                 array('is_measurement_int')),
      'height'          => array(array('not_empty'),
                                 array('is_measurement_float')),
      'inspection_date' => array(array('not_empty'),
                                 array('is_date')),
      'inspected_by'    => array(array('not_empty')),
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
                                array('is_barcode', array(':value', TRUE)),
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
      'inspection_date' => self::$fields['inspection_date'],
      'inspected_by'    => self::$fields['inspected_by'],
//      'user_id'         => self::$fields['user_id'],
//      'timestamp'       => self::$fields['timestamp'],
    );
  }

}
