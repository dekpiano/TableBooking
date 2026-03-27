<?php
session_start();
header('Content-Type: application/json');

include_once 'connectDB.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $table_id = $conn->real_escape_string($_POST['table_id']);
    $status = $conn->real_escape_string($_POST['status']);
    
    // Check if status is valid
    $valid_statuses = ['available', 'blocked_visible', 'blocked_hidden'];
    if (!in_array($status, $valid_statuses)) {
        echo json_encode(['success' => false, 'message' => 'Invalid status']);
        exit();
    }

    // Upsert status
    $sql = "INSERT INTO tb_tables_config (table_id, status) 
            VALUES ('$table_id', '$status') 
            ON DUPLICATE KEY UPDATE status = VALUES(status)";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating status: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>
