<?php
header('Content-Type: application/json');
// Temporarily enable error reporting for debugging. REMOVE IN PRODUCTION!
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

try {
    include_once 'connectDB.php';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // รับข้อมูลจากฟอร์ม
        $tableId = $_POST['tableId'] ?? '';
        $name = $_POST['name'] ?? '';
        $lastName = $_POST['lastName'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $batch = $_POST['batch'] ?? '';
        $gradYear = $_POST['gradYear'] ?? '';
        $paymentAmount = $_POST['payment_amount'] ?? 0;
        $transferDate = $_POST['transferDate'] ?? NULL;
        $transferTime = $_POST['transferTime'] ?? NULL;

        $slip_path = NULL;

        // จัดการการอัปโหลดสลิป
        if (isset($_FILES['slipUpload'])) {
            $upload_error = $_FILES['slipUpload']['error'];
            if ($upload_error !== UPLOAD_ERR_OK) {
                $phpFileUploadErrors = array(
                    UPLOAD_ERR_OK         => 'There is no error, the file uploaded with success',
                    UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
                    UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
                    UPLOAD_ERR_PARTIAL    => 'The uploaded file was only partially uploaded',
                    UPLOAD_ERR_NO_FILE    => 'No file was uploaded',
                    UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
                    UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
                    UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload.',
                );
                $error_message = 'File upload error: ' . ($phpFileUploadErrors[$upload_error] ?? 'Unknown error');
                error_log('VERIFY_SLIP_ERROR: ' . $error_message . ' - File details: ' . json_encode($_FILES['slipUpload']));
                echo json_encode(['success' => false, 'message' => 'ขออภัย เกิดข้อผิดพลาดในการอัปโหลดไฟล์ของคุณ.', 'details' => $error_message]);
                exit();
            }

            $target_dir = __DIR__ . "/../uploads/slips/"; // ใช้ __DIR__ เพื่อให้ได้ absolute path ที่ถูกต้อง
            
            if (!is_dir($target_dir)) {
                if (!mkdir($target_dir, 0777, true)) {
                    $error_message = 'Failed to create upload directory: ' . $target_dir;
                    error_log('VERIFY_SLIP_ERROR: ' . $error_message);
                    echo json_encode(['success' => false, 'message' => 'ขออภัย ไม่สามารถสร้างโฟลเดอร์อัปโหลดได้.', 'details' => $error_message]);
                    exit();
                }
            }

            $file_extension = pathinfo($_FILES["slipUpload"]["name"], PATHINFO_EXTENSION);
            $new_file_name = uniqid('slip_') . '.' . $file_extension;
            $target_file = $target_dir . $new_file_name;
            
            // ตรวจสอบประเภทไฟล์
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
            if (in_array(strtolower($file_extension), $allowed_types)) {
                error_log('VERIFY_SLIP_DEBUG: Attempting to move file. Temp: ' . $_FILES["slipUpload"]["tmp_name"] . ' to Destination: ' . $target_file);
                if (move_uploaded_file($_FILES["slipUpload"]["tmp_name"], $target_file)) {
                    error_log('VERIFY_SLIP_DEBUG: File moved successfully.');
                    $slip_path = $new_file_name; // บันทึกเฉพาะชื่อไฟล์
                } else {
                    $last_error = error_get_last();
                    $error_message = 'Failed to move uploaded file. Destination: ' . $target_file . '. PHP Error: ' . ($last_error['message'] ?? 'None');
                    error_log('VERIFY_SLIP_ERROR: ' . $error_message . ' - Permissions check: ' . (is_writable(dirname($target_file)) ? 'Writable' : 'Not Writable')); // Add writability check
                    echo json_encode(['success' => false, 'message' => 'ขออภัย เกิดข้อผิดพลาดในการย้ายไฟล์ที่อัปโหลด.', 'details' => $error_message]);
                    exit();
                }
            } else {
                $error_message = 'Invalid file type: ' . $file_extension;
                error_log('VERIFY_SLIP_ERROR: ' . $error_message . ' - File details: ' . json_encode($_FILES['slipUpload']));
                echo json_encode(['success' => false, 'message' => 'ขออภัย อนุญาตเฉพาะไฟล์ JPG, JPEG, PNG, GIF และ PDF เท่านั้น.', 'details' => $error_message]);
                exit();
            }
        }

        // เตรียมและผูกพารามิเตอร์
        $stmt = $conn->prepare("INSERT INTO bookings (TableID, name, lastName, phone, batch, gradYear, payment_amount, transfer_date, transfer_time, slip_path, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param("sssssissss", $tableId, $name, $lastName, $phone, $batch, $gradYear, $paymentAmount, $transferDate, $transferTime, $slip_path);

        // รันคำสั่ง
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'จองโต๊ะและอัปโหลดสลิปสำเร็จ!']);
        } else {
            $error_message = 'Database insert error: ' . $stmt->error;
            error_log('VERIFY_SLIP_ERROR: ' . $error_message);
            echo json_encode(['success' => false, 'message' => 'Error: ' . $stmt->error, 'details' => $error_message]);
        }

        $stmt->close();
    }
} catch (Exception $e) {
    $error_message = 'Unhandled PHP Exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine();
    error_log('VERIFY_SLIP_FATAL_ERROR: ' . $error_message);
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดที่ไม่คาดคิด.', 'details' => $error_message]);
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
?>