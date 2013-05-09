<?php

class ORM extends Kohana_ORM {

  private function set_revision()
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

  public function get_revisions() {
    $revisions = array();
    foreach (DB::select('id', 'data', 'user_id', 'timestamp')
      ->from('revisions')
      ->where('model', '=', $this->_object_name)
      ->and_where('model_id', '=', $this->id)
      ->order_by('timestamp', 'DESC')
      ->execute()
      ->as_array() as $values) {
        $revision = ORM::factory($this->_object_name)->values(unserialize($values['data']));
        $revision->user_id = $values['user_id'];
        $revision->timestamp = $values['timestamp'];
        $revisions[$values['id']] = $revision;
      }
    return $revisions;
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
    $this->set_revision();
  }

  public function update(Validation $validation = NULL)
  {
    $this->set_revision();
    parent::update($validation);
  }

  public function delete()
  {
    $this->set_revision();
    parent::delete();
  }

}
