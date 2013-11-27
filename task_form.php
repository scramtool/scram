<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<?php 
//
//  Copyright (C) 2012 Danny Havenith
//
//  Distributed under the Boost Software License, Version 1.0. (See
//  accompanying file LICENSE_1_0.txt or copy at
//  http://www.boost.org/LICENSE_1_0.txt)
//

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

$inputwidth = 50;
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
<form>
<div id='taskForm' class='yellowNote'>
<div class="taskNumbers">
<div>
    <input type="text" size='20' id="taskOwner" name="member_name" class="taskOwner" value='<?=$task['name']?>'/>
</div>
<div>
    <label for="taskEstimate">left:</label>
    <input type='text' id='taskEstimate' class='estimate positive-integer show_changes' value='<?=$task['estimate']?>'>
    <label for="taskSpent">spent today:</label>
    <input type='text' id='taskSpent' class='estimate positive-integer show_changes' value='<?=$task['burnt']?>'>
</div>
</div>
<textarea cols= '<?=$inputwidth?>' class='taskDescription' id='description' ><?=$task['description']?></textarea>
<br style="clear:both" />
<button class='submitTaskButton'>Submit changes</button>
<div class='taskStory' id='story'><?=$task_html?></div>
</div>
<div class='smallChart' style="width:400px;height:200px" id='taskBurnDown' ></div>
</form>
</body>