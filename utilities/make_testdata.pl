#!/usr/bin/perl
use POSIX;
use DateTime;

$plandate = DateTime->new( year=> 2012, month => 9, day => 1);
$startdate = DateTime->new( year=> 2012, month => 9, day => 2);
$developer_count = 1;
%developer_ids = {};
$task_counter = 1;

sub get_dev($)
{
	my ($devname) = @_;
	if (!defined( $developer_ids{$devname}))
	{
		$developer_ids{$devname} = $developer_count;
		emit( "insert into resource( resource_id, name) values( $developer_count, '$devname');");
		++$developer_count;
	}
	return $developer_ids{$devname};
}

sub emit($)
{
	print @_;
	print "\n";
}

sub add_task( $$$)
{
	my ($desc, $dev, $est) = @_;
	$dev_id = get_dev( $dev);
	$est = ceil( $est);
	emit( "insert into task( task_id, sprint_id, description, status) values( $task_counter, 1, '$desc', 'toDo');");
	$date_val = $plandate->strftime( '%F');
	emit( "insert into report( task_id, resource_id, burnt, estimate, date) values( $task_counter, $dev_id, 0, $est, '$date_val');");
	return $task_counter++;
}

sub add_report($$$$$)
{
	my ($task, $developer, $spent, $left, $day) = @_;
	if ($spent || $spent eq 0)
	{
	$spent = ceil( $spent);
	$left = ceil( $left);
	$day = $day + 2 * floor( $day/5);
	$date_val = $startdate->clone();
	$date_val->add( days => $day);
	$report_date = $date_val->strftime( '%F');
	emit("insert into report( task_id, resource_id, burnt, estimate, date) values ($task, $developer, $spent, $left, '$report_date');");
	}

}

while(<>)
{
	@fields = split /,/;
	$description = shift @fields;
	$developer = shift @fields;
	$initial_estimate = shift @fields;
	$task_id = add_task( $description, $developer, $initial_estimate);
	$day_count = 0;
	while (scalar(@fields))
	{
		$spent = shift @fields;
		$new_estimate = shift @fields;
		add_report( $task_id, get_dev( $developer), $spent, $new_estimate, $day_count);
		++$day_count;
	}
}

