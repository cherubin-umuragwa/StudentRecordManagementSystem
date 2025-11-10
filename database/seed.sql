-- ============================================
-- Student Grade Management System
-- Sample Data (Seeds)
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
('School of Arts & Humanities', 'ART', 'Arts, Languages, and Social Sciences');

-- ============================================
-- Insert Sample Departments
-- ============================================
INSERT INTO `departments` (`school_id`, `name`, `code`, `description`) VALUES
(1, 'Computer Science & Engineering', 'CSE', 'Software and Computer Systems'),
(1, 'Electrical Engineering', 'ELE', 'Electrical and Electronics'),
(1, 'Mechanical Engineering', 'MEC', 'Mechanical Systems and Design'),
(2, 'Business Administration', 'BBA', 'General Business Management'),
(2, 'Accounting & Finance', 'ACF', 'Financial Management and Accounting'),
(3, 'Teacher Education', 'TED', 'Primary and Secondary Education'),
(4, 'Nursing', 'NUR', 'Nursing and Patient Care'),
(5, 'English Literature', 'ENG', 'English Language and Literature');

-- ============================================
-- Insert Sample Programs
-- ============================================
INSERT INTO `programs` (`department_id`, `name`, `code`, `duration_years`, `total_credits`, `tuition_per_semester`) VALUES
(1, 'Bachelor of Science in Computer Science', 'BSC-CS', 4, 120, 2000.00),
(1, 'Bachelor of Science in Software Engineering', 'BSC-SE', 4, 120, 2000.00),
(2, 'Bachelor of Engineering in Electrical Engineering', 'BE-EE', 4, 128, 2200.00),
(3, 'Bachelor of Engineering in Mechanical Engineering', 'BE-ME', 4, 128, 2200.00),
(4, 'Bachelor of Business Administration', 'BBA', 3, 90, 1800.00),
(5, 'Bachelor of Science in Accounting', 'BSC-ACC', 4, 120, 1900.00),
(6, 'Bachelor of Education', 'BED', 4, 120, 1500.00),
(7, 'Bachelor of Science in Nursing', 'BSC-NUR', 4, 130, 2500.00),
(8, 'Bachelor of Arts in English', 'BA-ENG', 3, 90, 1600.00);

-- ============================================
-- Insert Default Users
-- ============================================
-- Password for all users: password123
INSERT INTO `users` (`username`, `password`, `email`, `role`, `first_name`, `last_name`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@school.edu', 'admin', 'System', 'Administrator'),
('registrar', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'registrar@school.edu', 'registrar', 'Academic', 'Registrar');

-- ============================================
-- Insert Sample Subjects
-- ============================================
INSERT INTO `subjects` (`name`, `code`, `description`) VALUES
('English', 'ENG_01', 'English Subject'),
('Mathematics', 'MATH_01', 'Mathematics Subject'),
('Science', 'SCI_01', 'Science Subject');

-- ============================================
-- Insert Sample Courses for BSc Computer Science
-- ============================================
INSERT INTO `courses` (`program_id`, `code`, `name`, `description`, `credits`, `year_level`, `semester`, `is_elective`, `max_students`) VALUES
-- Year 1, Semester 1
(1, 'CS101', 'Introduction to Programming', 'Fundamentals of programming using Python', 4, 1, 1, 0, 50),
(1, 'CS102', 'Computer Organization', 'Basic computer architecture and organization', 3, 1, 1, 0, 50),
(1, 'MATH101', 'Calculus I', 'Differential and integral calculus', 4, 1, 1, 0, 50),
(1, 'ENG101', 'English Communication', 'Academic writing and communication skills', 3, 1, 1, 0, 50),
(1, 'PHY101', 'Physics I', 'Mechanics and thermodynamics', 3, 1, 1, 0, 50),

-- Year 1, Semester 2
(1, 'CS103', 'Data Structures', 'Arrays, linked lists, trees, graphs', 4, 1, 2, 0, 50),
(1, 'CS104', 'Object-Oriented Programming', 'OOP concepts using Java', 4, 1, 2, 0, 50),
(1, 'MATH102', 'Calculus II', 'Multivariable calculus', 4, 1, 2, 0, 50),
(1, 'STAT101', 'Statistics', 'Probability and statistics', 3, 1, 2, 0, 50),
(1, 'PHY102', 'Physics II', 'Electricity and magnetism', 3, 1, 2, 0, 50),

-- Year 2, Semester 1
(1, 'CS201', 'Algorithms', 'Algorithm design and analysis', 4, 2, 1, 0, 45),
(1, 'CS202', 'Database Systems', 'Relational databases and SQL', 4, 2, 1, 0, 45),
(1, 'CS203', 'Computer Networks', 'Network protocols and architecture', 3, 2, 1, 0, 45),
(1, 'MATH201', 'Discrete Mathematics', 'Logic, sets, and graph theory', 3, 2, 1, 0, 45),
(1, 'CS204', 'Web Development', 'HTML, CSS, JavaScript, PHP', 3, 2, 1, 1, 45),

-- Year 2, Semester 2
(1, 'CS205', 'Operating Systems', 'OS concepts and implementation', 4, 2, 2, 0, 45),
(1, 'CS206', 'Software Engineering', 'Software development lifecycle', 4, 2, 2, 0, 45),
(1, 'CS207', 'Computer Graphics', 'Graphics programming and visualization', 3, 2, 2, 1, 45),
(1, 'CS208', 'Mobile App Development', 'Android and iOS development', 3, 2, 2, 1, 45),

-- Year 3, Semester 1
(1, 'CS301', 'Artificial Intelligence', 'AI concepts and machine learning', 4, 3, 1, 0, 40),
(1, 'CS302', 'Computer Security', 'Cryptography and network security', 3, 3, 1, 0, 40),
(1, 'CS303', 'Cloud Computing', 'Cloud platforms and services', 3, 3, 1, 1, 40),
(1, 'CS304', 'Big Data Analytics', 'Data mining and analytics', 3, 3, 1, 1, 40),

-- Year 3, Semester 2
(1, 'CS305', 'Distributed Systems', 'Distributed computing concepts', 4, 3, 2, 0, 40),
(1, 'CS306', 'Human-Computer Interaction', 'UI/UX design principles', 3, 3, 2, 1, 40),
(1, 'CS307', 'Blockchain Technology', 'Blockchain and cryptocurrencies', 3, 3, 2, 1, 40),

-- Year 4, Semester 1
(1, 'CS401', 'Final Year Project I', 'Research and project development', 6, 4, 1, 0, 30),
(1, 'CS402', 'Advanced Topics in CS', 'Current trends in computer science', 3, 4, 1, 1, 30),

-- Year 4, Semester 2
(1, 'CS403', 'Final Year Project II', 'Project completion and presentation', 6, 4, 2, 0, 30),
(1, 'CS404', 'Professional Ethics', 'Ethics in computing', 2, 4, 2, 0, 30);

-- ============================================
-- Insert Sample Course Schedules
-- ============================================
INSERT INTO `course_schedule` (`course_id`, `day_of_week`, `start_time`, `end_time`, `room`, `building`) VALUES
(1, 'Monday', '08:00:00', '10:00:00', 'Lab 101', 'Computer Science Building'),
(1, 'Wednesday', '08:00:00', '10:00:00', 'Lab 101', 'Computer Science Building'),
(2, 'Tuesday', '10:00:00', '12:00:00', 'Room 201', 'Computer Science Building'),
(2, 'Thursday', '10:00:00', '12:00:00', 'Room 201', 'Computer Science Building'),
(3, 'Monday', '14:00:00', '16:00:00', 'Room 301', 'Mathematics Building'),
(3, 'Friday', '14:00:00', '16:00:00', 'Room 301', 'Mathematics Building');
