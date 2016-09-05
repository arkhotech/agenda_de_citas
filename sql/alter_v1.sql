
--
-- Alter structure for table `apps`
--

ALTER TABLE `calendars`.`apps` 
ADD COLUMN `from_email` VARCHAR(80) NOT NULL COMMENT '' AFTER `contact_email`,
ADD COLUMN `from_name` VARCHAR(80) NOT NULL COMMENT '' AFTER `from_email`;