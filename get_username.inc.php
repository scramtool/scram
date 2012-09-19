<?php
/**
 * After including this page, the global variable $member_id will be set.
 * If the name of the team member cannot be determined by looking at either the get-variables or cookies, this 
 * file will redirect to a form that asks the user for a name.
 * 
 * Note that currently, this is in no way a form of user _authentication_ the user is asked for a name, that's all.
 */

require_once 'connect_db.inc.php';

$member_id = -1;
$member_name = "Somebody I Don't Know";
$need_identification = true;
if (isset( $_GET['member_name']))
{
	setcookie( 'scram_team_member_name', $_GET['member_name'], time()+60*60*24*365);
	$member_name = $_GET['member_name'];
	$need_identification = false;
}

if ($need_identification && isset($_COOKIE['scram_team_member_name']))
{
	$member_name = $_COOKIE['scram_team_member_name'];
	$need_identification = false;
}

$member_name_db = $database->escape($member_name);
$member = $database->get_single_result( "select resource_id from resource where name = '$member_name_db'");
if (isset($member['resource_id']))
{
	$member_id = $member['resource_id'];
}
else
{
	header("Location: username.php");
}