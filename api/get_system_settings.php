<?php
include_once 'connectDB.php';

$sql = "SELECT setting_key, setting_value FROM system_settings";
$result = $conn->query($sql);

$settings = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $settings[$row["setting_key"]] = $row["setting_value"];
    }
}

// Default value if Not exists (e.g. table not created yet)
if (!isset($settings['booking_mode'])) {
    $settings['booking_mode'] = 'open';
}

header('Content-Type: application/json');
echo json_encode(['success' => true, 'data' => $settings]);

$conn->close();
?>
