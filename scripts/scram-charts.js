var chartData;
var burndownUrl = 'burndown_data.php';
var useRealDates = false;

function loadCharts( sprint_id)
{
	$.getJSON( burndownUrl + '?sprint_id=' + sprint_id, function (data){
		chartData = data;
		drawCharts( data);
	});
}

/**
 * Create an array with the weekdays in the given sprint.
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
			result.push( Date.parse( start.getTime()));
		}
	}
	
	return result;
}

function drawMultiLine( element, burnDownSeries, progressionSeries)
{
	$('#' + element).empty();
	var chart = new Charts.LineChart( element, {show_grid: true});

	chart.add_line({
		data: burnDownSeries,
		options: {
			line_color: "#ff5500",
			dot_color: "#ff5500",
			fill_area: false
		}
	});

	chart.add_line( {
		data: progressionSeries,
		options: {
			line_color: "#00aadd",
			fill_area: false
		}
	});
	
	chart.draw();
}

/**
 * Given the burn up and -down data for the sprint, create the standard scrum graphs.
 * @param chart_data
 */
function drawCharts( chart_data)
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
	// if 'includeWeekends' is switched on the data is given as a time series (which automatically adds weekends to the
	// horizontal axis). If not, the horizontal axis represents 'sprint days', or in other words, weekdays in the sprint.
	$.each( chart_data.burndown, function (index, report){
		gridDate = Date.parse( report.grid_date);
		if (useRealDates) {
			date = gridDate;
		}
		else {
			date = dayCounter;
		}

		total_effort =  parseFloat( report.burn_down) + parseFloat( report.burn_up);

		if (gridDate >= sprintStartDate) {
			// add a new point to the burndown line
			burndown_data.push( [ date, report.burn_down]);
			// add a new point to the tantalus line
			tantalus.push( [date, total_effort]);
			burnup_data.push( [date, report.burn_up]);
		}
		
		if (gridDate <= sprintStartDate ) {
			sprintEffort = total_effort;
		}
		
		++dayCounter;
	});
	
	// gridDate is now the last date for which we have a report.
	if (gridDate < sprintEndDate) {
		// now finish the tantalus line beyond the last report
		if (useRealDates) {
			tantalus.push( [sprintEndDate, total_effort]);
		}
		else {
			tantalus.push( [days.length - 1, total_effort]);
		}
	}
		

	// create the 'ideal' burndown line.
	progression = [];
	total = sprintEffort;
	fraction = total/(days.length -1);
	dayCounter = 0;
	$.each( days, function (index, day){
		if (!useRealDates) {
			day = dayCounter;
		}
		progression.push( [day, (total>0)?Math.floor( total):0]);
		total -= fraction;
		++dayCounter;
	});
	
	
	
	drawMultiLine( 'burndown', burndown_data, progression);
	drawMultiLine( 'burnup', burnup_data, tantalus);
}