<?php 
require_once 'connect_db.inc.php';

$member_id = -1;
$member_name = "Somebody I Don't Know";
$need_identification = true;
if (isset( $_GET['member_name']))
{
	setcookie( 'scram_team_member_name', $_GET['member_name']);
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
$member_id = $member['resource_id'];
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="css/smoothness/jquery-ui-1.8.20.custom.css" rel="stylesheet" type="text/css"/>
<link href="css/scram.css" rel="stylesheet" type="text/css"/>
<link href="css/holygrail.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src="scripts/jquery-1.7.2.min.js"/></script>
<script type="text/javascript" src="scripts/jquery-ui-1.8.20.custom.min.js"/></script>
<script type="text/javascript" src="scripts/jquery.jeditable.mini.js"/></script>
<script type="text/javascript" src="scripts/scram.js"></script>
<script type="text/javascript">
var member_id = <?=$member_id?>;
var member_name = '<?=$member_name?>';
var need_identification = <?=$need_identification?1:0?>;
var sprint_id = 1;
//var tasks = new Array();
$(document).ready(function() {
	loadTasks( sprint_id, refreshTaskUi);
	});
</script>
<title>Task overview for <?=$member_name?></title>
</head>
<body>
<div class="header">
	<h1>Tasks for <?=$member_name?></h1>
</div>
<div class="colmask leftmenu">
    <div class="colright">
        <div class="col1wrap">
			<div class="mainColumn" id="tasks">
			    <h3 class="categoryHeader"><a href="#">My Tasks</a></h3>
			    <div class="categoryContent"><ul id="myTasks" class="taskList"></ul><br style="clear:both;"/></div>
			    <h3 class="categoryHeader"><a href="#">To Do</a></h3>
			    <div class="categoryContent"><ul id="toDoList" class="taskList"></ul><br style="clear:both;"/></div>
			    <h3 class="categoryHeader"><a href="#">In Progress</a></h3>
			    <div class="categoryContent"><ul id="inProgressList" class="taskList"></ul><br style="clear:both;"/></div>
			    <h3 class="categoryHeader"><a href="#">To be Verified</a></h3>
			    <div class="categoryContent"><ul id="toBeVerifiedList" class="taskList"></ul><br style="clear:both;"/></div>
			    <h3 class="categoryHeader"><a href="#">Done</a></h3>
			    <div class="categoryContent"><ul id="doneList" class="taskList"></ul><br style="clear:both;"/></div>
			    <h3 class="categoryHeader"><a href="#">Forwarded</a></h3>
			    <div class="categoryContent forwardedTasks"><ul id="forwardedList" class="taskList"></ul><br style="clear:both;"/></div>
			</div>
		</div>
		<div id="menu" class="menuColumn">
			<h2>Menu</h2>
			<ul>
			<li><a href="sprints.php">sprint overview</a>
			<li>team member page
			</ul>
		</div>
	</div>
</div>
</body>
</html>