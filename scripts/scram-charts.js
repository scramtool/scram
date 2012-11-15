//
//  Copyright (C) 2012 Danny Havenith
//
//  Distributed under the Boost Software License, Version 1.0. (See
//  accompanying file LICENSE_1_0.txt or copy at
//  http://www.boost.org/LICENSE_1_0.txt)
//

var chartData;
var burndownUrl = 'burndown_data.php';
var useRealDates = false;

function loadCharts( sprint_id, burnDownElement, burnUpElement)
{
	$.getJSON( burndownUrl + '?sprint_id=' + sprint_id, function (data){
		chartData = data;
		drawCharts( data, burnDownElement, burnUpElement, true);
	});
}

function loadTaskCharts( sprint_id, task_id, burnDownElement, burnUpElement)
{
	$.getJSON( burndownUrl + '?sprint_id=' + sprint_id + '&task_id=' + task_id, function (data){
		drawCharts( data, burnDownElement, burnUpElement, false);
	});
}

function redrawCharts( burndownElement, burnUpElement, realDates, addAvailability)
{
	useRealDates = realDates;
	drawCharts( chartData, burndownElement, burnUpElement, addAvailability);
}

/**
 * Create an array with the weekdays in the given sprint.
 * This functon returns an array of Date-objects, one for each working day in the sprint.
 * @param sprint
 * @returns [Date]
 */
function getWeekdays( sprint)
{
	var result = new Array();
	var start = Date.parse( sprint.start_date);
	var end = Date.parse( sprint.end_date);
	for (;start <= end; start.setDate( start.getDate()+1))
	{
		if (start.getDay() != 0 && start.getDay() != 6)
		{
			result.push( new Date( start.getTime()));
		}
	}
	
	return result;
}

/**
 * Draw a chart with three lines.
 * This function is called to draw both the burn-up and the burn-down charts
 * @param element
 * @param burnDownSeries
 * @param progressionSeries
 */
function drawMultiLine( element, burnDownSeries, progressionSeries, availabilitySeries)
{
	$('#' + element).empty();
	var chart = new Charts.LineChart( element, {show_grid: true});

	// add a progression line. This is typicall a line that runs
	// through the complete timeframe of the chart (from left to right).
	chart.add_line( {
		data: progressionSeries,
		options: {
			line_color: "#00aadd",
			fill_area: false
		}
	});

	// add a burn down (or -up) line. This line runs from the start of the 
	// sprint to the current date.
	// the order is important, because we want this line on top of the progression
	// line.
	chart.add_line({
		data: burnDownSeries,
		options: {
			line_color: "#ff5500",
			dot_color: "#ff5500",
			fill_area: false
		}
	});

	// add a thinner line that indicates the available resources.
	chart.add_line( {
		data: availabilitySeries,
		options: {
			line_color: "orange",
			dot_color: "orange",
			dot_size: 2,
			fill_area: false,
			line_width: 1
		}
	});
	
	chart.draw();
}

/**
 * Calculate the number of weekdays between two dates.
 * This is the _really_ simple implementation that iterates over all dates between the two given dates and determines whether they are
 * weekdays.
 */
function weekDaysBetween( date1, date2)
{
	if (date1 > date2) {
		start = new Date( date2.getTime());
		end = date1;
	} else {
		start = new Date( date1.getTime());
		end = date2;
	}

	var counter = 0;
	while (start < end)
	{
		if (start.getDay() != 0 && start.getDay() != 6)
		{
			++counter;
		}
		start.setDate( start.getDate() + 1);
	}

	return counter;
}

/**
 * Given the burn up and -down data for the sprint, create a burn up and burn down chart.
 * This function creates four data series, two for each chart. Each data series consists of pairs (date, value).
 * Dependent on the global variable 'useRealDates' the date-part of the pairs is either a real date object or a number
 * representing the 'sprint day'. If real dates are used, the charting component used, will space the dates correctly on the 
 * horizontal axis, but that means that weekends will also be visible, which normally 'breaks' the burn down lines.
 * @param chart_data
 */
function drawCharts( chart_data, burnDownElement, burnUpElement, addAvailability)
{
	
	var burndown_data = [];
	var burnup_data = [];
	var tantalus = [];
	var total_effort = 0;
	var dayCounter = 0;
	
	var sprintStartDate = Date.parse( chart_data.sprint.start_date);
	var sprintEndDate = Date.parse( chart_data.sprint.end_date);
	var days = getWeekdays( chart_data.sprint);
	var gridDate = new Date();
	var sprintEffort = 0;
	
	// create burn down, burn up and 'tantalus' series.
	// if 'useRealDates' is switched on, the data is given as a time series (which automatically adds weekends to the
	// horizontal axis). If not, the horizontal axis represents 'sprint days', or in other words, weekdays in the sprint.
	var currentBurnup = 0;
	$.each( chart_data.burndown, function (index, report){
		gridDate = Date.parse( report.grid_date);
		if (useRealDates) {
			date = gridDate;
		}
		else {
			date = weekDaysBetween( sprintStartDate, gridDate);
		}

		total_effort =  parseFloat( report.burn_down) + parseFloat( report.burn_up);

		if (gridDate >= sprintStartDate) {
			// add a new point to the burndown line
			burndown_data.push( [ date, report.burn_down]);
			// add a new point to the tantalus line
			tantalus.push( [date, total_effort]);
			burnup_data.push( [date, report.burn_up]);
			currentBurnup = report.burn_up; // collect the latest burn up value
		}
		
		if (gridDate <= sprintStartDate ) {
			sprintEffort = total_effort;
		}
		
		++dayCounter;
	});
	
	// gridDate is now the last date for which we have a report.
	if (gridDate < sprintEndDate || tantalus.length == 1) {
		// now finish the tantalus line beyond the last report
		if (useRealDates) {
			tantalus.push( [sprintEndDate, total_effort]);
		}
		else {
			tantalus.push( [days.length - 1, total_effort]);
		}
	}
		

	// create the availability data
	var indexInDaysArray = 0;
	var cumulativeAvailability = [];
	var totalAvailability = 0;
	var lastDate;
	$.each( chart_data.availability, function (index, avail){
		lastDate = Date.parse(avail.date);
		while( lastDate > days[indexInDaysArray]) {
			cumulativeAvailability[indexInDaysArray++] = totalAvailability;
		}
		totalAvailability += parseFloat( avail.hours);
	});
	while( cumulativeAvailability.length < days.length) {
		cumulativeAvailability.push( totalAvailability);
	}
	
	// create the 'ideal' burndown line and the critical burn up and -down line.
	var progression = [];
	var availabilityProgression = [];
	var availabilityProjection = [];
	var total = sprintEffort;
	var fraction = total/(days.length -1);
	var dayCounter = 0;
	$.each( days, function (index, realDay){
		// decide whether we use sprint days or real days
		if (!useRealDates) {
			day = dayCounter;
		}
		else {
			day = realDay;
		}
		
		if (addAvailability) {

			availabilityProgression.push([day, totalAvailability - cumulativeAvailability[dayCounter]]);
			availabilityProjection.push( [day, total_effort - totalAvailability + cumulativeAvailability[dayCounter]]);
		}
		
		progression.push( [day, (total>0)?Math.floor( total):0]);
		total -= fraction;
		++dayCounter;
	});
	
	
	if (burnDownElement != null) {
		drawMultiLine( burnDownElement, burndown_data, progression, availabilityProgression);
	}
	
	if (burnUpElement != null) {
		drawMultiLine( burnUpElement, burnup_data, tantalus, availabilityProjection);
	}
}