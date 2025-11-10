-- Migration: Update registration for enhanced fields
-- Run this to add new fields to registration_requests and users tables

USE `student_record_management_system`;

-- Update registration_requests table
ALTER TABLE `registration_requests`
ADD COLUMN `profile_photo` VARCHAR(255) DEFAULT NULL AFTER `passport_photo`,
ADD COLUMN `intake_month` ENUM('january','august') DEFAULT 'january' AFTER `entry_semester`,
ADD COLUMN `sponsor_name` VARCHAR(200) DEFAULT NULL AFTER `emergency_contact_email`,
ADD COLUMN `sponsor_relationship` VARCHAR(100) DEFAULT NULL AFTER `sponsor_name`,
ADD COLUMN `sponsor_phone` VARCHAR(20) DEFAULT NULL AFTER `sponsor_relationship`,
ADD COLUMN `sponsor_email` VARCHAR(100) DEFAULT NULL AFTER `sponsor_phone`,
ADD COLUMN `is_self_sponsored` TINYINT(1) DEFAULT 0 AFTER `sponsor_email`,
ADD COLUMN `id_type` ENUM('national_id','passport','refugee_id') DEFAULT 'national_id' AFTER `national_id`,
ADD COLUMN `id_number` VARCHAR(100) DEFAULT NULL AFTER `id_type`,
ADD COLUMN `marital_status` ENUM('single','married','divorced','widowed') DEFAULT 'single' AFTER `gender`,
ADD COLUMN `religion` VARCHAR(100) DEFAULT NULL AFTER `marital_status`;

-- Update users table
ALTER TABLE `users`
ADD COLUMN `intake_month` ENUM('january','august') DEFAULT NULL AFTER `current_semester`,
ADD COLUMN `sponsor_name` VARCHAR(200) DEFAULT NULL AFTER `alternative_phone`,
ADD COLUMN `sponsor_phone` VARCHAR(20) DEFAULT NULL AFTER `sponsor_name`,
ADD COLUMN `is_self_sponsored` TINYINT(1) DEFAULT 0 AFTER `sponsor_phone`,
ADD COLUMN `id_type` ENUM('national_id','passport','refugee_id') DEFAULT 'national_id' AFTER `national_id`,
ADD COLUMN `id_number` VARCHAR(100) DEFAULT NULL AFTER `id_type`,
ADD COLUMN `marital_status` ENUM('single','married','divorced','widowed') DEFAULT NULL AFTER `gender`,
ADD COLUMN `religion` VARCHAR(100) DEFAULT NULL AFTER `marital_status`;

SELECT 'Migration completed successfully!' as Status;


-- Additional fields for enhanced education section
ALTER TABLE `registration_requests`
ADD COLUMN `student_type` ENUM('local','international') DEFAULT 'local' AFTER `nationality`,
ADD COLUMN `olevel_school` VARCHAR(200) DEFAULT NULL AFTER `secondary_school`,
ADD COLUMN `olevel_completion_year` YEAR DEFAULT NULL AFTER `olevel_school`,
ADD COLUMN `olevel_index_number` VARCHAR(100) DEFAULT NULL AFTER `olevel_completion_year`,
ADD COLUMN `olevel_certificate` VARCHAR(255) DEFAULT NULL AFTER `olevel_index_number`,
ADD COLUMN `alevel_school` VARCHAR(200) DEFAULT NULL AFTER `olevel_certificate`,
ADD COLUMN `alevel_completion_year` YEAR DEFAULT NULL AFTER `alevel_school`,
ADD COLUMN `alevel_index_number` VARCHAR(100) DEFAULT NULL AFTER `alevel_completion_year`,
ADD COLUMN `alevel_certificate` VARCHAR(255) DEFAULT NULL AFTER `alevel_index_number`,
ADD COLUMN `international_certificate` VARCHAR(255) DEFAULT NULL AFTER `alevel_certificate`,
ADD COLUMN `international_certificate_translated` VARCHAR(255) DEFAULT NULL AFTER `international_certificate`;

ALTER TABLE `users`
ADD COLUMN `student_type` ENUM('local','international') DEFAULT 'local' AFTER `nationality`;

SELECT 'Education section fields added successfully!' as Status;


-- Application Fee Payment fields
ALTER TABLE `registration_requests`
ADD COLUMN `payment_status` ENUM('pending','completed','verified') DEFAULT 'pending' AFTER `info_accurate`,
ADD COLUMN `payment_proof` VARCHAR(255) DEFAULT NULL AFTER `payment_status`,
ADD COLUMN `transaction_reference` VARCHAR(100) DEFAULT NULL AFTER `payment_proof`,
ADD COLUMN `payment_date` DATETIME DEFAULT NULL AFTER `transaction_reference`,
ADD COLUMN `payment_verified_by` INT DEFAULT NULL AFTER `payment_date`,
ADD COLUMN `payment_verified_at` DATETIME DEFAULT NULL AFTER `payment_verified_by`;

SELECT 'Application fee payment fields added successfully!' as Status;
