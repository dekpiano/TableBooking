<?php
include_once 'connectDB.php';

// 1. Add event_year to tb_evaluations
$sql_add_col = "ALTER TABLE tb_evaluations ADD COLUMN IF NOT EXISTS event_year INT DEFAULT 2025 AFTER lucky_code";
if ($conn->query($sql_add_col)) {
    echo "Column event_year added/verified in tb_evaluations\n";
} else {
    echo "Error adding column: " . $conn->error . "\n";
}

// 2. Add current_event_year to system_settings
$check_setting = $conn->query("SELECT * FROM system_settings WHERE setting_key = 'current_event_year'");
if ($check_setting->num_rows == 0) {
    if ($conn->query("INSERT INTO system_settings (setting_key, setting_value) VALUES ('current_event_year', '2025')")) {
        echo "Default current_event_year set to 2025\n";
    }
}

// 3. Ensure booking_mode exists (from previous turn)
$check_mode = $conn->query("SELECT * FROM system_settings WHERE setting_key = 'booking_mode'");
if ($check_mode->num_rows == 0) {
    $conn->query("INSERT INTO system_settings (setting_key, setting_value) VALUES ('booking_mode', 'open')");
    echo "Default booking_mode set to open\n";
}

$conn->close();
?>
