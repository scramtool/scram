<?php
require_once 'connect_db.inc.php';
require_once 'utilities.inc.php';

function get_availability( $sprint_id)
{
	global $database;
	$sprint_id = $database->escape($sprint_id);
	$query = "select r.name, r.resource_id, date, hours from resource as r join availability as a on r.resource_id = a.resource_id where a.sprint_id = $sprint_id order by r.name, date";
	$list = array();
	$dummy_header = array();
	$database->get_result_table($query, $dummy_header, $list);
	$resources = array();
	$times = array();
	foreach ($list as $row) {
		$date = strtotime( $row['date']);
		$year = date( "%Y", $date);
		$month = date( "%m", $date);
		$day = date( "%d", $date);
		$key = 'k_' . $row['resource_id'] . "_$year-$month-$day";
		$times[$key] = $row['hours'];
	};
	
	$sprint = $database->get_single_result( "select * from sprint where sprint_id = $sprint_id");
	$database->get_result_table("select resource_id, name from resource", $dummy_header, $resources);
	return array( 'resources' => $resources, 'times' => $times, 'sprint' => $sprint);
}

make_global( $_GET, array('sprint_id'));
print json_encode( get_availability( $sprint_id));