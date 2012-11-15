<?php
//
//  Copyright (C) 2012 Danny Havenith
//
//  Distributed under the Boost Software License, Version 1.0. (See
//  accompanying file LICENSE_1_0.txt or copy at
//  http://www.boost.org/LICENSE_1_0.txt)
//

class Log
{
	function __construct( $database)
	{
		$this->database = $database;
	}
	
	function estimate( $task_id, $new_estimate, $time_spent)
	{
		// a query that fetches the last estimate and inserts a log that shows the last estimate, the new estimate and the last effort spent
		$query = <<<EOT
INSERT INTO log( time, resource_id, task_id, details, type)
SELECT NOW(), resource_id, a.task_id, CONCAT(  'left: ', $new_estimate,'(', estimate, ') spent:', $time_spent) as details, 'estimate' 
FROM report as a 
     JOIN (select max(date) as date, task_id from report where task_id = $task_id) as b 
     ON a.date = b.date and a.task_id = b.task_id 
WHERE a.task_id = $task_id	
EOT;
		$this->database->exec( $query);
	}
	
	function move( $task_id, $new_state)
	{
		$this->do_log( 'move', $task_id, $new_state);
	}
	
	private function do_log( $type, $task_id, $details)
	{
		$timestamp = $this->get_current_time();
		$this->database->exec( 
			"INSERT INTO log( time, resource_id, type, task_id, details) " .
			"SELECT  '$timestamp', resource_id, '$type', $task_id, '$details' FROM task WHERE task_id = $task_id");
	} 
	
	private function get_current_time()
	{
		return date( "Y-m-d H:i:s", time());
	}
	
	private $database;
	private $resource_id;
}
