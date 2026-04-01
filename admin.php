<?php
session_start();
include_once 'api/connectDB.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$admin_name = $_SESSION['admin_name'];

// Fetch all bookings to calculate stats and for the table
$sql = "SELECT b.*, a.admin_name AS approver_name
        FROM bookings b
        LEFT JOIN tb_admins a ON b.approved_by_admin_id = a.admin_id
        ORDER BY b.created_at DESC";
$result = $conn->query($sql);

// Store all results in an array
$bookings = $result->fetch_all(MYSQLI_ASSOC);

// Calculate dashboard statistics
$total_bookings = count($bookings);
$pending_count = 0;
$verified_count = 0;
$rejected_count = 0;
$total_income = 0;

foreach ($bookings as $booking) {
    switch ($booking['status']) {
        case 'pending':
            $pending_count++;
            break;
        case 'verified':
            $verified_count++;
            $total_income += $booking['payment_amount'];
            break;
        case 'rejected':
            $rejected_count++;
            break;
    }
}

// Handle session messages for SweetAlert2
$message = null;
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']); // Clear the message after displaying
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Table Bookings</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=K2D:wght@400;700&display=swap" rel="stylesheet">
    <!-- DataTables Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- SheetJS (xlsx.js) -->
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <style>
        body {
            font-family: 'K2D', sans-serif;
            background-color: #a9d6f5; /* Fallback */
            background-image: linear-gradient(to top right, #f8b4c8, #a9d6f5);
            color: #212529;
            min-height: 100vh;
        }
        .header-banner {
            padding: 1.5rem 0.5rem;
            margin-bottom: 2rem;
            background-color: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(8px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.8);
            text-align: center;
        }
        .header-banner h1 {
            font-weight: 700;
            color: #333;
            text-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .main-container {
            background-color: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
        }
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        .admin-header h2 {
            font-weight: 700;
            color: #333;
            margin-bottom: 0;
        }
        .table-responsive {
            margin-top: 20px;
        }
        .dashboard-card {
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .dashboard-card .card-title {
            font-size: 1.1rem;
            font-weight: bold;
        }
        .dashboard-card .card-text {
            font-size: 2.2rem;
            font-weight: 700;
        }
        .dashboard-card .icon {
            font-size: 3rem;
            opacity: 0.5;
        }
        /* Customizing DataTables for Bootstrap 5 */
        .dataTables_wrapper {
            font-size: 0.9rem;
        }
        .table {
            border-radius: 0.5rem;
            overflow: hidden; /* Ensures border-radius is applied to table corners */
        }
        .table thead {
            background-color: #0d6efd;
            color: white;
        }
        .btn-sm {
            font-size: 0.85rem;
            padding: 0.25rem 0.5rem;
        }
        .badge {
            font-size: 0.8em;
            padding: 0.4em 0.6em;
        }
        .action-buttons a {
            margin: 0 2px;
        }

        /* --- Table Grid Styles for Admin Modal --- */
        .table-grid-section {
            display: grid;
            grid-template-columns: repeat(9, 1fr) 0.5fr 1fr 0.5fr repeat(10, 1fr);
            gap: 4px;
            margin-bottom: 5px;
        }
        .walkway-column {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #777;
            font-weight: bold;
            writing-mode: vertical-rl;
            text-orientation: mixed;
            white-space: nowrap;
            font-size: 10px;
            background-color: rgba(0, 0, 0, 0.05);
            border-radius: 4px;
        }
        .walkway-row {
            padding: 4px 0;
            margin: 5px 0;
            background-color: rgba(0, 0, 0, 0.1);
            color: #555;
            font-weight: bold;
            border-radius: 4px;
            font-size: 12px;
            text-align: center;
            width: 100%;
        }
        .admin-table-item {
            aspect-ratio: 1/1;
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: white;
            border: 1px solid #ddd;
            font-size: 10px;
            cursor: default;
            position: relative;
            padding: 2px;
        }
        .admin-table-item.square {
            border-radius: 4px;
        }
        .admin-table-item span {
            font-weight: bold;
            margin-bottom: 2px;
        }
        .admin-table-item select {
            font-size: 9px;
            padding: 0 2px;
            width: 90%;
            height: 18px;
        }
        .admin-table-item.reserved {
            background-color: #ffebee;
            border-color: #ffcdd2;
        }
        .admin-table-item.blocked-v {
            background-color: #fff3e0;
            border-color: #ffe0b2;
        }
        .admin-table-item.blocked-h {
            background-color: #f5f5f5;
            border-color: #e0e0e0;
            opacity: 0.6;
        }
    </style>
</head>
<body>
    <header class="header-banner">
        <h1><i class="bi bi-gear-wide-connected"></i> แผงควบคุมผู้ดูแลระบบ</h1>
    </header>

    <div class="container-fluid">
        <div class="container main-container">
            <div class="admin-header">
                <h2><i class="bi bi-speedometer2"></i> สรุปข้อมูล (Dashboard)</h2>
                <div>
                    <span class="me-3"><i class="bi bi-person-circle"></i> สวัสดี, <?php echo htmlspecialchars($admin_name); ?></span>
                    <a href="admin_luckydraw.php" class="btn btn-info btn-sm text-white"><i class="bi bi-gift-fill text-white"></i> ระบบจับรางวัล</a>
                    <button id="toggleModeBtn" class="btn btn-outline-primary btn-sm"><i class="bi bi-toggle-on"></i> โหมดการจอง</button>
                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#manageTableStatusModal"><i class="bi bi-slash-circle text-white"></i> จัดการสถานะโต๊ะ</button>
                    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addBookingModal"><i class="bi bi-plus-circle-fill"></i> เพิ่มการจองใหม่</button>
                    <a href="logout.php" class="btn btn-danger btn-sm"><i class="bi bi-box-arrow-right"></i> ออกจากระบบ</a>
                </div>
            </div>

            <!-- Dashboard Cards -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6">
                    <div class="dashboard-card bg-primary text-center">
                        <div class="row">
                            <div class="col-8 text-start">
                                <div class="card-title">ยอดจองทั้งหมด</div>
                                <div class="card-text"><?php echo $total_bookings; ?></div>
                            </div>
                            <div class="col-4 d-flex align-items-center justify-content-end">
                                <i class="bi bi-journal-text icon"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="dashboard-card bg-warning text-dark text-center">
                         <div class="row">
                            <div class="col-8 text-start">
                                <div class="card-title">รอตรวจสอบ</div>
                                <div class="card-text"><?php echo $pending_count; ?></div>
                            </div>
                            <div class="col-4 d-flex align-items-center justify-content-end">
                                <i class="bi bi-hourglass-split icon"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="dashboard-card bg-success text-center">
                        <div class="row">
                            <div class="col-8 text-start">
                                <div class="card-title">อนุมัติแล้ว</div>
                                <div class="card-text"><?php echo $verified_count; ?></div>
                            </div>
                            <div class="col-4 d-flex align-items-center justify-content-end">
                                <i class="bi bi-check-circle-fill icon"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="dashboard-card bg-info text-center">
                        <div class="row">
                            <div class="col-8 text-start">
                                <div class="card-title">ยอดรวม (บาท)</div>
                                <div class="card-text"><?php echo number_format($total_income, 2); ?></div>
                            </div>
                            <div class="col-4 d-flex align-items-center justify-content-end">
                                <i class="bi bi-cash-stack icon"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="admin-header mt-5 d-flex justify-content-between align-items-center">
                <h2 class="m-0"><i class="bi bi-table"></i> การจองโต๊ะทั้งหมด</h2>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#exportExcelModal">
                    <i class="bi bi-file-earmark-excel-fill"></i> ส่งออกข้อมูลเป็น Excel
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle" id="bookingsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>แก้ไข</th>
                            <th>โต๊ะ</th>
                            <th>ชื่อ</th>
                            <th>นามสกุล</th>
                            <th>เบอร์โทร</th>
                            <th>จำนวนเงิน</th>
                            <th>สลิป</th>
                            <th>สถานะ</th>
                            <th>ผู้อนุมัติ</th>
                            <th>จัดการอนุมัติ</th>
                            <th>เปลี่ยนโต๊ะ</th>
                            <th>ลบ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($total_bookings > 0) {
                            foreach($bookings as $row) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row["id"]) . "</td>";
                                echo "<td>";
                                echo "<button class=\"btn btn-sm btn-outline-warning\" onclick=\"editBookingInfo(" . $row['id'] . ", '" . htmlspecialchars($row['name']) . "', '" . htmlspecialchars($row['lastName']) . "', '" . htmlspecialchars($row['phone']) . "')\">";
                                echo "<i class=\"bi bi-pencil-square\"></i> แก้ไข";
                                echo "</button>";
                                echo "</td>";
                                echo "<td>" . htmlspecialchars($row["TableID"]) . "</td>";
                                echo "<td>" . htmlspecialchars($row["name"]) . "</td>";
                                echo "<td>" . htmlspecialchars($row["lastName"]) . "</td>";
                                echo "<td>" . htmlspecialchars($row["phone"]) . "</td>";
                                echo "<td>" . htmlspecialchars($row["payment_amount"]) . "</td>";
                                echo "<td>";
                                if ($row["slip_path"]) {
                                    echo "<a href=\"#\" onclick=\"showSlip('uploads/slips/" . htmlspecialchars($row["slip_path"]) . "', " . $row['id'] . ")\" class=\"btn btn-info btn-sm\"><i class=\"bi bi-eye\"></i> ดู / แก้ไข</a>";
                                } else {
                                    echo "<a href=\"#\" onclick=\"updateSlip(" . $row['id'] . ")\" class=\"btn btn-primary btn-sm\"><i class=\"bi bi-upload\"></i> อัปโหลด</a>";
                                }
                                echo "</td>";
                                
                                // Status badge
                                $status_class = 'bg-secondary';
                                if ($row["status"] == 'pending') $status_class = 'bg-warning text-dark';
                                if ($row["status"] == 'verified') $status_class = 'bg-success';
                                if ($row["status"] == 'rejected') $status_class = 'bg-danger';
                                echo "<td><span class=\"badge " . $status_class . "\">" . htmlspecialchars($row["status"]) . "</span></td>";

                                echo "<td>" . htmlspecialchars($row["approver_name"] ?? '-') . "</td>";
                                echo "<td class=\"action-buttons\">";
                                if ($row["status"] == 'pending') {
                                    echo "<a href=\"#\" onclick=\"confirmUpdateStatus(" . htmlspecialchars($row["id"]) . ", 'verified')\" class=\"btn btn-success btn-sm\">ยืนยันการอนุมัติ</a>";
                                } else if ($row["status"] == 'verified') {
                                    echo "<a href=\"#\" onclick=\"confirmUpdateStatus(" . htmlspecialchars($row["id"]) . ", 'pending')\" class=\"btn btn-warning btn-sm\"><i class=\"bi bi-arrow-counterclockwise\"></i> ยกเลิก</a>";
                                } else {
                                    echo "-";
                                }
                                echo "</td>";
                                // Change table button
                                echo "<td class=\"action-buttons\">";
                                if ($row["status"] == 'verified') {
                                    echo "<a href=\"#\" onclick=\"openChangeTableModal(" . htmlspecialchars($row["id"]) . ", '" . htmlspecialchars($row["TableID"]) . "')\" class=\"btn btn-primary btn-sm\"><i class=\"bi bi-arrow-left-right\"></i> เปลี่ยนโต๊ะ</a>";
                                } else {
                                    echo "-";
                                }
                                echo "</td>";
                                echo "<td class=\"action-buttons\">";
                                echo "<a href=\"#\" onclick=\"confirmDelete(" . htmlspecialchars($row["id"]) . ")\" class=\"btn btn-danger btn-sm\"><i class=\"bi bi-trash-fill\"></i> ลบ</a>";
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo '<tr><td colspan="12" class="text-center">ไม่มีการจอง</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Booking Modal -->
    <div class="modal fade" id="addBookingModal" tabindex="-1" aria-labelledby="addBookingModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addBookingModalLabel">เพิ่มการจองใหม่</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addBookingForm" action="add_booking.php" method="POST">
                        <div class="mb-3">
                            <label for="TableID" class="form-label">หมายเลขโต๊ะ</label>
                            <select class="form-select" id="TableID" name="TableID" required>
                                <!-- Options will be populated by JavaScript -->
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="name" class="form-label">ชื่อ</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="lastName" class="form-label">นามสกุล</label>
                            <input type="text" class="form-control" id="lastName" name="lastName" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">เบอร์โทร</label>
                            <input type="tel" class="form-control" id="phone" name="phone" required>
                        </div>
                        <div class="mb-3">
                            <label for="payment_amount" class="form-label">จำนวนเงิน</label>
                            <input type="number" class="form-control" id="payment_amount" name="payment_amount" required>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                            <button type="submit" class="btn btn-primary">บันทึกการจอง</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Manage Table Status Modal -->
    <div class="modal fade" id="manageTableStatusModal" tabindex="-1" aria-labelledby="manageTableStatusModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title" id="manageTableStatusModalLabel"><i class="bi bi-grid-3x3-gap"></i> จัดการสถานะการเปิด/ปิดโต๊ะ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-3"><i class="bi bi-info-circle"></i> คุณสามารถตั้งค่า�    <!-- Export Excel Modal -->
    <div class="modal fade" id="exportExcelModal" tabindex="-1" aria-labelledby="exportExcelModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="exportExcelModalLabel"><i class="bi bi-file-earmark-excel"></i> เลือกข้อมูลที่ต้องการส่งออก (Excel)</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="exportForm">
                        <div class="mb-4">
                            <p class="fw-bold mb-2"><i class="bi bi-filter-circle"></i> กรองสถานะที่ต้องการ:</p>
                            <div class="d-flex gap-3 flex-wrap">
                                <div class="form-check">
                                    <input class="form-check-input status-filter" type="checkbox" value="verified" id="statusVerified" checked>
                                    <label class="form-check-label" for="statusVerified">ยืนยันแล้ว</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input status-filter" type="checkbox" value="pending" id="statusPending" checked>
                                    <label class="form-check-label" for="statusPending">รอตรวจสอบ</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input status-filter" type="checkbox" value="rejected" id="statusRejected">
                                    <label class="form-check-label" for="statusRejected">ปฏิเสธ</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <p class="fw-bold mb-2"><i class="bi bi-list-check"></i> เลือกคอลัมน์ที่ต้องการ:</p>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input col-selection" type="checkbox" value="TableID" id="colTable" checked>
                                        <label class="form-check-label" for="colTable">หมายเลขโต๊ะ</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input col-selection" type="checkbox" value="name" id="colName" checked>
                                        <label class="form-check-label" for="colName">ชื่อ</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input col-selection" type="checkbox" value="lastName" id="colLastName" checked>
                                        <label class="form-check-label" for="colLastName">นามสกุล</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input col-selection" type="checkbox" value="phone" id="colPhone" checked>
                                        <label class="form-check-label" for="colPhone">เบอร์โทรศัพท์</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input col-selection" type="checkbox" value="status" id="colStatus" checked>
                                        <label class="form-check-label" for="colStatus">สถานะการจอง</label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input col-selection" type="checkbox" value="batch" id="colBatch" checked>
                                        <label class="form-check-label" for="colBatch">รุ่น</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input col-selection" type="checkbox" value="gradYear" id="colGradYear" checked>
                                        <label class="form-check-label" for="colGradYear">ปีที่จบ</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input col-selection" type="checkbox" value="payment_amount" id="colAmount" checked>
                                        <label class="form-check-label" for="colAmount">ยอดเงินโอน</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input col-selection" type="checkbox" value="transfer_date" id="colDate" checked>
                                        <label class="form-check-label" for="colDate">วันที่/เวลาโอน</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input col-selection" type="checkbox" value="created_at" id="colCreated" checked>
                                        <label class="form-check-label" for="colCreated">วันที่ทำรายการ</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-info py-2">
                             <i class="bi bi-info-circle"></i> ระบบจะทำการเรียงลำดับตาม **หมายเลขโต๊ะ** ให้อัตโนมัติ
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="button" class="btn btn-success" onclick="executeExport()"><i class="bi bi-download"></i> ดาวน์โหลด Excel</button>
                </div>
            </div>
        </div>
    </div>
iv class="form-check">
                                        <input class="form-check-input col-selection" type="checkbox" value="payment_amount" id="colAmount" checked>
                                        <label class="form-check-label" for="colAmount">ยอดเงินโอน</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input col-selection" type="checkbox" value="transfer_date" id="colDate" checked>
                                        <label class="form-check-label" for="colDate">วันที่/เวลาโอน</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input col-selection" type="checkbox" value="created_at" id="colCreated" checked>
                                        <label class="form-check-label" for="colCreated">วันที่ทำรายการ</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-info py-2">
                             <i class="bi bi-info-circle"></i> ระบบจะทำการเรียงลำดับตาม **หมายเลขโต๊ะ** ให้อัตโนมัติ
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="button" class="btn btn-success" onclick="executeExport()"><i class="bi bi-download"></i> ดาวน์โหลด Excel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- JQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

    <script>
        const layoutConfig = [ { id: 'main-section', className: 'section-main', cols: 20, startCol: 'A', rows: 15, startRow: 1 } ];
        const API_GET_RESERVATIONS = 'api/get_reservations.php';

        $(document).ready(function() {
            $('#bookingsTable').DataTable({
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/1.11.5/i18n/th.json"
                },
                "order": [[ 0, "desc" ]],
                "responsive": true,
                "autoWidth": false
            });

            // Event listener for when the modal is about to be shown
            document.getElementById('addBookingModal').addEventListener('show.bs.modal', async function () {
                const selectTable = document.getElementById('TableID');
                const exportExcelModal = new bootstrap.Modal(document.getElementById('exportExcelModal'));

        // Load mode setting on init
                selectTable.innerHTML = '<option value="">กำลังโหลดโต๊ะที่ว่าง...</option>';
                
                const availableTables = await getAvailableTables();
                
                // Clear loading text
                selectTable.innerHTML = '';

                if (availableTables.length > 0) {
                    availableTables.forEach(table => {
                        const option = document.createElement('option');
                        option.value = table;
                        option.textContent = table;
                        selectTable.appendChild(option);
                    });
                } else {
                    selectTable.innerHTML = '<option value="">ไม่มีโต๊ะว่าง</option>';
                }
            });

            // Display SweetAlert2 messages if they exist
            <?php if ($message): ?>
                Swal.fire({
                    icon: '<?php echo $message['type']; ?>',
                    title: '<?php echo $message['text']; ?>',
                    showConfirmButton: false,
                    timer: 2500
                });
            <?php endif; ?>

            // Fetch and set initial mode
            fetchSystemSettings();

            // Toggle mode event listener
            $('#toggleModeBtn').on('click', function() {
                const currentMode = $(this).data('mode') || 'open';
                const nextMode = currentMode === 'open' ? 'closed' : 'open';
                updateSystemMode(nextMode);
            });
        });

        async function fetchSystemSettings() {
            try {
                const response = await fetch('api/get_system_settings.php');
                const result = await response.json();
                if (result.success && result.data.booking_mode) {
                    setModeUI(result.data.booking_mode);
                }
            } catch (error) {
                console.error("Error fetching system settings:", error);
            }
        }

        async function updateSystemMode(mode) {
            const modeText = mode === 'open' ? 'เปิดการจอง' : 'ปิดการจอง (แสดงข้อมูล)';
            const { isConfirmed } = await Swal.fire({
                title: 'ยืนยันการเปลี่ยนโหมดระบบ?',
                text: `คุณต้องการเปลี่ยนเป็น "${modeText}" ใช่หรือไม่?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'ตกลง',
                cancelButtonText: 'ยกเลิก'
            });

            if (isConfirmed) {
                try {
                    const formData = new FormData();
                    formData.append('booking_mode', mode);
                    const response = await fetch('api/update_system_settings.php', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();
                    if (result.success) {
                        setModeUI(mode);
                        Swal.fire('สำเร็จ!', `เปลี่ยนโหมดเป็น "${modeText}" เรียบร้อยแล้ว`, 'success');
                    } else {
                        throw new Error(result.message);
                    }
                } catch (error) {
                    Swal.fire('เกิดข้อผิดพลาด', error.message, 'error');
                }
            }
        }

        function setModeUI(mode) {
            const btn = $('#toggleModeBtn');
            btn.data('mode', mode);
            if (mode === 'open') {
                btn.html('<i class="bi bi-toggle-on"></i> โหมด: กำลังเปิดจอง');
                btn.removeClass('btn-outline-secondary').addClass('btn-outline-primary');
            } else {
                btn.html('<i class="bi bi-toggle-off"></i> โหมด: ปิดจอง (แสดงชื่อ)');
                btn.removeClass('btn-outline-primary').addClass('btn-outline-secondary');
            }
        }

        async function getAvailableTables(currentTableId = null) {
            try {
                const response = await fetch(API_GET_RESERVATIONS);
                if (!response.ok) throw new Error('Could not fetch reservations.');
                const result = await response.json();

                if (!result.success) throw new Error(result.message);

                const allReservations = Array.isArray(result.data) ? result.data : [];
                const reservedOrPendingTables = allReservations.map(r => r.TableID);

                let allPossibleTables = [];
                layoutConfig.forEach(section => {
                    for (let i = 0; i < section.rows; i++) {
                        for (let j = 0; j < section.cols; j++) {
                            const colChar = String.fromCharCode(section.startCol.charCodeAt(0) + j);
                            const tableName = `${colChar}${section.startRow + i}`;
                            allPossibleTables.push(tableName);
                        }
                    }
                });

                const availableTables = allPossibleTables.filter(table => 
                    !reservedOrPendingTables.includes(table) || table === currentTableId
                );

                return availableTables;

            } catch (error) {
                console.error("Error getting available tables:", error);
                Swal.fire('ข้อผิดพลาด', 'ไม่สามารถโหลดข้อมูลโต๊ะที่ว่างได้', 'error');
                return []; // Return empty array on error
            }
        }

        async function openChangeTableModal(bookingId, currentTableId) {
            const availableTables = await getAvailableTables(currentTableId);
            if (availableTables.length === 0) {
                Swal.fire('ขออภัย', 'ไม่มีโต๊ะว่างให้เปลี่ยนในขณะนี้', 'info');
                return;
            }

            const inputOptions = {};
            availableTables.forEach(table => {
                inputOptions[table] = table;
            });

            const { value: newTableId } = await Swal.fire({
                title: `เปลี่ยนโต๊ะสำหรับ ID: ${bookingId}`,
                text: `โต๊ะปัจจุบัน: ${currentTableId}`,
                input: 'select',
                inputOptions: inputOptions,
                inputValue: currentTableId,
                showCancelButton: true,
                confirmButtonText: 'ยืนยันการเปลี่ยน',
                cancelButtonText: 'ยกเลิก',
                inputValidator: (value) => {
                    if (!value) {
                        return 'กรุณาเลือกโต๊ะใหม่!'
                    }
                }
            });

            if (newTableId && newTableId !== currentTableId) {
                submitChangeTable(bookingId, newTableId);
            }
        }

        async function submitChangeTable(bookingId, newTableId) {
            try {
                const formData = new FormData();
                formData.append('booking_id', bookingId);
                formData.append('new_table_id', newTableId);

                const response = await fetch('api/change_table.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    await Swal.fire('สำเร็จ!', 'เปลี่ยนโต๊ะเรียบร้อยแล้ว', 'success');
                    location.reload();
                } else {
                    throw new Error(result.message || 'เกิดข้อผิดพลาดที่ไม่รู้จัก');
                }
            } catch (error) {
                Swal.fire('เกิดข้อผิดพลาด', error.message, 'error');
            }
        }

        async function updateSlip(bookingId) {
            const { value: file } = await Swal.fire({
                title: 'เลือกสลิปใหม่',
                input: 'file',
                inputAttributes: {
                    'accept': 'image/*',
                    'aria-label': 'อัปโหลดสลิปของคุณ'
                },
                showCancelButton: true,
                confirmButtonText: 'อัปโหลด',
                cancelButtonText: 'ยกเลิก',
                inputValidator: (value) => {
                    if (!value) {
                        return 'กรุณาเลือกไฟล์!'
                    }
                }
            });

            if (file) {
                const formData = new FormData();
                formData.append('booking_id', bookingId);
                formData.append('slip', file);

                try {
                    const response = await fetch('api/update_slip.php', {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();

                    if (result.success) {
                        await Swal.fire('สำเร็จ!', 'อัปเดตสลิปเรียบร้อยแล้ว', 'success');
                        location.reload();
                    } else {
                        throw new Error(result.message || 'เกิดข้อผิดพลาดในการอัปโหลด');
                    }
                } catch (error) {
                    Swal.fire('เกิดข้อผิดพลาด', error.message, 'error');
                }
            }
        }

        function showSlip(slipPath, bookingId) {
            Swal.fire({
                title: 'สลิปการโอนเงิน',
                imageUrl: slipPath,
                imageAlt: 'สลิปการโอนเงิน',
                position: 'center-end',
                imageWidth: 400,
                showCloseButton: true,
                showConfirmButton: true,
                confirmButtonText: '<i class="bi bi-pencil-square"></i> แก้ไขสลิป',
                confirmButtonAriaLabel: 'แก้ไขสลิป',
                showCancelButton: true,
                cancelButtonText: 'ปิด',
            }).then((result) => {
                if (result.isConfirmed) {
                    updateSlip(bookingId);
                }
            });
        }

        function confirmUpdateStatus(id, status) {
            let title = status === 'verified' ? 'ยืนยันการอนุมัติ?' : 'ยกเลิกการอนุมัติ?';
            let text = status === 'verified' ? "คุณต้องการอนุมัติการจองนี้ใช่หรือไม่?" : "คุณต้องการเปลี่ยนสถานะกลับเป็น 'รอตรวจสอบ' ใช่หรือไม่?";
            let icon = status === 'verified' ? 'question' : 'warning';
            let confirmBtnText = status === 'verified' ? 'ใช่, อนุมัติเลย' : 'ใช่, ยกเลิกการอนุมัติ';

            Swal.fire({
                title: title,
                text: text,
                icon: icon,
                showCancelButton: true,
                confirmButtonColor: status === 'verified' ? '#198754' : '#ffc107',
                cancelButtonColor: '#3085d6',
                confirmButtonText: confirmBtnText,
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'update_status.php?id=' + id + '&status=' + status;
                }
            })
        }

        function confirmDelete(id) {
            Swal.fire({
                title: 'คุณแน่ใจหรือไม่?',
                text: "คุณต้องการลบข้อมูลนี้ใช่หรือไม่!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'ใช่, ลบเลย!',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'delete_booking.php?id=' + id;
                }
            })
        }

        async function editBookingInfo(id, name, lastName, phone) {
            const { value: formValues } = await Swal.fire({
                title: 'แก้ไขข้อมูลผู้จอง',
                html:
                    `<div class="mb-3 text-start">
                        <label class="form-label">ชื่อ</label>
                        <input id="swal-input1" class="form-control" value="${name}">
                    </div>` +
                    `<div class="mb-3 text-start">
                        <label class="form-label">นามสกุล</label>
                        <input id="swal-input2" class="form-control" value="${lastName}">
                    </div>` +
                    `<div class="mb-3 text-start">
                        <label class="form-label">เบอร์โทร</label>
                        <input id="swal-input3" class="form-control" value="${phone}">
                    </div>`,
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: 'บันทึก',
                cancelButtonText: 'ยกเลิก',
                preConfirm: () => {
                    return [
                        document.getElementById('swal-input1').value,
                        document.getElementById('swal-input2').value,
                        document.getElementById('swal-input3').value
                    ]
                }
            });

            if (formValues) {
                const [newName, newLastName, newPhone] = formValues;
                
                if (!newName || !newLastName || !newPhone) {
                    Swal.fire('ข้อผิดพลาด', 'กรุณากรอกข้อมูลให้ครบทุกช่อง', 'error');
                    return;
                }

                try {
                    const formData = new FormData();
                    formData.append('booking_id', id);
                    formData.append('name', newName);
                    formData.append('lastName', newLastName);
                    formData.append('phone', newPhone);

                    const response = await fetch('api/update_booking_info.php', {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();

                    if (result.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'สำเร็จ!',
                            text: result.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        throw new Error(result.message || 'เกิดข้อผิดพลาดในการอัปเดต');
                    }
                } catch (error) {
                    Swal.fire('เกิดข้อผิดพลาด', error.message, 'error');
                }
            }
        }

        // --- MANAGE TABLE STATUS LOGIC ---
        const manageTableStatusModal = document.getElementById('manageTableStatusModal');
        const tableStatusGrid = document.getElementById('tableStatusGrid');

        manageTableStatusModal.addEventListener('show.bs.modal', async function () {
            await loadTableStatuses();
        });

        async function loadTableStatuses() {
            try {
                tableStatusGrid.innerHTML = `
                    <div class="text-center w-100 py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">กำลังโหลด...</span>
                        </div>
                    </div>`;

                const [reservationsRes, tableStatusRes] = await Promise.all([
                    fetch(API_GET_RESERVATIONS),
                    fetch('api/get_table_status.php')
                ]);

                const reservations = await reservationsRes.json();
                const tableStatuses = await tableStatusRes.json();

                const reservedTables = (reservations.success && Array.isArray(reservations.data)) 
                    ? reservations.data.map(r => r.TableID) 
                    : [];
                
                const statusMap = {};
                if (tableStatuses.success && Array.isArray(tableStatuses.data)) {
                    tableStatuses.data.forEach(item => {
                        statusMap[item.table_id] = item.status;
                    });
                }

                tableStatusGrid.innerHTML = '';
                
                const walkwayAfterRow1 = 5;
                const walkwayAfterRow2 = 10;
                const walkwayAfterCol_IJ = 9;  // After 'I'
                
                // Helper to create grid section
                const createGridSection = (startRowIndex, endRowIndex, hasIJWalkway) => {
                    const grid = document.createElement('div');
                    grid.className = 'table-grid-section';
                    if (!hasIJWalkway) {
                        grid.style.gridTemplateColumns = 'repeat(9, 1fr) 1fr 0.5fr repeat(10, 1fr)';
                    }

                    const middleRowInSection = startRowIndex + Math.floor((endRowIndex - startRowIndex) / 2);

                    for (let i = startRowIndex; i < endRowIndex; i++) {
                        const rowNum = 1 + i;
                        
                        // Columns A-I
                        for (let j = 0; j < walkwayAfterCol_IJ; j++) {
                            const colChar = String.fromCharCode('A'.charCodeAt(0) + j);
                            grid.appendChild(createTableAdminCell(colChar, rowNum, statusMap, reservedTables));
                        }

                        // I-J Walkway
                        if (hasIJWalkway) {
                            const w = document.createElement('div');
                            w.className = 'walkway-column';
                            if (i === middleRowInSection) w.textContent = 'ทางเดิน';
                            grid.appendChild(w);
                        }

                        // Column J
                        const colCharJ = String.fromCharCode('A'.charCodeAt(0) + walkwayAfterCol_IJ);
                        grid.appendChild(createTableAdminCell(colCharJ, rowNum, statusMap, reservedTables));

                        // J-K Walkway
                        const wJK = document.createElement('div');
                        wJK.className = 'walkway-column';
                        if (i === middleRowInSection) wJK.textContent = 'ทางเดิน';
                        grid.appendChild(wJK);

                        // Columns K-T
                        for (let j = 11; j <= 20; j++) {
                            const colChar = String.fromCharCode('A'.charCodeAt(0) + j - 1);
                            grid.appendChild(createTableAdminCell(colChar, rowNum, statusMap, reservedTables));
                        }
                    }
                    return grid;
                };

                const createTableAdminCell = (colChar, rowNum, statusMap, reservedTables) => {
                    const tableId = `${colChar}${rowNum}`;
                    const currentStatus = statusMap[tableId] || 'available';
                    const isReserved = reservedTables.includes(tableId);
                    
                    const div = document.createElement('div');
                    div.className = 'admin-table-item';
                    if (isReserved) div.classList.add('reserved');
                    if (currentStatus === 'blocked_visible') div.classList.add('blocked-v');
                    if (currentStatus === 'blocked_hidden') div.classList.add('blocked-h');
                    
                    // Square tables check (K1-2, J1-10)
                    if ((colChar === 'K' && (rowNum >= 1 && rowNum <= 2)) ||
                        (colChar === 'J' && (rowNum >= 1 && rowNum <= 10))) {
                        div.classList.add('square');
                    }

                    let statusBadge = '';
                    if (isReserved) {
                        statusBadge = '<span class="text-danger fw-bold" style="font-size:8px;">จองแล้ว</span>';
                    } else {
                        statusBadge = `
                            <select class="form-select status-select" data-table-id="${tableId}" onchange="updateTableStatus('${tableId}', this.value)">
                                <option value="available" ${currentStatus === 'available' ? 'selected' : ''}>🟢 เปิด</option>
                                <option value="blocked_visible" ${currentStatus === 'blocked_visible' ? 'selected' : ''}>🟡 ปิด</option>
                                <option value="blocked_hidden" ${currentStatus === 'blocked_hidden' ? 'selected' : ''}>🔴 ซ่อน</option>
                            </select>
                        `;
                    }

                    div.innerHTML = `<span>${tableId}</span>${statusBadge}`;
                    return div;
                }

                const createWalkwayRow = (text) => {
                    const row = document.createElement('div');
                    row.className = 'walkway-row';
                    row.textContent = text;
                    return row;
                };

                // Assemble sections
                tableStatusGrid.appendChild(createGridSection(0, walkwayAfterRow1, true));
                tableStatusGrid.appendChild(createWalkwayRow('----------- ทางเดินกลาง -----------'));
                tableStatusGrid.appendChild(createGridSection(walkwayAfterRow1, walkwayAfterRow2, true));
                tableStatusGrid.appendChild(createWalkwayRow('----------- ทางเดินหลัง -----------'));
                tableStatusGrid.appendChild(createGridSection(walkwayAfterRow2, 15, false));

            } catch (error) {
                console.error("Error loading table statuses:", error);
                tableStatusGrid.innerHTML = `<div class="alert alert-danger w-100">เกิดข้อผิดพลาดในการโหลดข้อมูล</div>`;
            }
        }

        async function updateTableStatus(tableId, newStatus) {
            try {
                const formData = new FormData();
                formData.append('table_id', tableId);
                formData.append('status', newStatus);

                const response = await fetch('api/update_table_status.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (!result.success) {
                    throw new Error(result.message || 'เกิดข้อผิดพลาดในการอัปเดต');
                }
                
                // Optional: Show a small toast notification instead of a full Swal
                console.log(`Updated table ${tableId} to ${newStatus}`);
                
            } catch (error) {
                Swal.fire('เกิดข้อผิดพลาด', error.message, 'error');
                // Reload the modal content to revert changes on failure
                loadTableStatuses();
            }
        }

        // --- EXPORT EXCEL LOGIC ---
        const allBookings = <?php echo json_encode($bookings); ?>;
        
        function executeExport() {
            // 1. Get Selected Statuses
            const allowedStatuses = [];
            document.querySelectorAll('.status-filter:checked').forEach(cb => {
                allowedStatuses.push(cb.value);
            });

            // 2. Get Selected Columns
            const selectedCols = [];
            const colLabels = {
                'TableID': 'หมายเลขโต๊ะ',
                'name': 'ชื่อ',
                'lastName': 'นามสกุล',
                'phone': 'เบอร์โทรศัพท์',
                'batch': 'รุ่น',
                'gradYear': 'ปีที่จบ',
                'status': 'สถานะ',
                'payment_amount': 'ยอดเงิน',
                'transfer_date': 'วันที่โอน',
                'created_at': 'วันที่จอง'
            };

            document.querySelectorAll('.col-selection:checked').forEach(cb => {
                selectedCols.push(cb.value);
            });

            if (selectedCols.length === 0) {
                Swal.fire('คำเตือน', 'กรุณาเลือกอย่างน้อย 1 คอลัมน์', 'warning');
                return;
            }

            if (allowedStatuses.length === 0) {
                Swal.fire('คำเตือน', 'กรุณาเลือกสถานะความต้องการ', 'warning');
                return;
            }

            // 3. Filter and Sort Data
            const filteredData = allBookings.filter(item => allowedStatuses.includes(item.status));
            const sortedData = [...filteredData].sort((a, b) => {
                return a.TableID.localeCompare(b.TableID, undefined, {numeric: true, sensitivity: 'base'});
            });

            // 4. Construct EXACT row objects for Excel
            const excelData = sortedData.map(item => {
                const cleanRow = {};
                selectedCols.forEach(colKey => {
                    let val = item[colKey];
                    // Translation logic
                    if (colKey === 'status') {
                        val = val === 'verified' ? 'ยืนยันแล้ว' : (val === 'pending' ? 'รอตรวจสอบ' : (val === 'rejected' ? 'ปฏิเสธ' : val));
                    }
                    if (colKey === 'transfer_date') {
                        val = (item.transfer_date || '') + ' ' + (item.transfer_time || '');
                    }
                    
                    const label = colLabels[colKey] || colKey;
                    cleanRow[label] = val;
                });
                return cleanRow;
            });

            // 5. Generate and Download
            const worksheet = XLSX.utils.json_to_sheet(excelData);
            const workbook = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(workbook, worksheet, "Sheet1");

            const dateStr = new Date().toISOString().slice(0, 10).replace(/-/g, "");
            XLSX.writeFile(workbook, `bookings_report_${dateStr}.xlsx`);
            
            // Close
            const modalEl = document.getElementById('exportExcelModal');
            const modalInstance = bootstrap.Modal.getInstance(modalEl);
            if (modalInstance) modalInstance.hide();
            
            Swal.fire({
                icon: 'success',
                title: 'สำเร็จ',
                text: `ส่งออก ${excelData.length} รายการ เรียบร้อยแล้ว`,
                timer: 1500,
                showConfirmButton: false
            });
        }
    </script>
</body>
</html>