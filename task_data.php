<?php
require_once 'connect_db.inc.php';
require_once 'utilities.inc.php';

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

function get_task_query( $sprint_id, $task_id = -1)
{
	global $database;
	$sprint_id = $database->escape( $sprint_id);
	$task_restriction = "";
	if ($task_id > 0)
	{
		$task_restriction = "WHERE task.task_id = $task_id";
		$report_restriction = "WHERE report.task_id = $task_id";
	}
	else
	{
		$task_restriction = "WHERE task.sprint_id = $sprint_id";
		$report_restriction = "WHERE sprint_id =$sprint_id";
	}
	return <<<EOT
SELECT task.* , resource.name, report.estimate, DATE_FORMAT(report_date, "%Y-%m-%d") as report_date
FROM (
	SELECT report.task_id, max( date ) AS report_date
	FROM report
	JOIN task ON report.task_id = task.task_id
	$report_restriction
	GROUP BY report.task_id
) AS task_report
JOIN report ON task_report.task_id = report.task_id AND task_report.report_date = report.date
RIGHT OUTER JOIN task ON task.task_id = report.task_id
LEFT OUTER JOIN resource ON task.resource_id = resource.resource_id
$task_restriction
ORDER BY report.estimate DESC
EOT;
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
	
	$query = "UPDATE task SET status='$status' $owner_update WHERE task_id = $task_id";
	$success = $database->exec($query);
	if ($success)
	{
		print_single_task($task_id);
	}
	
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
function handle_add( $description, $estimate, $sprint_id, $placeholder)
{
	global $database;
	
	$description = $database->escape($description);
	$estimate	 = $database->escape($estimate);
	$sprint_id 	 = $database->escape($sprint_id);
	
	// insert a task into the database. A task id will be created automatically, so we need to retreive that.
	$database->exec("INSERT INTO task(sprint_id, description) VALUES ( $sprint_id, '$description')");
	$id = $database->last_inserted_id();
	
	if (isset($estimate))
	{
		// insert a new report. By default, the date of the report will be today.
		$database->exec("INSERT INTO report( task_id, resource_id, burnt, estimate) VALUES( $id, 0, 0, $estimate)");
	}
	
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
		make_global( $_GET, Array('estimate', 'description', 'placeholder', 'sprint_id'));
		handle_add( $description, $estimate, $sprint_id, $placeholder);
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