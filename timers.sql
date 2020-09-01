CREATE TABLE IF NOT EXISTS `timers` (
	`name` VARCHAR(255),
	`owner` VARCHAR(25),
	`mode` VARCHAR(50),
	`endtime` int,
	`settime` int,
	`callback` VARCHAR(255),
	`data` VARCHAR(255),
	`alerts` TEXT
);
