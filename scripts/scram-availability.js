
var getAvailabilityUrl = "availability.php";

var times;

/**
 * Load availability data (the number of hours every engineer is available to the sprint each day) and 
 * call the given callback when the data has arrived.
 * @param sprint_id sprint for which the data must be obtained
 * @param callback 
 */
function loadAvailability( sprint_id, callback)
{
	$.getJSON( getAvailabilityUrl + '?sprint_id=' + sprint_id, callback);
}

/**
 * Create an array with the weekdays in the given sprint.
 * @param sprint
 * @returns [Date]
 */
function get_weekdays( sprint)
{
	result = new Array();
	start = Date.parse( sprint.start_date);
	end = Date.parse( sprint.end_date);
	for (;start <= end; start.setDate( start.getDate()+1))
	{
		if (start.getDay() != 0 && start.getDay() != 6)
		{
			result.push( Date.parse( start.getTime()));
		}
	}
	
	return result;
}

function formatDateHeader( date)
{
	div = $("<div />");
	$.each( date.toDateString().split(' '), function (index, value){
		$("<div/>").text( value).appendTo( div);
	});
	
	return div;
}

function createAvailabilityTable( element_id, data)
{
	times = data.times;
	var table = $("<table style='availability' />");
	var dates = getWeekdays( data.sprint);
	var header = $("<tr></tr>");
	$("<th>name</th>").appendTo( header);
	$.each( dates, function (index, date){
		td = $("<th/>");
		td.append( formatDateHeader( date));
		td.appendTo( header);
	});
	header.appendTo( table);
	$.each( data.resources, function (index, resource) {
		id = resource.resource_id;
		var row = $("<tr />");
		$("<td />").text( resource.name).appendTo( row);
		$.each( dates, function (index2, date){
			day = date.getDate();
			month = date.getMonth() + 1;
			year = date.getFullYear();
			
			key = 'k_' + id + "_" + year + '-' + month + '-' + day;
			val = (data.times[key])?(data.times[key]):"";
			td = $('<td />');
			$("<input />").attr({'id':key,'name':key,'type':'text','class':"hourCell positive-integer dailyRation", 'value': val})
//			$("<input />").data('id', key).data('type', 'text').data('style', 'hourCell positive-integer dailyRation')
				.appendTo( td);
			td.appendTo( row);
		});
		row.appendTo( table);
	});
	
	$('#' + element_id).append( table);
	
}