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
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .success-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            padding: 3rem;
            max-width: 600px;
            text-align: center;
        }
        .success-icon {
            font-size: 5rem;
            color: #28a745;
            margin-bottom: 1rem;
        }
        .student-details {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            margin: 2rem 0;
            text-align: left;
        }
    </style>
</head>
<body>
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
                <i class="fas fa-phone"></i> +255 123 456 789
            </small>
        </div>
    </div>
</body>
</html>
