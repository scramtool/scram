//
//  Copyright (C) 2012, 2013 Danny Havenith
//
//  Distributed under the Boost Software License, Version 1.0. (See
//  accompanying file LICENSE_1_0.txt or copy at
//  http://www.boost.org/LICENSE_1_0.txt)
//

var taskListUrl = 'task_data.php';
var peopleListUrl = 'people_data.php';
var changeTextUrl = 'change_task_text.php';
var sprintDetailsUrl = 'sprint_data.php';
var logUrl = 'log_data.php';

var placeholderCounter = 0;

/**
 * An associative container that holds task_id->task pairs.
 * Because task_ids are numerical, they get prepended with 'x' so that javascript
 * turns this array into a sparse map.
 */
var currentTasks = new Array();


/**
 * Get the task with the given id from the global array currentTasks.
 * This function encodes the custom to prepend the task id with 'x' in the
 * array, so that the key is interepreted as a string and not a numerical index.
 * @param id
 * @returns
 */
function getTask( id)
{
	return currentTasks['x'+id];
}

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

/**
 * Set up the task table for the sprint details page.
 * @param sprint_id
 * @param table_name
 */
function loadTaskTable( sprint_id, table_name) {
	$('#' + table_name).dataTable( {
		"bProcessing": true,
		"sScrollY": 400,
//		"bScrollCollapse": true,
		"bJQueryUI": true,
		"bPaginate": false,
		"bAutoWidth":true,
		"sAjaxSource": taskListUrl + '?action=table&sprint_id=' + sprint_id,
		"aoColumnDefs": [
		                 { "bVisible": false, "aTargets": [ 0,1,4,5 ] },
		                 { "sWidth":"10%", "aTargets": [ -1 ] }
		               ]
	} );	
	$(window).bind('resize', function () {
		$('#' + table_name).dataTable().fnAdjustColumnSizing();
	  } );
}

/**
 * Toggle between showing all tasks and showing only the tasks of the
 * current user.
 * @param showAll
 */
function filterTasks( showAll)
{
	if (showAll) {
		$('.someoneElses, .inProgressBox').fadeIn( 500);
	}
	else {
		$('.someoneElses, .inProgressBox').fadeOut( 500);
	}
}

function loadLogs( sprint, callback)
{
	$.getJSON( logUrl + '?sprint_id=' + sprint, callback);
}

function refreshLogs( data)
{
	var personTemplate = $('#personTemplate');
	personTemplate.hide();
	var lastPerson = "";
	var currentPersonMarkup = personTemplate;
	$.each( data, function (index, log) {
		if (log.name != lastPerson) {
			var previousPersonMarkup = currentPersonMarkup;
			currentPersonMarkup = personTemplate.clone();
			currentPersonMarkup.insertAfter( previousPersonMarkup);
			lastPerson = log.name;
			currentPeronsMarkup.show();
		}
		
	});
}

/**
 * Load the data for all people involved in a sprint and call the callback when finished.
 * @param sprint
 * @param callback
 */
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

/**
 * Load the details for a sprint, such as the sprint name, the start date and the end date.
 * @param sprint
 * @param callback
 */
function loadSprintDetails( sprint, callback)
{
	$.getJSON( sprintDetailsUrl + '?action=get&sprint_id=' + sprint, callback);	
}

/**
 * Show the details of the given sprint in an element with id "sprintDetails".
 * @param sprint
 */
function refreshSprintDetails( sprint)
{
	$("#sprintDetails").html( "<h2>" + sprint.description + "</h2>Start date:" + sprint.start_date + " End date: " + sprint.end_date);
}

/**
 * Submit a form with data for a new task to the server.
 * If the server accepts the data, a new task object will be asynchronously returned. This task object 
 * is added to the global array currentTasks and then given to the callback parameter.
 * 
 * Note that the form from which the data is submitted should have inputs with
 * exactly the right names.
 * 
 * @param form
 */
function submitNewTaskForm(form, callback)
{
	var query = "";
	
	var task = new Array();
	form.find("input, textarea").each( 
			function(index, element) {
				value =  encodeURIComponent( element.value);
				name = element.name;
				query += '&' + name + "=" + value;
				task[name] = element.value;
				});
	
	if (!task.estimate) task.estimate = 8;
	if (task.description && task.description.length != 0)
	{
		query += '&sprint_id=' + sprint_id;
		$.getJSON( taskListUrl + '?action=add' + query, function (task) {
				currentTasks[ 'x'+task.task_id] = task;
				if (callback) {
					callback(task);
				}
			}
		);
	}
}

/**
 * This function is called when the submit new task-button is pressed in the sprint details page. 
 * $(this) references the button.
 * The button is supposed to be in a form. This function will retreive all values in that form and create an
 * ajax GET-request that should add the task to the database.
 * 
 * Beware: this function makes quite a few assumptions about the form that holds the submit-button.
 * @returns {Boolean}
 */
function submitNewTask( )
{
	submitNewTaskForm( $(this).parent(), 
			function (task)
			{
				var table = $('#taskTable').dataTable();
				table.fnAddData([
				                 task.task_id, task.sprint_id, 
				                 task.description, task.status,
				                 task.resource_id, "",task.name,
				                 0, task.estimate, task.estimate, task.estimate
				                 ]);
			});

	// clear the form and bring the cursor to the first input.
	$('form input').val("");
	$('form #estimate').val("8");
	$('.firstToFocus').focus();
	
	return false;

}


/**
 * Submit the estimates that have been filled in by the user.
 * This will result in a new report in the database on the server side.
 * @returns {Boolean}
 */
function submitEstimate( )
{
	var query = "";
	$(this).parent().find("input").each( function(index, element) {query += '&' + element.name + "=" + element.value;});
	$(this).replaceWith( '<img class="centered" src="images/ajax-loader.gif"/>');
	$.getJSON( taskListUrl + '?action=report' + query,
			function (task)
			{
				currentTasks['x'+task.task_id] = task;
				$("#container-for-" + task.task_id).html( makeTaskMarkup( task, true));
				setAdvancedUIBehaviour();
			});
	return false;
}

/**
 * submit a change in the a tasks text to the server side.
 * This function returns the markup for a wait-icon and returns before the request is completed. After the request completes
 * an element with name 'description-for-<n>' (with <n> the task id) will get updated with the accepted task text.
 * @param value
 * @param settings
 * @returns {String} a temporary content for the task text element (a wait animation)
 */
function submitText( value, settings)
{
     //console.log(settings.submitdata.task_id);
     $.getJSON( changeTextUrl + '?id=' + settings.submitdata.task_id + '&text=' + encodeURIComponent(value),
			function (taskText)
			{
    	 		currentTasks['x'+taskText.task_id].description = taskText.text;
				$("#description-for-" + taskText.task_id).html( taskText.text);
			});
     
     //return settings.submitdata.task_id + ' : ' + value;
     return '<img class="centered" src="images/ajax-loader.gif"/>';
}

/**
 * Take a task details item and make it editable, by calling the editable function (from jquery.jeditable.js).
 * @param index
 * @param value
 */
function makeEditable( index, value)
{
	var task_id = $(value).attr('id').substring(16);
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
 * Given a container name (the name of a <li> item for a task), extract the task id.
 * This function assumes a specific format of the container name, such as 
 * 'container-for-<task_id>'.
 * @param containerId
 * @returns numerical task id.
 */
function containerIdToTaskId( containerId)
{
	// assume the container has an id of the form
	// 'container-for-<task_id>'. Which means that if we cut of the first 14
	// characters, we get the task id.
	return containerId.substring(14);
}

/**
 * Create the markup for a task detail form.
 * At some point I should probably merge this code with that of makeTaskMarkup.
 * 
 * @param task
 * @returns {String} html for the task form.
 */
function makeTaskFormMarkup( task)
{
	var today = new Date();
	var report_date = Date.parse( task.report_date);
	var today_string = today.toString( "yyyy-MM-dd");

	if (isOnSameDay( report_date, today))
	{
		burnt = task.burnt;
	}
	else
	{
		burnt = 0;
	}
	
	var html = 
'	<form id="form-for-' + task.id + '">'+
'	<div id="taskForm" class="yellowNote">'+
'	<div class="taskNumbers">'+
'	<div>'+
'	    <input type="text" size="20" id="member_name" name="member_name" class="memberName" value="' + task.name+ '"/>'+
'	</div>'+
'	<div>'+
'	    <input type="hidden" id="sprint_id" name="sprint_id" value="' + sprint_id+ '">'+
'	    <input type="hidden" id="status" name="status" value="' + task.status + '">'+
'	    <input type="hidden" id="id" name = "id" value="' + task.task_id+ '">'+
'	    <input type="hidden" id="estimate-original" name = "estimate-original" value="' + task.estimate+ '"/>'+
'	    <input type="hidden" id="spent-original" name = "spent-original" value="' + burnt+ '"/>'+
'       <input type="hidden" name="ref_date" id="ref-date" value="' + today_string + '"/>' + 
'	    <label for="estimate">left:</label>'+
'	    <input type="text" id="estimate" name = "estimate" class="estimate positive-integer show-changes" value="' + task.estimate+ '">'+
'	    <label for="spent">&nbsp;spent today:</label>'+
'	    <input type="text" id="spent" name="spent" class="estimate positive-integer show-changes" value="' + burnt+ '">'+
'	</div>'+
'	</div>'+
'	<textarea cols="40" class="taskDescription" id="description" name="description" >' + task.description + '</textarea>'+
'	<br style="clear:both" />'+
'	</div>'+
'	</form>'
;
	return html;
}

/**
 * This function takes a task object fresh from the server and gives its html rendering
 * a place in one of the sections on the task board.
 * @param task
 */
function updateTask(task)
{
	currentTasks['x'+task.task_id] = task;
	
	// can we find the <li>-item that should contain the task, if not create one
	var container = $("#container-for-" + task.task_id);
	if (container.length != 0)
	{
		container.html( makeTaskMarkup( task, isTaskEstimateable(member_id, task)));
	}
	else {
		container = createTaskListItem( task);
	}
	
	// determine in which section to place the <li>
	var target = task.status + "List";
	if (worksOnTask(member_id, task)) target = "myTasks";
	
	// if the task is not in the right container, we move it there
	if (container.parent("#" + target).length == 0) {
		container.prependTo( "#" + target);				
	}
	setAdvancedUIBehaviour();
}

function postTaskUpdates( form)
{
	var postData = form.serialize();
	$.post( taskListUrl + "?action=update", postData, updateTask, "json");
}

function showTaskDialog()
{
	var id = containerIdToTaskId($(this).attr('id'));
	var taskForm = $(makeTaskFormMarkup(getTask(id)));
	taskForm.append('<div class="smallChart" style="width:400px;height:200px" id="taskBurnDown" ></div>');
	
	
	if (typeof theModalDialog == 'undefined') {
		theModalDialog = $("<div />");
	}
	theModalDialog.html(taskForm);
	theModalDialog.dialog({
		modal : true,
		width : 512,
		height: 500,
		buttons: [{ text:"OK", click: function (){
			postTaskUpdates( $(this).find('form'));
			$(this).dialog( 'close');
			}}]
	});
	
	// select all when entering a form input
	theModalDialog.find('textarea, input').focus( function (){$(this).select();});
	// focus on the first field.
	theModalDialog.find('#member_name').focus();
	
	setAdvancedUIBehaviour();
	loadTaskCharts( sprint_id, id, null, 'taskBurnDown');

	return true;
}

function createEmptyTask()
{
	var task = {
			"sprint_id": sprint_id, 
			"description":"",
			"status":"",
			"resource_id":member_id,
			"story":"",
			"name": member_name,
			"estimate":"0",
			"burnt":"0"
		};
	return task;
}

/**
 * Create a dependency between two numerical inputs where if the value of one ('destination') changes, 
 * its delta will be subtracted from the other ('source'). If source changes, those changes will not influence 
 * 'destination'. 
 * @param source
 * @param destination
 */
function siphon( source, destination)
{
	// this makes use of the fact that the attribute 'value' contains the original value while the 
	// _property_ 'value' contains the new value.
	destination.change( function(){
		var oldValue = $(this).attr('value');
		var newValue = $(this).prop('value');
		if (newValue != oldValue) {
			var delta = newValue - oldValue;
			destination.attr('value', newValue);
			source.prop( 'value', Math.max( 0, source.prop('value') - delta));
			source.change();
		};
	});
}

function showSplitTaskDialog()
{
	var id = containerIdToTaskId($(this).attr('id'));
	var sourceTask = getTask( id);
	var destinationTask = createEmptyTask();
	destinationTask.status = sourceTask.status;
	destinationTask.description = sourceTask.description;
	
	var sourceTaskDiv = $('<div/>').addClass('sideBySideTask').html( makeTaskFormMarkup( sourceTask));
	var destinationTaskDiv = $('<div/>').addClass('sideBySideTask').html(makeTaskFormMarkup( destinationTask));
	
	var dialog = $('<div/>').append( sourceTaskDiv).append( destinationTaskDiv);
	dialog.dialog({
		modal : true,
		width : 920,
		height: 240,
		buttons: [
		          { 
		        	  text:"OK", 
		        	  click: function () {
		        		  postTaskUpdates( sourceTaskDiv.find('form'));
		        		  submitNewTaskForm( destinationTaskDiv.find('form'), updateTask);
		        		  $(this).dialog( 'close');
		        	  }
		          }
		          ]
	});	
	//destinationTaskDiv.toggle('slide');
	dialog.find('textarea, input').focus( function (){$(this).select();});
	destinationTaskDiv.find('#member_name').focus();
	siphon( sourceTaskDiv.find('#estimate'), destinationTaskDiv.find('#estimate'));
	setAdvancedUIBehaviour();
}

function menuClicked( key, options)
{
	var obj_id = "#" + $(this).attr('id');
	alert( 'clicked:' + key + ' on:' + obj_id);
}

/**
 * This function is called when the user selects one of the "Move to" menu items.
 * @param key the name of the new state
 * @param options unused
 */
function moveItem( key, options)
{
	var task_id = containerIdToTaskId( $(this).attr('id'));
	var newValues = {'task_id':task_id, 'status': key};
	submitTaskChanges( newValues, true);
}

function addMenus()
{
	$.contextMenu({
		selector : '.taskNote',
		callback : menuClicked,
		items: {
			"move": { 
				"name": "Move to",
				"items": {
					"toDo": {name: "To Do", callback: moveItem},
					"inProgress": { name: "In Progress", callback: moveItem},
					"toBeVerified": { name: "To Be Verified", callback: moveItem},
					"done" : { name: "Done", callback: moveItem},
					"forwarded" : { name: "Forwarded", callback: moveItem}
				
				},
				"icon": "paste"
			},
			"split" : { name: "Split", callback : showSplitTaskDialog},
			"details": { name: "Details", callback: showTaskDialog }
		
		}
	});
	
	$.contextMenu({
		selector: '.categoryContent',
		callback : menuClicked,
		items: {
			'newTask' : { name: 'New Task'}
		}
	});
}

/**
 * Set up advanced ui behaviour. This is behaviour that can't be reached with stylesheets alone and that need some
 * extra javascript to set up. It is safe to call this function multiple times on a page.
 * This function searches the page for elements with particular classes and attaches the required behavior. Currently
 * supported behaviour-classes are :
 * 
 * submitReportButton: a small button with icon and no text, inside a task div that will call submitEstimate() when pressed
 * taskDetails:        a div that becomes editable when double-clicked
 * positive-integer:   a text input that only allows positive integers to be entered.
 * show-changes		   an input that will add the class 'changed' when its value differs from the original value.
 */
function setAdvancedUIBehaviour()
{
	$(".submitReportButton").button( {icons: {primary: "ui-icon-disk"}, text:false}).unbind('click').click( submitEstimate);
	$('.taskDetails').each( makeEditable); 
	$(".positive-integer").numeric({ decimal: false, negative: false }, function() { 
		alert("Positive integers only"); this.value = ""; this.focus(); 
		});
	$( ".memberName" ).autocomplete({
		source: "names.php",
		autoFocus: true,
		minLength: 1
		});	
	
	addMenus();
	$(".show-changes").change(changedMarkup);
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
			var list = '#' + task.status + 'List';
			addTaskToList(task, list);
		}
	}

	// set up drag-n-drop between the tasklists.
	$(".taskList").sortable({'connectWith':'.taskList', receive: noteReceived});
	setAdvancedUIBehaviour();
}


/**
 * Add a representation of a person to a list with the given element id.
 * This will create a "purple note" and add it to the given list.
 * @param person
 * @param listName
 */
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
 * This creates a "yellow note" with information about the task.
 * @param task The task to display
 * @param isInWorkList Whether the task is displayed in the current work list (this means that a form for estimates will be shown)
 * @returns
 */
function makeTaskMarkup( task, isInWorkList)
{
	var note_class = "note yellowNote";
	
	// the 'title bar' of the task, including the estimate number and the name of the assignee
	var reported_time = $('<div class="clickable estimate frozen">' + task.estimate + '</div><div>&nbsp;&nbsp;&nbsp;</div><div>' + task.name+ '</div>');
	
	// create the complete 'title bar' with header and sliding form
	var title_bar = $('<div class="taskNumbers"/>').append( reported_time);
	
	// if this is a task of the current user and it it either in progress or to be verified, add
	// a form to allow submitting new estimates.
	if (isInWorkList) {
		var today = new Date();
		var report_date = Date.parse( task.report_date);
		var today_string = today.toString( "yyyy-MM-dd");

		// form to fill in estimate and hours spent, all enclosed in a div
		var report_form = $(
			'<div class="overlay">' +
			'<form class = "taskNumberForm">'+
			' <input type="hidden" class="holdsTaskId" name="task_id" value="' + task.task_id + '"/>' +
			' <input type="hidden" name="ref_date" id="ref-date" value="' + today_string + '"/>' + 
			' <input type="hidden" id="estimate-original" value="' + task.estimate + '"/>' +
			' <input type="hidden" id="spent-original" value="0"/>' +
			' <label for="estimate">left:</label><div class="holdClick"><input type="text"  id="estimate" name="estimate" class="estimate positive-integer show-changes" value="' + task.estimate + '"/> </div>'+
			' <label for="spent">spent:</label><div class="holdClick"> <input type="text" id="spent" name="spent" class="estimate positive-integer show-changes" value="0"/> </div>'+
			' <button class="submitReportButton" style="margin: 2px 4px;">Submit Todays numbers</button>'+
			'</form>'+
			'</div>'
			);
		
		// make sure that clicking in the tasknumber fields doesn't slide the whole form out of view.
		report_form.find('.holdClick').click( function(e){ 
			e.stopPropagation(); 
			return false;
			});
		

		if (isOnSameDay( report_date, today))
		{
			report_form.find('#spent, #spent-original').val( task.burnt);
			report_form.hide();
		}	

		title_bar
			.append( report_form)
			.css( 'cursor', 'pointer')
			.on('click', function() {
					$(this).children('.overlay').toggle( 'slide', {direction:"up"}, 200);
				});
	}
	
	
	if (task.resource_id == member_id)
	{
		note_class = "note greenNote";
	}
	else
	{
		note_class = "note yellowNote";
		if (task.name) {
			note_class = note_class + " someoneElses";
		}
	}
	
	var markup = $('<div class="' + note_class + '"/>').append( title_bar).append('<div id="description-for-' 
	+ task.task_id + '" class="taskDetails">' + task.description  +'</div>');
	
	markup.children('.taskNumbers').append('<br style="clear:both" />');
		
	
	return markup;
}

/**
 * This handler function is fired for selected input elements after they have changed.
 * It assumes there is a sibling (hidden) element with name "<id of this element>-original".
 * If the value of that element is different than the source element, the source element
 * will get the additional style 'changed'. If the values are the same, the 'changed' style will be removed
 * if present.
 * 
 * It would have been possible to compare the value attribute with the value property instead of relying
 * on an additional hidden input, but that won't work with some older IE browsers.
 */
function changedMarkup() 
{
	// find the input with id '<source element id>-original'
	var originalId = '#' + $(this).prop('id') + '-original';
	var originalInput = $(this).siblings( originalId);
	
	// get the original value and the new value
	var original = originalInput.val();
	var current = $(this).val();
	
	// apply the 'changed' style, if applicable.
	if ( original == current) {
		$(this).removeClass('changed');
	} else {
		$(this).addClass('changed');
	}
}

/**
 * Create a select option for the task status. But don't show the option
 * if the current status euqls the option value.
 * @param task, option
 * @returns
 */
function makeStatusOption( task, option)
{
	return '<option value="' + option + '" ' + (task.status==option?"selected":"") + '>' + option + '</option>';
}

/**
 * create a placeholder for a newly added task.
 * This placeholder will be shown in the UI while the add-request is sent to the server.
 * @param task
 * @returns
 */
function makeTaskPlaceholder( task)
{
	var reported_time = '<div class="clickable estimate frozen">' + task.estimate + '</div><div>&nbsp;&nbsp;&nbsp;</div><div>' + task.name+ '&nbsp;<img class="centered" src="images/ajax-loader.gif"/></div>';
	var html = '<div class="yellowNote"><div class="taskNumbers">'+ reported_time + '</div><div id="description-for-' + task.task_id + '" class="taskDetails">'+ task.description  +'</div></div>';
	return html;
}

/**
 * Create html markup to represent a person (a "purple note").
 * @param person
 * @param listName
 * @returns {String}
 */
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

/**
 * Create a <li> element that will hold the html markup for a task. The created element will have id 'container-for-<n>' with <n> a task id.
 * @param task
 * @returns
 */
function createTaskListItem( task)
{
	return $('<li/>', 
			{
				'class': 'taskNote', 
				'id':'container-for-' + task.task_id, 
				'html':makeTaskMarkup( task, isTaskEstimateable(member_id, task))
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
	
	setAdvancedUIBehaviour();
}

function refreshSprintPeople()
{
	// clear the people list
	$("#sprintPeople").html("");
	
	// now add each person to the people list
	for (var person_key in currentPeople)
	{
		addPersonToList( currentPeople[person_key], "#sprintPeople");
	}
	
}

/**
 * kind-of generic function to deal with changes in a task.
 * @param newValues
 * @param allowMove
 */
function submitTaskChanges( newValues, allowMove) {
	var task_id = newValues.task_id;
	
	// try to find out if estimates have changed as well, in which case we need to submit those changes
	// together with the move.
	var changedSelector = '#container-for-'+task_id+' .changed';
	var changedEstimates = $( changedSelector).length;
	$(changedSelector).removeClass('changed'); // un-mark as changed.
	
	// if estimates were changed, we also need to upload the new estimates.
	if (changedEstimates) {
		newValues.spent = $('#container-for-'+task_id+' #spent').val();
		newValues.estimate = $('#container-for-'+task_id+' #estimate').val();
		newValues.ref_date = $('#container-for-'+task_id+' #ref-date').val();
	}

	var query = '?action=move&' + $.param( newValues);
	$('#description-for-' + task_id).html('<img class="centered" src="images/ajax-loader.gif"/>');
	$.getJSON( taskListUrl + query,
			function (task)
			{
				currentTasks['x'+task.task_id] = task;
				$("#container-for-" + task.task_id).html( makeTaskMarkup( task, isTaskEstimateable(member_id, task)));
				var target = newValues.status + "List";
				if (worksOnTask(member_id, task)) target = "myTasks"; 
				if (allowMove) $("#container-for-" + task.task_id).prependTo( "#" + target);				
				setAdvancedUIBehaviour();
			});
}

/**
 * This function is called when a task list (a <ul>-element) receives a note from another
 * task list through a drag-n-drop operation.
 * @param event
 * @param ui
 */
function noteReceived( event, ui)
{
	var task_id = $(ui.item).attr("id").replace('container-for-','');
	var new_status = $(this).attr("id").replace('List', '');
	var old_status = $(ui.sender).attr("id").replace('List','');
	var to_mytasks = false;
	
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
		var newValues = {'task_id': task_id, 'status': new_status};
		if (to_mytasks && currentTasks['x'+task_id].resource_id != member_id)
		{
			newValues.owner = member_id;
		}
		submitTaskChanges( newValues, false);
	}
}

function isTaskEstimateable( resourceId, taskInfo)
{
	return taskInfo.resource_id == resourceId && 
		((taskInfo.status == 'inProgress') || taskInfo.status == 'toBeVerified');
}

/**
 * Does the person with the given resourceId currently work on the given task?
 * @param resourceId
 * @param taskInfo
 * @returns {Boolean}
 */
function worksOnTask( resourceId, taskInfo)
{
	return taskInfo.resource_id == resourceId && 
	    (taskInfo.status == 'inProgress');
}

/**
 * Determine whether two DateTimes are on the same day. 
 * @param date1
 * @param date2
 * @returns {Boolean}
 */
function isOnSameDay( date1, date2)
{
	return date1 && date2 && date1.getFullYear() == date2.getFullYear()
		&&	date1.getMonth() 	== date2.getMonth()
		&&	date1.getDate() 	== date2.getDate();
}