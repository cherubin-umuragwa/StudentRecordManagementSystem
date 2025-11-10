-- ============================================
-- Student Grade Management System
-- Comprehensive Sample Data (Seeds)
-- All passwords: password123
-- ============================================

USE `student_grade_management`;

-- ============================================
-- Insert Sample Schools
-- ============================================
INSERT INTO `schools` (`name`, `code`, `description`) VALUES
('School of Engineering', 'ENG', 'Engineering and Technology programs'),
('School of Business', 'BUS', 'Business Administration and Management'),
('School of Education', 'EDU', 'Teacher Education and Pedagogy'),
('School of Health Sciences', 'HLT', 'Medical and Health-related programs'),
('School of Arts & Humanities', 'ART', 'Arts, Languages, and Social Sciences'),
('School of Natural Sciences', 'SCI', 'Pure and Applied Sciences');

-- ============================================
-- Insert Sample Departments
-- ============================================
INSERT INTO `departments` (`school_id`, `name`, `code`, `description`) VALUES
-- Engineering Departments
(1, 'Computer Science & Engineering', 'CSE', 'Software and Computer Systems'),
(1, 'Electrical Engineering', 'ELE', 'Electrical and Electronics'),
(1, 'Mechanical Engineering', 'MEC', 'Mechanical Systems and Design'),
(1, 'Civil Engineering', 'CIV', 'Construction and Infrastructure'),
-- Business Departments
(2, 'Business Administration', 'BBA', 'General Business Management'),
(2, 'Accounting & Finance', 'ACF', 'Financial Management and Accounting'),
(2, 'Marketing', 'MKT', 'Marketing and Sales Management'),
-- Education Departments
(3, 'Teacher Education', 'TED', 'Primary and Secondary Education'),
(3, 'Educational Psychology', 'EPY', 'Psychology in Education'),
-- Health Sciences Departments
(4, 'Nursing', 'NUR', 'Nursing and Patient Care'),
(4, 'Public Health', 'PHT', 'Community Health and Epidemiology'),
-- Arts & Humanities Departments
(5, 'English Literature', 'ENG', 'English Language and Literature'),
(5, 'History', 'HIS', 'World and Regional History'),
-- Natural Sciences Departments
(6, 'Mathematics', 'MAT', 'Pure and Applied Mathematics'),
(6, 'Physics', 'PHY', 'Theoretical and Applied Physics'),
(6, 'Chemistry', 'CHE', 'Organic and Inorganic Chemistry');

-- ============================================
-- Insert Sample Programs
-- ============================================
INSERT INTO `programs` (`department_id`, `name`, `code`, `duration_years`, `total_credits`, `tuition_per_semester`) VALUES
-- Computer Science & Engineering Programs
(1, 'Bachelor of Science in Computer Science', 'BSC-CS', 4, 120, 2000.00),
(1, 'Bachelor of Science in Software Engineering', 'BSC-SE', 4, 120, 2000.00),
(1, 'Bachelor of Science in Information Technology', 'BSC-IT', 4, 120, 1900.00),
-- Electrical Engineering Programs
(2, 'Bachelor of Engineering in Electrical Engineering', 'BE-EE', 4, 128, 2200.00),
(2, 'Bachelor of Engineering in Electronics', 'BE-EC', 4, 128, 2200.00),
-- Mechanical Engineering Programs
(3, 'Bachelor of Engineering in Mechanical Engineering', 'BE-ME', 4, 128, 2200.00),
-- Civil Engineering Programs
(4, 'Bachelor of Engineering in Civil Engineering', 'BE-CE', 4, 128, 2200.00),
-- Business Programs
(5, 'Bachelor of Business Administration', 'BBA', 3, 90, 1800.00),
(5, 'Bachelor of Commerce', 'BCOM', 3, 90, 1700.00),
-- Accounting & Finance Programs
(6, 'Bachelor of Science in Accounting', 'BSC-ACC', 4, 120, 1900.00),
(6, 'Bachelor of Science in Finance', 'BSC-FIN', 4, 120, 1900.00),
-- Marketing Programs
(7, 'Bachelor of Science in Marketing', 'BSC-MKT', 3, 90, 1800.00),
-- Education Programs
(8, 'Bachelor of Education', 'BED', 4, 120, 1500.00),
(8, 'Bachelor of Education in Science', 'BED-SCI', 4, 120, 1500.00),
-- Educational Psychology Programs
(9, 'Bachelor of Arts in Educational Psychology', 'BA-EPY', 4, 120, 1600.00),
-- Nursing Programs
(10, 'Bachelor of Science in Nursing', 'BSC-NUR', 4, 130, 2500.00),
-- Public Health Programs
(11, 'Bachelor of Science in Public Health', 'BSC-PHT', 4, 120, 2000.00),
-- English Literature Programs
(12, 'Bachelor of Arts in English', 'BA-ENG', 3, 90, 1600.00),
-- History Programs
(13, 'Bachelor of Arts in History', 'BA-HIS', 3, 90, 1600.00),
-- Mathematics Programs
(14, 'Bachelor of Science in Mathematics', 'BSC-MAT', 4, 120, 1800.00),
-- Physics Programs
(15, 'Bachelor of Science in Physics', 'BSC-PHY', 4, 120, 1800.00),
-- Chemistry Programs
(16, 'Bachelor of Science in Chemistry', 'BSC-CHE', 4, 120, 1800.00);

-- ============================================
-- Insert Users (All passwords: password123)
-- Password hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
-- ============================================

-- Admin Users
INSERT INTO `users` (`username`, `password`, `email`, `role`, `first_name`, `middle_name`, `last_name`, `phone`, `gender`, `created_at`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@school.edu', 'admin', 'System', NULL, 'Administrator', '+256712345001', 'male', NOW()),
('admin2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin2@school.edu', 'admin', 'Jane', 'Mary', 'Smith', '+256712345002', 'female', NOW());

-- Registrar Users
INSERT INTO `users` (`username`, `password`, `email`, `role`, `first_name`, `middle_name`, `last_name`, `phone`, `gender`, `created_at`) VALUES
('registrar', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'registrar@school.edu', 'registrar', 'Academic', NULL, 'Registrar', '+256712345003', 'male', NOW()),
('registrar2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'registrar2@school.edu', 'registrar', 'Sarah', 'Ann', 'Johnson', '+256712345004', 'female', NOW());

-- Lecturer Users
INSERT INTO `users` (`username`, `password`, `email`, `role`, `first_name`, `middle_name`, `last_name`, `phone`, `gender`, `created_at`) VALUES
('lecturer1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'lecturer1@school.edu', 'lecturer', 'John', 'Paul', 'Mwangi', '+256712345101', 'male', NOW()),
('lecturer2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'lecturer2@school.edu', 'lecturer', 'Grace', 'Neema', 'Kamau', '+256712345102', 'female', NOW()),
('lecturer3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'lecturer3@school.edu', 'lecturer', 'David', 'James', 'Ochieng', '+256712345103', 'male', NOW()),
('lecturer4', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'lecturer4@school.edu', 'lecturer', 'Mary', 'Rose', 'Njeri', '+256712345104', 'female', NOW()),
('lecturer5', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'lecturer5@school.edu', 'lecturer', 'Peter', 'Michael', 'Wanjiru', '+256712345105', 'male', NOW());

-- Student Users
INSERT INTO `users` (`username`, `student_number`, `password`, `email`, `role`, `first_name`, `middle_name`, `last_name`, `phone`, `date_of_birth`, `gender`, `nationality`, `program_id`, `current_year`, `current_semester`, `enrollment_date`, `academic_status`, `created_at`) VALUES
-- Computer Science Students
('student1', 'STU2025001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student1@school.edu', 'student', 'James', 'Kipchoge', 'Mutua', '+256712345201', '2003-05-15', 'male', 'Ugandan', 1, 1, 1, '2025-09-01', 'active', NOW()),
('student2', 'STU2025002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student2@school.edu', 'student', 'Amina', 'Hassan', 'Mohamed', '+256712345202', '2003-08-22', 'female', 'Ugandan', 1, 1, 1, '2025-09-01', 'active', NOW()),
('student3', 'STU2025003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student3@school.edu', 'student', 'Daniel', 'Baraka', 'Kimani', '+256712345203', '2003-03-10', 'male', 'Kenyan', 2, 1, 1, '2025-09-01', 'active', NOW()),
('student4', 'STU2025004', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student4@school.edu', 'student', 'Fatuma', 'Juma', 'Ali', '+256712345204', '2003-11-30', 'female', 'Ugandan', 1, 1, 1, '2025-09-01', 'active', NOW()),
-- Business Students
('student5', 'STU2025005', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student5@school.edu', 'student', 'Michael', 'Otieno', 'Wekesa', '+256712345205', '2004-01-18', 'male', 'Kenyan', 8, 1, 1, '2025-09-01', 'active', NOW()),
('student6', 'STU2025006', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student6@school.edu', 'student', 'Lucy', 'Wanjiku', 'Kariuki', '+256712345206', '2003-07-25', 'female', 'Kenyan', 8, 1, 1, '2025-09-01', 'active', NOW()),
-- Engineering Students
('student7', 'STU2025007', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student7@school.edu', 'student', 'Emmanuel', 'Musa', 'Ndege', '+256712345207', '2003-09-12', 'male', 'Ugandan', 4, 1, 1, '2025-09-01', 'active', NOW()),
('student8', 'STU2025008', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student8@school.edu', 'student', 'Esther', 'Akinyi', 'Omondi', '+256712345208', '2003-04-08', 'female', 'Kenyan', 6, 1, 1, '2025-09-01', 'active', NOW()),
-- Education Students
('student9', 'STU2025009', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student9@school.edu', 'student', 'Joseph', 'Kamau', 'Maina', '+256712345209', '2003-12-05', 'male', 'Kenyan', 14, 1, 1, '2025-09-01', 'active', NOW()),
('student10', 'STU2025010', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student10@school.edu', 'student', 'Rebecca', 'Njoki', 'Wambui', '+256712345210', '2003-06-20', 'female', 'Kenyan', 14, 1, 1, '2025-09-01', 'active', NOW()),
-- Health Sciences Students
('student11', 'STU2025011', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student11@school.edu', 'student', 'Samuel', 'Kiprop', 'Koech', '+256712345211', '2003-02-14', 'male', 'Kenyan', 17, 1, 1, '2025-09-01', 'active', NOW()),
('student12', 'STU2025012', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student12@school.edu', 'student', 'Catherine', 'Wangari', 'Mwangi', '+256712345212', '2003-10-28', 'female', 'Kenyan', 17, 1, 1, '2025-09-01', 'active', NOW()),
-- Year 2 Students
('student13', 'STU2024001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student13@school.edu', 'student', 'Patrick', 'Ouma', 'Otieno', '+256712345213', '2002-05-10', 'male', 'Kenyan', 1, 2, 1, '2024-09-01', 'active', NOW()),
('student14', 'STU2024002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student14@school.edu', 'student', 'Agnes', 'Chebet', 'Rotich', '+256712345214', '2002-08-15', 'female', 'Kenyan', 1, 2, 1, '2024-09-01', 'active', NOW()),
('student15', 'STU2024003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student15@school.edu', 'student', 'Brian', 'Mwenda', 'Njoroge', '+256712345215', '2002-03-22', 'male', 'Kenyan', 8, 2, 1, '2024-09-01', 'active', NOW());

-- ============================================
-- Insert Sample Subjects
-- ============================================
INSERT INTO `subjects` (`name`, `code`, `description`) VALUES
('English Language', 'ENG101', 'English Language and Communication'),
('Mathematics', 'MATH101', 'General Mathematics'),
('Physics', 'PHY101', 'General Physics'),
('Chemistry', 'CHE101', 'General Chemistry'),
('Biology', 'BIO101', 'General Biology'),
('Computer Science', 'CS101', 'Introduction to Computer Science'),
('Business Studies', 'BUS101', 'Introduction to Business'),
('History', 'HIS101', 'World History'),
('Geography', 'GEO101', 'Physical Geography'),
('Kiswahili', 'KIS101', 'Kiswahili Language');

-- ============================================
-- Insert Sample Classrooms
-- ============================================
INSERT INTO `classrooms` (`name`, `description`, `lecturer_id`, `created_at`) VALUES
('Computer Science - Year 1A', 'First year computer science students - Section A', 5, NOW()),
('Computer Science - Year 1B', 'First year computer science students - Section B', 5, NOW()),
('Business Administration - Year 1', 'First year business students', 6, NOW()),
('Engineering - Year 1', 'First year engineering students', 7, NOW()),
('Education - Year 1', 'First year education students', 8, NOW()),
('Nursing - Year 1', 'First year nursing students', 9, NOW());

-- ============================================
-- Insert Classroom Students
-- ============================================
INSERT INTO `classroom_students` (`classroom_id`, `student_id`, `enrolled_at`) VALUES
-- Computer Science Year 1A
(1, 7, NOW()),
(1, 8, NOW()),
(1, 9, NOW()),
(1, 10, NOW()),
-- Computer Science Year 1B
(2, 11, NOW()),
(2, 12, NOW()),
-- Business Administration Year 1
(3, 13, NOW()),
(3, 14, NOW()),
-- Engineering Year 1
(4, 15, NOW()),
(4, 16, NOW()),
-- Education Year 1
(5, 17, NOW()),
(5, 18, NOW()),
-- Nursing Year 1
(6, 19, NOW()),
(6, 20, NOW());

-- ============================================
-- Insert Sample Courses for Computer Science
-- ============================================
INSERT INTO `courses` (`program_id`, `code`, `name`, `description`, `credits`, `year_level`, `semester`, `is_elective`, `max_students`, `instructor_id`) VALUES
-- Year 1, Semester 1
(1, 'CS101', 'Introduction to Programming', 'Fundamentals of programming using Python', 4, 1, 1, 0, 50, 5),
(1, 'CS102', 'Computer Organization', 'Basic computer architecture and organization', 3, 1, 1, 0, 50, 5),
(1, 'MATH101', 'Calculus I', 'Differential and integral calculus', 4, 1, 1, 0, 50, 6),
(1, 'ENG101', 'English Communication', 'Academic writing and communication skills', 3, 1, 1, 0, 50, 7),
(1, 'PHY101', 'Physics I', 'Mechanics and thermodynamics', 3, 1, 1, 0, 50, 8),
-- Year 1, Semester 2
(1, 'CS103', 'Data Structures', 'Arrays, linked lists, trees, graphs', 4, 1, 2, 0, 50, 5),
(1, 'CS104', 'Object-Oriented Programming', 'OOP concepts using Java', 4, 1, 2, 0, 50, 5),
(1, 'MATH102', 'Calculus II', 'Multivariable calculus', 4, 1, 2, 0, 50, 6),
(1, 'STAT101', 'Statistics', 'Probability and statistics', 3, 1, 2, 0, 50, 6),
(1, 'PHY102', 'Physics II', 'Electricity and magnetism', 3, 1, 2, 0, 50, 8),
-- Year 2, Semester 1
(1, 'CS201', 'Algorithms', 'Algorithm design and analysis', 4, 2, 1, 0, 45, 5),
(1, 'CS202', 'Database Systems', 'Relational databases and SQL', 4, 2, 1, 0, 45, 5),
(1, 'CS203', 'Computer Networks', 'Network protocols and architecture', 3, 2, 1, 0, 45, 5),
(1, 'MATH201', 'Discrete Mathematics', 'Logic, sets, and graph theory', 3, 2, 1, 0, 45, 6),
(1, 'CS204', 'Web Development', 'HTML, CSS, JavaScript, PHP', 3, 2, 1, 1, 45, 5);

-- ============================================
-- Insert Sample Course Registrations
-- ============================================
INSERT INTO `course_registrations` (`student_id`, `course_id`, `academic_year`, `semester`, `status`, `approved_by`, `approved_at`) VALUES
-- Student 1 (James) - Year 1, Semester 1 courses
(7, 1, '2025/2026', 1, 'approved', 3, NOW()),
(7, 2, '2025/2026', 1, 'approved', 3, NOW()),
(7, 3, '2025/2026', 1, 'approved', 3, NOW()),
(7, 4, '2025/2026', 1, 'approved', 3, NOW()),
(7, 5, '2025/2026', 1, 'approved', 3, NOW()),
-- Student 2 (Amina) - Year 1, Semester 1 courses
(8, 1, '2025/2026', 1, 'approved', 3, NOW()),
(8, 2, '2025/2026', 1, 'approved', 3, NOW()),
(8, 3, '2025/2026', 1, 'approved', 3, NOW()),
(8, 4, '2025/2026', 1, 'approved', 3, NOW()),
(8, 5, '2025/2026', 1, 'approved', 3, NOW()),
-- Student 13 (Patrick) - Year 2, Semester 1 courses
(19, 11, '2025/2026', 1, 'approved', 3, NOW()),
(19, 12, '2025/2026', 1, 'approved', 3, NOW()),
(19, 13, '2025/2026', 1, 'approved', 3, NOW()),
(19, 14, '2025/2026', 1, 'approved', 3, NOW()),
(19, 15, '2025/2026', 1, 'pending', NULL, NULL);

-- ============================================
-- Insert Sample Grades
-- ============================================
INSERT INTO `grades` (`student_id`, `subject_id`, `classroom_id`, `lecturer_id`, `grade`, `grade_type`, `remarks`, `graded_at`) VALUES
-- Student 1 (James) grades
(7, 6, 1, 5, 85.50, 'quiz', 'Good understanding of concepts', NOW()),
(7, 6, 1, 5, 78.00, 'assignment', 'Well done', NOW()),
(7, 1, 1, 7, 92.00, 'exam', 'Excellent performance', NOW()),
(7, 2, 1, 6, 88.50, 'quiz', 'Very good', NOW()),
-- Student 2 (Amina) grades
(8, 6, 1, 5, 90.00, 'quiz', 'Excellent work', NOW()),
(8, 6, 1, 5, 87.50, 'assignment', 'Outstanding', NOW()),
(8, 1, 1, 7, 95.00, 'exam', 'Top performer', NOW()),
(8, 2, 1, 6, 91.00, 'quiz', 'Excellent', NOW()),
-- Student 3 (Daniel) grades
(9, 6, 1, 5, 75.00, 'quiz', 'Good effort', NOW()),
(9, 6, 1, 5, 80.00, 'assignment', 'Good work', NOW()),
(9, 1, 1, 7, 82.00, 'exam', 'Good performance', NOW()),
-- Student 13 (Patrick) - Year 2 grades
(19, 6, 1, 5, 88.00, 'quiz', 'Very good understanding', NOW()),
(19, 6, 1, 5, 85.00, 'assignment', 'Well done', NOW()),
(19, 1, 1, 7, 90.00, 'exam', 'Excellent', NOW());

-- ============================================
-- Insert Sample Course Schedule
-- ============================================
INSERT INTO `course_schedule` (`course_id`, `day_of_week`, `start_time`, `end_time`, `room`, `building`) VALUES
-- CS101 Schedule
(1, 'Monday', '08:00:00', '10:00:00', 'Lab 101', 'Computer Science Building'),
(1, 'Wednesday', '08:00:00', '10:00:00', 'Lab 101', 'Computer Science Building'),
-- CS102 Schedule
(2, 'Tuesday', '10:00:00', '12:00:00', 'Room 201', 'Computer Science Building'),
(2, 'Thursday', '10:00:00', '12:00:00', 'Room 201', 'Computer Science Building'),
-- MATH101 Schedule
(3, 'Monday', '14:00:00', '16:00:00', 'Room 301', 'Mathematics Building'),
(3, 'Friday', '14:00:00', '16:00:00', 'Room 301', 'Mathematics Building'),
-- ENG101 Schedule
(4, 'Tuesday', '08:00:00', '10:00:00', 'Room 101', 'Arts Building'),
(4, 'Thursday', '08:00:00', '10:00:00', 'Room 101', 'Arts Building'),
-- PHY101 Schedule
(5, 'Wednesday', '14:00:00', '16:00:00', 'Lab 201', 'Science Building'),
(5, 'Friday', '10:00:00', '12:00:00', 'Lab 201', 'Science Building');

-- ============================================
-- Insert Sample Registration Requests (Pending)
-- ============================================
INSERT INTO `registration_requests` (`username`, `password`, `email`, `first_name`, `middle_name`, `last_name`, `date_of_birth`, `gender`, `nationality`, `phone`, `address`, `city`, `region`, `country`, `guardian_name`, `guardian_phone`, `secondary_school`, `completion_year`, `certificate_type`, `division_grade`, `program`, `school`, `department`, `entry_year`, `entry_semester`, `status`, `created_at`) VALUES
('pending1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'pending1@example.com', 'Alice', 'Wanjiru', 'Kamau', '2004-03-15', 'female', 'Kenyan', '+256712345301', 'Nairobi Street', 'Dar es Salaam', 'Dar es Salaam', 'Uganda', 'John Kamau', '+256712345401', 'Nairobi High School', 2024, 'KCSE', 'B+', 'Bachelor of Science in Computer Science', 'School of Engineering', 'Computer Science & Engineering', 2025, 1, 'pending', NOW()),
('pending2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'pending2@example.com', 'Robert', 'Mwangi', 'Ochieng', '2004-07-20', 'male', 'Kenyan', '+256712345302', 'Mombasa Road', 'Arusha', 'Arusha', 'Uganda', 'Mary Ochieng', '+256712345402', 'Mombasa Secondary', 2024, 'KCSE', 'A-', 'Bachelor of Business Administration', 'School of Business', 'Business Administration', 2025, 1, 'pending', NOW()),
('pending3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'pending3@example.com', 'Linda', 'Achieng', 'Wambui', '2004-11-10', 'female', 'Ugandan', '+256712345303', 'Uhuru Street', 'Mwanza', 'Mwanza', 'Uganda', 'Peter Wambui', '+256712345403', 'Mwanza Girls School', 2024, 'NECTA', 'Division I', 'Bachelor of Science in Nursing', 'School of Health Sciences', 'Nursing', 2025, 1, 'pending', NOW());

-- ============================================
-- End of Sample Data
-- ============================================


-- ============================================
-- Insert Accountant Users
-- ============================================
INSERT INTO `users` (`username`, `password`, `email`, `role`, `first_name`, `middle_name`, `last_name`, `phone`, `gender`, `created_at`) VALUES
('accountant', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'accountant@school.edu', 'accountant', 'Finance', NULL, 'Officer', '+256712345501', 'male', NOW()),
('accountant2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'accountant2@school.edu', 'accountant', 'Sarah', 'Jane', 'Accountant', '+256712345502', 'female', NOW());

-- ============================================
-- Insert Sample Scholarships
-- ============================================
INSERT INTO `scholarships` (`name`, `description`, `scholarship_type`, `amount`, `percentage`, `duration_semesters`, `eligibility_criteria`, `is_active`) VALUES
('Presidential Scholarship', 'Full scholarship for top performers', 'full', NULL, 100.00, 8, 'GPA above 3.8, Leadership qualities', 1),
('Merit Scholarship', 'Partial scholarship for good academic performance', 'merit', NULL, 50.00, 4, 'GPA above 3.5', 1),
('Sports Excellence Award', 'For outstanding sports achievements', 'sports', 500000.00, NULL, 4, 'National level sports participation', 1),
('Need-Based Grant', 'Financial aid for students in need', 'need_based', 750000.00, NULL, 2, 'Family income below threshold', 1),
('Academic Excellence', 'For students with exceptional grades', 'merit', NULL, 75.00, 8, 'GPA 3.7 and above', 1);

-- ============================================
-- Insert Sample Student Scholarships
-- ============================================
INSERT INTO `student_scholarships` (`student_id`, `scholarship_id`, `academic_year`, `start_semester`, `status`, `awarded_by`, `awarded_date`, `notes`) VALUES
(8, 1, '2025/2026', 1, 'active', 23, '2025-08-15', 'Awarded for excellent KCSE performance'),
(9, 2, '2025/2026', 1, 'active', 23, '2025-08-20', 'Merit-based scholarship'),
(19, 3, '2025/2026', 1, 'active', 23, '2025-08-18', 'National football team member');

-- ============================================
-- Insert Sample Invoices
-- ============================================
INSERT INTO `invoices` (`student_id`, `invoice_number`, `academic_year`, `semester`, `tuition_fee`, `registration_fee`, `library_fee`, `lab_fee`, `other_fees`, `total_amount`, `amount_paid`, `balance`, `status`, `due_date`, `generated_by`, `notes`) VALUES
-- Student 1 (James) - Full payment
(7, 'INV-2025-001', '2025/2026', 1, 2000.00, 100.00, 50.00, 150.00, 50.00, 2350.00, 2350.00, 0.00, 'paid', '2025-10-15', 23, 'Paid in full'),
-- Student 2 (Amina) - With scholarship (50% off)
(8, 'INV-2025-002', '2025/2026', 1, 1000.00, 100.00, 50.00, 150.00, 50.00, 1350.00, 1350.00, 0.00, 'paid', '2025-10-15', 23, '50% scholarship applied'),
-- Student 3 (Daniel) - Partial payment
(9, 'INV-2025-003', '2025/2026', 1, 2000.00, 100.00, 50.00, 150.00, 50.00, 2350.00, 1500.00, 850.00, 'partial', '2025-10-15', 23, 'Partial payment received'),
-- Student 4 (Fatuma) - Pending
(10, 'INV-2025-004', '2025/2026', 1, 2000.00, 100.00, 50.00, 150.00, 50.00, 2350.00, 0.00, 2350.00, 'pending', '2025-10-15', 23, 'Payment pending'),
-- Student 5 (Michael) - Business student
(11, 'INV-2025-005', '2025/2026', 1, 1800.00, 100.00, 50.00, 100.00, 50.00, 2100.00, 2100.00, 0.00, 'paid', '2025-10-15', 23, 'Paid in full'),
-- Student 13 (Patrick) - Year 2 student
(19, 'INV-2025-013', '2025/2026', 1, 2000.00, 100.00, 50.00, 150.00, 50.00, 2350.00, 1000.00, 1350.00, 'partial', '2025-10-15', 23, 'Installment plan'),
-- Student 14 (Agnes) - Year 2 student
(20, 'INV-2025-014', '2025/2026', 1, 2000.00, 100.00, 50.00, 150.00, 50.00, 2350.00, 0.00, 2350.00, 'overdue', '2025-09-30', 23, 'Payment overdue');

-- ============================================
-- Insert Sample Payments
-- ============================================
INSERT INTO `payments` (`invoice_id`, `student_id`, `payment_reference`, `amount`, `payment_method`, `payment_date`, `transaction_id`, `received_by`, `receipt_number`, `notes`) VALUES
-- Student 1 full payment
(1, 7, 'PAY-2025-001', 2350.00, 'bank_transfer', '2025-09-10', 'TXN123456789', 23, 'RCP-001', 'Bank transfer from CRDB'),
-- Student 2 full payment (with scholarship)
(2, 8, 'PAY-2025-002', 1350.00, 'mobile_money', '2025-09-12', 'MM987654321', 23, 'RCP-002', 'M-Pesa payment'),
-- Student 3 partial payment
(3, 9, 'PAY-2025-003', 1500.00, 'cash', '2025-09-15', NULL, 23, 'RCP-003', 'Cash payment - first installment'),
-- Student 5 full payment
(5, 11, 'PAY-2025-005', 2100.00, 'bank_transfer', '2025-09-08', 'TXN111222333', 23, 'RCP-005', 'NMB Bank transfer'),
-- Student 13 partial payment
(6, 19, 'PAY-2025-013', 1000.00, 'mobile_money', '2025-09-20', 'MM555666777', 23, 'RCP-013', 'Tigo Pesa - first installment');

-- ============================================
-- Insert Financial Clearance Records
-- ============================================
INSERT INTO `financial_clearance` (`student_id`, `academic_year`, `semester`, `total_fees`, `total_paid`, `balance`, `clearance_status`, `cleared_by`, `cleared_date`, `can_register_courses`, `can_take_exams`, `can_graduate`) VALUES
-- Cleared students
(7, '2025/2026', 1, 2350.00, 2350.00, 0.00, 'cleared', 23, '2025-09-10', 1, 1, 1),
(8, '2025/2026', 1, 1350.00, 1350.00, 0.00, 'cleared', 23, '2025-09-12', 1, 1, 1),
(11, '2025/2026', 1, 2100.00, 2100.00, 0.00, 'cleared', 23, '2025-09-08', 1, 1, 1),
-- Partially cleared (can register but not take exams)
(9, '2025/2026', 1, 2350.00, 1500.00, 850.00, 'pending', NULL, NULL, 1, 0, 0),
(19, '2025/2026', 1, 2350.00, 1000.00, 1350.00, 'pending', NULL, NULL, 1, 0, 0),
-- Not cleared
(10, '2025/2026', 1, 2350.00, 0.00, 2350.00, 'not_cleared', NULL, NULL, 0, 0, 0),
(20, '2025/2026', 1, 2350.00, 0.00, 2350.00, 'not_cleared', NULL, NULL, 0, 0, 0);

-- ============================================
-- End of Financial Sample Data
-- ============================================
