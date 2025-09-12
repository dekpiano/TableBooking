<?php
session_start();
include_once 'connectDB.php';

header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

if (!isset($_POST['booking_id']) || !isset($_FILES['slip'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters.']);
    exit();
}

$bookingId = $_POST['booking_id'];
$slipFile = $_FILES['slip'];

// Validate file
if ($slipFile['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'File upload error.']);
    exit();
}

$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $slipFile['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, $allowedTypes)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, GIF, and WEBP are allowed.']);
    exit();
}

// --- Transaction Start ---
$conn->begin_transaction();

try {
    // 1. Get the old slip path to delete the file
    $stmt = $conn->prepare("SELECT slip_path FROM bookings WHERE id = ?");
    $stmt->bind_param("i", $bookingId);
    $stmt->execute();
    $result = $stmt->get_result();
    $booking = $result->fetch_assoc();
    $oldSlipPath = $booking['slip_path'] ?? null;
    $stmt->close();

    // 2. Generate a new unique filename
    $fileExtension = pathinfo($slipFile['name'], PATHINFO_EXTENSION);
    $newFileName = 'slip_' . uniqid() . '.' . $fileExtension;
    $uploadDir = '../uploads/slips/';
    $newFilePath = $uploadDir . $newFileName;

    // 3. Move the uploaded file
    if (!move_uploaded_file($slipFile['tmp_name'], $newFilePath)) {
        throw new Exception('Failed to move uploaded file.');
    }

    // 4. Update the database with the new slip path
    $stmt = $conn->prepare("UPDATE bookings SET slip_path = ? WHERE id = ?");
    $stmt->bind_param("si", $newFileName, $bookingId);
    if (!$stmt->execute()) {
        throw new Exception('Database update failed.');
    }
    $stmt->close();

    // 5. If update was successful, delete the old file
    if ($oldSlipPath) {
        $oldFileFullPath = $uploadDir . $oldSlipPath;
        if (file_exists($oldFileFullPath)) {
            unlink($oldFileFullPath);
        }
    }

    // If everything is fine, commit the transaction
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Slip updated successfully.']);

} catch (Exception $e) {
    // If something went wrong, roll back
    $conn->rollback();
    // Also delete the newly uploaded file if it exists
    if (isset($newFilePath) && file_exists($newFilePath)) {
        unlink($newFilePath);
    }
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}

$conn->close();
?>