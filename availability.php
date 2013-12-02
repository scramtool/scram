<?php
//
//  Copyright (C) 2012,2013 Danny Havenith
//
//  Distributed under the Boost Software License, Version 1.0. (See
//  accompanying file LICENSE_1_0.txt or copy at
//  http://www.boost.org/LICENSE_1_0.txt)
//
require_once 'connect_db.inc.php';
require_once 'utilities.inc.php';

/**
 * Return a json-encoded object with availability information for a sprint with a given sprint-id.
 * 
 * @param unknown_type $inputs This must be an associative array with at least a key 'sprint_id' for 
 *                      which the value is the id of the requested sprint.
 * 
 * @return a json encoded object of the shape {resources: <array of resources in this sprint>, times: <
 */
function get_availability( &$inputs)
{
	global $database;
	$sprint_id = $database->escape($inputs['sprint_id']);
	$query = "select r.name, r.resource_id, date, hours from resource as r join availability as a on r.resource_id = a.resource_id where a.sprint_id = $sprint_id order by r.name, date";
	$list = array();
	$dummy_header = array();
	$database->get_result_table($query, $dummy_header, $list);
	$resources = array();
	$times = array();
	foreach ($list as $row) {
		$date = strtotime( $row['date']);
		$year = date( "Y", $date);  // four-digit year.
		$month = date( "n", $date); // month without leading zeros.
		$day = date( "j", $date);   // day without leading zeros.
		$key = 'k_' . $row['resource_id'] . "_$year-$month-$day";
		$times[$key] = $row['hours'];
	};
	
	$sprint = $database->get_single_result( "select * from sprint where sprint_id = $sprint_id");
	$database->get_result_table("select resource_id, name from resource", $dummy_header, $resources);
	return json_encode( array( 'resources' => $resources, 'times' => $times, 'sprint' => $sprint));
}

/**
 * This function expects the $inputs array to contain at least a key 'sprint_id' and 
 * a number of keys with format k_<resource-id>_<year>-<month>-<day>. for each found item, It will add an
 * availability record for the given resource id on the given date, where the value represents the number of hours.
 * Before inserting availability records, all existing records for the given sprint will be deleted.
 * 
 * @param unknown_type $inputs
 */
function change_availability( &$inputs)
{
	global $database;
	$sprint_id = $database->escape( $inputs['sprint_id']);
	$insert_array = Array();
	$count = 0;
	foreach ( $inputs as $key => $value)
	{
	    
	    $value = intval( $value); // force intval to be numeric
		if (preg_match('/k_(\d+)_(\d{4}-\d+-\d+)/', $key, $key_parts))
		{
			$insert_array[] = "($sprint_id, $key_parts[1], '$key_parts[2]', $value)";
			++$count;
		}
	}
	
	if ($count)
	{
		$insert_string = implode(',', $insert_array);
		$database->exec( "INSERT INTO availability(sprint_id, resource_id, date, hours) VALUES $insert_string " .
		        "ON DUPLICATE KEY UPDATE hours = VALUES( hours)");
	}
	
	return get_availability( $inputs);
}

if (isset($_GET['action']))
{
	$input = &$_GET;
}
else
{
	$input = &$_POST;
}

print dispatch_command($input, 'action', array( 'get' => 'get_availability', 'post' => 'change_availability'));
