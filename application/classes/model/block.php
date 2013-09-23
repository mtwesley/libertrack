<?php

class Model_Block extends ORM {

  protected $_belongs_to = array(
    'site' => array(),
    'user' => array(),
    'file' => array(
      'foreign_key' => 'inspection_file_id'
    )
  );

  public function formo() {
    $array = array(
      'site' => array(
        'orm_primary_val' => 'name',
        'label'           => 'Site'
      ),
      'id'         => array('render' => FALSE),
      'is_deleted' => array('render' => FALSE),
      'utm_origin' => array('render' => FALSE),
      'utm_east'   => array('render' => FALSE),
      'utm_north_south'    => array('render' => FALSE),
      'utm_west'   => array('render' => FALSE),
      'inspection_file_id' => array('render' => FALSE),
      'status'     => array('render' => FALSE),
    );
    foreach (self::fields() as $field => $label) {
      $array[$field]['label'] = $label;
    }
    return $array;
  }

  public static function fields() {
    return array(
      'site_id' => 'Site',
      'name'    => 'Name'
    );
  }

  public function get_inspection_data($args = array()) {
    $query = DB::select('form_data_id')
      ->from('block_inspection_data')
      ->where('block_id', '=', $this->id);
    foreach ($args as $key => $value) $query->where($key, 'IN', (array) $value);
    return $query->execute()->as_array(NULL, 'form_data_id');
  }

  public function unset_inspection_data($args = array()) {
    $query = DB::delete('block_inspection_data')
      ->where('block_id', '=', $this->id);
    foreach ($args as $key => $value) $query->where($key, 'IN', (array) $value);
    $query->execute();
  }

  public function set_inspection_data($form_type, $form_data_id) {
    DB::insert('block_inspection_data', array('block_id', 'form_type', 'form_data_id'))
      ->values(array($this->id, $form_type, $form_data_id))
      ->execute();
  }

  public function rules()
  {
    return array(
      'name'      => array(array('not_empty'),
                           array('is_block_name')),
      'site_id'   => array(array('not_empty')),
      'user_id'   => array(),
      'timestamp' => array()
    );
  }

}
