<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<?php
	$sprint_id = $_GET['sprint_id']; 
?>
<html>
<head>
<script src="http://api.simile-widgets.org/timeplot/1.1/timeplot-api.js" 
       type="text/javascript"></script>
<script type="text/javascript">
var timeplot;

function onLoad() {
	  var eventSource = new Timeplot.DefaultEventSource();
	  var plotInfo = [
	                  Timeplot.createPlotInfo({
	                    id: "plot1",
	                    dataSource: new Timeplot.ColumnSource(eventSource,1),
	                    valueGeometry: new Timeplot.DefaultValueGeometry({
	                      gridColor: "#000000",
	                      axisLabelsPlacement: "left",
	                    }),
	                    timeGeometry: new Timeplot.DefaultTimeGeometry({
	                      gridColor: "#000000",
	                      axisLabelsPlacement: "top"
	                    }),
	                    lineColor : "#ff0000",
	                    showValues : true
	                  })
	                ];	  
	  timeplot = Timeplot.create(document.getElementById("burndown-chart"), plotInfo);
	  timeplot.loadText("burndown_data.php?sprint_id=<?=$sprint_id;?>", ",", eventSource);
	}

var resizeTimerID = null;
function onResize() {
    if (resizeTimerID == null) {
        resizeTimerID = window.setTimeout(function() {
            resizeTimerID = null;
            timeplot.repaint();
        }, 100);
    }
}
</script>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Sprint burndown</title>
</head>
<body onload="onLoad();">
<div id="burndown-chart" style="height: 500px;"></div>    
    <?php

	?>
</body>
</html>