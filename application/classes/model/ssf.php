<?php

class Model_SSF extends SGS_Form_ORM {

  const PARSE_START = 13;

  public static $fields = array(
    'create_date'       => 'Date Surveyed',
    'operator_tin'      => 'Operator TIN',
    'site_name'         => 'Site Name',
    'site_type'         => 'Site Type',
    'site_reference'    => 'Site Reference',
    'block_coordinates' => 'Block Name',
    'barcode'           => 'Barcode',
    'tree_map_number'   => 'Tree Map Number',
    'survey_line'       => 'Survey Line',
    'cell_number'       => 'Cell Number',
    'species_code'      => 'Species Code',
    'diameter'          => 'Diameter',
    'height'            => 'Height',
    'is_requested'      => 'Is Requested',
    'is_fda_approved'   => 'Is FDA Approved',
    'fda_remarks'       => 'FDA Remarks',
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

  protected function _initialize()
  {
    parent::_initialize();
    $this->_object_plural = 'ssf';
  }

  public function parse_csv($row, &$csv)
  {
    extract(SGS::parse_site_and_block_info($csv[2][B]));
    $data = array(
      'barcode'           => $row[A],
      'tree_map_number'   => $row[B],
      'survey_line'       => $row[C],
      'cell_number'       => $row[D],
      'species_code'      => $row[E],
      'diameter'          => $row[F],
      'height'            => $row[G],
    );

    if (array_filter($data)) return array(
      'create_date'       => $csv[3][B],
      'operator_tin'      => $csv[2][H],
      'site_name'         => $site_name,
      'site_type'         => $site_type,
      'site_reference'    => $site_reference,
      'block_coordinates' => $block_coordinates,
    ) + $data + array(
      'is_requested'      => $row[H] == 'NO' ? 'NO' : 'YES',
      'is_fda_approved'   => $row[I] == 'NO' ? 'NO' : 'YES',
      'fda_remarks'       => $row[J],
    );
  }

  public function parse_data($data)
  {
    if ($data['site_type'] and $data['site_reference']) $this->site = SGS::lookup_site($data['site_type'], $data['site_reference']);
    elseif ($data['site_name']) $this->site = SGS::lookup_site_by_name($data['site_name']);

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
        $this->$key = SGS::lookup_barcode($value); break;

      case 'species_code':
        $this->species = SGS::lookup_species($value); break;

      case 'create_date':
        $this->$key = SGS::date($value, TRUE); break;

      default:
        $this->$key = $value; break;
    }
  }

  public function make_suggestions($values, $errors) {
    $suggestions = array();
    foreach ($errors as $field => $error) {
      switch ($field) {
        case 'barcode':
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

  public static function fields($display = FALSE)
  {
    foreach (self::$fields as $key => $value) switch ($key) {
      case 'site_type':
      case 'site_reference':
        if ($display) continue;
      default:
        $fields[$key] = $value;
    }
    return $fields;
  }

  public function rules()
  {
    return array(
      'site_id'         => array(array('not_empty')),
      'operator_id'     => array(array('not_empty')),
      'block_id'        => array(array('not_empty')),
      'species_id'      => array(array('not_empty')),
      'barcode_id'      => array(array('not_empty'),
                                 array('is_unique', array($this->_table_name, ':field', ':value'))),
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

      'species_code'      => array(array('not_empty'),
                                   array('is_species_code'),
                                   array('is_existing_species'))
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
