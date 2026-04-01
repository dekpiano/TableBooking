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

// Get all participants who haven't won yet in THIS YEAR
$sql = "SELECT id, full_name, phone, lucky_code FROM tb_evaluations WHERE is_winner = 0 AND event_year = $event_year";
$result = $conn->query($sql);

$pool = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $pool[] = $row;
    }
}

// Also get stats for THIS YEAR
$statsSql = "SELECT COUNT(*) as total_count FROM tb_evaluations WHERE event_year = $event_year";
$statsResult = $conn->query($statsSql);
$totalCount = ($statsResult) ? $statsResult->fetch_assoc()['total_count'] : 0;

// Winners count for THIS YEAR
$winnersSql = "SELECT COUNT(*) as win_count FROM tb_evaluations WHERE is_winner = 1 AND event_year = $event_year";
$winnersResult = $conn->query($winnersSql);
$winCount = ($winnersResult) ? $winnersResult->fetch_assoc()['win_count'] : 0;

echo json_encode(['success' => true, 'data' => $pool, 'total' => (int)$totalCount, 'winners' => (int)$winCount, 'event_year' => $event_year]);

$conn->close();
?>
