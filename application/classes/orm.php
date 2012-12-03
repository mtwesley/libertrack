<?php

class ORM extends Kohana_ORM {

	public function save(Validation $validation = NULL)
	{
    if (in_array('user_id', array_keys((array) $this->_object))) {
      $this->user = Auth::instance()->get_user();
    }
		return parent::save();
	}

}
