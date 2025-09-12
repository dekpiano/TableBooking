<?php

$servername = "localhost";
$username = "root"; // เปลี่ยนเป็นชื่อผู้ใช้ฐานข้อมูลของคุณ
$password = ""; // เปลี่ยนเป็นรหัสผ่านฐานข้อมูลของคุณ
$dbname = "tb_bookings"; // เปลี่ยนเป็นชื่อฐานข้อมูลของคุณ

// สร้างการเชื่อมต่อ
$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    // สำหรับ API ควรส่ง JSON error
    if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]);
        exit();
    } else {
        // สำหรับหน้าเว็บปกติ ให้แสดงข้อความ error
        die("Connection failed: " . $conn->connect_error);
    }
}

// ตั้งค่า charset เป็น utf8mb4 เพื่อรองรับภาษาไทยและอีโมจิ
$conn->set_charset("utf8mb4");

?>