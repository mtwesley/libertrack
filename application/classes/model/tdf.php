<?php

class Model_TDF extends SGS_Form_ORM {

  const PARSE_START = 13;

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
                                  array(array($this, 'is_unique'),
                                        array(':field', ':value'))),
      'tree_barcode_id'  => array(array('not_empty')),
      'stump_barcode_id' => array(array('not_empty'),
                                  array(array($this, 'is_unique'),
                                        array(':field', ':value'))),
      'survey_line'      => array(array('not_empty')),
      'cell_number'      => array(array('not_empty')),
      'top_min'          => array(array('not_empty')),
      'top_max'          => array(array('not_empty')),
      'bottom_min'       => array(array('not_empty')),
      'bottom_max'       => array(array('not_empty')),
      'length'           => array(array('not_empty')),
      'action'           => array(),
      'comment'          => array(),
      // 'coc_status'       => array(),
      'user_id'          => array(),
      'timestamp'        => array()
    );
  }

}
