ALTER TABLE `report`
  DROP PRIMARY KEY,
   ADD PRIMARY KEY(
     `task_id`,
     `date`);