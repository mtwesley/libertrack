<?php

class Model_SSF extends SGS_Form_ORM {

  const PARSE_START = 13;

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
    if ( ! (array_filter($row))) return null;

    $matches = array();
    preg_match('/((([A-Z]+)\/)?([A-Z1-9\s-_]+)?)\/?([A-Z1-9]+)/', $csv[2][B], $matches);

    $operator_tin      = $csv[2][H];
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
      'barcode'           => $row[A],
      'tree_map_number'   => $row[B],
      'survey_line'       => $row[C],
      'cell_number'       => $row[D],
      'species_code'      => $row[E],
      'diameter'          => $row[F],
      'height'            => $row[G],
      'is_requested'      => $row[H] == 'NO' ? 'NO' : 'YES',
      'is_fda_approved'   => $row[I] == 'NO' ? 'NO' : 'YES',
      'fda_remarks'       => $row[J],
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
  }

  public function rules()
  {
    return array(
      'site_id'         => array(array('not_empty')),
      'operator_id'     => array(array('not_empty')),
      'block_id'        => array(array('not_empty')),
      'species_id'      => array(array('not_empty')),
      'barcode_id'      => array(array('not_empty'),
                                 array(array($this, 'is_unique'), array(':field', ':value'))),
      'survey_line'     => array(array('not_empty')),
      'cell_number'     => array(array('not_empty')),
      'tree_map_number' => array(array('not_empty')),
      'diameter'        => array(array('not_empty')),
      'height'          => array(array('not_empty')),
      'is_requested'    => array(array('not_empty'),
                                 array('is_boolean')),
      'is_fda_approved' => array(array('not_empty'),
                                 array('is_boolean')),
      'fda_remarks'     => array(),
      'user_id'         => array(),
      'timestamp'       => array()
    );
  }

}
