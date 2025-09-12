<?php
session_start();
include_once 'api/connectDB.php';

if (isset($_GET['id']) && isset($_GET['status'])) {
    $id = $_GET['id'];
    $status = $_GET['status'];
    $admin_id = $_SESSION['admin_id'] ?? null; // Get admin_id from session

    // ตรวจสอบว่าสถานะที่ส่งมาถูกต้อง
    if ($status == 'verified') {
        if ($admin_id === null) {
            echo "Admin not logged in.";
            exit();
        }
        $stmt = $conn->prepare("UPDATE bookings SET status = ?, approved_by_admin_id = ? WHERE id = ?");
        if ($stmt === false) {
            echo "Error preparing statement: " . $conn->error;
            exit();
        }
        $stmt->bind_param("sii", $status, $admin_id, $id);
    } else if ($status == 'rejected') {
        $stmt = $conn->prepare("UPDATE bookings SET status = ?, approved_by_admin_id = NULL WHERE id = ?");
        if ($stmt === false) {
            echo "Error preparing statement: " . $conn->error;
            exit();
        }
        $stmt->bind_param("si", $status, $id);
    } else if ($status == 'pending') { // สำหรับยกเลิกการอนุมัติ
        $stmt = $conn->prepare("UPDATE bookings SET status = ?, approved_by_admin_id = NULL WHERE id = ?");
        if ($stmt === false) {
            echo "Error preparing statement: " . $conn->error;
            exit();
        }
        $stmt->bind_param("si", $status, $id);
    } else {
        echo "Invalid status provided.";
        exit();
    }

    // รันคำสั่ง
    if ($stmt->execute()) {
        header("Location: admin.php");
        exit();
    } else {
        echo "Error updating record: " . $stmt->error;
    }
} else {
    echo "Missing ID or status parameter.";
}

$conn->close();
?>