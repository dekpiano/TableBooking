<?php
session_start();
include_once 'api/connectDB.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // First, get the slip_path to delete the file
    $stmt = $conn->prepare("SELECT slip_path FROM bookings WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $booking = $result->fetch_assoc();

    if ($booking && $booking['slip_path']) {
        $slip_file_path = __DIR__ . '/uploads/slips/' . $booking['slip_path'];
        if (file_exists($slip_file_path)) {
            unlink($slip_file_path); // Delete the actual file
        }
    }

    // Then, delete the booking record from the database
    $stmt = $conn->prepare("DELETE FROM bookings WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: admin.php");
        exit();
    } else {
        echo "Error deleting record: " . $stmt->error;
    }
} else {
    echo "Missing ID parameter.";
}

$conn->close();
?>