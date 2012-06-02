<?php 
require_once 'connect_db.inc.php';

$member_id = -1;
$member_name = "Somebody I Don't Know";
$need_identification = true;
if (isset( $_GET['member_name']))
{
	setcookie( 'scram_team_member_name', $_GET['member_name']);
	$member_name = $_GET['member_name'];
	$need_identification = false;
}

if ($need_identification && isset($_COOKIE['scram_team_member_name']))
{
	$member_name = $_COOKIE['scram_team_member_name'];
	$need_identification = false;
}

$member_name_db = $database->escape($member_name);
$member = $database->get_single_result( "select resource_id from resource where name = '$member_name_db'");
$member_id = $member['resource_id'];
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.9.0/build/fonts/fonts-min.css" />
<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.9.0/build/calendar/assets/skins/sam/calendar.css" />
<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.9.0/build/datatable/assets/skins/sam/datatable.css" />
<script type="text/javascript" src="http://yui.yahooapis.com/2.9.0/build/yahoo-dom-event/yahoo-dom-event.js"></script>
<script type="text/javascript" src="http://yui.yahooapis.com/2.9.0/build/calendar/calendar-min.js"></script>
<script type="text/javascript" src="http://yui.yahooapis.com/2.9.0/build/element/element-min.js"></script>
<script type="text/javascript" src="http://yui.yahooapis.com/2.9.0/build/datasource/datasource-min.js"></script>
<script type="text/javascript" src="http://yui.yahooapis.com/2.9.0/build/event-delegate/event-delegate-min.js"></script>

<script type="text/javascript" src="http://yui.yahooapis.com/2.9.0/build/datatable/datatable-min.js"></script>
<script type="text/javascript" src="http://yui.yahooapis.com/2.9.0/build/datatable/datatable.js"></script>
<link href="css/smoothness/jquery-ui-1.8.20.custom.css" rel="stylesheet" type="text/css"/>
<link href="css/scram.css" rel="stylesheet" type="text/css"/>
<script src="http://yui.yahooapis.com/3.5.1/build/yui/yui-min.js"></script>
<script type="text/javascript" src="scripts/scram.js"></script>
<script type="text/javascript">
var member_id = <?=$member_id?>;
var member_name = '<?=$member_name?>';
var need_identification = <?=$need_identification?1:0?>;
var sprint_id = 1;
YAHOO.example.Data = {
	    addresses: [
	        {name:"John A. Smith", address:"1236 Some Street", city:"San Francisco", state:"CA", amount:5, active:"yes", colors:["red"], fruit:["banana","cherry"], last_login:"4/19/2007"},
	        {name:"Joan B. Jones", address:"3271 Another Ave", city:"New York", state:"NY", amount:3, active:"no", colors:["red","blue"], fruit:["apple"], last_login:"2/15/2006"},
	        {name:"Bob C. Uncle", address:"9996 Random Road", city:"Los Angeles", state:"CA", amount:0, active:"maybe", colors:["green"], fruit:["cherry"], last_login:"1/23/2004"},
	        {name:"John D. Smith", address:"1623 Some Street", city:"San Francisco", state:"CA", amount:5, active:"yes", colors:["red"], fruit:["cherry"], last_login:"4/19/2007"},
	        {name:"Joan E. Jones", address:"3217 Another Ave", city:"New York", state:"NY", amount:3, active:"no", colors:["red","blue"], fruit:["apple","cherry"], last_login:"2/15/2006"},
	        {name:"Bob F. Uncle", address:"9899 Random Road", city:"Los Angeles", state:"CA", amount:0, active:"maybe", colors:["green"], fruit:["banana"], last_login:"1/23/2004"},
	        {name:"John G. Smith", address:"1723 Some Street", city:"San Francisco", state:"CA", amount:5, active:"yes", colors:["red"], fruit:["apple"], last_login:"4/19/2007"},
	        {name:"Joan H. Jones", address:"3241 Another Ave", city:"New York", state:"NY", amount:3, active:"no", colors:["red","blue"], fruit:["kiwi"], last_login:"2/15/2006"},
	        {name:"Bob I. Uncle", address:"9909 Random Road", city:"Los Angeles", state:"CA", amount:0, active:"maybe", colors:["green"], fruit:["apple","banana"], last_login:"1/23/2004"},
	        {name:"John J. Smith", address:"1623 Some Street", city:"San Francisco", state:"CA", amount:5, active:"yes", colors:["red"], fruit:["apple","cherry"], last_login:"4/19/2007"},
	        {name:"Joan K. Jones", address:"3721 Another Ave", city:"New York", state:"NY", amount:3, active:"no", colors:["red","blue"], fruit:["banana"], last_login:"2/15/2006"},
	        {name:"Bob L. Uncle", address:"9989 Random Road", city:"Los Angeles", state:"CA", amount:0, active:"maybe", colors:["green"], fruit:["cherry"], last_login:"1/23/2004"},
	        {name:"John M. Smith", address:"1293 Some Street", city:"San Francisco", state:"CA", amount:5, active:"yes", colors:["red"], fruit:["cherry"], last_login:"4/19/2007"},
	        {name:"Joan N. Jones", address:"3621 Another Ave", city:"New York", state:"NY", amount:3, active:"no", colors:["red","blue"], fruit:["apple"], last_login:"2/15/2006"},
	        {name:"Bob O. Uncle", address:"9959 Random Road", city:"Los Angeles", state:"CA", amount:0, active:"maybe", colors:["green"], fruit:["kiwi","cherry"], last_login:"1/23/2004"},
	        {name:"John P. Smith", address:"6123 Some Street", city:"San Francisco", state:"CA", amount:5, active:"yes", colors:["red"], fruit:["banana"], last_login:"4/19/2007"},
	        {name:"Joan Q. Jones", address:"3281 Another Ave", city:"New York", state:"NY", amount:3, active:"no", colors:["red","blue"], fruit:["apple"], last_login:"2/15/2006"},
	        {name:"Bob R. Uncle", address:"9989 Random Road", city:"Los Angeles", state:"CA", amount:0, active:"maybe", colors:["green"], fruit:["apple"], last_login:"1/23/2004"}
	    ]
	};

YAHOO.util.Event.addListener(window, "load", function() {
    YAHOO.example.InlineCellEditing = function() {
        // Custom formatter for "address" column to preserve line breaks
        var formatAddress = function(elCell, oRecord, oColumn, oData) {
            elCell.innerHTML = "<pre class=\"address\">" + oData + "</pre>";
        };

        var myColumnDefs = [
            {key:"uneditable"},
            {key:"address", formatter:formatAddress, editor: new YAHOO.widget.TextareaCellEditor()},
            {key:"city", editor: new YAHOO.widget.TextboxCellEditor({disableBtns:true})},
            {key:"state", editor: new YAHOO.widget.DropdownCellEditor({dropdownOptions:YAHOO.example.Data.stateAbbrs,disableBtns:true})},
            {key:"amount", editor: new YAHOO.widget.TextboxCellEditor({validator:YAHOO.widget.DataTable.validateNumber})},
            {key:"active", editor: new YAHOO.widget.RadioCellEditor({radioOptions:["yes","no","maybe"],disableBtns:true})},
            {key:"colors", editor: new YAHOO.widget.CheckboxCellEditor({checkboxOptions:["red","yellow","blue"]})},
            {key:"fruit", editor: new YAHOO.widget.DropdownCellEditor({multiple:true,dropdownOptions:["apple","banana","cherry"]})},
            {key:"last_login", formatter:YAHOO.widget.DataTable.formatDate, editor: new YAHOO.widget.DateCellEditor()}
        ];

        var myDataSource = new YAHOO.util.DataSource(YAHOO.example.Data.addresses);
        myDataSource.responseType = YAHOO.util.DataSource.TYPE_JSARRAY;
        myDataSource.responseSchema = {
            fields: ["address","city","state","amount","active","colors","fruit",{key:"last_login",parser:"date"}]
        };

        var myDataTable = new YAHOO.widget.DataTable("taskList", myColumnDefs, myDataSource, {});

        // Set up editing flow
        var highlightEditableCell = function(oArgs) {
            var elCell = oArgs.target;
            if(YAHOO.util.Dom.hasClass(elCell, "yui-dt-editable")) {
                this.highlightCell(elCell);
            }
        };
        
        myDataTable.subscribe("cellMouseoverEvent", highlightEditableCell);
        myDataTable.subscribe("cellMouseoutEvent", myDataTable.onEventUnhighlightCell);
        myDataTable.subscribe("cellClickEvent", myDataTable.onEventShowCellEditor);
        
        return {
            oDS: myDataSource,
            oDT: myDataTable
        };
    }();
});
</script>

<title>Sprint details</title>
</head>
<body  class="yui-skin-sam">
<div id="menu">
<h2>Menu</h2>
<ul>
<li>sprint overview
<li><a href="team_member.php" >team member page</a>
</ul>
</div>
<div class="mainBody" id="tasks">    
    <h3 class="taskCategoryHeader"><a href="#">Sprint Details</a></h3>
    <div class="scrumBoardTasks"><ul id="sprintDetails" class="taskList"></ul><br style="clear:both;"/></div>
    <h3 class="taskCategoryHeader"><a href="#">Sprint People</a></h3>
    <div class="scrumBoardTasks"><ul id="sprintPeople" class="taskList"></ul><br style="clear:both;"/></div>
    <h3 class="taskCategoryHeader"><a href="#">Sprint Tasks</a><button class="addTaskButton">Add a task</button></h3>
    <div class="scrumBoardTasks"><div id="taskList"></div><br style="clear:both;"/></div>
</div>

</body>
</html>