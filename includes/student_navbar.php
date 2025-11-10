<?php
// Get current page name
$current_page = basename($_SERVER['PHP_SELF']);
$is_dashboard = ($current_page == 'students.php');
?>
<!-- Sidebar -->
<div class="col-md-3 col-lg-2 sidebar student-sidebar">
    <div class="d-flex flex-column p-3">
        <div class="text-center mb-4">
            <i class="fas fa-user-graduate fa-2x mb-2"></i>
            <h5>Student Portal</h5>
            <small><?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?></small>
        </div>
        
        <ul class="nav nav-pills flex-column">
            <?php if ($is_dashboard): ?>
                <!-- Dashboard page with tabs -->
                <li class="nav-item">
                    <a class="nav-link active" href="#dashboard" data-bs-toggle="tab">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="student_courses.php">
                        <i class="fas fa-book me-2"></i>My Courses
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#grades" data-bs-toggle="tab">
                        <i class="fas fa-chart-bar me-2"></i>My Grades
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#performance" data-bs-toggle="tab">
                        <i class="fas fa-trending-up me-2"></i>Performance
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="student_financial_statement.php">
                        <i class="fas fa-file-invoice-dollar me-2"></i>Financial Statement
                    </a>
                </li>
            <?php else: ?>
                <!-- Other pages with regular links -->
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'students.php') ? 'active' : ''; ?>" href="students.php">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'student_courses.php') ? 'active' : ''; ?>" href="student_courses.php">
                        <i class="fas fa-book me-2"></i>My Courses
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'student_course_registration.php') ? 'active' : ''; ?>" href="student_course_registration.php">
                        <i class="fas fa-edit me-2"></i>Course Registration
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'student_financial_statement.php') ? 'active' : ''; ?>" href="student_financial_statement.php">
                        <i class="fas fa-file-invoice-dollar me-2"></i>Financial Statement
                    </a>
                </li>
            <?php endif; ?>
            <li class="nav-item">
                <a class="nav-link" href="logout.php">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </li>
        </ul>
    </div>
</div>
