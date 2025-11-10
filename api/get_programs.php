<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

if (isset($_GET['department_id'])) {
    $department_id = (int)$_GET['department_id'];
    
    $stmt = $pdo->prepare("SELECT id, name, code, duration_years, total_credits, tuition_per_semester 
                          FROM programs WHERE department_id = ? ORDER BY name");
    $stmt->execute([$department_id]);
    $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($programs);
} else {
    echo json_encode([]);
}
?>
