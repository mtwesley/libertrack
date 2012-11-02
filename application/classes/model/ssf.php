<?php

class Model_SSF extends SGS_Form_ORM {

  const PARSE_START = 13;

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

  public static $errors = array(
    'all' => array(
      'is_active_barcode' => ':field must not be pending assignment',
      'is_valid_barcode'  => ':field must be assigned as a standing tree',
    )
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

  public static function generate_report($records) {
    $total = count($records);

    $errors   = array();
    $_records = array();
    if ($records) foreach (DB::select('form_data_id', 'field', 'error')
      ->from('errors')
      ->where('form_type', '=', self::$type)
      ->and_where('form_data_id', 'IN', (array) array_keys($records))
      ->execute()
      ->as_array() as $result) {
        $_records[$result['form_data_id']][$result['field']][] = $result['error'];
        $errors[$result['error']][$result['field']][$result['form_data_id']] = $records[$result['form_data_id']];
    }

    $fail = count($_records);

    return array(
      'total'   => $total,
      'passed'  => $total - $fail,
      'failed'  => $fail,
      'records' => $_records,
      'errors'  => $errors
    );
  }

  public static function fields()
  {
    return (array) self::$fields;
  }

  protected function _initialize()
  {
    parent::_initialize();
    $this->_object_plural = 'ssf';
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

  public function download_data($values, $errors, $suggestions, $duplicates, $excel, $row) {
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
      $excel->getActiveSheet()->SetCellValue('L'.$row, implode(" \n", (array) $errors));
      $excel->getActiveSheet()->getStyle('L'.$row)->getAlignment()->setWrapText(true);
    }

    if ($suggestions) {
      $text = array();
      foreach ($suggestions as $field => $suggestion) {
        $text[] = 'Suggested values for '.self::$fields[$field].': '.implode(', ', $suggestion);
      }
      $excel->getActiveSheet()->SetCellValue('M'.$row, implode(" \n", (array) $text));
      $excel->getActiveSheet()->getStyle('M'.$row)->getAlignment()->setWrapText(true);
    }

    if ($duplicates) {
      $excel->getActiveSheet()->SetCellValue('N'.$row, 'Duplicate found');
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
    foreach ($errors as $field => $error) {
      $suggest = NULL;
      switch ($field) {
        case 'barcode':
          $args = array(
            'barcodes.type' => array('P'),
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
        case 'site_name':
          $args = array(
            'operators.id' => SGS::suggest_operator($values['operator_tin'], array(), 'id')
          );
          $suggest = SGS::suggest_site($values[$field], $args, 'name');
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
    if ($this->status == 'A') return;

    $errors = array();
    $this->unset_errors();

//    if (!($this->operator == $this->barcode->printjob->site->operator)) $errors['operator'][] = 'is_consistent_operator_barcode';
//    if (!($this->operator == $this->site->operator)) $errors['operator'][] = 'is_consistent_operator_site';
//    if (!($this->site == $this->barcode->printjob->site)) $errors['site'][] = 'is_consistent_site_barcode';
//    if (!(in_array($this->site, $this->operator->sites->find_all()->as_array()))) $errors['site'][] = 'is_consistent_site_operator';
//    if (!(in_array($this->block, $this->barcode->printjob->site->blocks->find_all()->as_array()))) $errors['block'][] = 'is_consistent_block_barcode';
//    if (!(in_array($this->block, $this->site->blocks->find_all()->as_array()))) $errors['block'][] = 'is_consistent_block_site';

    switch ($this->barcode->type) {
      case 'T': break;
      case 'P': $errors['barcode'][] = 'is_active_barcode'; break;
      default:  $errors['barcode'][] = 'is_valid_barcode'; break;
    }

    if ($errors) {
      $this->status = 'R';
      foreach ($errors as $field => $array) {
        foreach (array_filter(array_unique($array)) as $error) $this->set_error($field, $error);
      }
    } else $this->status = 'A';

    $this->save();
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
