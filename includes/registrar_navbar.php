<?php
// Get current page name
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar -->
<div class="col-md-3 col-lg-2 sidebar bg-info text-white">
    <div class="d-flex flex-column p-3">
        <div class="text-center mb-4">
            <i class="fas fa-user-tie fa-2x mb-2"></i>
            <h5>Registrar Portal</h5>
            <small><?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?></small>
        </div>
        
        <ul class="nav nav-pills flex-column">
            <li class="nav-item">
                <a class="nav-link text-white <?php echo ($current_page == 'registrar.php') ? 'active bg-dark' : ''; ?>" href="registrar.php">
                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white <?php echo ($current_page == 'registrar_course_approval.php') ? 'active bg-dark' : ''; ?>" href="registrar_course_approval.php">
                    <i class="fas fa-check-circle me-2"></i>Course Approvals
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
