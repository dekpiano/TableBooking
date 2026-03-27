<?php
session_start();
header('Content-Type: application/json');
include_once 'connectDB.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Get all participants who haven't won yet
$sql = "SELECT id, full_name, phone, lucky_code FROM tb_evaluations WHERE is_winner = 0";
$result = $conn->query($sql);

$pool = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $pool[] = $row;
    }
}

// Also get stats
$statsSql = "SELECT COUNT(*) as total_count FROM tb_evaluations";
$statsResult = $conn->query($statsSql);
$totalCount = $statsResult->fetch_assoc()['total_count'];

// Winners count
$winnersSql = "SELECT COUNT(*) as win_count FROM tb_evaluations WHERE is_winner = 1";
$winnersResult = $conn->query($winnersSql);
$winCount = $winnersResult->fetch_assoc()['win_count'];

echo json_encode(['success' => true, 'data' => $pool, 'total' => (int)$totalCount, 'winners' => (int)$winCount]);

$conn->close();
?>
