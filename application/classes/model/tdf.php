<?php

class Model_TDF extends SGS_Form_ORM {

  const PARSE_START = 9;

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

  protected function _initialize()
  {
    parent::_initialize();
    $this->_object_plural = 'tdf';
  }

  public function parse_csv($row, &$csv)
  {
    extract(SGS::parse_site_and_block_info($csv[2][B]));
    $data = array(
      'survey_line'       => $row[A],
      'cell_number'       => $row[B],
      'tree_barcode'      => $row[C],
      'species_code'      => $row[D],
      'barcode'           => $row[E],
      'bottom_max'        => $row[F],
      'bottom_min'        => $row[G],
      'top_max'           => $row[H],
      'top_min'           => $row[I],
      'length'            => $row[J],
      'stump_barcode'     => $row[K],
      'action'            => $row[L],
      'comment'           => $row[M],
    );

    if (array_filter($data)) return SGS::cleanify(array(
      'create_date'    => $csv[3][B],
      'operator_tin'   => $csv[2][G],
      'site_name'      => $site_name,
      'block_name'     => $block_name,
    ) + $data);
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
        $this->$key = SGS::lookup_barcode($value);
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

  public function export_headers($excel, $values, $headers = TRUE) {
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

    $excel->getActiveSheet()->SetCellValue('B2', $this->site->name);
    $excel->getActiveSheet()->SetCellValue('G2', $operator->tin);
    $excel->getActiveSheet()->SetCellValue('B3', SGS::date($values['create_date'], SGS::US_DATE_FORMAT));
    $excel->getActiveSheet()->SetCellValue('G3', ''); // log measurer
    $excel->getActiveSheet()->SetCellValue('G4', ''); // signed
    $excel->getActiveSheet()->SetCellValue('B5', ''); // date entered into CoCIS
    $excel->getActiveSheet()->SetCellValue('G5', ''); // entered by
  }

  public function make_suggestions($values, $errors) {
    $suggestions = array();
    foreach ($errors as $field => $error) {
      switch ($field) {
        case 'barcode':
        case 'tree_barcode':
        case 'stump_barcode':
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

  public function find_duplicates($values, $errors) {
    $duplicates = array();

    foreach ($this->fields() as $field => $label) {
      $duplicate = NULL;
      switch ($field) {
        case 'barcode':
        case 'tree_barcode':
        case 'stump_barcode':
          $duplicate = DB::select('id')
            ->from($this->_table_name)
            ->where($field.'_id', '=', SGS::lookup_barcode($values[$field]))
            ->and_where('operator_id', '=', SGS::lookup_operator($values['operator_tin']))
            ->and_where('site_id', '=', SGS::lookup_site($values['site_name']))
            ->and_where('block_id', '=', SGS::lookup_block($values['site_name'], $values['block_name']))
            ->execute()
            ->get('id');
          break;
      }
      if ($duplicate) $duplicates[$field] = $duplicate;
    }

    // everything else
    $id = DB::select('id')
      ->from($this->_table_name)
      ->where('survey_line', '=', $values['survey_line'])
      ->and_where('cell_number', '=', $values['cell_number'])
      ->and_where('species_code', '=', $values['species_code'])
      ->and_where('operator_id', '=', SGS::lookup_operator($values['operator_tin']))
      ->and_where('site_id', '=', SGS::lookup_site($values['site_name']))
      ->and_where('block_id', '=', SGS::lookup_block($values['site_name'], $values['block_name']))
      ->execute()
      ->get('id');

    if ($id) $duplicates[] = $id;

    return $duplicates;
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
      // 'coc_status'       => array(),
      'create_date'      => array(array('not_empty'),
                                  array('is_date')),
      'user_id'          => array(),
      'timestamp'        => array()
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
      'tree_barcode'   => array(array('not_empty'),
                                array('is_barcode'),
                                array('is_existing_barcode')),
      'stump_barcode'  => array(array('not_empty'),
                                array('is_barcode'),
                                array('is_existing_barcode')),
      'species_code'   => array(array('not_empty'),
                                array('is_species_code'),
                                array('is_existing_species'))
    );
  }

}
