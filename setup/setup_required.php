<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Required</title>
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
        .setup-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            padding: 3rem;
            max-width: 600px;
            text-align: center;
        }
        .setup-icon {
            font-size: 5rem;
            color: #ffc107;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="setup-card">
        <i class="fas fa-exclamation-triangle setup-icon"></i>
        <h2 class="mb-3">Setup Required</h2>
        <p class="lead">The enhanced registration system needs to be installed first.</p>
        
        <div class="alert alert-warning text-start">
            <h5><i class="fas fa-info-circle me-2"></i>What's Missing?</h5>
            <p>The database tables for the enhanced registration system (V2) haven't been created yet.</p>
            <p class="mb-0">This includes:</p>
            <ul>
                <li>Schools table</li>
                <li>Departments table</li>
                <li>Programs table</li>
                <li>Enhanced registration fields</li>
            </ul>
        </div>
        
        <div class="alert alert-info text-start">
            <h5><i class="fas fa-rocket me-2"></i>Quick Setup</h5>
            <p class="mb-0">Click the button below to automatically install all required tables and sample data.</p>
        </div>
        
        <div class="d-grid gap-2 mt-4">
            <a href="install_v2.php" class="btn btn-primary btn-lg">
                <i class="fas fa-download me-2"></i>Install Enhanced Registration System
            </a>
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Login
            </a>
        </div>
        
        <div class="mt-4">
            <small class="text-muted">
                <strong>Note:</strong> This is a one-time setup that takes less than a minute.
            </small>
        </div>
    </div>
</body>
</html>
