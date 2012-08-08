<?php

class Model_TDF extends SGS_Form_ORM {

  const PARSE_START = 13;

  public static $fields = array(
    'operator_tin'      => 'Operator TIN',
    'site_name'         => 'Site Name',
    'site_type'         => 'Site Type',
    'site_reference'    => 'Site Reference',
    'block_coordinates' => 'Block Name',
    'survey_line'       => 'Survey Line',
    'cell_number'       => 'Cell Number',
    'tree_barcode'      => 'Tree Barcode',
    'species_code'      => 'Species Code',
    'barcode'           => 'New Cross Cut Barcode',
    'bottom_max'        => 'Butt Max',
    'bottom_min'        => 'Butt Min',
    'top_max'           => 'Top Max',
    'top_min'           => 'Top Min',
    'stump_barcode'     => 'Stump Barcode',
    'length'            => 'Length',
    'action'            => 'Action',
    'comment'           => 'Comment',
    'create_date'       => 'Date Registered',
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
    if ( ! (array_filter($row))) return null;

    $matches = array();
    preg_match('/((([A-Z]+)\/)?([A-Z1-9\s-_]+)?)\/?([A-Z1-9]+)/', $csv[2][B], $matches);

    $create_date       = $csv[3][B];
    $operator_tin      = $csv[2][G];
    $site_name         = $matches[1];
    $site_type         = $matches[3];
    $site_reference    = $matches[4];
    $block_coordinates = $matches[5];

    return array(
      'operator_tin'      => $operator_tin,
      'site_name'         => $site_name,
      'site_type'         => $site_type,
      'site_reference'    => $site_reference,
      'block_coordinates' => $block_coordinates,
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
      'create_date'       => $create_date,
    );
  }

  public function parse_data($data)
  {
    $this->site = SGS::lookup_site($data['site_type'], $data['site_reference']);

    foreach ($data as $key => $value) switch ($key) {
      case 'operator_tin':
        $this->operator = SGS::lookup_operator($value); break;

      case 'site_name':
      case 'site_type':
      case 'site_reference':
        break;

      case 'block_coordinates':
        $this->block = SGS::lookup_block($data['site_type'], $data['site_reference'], $value); break;

      case 'barcode':
      case 'tree_barcode':
      case 'stump_barcode':
        $this->$key = SGS::lookup_barcode($value);
        break;

      case 'species_code':
        $this->species = SGS::lookup_species($value); break;

      case 'create_date':
        $this->$key = Date::formatted_time($value, SGS::PGSQL_DATE_FORMAT); break;

      default:
        $this->$key = $value; break;
    }
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

  public function fields()
  {
    return array(
      'operator_tin'      => 'Operator TIN',
      'site_name'         => 'Site Name',
      'site_type'         => 'Site Type',
      'site_reference'    => 'Site Reference',
      'block_coordinates' => 'Block Name',
      'survey_line'       => 'Survey Line',
      'cell_number'       => 'Cell Number',
      'tree_barcode'      => 'Tree Barcode',
      'species_code'      => 'Species Code',
      'barcode'           => 'New Cross Cut Barcode',
      'bottom_max'        => 'Butt Max',
      'bottom_min'        => 'Butt Min',
      'top_max'           => 'Top Max',
      'top_min'           => 'Top Min',
      'stump_barcode'     => 'Stump Barcode',
      'length'            => 'Length',
      'action'            => 'Action',
      'comment'           => 'Comment',
      'create_date'       => 'Date Registered',
    );
  }

  public function rules()
  {
    return array(
      'site_id'          => array(array('not_empty')),
      'operator_id'      => array(array('not_empty')),
      'block_id'         => array(array('not_empty')),
      'species_id'       => array(array('not_empty')),
      'barcode_id'       => array(array('not_empty'),
                                  array('is_unique', array($this->_table_name, ':field', ':value'))),
      'tree_barcode_id'  => array(array('not_empty')),
      'stump_barcode_id' => array(array('not_empty'),
                                  array('is_unique', array($this->_table_name, ':field', ':value'))),
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
      'operator_tin'      => array(array('not_empty'),
                                   array('is_operator_tin'),
                                   array('is_existing_operator')),

      'site_name'         => array(array('is_existing_site_by_name')),
      'site_type'         => array(array('is_site_type')),

      'site_reference'    => array(array('is_site_reference'),
                                   array('is_existing_site', array(':validation', 'site_type', 'site_reference'))),

      'block_coordinates' => array(array('not_empty'),
                                   array('is_block_coordinates'),
                                   array('is_existing_block', array(':validation', 'site_type', 'site_reference', 'block_coordinates'))),

      'barcode'           => array(array('not_empty'),
                                   array('is_barcode'),
                                   array('is_existing_barcode')),

      'tree_barcode'      => array(array('not_empty'),
                                   array('is_barcode'),
                                   array('is_existing_barcode')),

      'stump_barcode'     => array(array('not_empty'),
                                   array('is_barcode'),
                                   array('is_existing_barcode')),

      'species_code'      => array(array('not_empty'),
                                   array('is_species_code'),
                                   array('is_existing_species'))
    );
  }

}
