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
    $booking_id = $_POST['booking_id'] ?? null;
    $name = $_POST['name'] ?? null;
    $lastName = $_POST['lastName'] ?? null;
    $phone = $_POST['phone'] ?? null;

    if (!$booking_id || !$name || !$lastName || !$phone) {
        echo json_encode(['success' => false, 'message' => 'ข้อมูลไม่ครบถ้วน']);
        exit();
    }

    // Update booking info
    $stmt = $conn->prepare("UPDATE bookings SET name = ?, lastName = ?, phone = ? WHERE id = ?");
    $stmt->bind_param("sssi", $name, $lastName, $phone, $booking_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'อัปเดตข้อมูลเรียบร้อยแล้ว']);
    } else {
        echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $conn->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>
