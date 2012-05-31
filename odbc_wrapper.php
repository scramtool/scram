<?php

require_once("database_wrapper.php");

/**
 *
 * @author nly95769
 * Simple wrapper around odbc databases. This object serves to abstract the database interface
 * used (odbc, mysql). \see mysql_wrapper.
 */
class odbc_wrapper  extends database_wrapper
{

	function __construct( $db_name, $db_user, $db_password)
	{
		$this->connection = odbc_connect( $db_name, $db_user, $db_password);
	}


	function exec( $query)
	{
		return odbc_exec( $this->connection, $query);
	}

	function num_rows( $query_result)
	{
		return odbc_num_rows( $query_result);
	}

	function fetch_row( $query_result)
	{
		return odbc_fetch_row( $query_result);
	}

	function result( $row, $field)
	{
		return odbc_result($row, $field);
	}

	function num_fields( $query_result)
	{
		return odbc_num_fields( $query_result);
	}

	function field_name( $query_result, $field_number)
	{
		return odbc_field_name($query_result, $field_number);
	}
	
	function last_inserted_id()
	{
		$fields = $this->get_single_result("SELECT LAST_INSERT_ID() AS id");
		return $fields['id'];
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
		$sql = "SET TRANSACTION ISOLATION LEVEL SERIALIZABLE;"
		."BEGIN TRANSACTION;"
		."SELECT * FROM $table_string WITH (HOLDLOCK, TABLOCK);";

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
		$this->exec( "COMMIT TRANSACTION");
	}

	function ok()
	{
		return !is_bool($this->connection);
	}

	function escape( $string)
	{
		return addslashes($string);
	}
	
	/**
	* Create a query text that will add a number of days to a date.
	* @return string
	*/
	function TxtAddDaysToDate( $date, $days)
	{
		return "(DATEADD(day,$days, $date))";
	}
		
	
	var $connection;
}
?>