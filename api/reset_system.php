<?php
session_start();
header('Content-Type: application/json');
include_once 'connectDB.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'reset_luckydraw') {
        // Increment the event year instead of truncating the table
        $res_year = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key = 'current_event_year'");
        $current_year = ($res_year && $res_year->num_rows > 0) ? (int)$res_year->fetch_assoc()['setting_value'] : 2025;
        $next_year = $current_year + 1;

        $stmt = $conn->prepare("UPDATE system_settings SET setting_value = ? WHERE setting_key = 'current_event_year'");
        $stmt->bind_param("s", $next_year);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => "เปลี่ยนรอบจัดกิจกรรมเป็นปีที่ $next_year เรียบร้อยแล้ว (รายชื่อเดิมของปี $current_year จะไม่ถูกนำมาสุ่มอีก)"]);
        } else {
            echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $conn->error]);
        }
        $stmt->close();
    } else if ($action === 'reset_all') {
        // Just increment the lucky draw year and event year to preserve historical data
        $conn->begin_transaction();
        try {
            $res_year = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key = 'current_event_year'");
            $current_year = ($res_year && $res_year->num_rows > 0) ? (int)$res_year->fetch_assoc()['setting_value'] : 2025;
            $next_year = $current_year + 1;
            
            $conn->query("UPDATE system_settings SET setting_value = '$next_year' WHERE setting_key = 'current_event_year'");
            
            $conn->commit();
            echo json_encode(['success' => true, 'message' => "เข้าสู่รอบกิจกรรมปี $next_year เรียบร้อยแล้ว (โต๊ะจะถูกปล่อยว่างตามปีใหม่ และเก็บข้อมูลเก่าไว้)"]);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>
