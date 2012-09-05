<?php

class Formo_Driver_Text extends Formo_Driver {

	protected $_view_file = 'text';
	public $empty_input = TRUE;

	// Setup the html field
	public function html()
	{
		$this->_view
			->set('name', $this->_field->name())
			->set('value', $this->_field->val());
	}

}
