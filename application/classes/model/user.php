<?php

class Model_User extends Model_ACL_User {

  protected $_has_many = array(
//    'species' => array(),
//    'operators' => array(),
//    'sites' => array(),
//    'blocks' => array(),
//    'printjobs' => array(),
//    'barcodes' => array(),
//    'files' => array(),
//    'csv' => array(),
//    'invoices' => array(),
		'user_tokens'  => array(
      'model' => 'user_token'
    ),
		'roles'        => array(
      'model'   => 'role',
      'through' => 'roles_users'
    ),
		'capabilities' => array(
      'model' => 'capability',
      'through' => 'capabilities_users'
    ),
  );

  public function formo() {
    return array(
      'id'             => array('render' => FALSE),
      'last_timestamp' => array('render' => FALSE),
      'logins'         => array('render' => FALSE),
      'user_tokens'    => array('render' => FALSE),
      'roles'          => array(
        'orm_primary_val' => 'description',
        'label'           => 'Privileges',
        'attr'            => array('class' => 'listed')
      ),
      'username' => array('label' => 'Username'),
      'password' => array(
        'driver' => 'password',
        'label'  => 'Password',
        'value'  => NULL,
      ),
      'email'    => array('label' => 'E-mail'),
      'name'     => array('label' => 'Full Name')
    );
  }

	public function labels()
	{
		return array(
			'username' => 'Username',
			'email'    => 'E-mail',
			'password' => 'Password',
		);
	}

  public function rules()
	{
		return array(
			'username' => array(array('not_empty'),
                          array('max_length', array(':value', 32)),
                          array(array($this, 'unique'), array('username', ':value'))),
			'password' => array(array('not_empty')),
			'email'    => array(array('email'),
                          array(array($this, 'unique'), array('email', ':value')),
			),
		);
	}

	public function complete_login()
	{
		if ($this->_loaded)
		{
			// Set the last login date
			$this->last_timestamp = SGS::date('now', SGS::PGSQL_DATETIME_FORMAT);

			// Save the user
			$this->update();
		}
	}

	public static function get_password_validation($values)
	{
		return Validation::factory($values)
			->rule('password', 'min_length', array(':value', 5))
			->rule('password_confirm', 'matches', array(':validation', ':field', 'password'));
	}
}
