<?php
function get_task_query( $sprint_id, $task_id = -1)
{
	$sprint_id = (int)$sprint_id;
	$task_id = (int)$task_id;
	$task_restriction = "";
	if ($task_id > 0)
	{
		$task_restriction = "WHERE task.task_id = $task_id";
		$report_restriction = "WHERE report.task_id = $task_id";
	}
	else
	{
		$task_restriction = "WHERE task.sprint_id = $sprint_id";
		$report_restriction = "WHERE sprint_id =$sprint_id";
	}
	return <<<EOT
SELECT task.* , resource.name, report.estimate, DATE_FORMAT(report_date, "%Y-%m-%d") as report_date
FROM (
	SELECT report.task_id, max( date ) AS report_date
	FROM report
	JOIN task ON report.task_id = task.task_id
	$report_restriction
	GROUP BY report.task_id
) AS task_report
JOIN report ON task_report.task_id = report.task_id AND task_report.report_date = report.date
RIGHT OUTER JOIN task ON task.task_id = report.task_id
LEFT OUTER JOIN resource ON task.resource_id = resource.resource_id
	$task_restriction
ORDER BY report.description DESC
EOT;
}
