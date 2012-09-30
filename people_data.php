<?php
require_once 'connect_db.inc.php';

function print_people_data( $sprint_id)
{
	global $database;

	$query = <<<EOT
SELECT name, resource.resource_id
FROM resource
RIGHT OUTER JOIN task ON task.resource_id = resource.resource_id
WHERE resource.resource_id IS NOT NULL
AND task.sprint_id =$sprint_id
GROUP BY resource.resource_id
ORDER BY name
EOT;

	$headers = array();
	$table = array();
	$database->get_result_table($query, $headers, $table);
	
	print json_encode($table);
}

if (isset( $_GET['sprint_id']))
{
	$sprint_id = $_GET['sprint_id'];
	print_people_data($sprint_id);
}
