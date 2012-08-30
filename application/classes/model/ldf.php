<?php

class Model_LDF extends SGS_Form_ORM {

  const PARSE_START = 9;

  public static $fields = array(
    'create_date'    => 'Date Registered',
    'operator_tin'   => 'Operator TIN',
    'site_name'      => 'Site Name',
    'parent_barcode' => 'Original Log Barcode',
    'species_code'   => 'Species Code',
    'barcode'        => 'New Cross Cut Barcode',
    'bottom_max'     => 'Butt Max',
    'bottom_min'     => 'Butt Min',
    'top_max'        => 'Top Max',
    'top_min'        => 'Top Min',
    'length'         => 'Length',
    'volume'         => 'Volume',
    'action'         => 'Action',
    'comment'        => 'Comment',
    // 'coc_status'     => 'CoC Status',
  );

  protected $_table_name = 'ldf_data';

  protected $_belongs_to = array(
    'site'     => array(),
    'operator' => array(),
    'block'    => array(),
    'barcode'  => array(),
    'parent_barcode' => array(
      'model'        => 'barcode',
      'foreign_key'  => 'parent_barcode_id'),
    'species'  => array(),
    'user'     => array(),
  );

  protected function _initialize() {
    parent::_initialize();
    $this->_object_plural = 'ldf';
  }

  public function parse_csv($row, &$csv)
  {
    extract(SGS::parse_site_and_block_info($csv[2][B]));
    $data = array(
      'parent_barcode' => $row[A],
      'species_code'   => $row[B],
      'barcode'        => $row[C],
      'bottom_max'     => $row[D],
      'bottom_min'     => $row[E],
      'top_max'        => $row[F],
      'top_min'        => $row[G],
      'length'         => $row[H],
      'volume'         => $row[I],
      'action'         => $row[J],
      'comment'        => $row[K],
      // 'coc_status'     => $row[L],
    );

    if (array_filter($data)) return SGS::cleanify(array(
      'create_date'    => $csv[3][B],
      'operator_tin'   => $csv[4][B],
      'site_name'      => $site_name,
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

      case 'barcode':
      case 'parent_barcode':
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
    $excel->getActiveSheet()->SetCellValue('A'.$row, $this->parent_barcode->barcode);
    $excel->getActiveSheet()->SetCellValue('B'.$row, $this->species->code);
    $excel->getActiveSheet()->SetCellValue('C'.$row, $this->barcode->barcode);
    $excel->getActiveSheet()->SetCellValue('D'.$row, $this->bottom_max);
    $excel->getActiveSheet()->SetCellValue('E'.$row, $this->bottom_min);
    $excel->getActiveSheet()->SetCellValue('F'.$row, $this->top_max);
    $excel->getActiveSheet()->SetCellValue('G'.$row, $this->top_min);
    $excel->getActiveSheet()->SetCellValue('H'.$row, $this->length);
    $excel->getActiveSheet()->SetCellValue('I'.$row, $this->volume);
    $excel->getActiveSheet()->SetCellValue('J'.$row, $this->action);
    $excel->getActiveSheet()->SetCellValue('K'.$row, $this->comment);
//    $excel->getActiveSheet()->SetCellValue('L'.$count, $this->coc_status);
  }

  public function export_headers($excel, $args, $headers = TRUE) {
    if ($headers) {
      $excel->getActiveSheet()->SetCellValue('C1', 'LOG DATA FORM');
      $excel->getActiveSheet()->SetCellValue('K1', 'SOP13-6'); // don't know
      $excel->getActiveSheet()->SetCellValue('A2', 'Site type and Reference:');
      $excel->getActiveSheet()->SetCellValue('F2', 'Site Holder Name:');
      $excel->getActiveSheet()->SetCellValue('A3', 'Date Registered:');
      $excel->getActiveSheet()->SetCellValue('F3', 'Form Reference No.:');
      $excel->getActiveSheet()->SetCellValue('A4', 'Site TIN:');
      $excel->getActiveSheet()->SetCellValue('F4', 'Log Measurer:');
      $excel->getActiveSheet()->SetCellValue('A5', 'Date Entered in to CoCIS:');
      $excel->getActiveSheet()->SetCellValue('F5', 'Entered By:');
      $excel->getActiveSheet()->SetCellValue('A6', 'Original Log Barcode');
      $excel->getActiveSheet()->SetCellValue('B6', 'Species Code');
      $excel->getActiveSheet()->SetCellValue('C6', 'New Cross Cut Barcode');
      $excel->getActiveSheet()->SetCellValue('D6', 'Diameter (cm underbark to the nearest cm)');
      $excel->getActiveSheet()->SetCellValue('H6', 'Length (m) to the nearest 0.1m');
      $excel->getActiveSheet()->SetCellValue('I6', 'Volume declared (m3)');
      $excel->getActiveSheet()->SetCellValue('J6', 'Action');
      $excel->getActiveSheet()->SetCellValue('K6', 'Comment');
      $excel->getActiveSheet()->SetCellValue('D7', 'Butt end');
      $excel->getActiveSheet()->SetCellValue('F7', 'Top');
      $excel->getActiveSheet()->SetCellValue('D8', 'Max');
      $excel->getActiveSheet()->SetCellValue('E8', 'Min');
      $excel->getActiveSheet()->SetCellValue('F8', 'Max');
      $excel->getActiveSheet()->SetCellValue('G8', 'Min');
    }

    $excel->getActiveSheet()->SetCellValue('B2', $this->site->type.'/'.$this->site->name);
    $excel->getActiveSheet()->SetCellValue('G2', $this->operator->tin); // site holder name
    $excel->getActiveSheet()->SetCellValue('B3', SGS::date($args['create_date'], SGS::US_DATE_FORMAT));
    $excel->getActiveSheet()->SetCellValue('G3', ''); // form reference number ?
    $excel->getActiveSheet()->SetCellValue('B4', $this->operator->tin);
    $excel->getActiveSheet()->SetCellValue('G4', ''); // log measurer
    $excel->getActiveSheet()->SetCellValue('B5', ''); // date entered into CoCIS
    $excel->getActiveSheet()->SetCellValue('G5', ''); // entered by
  }

  public function download_data($values, $excel, $row) {
    $excel->getActiveSheet()->SetCellValue('A'.$row, $values['parent_barcode']);
    $excel->getActiveSheet()->SetCellValue('B'.$row, $values['species_code']);
    $excel->getActiveSheet()->SetCellValue('C'.$row, $values['barcode']);
    $excel->getActiveSheet()->SetCellValue('D'.$row, $values['bottom_max']);
    $excel->getActiveSheet()->SetCellValue('E'.$row, $values['bottom_min']);
    $excel->getActiveSheet()->SetCellValue('F'.$row, $values['top_max']);
    $excel->getActiveSheet()->SetCellValue('G'.$row, $values['top_min']);
    $excel->getActiveSheet()->SetCellValue('H'.$row, $values['length']);
    $excel->getActiveSheet()->SetCellValue('I'.$row, $values['volume']);
    $excel->getActiveSheet()->SetCellValue('J'.$row, $values['action']);
    $excel->getActiveSheet()->SetCellValue('K'.$row, $values['comment']);
//    $excel->getActiveSheet()->SetCellValue('L'.$count, $values['coc_status']);
  }

  public function download_headers($values, $excel, $args, $headers = TRUE) {
    if ($headers) {
      $excel->getActiveSheet()->SetCellValue('C1', 'LOG DATA FORM');
      $excel->getActiveSheet()->SetCellValue('K1', 'SOP13-6'); // don't know
      $excel->getActiveSheet()->SetCellValue('A2', 'Site type and Reference:');
      $excel->getActiveSheet()->SetCellValue('F2', 'Site Holder Name:');
      $excel->getActiveSheet()->SetCellValue('A3', 'Date Registered:');
      $excel->getActiveSheet()->SetCellValue('F3', 'Form Reference No.:');
      $excel->getActiveSheet()->SetCellValue('A4', 'Site TIN:');
      $excel->getActiveSheet()->SetCellValue('F4', 'Log Measurer:');
      $excel->getActiveSheet()->SetCellValue('A5', 'Date Entered in to CoCIS:');
      $excel->getActiveSheet()->SetCellValue('F5', 'Entered By:');
      $excel->getActiveSheet()->SetCellValue('A6', 'Original Log Barcode');
      $excel->getActiveSheet()->SetCellValue('B6', 'Species Code');
      $excel->getActiveSheet()->SetCellValue('C6', 'New Cross Cut Barcode');
      $excel->getActiveSheet()->SetCellValue('D6', 'Diameter (cm underbark to the nearest cm)');
      $excel->getActiveSheet()->SetCellValue('H6', 'Length (m) to the nearest 0.1m');
      $excel->getActiveSheet()->SetCellValue('I6', 'Volume declared (m3)');
      $excel->getActiveSheet()->SetCellValue('J6', 'Action');
      $excel->getActiveSheet()->SetCellValue('K6', 'Comment');
      $excel->getActiveSheet()->SetCellValue('D7', 'Butt end');
      $excel->getActiveSheet()->SetCellValue('F7', 'Top');
      $excel->getActiveSheet()->SetCellValue('D8', 'Max');
      $excel->getActiveSheet()->SetCellValue('E8', 'Min');
      $excel->getActiveSheet()->SetCellValue('F8', 'Max');
      $excel->getActiveSheet()->SetCellValue('G8', 'Min');
    }

    $excel->getActiveSheet()->SetCellValue('B2', substr($values['site_name'], 0 , 3).'/'.$values['site_name']);
    $excel->getActiveSheet()->SetCellValue('G2', $values['operator_tin']); // site holder name
    $excel->getActiveSheet()->SetCellValue('B3', SGS::date($args['create_date'], SGS::US_DATE_FORMAT));
    $excel->getActiveSheet()->SetCellValue('G3', ''); // form reference number ?
    $excel->getActiveSheet()->SetCellValue('B4', $values['operator_tin']);
    $excel->getActiveSheet()->SetCellValue('G4', ''); // log measurer
    $excel->getActiveSheet()->SetCellValue('B5', ''); // date entered into CoCIS
    $excel->getActiveSheet()->SetCellValue('G5', ''); // entered by
  }

  public function make_suggestions($values, $errors) {
    $suggestions = array();
    foreach ($errors as $field => $error) {
      switch ($field) {
        case 'barcode':
        case 'parent_barcode':
          $args = array(
            'barcodes.type' => array('P'),
            'sites.id' => SGS::suggest_site($values['site_name'], array(), 'id'),
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
        case 'parent_barcode':
          $query = DB::select('id')
            ->from($this->_table_name)
            ->where($field.'_id', '=', $val = SGS::lookup_barcode($values[$field]) ? $val : NULL);

          if ($operator_id = SGS::lookup_operator($values['operator_tin'], TRUE)) $query->and_where('operator_id', '=', $operator_id);
          if ($site_id     = SGS::lookup_site($values['site_name'], TRUE)) $query->and_where('site_id', '=', $site_id);
          if ($block_id    = SGS::lookup_block($values['site_name'], $values['block_name'], TRUE)) $query->and_where('block_id', '=', $block_id);

          if ($duplicate = $query->execute()->get('id')) $duplicates[$field] = $duplicate;
          break;
      }
    }

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
      'site_id'            => array(array('not_empty')),
      'operator_id'        => array(array('not_empty')),
      'species_id'         => array(array('not_empty')),
      'barcode_id'         => array(array('not_empty'),
                                    array('is_unique', array($this->_table_name, ':field', ':value', $this->id))),
      'parent_barcode_id'  => array(array('not_empty'),
                                    array('is_unique', array($this->_table_name, ':field', ':value', $this->id))),
      'invoice_id'         => array(),
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
      'action'             => array(),
      'comment'            => array(),
      // 'coc_status'         => array(),
      'create_date'        => array(array('not_empty'),
                                    array('is_date')),
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
      'parent_barcode'    => array(array('not_empty'),
                                   array('is_barcode', array(':value', TRUE)),
                                   array('is_existing_barcode')),
      'species_code'      => array(array('not_empty'),
                                   array('is_species_code'),
                                   array('is_existing_species'))
    );
  }

}
