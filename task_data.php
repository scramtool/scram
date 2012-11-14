<?php
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

function print_single_task( $task_id)
{
	global $database;
	$task_info = $database->get_single_result( get_task_query( 0, $task_id));
	print json_encode($task_info);
}

function handle_report( $task_id, $estimate, $spent)
{
	global $database;
	$log= new Log( $database);
	$log->estimate($task_id, $estimate, $spent);

	$success = $database->exec("INSERT INTO report(task_id, resource_id, date, burnt, estimate) SELECT $task_id, resource_id, NOW(), $spent, $estimate FROM task WHERE task_id = $task_id");
	if ($success)
	{
		print_single_task( $task_id);
	}

}

function handle_move( $task_id, $status, $owner)
{
	global $database;
	$task_id = $database->escape($task_id);
	$status = $database->escape( $status);
	
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
	
	if ($success)
	{
		print_single_task($task_id);
	}

	// if the new status is 'forwarded' or 'done', automatically add a report setting the tasks new estimate to 0
	if ($status == 'forwarded' || $status == 'done')
	{
		$report_query = 
			"INSERT INTO report(task_id, resource_id, date, reason, burnt, estimate) ".
			"SELECT $task_id, resource_id, NOW(), 'forward', 0, 0 FROM task WHERE task_id = $task_id";
			
		$database->exec( $report_query);
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
if (isset($_GET['action']))
{
	$action = $_GET['action'];
	if ($action == 'report')
	{
		make_global( $_GET, Array('task_id', 'estimate', 'spent'));
		handle_report($task_id, $estimate, $spent);
	}
	elseif ( $action == 'move')
	{
		make_global( $_GET, Array('task_id', 'status', 'owner'));

		// Yesteryear, I could just give an undefined variable as an argument to a function
		// and the function would determine if it was a defined value. Nowadays I get warnings
		// if I try to use the undefined variable.
		if (isset( $owner))
		{
			handle_move( $task_id, $status, $owner);
		}
		else
		{
			handle_move( $task_id, $status, null);
		}
	}
	elseif ($action == 'add')
	{
		handle_add( $_GET);
	}
	
}
else 
{
	if (isset( $_GET['sprint_id']))
	{
		$sprint_id = $_GET['sprint_id'];
		print_task_data( $sprint_id);
	}
}