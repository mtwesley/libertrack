<?php

global $config;
global $connection;

$config['database'] = array(
  'host'     => 'localhost',
  'port'     => 5432,
  'database' => 'sgs_test2',
  'username' => '_postgres',
  'password' => 'postgres',
);

$connection = pg_connect("host={$config['database']['host']} port={$config['database']['port']} dbname={$config['database']['database']} user={$config['database']['username']} password={$config['database']['password']}");
if (!$connection) die("Unable to connect to database");

?>
