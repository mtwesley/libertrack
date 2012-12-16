<?php

class ORM extends Kohana_ORM {

	public function save(Validation $validation = NULL)
	{
    if (in_array('user_id', array_keys((array) $this->_object))) $this->user = Auth::instance()->get_user();

		return parent::save();
	}

  public function update(Validation $validation = NULL) {
    $new = $this->_object;
    $old = $this->_original_values;

    unset($new['timestamp']);
    unset($old['timestamp']);

    sort($new);
    ksort($new);

    sort($old);
    ksort($old);

    $data = serialize($this->_original_values);

    parent::update($validation);

    if (md5(serialize($new)) != md5(serialize($old))) switch ($this->_object_name) {
      case 'user': case 'tolerance': return;
      default: DB::insert('revisions', array('model', 'model_id', 'data', 'url', 'user_id', 'session_id',))
        ->values(array(
          $this->_object_name,
          $this->id,
          $data,
          Request::current()->url().URL::query(),
          Auth::instance()->get_user()->id ?: 1,
          DB::select('id')->from('sessions')->where('cookie', '=', Session::instance()->id())->execute()->get('id')
        ))
        ->execute();
    }
  }

}
