<?php
header('Content-Type: application/json');
session_start();
include_once 'connectDB.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];

    $sql = "DELETE FROM tb_evaluations WHERE id = $id";

    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'message' => 'Evaluation deleted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}

$conn->close();
?>
