<?php
require_once 'connect_db.inc.php';
require_once 'get_username.inc.php';
require_once 'get_sprint_id.inc.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<script type="text/javascript" src="scripts/jquery-1.7.2.min.js" ></script>
<script type="text/javascript" src="scripts/jquery-ui-1.8.20.custom.min.js" ></script>
<script type="text/javascript" src="scripts/jquery.jeditable.mini.js" ></script>
<script type="text/javascript" src="scripts/jquery.numeric.js" ></script>
<script type="text/javascript" src="scripts/raphael-min.js"></script>
<script type="text/javascript" src="scripts/charts.min.js"></script>
<script type="text/javascript" src="scripts/scram.js"></script>
<script type="text/javascript" src="scripts/scram-charts.js"></script>
<script type="text/javascript" src="scripts/scram-availability.js"></script>
<script type="text/javascript" src="scripts/date.js"></script>

<link href="css/holygrail.css" rel="stylesheet" type="text/css" />
<link href="css/scram.css" rel="stylesheet" type="text/css" />
<link href="css/smoothness/jquery-ui-1.8.20.custom.css" rel="stylesheet" type="text/css" />

<script type="text/javascript">
var member_id = <?=$member_id?>;
var member_name = '<?=$member_name?>';
var need_identification = <?=$need_identification?1:0?>;
var sprint_id = <?=$sprint_id?>;

$(document).ready(function() {
	 $.ajaxSetup({
	        // Disable caching of AJAX responses */
	        cache: false
	        });	
	$( "#tabs" ).tabs();
	loadCharts( sprint_id);
	loadTasks( sprint_id, refreshSprintTasks);
	loadPeople( sprint_id, refreshSprintPeople);
	loadSprintDetails( sprint_id, refreshSprintDetails);
	loadAvailability( sprint_id, function ( table) { createAvailabilityTable( 'tabs-3', table);});
	$(".newTaskButton").button( {icons: {primary: "ui-icon-plus"}, text:false}).click( submitNewTask);
	$(".firstToFocus").focus();
	$( "#member_name" ).autocomplete({
		source: "names.php",
		minLength: 1
		});	
	});

</script>

<title>Sprint details</title>

</head>

<body>

	<div class="colmask leftmenu">
		<div class="colright">
			<div class="col1wrap">
				<div class="mainColumn" id="tasks">
					<h3 class="categoryHeader">
						<a href="#">Sprint Details</a>
					</h3>
					<div class="categoryContent" id="sprintDetails">
					</div>

					<div id="tabs">
						<ul>
							<li><a href="#tabs-1">Stats</a></li>
							<li><a href="#tabs-2">Details</a></li>
							<li><a href="#tabs-3">Availability</a></li>
						</ul>
						<div id="tabs-1" >
						<div id="graphwrapper">
							<h3 class="categoryHeader">
								<a href="#">Burn Down</a>
							</h3>
							<div class="categoryContent">
								<div id='burndown' style="width:800px;height:400px" class="bigChart"><img class="centered" src="images/ajax-loader.gif"/></div>
							</div>
							<h3 class="categoryHeader">
								<a href="#">Burn Up</a>
							</h3>
							<div class="categoryContent">
								<div id='burnup' style="width:800px;height:400px" class="bigChart"><img class="centered" src="images/ajax-loader.gif"/></div>
							</div>
						<br style="clear:both"/>
						</div>
						</div>
						<div id="tabs-2">
							<h3 class="categoryHeader">
								<a href="#">Sprint People</a>
							</h3>
							<div class="categoryContent">
								<ul id="sprintPeople" class="taskList"></ul>
								<br style="clear: both;" />
							</div>
							<h3 class="categoryHeader">
								<a href="#">Sprint Tasks</a>
							</h3>
							<div class="categoryTopLine">
								<form>
									New task: 
									<label for="description">Description:&nbsp;</label>
									<input
										type="text" name="description" id="description"
										class="firstToFocus" /> 
									<label for="estimate">Initial
										estimate:&nbsp;</label>
									<input type="text" name="estimate"
										id="estimate" class="estimate positive-integer" />
									<label for="member_name">Person:&nbsp;</label>
									<input type="text" id="member_name" name="member_name" class="member-name" />
									<label for="is_late">Late task:&nbsp;</label>
									<input type="checkbox" id="is_late" name="is_late">
									<button class="newTaskButton">Submit a new task</button>
								</form>
							</div>
							<div class="categoryContent">
								<div id="sprintTasks"></div>
								<br style="clear: both;" />
							</div>
						</div>
						<div id="tabs-3" style="overflow:scroll"></div>
					</div>
				</div>
			</div>
			<div id="menu" class="menuColumn">
				<h2>Menu</h2>
				<ul>
					<li>sprint overview
					<li><a href="team_member.php">team member page</a>
				</ul>
			</div>
		</div>
	</div>
</body>
</html>
