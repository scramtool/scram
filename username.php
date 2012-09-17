<?php
/**
 * obtain a user name.
 * Other pages will redirect to this page if the user name is not known.
 */
if (isset( $_GET['member_name']))
{
	require_once 'connect_db.inc.php';
	$name = $database->escape($_GET['member_name']);
	$user = $database->get_single_result("SELECT resource_id FROM resource where name = '$name'");
	if (!isset($user['resource_id']))
	{
		$database->exec("INSERT INTO resource( name) VALUES('$name')");
	}
	setcookie( 'scram_team_member_name', $_GET['member_name'], time()+60*60*24*365);
	header("Location: team_member.php");
}
?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	
	<link href="css/smoothness/jquery-ui-1.8.20.custom.css" rel="stylesheet" type="text/css"/>
	<script type="text/javascript" src="scripts/jquery-1.7.2.min.js"/></script>
	<script type="text/javascript" src="scripts/jquery-ui-1.8.20.custom.min.js"/></script>
	<script type="text/javascript" src="scripts/jquery.jeditable.mini.js"/></script>
	<script type="text/javascript" src="scripts/jquery.numeric.js"/></script>
	<script type="text/javascript">
	$(document).ready(function() {
		var item = $("#member_name");
		$( "#member_name" ).autocomplete({
			source: "names.php",
			minLength: 1
			});
	});
	</script>
	
	<link href="css/scram.css" rel="stylesheet" type="text/css"/>
</head>
<body>
<h1>Please tell me who you are</h1>
I need to know who you are. Please give your name here. The name you use here will show on each task that is allocated to you.
This page should be shown only once. The name you provide here will be remembered.
<div id="dialog-form" title="Create new user">
<form method="get">
<label for="name">Name</label>
<input name="member_name" id="member_name" />
<input type="submit" value="Continue"/>
</form>
</div>
</body>
</html>