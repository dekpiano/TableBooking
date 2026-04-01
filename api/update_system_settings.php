<?php
session_start();
include_once 'connectDB.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_mode = $_POST['booking_mode'] ?? 'open';

    // Check if system_settings table exists (in case it didn't during manual creation)
    $sql_create = "CREATE TABLE IF NOT EXISTS system_settings (
        setting_key VARCHAR(50) PRIMARY KEY,
        setting_value VARCHAR(255) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
    $conn->query($sql_create);

    $stmt = $conn->prepare("INSERT INTO system_settings (setting_key, setting_value) 
                            VALUES ('booking_mode', ?) 
                            ON DUPLICATE KEY UPDATE setting_value = ?");
    $stmt->bind_param("ss", $booking_mode, $booking_mode);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'ตั้งค่าระบบเรียบร้อยแล้ว']);
    } else {
        echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $conn->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>
