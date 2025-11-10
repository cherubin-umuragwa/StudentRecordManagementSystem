<?php
$student_number = isset($_GET['student_number']) ? $_GET['student_number'] : '';
$username = isset($_GET['username']) ? $_GET['username'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Successful</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
</head>
<body class="success-page">
    <div class="success-card">
        <i class="fas fa-check-circle success-icon"></i>
        <h2 class="mb-3">Registration Successful!</h2>
        <p class="lead">Thank you for registering with our institution.</p>
        
        <div class="student-details">
            <h5 class="mb-3">Your Registration Details:</h5>
            <p><strong>Student Number:</strong> <?php echo htmlspecialchars($student_number); ?></p>
            <p><strong>Username:</strong> <?php echo htmlspecialchars($username); ?></p>
            <p class="mb-0"><strong>Status:</strong> <span class="badge bg-warning">Pending Approval</span></p>
        </div>
        
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            <strong>What's Next?</strong>
            <p class="mb-0 mt-2">Your application is currently under review by the Registrar's Office. 
            You will receive an email notification once your application is approved.</p>
            <p class="mb-0 mt-2"><strong>Expected approval time:</strong> 2-3 business days</p>
        </div>
        
        <div class="alert alert-success">
            <i class="fas fa-envelope me-2"></i>
            A confirmation email has been sent to your registered email address with further instructions.
        </div>
        
        <div class="mt-4">
            <a href="index.php" class="btn btn-primary btn-lg">
                <i class="fas fa-sign-in-alt me-2"></i>Go to Login Page
            </a>
        </div>
        
        <div class="mt-3">
            <small class="text-muted">
                If you have any questions, please contact:<br>
                <i class="fas fa-envelope"></i> registrar@institution.edu<br>
                <i class="fas fa-phone"></i> +256 123 456 789
            </small>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/script.js"></script>
</body>
</html>
