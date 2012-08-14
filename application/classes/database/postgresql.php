<?php

class Database_PostgreSQL extends Kohana_Database_PostgreSQL {

  public function quote_identifier($value)
	{
		if (is_array($value))
		{
			list($value, $alias) = $value;
		}

		if ($value instanceof Database_Query)
		{
			// Create a sub-query
			$value = '('.$value->compile($this).')';
		}
		elseif ($value instanceof Database_Expression)
		{
			// Compile the expression
			$value = $value->compile($this);
		}
		else
		{
			// Convert to a string
			$value = (string) $value;

			if (strpos($value, '.') !== FALSE)
			{
				$parts = explode('.', $value);

				foreach ($parts as & $part)
				{
					// Quote each of the parts
					$part = $this->_identifier.$part.$this->_identifier;
				}

				$value = implode('.', $parts);
			}
			else
			{
				$value = $this->_identifier.$value.$this->_identifier;
			}
		}

		if (isset($alias))
		{
			$value .= ' AS '.$this->_identifier.$alias.$this->_identifier;
		}

		return $value;
	}


}