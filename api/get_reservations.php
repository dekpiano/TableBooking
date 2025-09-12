<?php
header('Content-Type: application/json');

include_once 'connectDB.php';

$sql = "SELECT TableID, name, lastName, phone, batch, gradYear, payment_amount, transfer_date, transfer_time, status FROM bookings WHERE status = 'verified' OR status = 'pending'";
$result = $conn->query($sql);

$reservations = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $reservations[] = $row;
    }
}

echo json_encode(['success' => true, 'data' => $reservations]);

$conn->close();
?>