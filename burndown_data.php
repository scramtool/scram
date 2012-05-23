<?php
require_once 'connect_db.inc.php';

function get_burndown_grid_query( $sprint_id)
{
	return <<<EOT
	select report.*, grid_date, task_burn
	from report join
	    (
	        select max(report.date) as last_date, sum( report.burnt) as task_burn, grid.task_id as task_id, grid.date as grid_date
	        from report join
	            (
	            select * from (select distinct( date)
	            from report join task on task.task_id = report.task_id where task.sprint_id = $sprint_id) as dates,
	                    (select task.task_id from task where sprint_id = 1) as tasks order by date, task_id
	             ) as grid
	             on report.task_id = grid.task_id and report.date <= grid.date group by grid.task_id, grid.date
	      ) as grid_reports
	     on last_date = report.date and grid_reports.task_id = report.task_id
	order by grid_date, report.task_id
EOT;
}

function get_burndown_query( $sprint_id)
{
	$grid_query = get_burndown_grid_query($sprint_id);
	return "select grid_date, sum( estimate) as burn_down, sum( task_burn) as burn_up from ($grid_query) as bd_grid group by grid_date";
}

function print_burndown_query( $sprint_id)
{
	global $database;
	$query = $database->exec( get_burndown_query($sprint_id));
	$fields = $database->num_fields($query);
	
	for ($field = 1; $field <= $fields; ++$field)
	{
		print $database->field_name($query, $field) . ',';
	}
	print "\n";
	
	while ($database->fetch_row($query))
	{
		for ($field = 1; $field <= $fields; ++$field)
		{
			print $database->result( $query, $field) . ',';
		}
		print "\n";
	}
}

$sprint_id = $_GET['sprint_id'];
print_burndown_query($sprint_id);
