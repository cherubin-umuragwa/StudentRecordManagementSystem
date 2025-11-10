<?php
// Get current page name
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar -->
<div class="col-md-3 col-lg-2 sidebar bg-danger text-white">
    <div class="d-flex flex-column p-3">
        <div class="text-center mb-4">
            <i class="fas fa-user-shield fa-2x mb-2"></i>
            <h5>Admin Portal</h5>
            <small><?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?></small>
        </div>
        
        <ul class="nav nav-pills flex-column">
            <li class="nav-item">
                <a class="nav-link text-white <?php echo ($current_page == 'admin.php') ? 'active bg-dark' : ''; ?>" href="admin.php">
                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white <?php echo ($current_page == 'classroom.php') ? 'active bg-dark' : ''; ?>" href="classroom.php">
                    <i class="fas fa-chalkboard me-2"></i>Classrooms
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white <?php echo ($current_page == 'grades.php') ? 'active bg-dark' : ''; ?>" href="grades.php">
                    <i class="fas fa-chart-bar me-2"></i>Grades
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="logout.php">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </li>
        </ul>
    </div>
</div>
