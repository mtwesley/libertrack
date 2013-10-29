<?php defined('SYSPATH') or die('No direct access allowed.');

/**
  * There are two ways to define a connection for PostgreSQL:
  *
  * 1. Full connection string passed directly to pg_connect()
  *
  * string   info
  *
  * 2. Connection parameters:
  *
  * string   hostname    NULL to use default domain socket
  * integer  port        NULL to use the default port
  * string   username
  * string   password
  * string   database
  * boolean  persistent
  * mixed    ssl         TRUE to require, FALSE to disable, or 'prefer' to negotiate
  *
  * @link http://www.postgresql.org/docs/current/static/libpq-connect.html
  */

return array(
	'default' => array(
		'type'       => 'postgresql',
		'connection' => array(
			'hostname'   => 'celia',
			'username'   => 'sgs',
			'password'   => 'c3LiA!',
			'persistent' => FALSE,
			'database'   => 'sgs',
      'port'       => 5434
		),
		'primary_key'  => 'id',
		'schema'       => '',
		'table_prefix' => '',
		'charset'      => 'utf8',
		'caching'      => FALSE,
		'profiling'    => TRUE,
	),
	'ledger' => array(
		'type'       => 'postgresql',
		'connection' => array(
			'hostname'   => 'localhost',
			'username'   => 'sgs',
			'password'   => '5gSu8z_',
			'persistent' => FALSE,
			'database'   => 'ledger',
		),
		'primary_key'  => 'id',
		'schema'       => '',
		'table_prefix' => '',
		'charset'      => 'utf8',
		'caching'      => FALSE,
		'profiling'    => TRUE,
	),
);
