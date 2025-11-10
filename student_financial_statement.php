<?php
session_start();
require_once 'includes/config.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: index.php");
    exit();
}

$student_id = $_SESSION['user_id'];

// Get student information
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

// Get current semester and year
$current_year = $student['current_year'];
$current_semester = $student['current_semester'];

// Calculate total credits enrolled
$stmt = $pdo->prepare("
    SELECT SUM(c.credits) as total_credits 
    FROM course_registrations cr
    JOIN courses c ON cr.course_id = c.id
    WHERE cr.student_id = ? AND cr.academic_year = ? AND cr.semester = ? AND cr.status = 'approved'
");
$stmt->execute([$student_id, $current_year, $current_semester]);
$credits_result = $stmt->fetch();
$total_credits = $credits_result['total_credits'] ?? 0;

// Calculate fees
$credit_fee = 51000; // UGX per credit
$computer_lab_fee = 140000; // UGX
$functional_fee = ($current_year == 1 && $current_semester == 1) ? 650000 : 530000; // UGX

$tuition_fee = $total_credits * $credit_fee;
$total_amount_due = $tuition_fee + $computer_lab_fee + $functional_fee;

// Get total amount paid
$stmt = $pdo->prepare("
    SELECT SUM(amount) as total_paid 
    FROM student_payments 
    WHERE student_id = ? AND status = 'verified' AND academic_year = ? AND semester = ?
");
$stmt->execute([$student_id, $current_year, $current_semester]);
$payment_result = $stmt->fetch();
$total_paid = $payment_result['total_paid'] ?? 0;

// Calculate balance and percentage
$balance = $total_amount_due - $total_paid;
$percentage_paid = $total_amount_due > 0 ? ($total_paid / $total_amount_due) * 100 : 0;

// Get payment history
$stmt = $pdo->prepare("
    SELECT sp.*, CONCAT(u.first_name, ' ', u.last_name) as verified_by_name
    FROM student_payments sp
    LEFT JOIN users u ON sp.verified_by = u.id
    WHERE sp.student_id = ?
    ORDER BY sp.payment_date DESC
");
$stmt->execute([$student_id]);
$payments = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial Statement - Student Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
    <style>
        .financial-card {
            border-left: 4px solid;
            transition: transform 0.2s;
        }
        .financial-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .amount-due { border-left-color: #dc3545; }
        .amount-paid { border-left-color: #28a745; }
        .balance { border-left-color: #ffc107; }
        .percentage { border-left-color: #17a2b8; }
        
        .progress-lg {
            height: 30px;
            font-size: 16px;
            font-weight: bold;
        }
        
        @media print {
            .no-print { display: none; }
            .card { border: 1px solid #ddd !important; }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/student_navbar.php'; ?>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 ms-sm-auto px-4 py-4">
        <!-- Header -->
        <div class="row mb-4 no-print">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2><i class="fas fa-file-invoice-dollar me-2"></i>Financial Statement</h2>
                        <p class="text-muted">Academic Year <?php echo $current_year; ?> - Semester <?php echo $current_semester; ?></p>
                    </div>
                    <div>
                        <button class="btn btn-success me-2" onclick="window.print()">
                            <i class="fas fa-download me-2"></i>Download Statement
                        </button>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#paymentModal">
                            <i class="fas fa-plus me-2"></i>Enter Payment Details
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Financial Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card financial-card amount-due">
                    <div class="card-body">
                        <h6 class="text-muted">Total Amount Due</h6>
                        <h3 class="text-danger">UGX <?php echo number_format($total_amount_due); ?></h3>
                        <small class="text-muted">For this semester</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card financial-card amount-paid">
                    <div class="card-body">
                        <h6 class="text-muted">Amount Paid</h6>
                        <h3 class="text-success">UGX <?php echo number_format($total_paid); ?></h3>
                        <small class="text-muted">Verified payments</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card financial-card balance">
                    <div class="card-body">
                        <h6 class="text-muted">Balance</h6>
                        <h3 class="text-warning">UGX <?php echo number_format($balance); ?></h3>
                        <small class="text-muted">Remaining to pay</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card financial-card percentage">
                    <div class="card-body">
                        <h6 class="text-muted">Payment Progress</h6>
                        <h3 class="text-info"><?php echo number_format($percentage_paid, 1); ?>%</h3>
                        <small class="text-muted">Completed</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Progress Bar -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Payment Progress</h5>
                        <div class="progress progress-lg">
                            <div class="progress-bar <?php echo $percentage_paid >= 100 ? 'bg-success' : 'bg-info'; ?> progress-bar-striped progress-bar-animated" 
                                 role="progressbar" 
                                 style="width: <?php echo min($percentage_paid, 100); ?>%" 
                                 aria-valuenow="<?php echo $percentage_paid; ?>" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                <?php echo number_format($percentage_paid, 1); ?>%
                            </div>
                        </div>
                        <p class="text-muted mt-2 mb-0">
                            <?php if ($percentage_paid >= 100): ?>
                                <i class="fas fa-check-circle text-success"></i> Fully paid! Thank you.
                            <?php elseif ($percentage_paid >= 60): ?>
                                <i class="fas fa-info-circle text-info"></i> You're making good progress. Keep it up!
                            <?php else: ?>
                                <i class="fas fa-exclamation-circle text-warning"></i> Please complete your payment to avoid registration issues.
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fee Breakdown -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Fee Breakdown</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Fee Type</th>
                                    <th>Details</th>
                                    <th class="text-end">Amount (UGX)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Tuition Fee</strong></td>
                                    <td><?php echo $total_credits; ?> credits × UGX <?php echo number_format($credit_fee); ?> per credit</td>
                                    <td class="text-end"><?php echo number_format($tuition_fee); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Computer Lab Fee</strong></td>
                                    <td>Semester fee</td>
                                    <td class="text-end"><?php echo number_format($computer_lab_fee); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Functional Fee</strong></td>
                                    <td><?php echo ($current_year == 1 && $current_semester == 1) ? 'New Student' : 'Continuing Student'; ?></td>
                                    <td class="text-end"><?php echo number_format($functional_fee); ?></td>
                                </tr>
                                <tr class="table-primary">
                                    <td colspan="2"><strong>Total Amount Due</strong></td>
                                    <td class="text-end"><strong><?php echo number_format($total_amount_due); ?></strong></td>
                                </tr>
                                <tr class="table-success">
                                    <td colspan="2"><strong>Total Paid</strong></td>
                                    <td class="text-end"><strong><?php echo number_format($total_paid); ?></strong></td>
                                </tr>
                                <tr class="table-warning">
                                    <td colspan="2"><strong>Balance</strong></td>
                                    <td class="text-end"><strong><?php echo number_format($balance); ?></strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment History -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Payment History</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($payments)): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> No payment records found.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Transaction Ref</th>
                                            <th>Payment Method</th>
                                            <th>Amount (UGX)</th>
                                            <th>Status</th>
                                            <th>Verified By</th>
                                            <th class="no-print">Proof</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($payments as $payment): ?>
                                        <tr>
                                            <td><?php echo date('d M Y', strtotime($payment['payment_date'])); ?></td>
                                            <td><?php echo htmlspecialchars($payment['transaction_reference']); ?></td>
                                            <td><?php echo htmlspecialchars($payment['payment_method']); ?></td>
                                            <td><?php echo number_format($payment['amount']); ?></td>
                                            <td>
                                                <?php if ($payment['status'] == 'verified'): ?>
                                                    <span class="badge bg-success">Verified</span>
                                                <?php elseif ($payment['status'] == 'pending'): ?>
                                                    <span class="badge bg-warning">Pending</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Rejected</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo $payment['verified_by_name'] ?? 'N/A'; ?></td>
                                            <td class="no-print">
                                                <?php if ($payment['payment_proof']): ?>
                                                    <a href="<?php echo htmlspecialchars($payment['payment_proof']); ?>" 
                                                       target="_blank" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-file-download"></i> View
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
            </div>
        </div>
    </div>

    <!-- Payment Entry Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="paymentModalLabel">
                        <i class="fas fa-money-bill-wave me-2"></i>Enter Payment Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="process_student_payment.php" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Note:</strong> Your payment will be verified by the accountant before being reflected in your account.
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Payment Date</label>
                                <input type="date" class="form-control" name="payment_date" required 
                                       max="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Amount Paid (UGX)</label>
                                <input type="number" class="form-control" name="amount" required 
                                       min="1" placeholder="e.g., 500000">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Payment Method</label>
                                <select class="form-select" name="payment_method" required>
                                    <option value="">Select Method</option>
                                    <option value="Bank Transfer">Bank Transfer</option>
                                    <option value="MTN Mobile Money">MTN Mobile Money</option>
                                    <option value="Airtel Money">Airtel Money</option>
                                    <option value="Cash">Cash</option>
                                    <option value="Cheque">Cheque</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Transaction Reference</label>
                                <input type="text" class="form-control" name="transaction_reference" required 
                                       placeholder="e.g., TXN123456789">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label required">Proof of Payment</label>
                                <input type="file" class="form-control" name="payment_proof" required 
                                       accept=".pdf,.jpg,.jpeg,.png">
                                <small class="text-muted">Upload receipt/screenshot (PDF, JPG, PNG - Max 5MB)</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Additional Notes</label>
                                <textarea class="form-control" name="notes" rows="3" 
                                          placeholder="Any additional information about this payment"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-2"></i>Submit Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
