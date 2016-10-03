
--
-- Alter structure for table `apps`
--

ALTER TABLE `calendar`.`apps` ADD COLUMN `html_confirmation_email` TEXT NOT NULL after `from_name`;
ALTER TABLE `calendar`.`apps` ADD COLUMN `html_modify_email` TEXT NOT NULL after `html_confirmation_email`;
ALTER TABLE `calendar`.`apps` ADD COLUMN `html_cancel_email` TEXT NOT NULL after `html_modify_email`;
