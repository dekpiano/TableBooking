<?php
include_once 'api/connectDB.php';

// ตรวจสอบการเชื่อมต่อ (connectDB.php จะจัดการ error แล้ว)
// if ($conn->connect_error) {
//     die("Connection failed: " . $conn->connect_error);
// }

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $guests = $_POST['guests'];
    $message = $_POST['message'];
    $slip_path = NULL;

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
                echo "ขออภัย เกิดข้อผิดพลาดในการอัปโหลดไฟล์ของคุณ.";
            }
        } else {
            echo "ขออภัย อนุญาตเฉพาะไฟล์ JPG, JPEG, PNG, GIF และ PDF เท่านั้น.";
        }
    }

    // เตรียมและผูกพารามิเตอร์
    $stmt = $conn->prepare("INSERT INTO bookings (name, email, phone, booking_date, booking_time, guests, message, slip_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssiss", $name, $email, $phone, $date, $time, $guests, $message, $slip_path);

    // รันคำสั่ง
    if ($stmt->execute()) {
        header("Location: index2.html");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>