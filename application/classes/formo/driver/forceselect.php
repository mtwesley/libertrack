<?php defined('SYSPATH') or die('No direct script access.');

class Formo_Driver_ForceSelect extends Formo_Driver {

	protected $_view_file = 'forceselect';

	public function html()
	{
		$this->_view
			->set_var('tag', 'select')
			->attr('name', $this->name());
	}

}
