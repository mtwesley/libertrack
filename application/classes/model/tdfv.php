<?php

class Model_TDFV extends SGS_Form_ORM {

  const PARSE_START = 7;

  protected $_table_name = 'tdf_verification';

  protected $_belongs_to = array(
    'site'     => array(),
    'operator' => array(),
    'block'    => array(),
    'barcode'  => array(),
    'species'  => array(),
    'user'     => array(),
  );

  protected $_ignored_columns = array(
    'diameter',
    'bottom_diameter',
    'volume'
  );

  protected function _initialize()
  {
    parent::_initialize();
    $this->_object_plural = 'tdfv';
  }

  public function __get($column) {
    switch ($column) {
      case 'bottom_diameter':
        return SGS::floatify(($this->bottom_min + $this->bottom_max) / 2);

      case 'diameter':
        return SGS::floatify(($this->top_min + $this->top_max + $this->bottom_min + $this->bottom_max) / 4);

      case 'volume':
        return SGS::volumify(($this->diameter / 100), $this->length);

      default:
        return parent::__get($column);
    }
  }

  public function save(Validation $validation = NULL) {
    if ($this->barcode->type == 'L') {
      if ($barcode = SGS::lookup_barcode($this->barcode->barcode, array('F', 'P')) and $barcode->loaded()) $this->barcode = $barcode;
      else {
        $barcode = ORM::factory('barcode')->values($this->barcode->as_array());
        $barcode->id = NULL;
        $barcode->parent_id = NULL;
        $barcode->type = 'F';
        $barcode->save();
        $this->barcode = $barcode;
      }
    }

    parent::save($validation);
  }

  public static $type = 'TDFV';
  public static $verification_type = 'TDFV';

  public static $fields = array(
    'create_date'     => 'Date',
    'operator_tin'    => 'Operator TIN',
    'site_name'       => 'Site Name',
    'block_name'      => 'Block Name',
    'barcode'         => 'Felled Tree Barcode',
    'species_code'    => 'Species Code',
    'bottom_max'      => 'Butt Max',
    'bottom_min'      => 'Butt Min',
    'top_max'         => 'Top Max',
    'top_min'         => 'Top Min',
    'length'          => 'Length',
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
      'id'            => array('render' => FALSE),
      'create_date'   => array('order' => 0, 'attr' => array('class' => 'dpicker')),
      'barcode'       => array('render' => FALSE),
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
      'barcode'           => SGS::barcodify(trim($row[A])),
      'species_code'      => trim($row[B]),
      'bottom_max'        => trim($row[C]),
      'bottom_min'        => trim($row[D]),
      'top_max'           => trim($row[E]),
      'top_min'           => trim($row[F]),
      'length'            => trim($row[G]),
    );

    if (array_filter($data)) return SGS::cleanify(array(
      'create_date'     => SGS::date(trim($csv[3][B] ?: $csv[3][C] ?: $csv[3][D]), SGS::US_DATE_FORMAT, TRUE, TRUE),
      'operator_tin'    => trim($csv[2][F] ?: $csv[2][G]),
      'site_name'       => $site_name,
      'block_name'      => $block_name,
      'inspection_date' => SGS::date(trim($csv[3][B] ?: $csv[3][C] ?: $csv[3][D]), SGS::US_DATE_FORMAT, TRUE, TRUE),
      'inspected_by'    => trim($csv[3][F] ?: $csv[3][G]),
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

      case 'bottom_min':
      case 'bottom_max':
      case 'top_min':
      case 'top_max':
        $this->$key = SGS::floatify($value); break;

      case 'length':
        $this->$key = SGS::floatify($value, 1); break;

      default:
        try { $this->$key = $value; } catch (Exception $e) {} break;
    }
  }

  public function export_data($excel, $row) {
    $excel->getActiveSheet()->SetCellValue('A'.$row, $this->barcode->barcode);
    $excel->getActiveSheet()->SetCellValue('B'.$row, $this->species->code);
    $excel->getActiveSheet()->SetCellValue('C'.$row, $this->bottom_max);
    $excel->getActiveSheet()->SetCellValue('D'.$row, $this->bottom_min);
    $excel->getActiveSheet()->SetCellValue('E'.$row, $this->top_max);
    $excel->getActiveSheet()->SetCellValue('F'.$row, $this->top_min);
    $excel->getActiveSheet()->SetCellValue('G'.$row, $this->length);
  }

  public function export_headers($excel, $args, $headers = TRUE) {
    if ($headers) {
      $excel->getActiveSheet()->SetCellValue('C1', 'TREE DATA VERIFICATION FORM');
      $excel->getActiveSheet()->SetCellValue('A2', 'Site type and Reference:');
      $excel->getActiveSheet()->SetCellValue('E2', 'Operator TIN:');
      $excel->getActiveSheet()->SetCellValue('A3', 'Inspection Date:');
      $excel->getActiveSheet()->SetCellValue('E3', 'Inspector:');
      $excel->getActiveSheet()->SetCellValue('A4', 'Felled Tree Barcode');
      $excel->getActiveSheet()->SetCellValue('B4', 'Species Code');
      $excel->getActiveSheet()->SetCellValue('C4', 'Diameter (cm underbark to the nearest cm)');
      $excel->getActiveSheet()->SetCellValue('G4', 'Length (m) to the nearest 0.1m');
      $excel->getActiveSheet()->SetCellValue('C5', 'Butt');
      $excel->getActiveSheet()->SetCellValue('E5', 'End');
      $excel->getActiveSheet()->SetCellValue('C6', 'Max');
      $excel->getActiveSheet()->SetCellValue('D6', 'Min');
      $excel->getActiveSheet()->SetCellValue('E6', 'Max');
      $excel->getActiveSheet()->SetCellValue('F6', 'Min');
    }

    $excel->getActiveSheet()->SetCellValue('B2', $this->site->type.'/'.$this->site->name.'/'.$this->block->name);
    $excel->getActiveSheet()->SetCellValue('F2', $this->operator->tin);
    $excel->getActiveSheet()->SetCellValue('B3', SGS::date($args['inspection_date'], SGS::US_DATE_FORMAT));
    $excel->getActiveSheet()->SetCellValue('F3', $this->inspected_by);
  }

  public function download_data($values, $errors, $excel, $row) {
    $excel->getActiveSheet()->SetCellValue('A'.$row, $values['barcode']);
    $excel->getActiveSheet()->SetCellValue('B'.$row, $values['species_code']);
    $excel->getActiveSheet()->SetCellValue('C'.$row, $values['bottom_max']);
    $excel->getActiveSheet()->SetCellValue('D'.$row, $values['bottom_min']);
    $excel->getActiveSheet()->SetCellValue('E'.$row, $values['top_max']);
    $excel->getActiveSheet()->SetCellValue('F'.$row, $values['top_min']);
    $excel->getActiveSheet()->SetCellValue('G'.$row, $values['length']);
  }

  public function download_headers($values, $excel, $args, $headers = TRUE) {
    if ($headers) {
      $excel->getActiveSheet()->SetCellValue('C1', 'TREE DATA VERIFICATION FORM');
      $excel->getActiveSheet()->SetCellValue('A2', 'Site type and Reference:');
      $excel->getActiveSheet()->SetCellValue('E2', 'Operator TIN:');
      $excel->getActiveSheet()->SetCellValue('A3', 'Inspection Date:');
      $excel->getActiveSheet()->SetCellValue('E3', 'Inspector:');
      $excel->getActiveSheet()->SetCellValue('A4', 'Felled Tree Barcode');
      $excel->getActiveSheet()->SetCellValue('B4', 'Species Code');
      $excel->getActiveSheet()->SetCellValue('C4', 'Diameter (cm underbark to the nearest cm)');
      $excel->getActiveSheet()->SetCellValue('G4', 'Length (m) to the nearest 0.1m');
      $excel->getActiveSheet()->SetCellValue('C5', 'Butt');
      $excel->getActiveSheet()->SetCellValue('E5', 'End');
      $excel->getActiveSheet()->SetCellValue('C6', 'Max');
      $excel->getActiveSheet()->SetCellValue('D6', 'Min');
      $excel->getActiveSheet()->SetCellValue('E6', 'Max');
      $excel->getActiveSheet()->SetCellValue('F6', 'Min');
    }

    $excel->getActiveSheet()->SetCellValue('B2', substr($values['site_name'], 0 , 3).'/'.$values['site_name'].'/'.$values['block_name']);
    $excel->getActiveSheet()->SetCellValue('F2', $values['operator_tin']);
    $excel->getActiveSheet()->SetCellValue('B3', SGS::date($args['inspection_date'], SGS::US_DATE_FORMAT));
    $excel->getActiveSheet()->SetCellValue('F3', $values['inspected_by']);
  }

  public function make_suggestions($values, $errors) {
    $suggestions = array();
    foreach ($errors as $field => $options) {
      extract($options);
      switch ($field) {
        case 'barcode':
          $args = array(
            'barcodes.type' => array('F', 'L', 'P'), // could be either type, but must be assigned correct one later
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
          $type = 'F';
          $query = DB::select('id')
            ->from($this->_table_name)
            ->where($field.'_id', '=', SGS::lookup_barcode($values[$field], $type, TRUE) ?: NULL);

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
      ->where('bottom_min', 'BETWEEN', SGS::deviation_range(SGS::floatify($values['bottom_min']), SGS::accuracy(self::$type, 'is_matching_diameter')))
      ->and_where('bottom_max', 'BETWEEN', SGS::deviation_range(SGS::floatify($values['bottom_max']), SGS::accuracy(self::$type, 'is_matching_diameter')))
      ->and_where('top_min', 'BETWEEN', SGS::deviation_range(SGS::floatify($values['top_min']), SGS::accuracy(self::$type, 'is_matching_diameter')))
      ->and_where('top_max', 'BETWEEN', SGS::deviation_range(SGS::floatify($values['top_max']), SGS::accuracy(self::$type, 'is_matching_diameter')))
      ->and_where('length', 'BETWEEN', SGS::deviation_range(SGS::floatify($values['length'], 1), SGS::accuracy(self::$type, 'is_matching_length')));

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
      'create_date'      => array(array('not_empty'),
                                  array('is_date')),
      'site_id'          => array(array('not_empty')),
      'operator_id'      => array(array('not_empty')),
      'block_id'         => array(array('not_empty')),
      'species_id'       => array(array('not_empty')),
      'barcode_id'       => array(array('not_empty'),
                                  array('is_unique', array($this->_table_name, ':field', ':value', $this->id))),
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
      'inspection_date'  => array(array('not_empty'),
                                 array('is_date')),
      'inspected_by'     => array(array('not_empty')),
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
      'top_min'          => self::$fields['top_min'],
      'top_max'          => self::$fields['top_max'],
      'bottom_min'       => self::$fields['bottom_min'],
      'bottom_max'       => self::$fields['bottom_max'],
      'length'           => self::$fields['length'],
      'inspection_date'  => self::$fields['inspection_date'],
      'inspected_by'     => self::$fields['inspected_by'],
//      'user_id'         => self::$fields['user_id'],
//      'timestamp'       => self::$fields['timestamp'],
    );
  }

}
