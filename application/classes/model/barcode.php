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

  protected $_ignored_columns = array(
    'is_locked'
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
        return DB::select('lock')
          ->from('barcode_locks')
          ->where('barcode_id', '=', $this->id)
          ->limit(1)
          ->execute()
          ->get('lock') ? TRUE : FALSE;

      default:
        return parent::__get($column);
    }
  }

  public function get_activity($activity = array(), $current = TRUE) {
    $query = DB::select('activity')
      ->from('barcode_activity')
      ->where('barcode_id', '=', $this->id);
    if ($activity) $query->where('activity', 'IN', (array) $activity);
    $query = $query
      ->order_by('timestamp', 'DESC')
      ->execute();

    return $current ? $query->get('activity') : $query->as_array(NULL, 'activity');
  }

  public function set_activity($activity, $comment, $trigger = NULL) {
    if (!$trigger) {
      $caller  = array_shift(debug_backtrace());
      $trigger = $caller['function'];
    }

    if (in_array($activity, array_keys(SGS::$barcode_activity)))
      DB::insert('barcode_activity', array('barcode_id', 'activity', 'comment', 'user_id'))
        ->values(array($this->id, $activity, $comment, Auth::instance()->get_user()->id ?: 1,))
        ->execute();
  }

  public function unset_activity($activity = array()) {
    $query = DB::delete('barcode_activity')
      ->where('barcode_id', '=', $this->id);

    if ($activity) $query->where('activity', 'IN', (array) $activity);
    $query->execute();
  }

  public function get_lock($lock = array(), $current = TRUE) {
    $query = DB::select('lock')
      ->from('barcode_locks')
      ->where('barcode_id', '=', $this->id);
    if ($lock) $query->where('lock', 'IN', (array) $lock);
    $query = $query
      ->order_by('timestamp', 'DESC')
      ->execute();

    return $current ? $query->get('lock') : $query->as_array(NULL, 'lock');
  }

  public function set_lock($lock = 'ADMIN', $lock_id = NULL, $comment = NULL) {
    $user_id =  Auth::instance()->get_user()->id ?: 1;
    if ($this->get_lock($lock) or (!$lock_id and ($lock !== 'ADMIN'))) return;
    if (in_array($lock, array_keys(SGS::$barcode_locks)))
      DB::insert('barcode_locks', array('barcode_id', 'lock', 'lock_id', 'comment', 'user_id'))
        ->values(array($this->id, $lock, $lock_id ?: $user_id, $comment, $user_id))
        ->execute();
  }

  public function unset_locks($lock = array()) {
    $query = DB::delete('barcode_locks')
      ->where('barcode_id', '=', $this->id);

    if ($lock) $query->where('locks', 'IN', (array) $lock);
    $query->execute();
  }

  public function image($filename = FALSE) {
    return Barcode::png($this->barcode, $filename);
  }

}
