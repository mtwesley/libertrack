<?php

class Model_LDFV extends SGS_Form_ORM {

  const PARSE_START = 7;

  protected $_table_name = 'ldf_verification';

  protected $_belongs_to = array(
    'site'     => array(),
    'operator' => array(),
    'barcode'  => array(),
    'species'  => array(),
    'user'     => array(),
  );

  protected $_ignored_columns = array(
    'diameter'
  );

  protected function _initialize() {
    parent::_initialize();
    $this->_object_plural = 'ldfv';
  }

  public function __get($column) {
    switch ($column) {
      case 'volume':
        return SGS::volumify(($this->diameter / 100), $this->length);

      case 'diameter':
        return SGS::floatify(($this->top_min + $this->top_max + $this->bottom_min + $this->bottom_max) / 4);

      default:
        return parent::__get($column);
    }
  }

  public function set($column, $value) {
    switch ($column) {
      case 'volume':
        $this->original_volume = $value;
        parent::set($column, $this->volume);

      default:
        parent::set($column, $value);
    }
  }

  public function save(Validation $validation = NULL) {
    if ($this->barcode->type == 'F') {
      if ($barcode = SGS::lookup_barcode($this->barcode->barcode, array('L', 'P')) and $barcode->loaded()) $this->barcode = $barcode;
      else {
        $barcode = ORM::factory('barcode')->values($this->barcode->as_array());
        $barcode->id = NULL;
        $barcode->parent_id = NULL;
        $barcode->type = 'L';
        $barcode->save();
        $this->barcode = $barcode;
      }
    }

    if (($this->barcode->barcode == $this->parent_barcode->barcode) and ($this->parent_barcode->type == 'L')) {
      if ($parent_barcode = SGS::lookup_barcode($this->barcode->barcode, array('F', 'P')) and $parent_barcode->loaded()) $this->parent_barcode = $parent_barcode;
      else {
        $parent_barcode = ORM::factory('barcode')->values($this->barcode->as_array());
        $parent_barcode->id = NULL;
        $parent_barcode->parent_id = NULL;
        $parent_barcode->type = 'F';
        $parent_barcode->save();
        $this->parent_barcode = $parent_barcode;
      }
    }

    if (($this->barcode->barcode != $this->parent_barcode->barcode) and ($this->parent_barcode->type == 'F') and ($parent_barcode = SGS::lookup_barcode($this->parent_barcode->barcode, 'L') and $parent_barcode->loaded())) $this->parent_barcode = $parent_barcode;

    parent::save($validation);
  }

  public static $type = 'LDFV';
  public static $verification_type = 'LDFV';

  public static $fields = array(
    'create_date'      => 'Date',
    'operator_tin'     => 'Operator TIN',
    'site_name'        => 'Site Name',
    'barcode'          => 'New Cross Cut Barcode',
    'species_code'     => 'Species Code',
    'bottom_max'       => 'Butt Max',
    'bottom_min'       => 'Butt Min',
    'top_max'          => 'Top Max',
    'top_min'          => 'Top Min',
    'length'           => 'Length',
    'volume'           => 'Volume',
//    'inspection_date'  => 'Inspection Date',
    'inspected_by'     => 'Inspector',
  );

  public static $checks = array();

  public function formo() {
    $array = array(
      'id'             => array('render' => FALSE),
      'create_date'    => array('order' => 0, 'attr' => array('class' => 'dpicker')),
      'barcode'        => array('render' => FALSE),
      'operator'       => array('render' => FALSE),
      'site'           => array('render' => FALSE),
      'status'         => array('render' => FALSE),
      'user'           => array('render' => FALSE),
      'timestamp'      => array('render' => FALSE),
      'species'        => array(
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
    extract(SGS::parse_site_and_block(trim($csv[2][B] ?: $csv[2][C] ?: $csv[2][D] ?: $csv[2][E])));
    $data = array(
      'barcode'        => SGS::barcodify(trim($row[A])),
      'species_code'   => trim($row[B]),
      'bottom_max'     => trim($row[C]),
      'bottom_min'     => trim($row[D]),
      'top_max'        => trim($row[E]),
      'top_min'        => trim($row[F]),
      'length'         => trim($row[G]),
      'volume'         => trim($row[H]),
    );

    if (array_filter($data)) return SGS::cleanify(array(
      'create_date'     => SGS::date(trim($csv[3][B] ?: $csv[3][C] ?: $csv[3][D]), SGS::US_DATE_FORMAT, TRUE, TRUE),
      'operator_tin'    => trim($csv[2][F] ?: $csv[2][G] ?: $csv[2][H]),
      'site_name'       => $site_name,
      'inspection_date' => SGS::date(trim($csv[3][B] ?: $csv[3][C] ?: $csv[3][D]), SGS::US_DATE_FORMAT, TRUE, TRUE),
      'inspected_by'    => trim($csv[3][F] ?: $csv[3][G] ?: $csv[3][H]),
    ) + $data);
  }

  public function parse_data($data)
  {
    foreach ($data as $key => $value) switch ($key) {
      case 'operator_tin':
        $this->operator = SGS::lookup_operator($value); break;

      case 'site_name':
        $this->site = SGS::lookup_site($value); break;

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
    $excel->getActiveSheet()->SetCellValue('H'.$row, $this->volume);
  }

  public function export_headers($excel, $args, $headers = TRUE) {
    if ($headers) {
      $excel->getActiveSheet()->SetCellValue('C1', 'LOG DATA VERIFICATION FORM');
      $excel->getActiveSheet()->SetCellValue('A2', 'Site type and Reference:');
      $excel->getActiveSheet()->SetCellValue('E2', 'Operator TIN:');
      $excel->getActiveSheet()->SetCellValue('A3', 'Inspection Date:');
      $excel->getActiveSheet()->SetCellValue('E3', 'Inspector:');
      $excel->getActiveSheet()->SetCellValue('A4', 'New Cross Cut Barcode');
      $excel->getActiveSheet()->SetCellValue('B4', 'Species Code');
      $excel->getActiveSheet()->SetCellValue('C4', 'Diameter (cm underbark to the nearest cm)');
      $excel->getActiveSheet()->SetCellValue('G4', 'Length (m) to the nearest 0.1m');
      $excel->getActiveSheet()->SetCellValue('H4', 'Volume (m3)');
      $excel->getActiveSheet()->SetCellValue('C5', 'Butt');
      $excel->getActiveSheet()->SetCellValue('E5', 'Top');
      $excel->getActiveSheet()->SetCellValue('C6', 'Max');
      $excel->getActiveSheet()->SetCellValue('D6', 'Min');
      $excel->getActiveSheet()->SetCellValue('E6', 'Max');
      $excel->getActiveSheet()->SetCellValue('F6', 'Min');
    }

    $excel->getActiveSheet()->SetCellValue('B2', $this->site->type.'/'.$this->site->name);
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
    $excel->getActiveSheet()->SetCellValue('H'.$row, $values['volume']);
  }

  public function download_headers($values, $excel, $args, $headers = TRUE) {
    if ($headers) {
      $excel->getActiveSheet()->SetCellValue('C1', 'LOG DATA VERIFICATION FORM');
      $excel->getActiveSheet()->SetCellValue('A2', 'Site type and Reference:');
      $excel->getActiveSheet()->SetCellValue('E2', 'Operator TIN:');
      $excel->getActiveSheet()->SetCellValue('A3', 'Inspection Date:');
      $excel->getActiveSheet()->SetCellValue('E3', 'Inspector:');
      $excel->getActiveSheet()->SetCellValue('A4', 'New Cross Cut Barcode');
      $excel->getActiveSheet()->SetCellValue('B4', 'Species Code');
      $excel->getActiveSheet()->SetCellValue('C4', 'Diameter (cm underbark to the nearest cm)');
      $excel->getActiveSheet()->SetCellValue('G4', 'Length (m) to the nearest 0.1m');
      $excel->getActiveSheet()->SetCellValue('H4', 'Volume (m3)');
      $excel->getActiveSheet()->SetCellValue('C5', 'Butt');
      $excel->getActiveSheet()->SetCellValue('E5', 'Top');
      $excel->getActiveSheet()->SetCellValue('C6', 'Max');
      $excel->getActiveSheet()->SetCellValue('D6', 'Min');
      $excel->getActiveSheet()->SetCellValue('E6', 'Max');
      $excel->getActiveSheet()->SetCellValue('F6', 'Min');
   }

    $excel->getActiveSheet()->SetCellValue('B2', substr($values['site_name'], 0 , 3).'/'.$values['site_name']);
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
          $suggest = SGS::suggest_barcode($values[$field], $args, 'barcode', TRUE, $min_length ?: 5, $min_similarity ?: 0.3, $max_distance ?: 3, $limit ?: 5, $offset ?: 0, $min_length ?: 2, $limit ?: 20, $offset ?: 0);
          break;
        case 'operator_tin':
          $args = array(
            'sites.id' => SGS::suggest_site($values['site_name'], array(), 'id'),
          );
          $suggest = SGS::suggest_operator($values[$field], $args, 'tin', TRUE, $min_length ?: 5, $min_similarity ?: 0.3, $max_distance ?: 3, $limit ?: 10, $offset ?: 0, $min_length ?: 5, $limit ?: 10, $offset ?: 0);
          break;
        case 'site_name':
          $args = array(
            'operators.id' => SGS::suggest_operator($values['operator_tin'], array(), 'id')
          );
          $suggest = SGS::suggest_site($values[$field], $args, 'name', TRUE, $min_length ?: 2, $min_similarity ?: 0.3, $max_distance ?: 3, $limit ?: 10, $offset ?: 0, $min_length ?: 5, $limit ?: 10, $offset ?: 0);
          break;
        case 'species_code':
          $suggest = SGS::suggest_species($values[$field], array(), 'code', TRUE, $min_length ?: 2, $min_similarity ?: 0.3, $max_distance ?: 3, $limit ?: 10, $offset ?: 0, $min_length ?: 2, $limit ?: 10, $offset ?: 0);
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
          $query = DB::select('id')
            ->from($this->_table_name)
            ->where($field.'_id', '=', ($val = SGS::lookup_barcode($values[$field], TRUE)) ? $val : NULL);

          if ($operator_id = SGS::lookup_operator($values['operator_tin'], TRUE)) $query->and_where('operator_id', '=', $operator_id);
          if ($site_id     = SGS::lookup_site($values['site_name'], TRUE)) $query->and_where('site_id', '=', $site_id);

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
      ->and_where('length', 'BETWEEN', SGS::deviation_range(SGS::floatify($values['length'], 1), SGS::accuracy(self::$type, 'is_matching_length')))
      ->and_where('volume', 'BETWEEN', SGS::deviation_range(SGS::quantitify($values['volume']), SGS::accuracy(self::$type, 'is_matching_volume')));

    if ($species_id  = SGS::lookup_species($values['species_code'], TRUE)) $query->and_where('species_id', '=', $species_id);
    if ($operator_id = SGS::lookup_operator($values['operator_tin'], TRUE)) $query->and_where('operator_id', '=', $operator_id);
    if ($site_id     = SGS::lookup_site($values['site_name'], TRUE)) $query->and_where('site_id', '=', $site_id);

    if ($results = $query->execute()->as_array(NULL, 'id')) foreach (array_filter(array_unique($results)) as $duplicate) $duplicates[] = $duplicate;
    return $duplicates;
  }

  public function run_checks() {}

  public static function fields()
  {
    return (array) self::$fields;
  }

  public function rules()
  {
    return array(
      'create_date'        => array(array('not_empty'),
                                    array('is_date')),
      'site_id'            => array(array('not_empty')),
      'operator_id'        => array(array('not_empty')),
      'species_id'         => array(array('not_empty')),
      'barcode_id'         => array(array('not_empty'),
                                    array('is_unique', array($this->_table_name, ':field', ':value', $this->id))),
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
      'volume'             => array(array('not_empty'),
                                    array('is_measurement_float')),
      'inspection_date'    => array(array('not_empty'),
                                    array('is_date')),
      'inspected_by'       => array(array('not_empty')),
      'user_id'            => array(),
      'timestamp'          => array()
    );
  }

  public function other_rules()
  {
    return array(
      'operator_tin'      => array(array('not_empty'),
                                   array('is_operator_tin'),
                                   array('is_existing_operator')),
      'site_name'         => array(array('is_text_short'),
                                   array('is_existing_site')),
      'barcode'           => array(array('not_empty'),
                                   array('is_barcode', array(':value', TRUE)),
                                   array('is_existing_barcode')),
      'species_code'      => array(array('not_empty'),
                                   array('is_species_code'),
                                   array('is_existing_species'))
    );
  }

  public function labels()
  {
    return array(
      'create_date'        => self::$fields['create_date'],
      'operator_id'        => 'Operator',
      'site_id'            => 'Site',
      'species_id'         => 'Species',
      'barcode_id'         => self::$fields['barcode'],
      'top_min'            => self::$fields['top_min'],
      'top_max'            => self::$fields['top_max'],
      'bottom_min'         => self::$fields['bottom_min'],
      'bottom_max'         => self::$fields['bottom_max'],
      'length'             => self::$fields['length'],
      'volume'             => self::$fields['volume'],
      'inspection_date'    => self::$fields['inspection_date'],
      'inspected_by'       => self::$fields['inspected_by'],
//      'user_id'         => self::$fields['user_id'],
//      'timestamp'       => self::$fields['timestamp'],
    );
  }

}