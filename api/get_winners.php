<?php
session_start();
header('Content-Type: application/json');
include_once 'connectDB.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Get all winners
$sql = "SELECT id, full_name, phone, lucky_code, updated_at as won_at 
        FROM tb_evaluations 
        WHERE is_winner = 1 
        ORDER BY updated_at DESC";
$result = $conn->query($sql);

$winners = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $winners[] = $row;
    }
}

echo json_encode(['success' => true, 'data' => $winners]);

$conn->close();
?>
