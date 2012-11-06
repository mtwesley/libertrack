<?php

class Model_TDF extends SGS_Form_ORM {

  const PARSE_START = 9;

  public static $type = 'TDF';

  public static $fields = array(
    'create_date'    => 'Date Registered',
    'operator_tin'   => 'Operator TIN',
    'site_name'      => 'Site Name',
    'block_name'     => 'Block Name',
    'survey_line'    => 'Survey Line',
    'cell_number'    => 'Cell Number',
    'tree_barcode'   => 'Tree Barcode',
    'species_code'   => 'Species Code',
    'barcode'        => 'Felled Tree Barcode',
    'bottom_max'     => 'Butt Max',
    'bottom_min'     => 'Butt Min',
    'top_max'        => 'Top Max',
    'top_min'        => 'Top Min',
    'stump_barcode'  => 'Stump Barcode',
    'length'         => 'Length',
    'action'         => 'Action',
    'comment'        => 'Comment',
  );

  public static $errors = array(
    'all' => array(
      'is_active_barcode'   => ':field must not be pending assignment',
      'is_valid_barcode'    => ':field must be assigned as a felled tree',
      'is_within_tolerance' => ':field must be within tolerance range',
      'is_valid_match'      => ':field must match required value',
      'is_valid_match_ssf'  => ':field must match stock survey data for standing tree',
      'is_existing'         => 'Data must be available',
      'is_existing_ssf'     => 'Stock survey data must be available for standing tree'
    )
  );

  protected $_table_name = 'tdf_data';

  protected $_belongs_to = array(
    'site'     => array(),
    'operator' => array(),
    'block'    => array(),
    'barcode'  => array(),
    'tree_barcode'  => array(
      'model'       => 'barcode',
      'foreign_key' => 'tree_barcode_id'),
    'stump_barcode' => array(
      'model'       => 'barcode',
      'foreign_key' => 'stump_barcode_id'),
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
        $errors[$result['error']][$result['field']][$result['form_data_id']] = $result['form_data_id'];
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
    $this->_object_plural = 'tdf';
  }

  public function parse_csv($row, &$csv)
  {
    extract(SGS::parse_site_and_block(trim($csv[2][B] ?: $csv[2][C] ?: $csv[2][D] ?: $csv[2][E])));
    $data = array(
      'survey_line'       => trim($row[A]),
      'cell_number'       => trim($row[B]),
      'tree_barcode'      => SGS::barcodify(trim($row[C])),
      'species_code'      => trim($row[D]),
      'barcode'           => SGS::barcodify(trim($row[E])),
      'bottom_max'        => trim($row[F]),
      'bottom_min'        => trim($row[G]),
      'top_max'           => trim($row[H]),
      'top_min'           => trim($row[I]),
      'length'            => trim($row[J]),
      'stump_barcode'     => SGS::barcodify(trim($row[K])),
    );

    if (array_filter($data)) return SGS::cleanify(array(
      'create_date'    => SGS::date(trim($csv[3][B] ?: $csv[3][C] ?: $csv[3][D] ?: $csv[3][E]), SGS::US_DATE_FORMAT, TRUE, TRUE), // SGS::date(trim($csv[3][B] ?: $csv[3][C] ?: $csv[3][D] ?: $csv[3][E]), SGS::US_DATE_FORMAT, TRUE, TRUE),
      'operator_tin'   => trim($csv[2][G] ?: $csv[2][H] ?: $csv[2][I] ?: $csv[2][J] ?: $csv[2][K]),
      'site_name'      => $site_name,
      'block_name'     => $block_name,
    ) + $data + array(
      'action'            => trim($row[L]),
      'comment'           => trim($row[M]),
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
        $this->block = SGS::lookup_block($data['site_name'], $value);
        break;

      case 'barcode':
      case 'tree_barcode':
      case 'stump_barcode':
        $this->$key = SGS::lookup_barcode(SGS::barcodify($value)); break;
        break;

      case 'species_code':
        $this->species = SGS::lookup_species($value); break;

      case 'create_date':
        $this->$key = SGS::date($value, SGS::PGSQL_DATE_FORMAT); break;

      default:
        try { $this->$key = $value; } catch (Exception $e) {}
    }
  }

  public function export_data($excel, $row) {
    $excel->getActiveSheet()->SetCellValue('A'.$row, $this->survey_line);
    $excel->getActiveSheet()->SetCellValue('B'.$row, $this->cell_number);
    $excel->getActiveSheet()->SetCellValue('C'.$row, $this->tree_barcode->barcode);
    $excel->getActiveSheet()->SetCellValue('D'.$row, $this->species->code);
    $excel->getActiveSheet()->SetCellValue('E'.$row, $this->barcode->barcode);
    $excel->getActiveSheet()->SetCellValue('F'.$row, $this->bottom_max);
    $excel->getActiveSheet()->SetCellValue('G'.$row, $this->bottom_min);
    $excel->getActiveSheet()->SetCellValue('H'.$row, $this->top_max);
    $excel->getActiveSheet()->SetCellValue('I'.$row, $this->top_min);
    $excel->getActiveSheet()->SetCellValue('J'.$row, $this->length);
    $excel->getActiveSheet()->SetCellValue('K'.$row, $this->stump_barcode->barcode);
    $excel->getActiveSheet()->SetCellValue('L'.$row, $this->action);
    $excel->getActiveSheet()->SetCellValue('M'.$row, $this->comment);
  }

  public function export_headers($excel, $args, $headers = TRUE) {
    if ($headers) {
      $excel->getActiveSheet()->SetCellValue('C1', 'Tree Felling & Stump Registration');
      $excel->getActiveSheet()->SetCellValue('K1', 'SOP10-5'); // don't know
      $excel->getActiveSheet()->SetCellValue('A2', 'Site type and Reference:');
      $excel->getActiveSheet()->SetCellValue('F2', 'Holder TIN:');
      $excel->getActiveSheet()->SetCellValue('A3', 'Date Registered:');
      $excel->getActiveSheet()->SetCellValue('F3', 'Log Measurer:');
      $excel->getActiveSheet()->SetCellValue('F4', 'Signed:');
      $excel->getActiveSheet()->SetCellValue('A5', 'Date Entered in to CoCIS:');
      $excel->getActiveSheet()->SetCellValue('F5', 'Entered By:');
      $excel->getActiveSheet()->SetCellValue('A6', 'Block Map Cell');
      $excel->getActiveSheet()->SetCellValue('C6', 'Tree Barcode');
      $excel->getActiveSheet()->SetCellValue('D6', 'Species Code');
      $excel->getActiveSheet()->SetCellValue('E6', 'Felled Tree Barcode');
      $excel->getActiveSheet()->SetCellValue('F6', 'Diameter (cm underbark to the nearest cm)');
      $excel->getActiveSheet()->SetCellValue('J6', 'Length (m) to the nearest 0.1m');
      $excel->getActiveSheet()->SetCellValue('K6', 'Stump Barcode');
      $excel->getActiveSheet()->SetCellValue('L6', 'Action');
      $excel->getActiveSheet()->SetCellValue('M6', 'Comment');
      $excel->getActiveSheet()->SetCellValue('F7', 'Butt end');
      $excel->getActiveSheet()->SetCellValue('H7', 'Top');
      $excel->getActiveSheet()->SetCellValue('A8', 'Survey Line');
      $excel->getActiveSheet()->SetCellValue('B8', 'Distance Number');
      $excel->getActiveSheet()->SetCellValue('F8', 'Max');
      $excel->getActiveSheet()->SetCellValue('G8', 'Min');
      $excel->getActiveSheet()->SetCellValue('H8', 'Max');
      $excel->getActiveSheet()->SetCellValue('I8', 'Min');
    }

    $excel->getActiveSheet()->SetCellValue('B2', $this->site->type.'/'.$this->site->name.'/'.$this->block->name);
    $excel->getActiveSheet()->SetCellValue('G2', $this->operator->tin);
    $excel->getActiveSheet()->SetCellValue('B3', SGS::date($args['create_date'], SGS::US_DATE_FORMAT));
    $excel->getActiveSheet()->SetCellValue('G3', ''); // log measurer
    $excel->getActiveSheet()->SetCellValue('G4', ''); // signed
    $excel->getActiveSheet()->SetCellValue('B5', ''); // date entered into CoCIS
    $excel->getActiveSheet()->SetCellValue('G5', ''); // entered by
  }

  public function download_data($values, $errors, $excel, $row) {
    $excel->getActiveSheet()->SetCellValue('A'.$row, $values['survey_line']);
    $excel->getActiveSheet()->SetCellValue('B'.$row, $values['cell_number']);
    $excel->getActiveSheet()->SetCellValue('C'.$row, $values['tree_barcode']);
    $excel->getActiveSheet()->SetCellValue('D'.$row, $values['species_code']);
    $excel->getActiveSheet()->SetCellValue('E'.$row, $values['barcode']);
    $excel->getActiveSheet()->SetCellValue('F'.$row, $values['bottom_max']);
    $excel->getActiveSheet()->SetCellValue('G'.$row, $values['bottom_min']);
    $excel->getActiveSheet()->SetCellValue('H'.$row, $values['top_max']);
    $excel->getActiveSheet()->SetCellValue('I'.$row, $values['top_min']);
    $excel->getActiveSheet()->SetCellValue('J'.$row, $values['length']);
    $excel->getActiveSheet()->SetCellValue('K'.$row, $values['stump_barcode']);
    $excel->getActiveSheet()->SetCellValue('L'.$row, $values['action']);
    $excel->getActiveSheet()->SetCellValue('M'.$row, $values['comment']);

    if ($errors) {
      foreach ($errors as $field => $array) foreach ((array) $array as $error) $text[] = SGS::decode_error($field, $error, array(':field' => $fields[$field]));
      $excel->getActiveSheet()->SetCellValue('O'.$row, implode(" \n", (array) $text));
      $excel->getActiveSheet()->getStyle('O'.$row)->getAlignment()->setWrapText(true);
    }
  }

  public function download_headers($values, $excel, $args, $headers = TRUE) {
    if ($headers) {
      $excel->getActiveSheet()->SetCellValue('C1', 'Tree Felling & Stump Registration');
      $excel->getActiveSheet()->SetCellValue('K1', 'SOP10-5'); // don't know
      $excel->getActiveSheet()->SetCellValue('A2', 'Site type and Reference:');
      $excel->getActiveSheet()->SetCellValue('F2', 'Holder TIN:');
      $excel->getActiveSheet()->SetCellValue('A3', 'Date Registered:');
      $excel->getActiveSheet()->SetCellValue('F3', 'Log Measurer:');
      $excel->getActiveSheet()->SetCellValue('F4', 'Signed:');
      $excel->getActiveSheet()->SetCellValue('A5', 'Date Entered in to CoCIS:');
      $excel->getActiveSheet()->SetCellValue('F5', 'Entered By:');
      $excel->getActiveSheet()->SetCellValue('A6', 'Block Map Cell');
      $excel->getActiveSheet()->SetCellValue('C6', 'Tree Barcode');
      $excel->getActiveSheet()->SetCellValue('D6', 'Species Code');
      $excel->getActiveSheet()->SetCellValue('E6', 'Felled Tree Barcode');
      $excel->getActiveSheet()->SetCellValue('F6', 'Diameter (cm underbark to the nearest cm)');
      $excel->getActiveSheet()->SetCellValue('J6', 'Length (m) to the nearest 0.1m');
      $excel->getActiveSheet()->SetCellValue('K6', 'Stump Barcode');
      $excel->getActiveSheet()->SetCellValue('L6', 'Action');
      $excel->getActiveSheet()->SetCellValue('M6', 'Comment');
      $excel->getActiveSheet()->SetCellValue('F7', 'Butt end');
      $excel->getActiveSheet()->SetCellValue('H7', 'Top');
      $excel->getActiveSheet()->SetCellValue('A8', 'Survey Line');
      $excel->getActiveSheet()->SetCellValue('B8', 'Distance Number');
      $excel->getActiveSheet()->SetCellValue('F8', 'Max');
      $excel->getActiveSheet()->SetCellValue('G8', 'Min');
      $excel->getActiveSheet()->SetCellValue('H8', 'Max');
      $excel->getActiveSheet()->SetCellValue('I8', 'Min');
    }

    $excel->getActiveSheet()->SetCellValue('B2', substr($values['site_name'], 0 , 3).'/'.$values['site_name'].'/'.$values['block_name']);
    $excel->getActiveSheet()->SetCellValue('G2', $values['operator_tin']);
    $excel->getActiveSheet()->SetCellValue('B3', SGS::date($args['create_date'], SGS::US_DATE_FORMAT));
    $excel->getActiveSheet()->SetCellValue('G3', ''); // log measurer
    $excel->getActiveSheet()->SetCellValue('G4', ''); // signed
    $excel->getActiveSheet()->SetCellValue('B5', ''); // date entered into CoCIS
    $excel->getActiveSheet()->SetCellValue('G5', ''); // entered by
  }

  public function make_suggestions($values, $errors) {
    $suggestions = array();
    foreach ($errors as $field => $error) {
      switch ($field) {
        case 'tree_barcode':
          $args = array(
            'barcodes.type' => array('P', 'S'),
            'operators.id' => SGS::suggest_operator($values['operator_tin'], array(), 'id')
          );
          $suggest = SGS::suggest_barcode($values[$field], $args, 'barcode');
          break;
        case 'barcode':
        case 'stump_barcode':
          $args = array(
            'barcodes.type' => array('F', 'P'),
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
        case 'tree_barcode':
        case 'stump_barcode':
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
      ->and_where('cell_number', '=', (int) $values['cell_number']);

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
      case 'F': break;
      case 'P': $errors['barcode_id'][] = 'is_active_barcode'; break;
      default:  $errors['barcode_id'][] = 'is_valid_barcode'; break;
    }

    switch ($this->tree_barcode->type) {
      case 'T': break;
      case 'P': $errors['tree_barcode_id'][] = 'is_active_barcode'; break;
      default:  $errors['tree_barcode_id'][] = 'is_valid_barcode'; break;
    }

    switch ($this->stump_barcode->type) {
      case 'S': break;
      case 'P': $errors['stump_barcode_id'][] = 'is_active_barcode'; break;
      default:  $errors['stump_barcode_id'][] = 'is_valid_barcode'; break;
    }

    $ssf = ORM::factory('SSF')
      ->where('barcode_id', '=', $this->tree_barcode->id)
      ->find();

    if ($ssf->loaded()) {
      if (!Valid::meets_tolerance($this->length, $ssf->height, SGS::TDF_HEIGHT_TOLERANCE)) $errors['length'][] = 'is_within_tolerance';
      if (!Valid::meets_tolerance(($this->bottom_min + $this->bottom_max) / 2, $ssf->diameter, SGS::TDF_DIAMETER_TOLERANCE)) {
        $errors['bottom_min'][] = 'is_within_tolerance';
        $errors['bottom_max'][] = 'is_within_tolerance';
      }
      if (!($this->species->class == $ssf->species->class)) $errors['species_id'][] = 'is_valid_match_ssf';
      if (!($this->cell_number == $ssf->cell_number)) $errors['cell_number'][] = 'is_valid_match_ssf';
      if (!($this->survey_line == $ssf->survey_line)) $errors['survey_line'][] = 'is_valid_match_ssf';
    } else $errors['barcode_id'][] = 'is_existing_ssf';

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
      'site_id'          => array(array('not_empty')),
      'operator_id'      => array(array('not_empty')),
      'block_id'         => array(array('not_empty')),
      'species_id'       => array(array('not_empty')),
      'barcode_id'       => array(array('not_empty'),
                                  array('is_unique', array($this->_table_name, ':field', ':value', $this->id))),
      'tree_barcode_id'  => array(array('not_empty')),
      'stump_barcode_id' => array(array('not_empty'),
                                  array('is_unique', array($this->_table_name, ':field', ':value', $this->id))),
      'survey_line'      => array(array('not_empty'),
                                  array('is_survey_line')),
      'cell_number'      => array(array('not_empty'),
                                  array('is_positive_int')),
      'top_min'          => array(array('not_empty'),
                                  array('is_measurement_int')),
      'top_max'          => array(array('not_empty'),
                                  array('is_measurement_int')),
      'bottom_min'       => array(array('not_empty'),
                                  array('is_measurement_int')),
      'bottom_max'       => array(array('not_empty'),
                                  array('is_measurement_int')),
      'length'           => array(array('not_empty'),
                                  array('is_measurement_float')),
      'action'           => array(),
      'comment'          => array(),
      'create_date'      => array(array('not_empty'),
                                  array('is_date')),
      'user_id'          => array(),
      'timestamp'        => array(),
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
      'tree_barcode'   => array(array('not_empty'),
                                array('is_barcode', array(':value', TRUE)),
                                array('is_existing_barcode')),
      'stump_barcode'  => array(array('not_empty'),
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
      'create_date'      => self::$fields['create_date'],
      'operator_id'      => 'Operator',
      'site_id'          => 'Site',
      'block_id'         => 'Block',
      'species_id'       => 'Species',
      'barcode_id'       => self::$fields['barcode'],
      'tree_barcode_id'  => self::$fields['tree_barcode'],
      'stump_barcode_id' => self::$fields['stump_barcode'],
      'survey_line'      => self::$fields['survey_line'],
      'cell_number'      => self::$fields['cell_number'],
      'top_min'          => self::$fields['top_min'],
      'top_max'          => self::$fields['top_max'],
      'bottom_min'       => self::$fields['bottom_min'],
      'bottom_max'       => self::$fields['bottom_max'],
      'length'           => self::$fields['length'],
      'action'           => self::$fields['action'],
      'comment'          => self::$fields['comment'],
//      'user_id'         => self::$fields['user_id'],
//      'timestamp'       => self::$fields['timestamp'],
    );
  }

}
