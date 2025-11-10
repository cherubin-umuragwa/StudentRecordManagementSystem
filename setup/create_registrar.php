<?php
/**
 * Quick script to create/update the registrar account
 * Run this once to fix the registrar login issue
 */

include 'includes/conn.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Create Registrar Account</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
</head>
<body>
<div class='container mt-5'>
    <div class='card'>
        <div class='card-header bg-success text-white'>
            <h3>Create/Update Registrar Account</h3>
        </div>
        <div class='card-body'>";

try {
    // Generate correct password hash for "registrar123"
    $password = 'registrar123';
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    echo "<h5>Step 1: Checking if registrar exists...</h5>";
    
    // Check if registrar already exists
    $check = $pdo->prepare("SELECT * FROM users WHERE username = 'registrar'");
    $check->execute();
    $existing = $check->fetch();
    
    if ($existing) {
        echo "<div class='alert alert-info'>Registrar account found. Updating password...</div>";
        
        // Update existing registrar
        $stmt = $pdo->prepare("UPDATE users SET password = ?, role = 'registrar' WHERE username = 'registrar'");
        $stmt->execute([$hashed_password]);
        
        echo "<div class='alert alert-success'>✓ Registrar password updated successfully!</div>";
    } else {
        echo "<div class='alert alert-info'>Registrar account not found. Creating new account...</div>";
        
        // Create new registrar
        $stmt = $pdo->prepare("INSERT INTO users (username, password, email, role, first_name, last_name) 
                              VALUES ('registrar', ?, 'registrar@school.edu', 'registrar', 'University', 'Registrar')");
        $stmt->execute([$hashed_password]);
        
        echo "<div class='alert alert-success'>✓ Registrar account created successfully!</div>";
    }
    
    echo "<div class='alert alert-success mt-4'>
            <h5>✓ Setup Complete!</h5>
            <p><strong>Registrar Login Credentials:</strong></p>
            <ul>
                <li>Username: <code>registrar</code></li>
                <li>Password: <code>registrar123</code></li>
            </ul>
            <p>You can now login at the main page.</p>
          </div>";
    
    echo "<a href='index.php' class='btn btn-primary'>Go to Login Page</a>";
    
    // Display the hash for reference
    echo "<hr><small class='text-muted'>Password hash: " . $hashed_password . "</small>";
    
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
}

echo "    </div>
    </div>
</div>
</body>
</html>";
?>
