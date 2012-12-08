<?php

class ORM extends Kohana_ORM {

	public function save(Validation $validation = NULL)
	{
    if (in_array('user_id', array_keys((array) $this->_object))) $this->user = Auth::instance()->get_user();

		return parent::save();
	}

  public function update(Validation $validation = NULL) {
    $data = serialize($this->_original_values);

    parent::update($validation);

    switch ($this->_object_name) {
      case 'user': case 'tolerance': return;
      default: DB::insert('revisions', array('model', 'model_id', 'data', 'user_id'))
        ->values(array($this->_object_name, $this->id, $data, Auth::instance()->get_user()->id ?: 1))
        ->execute();
    }
  }

}
