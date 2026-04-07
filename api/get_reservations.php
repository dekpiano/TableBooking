<?php
header('Content-Type: application/json');

include_once 'connectDB.php';

// Fetch current event year
$res_year = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key = 'current_event_year'");
$current_year = ($res_year && $res_year->num_rows > 0) ? (int)$res_year->fetch_assoc()['setting_value'] : 2025;

$sql = "SELECT TableID, name, lastName, phone, batch, gradYear, payment_amount, transfer_date, transfer_time, status FROM bookings WHERE (status = 'verified' OR status = 'pending') AND event_year = $current_year";
$result = $conn->query($sql);

$reservations = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $reservations[] = $row;
    }
}

echo json_encode(['success' => true, 'data' => $reservations]);

$conn->close();
?>