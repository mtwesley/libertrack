<?php

abstract class Database_Query_Builder_Where extends Kohana_Database_Query_Builder_Where {

	protected function _compile_conditions(Database $db, array $conditions)
	{
		$last_condition = NULL;

		$sql = '';
		foreach ($conditions as $group)
		{
			// Process groups of conditions
			foreach ($group as $logic => $condition)
			{
				if ($condition === '(')
				{
					if ( ! empty($sql) AND $last_condition !== '(')
					{
						// Include logic operator
						$sql .= ' '.$logic.' ';
					}

					$sql .= '(';
				}
				elseif ($condition === ')')
				{
					$sql .= ')';
				}
				else
				{
					if ( ! empty($sql) AND $last_condition !== '(')
					{
						// Add the logic operator
						$sql .= ' '.$logic.' ';
					}

					// Split the condition
					list($column, $op, $value) = $condition;

					if ($value === NULL)
					{
						if ($op === '=')
						{
							// Convert "val = NULL" to "val IS NULL"
							$op = 'IS';
						}
						elseif ($op === '!=')
						{
							// Convert "val != NULL" to "valu IS NOT NULL"
							$op = 'IS NOT';
						}
					}

					// Database operators are always uppercase
					$op = strtoupper($op);

					if ($op === 'BETWEEN' AND is_array($value))
					{
						// BETWEEN always has exactly two arguments
						list($min, $max) = $value;

						if ((is_string($min) AND array_key_exists($min, $this->_parameters)) === FALSE)
						{
							// Quote the value, it is not a parameter
							$min = $db->quote($min);
						}

						if ((is_string($max) AND array_key_exists($max, $this->_parameters)) === FALSE)
						{
							// Quote the value, it is not a parameter
							$max = $db->quote($max);
						}

						// Quote the min and max value
						$value = $min.' AND '.$max;
					}
					elseif ((is_string($value) AND array_key_exists($value, $this->_parameters)) === FALSE)
					{
						// Quote the value, it is not a parameter
						$value = $db->quote($value);
					}

					if ($column)
					{
						if (is_array($column))
						{

							// Use the column name
              if (isset($column['__cast']))
              {
                $column = $db->quote_identifier(reset($column)).'::'.$column['__cast'];
              }
							else
              {
                $column = $db->quote_identifier(reset($column));
              }
						}
						else
						{
							// Apply proper quoting to the column
							$column = $db->quote_column($column);
						}
					}

					// Append the statement to the query
					$sql .= trim($column.' '.$op.' '.$value);
				}

				$last_condition = $condition;
			}
		}

		return $sql;
	}

}
