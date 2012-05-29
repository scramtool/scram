<?php
if (isset($_GET['id']))
{
	require_once 'connect_db.inc.php';
	
	// todo: should use prepared statements instead of all this escaping.
	$id = $database->escape( $_GET['id']);
	$text = $database->escape($_GET['text']);
	
	$result = $database->exec( "UPDATE task SET description = '$text' WHERE task_id = $id");
	$return = array( 'task_id' => $id, 'text' => $text);
	if (!$result)
	{
		$return['text'] = 'Error: something went wrong while changing text. Refresh page and call Danny';
	}

	print json_encode($return);
}