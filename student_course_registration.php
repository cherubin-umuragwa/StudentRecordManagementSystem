<?php
include 'includes/conn.php';
include 'includes/functions.php';
requireRole('student');

$student_id = $_SESSION['user_id'];
$message = '';
$message_type = '';

// Get student information
$student_stmt = $pdo->prepare("SELECT u.*, p.name as program_name, p.id as program_id 
                               FROM users u 
                               LEFT JOIN programs p ON u.program_id = p.id 
                               WHERE u.id = ?");
$student_stmt->execute([$student_id]);
$student = $student_stmt->fetch();

if (!$student['program_id']) {
    die("Error: You are not enrolled in any program. Please contact the registrar.");
}

$current_year = $student['current_year'] ?? 1;
$current_semester = $student['current_semester'] ?? 1;
$current_academic_year = date('Y') . '/' . (date('Y') + 1);

// Handle course registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_courses'])) {
    $selected_courses = $_POST['courses'] ?? [];
    
    if (empty($selected_courses)) {
        $message = "Please select at least one course to register.";
        $message_type = "warning";
    } else {
        try {
            $pdo->beginTransaction();
            
            $registered_count = 0;
            $already_registered = 0;
            
            foreach ($selected_courses as $course_id) {
                // Check if already registered
                $check = $pdo->prepare("SELECT id FROM course_registrations 
                                       WHERE student_id = ? AND course_id = ? 
                                       AND academic_year = ? AND semester = ?");
                $check->execute([$student_id, $course_id, $current_academic_year, $current_semester]);
                
                if ($check->rowCount() > 0) {
                    $already_registered++;
                    continue;
                }
                
                // Register for course
                $stmt = $pdo->prepare("INSERT INTO course_registrations 
                                      (student_id, course_id, academic_year, semester, status) 
                                      VALUES (?, ?, ?, ?, 'pending')");
                $stmt->execute([$student_id, $course_id, $current_academic_year, $current_semester]);
                $registered_count++;
            }
            
            $pdo->commit();
            
            $message = "Successfully registered for $registered_count course(s).";
            if ($already_registered > 0) {
                $message .= " ($already_registered already registered)";
            }
            $message .= " Your registration is pending registrar approval.";
            $message_type = "success";
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = "Error registering courses: " . $e->getMessage();
            $message_type = "danger";
        }
    }
}

// Handle course drop
if (isset($_GET['action']) && $_GET['action'] === 'drop' && isset($_GET['reg_id'])) {
    $reg_id = $_GET['reg_id'];
    
    // Only allow dropping pending registrations
    $stmt = $pdo->prepare("UPDATE course_registrations 
                          SET status = 'dropped' 
                          WHERE id = ? AND student_id = ? AND status = 'pending'");
    $stmt->execute([$reg_id, $student_id]);
    
    if ($stmt->rowCount() > 0) {
        $message = "Course registration dropped successfully.";
        $message_type = "success";
    } else {
        $message = "Cannot drop this course. It may already be approved.";
        $message_type = "danger";
    }
}

// Get available courses for current year and semester
$available_courses = $pdo->prepare("SELECT c.*, 
                                   (SELECT COUNT(*) FROM course_registrations cr 
                                    WHERE cr.course_id = c.id 
                                    AND cr.status = 'approved' 
                                    AND cr.academic_year = ?) as enrolled_count,
                                   u.first_name as instructor_first, 
                                   u.last_name as instructor_last
                                   FROM courses c 
                                   LEFT JOIN users u ON c.instructor_id = u.id 
                                   WHERE c.program_id = ? 
                                   AND c.year_level = ? 
                                   AND c.semester = ? 
                                   ORDER BY c.is_elective, c.code");
$available_courses->execute([$current_academic_year, $student['program_id'], $current_year, $current_semester]);
$courses = $available_courses->fetchAll();

// Get student's registered courses
$registered_courses = $pdo->prepare("SELECT cr.*, c.code, c.name, c.credits, c.is_elective,
                                    u.first_name as approver_first, u.last_name as approver_last
                                    FROM course_registrations cr 
                                    JOIN courses c ON cr.course_id = c.id 
                                    LEFT JOIN users u ON cr.approved_by = u.id 
                                    WHERE cr.student_id = ? 
                                    AND cr.academic_year = ? 
                                    AND cr.semester = ?
                                    AND cr.status != 'dropped'
                                    ORDER BY cr.status, c.code");
$registered_courses->execute([$student_id, $current_academic_year, $current_semester]);
$my_registrations = $registered_courses->fetchAll();

// Calculate total credits
$total_credits = 0;
$approved_credits = 0;
foreach ($my_registrations as $reg) {
    $total_credits += $reg['credits'];
    if ($reg['status'] === 'approved') {
        $approved_credits += $reg['credits'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Registration - Student Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="students.php">
                <i class="fas fa-user-graduate"></i> Student Portal
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    <?php echo $student['first_name'] . ' ' . $student['last_name']; ?>
                </span>
                <a class="nav-link" href="students.php">Dashboard</a>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Student Info -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="stat-box">
                    <div class="row">
                        <div class="col-md-3">
                            <h6>Student Number</h6>
                            <h5><?php echo $student['student_number'] ?? 'N/A'; ?></h5>
                        </div>
                        <div class="col-md-3">
                            <h6>Program</h6>
                            <h5><?php echo $student['program_name']; ?></h5>
                        </div>
                        <div class="col-md-3">
                            <h6>Current Year/Semester</h6>
                            <h5>Year <?php echo $current_year; ?>, Semester <?php echo $current_semester; ?></h5>
                        </div>
                        <div class="col-md-3">
                            <h6>Academic Year</h6>
                            <h5><?php echo $current_academic_year; ?></h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-primary"><?php echo count($my_registrations); ?></h3>
                        <p class="mb-0">Registered Courses</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-success"><?php echo $approved_credits; ?></h3>
                        <p class="mb-0">Approved Credits</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-info"><?php echo $total_credits; ?></h3>
                        <p class="mb-0">Total Credits</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- My Registered Courses -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-list-check me-2"></i>My Course Registrations</h5>
            </div>
            <div class="card-body">
                <?php if (count($my_registrations) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Course Code</th>
                                    <th>Course Name</th>
                                    <th>Credits</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Registration Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($my_registrations as $reg): ?>
                                <tr>
                                    <td><strong><?php echo $reg['code']; ?></strong></td>
                                    <td><?php echo $reg['name']; ?></td>
                                    <td><?php echo $reg['credits']; ?></td>
                                    <td>
                                        <?php if ($reg['is_elective']): ?>
                                            <span class="badge bg-success">Elective</span>
                                        <?php else: ?>
                                            <span class="badge bg-primary">Core</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $badge_class = [
                                            'pending' => 'warning',
                                            'approved' => 'success',
                                            'rejected' => 'danger'
                                        ];
                                        $status_class = $badge_class[$reg['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?php echo $status_class; ?> status-badge">
                                            <?php echo ucfirst($reg['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($reg['registration_date'])); ?></td>
                                    <td>
                                        <?php if ($reg['status'] === 'pending'): ?>
                                            <a href="?action=drop&reg_id=<?php echo $reg['id']; ?>" 
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('Are you sure you want to drop this course?')">
                                                <i class="fas fa-times"></i> Drop
                                            </a>
                                        <?php elseif ($reg['status'] === 'rejected'): ?>
                                            <small class="text-danger">
                                                <?php echo $reg['rejection_reason'] ?? 'No reason provided'; ?>
                                            </small>
                                        <?php else: ?>
                                            <span class="text-success"><i class="fas fa-check-circle"></i> Approved</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        You haven't registered for any courses yet. Select courses below to register.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Available Courses -->
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-book me-2"></i>Available Courses - Year <?php echo $current_year; ?>, Semester <?php echo $current_semester; ?></h5>
            </div>
            <div class="card-body">
                <?php if (count($courses) > 0): ?>
                    <form method="POST">
                        <div class="row">
                            <?php 
                            // Get IDs of already registered courses
                            $registered_ids = array_column($my_registrations, 'course_id');
                            
                            foreach ($courses as $course): 
                                $is_registered = in_array($course['id'], $registered_ids);
                                $is_full = $course['enrolled_count'] >= $course['max_students'];
                            ?>
                            <div class="col-md-6 mb-3">
                                <div class="card course-card <?php echo $course['is_elective'] ? 'elective' : ''; ?>">
                                    <div class="card-body">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" 
                                                   name="courses[]" value="<?php echo $course['id']; ?>" 
                                                   id="course<?php echo $course['id']; ?>"
                                                   <?php echo ($is_registered || $is_full) ? 'disabled' : ''; ?>>
                                            <label class="form-check-label w-100" for="course<?php echo $course['id']; ?>">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <h6 class="mb-1">
                                                            <?php echo $course['code']; ?> - <?php echo $course['name']; ?>
                                                            <?php if ($course['is_elective']): ?>
                                                                <span class="badge bg-success">Elective</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-primary">Core</span>
                                                            <?php endif; ?>
                                                        </h6>
                                                        <p class="text-muted small mb-1"><?php echo $course['description']; ?></p>
                                                        <small>
                                                            <i class="fas fa-graduation-cap"></i> <?php echo $course['credits']; ?> Credits
                                                            <?php if ($course['instructor_first']): ?>
                                                                | <i class="fas fa-user"></i> <?php echo $course['instructor_first'] . ' ' . $course['instructor_last']; ?>
                                                            <?php endif; ?>
                                                        </small>
                                                    </div>
                                                    <div class="text-end">
                                                        <?php if ($is_registered): ?>
                                                            <span class="badge bg-info">Registered</span>
                                                        <?php elseif ($is_full): ?>
                                                            <span class="badge bg-danger">Full</span>
                                                        <?php else: ?>
                                                            <small class="text-muted">
                                                                <?php echo $course['enrolled_count']; ?>/<?php echo $course['max_students']; ?>
                                                            </small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="d-grid gap-2 mt-3">
                            <button type="submit" name="register_courses" class="btn btn-primary btn-lg">
                                <i class="fas fa-check-circle me-2"></i>Register for Selected Courses
                            </button>
                            <a href="students.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                            </a>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        No courses available for your current year and semester.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/script.js"></script>
</body>
</html>
