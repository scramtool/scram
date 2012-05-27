<?php 
require_once 'connect_db.inc.php';

$member_id = -1;
$member_name = "Somebody I Don't Know";
$need_identification = true;
if (!isset($_COOKIE['scram_team_member_name']))
{
	if (isset( $_GET['member_name']))
	{
			setcookie( 'scram_team_member_name', $_GET['member_name']);
			$member_name = $_GET['member_name'];
			$need_identification = false;
	}
}
else
{
	$member_name = $_COOKIE['scram_team_member_name'];
	$need_identification = false;
}

$member_name = $database->escape($member_name);
$member = $database->get_single_result( "select resource_id from resource where name = '$member_name'");
$member_id = $member['resource_id'];
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="css/start/jquery-ui-1.8.20.custom.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src="scripts/jquery-1.7.2.min.js"/></script>
<script type="text/javascript" src="scripts/jquery-ui-1.8.20.custom.min.js"/></script>
<script type="text/javascript" src="scripts/scram.js"></script>
<script type="text/javascript">
var member_id = <?=$member_id?>;
var member_name = '<?=$member_name?>';
var need_identification = <?=$need_identification?1:0?>;
$(document).ready(function() {
	loadTasks( 1, function( task) {
		item = $('<li/>', {'class': 'taskNote', 'html':makeTaskMarkup( task, worksOnTask( member_id, task))});
		if (worksOnTask( member_id, task))
		{
			item.appendTo("#taskList");
		}
		else
		{
			list = '#' + task.status + 'List';
			item.appendTo( list);
		}
	});
	$("#allTasks").accordion({autoheight:false});
});
</script>
<title>Task overview for <?=$member_name?></title>
</head>
<body>
<h1>Tasks for <?=$member_name?></h1>
<div id="menu">
<h2>Menu</h2>
<ul>
<li><a href="sprints.php">sprint overview</a>
<li>team member page
</ul>
</div>
<div class="scrumBoardTasks" id="tasks">
<h2>Tasks</h2>
<ul id = "taskList">
</ul>
<div id="allTasks" style="bottom:0;left:0;">
    <h3><a href="#">To Do</a></h3>
    <div class="scrumBoardTasks"><ul id="toDoList"></ul></div>
    <h3><a href="#">In Progress</a></h3>
    <div class="scrumBoardTasks"><ul id="inProgressList"></ul></div>
    <h3><a href="#">To be Verified</a></h3>
    <div class="scrumBoardTasks"><ul id="toBeVerifiedList"></ul></div>
    <h3><a href="#">Done</a></h3>
    <div class="scrumBoardTasks"><ul id="doneList"></ul></div>
    <h3><a href="#">Forwarded</a></h3>
    <div class="forwardedTasks"><ul id="forwardedList"></ul></div>
</div>
</div>
</body>
</html>