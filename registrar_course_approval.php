<?php
include 'includes/conn.php';
include 'includes/functions.php';
requireRole('registrar');

$message = '';
$message_type = '';

// Handle approval
if (isset($_GET['action']) && isset($_GET['id'])) {
    $registration_id = $_GET['id'];
    $action = $_GET['action'];
    
    if ($action === 'approve') {
        $stmt = $pdo->prepare("UPDATE course_registrations 
                              SET status = 'approved', approved_by = ?, approved_at = NOW() 
                              WHERE id = ?");
        $stmt->execute([$_SESSION['user_id'], $registration_id]);
        $message = "Course registration approved successfully!";
        $message_type = "success";
    } elseif ($action === 'reject') {
        $reason = isset($_POST['rejection_reason']) ? $_POST['rejection_reason'] : 'Not specified';
        $stmt = $pdo->prepare("UPDATE course_registrations 
                              SET status = 'rejected', approved_by = ?, approved_at = NOW(), rejection_reason = ? 
                              WHERE id = ?");
        $stmt->execute([$_SESSION['user_id'], $reason, $registration_id]);
        $message = "Course registration rejected.";
        $message_type = "warning";
    }
}

// Get pending course registrations
$pending_registrations = $pdo->query("
    SELECT cr.*, 
           u.first_name, u.last_name, u.username, u.student_number,
           c.code as course_code, c.name as course_name, c.credits, c.max_students,
           p.name as program_name,
           (SELECT COUNT(*) FROM course_registrations 
            WHERE course_id = cr.course_id AND status = 'approved' 
            AND academic_year = cr.academic_year AND semester = cr.semester) as enrolled_count
    FROM course_registrations cr
    JOIN users u ON cr.student_id = u.id
    JOIN courses c ON cr.course_id = c.id
    LEFT JOIN programs p ON u.program_id = p.id
    WHERE cr.status = 'pending'
    ORDER BY cr.registration_date DESC
")->fetchAll();

// Get approved registrations (recent)
$approved_registrations = $pdo->query("
    SELECT cr.*, 
           u.first_name, u.last_name, u.username, u.student_number,
           c.code as course_code, c.name as course_name, c.credits,
           approver.first_name as approver_first, approver.last_name as approver_last
    FROM course_registrations cr
    JOIN users u ON cr.student_id = u.id
    JOIN courses c ON cr.course_id = c.id
    LEFT JOIN users approver ON cr.approved_by = approver.id
    WHERE cr.status = 'approved'
    ORDER BY cr.approved_at DESC
    LIMIT 20
")->fetchAll();

// Get rejected registrations (recent)
$rejected_registrations = $pdo->query("
    SELECT cr.*, 
           u.first_name, u.last_name, u.username, u.student_number,
           c.code as course_code, c.name as course_name, c.credits,
           approver.first_name as approver_first, approver.last_name as approver_last
    FROM course_registrations cr
    JOIN users u ON cr.student_id = u.id
    JOIN courses c ON cr.course_id = c.id
    LEFT JOIN users approver ON cr.approved_by = approver.id
    WHERE cr.status = 'rejected'
    ORDER BY cr.approved_at DESC
    LIMIT 20
")->fetchAll();

// Statistics
$total_pending = count($pending_registrations);
$total_approved = $pdo->query("SELECT COUNT(*) FROM course_registrations WHERE status = 'approved'")->fetchColumn();
$total_rejected = $pdo->query("SELECT COUNT(*) FROM course_registrations WHERE status = 'rejected'")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Registration Approval - Registrar Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            background: linear-gradient(135deg, #e83e8c 0%, #dc3545 100%);
            color: white;
            min-height: 100vh;
            padding: 0;
        }
        .sidebar .nav-link {
            color: white;
            padding: 1rem 1.5rem;
            border-left: 4px solid transparent;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: rgba(255,255,255,0.1);
            border-left-color: white;
        }
        .stat-card {
            border-radius: 10px;
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .registration-card {
            border-left: 4px solid #667eea;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar">
                <div class="d-flex flex-column p-3">
                    <div class="text-center mb-4">
                        <i class="fas fa-user-check fa-2x mb-2"></i>
                        <h5>Registrar Portal</h5>
                        <small><?php echo $_SESSION['first_name'] . ' ' . $_SESSION['last_name']; ?></small>
                    </div>
                    
                    <ul class="nav nav-pills flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="registrar.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="registrar.php#pending">
                                <i class="fas fa-user-graduate me-2"></i>Student Registrations
                                <?php 
                                $pending_students = $pdo->query("SELECT COUNT(*) FROM registration_requests WHERE status = 'pending'")->fetchColumn();
                                if ($pending_students > 0): 
                                ?>
                                <span class="badge bg-warning"><?php echo $pending_students; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="registrar_course_approval.php">
                                <i class="fas fa-book me-2"></i>Course Approvals
                                <?php if ($total_pending > 0): ?>
                                <span class="badge bg-warning"><?php echo $total_pending; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 ms-sm-auto px-4 py-4">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Course Registration Approvals</h2>
                    <span>Welcome, <?php echo $_SESSION['first_name']; ?>!</span>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card stat-card bg-warning text-dark">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?php echo $total_pending; ?></h4>
                                        <p>Pending Approvals</p>
                                    </div>
                                    <i class="fas fa-clock fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stat-card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?php echo $total_approved; ?></h4>
                                        <p>Approved</p>
                                    </div>
                                    <i class="fas fa-check-circle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stat-card bg-danger text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?php echo $total_rejected; ?></h4>
                                        <p>Rejected</p>
                                    </div>
                                    <i class="fas fa-times-circle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <ul class="nav nav-tabs mb-4" id="approvalTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button">
                            <i class="fas fa-clock me-2"></i>Pending (<?php echo $total_pending; ?>)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved" type="button">
                            <i class="fas fa-check-circle me-2"></i>Approved
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="rejected-tab" data-bs-toggle="tab" data-bs-target="#rejected" type="button">
                            <i class="fas fa-times-circle me-2"></i>Rejected
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="approvalTabsContent">
                    <!-- Pending Tab -->
                    <div class="tab-pane fade show active" id="pending" role="tabpanel">
                        <h4 class="mb-3">Pending Course Registrations</h4>
                        
                        <?php if (count($pending_registrations) > 0): ?>
                            <?php foreach ($pending_registrations as $reg): ?>
                            <div class="card registration-card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <h5><?php echo $reg['first_name'] . ' ' . $reg['last_name']; ?></h5>
                                            <p class="mb-1">
                                                <strong>Student Number:</strong> <?php echo $reg['student_number'] ?? 'N/A'; ?><br>
                                                <strong>Username:</strong> <?php echo $reg['username']; ?><br>
                                                <strong>Program:</strong> <?php echo $reg['program_name'] ?? 'N/A'; ?>
                                            </p>
                                            <hr>
                                            <h6 class="text-primary">Course Details:</h6>
                                            <p class="mb-1">
                                                <strong>Course:</strong> <?php echo $reg['course_code']; ?> - <?php echo $reg['course_name']; ?><br>
                                                <strong>Credits:</strong> <?php echo $reg['credits']; ?><br>
                                                <strong>Academic Year:</strong> <?php echo $reg['academic_year']; ?><br>
                                                <strong>Semester:</strong> <?php echo $reg['semester']; ?><br>
                                                <strong>Enrollment:</strong> <?php echo $reg['enrolled_count']; ?> / <?php echo $reg['max_students']; ?> students
                                            </p>
                                            <p class="mb-0">
                                                <small class="text-muted">
                                                    <i class="fas fa-clock"></i> Registered: <?php echo date('M j, Y H:i', strtotime($reg['registration_date'])); ?>
                                                </small>
                                            </p>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <?php if ($reg['enrolled_count'] < $reg['max_students']): ?>
                                                <a href="?action=approve&id=<?php echo $reg['id']; ?>" 
                                                   class="btn btn-success mb-2 w-100"
                                                   onclick="return confirm('Approve this course registration?')">
                                                    <i class="fas fa-check"></i> Approve
                                                </a>
                                            <?php else: ?>
                                                <div class="alert alert-warning mb-2">
                                                    <i class="fas fa-exclamation-triangle"></i> Course is full!
                                                </div>
                                            <?php endif; ?>
                                            <button class="btn btn-danger w-100" data-bs-toggle="modal" 
                                                    data-bs-target="#rejectModal<?php echo $reg['id']; ?>">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Reject Modal -->
                            <div class="modal fade" id="rejectModal<?php echo $reg['id']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title">Reject Course Registration</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST" action="?action=reject&id=<?php echo $reg['id']; ?>">
                                            <div class="modal-body">
                                                <p><strong>Student:</strong> <?php echo $reg['first_name'] . ' ' . $reg['last_name']; ?></p>
                                                <p><strong>Course:</strong> <?php echo $reg['course_code']; ?> - <?php echo $reg['course_name']; ?></p>
                                                <div class="mb-3">
                                                    <label class="form-label">Rejection Reason:</label>
                                                    <textarea class="form-control" name="rejection_reason" rows="3" required></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-danger">Reject Registration</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>No pending course registrations at this time.
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Approved Tab -->
                    <div class="tab-pane fade" id="approved" role="tabpanel">
                        <h4 class="mb-3">Approved Course Registrations</h4>
                        
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Student Number</th>
                                        <th>Course</th>
                                        <th>Credits</th>
                                        <th>Academic Year</th>
                                        <th>Semester</th>
                                        <th>Approved By</th>
                                        <th>Approved Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($approved_registrations as $reg): ?>
                                    <tr>
                                        <td><?php echo $reg['first_name'] . ' ' . $reg['last_name']; ?></td>
                                        <td><?php echo $reg['student_number'] ?? 'N/A'; ?></td>
                                        <td><?php echo $reg['course_code']; ?> - <?php echo $reg['course_name']; ?></td>
                                        <td><?php echo $reg['credits']; ?></td>
                                        <td><?php echo $reg['academic_year']; ?></td>
                                        <td><?php echo $reg['semester']; ?></td>
                                        <td><?php echo $reg['approver_first'] . ' ' . $reg['approver_last']; ?></td>
                                        <td><?php echo date('M j, Y', strtotime($reg['approved_at'])); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Rejected Tab -->
                    <div class="tab-pane fade" id="rejected" role="tabpanel">
                        <h4 class="mb-3">Rejected Course Registrations</h4>
                        
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Student Number</th>
                                        <th>Course</th>
                                        <th>Reason</th>
                                        <th>Rejected By</th>
                                        <th>Rejected Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rejected_registrations as $reg): ?>
                                    <tr>
                                        <td><?php echo $reg['first_name'] . ' ' . $reg['last_name']; ?></td>
                                        <td><?php echo $reg['student_number'] ?? 'N/A'; ?></td>
                                        <td><?php echo $reg['course_code']; ?> - <?php echo $reg['course_name']; ?></td>
                                        <td><?php echo $reg['rejection_reason'] ?? 'Not specified'; ?></td>
                                        <td><?php echo $reg['approver_first'] . ' ' . $reg['approver_last']; ?></td>
                                        <td><?php echo date('M j, Y', strtotime($reg['approved_at'])); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
