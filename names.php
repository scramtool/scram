<?php
require_once 'connect_db.inc.php';

if (isset($_GET['term']))
{
	$term = $database->escape($_GET['term']);
	$filter = " WHERE name LIKE '%$term%'";
}
else
{
	$filter = '';
}

$table = Array();
$headers = Array();
$result = $database->exec("SELECT name FROM resource $filter");
$output = Array();
while ($database->fetch_row( $result))
{
	$output[] = $database->result($result, 1);
}
print json_encode( $output);