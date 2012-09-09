var chartData = { dates: null, burndown:null, tantalus: null, burnup:null};

function drawBurnDown( elementname, sprint_id)
{
	var chart = new Charts.LineChart('burndown', {show_grid: true});
	var day1 = new Date(2012, 9, 1);
	var day2 = new Date(2012, 9, 5);
	var day2b = new Date(2012, 9, 10);
	var day3 = new Date(2012, 9, 20);
	
	chart.add_line({
		  data: [[day1, 100],[day2, 200],[day3, 300]]
		});

	chart.add_line({
		  data: [[day1, 100],[day2, 200],[day2b, 300]]
		});
	
	chart.draw();
	
}