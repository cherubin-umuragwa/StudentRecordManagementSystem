-- ============================================
-- Migration: Add Financial Management System
-- Run this on existing database to add finance features
-- ============================================

USE `student_grade_management`;

-- 1. Update users table to add accountant role
ALTER TABLE `users` 
MODIFY `role` enum('admin','lecturer','student','registrar','accountant') NOT NULL;

-- 2. Create invoices table
CREATE TABLE IF NOT EXISTS `invoices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `academic_year` varchar(20) NOT NULL,
  `semester` int(1) NOT NULL,
  `tuition_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `registration_fee` decimal(10,2) DEFAULT 0.00,
  `library_fee` decimal(10,2) DEFAULT 0.00,
  `lab_fee` decimal(10,2) DEFAULT 0.00,
  `other_fees` decimal(10,2) DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL,
  `amount_paid` decimal(10,2) DEFAULT 0.00,
  `balance` decimal(10,2) NOT NULL,
  `status` enum('pending','partial','paid','overdue') DEFAULT 'pending',
  `due_date` date NOT NULL,
  `generated_by` int(11) DEFAULT NULL,
  `generated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_number` (`invoice_number`),
  KEY `student_id` (`student_id`),
  KEY `generated_by` (`generated_by`),
  KEY `status` (`status`),
  KEY `academic_year_semester` (`academic_year`, `semester`),
  CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `invoices_ibfk_2` FOREIGN KEY (`generated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. Create payments table
CREATE TABLE IF NOT EXISTS `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `payment_reference` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','bank_transfer','mobile_money','cheque','card') NOT NULL,
  `payment_date` date NOT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `received_by` int(11) DEFAULT NULL,
  `receipt_number` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_reference` (`payment_reference`),
  KEY `invoice_id` (`invoice_id`),
  KEY `student_id` (`student_id`),
  KEY `received_by` (`received_by`),
  KEY `payment_date` (`payment_date`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payments_ibfk_3` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 4. Create scholarships table
CREATE TABLE IF NOT EXISTS `scholarships` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `scholarship_type` enum('full','partial','merit','need_based','sports','other') NOT NULL,
  `amount` decimal(10,2) DEFAULT NULL COMMENT 'Fixed amount or NULL for percentage',
  `percentage` decimal(5,2) DEFAULT NULL COMMENT 'Percentage discount or NULL for fixed amount',
  `duration_semesters` int(2) DEFAULT NULL COMMENT 'Number of semesters, NULL for unlimited',
  `eligibility_criteria` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 5. Create student_scholarships table
CREATE TABLE IF NOT EXISTS `student_scholarships` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `scholarship_id` int(11) NOT NULL,
  `academic_year` varchar(20) NOT NULL,
  `start_semester` int(1) NOT NULL,
  `end_semester` int(1) DEFAULT NULL,
  `status` enum('active','expired','revoked','completed') DEFAULT 'active',
  `awarded_by` int(11) DEFAULT NULL,
  `awarded_date` date NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `scholarship_id` (`scholarship_id`),
  KEY `awarded_by` (`awarded_by`),
  KEY `status` (`status`),
  CONSTRAINT `student_scholarships_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_scholarships_ibfk_2` FOREIGN KEY (`scholarship_id`) REFERENCES `scholarships` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_scholarships_ibfk_3` FOREIGN KEY (`awarded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 6. Create financial_clearance table
CREATE TABLE IF NOT EXISTS `financial_clearance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `academic_year` varchar(20) NOT NULL,
  `semester` int(1) NOT NULL,
  `total_fees` decimal(10,2) NOT NULL,
  `total_paid` decimal(10,2) NOT NULL,
  `balance` decimal(10,2) NOT NULL,
  `clearance_status` enum('cleared','not_cleared','pending') DEFAULT 'pending',
  `cleared_by` int(11) DEFAULT NULL,
  `cleared_date` date DEFAULT NULL,
  `can_register_courses` tinyint(1) DEFAULT 0,
  `can_take_exams` tinyint(1) DEFAULT 0,
  `can_graduate` tinyint(1) DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_year_semester` (`student_id`, `academic_year`, `semester`),
  KEY `cleared_by` (`cleared_by`),
  KEY `clearance_status` (`clearance_status`),
  CONSTRAINT `financial_clearance_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `financial_clearance_ibfk_2` FOREIGN KEY (`cleared_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 7. Insert accountant users
INSERT INTO `users` (`username`, `password`, `email`, `role`, `first_name`, `middle_name`, `last_name`, `phone`, `gender`, `created_at`) VALUES
('accountant', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'accountant@school.edu', 'accountant', 'Finance', NULL, 'Officer', '+256712345501', 'male', NOW()),
('accountant2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'accountant2@school.edu', 'accountant', 'Sarah', 'Jane', 'Accountant', '+256712345502', 'female', NOW())
ON DUPLICATE KEY UPDATE username=username;

-- 8. Insert sample scholarships
INSERT INTO `scholarships` (`name`, `description`, `scholarship_type`, `amount`, `percentage`, `duration_semesters`, `eligibility_criteria`, `is_active`) VALUES
('Presidential Scholarship', 'Full scholarship for top performers', 'full', NULL, 100.00, 8, 'GPA above 3.8, Leadership qualities', 1),
('Merit Scholarship', 'Partial scholarship for good academic performance', 'merit', NULL, 50.00, 4, 'GPA above 3.5', 1),
('Sports Excellence Award', 'For outstanding sports achievements', 'sports', 500000.00, NULL, 4, 'National level sports participation', 1),
('Need-Based Grant', 'Financial aid for students in need', 'need_based', 750000.00, NULL, 2, 'Family income below threshold', 1),
('Academic Excellence', 'For students with exceptional grades', 'merit', NULL, 75.00, 8, 'GPA 3.7 and above', 1);

-- Migration complete!
SELECT 'Financial management system tables created successfully!' as Status;
