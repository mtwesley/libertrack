<?php

class Model_SSF extends SGS_Form_ORM {

  const PARSE_START = 13;

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

  public static $type = 'SSF';

  public static $fields = array(
    'create_date'     => 'Date Surveyed',
    'operator_tin'    => 'Operator TIN',
    'site_name'       => 'Site Name',
    'block_name'      => 'Block Name',
    'barcode'         => 'Tree Barcode',
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

  public static $checks = array(
    'consistency' => array(
      'title'  => 'Data Consistency',
      'checks' => array(
        'is_valid_barcode' => array(
          'title' => 'Tree barcode assignment is valid',
          'error' => 'Tree barcode assignment is invalid',
        )
    )),
    'reliability' => array(
      'title'  => 'Data Reliability',
      'checks' => array(
        'is_consistent_operator' => array(
          'title'   => 'Operator assignments are consistent',
          'warning' => 'Operator assignments are inconsistent'
        ),
        'is_consistent_site' => array(
          'title'   => 'Site assignments are consistent',
          'warning' => 'Site assignments are inconsistent'
        ),
        'is_consistent_block' => array(
          'title'   => 'Block assignments are consistent',
          'warning' => 'Block assignments are inconsistent'
        )
    ))
  );

  public static function fields()
  {
    return (array) self::$fields;
  }

  public function formo() {
    $array = array(
      'id'        => array('render' => FALSE),
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
      ),
      'is_requested' => array(
        'driver'  => 'forceselect',
        'options' => array(TRUE => 'YES', FALSE => 'NO'),
        'is_required' => TRUE
      ),
      'is_fda_approved' => array(
        'driver'  => 'forceselect',
        'options' => array(TRUE => 'YES', FALSE => 'NO')
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
      'barcode'           => SGS::barcodify(trim($row[A])),
      'tree_map_number'   => trim($row[B]),
      'survey_line'       => trim($row[C]),
      'cell_number'       => trim($row[D]),
      'species_code'      => trim($row[E]),
      'diameter'          => trim($row[F]),
      'height'            => trim($row[G]),
    );

    if (array_filter($data)) return SGS::cleanify(array(
      'create_date'     => SGS::date(trim($csv[3][B] ?: $csv[3][C] ?: $csv[3][D] ?: $csv[3][E]), SGS::US_DATE_FORMAT, TRUE, TRUE),
      'operator_tin'    => trim($csv[2][H] ?: $csv[2][I] ?: $csv[2][J]),
      'site_name'       => $site_name,
      'block_name'      => $block_name,
    ) + $data + array(
      'is_requested'    => trim($row[H]) == 'NO' ? 'NO' : 'YES',
      'is_fda_approved' => trim($row[I]) == 'NO' ? 'NO' : 'YES',
      'fda_remarks'     => trim($row[J]),
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

      case 'block_name':
        $this->block = SGS::lookup_block($data['site_name'], $value); break;

      case 'barcode':
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
      $excel->getActiveSheet()->SetCellValue('A11', 'Tree Barcode');
      $excel->getActiveSheet()->SetCellValue('B11', 'Tree Map Number');
      $excel->getActiveSheet()->SetCellValue('C11', 'Cell Reference');
      $excel->getActiveSheet()->SetCellValue('E11', 'Species Code');
      $excel->getActiveSheet()->SetCellValue('F11', 'Diameter Class Number (cm)');
      $excel->getActiveSheet()->SetCellValue('G11', "Height (m)");
      $excel->getActiveSheet()->SetCellValue('H11', 'Crop Trees');
      $excel->getActiveSheet()->SetCellValue('J11', 'FDA Remarks/Reason for Rejection');
      $excel->getActiveSheet()->SetCellValue('C12', "Survey Line");
      $excel->getActiveSheet()->SetCellValue('D12', 'Cell ID Number');
      $excel->getActiveSheet()->SetCellValue('H12', 'Requested');
      $excel->getActiveSheet()->SetCellValue('I12', 'FDA Approved');
    }

    $excel->getActiveSheet()->SetCellValue('B2', $this->site->type.'/'.$this->site->name.'/'.$this->block->name);
    $excel->getActiveSheet()->SetCellValue('H2', $this->operator->tin);
    $excel->getActiveSheet()->SetCellValue('B3', SGS::date($args['create_date'], SGS::US_DATE_FORMAT));
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

  public function download_data($values, $errors, $excel, $row) {
    $excel->getActiveSheet()->SetCellValue('A'.$row, $values['barcode']);
    $excel->getActiveSheet()->SetCellValue('B'.$row, $values['tree_map_number']);
    $excel->getActiveSheet()->SetCellValue('C'.$row, $values['survey_line']);
    $excel->getActiveSheet()->SetCellValue('D'.$row, $values['cell_number']);
    $excel->getActiveSheet()->SetCellValue('E'.$row, $values['species_code']);
    $excel->getActiveSheet()->SetCellValue('F'.$row, $values['diameter']);
    $excel->getActiveSheet()->SetCellValue('G'.$row, $values['height']);
    $excel->getActiveSheet()->SetCellValue('H'.$row, $values['is_requested']);
    $excel->getActiveSheet()->SetCellValue('I'.$row, $values['is_fda_approved']);
    $excel->getActiveSheet()->SetCellValue('J'.$row, $values['fda_remarks']);

    if ($errors) {
      foreach ($errors as $field => $array) foreach ((array) $array as $error) $text[] = SGS::decode_error($field, $error, array(':field' => $fields[$field]));
      $excel->getActiveSheet()->SetCellValue('L'.$row, implode(" \n", (array) $text));
      $excel->getActiveSheet()->getStyle('L'.$row)->getAlignment()->setWrapText(true);
    }
  }

  public function download_headers($values, $excel, $args, $headers = TRUE) {
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
      $excel->getActiveSheet()->SetCellValue('A11', 'Tree Barcode');
      $excel->getActiveSheet()->SetCellValue('B11', 'Tree Map Number');
      $excel->getActiveSheet()->SetCellValue('C11', 'Cell Reference');
      $excel->getActiveSheet()->SetCellValue('E11', 'Species Code');
      $excel->getActiveSheet()->SetCellValue('F11', 'Diameter Class Number (cm)');
      $excel->getActiveSheet()->SetCellValue('G11', "Height (m)");
      $excel->getActiveSheet()->SetCellValue('H11', 'Crop Trees');
      $excel->getActiveSheet()->SetCellValue('J11', 'FDA Remarks/Reason for Rejection');
      $excel->getActiveSheet()->SetCellValue('C12', "Survey Line");
      $excel->getActiveSheet()->SetCellValue('D12', 'Cell ID Number');
      $excel->getActiveSheet()->SetCellValue('H12', 'Requested');
      $excel->getActiveSheet()->SetCellValue('I12', 'FDA Approved');
    }

    $excel->getActiveSheet()->SetCellValue('B2', substr($values['site_name'], 0 , 3).'/'.$values['site_name'].'/'.$values['block_name']);
    $excel->getActiveSheet()->SetCellValue('H2', $values['operator_tin']);
    $excel->getActiveSheet()->SetCellValue('B3', SGS::date($args['create_date'], SGS::US_DATE_FORMAT));
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
    foreach ($errors as $field => $options) {
      extract($options);
      $suggest = NULL;
      switch ($field) {
        case 'barcode':
          $args = array(
            'barcodes.type' => array('T', 'P'),
            'operators.id' => SGS::suggest_operator($values['operator_tin'], array(), 'id')
          );
          $suggest = SGS::suggest_barcode($values[$field], $args, 'barcode', TRUE, $min_length ?: 2, $limit ?: 20, $offset ?: 0);
          break;
        case 'operator_tin':
          $args = array(
            'sites.id' => SGS::suggest_site($values['site_name'], array(), 'id'),
          );
          $suggest = SGS::suggest_operator($values[$field], $args, 'tin', TRUE, $min_length ?: 5, $limit ?: 10, $offset ?: 0);
          break;
        case 'site_name':
          $args = array(
            'operators.id' => SGS::suggest_operator($values['operator_tin'], array(), 'id')
          );
          $suggest = SGS::suggest_site($values[$field], $args, 'name', TRUE, $min_length ?: 5, $limit ?: 10, $offset ?: 0);
          break;
        case 'species_code':
          $suggest = SGS::suggest_species($values[$field], array(), 'code', TRUE, $min_length ?: 2, $limit ?: 10, $offset ?: 0);
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
      ->and_where('tree_map_number', '=', (int) $values['tree_map_number']);

    if ($species_id  = SGS::lookup_species($values['species_code'], TRUE)) $query->and_where('species_id', '=', $species_id);
    if ($operator_id = SGS::lookup_operator($values['operator_tin'], TRUE)) $query->and_where('operator_id', '=', $operator_id);
    if ($site_id     = SGS::lookup_site($values['site_name'], TRUE)) $query->and_where('site_id', '=', $site_id);
    if ($block_id    = SGS::lookup_block($values['site_name'], $values['block_name'], TRUE)) $query->and_where('block_id', '=', $block_id);

    if ($duplicate = $query->execute()->get('id')) $duplicates[] = $duplicate;
    return $duplicates;
  }

  public function run_checks() {
    $errors   = array();
    $warnings = array();

    $this->unset_errors();
    $this->unset_warnings();

    // warnings
    if (!($this->operator_id == $this->barcode->printjob->site->operator_id)) $warnings['barcode_id'][] = 'is_consistent_operator';
    if (!($this->operator_id == $this->site->operator_id)) $warnings['site_id'][] = 'is_consistent_operator';

    if (!(in_array($this->site, $this->operator->sites->find_all()->as_array()))) $warnings['operator_id'][] = 'is_consistent_site';

    if (!(in_array($this->block, $this->barcode->printjob->site->blocks->find_all()->as_array()))) $warnings['barcode_id'][] = 'is_consistent_block';
    if (!(in_array($this->block, $this->site->blocks->find_all()->as_array()))) $warnings['site_id'][] = 'is_consistent_block';

    // errors
    switch ($this->barcode->type) {
      case 'T': break;
      default:  $errors['barcode_id'][] = 'is_valid_barcode'; break;
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
      'is_requested'    => self::$fields['is_requested'],
      'is_fda_approved' => self::$fields['is_fda_approved'],
      'fda_remarks'     => self::$fields['fda_remarks'],
//      'user_id'         => self::$fields['user_id'],
//      'timestamp'       => self::$fields['timestamp'],
    );
  }

  public function set($column, $value) {
    switch ($column) {
      case 'is_requested':
      case 'is_fda_approved':
        parent::set($column, SGS::booleanify($value));
      default:
        parent::set($column, $value);
    }
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
