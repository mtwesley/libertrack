<?php

return array(
	'driver'       => 'file',
	'hash_method'  => 'md5',
	'hash_key'     => NULL,
	'lifetime'     => 1209600,
	'session_type' => 'database',
	'session_key'  => 'auth_user',

	// Username/password combinations for the Auth File driver
	'users' => array(
		'admin' => md5('123456'),
	),

);
