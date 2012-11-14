<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<?php 
require_once 'connect_db.inc.php';
require_once 'task_data.inc.php';
require_once 'wiky.inc.php';
if (!isset($_GET['task_id']))
{
	die( "error on page: I need a task_id");
}
$task_id =  (int)$_GET['task_id'];
$task = $database->get_single_result( get_task_query(0,$task_id));
$wiki = new wiky();
$task_html = $wiki->parse( $task['story']);

?>
<html>
<head>
<script type="text/javascript">
$(document).ready(function(){
	loadTaskCharts(<?=$task['sprint_id']?>, <?=$task_id?>, null, 'taskBurnDown');
});
</script>
</head>
<body>
<div id='taskForm' class='yellowNote'>
<div class='taskNumbers'><div id='taskEstimate' class='frozen estimate'><?=$task['estimate']?></div><div id='taskOwner'><?=$task['name']?></div></div>
<div class='smallChart' style="width:400px;height:200px" id='taskBurnDown' ></div>
<div class='taskDescription' id='description'><?=$task['description']?></div>
<div class='taskStory' id='story'><?=$task_html?></div>
<br style="clear:both" />
</div>
</body>