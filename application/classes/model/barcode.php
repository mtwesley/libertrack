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
  }

  public function get_activity($current = TRUE) {
    $query = DB::select('activity')
      ->from('barcode_activity')
      ->where('barcode_id', '=', $this->id)
      ->order_by('timestamp', 'DESC')
      ->execute();

    return $current ? $query->get('activity') : $query->as_array(NULL, 'activity');
  }

  public function set_activity($activity, $trigger = NULL) {
    if (!$trigger) {
      $caller  = array_shift(debug_backtrace());
      $trigger = $caller['function'];
    }

    if (in_array($activity, SGS::$coc_status))
      DB::insert('barcode_activity', array('barcode_id', 'activity', 'user_id'))
        ->values(array($this->id, $activity, Auth::instance()->get_user()->id ?: 1,))
        ->execute();
  }

  public function unset_activity($activity = array()) {
    $query = DB::delete('barcode_activity')
      ->where('barcode_id', '=', $this->id);

    if ($activity) $query->where('activity', 'IN', (array) $activity);
    $query->execute();
  }

}
