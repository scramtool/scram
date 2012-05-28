/**
 * 
 */
var taskListUrl = 'task_data.php';
var currentTasks = new Array();

/**
 * Load all tasks of a particular sprint and call the given callback function to each individual task.
 * @param sprint numerical id of the sprint for which to load all tasks
 * @param callback function to call for each individual task.
 */
function loadTasks( sprint) {
	$.getJSON( taskListUrl + '?sprint_id=' + sprint, 
			function( tasklist) {
				currentTasks = new Array();
				$.each( tasklist, function( index, task){
					currentTasks['x' + task.task_id] = task;
				});
				refreshTaskUi();
			});
}

function submitEstimate( )
{
	var query = "";
	$(this).parent().children("input").each( function(index, element) {query += '&' + element.name + "=" + element.value;});
	$.getJSON( taskListUrl + '?action=report' + query,
			function (task)
			{
				currentTasks['x'+task.task_id] = task;
				$("#container-for-" + task.task_id).html( makeTaskMarkup( task, true));
			});
	return false;
}

function refreshTaskUi()
{
	// clear all scrumboards
	$(".scrumBoardTasks ul").html("");
	
	// now send the tasks to their appropriate scrumboard.
	for (var task_key in currentTasks)
	{
		var task = currentTasks[task_key];
		item = $('<li/>', {'class': 'taskNote', 'id':'container-for-' + task.task_id, 'html':makeTaskMarkup( task, worksOnTask( member_id, task))});
		if (worksOnTask( member_id, task))
		{
			item.appendTo("#myTasks");
		}
		else
		{
			list = '#' + task.status + 'List';
			item.appendTo( list);
		}
	}
	$(".submitReportButton").button( {icons: {primary: "ui-icon-gear"}, text:false}).click( submitEstimate);
	$(".taskList").sortable({'connectWith':'.taskList'}).disableSelection();
}

/**
 * Does the developer with the given resourceId currently work on the given task?
 * @param resourceId
 * @param taskInfo
 * @returns {Boolean}
 */
function worksOnTask( resourceId, taskInfo)
{
	return taskInfo.resource_id == resourceId && taskInfo.status == 'inProgress';
}

/**
 * Create the html markup for display of a task.
 * @param task The task to display
 * @param isInWorkList Whether the task is displayed in the current work list.
 * @returns
 */
function makeTaskMarkup( task, isInWorkList)
{
	if (!isInWorkList || isOnSameDay( new Date(task.report_date), new Date()))
	{
		reported_time = '<div class="clickable estimate frozen">' + task.estimate + '</div><div>&nbsp;&nbsp;&nbsp;</div><div>' + task.name+ '</div>';
	}
	else
	{
		reported_time = 
			'<form>'+
			' <input type="hidden" class="holdsTaskId" name="task_id" value="' + task.task_id + '"/>' +
			' <label for="estimate">left:</label><input type="text"  id="estimate" name="estimate" class="estimate" value="' + task.estimate + '"/>'+
			' <label for="spent">spent:</label><input type="text" id="spent" name="spent" class="estimate" value="0"/>'+
			' <button class="submitReportButton">Submit Todays numbers</button>'+
			'</form>';
	}
	html = '<div class="yellowNote"><div class="taskNumbers">'+ reported_time + '</div><div class="taskDetails">'+ task.description  +'</div></div>';
	return html;
}

function isOnSameDay( date1, date2)
{
	return 	date1.getFullYear() == date2.getFullYear()
		&&	date1.getMonth() 	== date2.getMonth()
		&&	date1.getDate() 	== date2.getDate();
}