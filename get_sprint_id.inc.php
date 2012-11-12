<?php
/**
 * Including this file will make sure that the global variable $sprint_id is set.
 * 
 */

$sprint_id = 3;
if (isset( $_GET['sprint_id']))
{
	$sprint_id = $_GET['sprint_id'];
}
