<?php
session_start(); // Start session for messages
include_once 'api/connectDB.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $guests = $_POST['guests'];
    $message = $_POST['message'];
    $tableID = $_POST['TableID'] ?? '';
    $slip_path = NULL;

    // Basic validation for TableID
    if (empty($tableID)) {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Error: TableID is required.'];
        header("Location: index2.html");
        exit();
    }

    // จัดการการอัปโหลดสลิป
    if (isset($_FILES['slip']) && $_FILES['slip']['error'] == 0) {
        $target_dir = "uploads/slips/";
        $file_extension = pathinfo($_FILES["slip"]["name"], PATHINFO_EXTENSION);
        $new_file_name = uniqid('slip_') . '.' . $file_extension;
        $target_file = $target_dir . $new_file_name;
        
        // ตรวจสอบประเภทไฟล์
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
        if (in_array(strtolower($file_extension), $allowed_types)) {
            if (move_uploaded_file($_FILES["slip"]["tmp_name"], $target_file)) {
                $slip_path = $target_file;
            } else {
                $_SESSION['message'] = ['type' => 'error', 'text' => 'ขออภัย เกิดข้อผิดพลาดในการอัปโหลดไฟล์ของคุณ.'];
                header("Location: index2.html");
                exit();
            }
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'ขออภัย อนุญาตเฉพาะไฟล์ JPG, JPEG, PNG, GIF และ PDF เท่านั้น.'];
            header("Location: index2.html");
            exit();
        }
    }

    // เตรียมและผูกพารามิเตอร์ (Added TableID)
    $stmt = $conn->prepare("INSERT INTO bookings (name, email, phone, booking_date, booking_time, guests, message, TableID, slip_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssisss", $name, $email, $phone, $date, $time, $guests, $message, $tableID, $slip_path);

    // รันคำสั่ง
    if ($stmt->execute()) {
        $_SESSION['message'] = ['type' => 'success', 'text' => 'การจองของคุณถูกส่งเรียบร้อยแล้ว!'];
        header("Location: index2.html");
        exit();
    } else {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Error: ' . $stmt->error];
        header("Location: index2.html");
        exit();
    }

    $stmt->close();
}

$conn->close();
?>