/**
 * 
 */
var taskListUrl = 'task_data.php';

/**
 * Load all taks of a particular sprint and call the given callback function to each individual task.
 * @param sprint numerical id of the sprint for which to load all tasks
 * @param callback function to call for each individual task.
 */
function loadTasks( sprint, callback) {
	$.getJSON( taskListUrl + '?sprint_id=' + sprint, function(taskList) {
		$.each( taskList, function( index, task) {
			callback( task);
		});
	});
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
		reported_time = '<div class="clickable estimate">' + task.estimate + '</div>';
	}
	else
	{
		reported_time = 
			'<form>'+
			' <label for="estimate">estimated time left:</label><input type="text" name="estimate" class="estimate" value="' + task.estimate + '"/>'+
			' <label for="spent">time spent:</label><input type="text" name="estimate" value="0"/>'+
			'</form>';
	}
	html = '<div class="yellowNote"><div class="taskDescription">'+ task.description + '</div>'+ reported_time +'</div>';
	return html;
}

function isOnSameDay( date1, date2)
{
	return 	date1.getFullYear() == date2.getFullYear()
		&&	date1.getMonth() 	== date2.getMonth()
		&&	date1.getDate() 	== date2.getDate();
}