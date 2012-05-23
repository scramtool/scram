<?php
/**
 * represent all the functions we need from a database
 *
 * The this generic database wrapper is intended as a base class for specific database wrappers
 * The specific database wrappers are supposed to implement the following functions:
 *  * exec
 *  * fetch_row
 *  * result
 *  * field_name
 *  * num_fields
 *
 */
class database_wrapper
{

	var $connection = 0;

	/**
	 * execute an SQL query and retrieve the first
	 * result row.
	 */
	function get_single_result( $query_string)
	{
		$res = $this->exec( $query_string);
		$this->fetch_row($res);
		$field_count = $this->num_fields( $res);
		$row = array();
		for ($n = 1; $n <= $field_count; ++$n)
		{
			$name = $this->field_name($res, $n);
			$value = $this->result( $res, $n);
			$row[ $name] = $value;
		}
		return $row;
	}

	/**
	 * execute an SQL query and retreive all result rows.
	 *
	 * This function returns an array of arrays (rows). One
	 * array for each row in the result set.
	 */
	function get_result_table( $query_string, &$headers, &$table)
	{
		$res = $this->exec( $query_string);
		$this->make_result_array( $res, $headers, $table);
	}

	/**
	 *
	 * execute an SQL query and return the results in a two-dimensional array.
	 * @param unknown_type $query_result The query to execute
	 * @param unknown_type $header_array one-dimensional array that receives the field names (column names)
	 * @param unknown_type $value_array array of row-arrays. Each row array contains data as fieldname => fieldvalue pairs
	 */
	function make_result_array( $query_result, &$header_array, &$value_array)
	{
		$field_count = $this->num_fields( $query_result);

		$header_array = array();
		for ( $i = 0; $i < $field_count; ++$i)
		{
			$header_array[] = $this->field_name( $query_result, $i + 1);
		}

		$value_array = array();
		while ($this->fetch_row( $query_result))
		{
			$row = array();
			for ($i = 0; $i < $field_count; ++$i)
			{
				$row[$header_array[$i]] = $this->result( $query_result, $i + 1);
			}

			$value_array[] = $row;
		}
	}

};

?>