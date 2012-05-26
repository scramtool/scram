<?php
require_once 'connect_db.inc.php';

/**
 * 
 * Create the query text for the burndown grid.
 * 
 * The burndown grid for a given sprint returns the cartesian product of all distinct dates for which a report
 * exists with all tasks that are part of that sprint. Each row of the query result represents
 * a cell in a table with the dates as columns and the tasks as rows.
 * 
 *  For each cell, the following information is returned:
 *  - the date of the cell, the "grid date" (this is not necessarily the date in the report, since an older report could be the most recent)
 *  - all report information from the most recent report on, or before the date of that cell. This includes the task id.
 *  - the sum of all reported burnup on, or before the grid date for the task of the cell (the "task burn")
 *   
 * @param unknown_type $sprint_id
 * @return string
 */
function get_burndown_grid_query( $sprint_id)
{
	global $database;
	$sprint_id = $database->escape( $sprint_id);
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


/**
 * Create the query text for accumulated burndown information.
 * 
 * The returned query text essentially takes the text from the burndown grid query and accumulates the estimates (as burn_down)
 * and task burnup (as burn_up) for each date of the sprint for which reports exist.
 * 
 * @param unknown_type $sprint_id
 * @return string
 */
function get_burndown_query( $sprint_id)
{
	$grid_query = get_burndown_grid_query($sprint_id);
	return "select grid_date, sum( estimate) as burn_down, sum( task_burn) as burn_up from ($grid_query) as bd_grid group by grid_date";
}


/**
 * Output the results of the burndown-query to standard output in csv-format.
 * 
 * @param unknown_type $sprint_id
 */
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

if (isset( $_GET['sprint_id']))
{
	$sprint_id = $_GET['sprint_id'];
	print_burndown_query($sprint_id);
}
