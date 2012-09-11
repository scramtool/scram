<?php 
require_once 'get_username.inc.php';
require_once 'get_sprint_id.inc.php';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="css/smoothness/jquery-ui-1.8.20.custom.css" rel="stylesheet" type="text/css"/>
<link href="css/scram.css" rel="stylesheet" type="text/css"/>
<link href="css/holygrail.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src="scripts/jquery-1.7.2.min.js"/></script>
<script type="text/javascript" src="scripts/jquery-ui-1.8.20.custom.min.js"/></script>
<script type="text/javascript" src="scripts/jquery.jeditable.mini.js"/></script>
<script type="text/javascript" src="scripts/jquery.numeric.js"/></script>
<script type="text/javascript" src="scripts/scram.js"></script>
<script type="text/javascript" src="scripts/date.js"></script>

<script type="text/javascript">
var member_id = <?=$member_id?>;
var member_name = '<?=$member_name?>';
var need_identification = <?=$need_identification?1:0?>;
var sprint_id = <?=$sprint_id?>;
//var tasks = new Array();
$(document).ready(function() {
	 $.ajaxSetup({
		        // Disable caching of AJAX responses */
		        cache: false
		        });	
     $(".positive-integer").numeric({ decimal: false, negative: false }, function() { alert("Positive integers only"); this.value = ""; this.focus(); });
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
			<li><a href="sprint_details.php">sprint overview</a>
			<li>team member page
			</ul>
		</div>
	</div>
</div>

</body>
</html>