<?php
/**
 * Installation script for Student Registration Feature
 * Run this file once to set up the registration system
 */

include 'includes/conn.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Install Registration Feature</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
</head>
<body>
<div class='container mt-5'>
    <div class='card'>
        <div class='card-header bg-primary text-white'>
            <h3>Student Registration Feature Installation</h3>
        </div>
        <div class='card-body'>";

try {
    // Step 1: Update users table to add registrar role
    echo "<h5>Step 1: Updating users table...</h5>";
    $pdo->exec("ALTER TABLE `users` MODIFY `role` ENUM('admin','teacher','student','registrar') NOT NULL");
    echo "<div class='alert alert-success'>✓ Users table updated successfully</div>";
    
    // Step 2: Create registration_requests table
    echo "<h5>Step 2: Creating registration_requests table...</h5>";
    $sql = "CREATE TABLE IF NOT EXISTS `registration_requests` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `username` varchar(50) NOT NULL,
      `password` varchar(255) NOT NULL,
      `email` varchar(100) NOT NULL,
      `first_name` varchar(50) NOT NULL,
      `last_name` varchar(50) NOT NULL,
      `date_of_birth` date NOT NULL,
      `gender` enum('male','female','other') NOT NULL,
      `phone` varchar(20) NOT NULL,
      `address` text NOT NULL,
      `guardian_name` varchar(100) NOT NULL,
      `guardian_phone` varchar(20) NOT NULL,
      `previous_school` varchar(200) DEFAULT NULL,
      `program` varchar(100) NOT NULL,
      `status` enum('pending','approved','rejected') DEFAULT 'pending',
      `approved_by` int(11) DEFAULT NULL,
      `approved_at` timestamp NULL DEFAULT NULL,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      KEY `approved_by` (`approved_by`),
      KEY `status` (`status`),
      CONSTRAINT `registration_requests_ibfk_1` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    $pdo->exec($sql);
    echo "<div class='alert alert-success'>✓ Registration requests table created successfully</div>";
    
    // Step 3: Insert default registrar account
    echo "<h5>Step 3: Creating default registrar account...</h5>";
    
    // Check if registrar already exists
    $check = $pdo->prepare("SELECT * FROM users WHERE username = 'registrar'");
    $check->execute();
    $existing = $check->fetch();
    
    // Password: registrar123
    $hashed_password = password_hash('registrar123', PASSWORD_DEFAULT);
    
    if ($existing) {
        // Update existing registrar
        $stmt = $pdo->prepare("UPDATE users SET password = ?, role = 'registrar', email = ?, first_name = 'University', last_name = 'Registrar' WHERE username = 'registrar'");
        $stmt->execute([$hashed_password, 'registrar@school.edu']);
    } else {
        // Create new registrar
        $stmt = $pdo->prepare("INSERT INTO `users` (`username`, `password`, `email`, `role`, `first_name`, `last_name`) 
                              VALUES ('registrar', ?, 'registrar@school.edu', 'registrar', 'University', 'Registrar')");
        $stmt->execute([$hashed_password]);
    }
    
    echo "<div class='alert alert-success'>✓ Default registrar account created</div>";
    
    echo "<div class='alert alert-info mt-4'>
            <h5>Installation Complete!</h5>
            <p><strong>Default Registrar Credentials:</strong></p>
            <ul>
                <li>Username: <code>registrar</code></li>
                <li>Password: <code>registrar123</code></li>
            </ul>
            <p>You can now:</p>
            <ul>
                <li>Students can register at: <a href='register.php'>register.php</a></li>
                <li>Registrar can login at: <a href='index.php'>index.php</a></li>
            </ul>
          </div>";
    
    echo "<a href='index.php' class='btn btn-primary'>Go to Login Page</a>";
    
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    echo "<p>If you see an error about the table already existing or column already modified, the installation may have already been completed.</p>";
}

echo "    </div>
    </div>
</div>
</body>
</html>";
?>
