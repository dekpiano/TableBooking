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

            <div class="admin-header mt-5">
                <h2><i class="bi bi-table"></i> การจองโต๊ะทั้งหมด</h2>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle" id="bookingsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
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
                    "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/th.json"
                },
                "order": [[ 0, "desc" ]],
                "responsive": true,
                "autoWidth": false
            });

            // Event listener for when the modal is about to be shown
            document.getElementById('addBookingModal').addEventListener('show.bs.modal', async function () {
                const selectTable = document.getElementById('TableID');
                // Clear existing options
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
        });

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

        function confirmUpdateStatus(id, status) {
            let statusText = '';
            let confirmButtonText = '';
            let titleText = 'คุณแน่ใจหรือไม่?';

            if (status === 'verified') {
                statusText = 'คุณต้องการอนุมัติรายการนี้ใช่หรือไม่?';
                confirmButtonText = 'ใช่, อนุมัติเลย!';
            } else if (status === 'pending') {
                statusText = 'คุณต้องการยกเลิกการอนุมัติรายการนี้ใช่หรือไม่?';
                confirmButtonText = 'ใช่, ยกเลิก!';
            }

            Swal.fire({
                title: titleText,
                text: statusText,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: confirmButtonText,
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `update_status.php?id=${id}&status=${status}`;
                }
            })
        }
    </script>
</body>
</html>