<?php
include 'includes/conn.php';
include 'includes/functions.php';
requireRole('student');

$student_id = $_SESSION['user_id'];
$message = '';
$message_type = '';

// Get student info with program details
$student_info = $pdo->prepare("SELECT u.*, p.name as program_name 
                               FROM users u 
                               LEFT JOIN programs p ON u.program_id = p.id 
                               WHERE u.id = ?");
$student_info->execute([$student_id]);
$student = $student_info->fetch();

// Get current academic year and semester
$current_year = date('Y') . '/' . (date('Y') + 1);

// Use student's current_semester from their profile, not calculated from date
// This allows proper control of which semester the student is in
$current_semester = $student['current_semester'] ?? 1;

// Handle course registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_course'])) {
    $course_id = $_POST['course_id'];
    
    // Check if already registered
    $check = $pdo->prepare("SELECT * FROM course_registrations 
                           WHERE student_id = ? AND course_id = ? AND academic_year = ? AND semester = ?");
    $check->execute([$student_id, $course_id, $current_year, $current_semester]);
    
    if ($check->rowCount() > 0) {
        $message = "You are already registered for this course!";
        $message_type = "warning";
    } else {
        // Register for course
        $stmt = $pdo->prepare("INSERT INTO course_registrations 
                              (student_id, course_id, academic_year, semester, status) 
                              VALUES (?, ?, ?, ?, 'pending')");
        $stmt->execute([$student_id, $course_id, $current_year, $current_semester]);
        $message = "Course registration submitted! Waiting for registrar approval.";
        $message_type = "success";
    }
}

// Handle course drop
if (isset($_GET['action']) && $_GET['action'] === 'drop' && isset($_GET['id'])) {
    $registration_id = $_GET['id'];
    
    $stmt = $pdo->prepare("UPDATE course_registrations SET status = 'dropped' WHERE id = ? AND student_id = ?");
    $stmt->execute([$registration_id, $student_id]);
    $message = "Course dropped successfully!";
    $message_type = "info";
}

// Get enrolled courses (approved) - Show ALL approved courses, not just current semester
$enrolled = [];
$pending = [];

try {
    $enrolled_courses = $pdo->prepare("
        SELECT cr.*, c.code, c.name, c.credits, c.instructor_id,
               u.first_name as instructor_first, u.last_name as instructor_last
        FROM course_registrations cr
        JOIN courses c ON cr.course_id = c.id
        LEFT JOIN users u ON c.instructor_id = u.id
        WHERE cr.student_id = ? AND cr.status = 'approved'
        ORDER BY cr.academic_year DESC, cr.semester DESC, c.code
    ");
    $enrolled_courses->execute([$student_id]);
    $enrolled = $enrolled_courses->fetchAll();

    // Get pending registrations - Show ALL pending, not just current semester
    $pending_courses = $pdo->prepare("
        SELECT cr.*, c.code, c.name, c.credits
        FROM course_registrations cr
        JOIN courses c ON cr.course_id = c.id
        WHERE cr.student_id = ? AND cr.status = 'pending'
        ORDER BY cr.academic_year DESC, cr.semester DESC, c.code
    ");
    $pending_courses->execute([$student_id]);
    $pending = $pending_courses->fetchAll();
} catch (PDOException $e) {
    // If course_registrations table doesn't exist, continue with empty arrays
    $enrolled = [];
    $pending = [];
}

// Get available courses for registration (based on student's program, year level, and semester)
$student_year = $student['current_year'] ?? 1;
$student_program = $student['program_id'] ?? 1;
$available = [];

try {
    // Only show courses that match:
    // 1. Student's program
    // 2. Student's current year level
    // 3. Current semester
    // 4. Not already registered (pending or approved)
    $available_courses = $pdo->prepare("
        SELECT c.*, 
               (SELECT COUNT(*) FROM course_registrations 
                WHERE course_id = c.id AND status = 'approved' 
                AND academic_year = ? AND semester = ?) as enrolled_count
        FROM courses c
        WHERE c.program_id = ? 
        AND c.year_level = ? 
        AND c.semester = ?
        AND c.id NOT IN (
            SELECT course_id FROM course_registrations 
            WHERE student_id = ? 
            AND academic_year = ? 
            AND semester = ?
            AND status IN ('pending', 'approved')
        )
        ORDER BY c.code
    ");
    $available_courses->execute([
        $current_year, 
        $current_semester, 
        $student_program, 
        $student_year, 
        $current_semester, 
        $student_id, 
        $current_year, 
        $current_semester
    ]);
    $available = $available_courses->fetchAll();
} catch (PDOException $e) {
    // If courses table doesn't exist, continue with empty array
    $available = [];
}

// Calculate total credits
$total_credits = 0;
foreach ($enrolled as $course) {
    $total_credits += $course['credits'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses - Student Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
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
        .course-card {
            border-left: 4px solid #007bff;
            transition: transform 0.2s;
        }
        .course-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .stat-card {
            border-radius: 10px;
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
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
                        <i class="fas fa-user-graduate fa-2x mb-2"></i>
                        <h5>Student Portal</h5>
                        <small><?php echo $_SESSION['first_name'] . ' ' . $_SESSION['last_name']; ?></small>
                    </div>
                    
                    <ul class="nav nav-pills flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="students.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="student_courses.php">
                                <i class="fas fa-book me-2"></i>My Courses
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="student_course_registration.php">
                                <i class="fas fa-plus-circle me-2"></i>Register Courses
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
                    <h2><i class="fas fa-book me-2"></i>My Courses</h2>
                    <div>
                        <span class="badge bg-primary">Academic Year: <?php echo $current_year; ?></span>
                        <span class="badge bg-info">Semester: <?php echo $current_semester; ?></span>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card stat-card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?php echo count($enrolled); ?></h4>
                                        <p>Enrolled Courses</p>
                                    </div>
                                    <i class="fas fa-check-circle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stat-card bg-warning text-dark">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?php echo count($pending); ?></h4>
                                        <p>Pending Approval</p>
                                    </div>
                                    <i class="fas fa-clock fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stat-card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?php echo $total_credits; ?></h4>
                                        <p>Total Credits</p>
                                    </div>
                                    <i class="fas fa-graduation-cap fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enrolled Courses -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5><i class="fas fa-check-circle me-2"></i>Enrolled Courses</h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($enrolled) > 0): ?>
                            <div class="row">
                                <?php foreach ($enrolled as $course): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="card course-card">
                                        <div class="card-body">
                                            <h6 class="card-title"><?php echo $course['code']; ?> - <?php echo $course['name']; ?></h6>
                                            <p class="card-text mb-2">
                                                <span class="badge bg-primary"><?php echo $course['credits']; ?> Credits</span>
                                                <span class="badge bg-info"><?php echo $course['academic_year']; ?> - Sem <?php echo $course['semester']; ?></span>
                                                <?php if ($course['instructor_first']): ?>
                                                <span class="badge bg-secondary">
                                                    <i class="fas fa-user"></i> 
                                                    <?php echo $course['instructor_first'] . ' ' . $course['instructor_last']; ?>
                                                </span>
                                                <?php endif; ?>
                                            </p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">
                                                    Registered: <?php echo date('M j, Y', strtotime($course['registration_date'])); ?>
                                                </small>
                                                <a href="?action=drop&id=<?php echo $course['id']; ?>" 
                                                   class="btn btn-sm btn-outline-danger"
                                                   onclick="return confirm('Are you sure you want to drop this course?')">
                                                    <i class="fas fa-times"></i> Drop
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <h6><i class="fas fa-info-circle me-2"></i>No Enrolled Courses</h6>
                                <p class="mb-2">You haven't been approved for any courses yet.</p>
                                <p class="mb-0"><strong>Next Steps:</strong></p>
                                <ul class="mb-2">
                                    <li>Register for courses below (see "Available Courses for Registration")</li>
                                    <li>Wait for registrar approval</li>
                                    <li>Your approved courses will appear here</li>
                                </ul>
                                <a href="#available" class="btn btn-primary btn-sm">
                                    <i class="fas fa-arrow-down me-1"></i>View Available Courses
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Pending Registrations -->
                <?php if (count($pending) > 0): ?>
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5><i class="fas fa-clock me-2"></i>Pending Approval</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Course Code</th>
                                        <th>Course Name</th>
                                        <th>Credits</th>
                                        <th>Status</th>
                                        <th>Registered Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pending as $course): ?>
                                    <tr>
                                        <td><?php echo $course['code']; ?></td>
                                        <td><?php echo $course['name']; ?></td>
                                        <td><?php echo $course['credits']; ?></td>
                                        <td><span class="badge bg-warning">Pending</span></td>
                                        <td><?php echo date('M j, Y', strtotime($course['registration_date'])); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            These courses are waiting for registrar approval. You will be notified once approved.
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Available Courses -->
                <?php if (count($available) > 0): ?>
                <div class="card" id="available">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Available Courses for Registration</h5>
                        <div>
                            <span class="badge bg-light text-dark">Year <?php echo $student_year; ?></span>
                            <span class="badge bg-light text-dark">Semester <?php echo $current_semester; ?></span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info mb-3">
                            <i class="fas fa-filter me-2"></i>
                            <strong>Filter Criteria:</strong><br>
                            <strong>Program:</strong> <?php echo $student['program_name'] ?? 'Your Program'; ?> (ID: <?php echo $student_program; ?>)<br>
                            <strong>Year Level:</strong> <?php echo $student_year; ?><br>
                            <strong>Semester:</strong> <?php echo $current_semester; ?><br>
                            <strong>Academic Year:</strong> <?php echo $current_year; ?><br>
                            <small class="text-muted">Only courses matching ALL criteria above will be shown.</small>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Course Code</th>
                                        <th>Course Name</th>
                                        <th>Credits</th>
                                        <th>Enrolled</th>
                                        <th>Max Students</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($available as $course): ?>
                                    <tr>
                                        <td><?php echo $course['code']; ?></td>
                                        <td><?php echo $course['name']; ?></td>
                                        <td><?php echo $course['credits']; ?></td>
                                        <td><?php echo $course['enrolled_count']; ?></td>
                                        <td><?php echo $course['max_students']; ?></td>
                                        <td>
                                            <?php if ($course['enrolled_count'] < $course['max_students']): ?>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                                <button type="submit" name="register_course" class="btn btn-sm btn-success">
                                                    <i class="fas fa-plus"></i> Register
                                                </button>
                                            </form>
                                            <?php else: ?>
                                            <span class="badge bg-danger">Full</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="card" id="available">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Available Courses for Registration</h5>
                        <div>
                            <span class="badge bg-light text-dark">Year <?php echo $student_year; ?></span>
                            <span class="badge bg-light text-dark">Semester <?php echo $current_semester; ?></span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info mb-3">
                            <i class="fas fa-filter me-2"></i>
                            <strong>Showing courses for:</strong> 
                            <?php echo $student['program_name'] ?? 'Your Program'; ?> - 
                            Year <?php echo $student_year; ?>, 
                            Semester <?php echo $current_semester; ?> 
                            (<?php echo $current_year; ?>)
                        </div>
                        <div class="alert alert-warning">
                            <h6><i class="fas fa-exclamation-triangle me-2"></i>No Available Courses</h6>
                            <p class="mb-2">There are no courses available for registration at this time.</p>
                            <p class="mb-0"><strong>Possible reasons:</strong></p>
                            <ul class="mb-0">
                                <li>You've already registered for all courses in your year/semester</li>
                                <li>No courses have been created for your program yet</li>
                                <li>All courses are full</li>
                                <li>Course registration period may not be open</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
