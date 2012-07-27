<?php

global $config;
global $connection;

$config['database'] = array(
  'host'     => 'localhost',
  'port'     => 5432,
  'database' => 'sgs',
  'username' => 'sgs',
  'password' => '5gSu8z_',
);

$connection = pg_connect("host={$config['database']['host']} port={$config['database']['port']} dbname={$config['database']['database']} user={$config['database']['username']} password={$config['database']['password']}");
if (!$connection) die("Unable to connect to database");

?>
