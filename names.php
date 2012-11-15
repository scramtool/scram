<?php
//
//  Copyright (C) 2012 Danny Havenith
//
//  Distributed under the Boost Software License, Version 1.0. (See
//  accompanying file LICENSE_1_0.txt or copy at
//  http://www.boost.org/LICENSE_1_0.txt)
//

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