
--
-- Alter structure for table `appointments`
--

ALTER TABLE `calendars`.`appointments`
ADD COLUMN `metadata` VARCHAR(255) AFTER `confirmation_date`
