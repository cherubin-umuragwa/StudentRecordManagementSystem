<?php
include 'includes/conn.php';
include 'includes/functions.php';
requireRole('accountant');

$accountant_id = $_SESSION['user_id'];
$message = '';
$message_type = '';

// Get financial statistics
$total_revenue = $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM payments")->fetchColumn();
$total_outstanding = $pdo->query("SELECT COALESCE(SUM(balance), 0) FROM invoices WHERE status != 'paid'")->fetchColumn();
$total_invoices = $pdo->query("SELECT COUNT(*) FROM invoices")->fetchColumn();
$paid_invoices = $pdo->query("SELECT COUNT(*) FROM invoices WHERE status = 'paid'")->fetchColumn();
$pending_invoices = $pdo->query("SELECT COUNT(*) FROM invoices WHERE status = 'pending'")->fetchColumn();
$overdue_invoices = $pdo->query("SELECT COUNT(*) FROM invoices WHERE status = 'overdue'")->fetchColumn();
$total_scholarships = $pdo->query("SELECT COUNT(*) FROM student_scholarships WHERE status = 'active'")->fetchColumn();

// Get recent payments
$recent_payments = $pdo->query("
    SELECT p.*, u.first_name, u.last_name, u.student_number, i.invoice_number
    FROM payments p
    JOIN users u ON p.student_id = u.id
    JOIN invoices i ON p.invoice_id = i.id
    ORDER BY p.created_at DESC
    LIMIT 10
")->fetchAll();

// Get outstanding invoices
$outstanding_invoices = $pdo->query("
    SELECT i.*, u.first_name, u.last_name, u.student_number, pr.name as program_name
    FROM invoices i
    JOIN users u ON i.student_id = u.id
    LEFT JOIN programs pr ON u.program_id = pr.id
    WHERE i.status != 'paid'
    ORDER BY i.due_date ASC
    LIMIT 10
")->fetchAll();

// Get students needing clearance
$clearance_pending = $pdo->query("
    SELECT fc.*, u.first_name, u.last_name, u.student_number
    FROM financial_clearance fc
    JOIN users u ON fc.student_id = u.id
    WHERE fc.clearance_status = 'pending'
    ORDER BY fc.created_at DESC
    LIMIT 10
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accountant Dashboard - Student Record Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .stat-card {
            border-left: 4px solid;
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .revenue-card { border-left-color: #28a745; }
        .outstanding-card { border-left-color: #dc3545; }
        .invoice-card { border-left-color: #007bff; }
        .scholarship-card { border-left-color: #ffc107; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container-fluid">
            <a class="navbar-brand" href="accountant.php">
                <i class="fas fa-calculator"></i> Finance Portal
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    Welcome, <?php echo $_SESSION['first_name']; ?>
                </span>
                <a class="nav-link" href="logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2">
                <div class="list-group">
                    <a href="#dashboard" class="list-group-item list-group-item-action active" data-bs-toggle="tab">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <a href="#invoices" class="list-group-item list-group-item-action" data-bs-toggle="tab">
                        <i class="fas fa-file-invoice"></i> Invoices
                    </a>
                    <a href="#payments" class="list-group-item list-group-item-action" data-bs-toggle="tab">
                        <i class="fas fa-money-bill-wave"></i> Payments
                    </a>
                    <a href="#scholarships" class="list-group-item list-group-item-action" data-bs-toggle="tab">
                        <i class="fas fa-graduation-cap"></i> Scholarships
                    </a>
                    <a href="#clearance" class="list-group-item list-group-item-action" data-bs-toggle="tab">
                        <i class="fas fa-check-circle"></i> Clearance
                    </a>
                    <a href="#reports" class="list-group-item list-group-item-action" data-bs-toggle="tab">
                        <i class="fas fa-chart-bar"></i> Reports
                    </a>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-10">
                <div class="tab-content">
                    <!-- Dashboard Tab -->
                    <div class="tab-pane fade show active" id="dashboard">
                        <h2 class="mb-4">Financial Dashboard</h2>

                        <!-- Statistics Cards -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card stat-card revenue-card">
                                    <div class="card-body">
                                        <h6 class="text-muted">Total Revenue</h6>
                                        <h3 class="text-success">TZS <?php echo number_format($total_revenue, 2); ?></h3>
                                        <small><i class="fas fa-arrow-up"></i> All time</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card stat-card outstanding-card">
                                    <div class="card-body">
                                        <h6 class="text-muted">Outstanding Balance</h6>
                                        <h3 class="text-danger">TZS <?php echo number_format($total_outstanding, 2); ?></h3>
                                        <small><i class="fas fa-exclamation-triangle"></i> Unpaid</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card stat-card invoice-card">
                                    <div class="card-body">
                                        <h6 class="text-muted">Total Invoices</h6>
                                        <h3 class="text-primary"><?php echo $total_invoices; ?></h3>
                                        <small><?php echo $paid_invoices; ?> paid, <?php echo $pending_invoices; ?> pending</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card stat-card scholarship-card">
                                    <div class="card-body">
                                        <h6 class="text-muted">Active Scholarships</h6>
                                        <h3 class="text-warning"><?php echo $total_scholarships; ?></h3>
                                        <small><i class="fas fa-award"></i> Students</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Payments -->
                        <div class="card mb-4">
                            <div class="card-header bg-success text-white">
                                <h5><i class="fas fa-money-bill-wave"></i> Recent Payments</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Receipt #</th>
                                                <th>Student</th>
                                                <th>Invoice #</th>
                                                <th>Amount</th>
                                                <th>Method</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($recent_payments as $payment): ?>
                                            <tr>
                                                <td><?php echo $payment['receipt_number']; ?></td>
                                                <td>
                                                    <?php echo $payment['first_name'] . ' ' . $payment['last_name']; ?>
                                                    <br><small class="text-muted"><?php echo $payment['student_number']; ?></small>
                                                </td>
                                                <td><?php echo $payment['invoice_number']; ?></td>
                                                <td class="text-success fw-bold">TZS <?php echo number_format($payment['amount'], 2); ?></td>
                                                <td>
                                                    <span class="badge bg-info"><?php echo ucfirst(str_replace('_', ' ', $payment['payment_method'])); ?></span>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($payment['payment_date'])); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Outstanding Invoices -->
                        <div class="card">
                            <div class="card-header bg-danger text-white">
                                <h5><i class="fas fa-exclamation-circle"></i> Outstanding Invoices</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Invoice #</th>
                                                <th>Student</th>
                                                <th>Program</th>
                                                <th>Total</th>
                                                <th>Paid</th>
                                                <th>Balance</th>
                                                <th>Due Date</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($outstanding_invoices as $invoice): ?>
                                            <tr>
                                                <td><?php echo $invoice['invoice_number']; ?></td>
                                                <td>
                                                    <?php echo $invoice['first_name'] . ' ' . $invoice['last_name']; ?>
                                                    <br><small class="text-muted"><?php echo $invoice['student_number']; ?></small>
                                                </td>
                                                <td><small><?php echo $invoice['program_name']; ?></small></td>
                                                <td>TZS <?php echo number_format($invoice['total_amount'], 2); ?></td>
                                                <td class="text-success">TZS <?php echo number_format($invoice['amount_paid'], 2); ?></td>
                                                <td class="text-danger fw-bold">TZS <?php echo number_format($invoice['balance'], 2); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($invoice['due_date'])); ?></td>
                                                <td>
                                                    <?php
                                                    $badge_class = [
                                                        'pending' => 'warning',
                                                        'partial' => 'info',
                                                        'overdue' => 'danger'
                                                    ];
                                                    ?>
                                                    <span class="badge bg-<?php echo $badge_class[$invoice['status']]; ?>">
                                                        <?php echo ucfirst($invoice['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Other tabs will be added here -->
                    <div class="tab-pane fade" id="invoices">
                        <h2>Invoice Management</h2>
                        <p class="text-muted">Generate and manage student invoices</p>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Invoice management features coming soon...
                        </div>
                    </div>

                    <div class="tab-pane fade" id="payments">
                        <h2>Payment Processing</h2>
                        <p class="text-muted">Record and track student payments</p>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Payment processing features coming soon...
                        </div>
                    </div>

                    <div class="tab-pane fade" id="scholarships">
                        <h2>Scholarship Management</h2>
                        <p class="text-muted">Manage scholarships and awards</p>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Scholarship management features coming soon...
                        </div>
                    </div>

                    <div class="tab-pane fade" id="clearance">
                        <h2>Financial Clearance</h2>
                        <p class="text-muted">Approve financial clearance for students</p>
                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Student</th>
                                                <th>Academic Year</th>
                                                <th>Semester</th>
                                                <th>Total Fees</th>
                                                <th>Paid</th>
                                                <th>Balance</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($clearance_pending as $clearance): ?>
                                            <tr>
                                                <td>
                                                    <?php echo $clearance['first_name'] . ' ' . $clearance['last_name']; ?>
                                                    <br><small class="text-muted"><?php echo $clearance['student_number']; ?></small>
                                                </td>
                                                <td><?php echo $clearance['academic_year']; ?></td>
                                                <td>Semester <?php echo $clearance['semester']; ?></td>
                                                <td>TZS <?php echo number_format($clearance['total_fees'], 2); ?></td>
                                                <td class="text-success">TZS <?php echo number_format($clearance['total_paid'], 2); ?></td>
                                                <td class="text-danger">TZS <?php echo number_format($clearance['balance'], 2); ?></td>
                                                <td>
                                                    <span class="badge bg-warning"><?php echo ucfirst($clearance['clearance_status']); ?></span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-success" title="Approve Clearance">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="reports">
                        <h2>Financial Reports</h2>
                        <p class="text-muted">Generate financial reports and analytics</p>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Reporting features coming soon...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
