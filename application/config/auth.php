<?php

return array(
	'driver'       => 'orm',
	'hash_method'  => 'md5',
	'hash_key'     => (string) md5('sgs'),
	'lifetime'     => 1209600,
	'session_type' => 'database',
	'session_key'  => 'auth_user',

	// username/password combinations for the auth file driver
	'users'        => array('admin' => md5('123456')),
);
