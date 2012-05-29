/**
 * 
 */
var taskListUrl = 'task_data.php';
var changeTextUrl = 'change_task_text.php';

/**
 * An associative container that holds task_id->task pairs.
 * Because task_ids are numerical, they get prepended with 'x' so that javascript
 * turns this array into a sparse map.
 */
var currentTasks = new Array();

/**
 * Load all tasks of a particular sprint, fill the global array currentTasks and refresh the UI.
 * @param sprint numerical id of the sprint for which to load all tasks
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

/**
 * Submit the estimates that have been filled in by the user.
 * @returns {Boolean}
 */
function submitEstimate( )
{
	var query = "";
	$(this).parent().children("input").each( function(index, element) {query += '&' + element.name + "=" + element.value;});
	$(this).replaceWith( '<img class="centered" src="images/ajax-loader.gif"/>');
	$.getJSON( taskListUrl + '?action=report' + query,
			function (task)
			{
				currentTasks['x'+task.task_id] = task;
				$("#container-for-" + task.task_id).html( makeTaskMarkup( task, true));
			});
	return false;
}

function submitText( value, settings)
{
     console.log(settings.submitdata.task_id);
     $.getJSON( changeTextUrl + '?id=' + settings.submitdata.task_id + '&text=' + encodeURIComponent(value),
			function (taskText)
			{
				$("#description-for-" + taskText.task_id).html( taskText.text);
			});
     
     //return settings.submitdata.task_id + ' : ' + value;
     return '<img class="centered" src="images/ajax-loader.gif"/>';
}

function makeEditable( index, value)
{
	task_id = $(value).attr('id').substring(16);
	$(value).editable( submitText, {
		width	  : 190,
		height	  : 60,
        type      : 'textarea',
        cancel    : 'Cancel',
        submit    : 'OK',
        submitdata: {'task_id':task_id},
        indicator : 'Please wait...',
        tooltip   : 'Click to edit...'
    });
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
	$(".taskList").sortable({'connectWith':'.taskList', receive: noteReceived});
	$('.taskDetails').each( makeEditable); 
}

function noteReceived( event, ui)
{
	task_id = $(ui.item).attr("id").replace('container-for-','');
	new_status = $(this).attr("id").replace('List', '');
	old_status = $(ui.sender).attr("id").replace('List','');
	if (new_status == 'inProgress' && old_status == 'myTasks' )
	{
		$(ui.sender).sortable('cancel');
	}
	else
	{
		if (new_status == 'myTasks')
		{
			new_status = 'inProgress';
			to_mytasks = true;
		}
		else
			{
			to_mytasks = false;
			}
		query = '?action=move&task_id=' + task_id +'&status=' + new_status;
		if (to_mytasks && currentTasks['x'+task_id].resource_id != member_id)
		{
			query += '&owner=' + member_id;
		}
		$('#description-for-' + task_id).html('<img class="centered" src="images/ajax-loader.gif"/>');
		$.getJSON( taskListUrl + query,
				function (task)
				{
					currentTasks['x'+task.task_id] = task;
					$("#container-for-" + task.task_id).html( makeTaskMarkup( task, to_mytasks));
					$(".submitReportButton").button( {icons: {primary: "ui-icon-gear"}, text:false}).click( submitEstimate);
					$('.taskDetails').each( makeEditable); 
				});
	}
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
	html = '<div class="yellowNote"><div class="taskNumbers">'+ reported_time + '</div><div id="description-for-' + task.task_id + '" class="taskDetails">'+ task.description  +'</div></div>';
	return html;
}

function makeWaitMarkup()
{
	html = '<div class="yellowNote"><div class="taskNumbers">Please wait</div><div class="taskDetails"><img class="centered" src="images/ajax-loader.gif"/></div></div>';
}

function isOnSameDay( date1, date2)
{
	return 	date1.getFullYear() == date2.getFullYear()
		&&	date1.getMonth() 	== date2.getMonth()
		&&	date1.getDate() 	== date2.getDate();
}