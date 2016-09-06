
--
-- Alter structure for table `appointments`
--

ALTER TABLE `calendars`.`appointments`
DROP COLUMN `appoinment_time`,
ADD COLUMN `appointment_start_time` DATETIME NOT NULL COMMENT '' AFTER `subject`,
ADD COLUMN `appointment_end_time` DATETIME NOT NULL COMMENT '' AFTER `appointment_start_time`;

--
-- Alter structure for table `calendars`
--

ALTER TABLE `calendars`.`calendars` 
ADD COLUMN `owner_email` VARCHAR(80) NOT NULL COMMENT '' AFTER `owner_name`;
