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

function worksOnTask( resourceId, taskInfo)
{
	return taskInfo.resources.indexOf( resourceId) != -1;
}