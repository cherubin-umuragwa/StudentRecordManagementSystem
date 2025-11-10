<?php
include 'includes/conn.php';
include 'includes/functions.php';
requireRole('registrar');

$message = '';
$message_type = '';

// Handle approval
if (isset($_GET['action']) && isset($_GET['id'])) {
    $request_id = $_GET['id'];
    $action = $_GET['action'];
    
    if ($action === 'approve') {
        try {
            $pdo->beginTransaction();
            
            // Get registration request
            $stmt = $pdo->prepare("SELECT * FROM registration_requests WHERE id = ? AND status = 'pending'");
            $stmt->execute([$request_id]);
            $request = $stmt->fetch();
            
            if ($request) {
                // Calculate expected graduation date (4 years from entry year)
                $entry_year = $request['entry_year'] ?? date('Y');
                $expected_graduation = ($entry_year + 4) . '-06-30'; // June 30th, 4 years later
                
                // Create user account with complete information
                $user_stmt = $pdo->prepare("INSERT INTO users 
                    (username, password, email, role, first_name, middle_name, last_name, 
                     student_number, nationality, national_id, phone, alternative_phone, 
                     date_of_birth, gender, address, city, region, postal_code, country,
                     program_id, current_year, current_semester, enrollment_date, 
                     expected_graduation, academic_status) 
                    VALUES (?, ?, ?, 'student', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
                            ?, 1, ?, NOW(), ?, 'active')");
                
                $user_stmt->execute([
                    $request['username'],
                    $request['password'],
                    $request['email'],
                    $request['first_name'],
                    $request['middle_name'],
                    $request['last_name'],
                    $request['student_number'],
                    $request['nationality'],
                    $request['national_id'],
                    $request['phone'],
                    $request['alternative_phone'],
                    $request['date_of_birth'],
                    $request['gender'],
                    $request['address'],
                    $request['city'],
                    $request['region'],
                    $request['postal_code'],
                    $request['country'],
                    $request['program'], // This is the program_id
                    $request['entry_semester'],
                    $expected_graduation
                ]);
                
                // Update request status
                $update_stmt = $pdo->prepare("UPDATE registration_requests SET status = 'approved', 
                                             approved_by = ?, approved_at = NOW() WHERE id = ?");
                $update_stmt->execute([$_SESSION['user_id'], $request_id]);
                
                $pdo->commit();
                $message = "Registration approved successfully! Student can now login and register for courses.";
                $message_type = "success";
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = "Error approving registration: " . $e->getMessage();
            $message_type = "danger";
        }
    } elseif ($action === 'reject') {
        $stmt = $pdo->prepare("UPDATE registration_requests SET status = 'rejected', 
                              approved_by = ?, approved_at = NOW() WHERE id = ?");
        $stmt->execute([$_SESSION['user_id'], $request_id]);
        $message = "Registration rejected.";
        $message_type = "warning";
    }
}

// Get all registration requests
$pending_requests = $pdo->query("SELECT * FROM registration_requests WHERE status = 'pending' ORDER BY created_at DESC")->fetchAll();
$approved_requests = $pdo->query("SELECT * FROM registration_requests WHERE status = 'approved' ORDER BY approved_at DESC LIMIT 20")->fetchAll();
$rejected_requests = $pdo->query("SELECT * FROM registration_requests WHERE status = 'rejected' ORDER BY approved_at DESC LIMIT 20")->fetchAll();

// Statistics
$total_pending = count($pending_requests);
$total_approved = $pdo->query("SELECT COUNT(*) FROM registration_requests WHERE status = 'approved'")->fetchColumn();
$total_rejected = $pdo->query("SELECT COUNT(*) FROM registration_requests WHERE status = 'rejected'")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Dashboard - Student Grade Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar registrar-sidebar">
                <div class="d-flex flex-column p-3">
                    <div class="text-center mb-4">
                        <i class="fas fa-user-check fa-2x mb-2"></i>
                        <h5>Registrar Portal</h5>
                        <small><?php echo $_SESSION['first_name'] . ' ' . $_SESSION['last_name']; ?></small>
                    </div>
                    
                    <ul class="nav nav-pills flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="#dashboard" data-bs-toggle="tab">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#pending" data-bs-toggle="tab">
                                <i class="fas fa-user-graduate me-2"></i>Student Registrations
                                <?php if ($total_pending > 0): ?>
                                <span class="badge bg-warning"><?php echo $total_pending; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="registrar_course_approval.php">
                                <i class="fas fa-book me-2"></i>Course Approvals
                                <?php 
                                $pending_courses = $pdo->query("SELECT COUNT(*) FROM course_registrations WHERE status = 'pending'")->fetchColumn();
                                if ($pending_courses > 0): 
                                ?>
                                <span class="badge bg-warning"><?php echo $pending_courses; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#approved" data-bs-toggle="tab">
                                <i class="fas fa-check-circle me-2"></i>Approved
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#rejected" data-bs-toggle="tab">
                                <i class="fas fa-times-circle me-2"></i>Rejected
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

                <div class="tab-content">
                    <!-- Dashboard Tab -->
                    <div class="tab-pane fade show active" id="dashboard">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2>Registrar Dashboard</h2>
                            <span>Welcome, <?php echo $_SESSION['first_name']; ?>!</span>
                        </div>

                        <!-- Statistics Cards - Student Registrations -->
                        <h5 class="mb-3"><i class="fas fa-user-graduate me-2"></i>Student Registrations</h5>
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="card stat-card bg-warning text-dark">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h4><?php echo $total_pending; ?></h4>
                                                <p>Pending Requests</p>
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

                        <!-- Statistics Cards - Course Registrations -->
                        <h5 class="mb-3"><i class="fas fa-book me-2"></i>Course Registrations</h5>
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="card stat-card bg-warning text-dark">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h4><?php echo $pending_courses; ?></h4>
                                                <p>Pending Approvals</p>
                                            </div>
                                            <i class="fas fa-hourglass-half fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card stat-card bg-success text-white">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h4><?php 
                                                $approved_courses = $pdo->query("SELECT COUNT(*) FROM course_registrations WHERE status = 'approved'")->fetchColumn();
                                                echo $approved_courses; 
                                                ?></h4>
                                                <p>Approved Courses</p>
                                            </div>
                                            <i class="fas fa-check-double fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card stat-card bg-info text-white">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h4><?php 
                                                $total_students = $pdo->query("SELECT COUNT(DISTINCT student_id) FROM course_registrations WHERE status = 'approved'")->fetchColumn();
                                                echo $total_students; 
                                                ?></h4>
                                                <p>Enrolled Students</p>
                                            </div>
                                            <i class="fas fa-users fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <i class="fas fa-book fa-3x text-primary mb-3"></i>
                                        <h5>Course Approvals</h5>
                                        <p class="text-muted">Review and approve student course registrations</p>
                                        <a href="registrar_course_approval.php" class="btn btn-primary">
                                            <i class="fas fa-arrow-right me-2"></i>Go to Course Approvals
                                            <?php if ($pending_courses > 0): ?>
                                            <span class="badge bg-warning text-dark ms-2"><?php echo $pending_courses; ?> Pending</span>
                                            <?php endif; ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <i class="fas fa-user-graduate fa-3x text-success mb-3"></i>
                                        <h5>Student Registrations</h5>
                                        <p class="text-muted">Review and approve new student applications</p>
                                        <a href="#pending" data-bs-toggle="tab" class="btn btn-success">
                                            <i class="fas fa-arrow-right me-2"></i>View Pending Requests
                                            <?php if ($total_pending > 0): ?>
                                            <span class="badge bg-warning text-dark ms-2"><?php echo $total_pending; ?> Pending</span>
                                            <?php endif; ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Pending Requests -->
                        <div class="card">
                            <div class="card-header">
                                <h5>Recent Pending Requests</h5>
                            </div>
                            <div class="card-body">
                                <?php if (count($pending_requests) > 0): ?>
                                    <?php foreach(array_slice($pending_requests, 0, 5) as $request): ?>
                                    <div class="card request-card">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <h6><?php echo $request['first_name'] . ' ' . $request['last_name']; ?></h6>
                                                    <p class="mb-1"><small><i class="fas fa-envelope"></i> <?php echo $request['email']; ?></small></p>
                                                    <p class="mb-1"><small><i class="fas fa-graduation-cap"></i> <?php echo $request['program']; ?></small></p>
                                                    <p class="mb-0"><small><i class="fas fa-clock"></i> <?php echo date('M j, Y', strtotime($request['created_at'])); ?></small></p>
                                                </div>
                                                <div class="col-md-4 text-end">
                                                    <button class="btn btn-sm btn-info mb-2" data-bs-toggle="modal" 
                                                            data-bs-target="#viewModal<?php echo $request['id']; ?>">
                                                        <i class="fas fa-eye"></i> View Details
                                                    </button><br>
                                                    <a href="?action=approve&id=<?php echo $request['id']; ?>" 
                                                       class="btn btn-sm btn-success"
                                                       onclick="return confirm('Approve this registration?')">
                                                        <i class="fas fa-check"></i> Approve
                                                    </a>
                                                    <a href="?action=reject&id=<?php echo $request['id']; ?>" 
                                                       class="btn btn-sm btn-danger"
                                                       onclick="return confirm('Reject this registration?')">
                                                        <i class="fas fa-times"></i> Reject
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-muted">No pending requests</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Pending Tab -->
                    <div class="tab-pane fade" id="pending">
                        <h2 class="mb-4">Pending Registration Requests</h2>
                        
                        <?php if (count($pending_requests) > 0): ?>
                            <?php foreach($pending_requests as $request): ?>
                            <div class="card request-card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <h5><?php echo $request['first_name'] . ' ' . $request['last_name']; ?></h5>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <p class="mb-1"><strong>Username:</strong> <?php echo $request['username']; ?></p>
                                                    <p class="mb-1"><strong>Email:</strong> <?php echo $request['email']; ?></p>
                                                    <p class="mb-1"><strong>Phone:</strong> <?php echo $request['phone']; ?></p>
                                                    <p class="mb-1"><strong>DOB:</strong> <?php echo date('M j, Y', strtotime($request['date_of_birth'])); ?></p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p class="mb-1"><strong>Gender:</strong> <?php echo ucfirst($request['gender']); ?></p>
                                                    <p class="mb-1"><strong>Program:</strong> <?php echo $request['program']; ?></p>
                                                    <p class="mb-1"><strong>Guardian:</strong> <?php echo $request['guardian_name']; ?></p>
                                                    <p class="mb-1"><strong>Applied:</strong> <?php echo date('M j, Y H:i', strtotime($request['created_at'])); ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <button class="btn btn-info mb-2" data-bs-toggle="modal" 
                                                    data-bs-target="#viewModal<?php echo $request['id']; ?>">
                                                <i class="fas fa-eye"></i> Full Details
                                            </button><br>
                                            <a href="?action=approve&id=<?php echo $request['id']; ?>" 
                                               class="btn btn-success mb-2"
                                               onclick="return confirm('Approve this registration?')">
                                                <i class="fas fa-check"></i> Approve
                                            </a><br>
                                            <a href="?action=reject&id=<?php echo $request['id']; ?>" 
                                               class="btn btn-danger"
                                               onclick="return confirm('Reject this registration?')">
                                                <i class="fas fa-times"></i> Reject
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="alert alert-info">No pending registration requests</div>
                        <?php endif; ?>
                    </div>

                    <!-- Approved Tab -->
                    <div class="tab-pane fade" id="approved">
                        <h2 class="mb-4">Approved Registrations</h2>
                        
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Program</th>
                                        <th>Approved Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($approved_requests as $request): ?>
                                    <tr>
                                        <td><?php echo $request['first_name'] . ' ' . $request['last_name']; ?></td>
                                        <td><?php echo $request['username']; ?></td>
                                        <td><?php echo $request['email']; ?></td>
                                        <td><?php echo $request['program']; ?></td>
                                        <td><?php echo date('M j, Y', strtotime($request['approved_at'])); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Rejected Tab -->
                    <div class="tab-pane fade" id="rejected">
                        <h2 class="mb-4">Rejected Registrations</h2>
                        
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Program</th>
                                        <th>Rejected Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($rejected_requests as $request): ?>
                                    <tr>
                                        <td><?php echo $request['first_name'] . ' ' . $request['last_name']; ?></td>
                                        <td><?php echo $request['username']; ?></td>
                                        <td><?php echo $request['email']; ?></td>
                                        <td><?php echo $request['program']; ?></td>
                                        <td><?php echo date('M j, Y', strtotime($request['approved_at'])); ?></td>
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

    <!-- View Details Modals -->
    <?php foreach($pending_requests as $request): ?>
    <div class="modal fade" id="viewModal<?php echo $request['id']; ?>" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">Registration Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <h6 class="text-primary">Personal Information</h6>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Full Name:</strong> <?php echo $request['first_name'] . ' ' . $request['last_name']; ?></p>
                            <p><strong>Date of Birth:</strong> <?php echo date('M j, Y', strtotime($request['date_of_birth'])); ?></p>
                            <p><strong>Gender:</strong> <?php echo ucfirst($request['gender']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Email:</strong> <?php echo $request['email']; ?></p>
                            <p><strong>Phone:</strong> <?php echo $request['phone']; ?></p>
                            <p><strong>Address:</strong> <?php echo $request['address']; ?></p>
                        </div>
                    </div>
                    
                    <h6 class="text-primary">Guardian Information</h6>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Guardian Name:</strong> <?php echo $request['guardian_name']; ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Guardian Phone:</strong> <?php echo $request['guardian_phone']; ?></p>
                        </div>
                    </div>
                    
                    <h6 class="text-primary">Academic Information</h6>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Program:</strong> <?php echo $request['program']; ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Previous School:</strong> <?php echo $request['previous_school'] ?: 'N/A'; ?></p>
                        </div>
                    </div>
                    
                    <h6 class="text-primary">Account Information</h6>
                    <p><strong>Username:</strong> <?php echo $request['username']; ?></p>
                    <p><strong>Applied On:</strong> <?php echo date('M j, Y H:i:s', strtotime($request['created_at'])); ?></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="?action=approve&id=<?php echo $request['id']; ?>" 
                       class="btn btn-success"
                       onclick="return confirm('Approve this registration?')">
                        <i class="fas fa-check"></i> Approve
                    </a>
                    <a href="?action=reject&id=<?php echo $request['id']; ?>" 
                       class="btn btn-danger"
                       onclick="return confirm('Reject this registration?')">
                        <i class="fas fa-times"></i> Reject
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/script.js"></script>
</body>
</html>
