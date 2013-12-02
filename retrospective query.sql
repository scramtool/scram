select task_info.task_id, task_info.description, task_info.status, start_report.estimate as start_estimate, end_report.estimate as work_left, (end_report.estimate + total_burnt) as final_size 
from 
  (select report.task_id, task.description, task.status, max( date) as end, min(date) as start, sum( burnt) as total_burnt from report, task where task.task_id = report.task_id and task.sprint_id =23 and task.status != 'forwarded' group by report.task_id) as task_info, 
  report as end_report, 
  report as start_report  
where task_info.end = end_report.date and task_info.task_id = end_report.task_id and task_info.start = start_report.date and task_info.task_id = start_report.task_id