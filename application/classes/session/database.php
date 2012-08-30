<?php

class Session_Database extends Kohana_Session_Database {

	protected function _destroy()
	{
		if ($this->_update_id === NULL)
		{
			// Session has not been created yet
			return TRUE;
		}

		// Delete the current session
		$query = DB::update($this->_table)
      ->value($this->_columns['session_id'], NULL)
			->where($this->_columns['session_id'], '=', ':id')
			->param(':id', $this->_update_id);

		try
		{
			// Execute the query
			$query->execute($this->_db);

			// Delete the cookie
			Cookie::delete($this->_name);
		}
		catch (Exception $e)
		{
			// An error occurred, the session has not been deleted
			return FALSE;
		}

		return TRUE;
	}

	protected function _write()
	{
    $user_id    = ($user = Auth::instance()->get_user()) ? $user->id : NULL;
    $ip_address = Request::$client_ip;
    $user_agent = Request::$user_agent;
    $timestamp  = SGS::date($this->_data['last_active'], SGS::PGSQL_DATETIME_FORMAT);

		if ($this->_update_id === NULL)
		{
			// Insert a new row
			$query = DB::insert($this->_table, $this->_columns + array('user_id', 'ip_address', 'user_agent', 'from_timestamp'))
				->values(array(':new_id', ':active', ':contents', $user_id, $ip_address, $user_agent, $timestamp));
		}
		else
		{
			// Update the row
			$query = DB::update($this->_table)
				->value($this->_columns['last_active'], ':active')
				->value($this->_columns['contents'], ':contents')
				->value('ip_address', $ip_address)
				->value('user_agent', $user_agent)
				->where($this->_columns['session_id'], '=', ':old_id');

      if ($user_id) $query->value('user_id', $user_id);
			if ($this->_update_id !== $this->_session_id) $query->value($this->_columns['session_id'], ':new_id');
		}

		$query
			->param(':new_id',   $this->_session_id)
			->param(':old_id',   $this->_update_id)
			->param(':active',   $timestamp ? $timestamp : NULL)
			->param(':contents', $this->__toString());

		// Execute the query
		$query->execute($this->_db);

		// The update and the session id are now the same
		$this->_update_id = $this->_session_id;

		// Update the cookie with the new session id
		Cookie::set($this->_name, $this->_session_id, $this->_lifetime);

		return TRUE;
	}

	protected function _gc()
	{
		if ($this->_lifetime)
		{
			// Expire sessions when their lifetime is up
			$expires = $this->_lifetime;
		}
		else
		{
			// Expire sessions after one month
			$expires = Date::MONTH;
		}

		// Delete all sessions that have expired
		DB::update($this->_table)
      ->value('cookie', NULL)
			->where($this->_columns['last_active'], '<', ':time')
			->param(':time', SGS::date(time() - $expires, SGS::PGSQL_DATETIME_FORMAT))
			->execute($this->_db);
	}


}
