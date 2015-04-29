<?php
//
//  Copyright (C) 2012, 2013 Danny Havenith
//
//  Distributed under the Boost Software License, Version 1.0. (See
//  accompanying file LICENSE_1_0.txt or copy at
//  http://www.boost.org/LICENSE_1_0.txt)
//
require_once 'connect_db.inc.php';
require_once 'utilities.inc.php';

function list_person_tasks( &$parameters)
{
    global $database;
    $sprint_id = get_escape_if_defined( $database, $parameters, 'sprint_id');
    $person_id = get_escape_if_defined( $database, $parameters, 'resource_id');
    
    $query = <<<EOT
    SELECT *
    FROM report
    JOIN task ON report.task_id = task.task_id
    WHERE report.resource_id = $person_id
    AND sprint_id = $sprint_id
    ORDER BY task.task_id, date
EOT;
    
    $headers = array();
    $table = array();
    $database->get_result_table($query, $headers, $table);
    
    return json_encode($table);
}

function list_people( &$parameters)
{
	global $database;
    $sprint_id = get_escape_if_defined( $database, $parameters, 'sprint_id');
    
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
	
	return json_encode($table);
}

print dispatch_command( $_GET, 'action', array( 'list' => 'list_people', 'tasks' => 'list_person_tasks'));
