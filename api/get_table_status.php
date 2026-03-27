<?php
header('Content-Type: application/json');

include_once 'connectDB.php';

$sql = "SELECT table_id, status FROM tb_tables_config";
$result = $conn->query($sql);

$tableStatuses = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $tableStatuses[] = $row;
    }
}

echo json_encode(['success' => true, 'data' => $tableStatuses]);

$conn->close();
?>
