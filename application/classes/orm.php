<?php

class ORM extends Kohana_ORM {

  private function create_revision()
  {
    $new = $this->_object;
    $old = $this->_original_values;

    unset($new['timestamp']);
    unset($old['timestamp']);

    sort($new);
    ksort($new);

    sort($old);
    ksort($old);

    $data = serialize($this->_original_values);

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

	public function save(Validation $validation = NULL)
	{
    if ($this->_changed and isset($this->user)) $this->user = Auth::instance()->get_user();
    if (isset($this->timestamp)) $this->timestamp = SGS::date('now', SGS::PGSQL_DATETIME_FORMAT);
		return parent::save();
	}

  public function create(Validation $validation = NULL)
  {
    parent::create($validation);
    $this->create_revision();
  }

  public function update(Validation $validation = NULL)
  {
    $this->create_revision();
    parent::update($validation);
  }

  public function delete()
  {
    $this->create_revision();
    parent::delete();
  }

}
