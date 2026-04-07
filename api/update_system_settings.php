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
    // Check if system_settings table exists
    $sql_create = "CREATE TABLE IF NOT EXISTS system_settings (
        setting_key VARCHAR(50) PRIMARY KEY,
        setting_value VARCHAR(255) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
    $conn->query($sql_create);

    $success = true;
    $message = 'ตั้งค่าระบบเรียบร้อยแล้ว';

    // Loop through all POST data and update settings
    foreach ($_POST as $key => $value) {
        $key = $conn->real_escape_string($key);
        $value = $conn->real_escape_string($value);

        $stmt = $conn->prepare("INSERT INTO system_settings (setting_key, setting_value) 
                                VALUES (?, ?) 
                                ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->bind_param("sss", $key, $value, $value);
        
        if (!$stmt->execute()) {
            $success = false;
            $message = 'เกิดข้อผิดพลาดในการอัปเดต ' . $key;
            break;
        }
        $stmt->close();
    }

    echo json_encode(['success' => $success, 'message' => $message]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>
