<?php
session_start();
header('Content-Type: application/json');
include_once 'connectDB.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Get current event year from settings
$res_year = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key = 'current_event_year'");
$event_year = ($res_year && $res_year->num_rows > 0) ? (int)$res_year->fetch_assoc()['setting_value'] : 2025;

// Get all winners for THIS YEAR
$sql = "SELECT id, full_name, phone, lucky_code, updated_at as won_at 
        FROM tb_evaluations 
        WHERE is_winner = 1 AND event_year = $event_year
        ORDER BY updated_at DESC";
$result = $conn->query($sql);

$winners = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $winners[] = $row;
    }
}

echo json_encode(['success' => true, 'data' => $winners, 'event_year' => $event_year]);

$conn->close();
?>
