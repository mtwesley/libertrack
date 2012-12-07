<?php defined('SYSPATH') or die('No direct script access.');

class Formo_Driver_Form extends Formo_Core_Driver_Form {
	public function html()
	{

		$this->_view
			->set_var('tag', 'form');

		// If the action hasn't been set, set it to the current uri
		( ! $this->_view->attr('action') AND URL::site(Request::current()->detect_uri()));

		// If it's not already defined, the form's type is 'post'
		( ! $this->_view->attr('method') AND $this->_view->attr('method', 'post'));

		// If it's not already defined, define the field's action
		( ! $this->_view->attr('action') AND $this->_view->attr('action', Request::$current->url()));
	}

}