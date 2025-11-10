<?php
// Get current page name
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar -->
<div class="col-md-3 col-lg-2 sidebar accountant-sidebar" style="min-height: 100vh; background-color: #2c3e50;">
    <div class="d-flex flex-column p-3">
        <div class="text-center mb-4 py-3">
            <div class="bg-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                <i class="fas fa-calculator fa-2x text-primary"></i>
            </div>
            <h5 class="text-white mt-3 mb-1">Accountant Portal</h5>
            <small class="text-white-50">Finance Officer</small>
            <hr class="bg-white-50 my-3">
            <small class="text-white"><?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?></small>
        </div>
        
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item mb-2">
                <a class="nav-link text-white <?php echo ($current_page == 'accountant.php') ? 'active' : ''; ?>" 
                   href="accountant.php"
                   style="<?php echo ($current_page == 'accountant.php') ? 'background-color: #27ae60;' : ''; ?>">
                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                </a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link text-white <?php echo ($current_page == 'accountant_verify_payments.php') ? 'active' : ''; ?>" 
                   href="accountant_verify_payments.php"
                   style="<?php echo ($current_page == 'accountant_verify_payments.php') ? 'background-color: #27ae60;' : ''; ?>">
                    <i class="fas fa-check-circle me-2"></i>Verify Payments
                </a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link text-white" href="logout.php">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </li>
        </ul>
    </div>
</div>

<style>
.accountant-sidebar .nav-link {
    border-radius: 8px;
    padding: 12px 16px;
    transition: all 0.3s ease;
}
.accountant-sidebar .nav-link:hover {
    background-color: rgba(255, 255, 255, 0.1);
    transform: translateX(5px);
}
.accountant-sidebar .nav-link.active {
    background-color: #27ae60 !important;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}
</style>
