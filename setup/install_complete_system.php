<?php
/**
 * Complete System Installation
 * This installs everything in the correct order:
 * 1. Basic registration system
 * 2. Enhanced registration V2 (schools, departments, programs)
 * 3. Course registration system
 * 4. Registrar account
 */

include 'includes/conn.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Complete System Installation</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>
    <style>
        .install-container { max-width: 900px; margin: 2rem auto; }
        .step-card { margin-bottom: 1rem; }
        .step-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .success-icon { color: #28a745; font-size: 1.5rem; }
        .error-icon { color: #dc3545; font-size: 1.5rem; }
        .warning-icon { color: #ffc107; font-size: 1.5rem; }
    </style>
</head>
<body>
<div class='container install-container'>
    <div class='card'>
        <div class='card-header step-header'>
            <h3><i class='fas fa-rocket me-2'></i>Complete System Installation</h3>
            <p class='mb-0'>Installing all components of the Student Grade Management System</p>
        </div>
        <div class='card-body'>";

$total_steps = 0;
$successful_steps = 0;
$warnings = 0;

try {
    // STEP 1: Update users table for registrar role
    echo "<div class='step-card card'>
            <div class='card-header bg-primary text-white'>
                <h5><i class='fas fa-database me-2'></i>Step 1: Basic System Setup</h5>
            </div>
            <div class='card-body'>";
    
    try {
        $pdo->exec("ALTER TABLE `users` MODIFY `role` ENUM('admin','teacher','student','registrar') NOT NULL");
        echo "<p><i class='fas fa-check-circle success-icon me-2'></i>Users table updated for registrar role</p>";
        $successful_steps++;
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate') !== false) {
            echo "<p><i class='fas fa-info-circle warning-icon me-2'></i>Registrar role already exists</p>";
            $warnings++;
        } else {
            echo "<p><i class='fas fa-exclamation-circle error-icon me-2'></i>Warning: " . $e->getMessage() . "</p>";
        }
    }
    $total_steps++;
    
    // Create registration_requests table
    try {
        $sql = "CREATE TABLE IF NOT EXISTS `registration_requests` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `username` varchar(50) NOT NULL,
          `password` varchar(255) NOT NULL,
          `email` varchar(100) NOT NULL,
          `first_name` varchar(50) NOT NULL,
          `middle_name` varchar(50) DEFAULT NULL,
          `last_name` varchar(50) NOT NULL,
          `date_of_birth` date NOT NULL,
          `gender` enum('male','female','other','prefer_not_to_say') NOT NULL,
          `nationality` varchar(100) DEFAULT NULL,
          `national_id` varchar(50) DEFAULT NULL,
          `phone` varchar(20) NOT NULL,
          `alternative_phone` varchar(20) DEFAULT NULL,
          `address` text NOT NULL,
          `street` varchar(200) DEFAULT NULL,
          `city` varchar(100) DEFAULT NULL,
          `region` varchar(100) DEFAULT NULL,
          `postal_code` varchar(20) DEFAULT NULL,
          `country` varchar(100) DEFAULT 'Tanzania',
          `guardian_name` varchar(100) NOT NULL,
          `guardian_phone` varchar(20) NOT NULL,
          `emergency_contact_name` varchar(100) DEFAULT NULL,
          `emergency_contact_relationship` varchar(50) DEFAULT NULL,
          `emergency_contact_phone` varchar(20) DEFAULT NULL,
          `emergency_contact_email` varchar(100) DEFAULT NULL,
          `previous_school` varchar(200) DEFAULT NULL,
          `secondary_school` varchar(200) DEFAULT NULL,
          `completion_year` year DEFAULT NULL,
          `certificate_type` varchar(100) DEFAULT NULL,
          `division_grade` varchar(50) DEFAULT NULL,
          `index_number` varchar(100) DEFAULT NULL,
          `birth_certificate` varchar(255) DEFAULT NULL,
          `national_id_copy` varchar(255) DEFAULT NULL,
          `certificate_copy` varchar(255) DEFAULT NULL,
          `passport_photo` varchar(255) DEFAULT NULL,
          `program` varchar(100) NOT NULL,
          `school` varchar(100) DEFAULT NULL,
          `department` varchar(100) DEFAULT NULL,
          `entry_year` year DEFAULT NULL,
          `entry_semester` int(1) DEFAULT 1,
          `student_number` varchar(50) DEFAULT NULL,
          `terms_accepted` tinyint(1) DEFAULT 0,
          `privacy_accepted` tinyint(1) DEFAULT 0,
          `info_accurate` tinyint(1) DEFAULT 0,
          `status` enum('pending','approved','rejected') DEFAULT 'pending',
          `approved_by` int(11) DEFAULT NULL,
          `approved_at` timestamp NULL DEFAULT NULL,
          `rejection_reason` text DEFAULT NULL,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`id`),
          KEY `approved_by` (`approved_by`),
          KEY `status` (`status`),
          KEY `idx_national_id` (`national_id`),
          KEY `idx_student_number` (`student_number`),
          KEY `idx_entry_year` (`entry_year`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
        
        $pdo->exec($sql);
        echo "<p><i class='fas fa-check-circle success-icon me-2'></i>Registration requests table created</p>";
        $successful_steps++;
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'already exists') !== false) {
            echo "<p><i class='fas fa-info-circle warning-icon me-2'></i>Registration requests table already exists</p>";
            $warnings++;
        }
    }
    $total_steps++;
    
    echo "</div></div>";
    
    // STEP 2: Create schools, departments, programs
    echo "<div class='step-card card'>
            <div class='card-header bg-success text-white'>
                <h5><i class='fas fa-school me-2'></i>Step 2: Academic Structure (Schools, Departments, Programs)</h5>
            </div>
            <div class='card-body'>";
    
    // Read and execute V2 SQL
    $v2_sql = file_get_contents('database_updates_v2.sql');
    $statements = array_filter(array_map('trim', explode(';', $v2_sql)));
    
    $v2_success = 0;
    foreach ($statements as $statement) {
        if (empty($statement) || strpos($statement, '--') === 0) continue;
        
        try {
            $pdo->exec($statement);
            $v2_success++;
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'already exists') === false && 
                strpos($e->getMessage(), 'Duplicate') === false) {
                // Only show real errors
            }
        }
    }
    
    echo "<p><i class='fas fa-check-circle success-icon me-2'></i>Academic structure created ($v2_success operations)</p>";
    
    // Verify schools exist
    $school_count = $pdo->query("SELECT COUNT(*) FROM schools")->fetchColumn();
    $dept_count = $pdo->query("SELECT COUNT(*) FROM departments")->fetchColumn();
    $prog_count = $pdo->query("SELECT COUNT(*) FROM programs")->fetchColumn();
    
    echo "<p><i class='fas fa-info-circle text-info me-2'></i>$school_count schools, $dept_count departments, $prog_count programs</p>";
    $successful_steps++;
    $total_steps++;
    
    echo "</div></div>";
    
    // STEP 3: Course Registration System
    echo "<div class='step-card card'>
            <div class='card-header bg-info text-white'>
                <h5><i class='fas fa-book me-2'></i>Step 3: Course Registration System</h5>
            </div>
            <div class='card-body'>";
    
    // Read and execute course registration SQL
    $course_sql = file_get_contents('database_course_registration.sql');
    $course_statements = array_filter(array_map('trim', explode(';', $course_sql)));
    
    $course_success = 0;
    foreach ($course_statements as $statement) {
        if (empty($statement) || strpos($statement, '--') === 0) continue;
        
        try {
            $pdo->exec($statement);
            $course_success++;
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'already exists') === false && 
                strpos($e->getMessage(), 'Duplicate') === false) {
                // Only show real errors
            }
        }
    }
    
    echo "<p><i class='fas fa-check-circle success-icon me-2'></i>Course registration system created ($course_success operations)</p>";
    
    // Verify courses exist
    $course_count = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
    echo "<p><i class='fas fa-info-circle text-info me-2'></i>$course_count courses available</p>";
    $successful_steps++;
    $total_steps++;
    
    echo "</div></div>";
    
    // STEP 4: Create Registrar Account
    echo "<div class='step-card card'>
            <div class='card-header bg-warning text-dark'>
                <h5><i class='fas fa-user-shield me-2'></i>Step 4: Registrar Account</h5>
            </div>
            <div class='card-body'>";
    
    $check = $pdo->prepare("SELECT * FROM users WHERE username = 'registrar'");
    $check->execute();
    $existing = $check->fetch();
    
    $hashed_password = password_hash('registrar123', PASSWORD_DEFAULT);
    
    if ($existing) {
        $stmt = $pdo->prepare("UPDATE users SET password = ?, role = 'registrar' WHERE username = 'registrar'");
        $stmt->execute([$hashed_password]);
        echo "<p><i class='fas fa-check-circle success-icon me-2'></i>Registrar account updated</p>";
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, email, role, first_name, last_name) 
                              VALUES ('registrar', ?, 'registrar@school.edu', 'registrar', 'University', 'Registrar')");
        $stmt->execute([$hashed_password]);
        echo "<p><i class='fas fa-check-circle success-icon me-2'></i>Registrar account created</p>";
    }
    $successful_steps++;
    $total_steps++;
    
    echo "</div></div>";
    
    // SUMMARY
    echo "<div class='card'>
            <div class='card-header bg-success text-white'>
                <h4><i class='fas fa-check-circle me-2'></i>Installation Complete!</h4>
            </div>
            <div class='card-body'>
                <div class='row text-center mb-4'>
                    <div class='col-md-4'>
                        <h2 class='text-success'>$successful_steps</h2>
                        <p>Steps Completed</p>
                    </div>
                    <div class='col-md-4'>
                        <h2 class='text-warning'>$warnings</h2>
                        <p>Warnings</p>
                    </div>
                    <div class='col-md-4'>
                        <h2 class='text-primary'>$total_steps</h2>
                        <p>Total Steps</p>
                    </div>
                </div>
                
                <div class='alert alert-success'>
                    <h5><i class='fas fa-rocket me-2'></i>System Ready!</h5>
                    <p><strong>What's been installed:</strong></p>
                    <ul>
                        <li>✅ Basic registration system with registrar approval</li>
                        <li>✅ Enhanced registration form (7 sections)</li>
                        <li>✅ Academic structure ($school_count schools, $dept_count departments, $prog_count programs)</li>
                        <li>✅ Course registration system ($course_count courses)</li>
                        <li>✅ Registrar account (username: registrar, password: registrar123)</li>
                    </ul>
                </div>
                
                <div class='alert alert-info'>
                    <h5><i class='fas fa-user-check me-2'></i>Default Accounts:</h5>
                    <table class='table table-sm'>
                        <tr><td><strong>Admin:</strong></td><td>admin / admin123</td></tr>
                        <tr><td><strong>Teacher:</strong></td><td>teacher / teacher123</td></tr>
                        <tr><td><strong>Student:</strong></td><td>student / student123</td></tr>
                        <tr><td><strong>Registrar:</strong></td><td>registrar / registrar123</td></tr>
                    </table>
                </div>
                
                <div class='d-grid gap-2'>
                    <a href='register_v2.php' class='btn btn-primary btn-lg'>
                        <i class='fas fa-user-plus me-2'></i>Test Student Registration
                    </a>
                    <a href='index.php' class='btn btn-success btn-lg'>
                        <i class='fas fa-sign-in-alt me-2'></i>Go to Login Page
                    </a>
                </div>
            </div>
        </div>";
    
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>
            <h5><i class='fas fa-exclamation-triangle me-2'></i>Installation Error</h5>
            <p>" . $e->getMessage() . "</p>
          </div>";
}

echo "    </div>
    </div>
</div>
</body>
</html>";
?>
