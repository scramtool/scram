<?php
/**
 * simple wrapper around mysql database functions
 */

require_once("database_wrapper.php");

/// simple database wrapper class
/// This class actually emulates the behavior of the odbc_xxx database functions,
/// where field indices start at 1 and fetch row does not return an array, but actual results
/// must be fetched through the 'result' function.
class mysql_wrapper  extends database_wrapper
{
	function __construct( $server, $database, $username, $password)
	{

		$this->connection = mysql_connect( $server, $username, $password);
		$this->last_result = array();

		if ($this->connection)
		{
			mysql_select_db( $database, $this->connection);
		}
	}

	function exec( $query)
	{
		if (is_bool($query) || !$this->ok())
		{
			print "previous error\n";
			return FALSE;
		}

		$result = mysql_query( $query, $this->connection);
		if (!$result)
		{
			print mysql_error( $this->connection) . "\n";
		}
		return $result;
	}

	function num_rows( $query_result)
	{
		return mysql_num_rows($query_result);
	}

	function fetch_row( $query_result)
	{
		if (is_bool($query_result) && !$query_result)
		{
			return $query_result;
		}
		else
		{
			return $this->last_result[$query_result] = mysql_fetch_array( $query_result);
		}
	}

	function result( $query_result, $field)
	{
		if (is_numeric($field))
		{
			--$field;
		}
		return $this->last_result[$query_result][$field];
	}

	function num_fields( $query_result)
	{
		return mysql_num_fields( $query_result);
	}

	/**
	 * Get the field name for the given (1-based) field number
	 * Enter description here ...
	 * @param unknown_type $query_result return values of previous 'exec' call
	 * @param unknown_type $field_number field number. Field numbers start at 1.
	 */
	function field_name( $query_result, $field_number)
	{
		return mysql_field_name($query_result, $field_number - 1);
	}
	/**
	 * Lock the given tables. For ODBC connections this is mapped onto a connection.
	 * @param unknown_type $tables The tables to lock
	 */
	function lock_tables( $tables)
	{

		$table_string = "";
		if (is_array( $tables))
		{
		 $table_string = implode( ' WRITE ,', $tables);
		}
		else
		{
		 $table_string = $tables;
		}

		$sql = "LOCK TABLES $table_string WRITE";

		if (!$this->exec( $sql))
		{
			error_log( "the following query had errors: '$sql'", 0);
		};
	}

	/**
	 * Unlock all previously locked tables
	 */
	function unlock_tables( )
	{
		$this->exec( "UNLOCK TABLES");
	}

	function ok()
	{
		return !is_bool( $this->connection);
	}

	function escape( $string)
	{
		return mysql_real_escape_string($string);
	}
	
	var $connection;
}
?>