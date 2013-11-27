<?php
//
//  Copyright (C) 2012 Danny Havenith
//
//  Distributed under the Boost Software License, Version 1.0. (See
//  accompanying file LICENSE_1_0.txt or copy at
//  http://www.boost.org/LICENSE_1_0.txt)
//

function get_task_query( $sprint_id, $task_id = -1)
{
	$sprint_id = (int)$sprint_id;
	$task_id = (int)$task_id;
	$task_restriction = "";
	if ($task_id > 0)
	{
		$task_restriction = "WHERE task.task_id = $task_id";
		$report_restriction = "WHERE report.task_id = $task_id AND report.reason!='forward'";
	}
	else
	{
		$task_restriction = "WHERE task.sprint_id = $sprint_id";
		$report_restriction = "WHERE sprint_id =$sprint_id  AND report.reason!='forward'";
	}
	return <<<EOT
SELECT task.* , resource.name, report.estimate, report.burnt, DATE_FORMAT(report_date, "%Y-%m-%d") as report_date
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
ORDER BY task.description ASC
EOT;
}

/**
 * return SQL text for a query that fetches enough information about tasks of a given sprint to put 
 * in a task table.
 * @param unknown $sprint_id
 * @return string
 */
function get_task_table_query( $sprint_id)
{
    return <<<EOT
SELECT task. * , resource.name AS `who` , reports.total_burnt, last_report.estimate AS remaining, first_report.estimate AS initial_estimate, last_report.estimate + reports.total_burnt AS current_size
FROM `task`
LEFT OUTER JOIN (
    SELECT task_id, min( date ) AS `first` , max( date ) AS `last` , sum( burnt ) AS total_burnt
    FROM report
    WHERE report.reason != 'forward'
    GROUP BY task_id
) AS reports ON task.task_id = reports.task_id
LEFT OUTER JOIN report AS first_report ON first_report.date = `first` AND first_report.task_id = task.task_id
LEFT OUTER JOIN report AS last_report ON last_report.date = last AND last_report.task_id = task.task_id
JOIN resource ON task.resource_id = resource.resource_id
WHERE task.sprint_id = $sprint_id
ORDER BY `task`.`status` ASC
EOT;
}