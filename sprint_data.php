<?php
require_once 'connect_db.inc.php';
require_once 'utilities.inc.php';
/**
 * 
 * Create the query text for a query that generates the dates for all weekdays in a sprint period.
 * @param unknown_type $sprint_id
 * @return string
 */
function create_date_range_query( $sprint_id)
{
	global $database;
	$add_expression = $database->TxtAddDaysToDate( 'start_date', 'counter');
	return <<<EOT
	select $add_expression as date 
	from sprint
	 join (
	 	select (a.a + b.a* 10) as counter 
	 	from (select 0 as a union all select 1 union all select 2 union all select 3 union all select 4 union all select 5 union all select 6 union all select 7 union all select 8 union all select 9) as a
		cross join (select 0 as a union all select 1 union all select 2 union all select 3 union all select 4 union all select 5 union all select 6 union all select 7 union all select 8 union all select 9) as b
	) as counter 
	where sprint.sprint_id = $sprint_id AND $add_expression <= sprint.end_date AND WEEKDAY($add_expression) < 5
EOT;
}

function handle_get( $sprint_id)
{
	global $database;
	$sprint_id = $database->escape( $sprint_id);
	$sprint_info = $database->get_single_result( "SELECT * FROM sprint WHERE sprint_id = $sprint_id");
	print json_encode($sprint_info);	
}

// START OF SCRIPT
if (isset($_GET['action']))
{
	$action = $_GET['action'];
	if ($action == 'get')
	{
		make_global( $_GET, Array('sprint_id'));
		handle_get( $sprint_id);
	}
}
