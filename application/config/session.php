<?php

return array(
  'native'     => array(
    'name'     => 'session_native',
    'lifetime' => 43200,
  ),
  'cookie' => array(
    'name'      => 'session_cookie',
    'encrypted' => TRUE,
    'lifetime'  => 43200,
  ),
  'database' => array(
    'name'      => 'session_database',
    'lifetime'  => 43200,
    'group'     => 'default',
    'table'     => 'sessions',
    'columns' => array(
      'session_id'  => 'id',
      'last_active' => 'last_active',
      'contents'    => 'contents'
    ),
  ),
);
