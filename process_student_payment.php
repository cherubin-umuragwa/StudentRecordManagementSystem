<?php
session_start();
require_once 'includes/config.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = $_SESSION['user_id'];
    $payment_date = $_POST['payment_date'];
    $amount = $_POST['amount'];
    $payment_method = $_POST['payment_method'];
    $transaction_reference = $_POST['transaction_reference'];
    $notes = $_POST['notes'] ?? '';
    
    // Get student's current academic year and semester
    $stmt = $pdo->prepare("SELECT current_year, current_semester FROM users WHERE id = ?");
    $stmt->execute([$student_id]);
    $student = $stmt->fetch();
    
    // Handle file upload
    $payment_proof = '';
    if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] == 0) {
        $upload_dir = 'uploads/payments/';
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['payment_proof']['name'], PATHINFO_EXTENSION);
        $new_filename = 'payment_' . $student_id . '_' . time() . '.' . $file_extension;
        $upload_path = $upload_dir . $new_filename;
        
        if (move_uploaded_file($_FILES['payment_proof']['tmp_name'], $upload_path)) {
            $payment_proof = $upload_path;
        }
    }
    
    // Insert payment record
    $stmt = $pdo->prepare("
        INSERT INTO student_payments 
        (student_id, academic_year, semester, payment_date, amount, payment_method, 
         transaction_reference, payment_proof, notes, status, submitted_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
    ");
    
    try {
        $stmt->execute([
            $student_id, 
            $student['current_year'], 
            $student['current_semester'],
            $payment_date, 
            $amount, 
            $payment_method, 
            $transaction_reference, 
            $payment_proof, 
            $notes
        ]);
        $_SESSION['success_message'] = "Payment details submitted successfully! Awaiting verification by accountant.";
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error submitting payment: " . $e->getMessage();
    }
    
    header("Location: student_financial_statement.php");
    exit();
}
?>
