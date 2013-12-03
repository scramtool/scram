//
//  Copyright (C) 2012,2013 Danny Havenith
//
//  Distributed under the Boost Software License, Version 1.0. (See
//  accompanying file LICENSE_1_0.txt or copy at
//  http://www.boost.org/LICENSE_1_0.txt)
//

var availabilityUrl = "availability.php";

var times;

/**
 * Load availability data (the number of hours every engineer is available to the sprint each day) and 
 * call the given callback when the data has arrived.
 * @param sprint_id sprint for which the data must be obtained
 * @param callback 
 */
function loadAvailability( sprint_id, callback)
{
	$.getJSON( availabilityUrl + '?action=get&sprint_id=' + sprint_id, callback);
}

/**
 * Create an array with the weekdays in the given sprint.
 * @param sprint
 * @returns [Date]
 */
function get_weekdays( sprint)
{
	var result = new Array();
	var start = Date.parse( sprint.start_date);
	var end = Date.parse( sprint.end_date);
	for (;start <= end; start.setDate( start.getDate()+1))
	{
		if (start.getDay() != 0 && start.getDay() != 6)
		{
			result.push( Date.parse( start.getTime()));
		}
	}
	
	return result;
}

/**
 * Format the column header, which contains the date. The date is split into 3 divs, so that
 * the table doesn't get too wide.
 * @param date - date to create a text representation of
 * @returns
 */
function formatDateHeader( date)
{
	var div = $("<div />");
	$.each( date.toDateString().split(' '), function (index, value){
		$("<div/>").text( value).appendTo( div);
	});
	
	return div;
}


function updateTotal( container)
{
	var total = 0;
	container.find('[data-scram-rowtotal]').each( function (index, element){
		var val = parseInt( $(element).html());
		if (!isNaN(val)) {
			total += val;
		}
	});
	container.find('[data-scram-total]').html( total);
}

function updateRowTotal( container, rownr) 
{
	var total = 0;
	container.find('[data-scram-row="'+rownr+'"]').each( function (index, element){
		var val = parseInt( element.value);
		if (!isNaN(val)) {
			total += val;
		}
	});
	container.find('[data-scram-rowtotal="'+rownr+'"]').html( total);
	updateTotal( container);
}

function updateColumnTotal( container, colnr) 
{
	var total = 0;
	container.find('[data-scram-column="'+colnr+'"]').each( function (index, element){
		var val = parseInt( element.value);
		if (!isNaN(val)) {
			total += val;
		}
	});
	container.find('[data-scram-columntotal="'+colnr+'"]').html( total);
}

/**
 * This function does almost exactly the same as the changeMarkup() function in 
 * scram.js: whenever a value in an input differs from its original value, it will get an
 * additional style ('changed') to mark it so.
 * 
 * The difference between the two functions is that this one uses the value property/atrribute
 * difference to determine the change, while changeMarkup() uses hidden fields with the original value.
 * 
 */
function tableCellChangeMarkup()
{
	var oldValue = $(this).attr('value');
	var newValue = $(this).prop('value');

	if (oldValue != newValue) {
		$(this).addClass('changed');
	}
	else {
		$(this).removeClass( 'changed');
	}
	
	var table = $(this).closest("table");
	var row = $(this).attr('data-scram-row');
	var col = $(this).attr('data-scram-column');
	updateRowTotal( table, row);
	updateColumnTotal( table, col);
}

/**
 * Create a table with a row for each developer and a column for each working day in the sprint
 * where each cell specifies how many hours the developer will be available on that day.
 * @param element_id the element that will receive the table.
 * @param data data structure that contains an array of available hours for each developer.
 */
function createAvailabilityTable( sprint_id, element_id, data)
{
	times = data.times;
	var table = $("<table style='availability' />");
	var dates = getWeekdays( data.sprint);
	var header = $("<tr></tr>");
	var colTotals = $("<tr></tr>");
	$("<th>name</th><th>total</th>").appendTo( header);
	$("<td>&nbsp;</td><td data-scram-total>&nbsp;</td>").appendTo( colTotals);
	var colnr = 0;
	$.each( dates, function (index, date){
		var td = $("<th/>");
		td.append( formatDateHeader( date));
		td.appendTo( header);
		$("<td class='estimate frozen'>0</td>").attr('data-scram-columntotal', colnr).appendTo( colTotals);
		++colnr;
	});
	header.appendTo( table);
	var rownr = 0;
	$.each( data.resources, function (index, resource) {
		var id = resource.resource_id;
		var row = $("<tr />");
		$("<td />").text( resource.name).appendTo( row);
		$("<td/>").text('0').attr( {'data-scram-rowtotal': rownr, 'class':'rowTotal estimate frozen'}).appendTo( row);
		var colnr = 0;
		$.each( dates, function (index2, date){
			var day = date.getDate();
			var month = date.getMonth() + 1;
			var year = date.getFullYear();
			
			var key = 'k_' + id + "_" + year + '-' + month + '-' + day;
			var val = parseInt(data['times'][key])?(data['times'][key]):"";
			var td = $('<td />').css('text-align', 'right');
			$("<input />").attr({'data-scram-row': rownr, 'data-scram-column': colnr, 'id':key,'name':key,'type':'text','class':"hourCell positive-integer dailyRation show-internal-changes", 'value': val})
				.appendTo( td);
			td.appendTo( row);
			++colnr;
		});
		row.appendTo( table);
		++rownr;
	});
	colTotals.appendTo( table);

	for (var rowCounter = 0; rowCounter < rownr; ++rowCounter) updateRowTotal( table, rowCounter);
	for (var colCounter = 0; colCounter < colnr; ++colCounter) updateColumnTotal( table, colCounter);
	
	var form = $('<form />').attr( { 'method':'POST', 'action':availabilityUrl + '?action=error'});
	form.append(table);
	$('<input />').attr( {'type':'submit', 'value':'Submit'}).appendTo( form);
	form.submit( function (event){
		event.preventDefault();
		
		$(this).children().filter(":input[type='submit']").attr('disabled','disabled');
		var compressed= {'sprint_id': sprint_id, 'action':'post'};
		
		// collect all hourCell values that were changed.
		$('.hourCell').filter('.changed').each( function (index) {
			var value = $(this).prop( 'value');
			var key = $(this).attr( 'id');
			compressed[ key] = value; 
		});
		
		$.post( availabilityUrl, compressed, function (newData) {
			createAvailabilityTable( sprint_id, element_id, newData);},
			'json');
	});
	$('#' + element_id).html('');
	$('#' + element_id).append( form);
	$('.show-internal-changes').change(tableCellChangeMarkup);
}