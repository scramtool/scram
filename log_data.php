<?php
//
//  Copyright (C) 2012 Danny Havenith
//
//  Distributed under the Boost Software License, Version 1.0. (See
//  accompanying file LICENSE_1_0.txt or copy at
//  http://www.boost.org/LICENSE_1_0.txt)
//

require_once 'connect_db.inc.php';
require_once 'utilities.inc.php';

function get_log_query( $sprint_id, $start_date, $end_date)
{
	return 
	"SELECT resource.resource_id, resource.name, task.*, log.type, log.details FROM resource, log, task ".
	"WHERE log.resource_id = resource.resource_id ".
		"AND log.task_id = task.task_id AND task.sprint_id = $sprint_id ".
		"AND log.time >= '$start_date' and log.time <= '$end_date' ".
	"ORDER BY resource.name, task.task_id";
}

function get_logs( &$parameters)
{
	global $database;
	$sprint_id  = get_if_defined( $parameters, 'sprint_id');
	$start_date = get_if_defined( $parameters, 'start_date', date( "Y-m-d H:i:s", time() - 60 * 60 * 24));
	$end_date   = get_if_defined( $parameters, 'end_date',   date( "Y-m-d H:i:s", time()));
	print( get_log_query( $sprint_id, $start_date, $end_date));
	$database->get_result_table(get_log_query( $sprint_id, $start_date, $end_date), $headers, $logs);
	
	return json_encode($logs);
	
}

print dispatch_command( $_GET, 'action', array( 'get' => 'get_logs'));
