
--
-- Alter structure for table `apps`
--

ALTER TABLE `calendars`.`apps` ADD COLUMN `html_confirmation_email` TEXT NOT NULL after `from_name`;
ALTER TABLE `calendars`.`apps` ADD COLUMN `html_modify_email` TEXT NOT NULL after `html_confirmation_email`;
ALTER TABLE `calendars`.`apps` ADD COLUMN `html_cancel_email` TEXT NOT NULL after `html_modify_email`;
