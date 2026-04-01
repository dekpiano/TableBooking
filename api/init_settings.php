<?php
include_once 'connectDB.php';

$sql = "CREATE TABLE IF NOT EXISTS system_settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

if ($conn->query($sql) === TRUE) {
    // Insert default booking_mode if not exists
    $check = $conn->query("SELECT * FROM system_settings WHERE setting_key = 'booking_mode'");
    if ($check->num_rows == 0) {
        $conn->query("INSERT INTO system_settings (setting_key, setting_value) VALUES ('booking_mode', 'open')");
    }
    echo "Table system_settings created/verified successfully";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?>
