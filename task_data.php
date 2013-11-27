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
require_once 'task_data.inc.php';
require_once 'log.inc.php';

class Task
{
	function __construct( $id, $description, $state, $resource, $resourcename)
	{
		$this->id 			= $id + 0;
		$this->description 	= $description;
		$this->status 		= $state;
		$this->resource 	= $resource;
		$this->resourcename	= $resourcename;
	}

	var $description;
	var $status;
	var $resourcename;
}

function print_task_data( $sprint_id)
{
	global $database;
	$headers = array();
	$tasks = array();

	$database->get_result_table( get_task_query($sprint_id), $headers, $tasks);

	print json_encode($tasks);
}

function print_task_table_data( $sprint_id)
{
    global $database;
    $headers = array();
    $tasks = array();
    $result = array();
    
    $database->get_result_table( get_task_table_query($sprint_id), $headers, $tasks);
    $result['aaData'] = array_map( "array_values", $tasks);
    print json_encode($result);
}

function print_csv( $headers, $table)
{
	$outstream = fopen("php://output", 'w');
	fputcsv( $outstream, $headers);
	foreach( $table as $row)
	{
		fputcsv( $outstream, array_values( $row));
	}
	fclose( $outstream);
}

function print_task_table_csv( $sprint_id)
{
    global $database;
    $headers = array();
    $tasks = array();
    $result = array();
    
    $database->get_result_table( get_task_table_query($sprint_id), $headers, $tasks);
	header("Content-type: text/csv");
	header("Content-Disposition: attachment; filename=sprint_tasks.csv");
	header("Pragma: no-cache");
	header("Expires: 0");
	print_csv( $headers, $tasks);
}

function print_single_task( $task_id)
{
	global $database;
	$task_info = $database->get_single_result( get_task_query( 0, $task_id));
	print json_encode($task_info);
}

function update_report( $task_id, $ref_date, $estimate, $spent)
{
    global $database;
    $task_id = (int)$task_id;
    $estimate = (int)$estimate;
    $spent = (int)$spent;
    
    $ref_date = "'" . $database->escape( $ref_date). "'";
    $log= new Log( $database);
    $log->estimate($task_id, $estimate, $spent);
    
    // insert a new report into the database, but if a report for this task,date,resource already existed, only update the values. 
    $success = $database->exec(
            "INSERT INTO report(task_id, resource_id, date, burnt, estimate) SELECT $task_id, resource_id, $ref_date, $spent, $estimate FROM task WHERE task_id = $task_id " .
            "ON DUPLICATE KEY UPDATE burnt = VALUES( burnt), estimate = VALUES( estimate)");
    return $success;
}

function handle_report( $task_id, $ref_date, $estimate, $spent)
{
	if (update_report($task_id, $ref_date, $estimate, $spent))
	{
		print_single_task( $task_id);
	}
}

/**
 * Handle a 'move' request. 
 * 
 * The task was moved from one box to another, update the tasks status in the database.
 * @param unknown $task_id
 * @param unknown $status The new status of the task
 * @param unknown $owner The person performing the move.
 */
function handle_move( $task_id, $status, $owner, $ref_date, $estimate = null, $spent = null)
{
	global $database;
	$task_id = $database->escape($task_id);
	$status = $database->escape( $status);
	
	if (isset($estimate) && isset( $spent))
	{
	    if (!isset($ref_date))
	    {
	        $ref_date =  date('Y-m-d');
	    }
	    update_report( $task_id, $ref_date, $estimate, $spent);    
	}
	
	if (isset( $owner))
	{
		$owner = $database->escape($owner);
		$owner_update = ", resource_id = $owner";
	}
	else {
		$owner_update = '';
	}
	
	
	$move_query = "UPDATE task SET status='$status' $owner_update WHERE task_id = $task_id";
	$success = $database->exec($move_query);
	
	// if the new status is 'forwarded' or 'done', automatically add a report setting the tasks new estimate to 0
	if ($status == 'forwarded' || $status == 'done')
	{
	    $reason = ($status == 'forwarded')?'forward':'estimate';
		$select_query =
		    "SELECT * FROM report WHERE task_id=" . $task_id . " AND date=CURDATE()";
		$result = $database->exec($select_query);
		if (mysql_num_rows($result) > 0)
		{
			// Update the existing report for today.
			$report_query = 
				"UPDATE report SET estimate=0, reason='$reason' WHERE task_id=" . $task_id . " AND date=CURDATE()";
		}
		else
		{
			// Add a report with estimate set to 0.
			$report_query = 
				"INSERT INTO report(task_id, resource_id, date, reason, burnt, estimate) ".
				"SELECT $task_id, resource_id, NOW(), '$reason', 0, 0 FROM task WHERE task_id = $task_id";
		}	
		$database->exec( $report_query);
	}

	if ($success)
	{
	    print_single_task($task_id);
	}
	
	$log = new Log( $database);
	$log->move($task_id, $status);
}

/**
 * Handle an 'add' request.
 * 
 * This will insert a new task entry in the database and if an estimate was provided, a report of this will be inserted as well.
 * placeholder is a client-provided value that refers to some placeholder that the client will have used locally for a dummy task.
 * This placeholder will be returned verbatim to the cliend after inserting.
 * 
 * @param unknown_type $description
 * @param unknown_type $estimate
 * @param unknown_type $sprint_id
 * @param unknown_type $placeholder
 */
function handle_add( $arguments)
{
	global $database;
	
	$description = $database->escape(get_if_defined($arguments, 'description'));
	$estimate	 = $database->escape(get_if_defined($arguments, 'estimate', 8));
	$sprint_id 	 = $database->escape(get_if_defined($arguments, 'sprint_id'));
	$name		 = $database->escape(get_if_defined($arguments, 'member_name', 'Nobody'));
	$is_late	 = $database->escape(get_if_defined($arguments, 'is_late', false));
	$placeholder = get_if_defined($arguments, 'placeholder', 0);
	
	$member_id = get_user_id( $database, $name);
	
	// insert a task into the database. A task id will be created automatically, so we need to retrieve that.
	$database->exec("INSERT INTO task(sprint_id, description, resource_id) VALUES ( $sprint_id, '$description', $member_id)");
	$id = $database->last_inserted_id();
	
	if ($is_late)
	{
		$date_expression = 'NOW()';
	}
	else 
	{
		// find a random ( :) ) date that is earlier than the first sprint date.
		$date_expression = "'1969-10-18'";	
	}
    $database->exec("INSERT INTO report( task_id, resource_id, burnt, estimate, date) VALUES( $id, 0, 0, $estimate, $date_expression)");
	
	$task_info = $database->get_single_result( get_task_query( 0, $id));
	$task_info['placeholder'] = $placeholder;
	
	print json_encode($task_info);
}


// START OF SCRIPT
if (isset( $_GET['sprint_id']))
{
    $sprint_id = $_GET['sprint_id'];
}

if (isset($_GET['action']))
{
	switch ($_GET['action'])
	{
	case 'report':
		make_global( $_GET, Array('ref_date','task_id', 'estimate', 'spent'));
		handle_report($task_id, $ref_date, $estimate, $spent);
		break;
	case 'move':
		make_global( $_GET, Array('task_id', 'status', 'owner', 'ref_date', 'estimate', 'spent'));
		handle_move( $task_id, $status, $owner, $ref_date, $estimate, $spent);
		break;
	case 'add':
		handle_add( $_GET);
		break;
	case 'table':
	    print_task_table_data($sprint_id);
	    break;
	case 'csv':
		print_task_table_csv( $sprint_id);
		break;
	}
}
else 
{
		print_task_data( $sprint_id);
}