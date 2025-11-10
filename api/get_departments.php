<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

if (isset($_GET['school_id'])) {
    $school_id = (int)$_GET['school_id'];
    
    $stmt = $pdo->prepare("SELECT id, name, code FROM departments WHERE school_id = ? ORDER BY name");
    $stmt->execute([$school_id]);
    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($departments);
} else {
    echo json_encode([]);
}
?>
