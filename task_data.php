<?php
require_once 'connect_db.inc.php';

class Task
{
	function __construct( $id, $description, $state, $resource)
	{
		$this->id 			= $id + 0;
		$this->description 	= $description;
		$this->state 		= $state;
		$this->resource 	= $resource + 0;
	}
	
	var $id;
	var $description;
	var $state;
	var $resource;
}

function get_task_query( $sprint_id)
{
	global $database;
	$sprint_id = $database->escape( $sprint_id);
	return "select * from task where sprint_id = $sprint_id order by task_id";
}

function print_task_data( $sprint_id)
{
	global $database;
	$query = $database->exec( get_task_query($sprint_id));
	
	$current_task_id = -1;
	$current_task = NULL;
	$tasks = array();
	while ($task = $database->fetch_row($query))
	{
		if ($task['task_id'] == $current_task_id)
		{
			$current_task->resources[] = $task['resource_id'] + 0;
		}
		else 
		{
			if ($current_task)
			{
				$tasks[] = $current_task;
			}
			$current_task = new Task( $task['task_id'], $task['description'], $task['resource_id']);
			$current_task_id = $current_task->id;
		}
	}
	if ($current_task)
	{
		$tasks[] = $current_task;
	}
	
	print json_encode($tasks);
}

if (isset( $_GET['sprint_id']))
{
	$sprint_id = $_GET['sprint_id'];
	print_task_data($sprint_id);
}