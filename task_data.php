<?php
require_once 'connect_db.inc.php';

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
	
	var $id;
	var $description;
	var $status;
	var $resourcename;
}

function get_task_query( $sprint_id, $task_id = -1)
{
	global $database;
	$task_restriction = "";
	if ($task_id > 0)
	{
		$task_restriction = "WHERE task.task_id = $task_id";
		$report_restriction = "WHERE report.task_id = $task_id";
	}
	else
	{
		$task_restriction = "";
		$report_restriction = "WHERE sprint_id =$sprint_id";
	}
	$sprint_id = $database->escape( $sprint_id);
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

function make_global( &$input, $names)
{
	foreach ($names as $name) {
		if (isset( $input[$name]))
		{
			$GLOBALS[$name] =  $input[$name];
		}
	}
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
		handle_move( $task_id, $status, $owner);
	}
	
}
else 
{
	if (isset( $_GET['sprint_id']))
	{
		$sprint_id = $_GET['sprint_id'];
		print_task_data($sprint_id);
	}
}