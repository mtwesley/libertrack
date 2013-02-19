<?php

class Model_TDF extends SGS_Form_ORM {

  const PARSE_START = 9;

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

  protected $_ignored_columns = array(
    'diameter'
  );

  protected function _initialize()
  {
    parent::_initialize();
    $this->_object_plural = 'tdf';
  }

  public function __get($column) {
    switch ($column) {
      case 'diameter':
        return (($this->bottom_min + $this->bottom_max) / 2);

      default:
        return parent::__get($column);
    }
  }

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


  public static $checks = array(
    'consistency' => array(
      'title'  => 'Data Consistency',
      'checks' => array(
        'is_valid_barcode' => array(
          'title'   => 'Tree barcode assignment is valid',
          'error'   => 'Tree barcode assignment is invalid',
          'warning' => 'Tree barcode is not yet assigned',
         ),
        'is_valid_tree_barcode' => array(
          'title'   => 'Felled tree barcode assignment is valid',
          'error'   => 'Felled tree barcode assignment is invalid',
          'warning' => 'Felled tree barcode is not yet assigned',
         ),
        'is_valid_stump_barcode' => array(
          'title'   => 'Stump barcode assignment is valid',
          'error'   => 'Stump barcode assignment is invalid',
          'warning' => 'Stump barcode is not yet assigned',
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
    )),
    'traceability' => array(
      'title'  => 'Traceability',
      'checks' => array(
        'is_existing_parent' => array(
          'title' => 'Traceable to SSF record',
          'error' => 'Not tracable to SSF record'
        ),
        'is_valid_parent' => array(
          'title' => 'SSF record passed checks and queries',
          'error' => 'SSF record failed checks and queries'
        )
    )),
    'tolerance' => array(
      'title'  => 'Tolerance',
      'checks' => array(
        'is_matching_survey_line' => array(
          'title'   => 'Survey line/cell matches data for SSF record',
          'error'   => 'Survey line/cell does not match data for SSF record',
          'warning' => 'Survey line/cell matches data for SSF record but is inaccurate'
        ),
        'is_matching_diameter' => array(
          'title'   => 'Diameter matches data for SSF record',
          'error'   => 'Diameter does not match data for SSF record',
          'warning' => 'Diameter matches data for SSF record but is inaccurate'
        ),
        'is_matching_length' => array(
          'title'   => 'Length matches data for SSF record',
          'error'   => 'Length does not match data for SSF record',
          'warning' => 'Length matches data for SSF record but is inaccurate'
        ),
        'is_matching_species' => array(
          'title'   => 'Species matches data for SSF record',
          'error'   => 'Species does not match data for SSF record',
          'warning' => 'Species class matches data for SSF record but species code does not'
        ),
        'is_matching_operator' => array(
          'title' => 'Operator matches data for SSF record',
          'error' => 'Operator does not match data for SSF record',
        ),
        'is_matching_site' => array(
          'title' => 'Site matches data for SSF record',
          'error' => 'Site does not match data for SSF record',
        ),
        'is_matching_block' => array(
          'title' => 'Block matches data for SSF record',
          'error' => 'Block does not match data for SSF record',
        )
    )),
  );

  public static function fields()
  {
    return (array) self::$fields;
  }

  public function formo() {
    $array = array(
      'id'            => array('render' => FALSE),
      'barcode'       => array('render' => FALSE),
      'tree_barcode'  => array('render' => FALSE),
      'stump_barcode' => array('render' => FALSE),
      'operator'      => array('render' => FALSE),
      'site'          => array('render' => FALSE),
      'block'         => array('render' => FALSE),
      'status'        => array('render' => FALSE),
      'user'          => array('render' => FALSE),
      'timestamp'     => array('render' => FALSE),
      'species'       => array(
        'orm_primary_val' => 'code',
        'label' => 'Species'
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
    foreach ($errors as $field => $options) {
      extract($options);
      switch ($field) {
        case 'tree_barcode':
          $args = array(
            'barcodes.type' => array('P', 'S'),
            'operators.id' => SGS::suggest_operator($values['operator_tin'], array(), 'id')
          );
          $suggest = SGS::suggest_barcode($values[$field], $args, 'barcode', TRUE, $min_length ?: 2, $limit ?: 20, $offset ?: 0);
          break;
        case 'barcode':
        case 'stump_barcode':
          $args = array(
            'barcodes.type' => array('F', 'P'),
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
    $this->reset_checks();

    $errors    = array();
    $warnings  = array();
    $successes = array();

    // reliability
    if (!($this->operator_id == $this->barcode->printjob->site->operator_id)) $warnings['barcode_id']['is_consistent_operator'] = array('value' => $this->operator->tin, 'comparison' => $this->barcode->printjob->site->operator->tin);
    if (!($this->operator_id == $this->tree_barcode->printjob->site->operator_id)) $warnings['tree_barcode_id']['is_consistent_operator'] = array('value' => $this->operator->tin, 'comparison' => $this->tree_barcode->printjob->site->operator->tin);
    if (!($this->operator_id == $this->stump_barcode->printjob->site->operator_id)) $warnings['stump_barcode_id']['is_consistent_operator'] = array('value' => $this->operator->tin, 'comparison' => $this->stump_barcode->printjob->site->operator->tin);
    if (!($this->operator_id == $this->site->operator_id)) $warnings['site_id']['is_consistent_operator'] = array('value' => $this->operator->tin, 'comparison' => $this->site->operator->tin);
    if (!(in_array('is_consistent_operator', SGS::flattenify($errors + $warnings)))) $successes['operator_id']['is_consistent_operator'] = array('value' => $this->operator->tin, 'comparison' => $this->operator->tin);

    if (!($this->site_id == $this->barcode->printjob->site_id)) $warnings['barcode_id']['is_consistent_site'] = array('value' => $this->site->name, 'comparison' => $this->barcode->printjob->site->name);
    if (!($this->site_id == $this->tree_barcode->printjob->site_id)) $warnings['tree_barcode_id']['is_consistent_site'] = array('value' => $this->site->name, 'comparison' => $this->tree_barcode->printjob->site->name);
    if (!($this->site_id == $this->stump_barcode->printjob->site_id)) $warnings['stump_barcode_id']['is_consistent_site'] = array('value' => $this->site->name, 'comparison' => $this->stump_barcode->printjob->site->name);
    if (!(in_array($this->site, $this->operator->sites->find_all()->as_array()))) $warnings['operator_id']['is_consistent_site'] = array('value' => $this->site->name);
    if (!(in_array('is_consistent_site', SGS::flattenify($errors + $warnings)))) $successes['site_id']['is_consistent_site'] = array('value' => $this->site->name, 'comparison' => $this->site->name);

    if (!(in_array($this->block, $this->barcode->printjob->site->blocks->find_all()->as_array()))) $warnings['barcode_id']['is_consistent_block'] = array('value' => $this->block->name);
    if (!(in_array($this->block, $this->tree_barcode->printjob->site->blocks->find_all()->as_array()))) $warnings['tree_barcode_id']['is_consistent_block'] = array('value' => $this->block->name);
    if (!(in_array($this->block, $this->stump_barcode->printjob->site->blocks->find_all()->as_array()))) $warnings['stump_barcode_id']['is_consistent_block'] = array('value' => $this->block->name);
    if (!(in_array($this->block, $this->site->blocks->find_all()->as_array()))) $warnings['site_id']['is_consistent_block'] = array('value' => $this->block->name);
    if (!(in_array('is_consistent_block', SGS::flattenify($errors + $warnings)))) $successes['block_id']['is_consistent_block'] = array('value' => $this->block->name, 'comparison' => $this->block->name);

    // consistency
    switch ($this->barcode->type) {
      case 'F': $successes['barcode_id']['is_valid_barcode'] = array('value' => SGS::$barcode_type[$this->barcode->type], 'comparison' => SGS::$barcode_type['F']); break;
      default:  $errors['barcode_id']['is_valid_barcode'] = array('value' => SGS::$barcode_type[$this->barcode->type], 'comparison' => SGS::$barcode_type['F']); break;
    }

    switch ($this->tree_barcode->type) {
      case 'T': $successes['tree_barcode_id']['is_valid_tree_barcode'] = array('value' => SGS::$barcode_type[$this->tree_barcode->type], 'comparison' => SGS::$barcode_type['T']); break;
      default:  $errors['tree_barcode_id']['is_valid_tree_barcode'] = array('value' => SGS::$barcode_type[$this->tree_barcode->type], 'comparison' => SGS::$barcode_type['T']); break;
    }

    switch ($this->stump_barcode->type) {
      case 'S': $successes['stump_barcode_id']['is_valid_stump_barcode'] = array('value' => SGS::$barcode_type[$this->stump_barcode->type], 'comparison' => SGS::$barcode_type['S']); break;
      default:  $errors['stump_barcode_id']['is_valid_stump_barcode'] = array('value' => SGS::$barcode_type[$this->stump_barcode->type], 'comparison' => SGS::$barcode_type['S']); break;
    }

    // traceability
    $parent = ORM::factory('SSF')
      ->where('barcode_id', '=', $this->tree_barcode->id)
      ->find();

    if ($parent->loaded()) {
      if ($parent->status != 'A') $errors['tree_barcode_id']['is_valid_parent'] = array('comparison' => SGS::$data_status[$parent->status]);
      else $successes['tree_barcode_id']['is_valid_parent'] = array('comparison' => SGS::$data_status[$parent->status]);

      if (!(ord($this->species->class) >= ord($parent->species->class))) $errors['species_id']['is_matching_species'] = array('value' => $this->species->class, 'comparison' => $parent->species->class);
      else if (!($this->species->code == $parent->species->code)) $warnings['species_id']['is_matching_species'] = array('value' => $this->species->code, 'comparison' => $parent->species->code);

      if (!($this->operator_id == $parent->operator_id)) $errors['operator_id']['is_matching_operator'] = array('value' => $this->operator->tin, 'comparison' => $parent->operator->tin);
      if (!($this->site_id == $parent->site_id)) $errors['site_id']['is_matching_site'] = array('value' => $this->site->name, 'comparison' => $parent->site->name);
      if (!($this->block_id == $parent->block_id)) $errors['block_id']['is_matching_block'] = array('value' => $this->block->name, 'comparison' => $parent->block->name);

      if (!Valid::meets_tolerance($this->survey_line, $parent->survey_line, SGS::TDF_SURVEY_LINE_TOLERANCE)) $errors['survey_line']['is_matching_survey_line'] = array('value' => $this->survey_line, 'comparison' => $parent->survey_line);
      else if (!Valid::meets_tolerance($this->survey_line, $parent->survey_line, SGS::TDF_SURVEY_LINE_ACCURACY)) $warnings['survey_line']['is_matching_survey_line'] = array('value' => $this->survey_line, 'comparison' => $parent->survey_line);

      if (!Valid::meets_tolerance($this->length, $parent->height, SGS::TDF_LENGTH_TOLERANCE)) $errors['length']['is_matching_length'] = array('value' => $this->length, 'comparison' => $parent->height);
      else if (!Valid::meets_tolerance($this->length, $parent->height, SGS::TDF_LENGTH_ACCURACY)) $warnings['length']['is_matching_length'] = array('value' => $this->length, 'comparison' => $parent->height);

      if (!Valid::meets_tolerance($diameter = (($this->bottom_min + $this->bottom_max) / 2), $parent->diameter, SGS::TDF_DIAMETER_TOLERANCE)) {
        $errors['bottom_min']['is_matching_diameter'] = array('value' => $diameter, 'comparison' => $parent->diameter);
        $errors['bottom_max']['is_matching_diameter'] = array('value' => $diameter, 'comparison' => $parent->diameter);
      }
      else if (!Valid::meets_tolerance($diameter = (($this->bottom_min + $this->bottom_max) / 2), $parent->diameter, SGS::TDF_DIAMETER_ACCURACY)) {
        $warnings['bottom_min']['is_matching_diameter'] = array('value' => $diameter, 'comparison' => $parent->diameter);
        $warnings['bottom_max']['is_matching_diameter'] = array('value' => $diameter, 'comparison' => $parent->diameter);
      }

      $successes['tree_barcode_id']['is_existing_parent'] = array('value' => 'Found', 'comparison' => 'Found');
      $successes['tree_barcode_id']['is_valid_parent'] = array('value' => 'Found', 'comparison' => 'Found');
    }
    else {
      $errors['tree_barcode_id']['is_existing_parent'] = array('value' => 'Found', 'comparison' => 'Not Found');
      $errors['tree_barcode_id']['is_valid_parent'] = array('value' => 'Found', 'comparison' => 'Not Found');
    }

    // all tolerance checks fail if any traceability checks fail
    if (array_intersect(SGS::flattenify($errors), array_keys(self::$checks['traceability']['checks']))) {
      foreach (self::$checks['tolerance']['checks'] as $check => $array) $errors['tree_barcode_id'][$check] = array();
    }

    // tolerance successes checks
    if ($parent->loaded()) {
      if (!(in_array('is_matching_operator', SGS::flattenify($errors + $warnings)))) $successes['operator_id']['is_matching_operator'] = array('value' => $this->operator->tin, 'comparison' => $parent->operator->tin);
      if (!(in_array('is_matching_site', SGS::flattenify($errors + $warnings)))) $successes['site_id']['is_matching_site'] = array('value' => $this->site->name, 'comparison' => $parent->site->name);
      if (!(in_array('is_matching_block', SGS::flattenify($errors + $warnings)))) $successes['block_id']['is_matching_block'] = array('value' => $this->block->name, 'comparison' => $parent->block->name);
      if (!(in_array('is_matching_species', SGS::flattenify($errors + $warnings)))) $successes['species_id']['is_matching_species'] = array('value' => $this->species->code, 'comparison' => $parent->species->code);
      if (!(in_array('is_matching_survey_line', SGS::flattenify($errors + $warnings)))) $successes['survey_line']['is_matching_survey_line'] = array('value' => $this->survey_line, 'comparison' => $parent->survey_line);
      if (!(in_array('is_matching_length', SGS::flattenify($errors + $warnings)))) $successes['length']['is_matching_length'] = array('value' => $this->length, 'comparison' => $parent->height);

      if (!(in_array('is_matching_diameter', SGS::flattenify($errors + $warnings)))) {
        $successes['bottom_min']['is_matching_diameter'] = array('value' => $diameter, 'comparison' => $parent->diameter);
        $successes['bottom_max']['is_matching_diameter'] = array('value' => $diameter, 'comparison' => $parent->diameter);
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
