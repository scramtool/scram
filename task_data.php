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

function get_task_query( $sprint_id)
{
	global $database;
	$sprint_id = $database->escape( $sprint_id);
	return <<<EOT
SELECT task.* , resource.name, report.estimate, report_date
FROM (
	SELECT report.task_id, max( date ) AS report_date
	FROM report
	JOIN task ON report.task_id = task.task_id
	WHERE sprint_id =1
	GROUP BY report.task_id
) AS task_report
JOIN report ON task_report.task_id = report.task_id AND task_report.report_date = report.date
RIGHT OUTER JOIN task ON task.task_id = report.task_id
LEFT OUTER JOIN resource ON task.resource_id = resource.resource_id
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

if (isset( $_GET['sprint_id']))
{
	$sprint_id = $_GET['sprint_id'];
	print_task_data($sprint_id);
}