<?php

class Model_LDF extends SGS_Form_ORM {

  const PARSE_START = 9;

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
    if ( ! (array_filter($row))) return null;

    $matches = array();
    preg_match('/((([A-Z]+)\/)?([A-Z1-9\s-_]+)?)\/?([A-Z1-9]+)/', $csv[2][B], $matches);

    $operator_tin      = $csv[4][B];
    $site_name         = $matches[1];
    $site_type         = $matches[3];
    $site_reference    = $matches[4];
    $block_coordinates = $matches[5];

    return array(
      'operator_tin'   => $operator_tin,
      'site_name'      => $site_name,
      'site_type'      => $site_type,
      'site_reference' => $site_reference,
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

      case 'barcode':
      case 'parent_barcode':
        $this->barcode = SGS::lookup_barcode($value); break;

      case 'species_code':
        $this->species = SGS::lookup_species($value); break;

      default:
        $this->$key = $value; break;
    }
  }

  public function parse_fields()
  {
    return array(
      'operator_tin'   => 'Operator TIN',
      'site_name'      => 'Site Name',
      'site_type'      => 'Site Type',
      'site_reference' => 'Site Reference',
      'parent_barcode' => 'Parent Barcode',
      'species_code'   => 'Species Code',
      'barcode'        => 'Barcode',
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
  }

  public function rules()
  {
    return array(
      'site_id'            => array(array('not_empty')),
      'operator_id'        => array(array('not_empty')),
      'species_id'         => array(array('not_empty')),
      'barcode_id'         => array(array('not_empty'),
                                    array(array($this, 'is_unique'),
                                          array(':field', ':value'))),
      'parent_barcode_id'  => array(array('not_empty')),
      'invoice_id'         => array(),
      'top_min'            => array(array('not_empty')),
      'top_max'            => array(array('not_empty')),
      'bottom_min'         => array(array('not_empty')),
      'bottom_max'         => array(array('not_empty')),
      'length'             => array(array('not_empty')),
      'volume'             => array(array('not_empty')),
      'action'             => array(),
      'comment'            => array(),
      // 'coc_status'         => array(),
      'user_id'            => array(),
      'timestamp'          => array()
    );
  }

//  id bigserial not null,
//  site_id d_id not null,
//  operator_id d_id not null,
//  barcode_id d_id unique not null,
//  parent_barcode_id d_id not null,
//  species_id d_id not null,
//  invoice_id d_id,
//  top_min d_measurement_int not null,
//  top_max d_measurement_int not null,
//  bottom_min d_measurement_int not null,
//  bottom_max d_measurement_int not null,
//  length d_measurement_float not null,
//  volume d_measurement_float not null,
//  action d_text_long,
//  comment d_text_long,
//  coc_status d_coc_status default 'P' not null,
//  user_id d_id default 1 not null,
//  timestamp d_timestamp default current_timestamp not null,


}
