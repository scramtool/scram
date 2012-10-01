<?php
/**
 * Take an associative array and create global variables out of the 
 * Enter description here ...
 * @param unknown_type $input
 * @param unknown_type $names
 */
function make_global( &$input, $names)
{
	foreach ($names as $name) {
		if (isset( $input[$name]))
		{
			$GLOBALS[$name] =  $input[$name];
		}
	}
}

/**
 * Searches in an array for a value with the given index. If the array holds no value at that index, and no value for $ifnotdefined is given
 * this function will abort the script. If there is a value for $ifnotdefined and the given index holds no value, the value of $ifnotdefined is
 *  returned.
 * Enter description here ...
 * @param unknown_type $array
 * @param unknown_type $index
 * @param unknown_type $ifnotdefined
 */
function get_if_defined( &$array, $index, $ifnotdefined = null)
{
	if ( isset($array[$index]))
	{
		return $array[$index];
	}
	
	if ( $ifnotdefined === null)
	{
		die("$index was not defined");
	}
	
	return $ifnotdefined;
}

/**
 * Get the user id that is associated with the given user name.
 * 
 * If no user (resource) exists yet with that name in that database, one will be created.
 * @param unknown_type $database Resource to query for the user
 * @param string $user_name 
 * @return a valid resource id.
 */
function get_user_id( $database, $user_name)
{
	$member = $database->get_single_result( "select resource_id from resource where name = '$user_name'");
	if (isset( $member['resource_id']))
	{
		$id = $member['resource_id'];
	}
	else
	{
		$database->exec("INSERT INTO resource( name) VALUES('$user_name')");
		$id = $database->last_inserted_id();
	}
	return $id;
}

function dispatch_command( $inputs, $command_keyword, $handlers)
{
	if (isset( $inputs[$command_keyword]))
	{
		$command = $inputs[$command_keyword];
		if (isset( $handlers[$command]))
		{
			return $handlers[$command]( $inputs);
		}
	}
	else
	{
		return false;
	}
}