/**
 * 
 */
var taskListUrl = 'task_data.php';
var peopleListUrl = 'people_data.php';
var changeTextUrl = 'change_task_text.php';
var sprintDetailsUrl = 'sprint_data.php';
var placeholderCounter = 0;

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
function loadTasks( sprint, callback) {
	$.getJSON( taskListUrl + '?sprint_id=' + sprint, 
			function( tasklist) {
				currentTasks = new Array();
				$.each( tasklist, function( index, task){
					currentTasks['x' + task.task_id] = task;
				});
				callback();
			});
}

function loadPeople( sprint, callback)
{
	$.getJSON( peopleListUrl + '?sprint_id=' + sprint, 
			function( peopleList) {
				currentPeople = new Array();
				$.each( peopleList, function( index, resource){
					currentPeople['x' + resource.resource_id] = resource;
				});
				callback();
			});	
}

function loadSprintDetails( sprint, callback)
{
	$.getJSON( sprintDetailsUrl + '?action=get&sprint_id=' + sprint, callback);	
}

function refreshSprintDetails( sprint)
{
	$("#sprintDetails").html( "<h2>" + sprint.description + "</h2>Start date:" + sprint.start_date + " End date: " + sprint.end_date);
}

function submitNewTask()
{
	var query = "";
	var placeholderid = ++placeholderCounter;
	
	task = new Array();
	$(this).parent().children("input").each( 
			function(index, element) {
				value =  encodeURIComponent( element.value);
				name = element.name;
				query += '&' + name + "=" + value;
				task[name] = element.value;
				});
	
	if (!task.estimate) task.estimate = 8;
	if (task.description.length != 0)
	{
		query += '&placeholder=' + placeholderid;
		query += '&sprint_id=' + sprint_id;
		
		task.name = "Nobody";
		item = 
			$('<li/>', 
				{
					'class': 'taskNote', 
					'id':'container-for-task-stub-' + placeholderid, 
					'html':makeTaskPlaceholder(task)
				}
			);
		item.prependTo( '#sprintTasks');
		$.getJSON( taskListUrl + '?action=add' + query,
				function (task)
				{
					alert( task.toString());
					currentTasks['x'+task.task_id] = task;
					$("#container-for-task-stub-" + task.placeholder).replaceWith( createTaskListItem( task));
				});
	}
	// clear the form and bring the cursor to the first input.
	$('.categoryTopLine form input').val("");
	$('.categoryTopLine form #estimate').val("8");
	$('.firstToFocus').focus();
	
	return false;
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

/**
 * Take a task details item and make it editable.
 * @param index
 * @param value
 */
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

/**
 * This function updates the ui of the team_member page after a task reload.
 */
function refreshTaskUi()
{
	// clear all scrumboards
	$(".scrumBoardTasks ul").html("");
	
	// now send the tasks to their appropriate scrumboard.
	for (var task_key in currentTasks)
	{
		var task = currentTasks[task_key];
		if (worksOnTask( member_id, task))
		{
			addTaskToList(task, "#myTasks");
		}
		else
		{
			list = '#' + task.status + 'List';
			addTaskToList(task, list);
		}
	}
	
	// TODO: figure out how to deal with jqui buttons after refresh.
	// putting it here looks like an ugly hack. Which it is, of course.
	$(".submitReportButton").button( {icons: {primary: "ui-icon-gear"}, text:false}).click( submitEstimate);
	$(".taskList").sortable({'connectWith':'.taskList', receive: noteReceived});
	$('.taskDetails').each( makeEditable); 
	$(".positive-integer").numeric({ decimal: false, negative: false }, function() { alert("Positive integers only"); this.value = ""; this.focus(); });
}

function addPersonToList( person, listName)
{
	var item = 
		$('<li/>', 
			{
				'class': 'personNote', 
				'id':'container-for-person-' + person.resource_id, 
				'html':makePersonMarkup( person)
			}
		);
	item.appendTo( listName);
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
			' <label for="estimate">left:</label><input type="text"  id="estimate" name="estimate" class="estimate positive-integer" value="' + task.estimate + '"/>'+
			' <label for="spent">spent:</label><input type="text" id="spent" name="spent" class="estimate positive-integer" value="0"/>'+
			' <button class="submitReportButton">Submit Todays numbers</button>'+
			'</form>';
	}
	html = '<div class="yellowNote"><div class="taskNumbers">'+ reported_time + '</div><div id="description-for-' + task.task_id + '" class="taskDetails">'+ task.description  +'</div></div>';
	return html;
}

/**
 * create a placeholder for a newly added task.
 * This placeholder will be shown in the UI while the add-request is sent to the server.
 * @param task
 * @returns
 */
function makeTaskPlaceholder( task)
{
	reported_time = '<div class="clickable estimate frozen">' + task.estimate + '</div><div>&nbsp;&nbsp;&nbsp;</div><div>' + task.name+ '&nbsp;<img class="centered" src="images/ajax-loader.gif"/></div>';
	html = '<div class="yellowNote"><div class="taskNumbers">'+ reported_time + '</div><div id="description-for-' + task.task_id + '" class="taskDetails">'+ task.description  +'</div></div>';
	return html;
}

function makePersonMarkup( person, listName)
{
	return '<div class="purpleNote">' + person.name + '</div>';
}

/**
 * Add the given task to an HTML <ul> element.
 * @param task
 * @param listName
 */
function addTaskToList( task, listName)
{
	var item = createTaskListItem( task);
	item.appendTo( listName);
}

function createTaskListItem( task)
{
	return $('<li/>', 
			{
				'class': 'taskNote', 
				'id':'container-for-' + task.task_id, 
				'html':makeTaskMarkup( task, worksOnTask( member_id, task))
			}
		);
}

/**
 * This function refreshes the task list of the sprint overview page after a reload of 
 * the tasks.
 */
function refreshSprintTasks()
{
	// clear all scrumboards
	$("#sprintTasks").html("");
	
	// now send the tasks to their appropriate scrumboard.
	for (var task_key in currentTasks)
	{
		addTaskToList( currentTasks[task_key], "#sprintTasks");
	}
	$('.taskDetails').each( makeEditable);
	$(".submitReportButton").button( {icons: {primary: "ui-icon-gear"}, text:false}).click( submitEstimate);
}

function refreshSprintPeople()
{
	// clear all scrumboards
	$("#sprintPeople").html("");
	
	// now send the tasks to their appropriate scrumboard.
	for (var person_key in currentPeople)
	{
		addPersonToList( currentPeople[person_key], "#sprintPeople");
	}
	
//	$(".submitReportButton").button( {icons: {primary: "ui-icon-gear"}, text:false}).click( submitEstimate);

}

/**
 * This function is called when a task list (a <ul>-element) receives a note from another
 * task list through a drag-n-drop operation.
 * @param event
 * @param ui
 */
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
 * Does the person with the given resourceId currently work on the given task?
 * @param resourceId
 * @param taskInfo
 * @returns {Boolean}
 */
function worksOnTask( resourceId, taskInfo)
{
	return taskInfo.resource_id == resourceId && taskInfo.status == 'inProgress';
}




/**
 * Determine whether two DateTimes are on the same day. 
 * @param date1
 * @param date2
 * @returns {Boolean}
 */
function isOnSameDay( date1, date2)
{
	return 	date1.getFullYear() == date2.getFullYear()
		&&	date1.getMonth() 	== date2.getMonth()
		&&	date1.getDate() 	== date2.getDate();
}