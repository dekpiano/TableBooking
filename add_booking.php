<?php
session_start();
include_once 'api/connectDB.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $lastName = $_POST['lastName'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $tableID = $_POST['TableID'] ?? '';
    $payment_amount = $_POST['payment_amount'] ?? 0;

    // Basic validation
    if (empty($name) || empty($lastName) || empty($phone) || empty($tableID) || !is_numeric($payment_amount) || $payment_amount < 0) {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'กรุณากรอกข้อมูลให้ครบถ้วนและถูกต้อง'];
        header('Location: admin.php');
        exit();
    }

    // Fetch current event year
    $res_year = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key = 'current_event_year'");
    $current_year = ($res_year && $res_year->num_rows > 0) ? (int)$res_year->fetch_assoc()['setting_value'] : 2025;

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO bookings (name, lastName, phone, TableID, payment_amount, status, created_at, event_year) VALUES (?, ?, ?, ?, ?, 'pending', NOW(), ?)");
    $stmt->bind_param("ssssdi", $name, $lastName, $phone, $tableID, $payment_amount, $current_year);

    if ($stmt->execute()) {
        $_SESSION['message'] = ['type' => 'success', 'text' => 'เพิ่มการจองใหม่เรียบร้อยแล้ว!'];
    } else {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'เกิดข้อผิดพลาดในการเพิ่มการจอง: ' . $stmt->error];
    }

    $stmt->close();
    $conn->close();

    header('Location: admin.php');
    exit();
} else {
    // If not a POST request, redirect to admin page
    header('Location: admin.php');
    exit();
}
?>