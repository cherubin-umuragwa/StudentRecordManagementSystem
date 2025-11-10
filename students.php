<?php
include 'includes/conn.php';
include 'includes/functions.php';
requireRole('student');

$student_id = $_SESSION['user_id'];

// Get student information with program details
$student_info = $pdo->prepare("SELECT u.*, p.name as program_name, p.code as program_code,
                                      d.name as department_name, s.name as school_name
                               FROM users u
                               LEFT JOIN programs p ON u.program_id = p.id
                               LEFT JOIN departments d ON p.department_id = d.id
                               LEFT JOIN schools s ON d.school_id = s.id
                               WHERE u.id = ?");
$student_info->execute([$student_id]);
$student = $student_info->fetch();

// Get student's grades from course registrations
try {
    $grades_stmt = $pdo->prepare("
        SELECT cr.course_id, c.code as course_code, c.name as course_name, c.credits,
               cr.grade as percentage, cr.letter_grade, cr.academic_year, cr.semester
        FROM course_registrations cr
        JOIN courses c ON cr.course_id = c.id
        WHERE cr.student_id = ? AND cr.grade IS NOT NULL
        ORDER BY cr.academic_year DESC, cr.semester DESC
    ");
    $grades_stmt->execute([$student_id]);
    $grades = $grades_stmt->fetchAll();
    
    // Calculate letter grades if not set
    foreach ($grades as &$grade) {
        if (empty($grade['letter_grade']) && !is_null($grade['percentage'])) {
            $grade['letter_grade'] = calculateGradePoint($grade['percentage']);
        }
    }
} catch (PDOException $e) {
    // Fallback to old grades system if course_registrations doesn't have grade columns
    $grades_stmt = $pdo->prepare("
        SELECT g.*, s.name as subject_name, c.name as classroom_name, 
               u.first_name as lecturer_first, u.last_name as lecturer_last 
        FROM grades g 
        JOIN subjects s ON g.subject_id = s.id 
        JOIN classrooms c ON g.classroom_id = c.id 
        JOIN users u ON g.lecturer_id = u.id 
        WHERE g.student_id = ? 
        ORDER BY g.graded_at DESC
    ");
    $grades_stmt->execute([$student_id]);
    $grades = $grades_stmt->fetchAll();
}

// Calculate statistics
$total_grades = count($grades);
$average_grade = 0;
if ($total_grades > 0) {
    $sum = 0;
    foreach ($grades as $grade) {
        $sum += $grade['grade'];
    }
    $average_grade = $sum / $total_grades;
}

// Get grade distribution
$grade_distribution = ['A' => 0, 'B+' => 0, 'B' => 0, 'C+' => 0, 'C' => 0, 'D+' => 0, 'D' => 0, 'F' => 0];
foreach ($grades as $grade) {
    $letter_grade = calculateGradePoint($grade['grade']);
    $grade_distribution[$letter_grade]++;
}

// Get enrolled courses
$current_year = date('Y') . '/' . (date('Y') + 1);
$current_semester = (date('n') >= 1 && date('n') <= 6) ? 1 : 2;

$enrolled_courses = [];
$approved_courses = 0;
$pending_courses = 0;
$rejected_courses = 0;

try {
    // Try to get courses - show ALL courses for this student, not just current semester
    $enrolled_courses_stmt = $pdo->prepare("
        SELECT cr.*, c.code, c.name, c.credits, 
               u.first_name as instructor_first, u.last_name as instructor_last
        FROM course_registrations cr
        JOIN courses c ON cr.course_id = c.id
        LEFT JOIN users u ON c.instructor_id = u.id
        WHERE cr.student_id = ?
        ORDER BY cr.academic_year DESC, cr.semester DESC, cr.status, c.code
    ");
    $enrolled_courses_stmt->execute([$student_id]);
    $enrolled_courses = $enrolled_courses_stmt->fetchAll();
    
    // Count courses by status
    foreach ($enrolled_courses as $course) {
        if ($course['status'] === 'approved') $approved_courses++;
        elseif ($course['status'] === 'pending') $pending_courses++;
        elseif ($course['status'] === 'rejected') $rejected_courses++;
    }
} catch (PDOException $e) {
    // If course_registrations table doesn't exist, just continue without courses
    $enrolled_courses = [];
}
foreach ($enrolled_courses as $course) {
    if ($course['status'] === 'approved') $approved_courses++;
    elseif ($course['status'] === 'pending') $pending_courses++;
    elseif ($course['status'] === 'rejected') $rejected_courses++;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal - Student Record Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/student_navbar.php'; ?>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 ms-sm-auto px-4 py-4">
                <div class="tab-content">
                    <!-- Dashboard Tab -->
                    <div class="tab-pane fade show active" id="dashboard">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2>Student Dashboard</h2>
                            <span>Welcome, <?php echo $_SESSION['first_name']; ?>!</span>
                        </div>

                        <!-- Student Program Information -->
                        <?php if ($student['program_id']): ?>
                        <div class="alert alert-info mb-4">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6><i class="fas fa-graduation-cap me-2"></i>Academic Information</h6>
                                    <p class="mb-1"><strong>Student Number:</strong> <?php echo $student['student_number'] ?? 'N/A'; ?></p>
                                    <p class="mb-1"><strong>Program:</strong> <?php echo $student['program_name']; ?></p>
                                    <p class="mb-0"><strong>Department:</strong> <?php echo $student['department_name']; ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>School:</strong> <?php echo $student['school_name']; ?></p>
                                    <p class="mb-1"><strong>Year Level:</strong> Year <?php echo $student['current_year'] ?? 1; ?></p>
                                    <p class="mb-0"><strong>Current Semester:</strong> Semester <?php echo $student['current_semester'] ?? 1; ?></p>
                                </div>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-warning mb-4">
                            <h6><i class="fas fa-exclamation-triangle me-2"></i>Program Enrollment Required</h6>
                            <p class="mb-0">You are not enrolled in any program yet. Please contact the registrar's office to complete your enrollment.</p>
                        </div>
                        <?php endif; ?>

                        <!-- Statistics -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card stat-card bg-primary text-white">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h4><?php echo $total_grades; ?></h4>
                                                <p>Total Grades</p>
                                            </div>
                                            <i class="fas fa-list-alt fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card stat-card bg-success text-white">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h4><?php echo number_format($average_grade, 2); ?></h4>
                                                <p>Average Grade</p>
                                            </div>
                                            <i class="fas fa-calculator fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card stat-card bg-warning text-dark">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h4><?php echo calculateGradePoint($average_grade); ?></h4>
                                                <p>Overall Grade</p>
                                            </div>
                                            <i class="fas fa-award fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card stat-card bg-info text-white">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h4><?php echo $grade_distribution['A']; ?></h4>
                                                <p>A Grades</p>
                                            </div>
                                            <i class="fas fa-star fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Course Overview -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0"><i class="fas fa-book me-2"></i>My Registered Courses</h5>
                                        <a href="student_courses.php" class="btn btn-light btn-sm">
                                            <i class="fas fa-arrow-right me-1"></i>View All Courses
                                        </a>
                                    </div>
                                    <div class="card-body">
                                        <?php if (count($enrolled_courses) > 0): ?>
                                            <div class="row mb-3">
                                                <div class="col-md-4">
                                                    <div class="text-center p-3 border rounded">
                                                        <h4 class="text-success"><?php echo $approved_courses; ?></h4>
                                                        <small>Approved</small>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="text-center p-3 border rounded">
                                                        <h4 class="text-warning"><?php echo $pending_courses; ?></h4>
                                                        <small>Pending</small>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="text-center p-3 border rounded">
                                                        <h4 class="text-danger"><?php echo $rejected_courses; ?></h4>
                                                        <small>Rejected</small>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <h6 class="mt-3 mb-3">All Registered Courses:</h6>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>Course Code</th>
                                                            <th>Course Name</th>
                                                            <th>Credits</th>
                                                            <th>Academic Year</th>
                                                            <th>Semester</th>
                                                            <th>Instructor</th>
                                                            <th>Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($enrolled_courses as $course): ?>
                                                        <tr>
                                                            <td><strong><?php echo $course['code']; ?></strong></td>
                                                            <td><?php echo $course['name']; ?></td>
                                                            <td><?php echo $course['credits']; ?></td>
                                                            <td><small><?php echo $course['academic_year']; ?></small></td>
                                                            <td><small>Sem <?php echo $course['semester']; ?></small></td>
                                                            <td>
                                                                <?php if ($course['instructor_first']): ?>
                                                                    <?php echo $course['instructor_first'] . ' ' . $course['instructor_last']; ?>
                                                                <?php else: ?>
                                                                    <span class="text-muted">TBA</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                $badge_class = 'secondary';
                                                                if ($course['status'] === 'approved') $badge_class = 'success';
                                                                elseif ($course['status'] === 'pending') $badge_class = 'warning';
                                                                elseif ($course['status'] === 'rejected') $badge_class = 'danger';
                                                                elseif ($course['status'] === 'dropped') $badge_class = 'secondary';
                                                                ?>
                                                                <span class="badge bg-<?php echo $badge_class; ?>">
                                                                    <?php echo ucfirst($course['status']); ?>
                                                                </span>
                                                            </td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php else: ?>
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle me-2"></i>
                                                <strong>No courses found.</strong><br>
                                                You haven't registered for any courses yet, or the course registration system hasn't been set up. 
                                                <a href="student_course_registration.php" class="alert-link">Click here to register for courses</a>
                                            </div>
                                            <div class="alert alert-warning">
                                                <strong>Troubleshooting:</strong><br>
                                                • Verify you've registered for courses via the registration page<br>
                                                • Contact the registrar if you've registered but don't see your courses
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Grade Distribution -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Grade Distribution</h5>
                                    </div>
                                    <div class="card-body">
                                        <?php foreach($grade_distribution as $letter => $count): 
                                            $percentage = $total_grades > 0 ? ($count / $total_grades) * 100 : 0;
                                            $color_class = '';
                                            switch($letter) {
                                                case 'A': $color_class = 'bg-success'; break;
                                                case 'B': $color_class = 'bg-info'; break;
                                                case 'C': $color_class = 'bg-warning'; break;
                                                case 'D': $color_class = 'bg-danger'; break;
                                                case 'F': $color_class = 'bg-dark'; break;
                                            }
                                        ?>
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between mb-1">
                                                <span>Grade <?php echo $letter; ?></span>
                                                <span><?php echo $count; ?> (<?php echo number_format($percentage, 1); ?>%)</span>
                                            </div>
                                            <div class="progress grade-progress">
                                                <div class="progress-bar <?php echo $color_class; ?>" 
                                                     style="width: <?php echo $percentage; ?>%"></div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Recent Grades</h5>
                                    </div>
                                    <div class="card-body">
                                        <?php foreach(array_slice($grades, 0, 5) as $grade): 
                                            $grade_color = getGradeColor($grade['grade']);
                                        ?>
                                        <div class="d-flex justify-content-between align-items-center mb-3 p-2 border rounded">
                                            <div>
                                                <h6 class="mb-0"><?php echo $grade['subject_name']; ?></h6>
                                                <small class="text-muted"><?php echo $grade['classroom_name']; ?></small>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-<?php echo $grade_color; ?>">
                                                    <?php echo $grade['grade']; ?>
                                                </span>
                                                <div>
                                                    <small class="text-muted"><?php echo date('M j', strtotime($grade['graded_at'])); ?></small>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Grades Tab -->
                    <div class="tab-pane fade" id="grades">
                        <h2 class="mb-4">My Grades</h2>
                        
                        <div class="card">
                            <div class="card-header">
                                <h5>All Grades</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Course ID</th>
                                                <th>Course Name</th>
                                                <th>Credits</th>
                                                <th>Percentage</th>
                                                <th>Grade</th>
                                                <th>Academic Year</th>
                                                <th>Semester</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($grades)): ?>
                                            <tr>
                                                <td colspan="7" class="text-center text-muted">
                                                    <i class="fas fa-info-circle me-2"></i>No grades available yet
                                                </td>
                                            </tr>
                                            <?php else: ?>
                                                <?php foreach($grades as $grade): 
                                                    // Handle both new and old grade systems
                                                    if (isset($grade['course_code'])) {
                                                        // New system with courses
                                                        $percentage = $grade['percentage'] ?? 0;
                                                        $letter_grade = $grade['letter_grade'] ?? calculateGradePoint($percentage);
                                                        $grade_color = getGradeColor($percentage);
                                                ?>
                                                <tr>
                                                    <td><strong><?php echo htmlspecialchars($grade['course_code']); ?></strong></td>
                                                    <td><?php echo htmlspecialchars($grade['course_name']); ?></td>
                                                    <td><?php echo $grade['credits']; ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $grade_color; ?>">
                                                            <?php echo number_format($percentage, 1); ?>%
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <strong class="text-<?php echo $grade_color; ?>">
                                                            <?php echo $letter_grade; ?>
                                                        </strong>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($grade['academic_year']); ?></td>
                                                    <td><?php echo $grade['semester']; ?></td>
                                                </tr>
                                                <?php 
                                                    } else {
                                                        // Old system with subjects
                                                        $percentage = $grade['grade'];
                                                        $letter_grade = calculateGradePoint($percentage);
                                                        $grade_color = getGradeColor($percentage);
                                                ?>
                                                <tr>
                                                    <td>-</td>
                                                    <td><?php echo htmlspecialchars($grade['subject_name']); ?></td>
                                                    <td>-</td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $grade_color; ?>">
                                                            <?php echo $percentage; ?>%
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <strong class="text-<?php echo $grade_color; ?>">
                                                            <?php echo $letter_grade; ?>
                                                        </strong>
                                                    </td>
                                                    <td>-</td>
                                                    <td>-</td>
                                                </tr>
                                                <?php 
                                                    }
                                                endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- Grading Scale Reference -->
                                <div class="mt-3">
                                    <h6>Grading Scale:</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <ul class="list-unstyled">
                                                <li><span class="badge bg-success">A</span> = 80% and above</li>
                                                <li><span class="badge bg-success">B+</span> = 75% - 79%</li>
                                                <li><span class="badge bg-info">B</span> = 70% - 74%</li>
                                                <li><span class="badge bg-info">C+</span> = 65% - 69%</li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <ul class="list-unstyled">
                                                <li><span class="badge bg-warning">C</span> = 60% - 64%</li>
                                                <li><span class="badge bg-warning">D+</span> = 55% - 59%</li>
                                                <li><span class="badge bg-warning">D</span> = 50% - 54%</li>
                                                <li><span class="badge bg-danger">F</span> = Below 50%</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Performance Tab -->
                    <div class="tab-pane fade" id="performance">
                        <h2 class="mb-4">Performance Analysis</h2>
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Grade Trend by Subject</h5>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="gradeChart" width="400" height="200"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Grade Summary</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="text-center">
                                            <h1 class="display-4 text-<?php 
                                                echo getGradeColor($average_grade); 
                                            ?>"><?php echo number_format($average_grade, 1); ?></h1>
                                            <p class="lead">Overall Average</p>
                                            <h3><?php echo calculateGradePoint($average_grade); ?></h3>
                                        </div>
                                        <hr>
                                        <div class="row text-center">
                                            <div class="col-6">
                                                <h5><?php echo $total_grades; ?></h5>
                                                <small>Total Records</small>
                                            </div>
                                            <div class="col-6">
                                                <h5><?php echo count(array_unique(array_column($grades, 'subject_id'))); ?></h5>
                                                <small>Subjects</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="assets/script.js"></script>
</body>
</html>