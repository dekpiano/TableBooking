<?php
session_start();
include_once 'connectDB.php';

header('Content-Type: application/json');

// Function to send JSON response
function json_response($success, $message, $data = null) {
    $response = ['success' => $success, 'message' => $message];
    if ($data !== null) {
        $response['data'] = $data;
    }
    echo json_encode($response);
    exit();
}

// 1. Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    json_response(false, 'Unauthorized access.');
}

// 2. Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Invalid request method.');
}

// 3. Get and validate input
$booking_id = $_POST['booking_id'] ?? null;
$new_table_id = $_POST['new_table_id'] ?? null;

if (empty($booking_id) || empty($new_table_id)) {
    json_response(false, 'Missing booking ID or new table ID.');
}

// 4. CRITICAL VALIDATION: Check if the new table is available
// A table is unavailable if it exists in a booking that is 'pending' or 'verified'
$check_sql = "SELECT id FROM bookings WHERE TableID = ? AND (status = 'pending' OR status = 'verified')";
$check_stmt = $conn->prepare($check_sql);
if (!$check_stmt) {
    json_response(false, 'Database error (prepare check): ' . $conn->error);
}

$check_stmt->bind_param("s", $new_table_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    // The table is already taken
    json_response(false, 'โต๊ะที่เลือกไม่ว่างแล้ว กรุณาเลือกโต๊ะอื่น');
}
$check_stmt->close();

// 5. Update the booking with the new table ID
$update_sql = "UPDATE bookings SET TableID = ? WHERE id = ?";
$update_stmt = $conn->prepare($update_sql);
if (!$update_stmt) {
    json_response(false, 'Database error (prepare update): ' . $conn->error);
}

$update_stmt->bind_param("si", $new_table_id, $booking_id);

if ($update_stmt->execute()) {
    // Success
    json_response(true, 'เปลี่ยนโต๊ะเรียบร้อยแล้ว');
} else {
    // Failure
    json_response(false, 'ไม่สามารถอัปเดตข้อมูลได้: ' . $update_stmt->error);
}

$update_stmt->close();
$conn->close();
?>
