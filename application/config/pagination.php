<?php defined('SYSPATH') or die('No direct script access.');

return array(
	'default' => array(
		'current_page'      => array(
      'source' => 'query_string',
      'key'    => 'page'),
		'total_items'       => 0,
		'items_per_page'    => 20,
		'view'              => 'pagination',
		'auto_hide'         => FALSE,
		'first_page_in_url' => TRUE,
	),

);
