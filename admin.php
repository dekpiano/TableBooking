<?php
session_start();
include_once 'api/connectDB.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$admin_name = $_SESSION['admin_name'];

$sql = "SELECT b.*, a.admin_name AS approver_name
        FROM bookings b
        LEFT JOIN tb_admins a ON b.approved_by_admin_id = a.admin_id
        ORDER BY b.created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Table Bookings</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <style>
        body { padding: 20px; }
        .table-responsive { margin-top: 20px; }
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .admin-header h2 { margin-bottom: 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="admin-header">
            <h2 class="mb-4">การจองโต๊ะทั้งหมด</h2>
            <div>
                <span class="mr-3">สวัสดี, <?php echo htmlspecialchars($admin_name); ?></span>
                <a href="logout.php" class="btn btn-danger btn-sm">ออกจากระบบ</a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="bookingsTable">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Table ID</th>
                        <th>ชื่อ</th>
                        <th>นามสกุล</th>
                        <th>เบอร์โทร</th>
                        <th>จำนวนเงิน</th>
                        <th>สลิปการโอนเงิน</th>
                        <th>วันที่โอน</th>
                        <th>เวลาที่โอน</th>
                        <th>สถานะ</th>
                        <th>ผู้อนุมัติ</th>
                        <th>วันที่สร้าง</th>
                        <th>#</th>
                        <th>ลบ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row["id"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["TableID"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["name"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["lastName"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["phone"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["payment_amount"]) . "</td>";
                            echo "<td>";
                            if ($row["slip_path"]) {
                                // Assuming slip_path stores just the filename, and actual path is uploads/slips/filename
                                echo "<a href=\"uploads/slips/" . htmlspecialchars($row["slip_path"]) . "\" target=\"_blank\" class=\"btn btn-info btn-sm\">ดูสลิป</a>";
                            } else {
                                echo "ไม่มีสลิป";
                            }
                            echo "</td>";
                            echo "<td>" . htmlspecialchars($row["transfer_date"] ?? '-') . "</td>";
                            echo "<td>" . htmlspecialchars($row["transfer_time"] ?? '-') . "</td>";
                            echo "<td>" . htmlspecialchars($row["status"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["approver_name"] ?? '-') . "</td>"; // Display approver's name
                            echo "<td>" . htmlspecialchars($row["created_at"]) . "</td>"; // Display created_at
                            echo "<td>";
                            if ($row["status"] == 'pending') {
                                echo "<a href=\"update_status.php?id=" . htmlspecialchars($row["id"]) . "&status=verified\" class=\"btn btn-success btn-sm mr-1\">อนุมัติ</a>";
                                echo "<a href=\"update_status.php?id=" . htmlspecialchars($row["id"]) . "&status=rejected\" class=\"btn btn-danger btn-sm\">ปฏิเสธ</a>";
                            } else if ($row["status"] == 'verified') {
                                echo "<a href=\"update_status.php?id=" . htmlspecialchars($row["id"]) . "&status=pending\" class=\"btn btn-warning btn-sm\">ยกเลิกอนุมัติ</a>";
                            } else { // rejected
                                echo "-";
                            }
                            echo "</td>";
                            echo "<td>"; // New column for delete button
                            echo "<a href=\"delete_booking.php?id=" . htmlspecialchars($row["id"]) . "\" class=\"btn btn-danger btn-sm\" onclick=\"return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบข้อมูลนี้?');\">ลบ</a>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo '<tr><td colspan="14">ไม่มีการจอง</td></tr>'; // colspan increased by 1
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>

<!-- Slip Modal -->
<div class="modal fade" id="slipModal" tabindex="-1" aria-labelledby="slipModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="slipModalLabel">สลิปการโอนเงิน</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <img id="modalSlipImage" src="" class="img-fluid" alt="สลิปการโอนเงิน">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        $('#bookingsTable').DataTable();
    });

    var slipModal = document.getElementById('slipModal');
    slipModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget; // Button that triggered the modal
        var slipPath = button.getAttribute('data-slip-path'); // Extract info from data-slip-path attribute
        var modalSlipImage = slipModal.querySelector('#modalSlipImage');
        modalSlipImage.src = slipPath;
    });
</script>