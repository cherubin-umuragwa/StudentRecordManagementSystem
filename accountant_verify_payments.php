<?php
session_start();
require_once 'includes/config.php';

// Check if user is logged in and is an accountant
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'accountant') {
    header("Location: index.php");
    exit();
}

// Handle payment verification
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['verify_payment'])) {
        $payment_id = $_POST['payment_id'];
        $accountant_id = $_SESSION['user_id'];
        
        $stmt = $pdo->prepare("
            UPDATE student_payments 
            SET status = 'verified', verified_by = ?, verified_at = NOW() 
            WHERE id = ?
        ");
        
        try {
            $stmt->execute([$accountant_id, $payment_id]);
            $_SESSION['success_message'] = "Payment verified successfully!";
        } catch (PDOException $e) {
            $_SESSION['error_message'] = "Error verifying payment.";
        }
    } elseif (isset($_POST['reject_payment'])) {
        $payment_id = $_POST['payment_id'];
        $rejection_reason = $_POST['rejection_reason'];
        $accountant_id = $_SESSION['user_id'];
        
        $stmt = $pdo->prepare("
            UPDATE student_payments 
            SET status = 'rejected', verified_by = ?, verified_at = NOW(), rejection_reason = ? 
            WHERE id = ?
        ");
        
        try {
            $stmt->execute([$accountant_id, $rejection_reason, $payment_id]);
            $_SESSION['success_message'] = "Payment rejected.";
        } catch (PDOException $e) {
            $_SESSION['error_message'] = "Error rejecting payment.";
        }
    }
    
    header("Location: accountant_verify_payments.php");
    exit();
}

// Get pending payments
$stmt = $pdo->prepare("
    SELECT sp.*, u.first_name, u.last_name, u.student_number, u.email,
           p.name as program_name
    FROM student_payments sp
    JOIN users u ON sp.student_id = u.id
    LEFT JOIN programs p ON u.program_id = p.id
    WHERE sp.status = 'pending'
    ORDER BY sp.submitted_at DESC
");
$stmt->execute();
$pending_payments = $stmt->fetchAll();

// Get verified payments
$stmt = $pdo->prepare("
    SELECT sp.*, u.first_name, u.last_name, u.student_number,
           CONCAT(v.first_name, ' ', v.last_name) as verified_by_name
    FROM student_payments sp
    JOIN users u ON sp.student_id = u.id
    LEFT JOIN users v ON sp.verified_by = v.id
    WHERE sp.status = 'verified'
    ORDER BY sp.verified_at DESC
    LIMIT 50
");
$stmt->execute();
$verified_payments = $stmt->fetchAll();

// Get rejected payments
$stmt = $pdo->prepare("
    SELECT sp.*, u.first_name, u.last_name, u.student_number,
           CONCAT(v.first_name, ' ', v.last_name) as verified_by_name
    FROM student_payments sp
    JOIN users u ON sp.student_id = u.id
    LEFT JOIN users v ON sp.verified_by = v.id
    WHERE sp.status = 'rejected'
    ORDER BY sp.verified_at DESC
    LIMIT 50
");
$stmt->execute();
$rejected_payments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Payments - Accountant Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/accountant_navbar.php'; ?>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 ms-sm-auto px-4 py-4">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <h2 class="mb-4"><i class="fas fa-check-circle me-2"></i>Payment Verification</h2>

        <!-- Tabs -->
        <ul class="nav nav-tabs mb-4" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#pending">
                    <i class="fas fa-clock me-2"></i>Pending 
                    <span class="badge bg-warning"><?php echo count($pending_payments); ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#verified">
                    <i class="fas fa-check me-2"></i>Verified
                    <span class="badge bg-success"><?php echo count($verified_payments); ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#rejected">
                    <i class="fas fa-times me-2"></i>Rejected
                    <span class="badge bg-danger"><?php echo count($rejected_payments); ?></span>
                </a>
            </li>
        </ul>

        <div class="tab-content">
            <!-- Pending Payments -->
            <div class="tab-pane fade show active" id="pending">
                <?php if (empty($pending_payments)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No pending payments to verify.
                    </div>
                <?php else: ?>
                    <?php foreach ($pending_payments as $payment): ?>
                        <div class="card mb-3">
                            <div class="card-header bg-warning">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h5 class="mb-0">
                                            <?php echo htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']); ?>
                                            <small class="text-muted">(<?php echo htmlspecialchars($payment['student_number']); ?>)</small>
                                        </h5>
                                        <small><?php echo htmlspecialchars($payment['program_name']); ?></small>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <h4 class="mb-0">UGX <?php echo number_format($payment['amount']); ?></h4>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Payment Date:</strong> <?php echo date('d M Y', strtotime($payment['payment_date'])); ?></p>
                                        <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($payment['payment_method']); ?></p>
                                        <p><strong>Transaction Ref:</strong> <?php echo htmlspecialchars($payment['transaction_reference']); ?></p>
                                        <p><strong>Academic Year:</strong> <?php echo $payment['academic_year']; ?> - Semester <?php echo $payment['semester']; ?></p>
                                        <?php if ($payment['notes']): ?>
                                            <p><strong>Notes:</strong> <?php echo htmlspecialchars($payment['notes']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Submitted:</strong> <?php echo date('d M Y H:i', strtotime($payment['submitted_at'])); ?></p>
                                        <p><strong>Student Email:</strong> <?php echo htmlspecialchars($payment['email']); ?></p>
                                        <?php if ($payment['payment_proof']): ?>
                                            <p><strong>Proof of Payment:</strong></p>
                                            <a href="<?php echo htmlspecialchars($payment['payment_proof']); ?>" 
                                               target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-file-download me-2"></i>View Proof
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <hr>
                                <div class="d-flex gap-2">
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="payment_id" value="<?php echo $payment['id']; ?>">
                                        <button type="submit" name="verify_payment" class="btn btn-success">
                                            <i class="fas fa-check me-2"></i>Verify Payment
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-danger" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#rejectModal<?php echo $payment['id']; ?>">
                                        <i class="fas fa-times me-2"></i>Reject Payment
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Reject Modal -->
                        <div class="modal fade" id="rejectModal<?php echo $payment['id']; ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title">Reject Payment</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="POST">
                                        <div class="modal-body">
                                            <input type="hidden" name="payment_id" value="<?php echo $payment['id']; ?>">
                                            <div class="mb-3">
                                                <label class="form-label">Reason for Rejection</label>
                                                <textarea class="form-control" name="rejection_reason" rows="3" required></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" name="reject_payment" class="btn btn-danger">Reject</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Verified Payments -->
            <div class="tab-pane fade" id="verified">
                <?php if (empty($verified_payments)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No verified payments yet.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Amount</th>
                                    <th>Payment Date</th>
                                    <th>Method</th>
                                    <th>Transaction Ref</th>
                                    <th>Verified By</th>
                                    <th>Verified At</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($verified_payments as $payment): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']); ?></td>
                                    <td>UGX <?php echo number_format($payment['amount']); ?></td>
                                    <td><?php echo date('d M Y', strtotime($payment['payment_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($payment['payment_method']); ?></td>
                                    <td><?php echo htmlspecialchars($payment['transaction_reference']); ?></td>
                                    <td><?php echo htmlspecialchars($payment['verified_by_name']); ?></td>
                                    <td><?php echo date('d M Y H:i', strtotime($payment['verified_at'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Rejected Payments -->
            <div class="tab-pane fade" id="rejected">
                <?php if (empty($rejected_payments)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No rejected payments.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Amount</th>
                                    <th>Payment Date</th>
                                    <th>Transaction Ref</th>
                                    <th>Rejection Reason</th>
                                    <th>Rejected By</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rejected_payments as $payment): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']); ?></td>
                                    <td>UGX <?php echo number_format($payment['amount']); ?></td>
                                    <td><?php echo date('d M Y', strtotime($payment['payment_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($payment['transaction_reference']); ?></td>
                                    <td><?php echo htmlspecialchars($payment['rejection_reason']); ?></td>
                                    <td><?php echo htmlspecialchars($payment['verified_by_name']); ?></td>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
