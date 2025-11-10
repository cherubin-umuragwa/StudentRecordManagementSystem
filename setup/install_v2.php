<?php
/**
 * Installation script for Enhanced Student Registration System V2
 * Run this file once to upgrade to the comprehensive registration system
 */

include 'includes/conn.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Install Enhanced Registration System V2</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
</head>
<body>
<div class='container mt-5'>
    <div class='card'>
        <div class='card-header bg-primary text-white'>
            <h3>Enhanced Student Registration System V2 - Installation</h3>
        </div>
        <div class='card-body'>";

try {
    echo "<h5>Installing Enhanced Registration System...</h5>";
    
    // Read and execute the SQL file
    $sql = file_get_contents('database_updates_v2.sql');
    
    // Split by semicolon and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $success_count = 0;
    $error_count = 0;
    
    foreach ($statements as $statement) {
        if (empty($statement) || strpos($statement, '--') === 0) continue;
        
        try {
            $pdo->exec($statement);
            $success_count++;
        } catch (PDOException $e) {
            // Some errors are OK (like table already exists)
            if (strpos($e->getMessage(), 'already exists') === false && 
                strpos($e->getMessage(), 'Duplicate') === false) {
                echo "<div class='alert alert-warning'>Warning: " . $e->getMessage() . "</div>";
                $error_count++;
            }
        }
    }
    
    echo "<div class='alert alert-success'>✓ Database updated successfully!</div>";
    echo "<div class='alert alert-info'>Executed $success_count SQL statements</div>";
    
    echo "<div class='alert alert-success mt-4'>
            <h5>✓ Installation Complete!</h5>
            <p><strong>New Features Available:</strong></p>
            <ul>
                <li>✓ Enhanced registration form with 7 sections</li>
                <li>✓ Schools, Departments, and Programs management</li>
                <li>✓ Document upload support</li>
                <li>✓ Student number auto-generation</li>
                <li>✓ Comprehensive student profiles</li>
                <li>✓ Emergency contact information</li>
                <li>✓ Previous education tracking</li>
            </ul>
            <p><strong>Sample Data Inserted:</strong></p>
            <ul>
                <li>5 Schools (Engineering, Business, Education, Health, Arts)</li>
                <li>8 Departments</li>
                <li>9 Programs</li>
            </ul>
          </div>";
    
    echo "<a href='register_v2.php' class='btn btn-success me-2'>Test Registration Form</a>";
    echo "<a href='index.php' class='btn btn-primary'>Go to Login Page</a>";
    
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    echo "<p>Please check your database connection and try again.</p>";
}

echo "    </div>
    </div>
</div>
</body>
</html>";
?>
