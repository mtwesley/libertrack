<?php

class Model_Barcode extends ORM {

  protected $_belongs_to = array(
    'printjob' => array(),
    'parent'   => array(
      'model'       => 'barcode',
      'foreign_key' => 'parent_id'
    ),
    'user' => array()
  );

  protected $_has_many = array(
    'children' => array(
      'model' => 'barcode',
      'foreign_key' => 'parent_id'
    )
  );

  public function formo() {
    $array = array(
      'id'        => array('render' => FALSE),
      'parent_id' => array('render' => FALSE),
      'type'      => array('render' => FASLE),
      'printjob'  => array(
        'orm_primary_val' => 'number',
        'label'           => 'Print Job'
      ),
    );
    foreach (self::fields() as $field => $label) {
      $array[$field]['label'] = $label;
    }
    return $array;
  }

  public static function fields() {
    return array(
      'barcode'     => 'Barcode',
      'is_locked'   => 'Locked',
      'printjob_id' => 'Print Job'
    );
  }

  public function __get($column) {
    switch ($column) {
      case 'is_locked':
        return parent::__get($column) == 't' ? TRUE : FALSE;

      default:
        return parent::__get($column);
    }
  }

  public function save(Validation $validation = NULL) {
    parent::create($validation);
    $this->set_coc_activity('P');
  }

  public function get_coc_activity($current = TRUE) {
    $query = DB::select('status')
      ->from('barcode_coc_activity')
      ->where('barcode_id', '=', $this->id)
      ->order_by('timestamp', 'DESC')
      ->execute();

    return $current ? $query->get('status') : $query->as_array(NULL, 'status');
  }

  public function set_coc_activity($status, $trigger = NULL) {
    if (!$trigger) {
      $caller  = array_shift(debug_backtrace());
      $trigger = $caller['function'];
    }

    if (in_array($status, SGS::$coc_status))
      DB::insert('barcode_coc_activity', array('barcode_id', 'status', 'user_id'))
        ->values(array($this->id, $status, Auth::instance()->get_user()->id ?: 1,))
        ->execute();
  }

  public function unset_coc_activity($status = array()) {
    $query = DB::delete('barcode_coc_activity')
      ->where('barcode_id', '=', $this->id);

    if ($status) $query->where('status', 'IN', (array) $status);
    $query->execute();
  }

}
