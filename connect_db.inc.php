<?php
//
//  Copyright (C) 2012 Danny Havenith
//
//  Distributed under the Boost Software License, Version 1.0. (See
//  accompanying file LICENSE_1_0.txt or copy at
//  http://www.boost.org/LICENSE_1_0.txt)
//

require_once( "server_config.inc.php");
if ($db_type == "odbc")
{
	require_once 'odbc_wrapper.php';
	$database = new odbc_wrapper( $db_name, $db_user, $db_password);
}
elseif ($db_type == 'mysql')
{
	require_once 'mysql_wrapper.php';
	$database = new mysql_wrapper( $db_server, $db_name, $db_user, $db_password);
}

function make_timestamp( $db_time)
{
	preg_match( "/^(\d+)-(\d+)-(\d+)\s(\d+):(\d+):(\d+)/", $db_time, $match);
	$result =  mktime( $match[4], $match[5], $match[6], $match[2], $match[3], $match[1]);
	return $result;
}

?>
