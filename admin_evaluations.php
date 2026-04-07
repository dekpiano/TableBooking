<?php
session_start();
include_once 'api/connectDB.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$admin_name = $_SESSION['admin_name'];

// Get current event year from settings
$res_year = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key = 'current_event_year'");
$event_year = ($res_year && $res_year->num_rows > 0) ? (int)$res_year->fetch_assoc()['setting_value'] : 2025;

// Fetch unique years from evaluations to filter
$years_res = $conn->query("SELECT DISTINCT event_year FROM tb_evaluations ORDER BY event_year DESC");
$years = [];
while($y = $years_res->fetch_assoc()) $years[] = $y['event_year'];

// Filter by year if requested
$filter_year = isset($_GET['year']) ? (int)$_GET['year'] : $event_year;

// --- Statistics Calculation ---
$stats = [];
$total_evals = 0;

// All evaluation fields
$q_fields = [
    'q1_1', 'q1_2', 'q1_3', 'q1_4', 'q1_5',
    'q2_1', 'q2_2', 'q2_3', 'q2_4',
    'q3_1', 'q3_2', 'q3_3', 'q3_4',
    'q4_1', 'q4_2', 'q4_3', 'q4_4', 'q4_5',
    'q5_1', 'q5_2', 'q5_3', 'q5_4',
    'q6_1', 'q6_2', 'q6_3'
];

$sql_avg = "SELECT COUNT(*) as total_count";
foreach($q_fields as $f) {
    $sql_avg .= ", AVG($f) as avg_$f";
}
$sql_avg .= " FROM tb_evaluations WHERE event_year = $filter_year";
$avg_res = $conn->query($sql_avg);
if ($avg_res) {
    $stats = $avg_res->fetch_assoc();
    $total_evals = $stats['total_count'];
}

// Map IDs to Question Texts for better display
$q_texts = [
    "q1_1" => "การประชาสัมพันธ์งาน", "q1_2" => "ความสะดวกในการซื้อบัตรและการรับบัตร", "q1_3" => "ความเหมาะสมของวัน เวลาจัดงาน", "q1_4" => "การต้อนรับของฝ่ายจัดงาน", "q1_5" => "การให้ข้อมูลและการประสานงาน",
    "q2_1" => "ความเหมาะสมของสถานที่", "q2_2" => "การตกแต่งสถานที่และบรรยากาศ", "q2_3" => "ความสะอาดและระเบียบเรียบร้อย", "q2_4" => "ระบบแสง เสียง และเวที",
    "q3_1" => "ความน่าสนใจของกิจกรรม", "q3_2" => "ความหลากหลายของกิจกรรม", "q3_3" => "การเปิดโอกาสให้มีส่วนร่วม", "q3_4" => "การจัดลำดับขั้นตอนของกิจกรรม",
    "q4_1" => "การบริการอาหารและเครื่องดื่ม", "q4_2" => "รสชาติของอาหาร", "q4_3" => "คุณค่าและปริมาณของอาหาร", "q4_4" => "การบริการจอดรถและการจราจร", "q4_5" => "ความสะอาดของห้องน้ำ",
    "q5_1" => "ความหลากหลายของสินค้า", "q5_2" => "ความเหมาะสมของราคา", "q5_3" => "คุณภาพของสินค้า", "q5_4" => "ความสะดวกในการเลือกซื้อ",
    "q6_1" => "ความประทับใจโดยรวม", "q6_2" => "ความคุ้มค่าในการเข้าร่วมงาน", "q6_3" => "ความต้องการเข้าร่วมอีกในอนาคต"
];

// Sections for table grouping
$sections = [
    "1. ด้านการจัดงานและพิธีการ" => ["q1_1", "q1_2", "q1_3", "q1_4", "q1_5"],
    "2. ด้านสถานที่และบรรยากาศ" => ["q2_1", "q2_2", "q2_3", "q2_4"],
    "3. ด้านกิจกรรมภายในงาน" => ["q3_1", "q3_2", "q3_3", "q3_4"],
    "4. ด้านอาหารและการบริการ" => ["q4_1", "q4_2", "q4_3", "q4_4", "q4_5"],
    "5. ด้านการจำหน่ายของที่ระลึก" => ["q5_1", "q5_2", "q5_3", "q5_4"],
    "6. ด้านความประทับใจโดยรวม" => ["q6_1", "q6_2", "q6_3"]
];

// Fetch all evaluations for the chosen year
$all_evals_sql = "SELECT id, full_name, phone, lucky_code, user_status, occupation, created_at, comment_impress, comment_improve, comment_more FROM tb_evaluations WHERE event_year = $filter_year ORDER BY created_at DESC";
$all_evals_res = $conn->query($all_evals_sql);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบจัดการผลการประเมิน - Admin Panel</title>
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
    <!-- MultiSelect/Select2 styles (Simplified using Bootstrap) -->
    <style>
        body {
            font-family: 'K2D', sans-serif;
            background-color: #f0f2f5;
            color: #333;
            min-height: 100vh;
        }
        .header-banner {
            background: linear-gradient(135deg, #0d6efd 0%, #004085 100%);
            color: white;
            padding: 2rem 1rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .main-container {
            background-color: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 8px 32px 0 rgba(0,0,0,0.05);
            margin-bottom: 3rem;
        }
        .stat-card {
            border-radius: 12px;
            padding: 1.5rem;
            color: white;
            height: 100%;
        }
        .bg-gradient-primary { background: linear-gradient(45deg, #007bff, #00c6ff); }
        .bg-gradient-success { background: linear-gradient(45deg, #28a745, #11998e); }
        .bg-gradient-orange { background: linear-gradient(45deg, #f093fb, #f5576c); }
        
        .stat-card h3 { font-weight: 700; font-size: 2.5rem; margin-bottom: 0; }
        .stat-card p { font-size: 0.9rem; opacity: 0.9; margin-bottom: 0; }
        
        .section-header {
            border-left: 5px solid #007bff;
            padding-left: 15px;
            margin: 2rem 0 1.5rem 0;
            color: #333;
            font-weight: 700;
        }
        .table-responsive {
            margin-top: 1rem;
        }
        .star-rating-badge {
            font-weight: 700;
            padding: 5px 10px;
            border-radius: 50px;
        }
        .rating-excellent { background-color: #d4edda; color: #155724; }
        .rating-good { background-color: #fff3cd; color: #856404; }
        .rating-poor { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>

<header class="header-banner">
    <div class="container d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="bi bi-clipboard-check-fill"></i> ระบบจัดการผลการประเมิน</h1>
            <p class="mb-0">สรุปผลความพึงพอใจการจัดงานคืนสู่เหย้า ปี <?php echo $filter_year; ?></p>
        </div>
        <div>
            <a href="admin.php" class="btn btn-outline-light"><i class="bi bi-arrow-left"></i> กลับหน้าแผงควบคุม</a>
        </div>
    </div>
</header>

<div class="container">
    <!-- Filter Bar -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">กรองข้อมูล</h5>
            <form action="" method="GET" class="d-flex gap-2">
                <select name="year" class="form-select" onchange="this.form.submit()">
                    <?php foreach($years as $y): ?>
                    <option value="<?php echo $y; ?>" <?php echo $y == $filter_year ? 'selected' : ''; ?>>ปีการศึกษา <?php echo $y; ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="stat-card bg-gradient-primary">
                <p>จำนวนผู้ทำแบบประเมินทั้งหมด</p>
                <h3><?php echo $total_evals; ?></h3>
                <small>คน</small>
            </div>
        </div>
        <div class="col-md-4">
            <?php
            // Calculate overall Avg
            $overall_sum = 0;
            $count = 0;
            foreach($q_fields as $f) {
                if (isset($stats["avg_$f"])) {
                    $overall_sum += (float)$stats["avg_$f"];
                    $count++;
                }
            }
            $overall_avg = $count > 0 ? $overall_sum / $count : 0;
            ?>
            <div class="stat-card bg-gradient-success">
                <p>คะแนนความพึงพอใจโดยรวม</p>
                <h3><?php echo number_format($overall_avg, 2); ?></h3>
                <small>เต็ม 5.00 คะแนน</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card bg-gradient-orange">
                <p>ระดับความพึงพอใจ</p>
                <h3>
                    <?php
                    if ($overall_avg >= 4.5) echo "ดีเยี่ยม";
                    else if ($overall_avg >= 3.5) echo "ดี";
                    else if ($overall_avg >= 2.5) echo "ปานกลาง";
                    else echo "ควรปรับปรุง";
                    ?>
                </h3>
                <small>ตามค่าเฉลี่ยรวม</small>
            </div>
        </div>
    </div>

    <!-- Average scores per section -->
    <div class="main-container">
        <h3 class="section-header">คะแนนเฉลี่ยแยกรายข้อ</h3>
        
        <?php foreach($sections as $title => $qs): ?>
            <h5 class="mt-4 text-primary fw-bold"><?php echo $title; ?></h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 70%">หัวข้อการประเมิน</th>
                            <th class="text-center">คะแนนเฉลี่ย (1-5)</th>
                            <th style="width: 15%">ระดับที่ได้</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($qs as $qid): 
                            $avg = (float)($stats["avg_$qid"] ?? 0);
                            $badge_class = "rating-poor";
                            $tag = "ควรปรับปรุง";
                            if ($avg >= 4.5) { $badge_class = "rating-excellent"; $tag = "ดีเยี่ยม"; }
                            else if ($avg >= 3.5) { $badge_class = "rating-good"; $tag = "ดี"; }
                            else if ($avg >= 2.5) { $badge_class = "rating-good"; $tag = "ปานกลาง"; }
                        ?>
                        <tr>
                            <td><?php echo $q_texts[$qid]; ?></td>
                            <td class="text-center fw-bold text-primary fs-5"><?php echo number_format($avg, 2); ?></td>
                            <td><span class="star-rating-badge <?php echo $badge_class; ?>"><?php echo $tag; ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Individual Responses Table -->
    <div class="main-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="section-header m-0 border-0 p-0">รายชื่อผู้ทำแบบประเมินและข้อเสนอแนะ</h3>
            <button class="btn btn-success" onclick="exportToExcel()"><i class="bi bi-file-earmark-excel"></i> ส่งออกข้อมูลทั้งหมด</button>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle" id="evaluationsTable">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>ชื่อ-นามสกุล</th>
                        <th>เบอร์โทร</th>
                        <th>Lucky Code</th>
                        <th>สถานะ</th>
                        <th>วันที่ทำรายการ</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($all_evals_res && $all_evals_res->num_rows > 0): ?>
                        <?php while($row = $all_evals_res->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td class="fw-bold"><?php echo htmlspecialchars($row['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['phone']); ?></td>
                            <td><span class="badge bg-warning text-dark font-monospace"><?php echo $row['lucky_code']; ?></span></td>
                            <td><small><?php echo htmlspecialchars($row['user_status']); ?></small></td>
                            <td><small><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></small></td>
                            <td>
                                <button class="btn btn-info btn-sm text-white" onclick="viewDetails(<?php echo $row['id']; ?>)"><i class="bi bi-eye"></i> ดู</button>
                                <button class="btn btn-danger btn-sm" onclick="deleteEvaluation(<?php echo $row['id']; ?>)"><i class="bi bi-trash"></i> ลบ</button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" id="modalContent">
            <!-- Content loaded via AJAX -->
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        $('#evaluationsTable').DataTable({
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/1.11.5/i18n/th.json"
            },
            "order": [[ 0, "desc" ]],
            "responsive": true
        });
    });

    async function viewDetails(id) {
        const modal = new bootstrap.Modal(document.getElementById('detailsModal'));
        document.getElementById('modalContent').innerHTML = '<div class="p-5 text-center"><div class="spinner-border text-primary"></div><p class="mt-2">กำลังโหลด...</p></div>';
        modal.show();

        try {
            const response = await fetch('api/get_evaluation_details.php?id=' + id);
            const html = await response.text();
            document.getElementById('modalContent').innerHTML = html;
        } catch (e) {
            document.getElementById('modalContent').innerHTML = '<div class="p-5 text-center text-danger">เกิดข้อผิดพลาดในการโหลดข้อมูล</div>';
        }
    }

    function deleteEvaluation(id) {
        Swal.fire({
            title: 'ยืนยันการลบ?',
            text: "ข้อมูลแบบประเมินและสิทธิ์ Lucky Draw จะถูกลบถาวร!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'ใช่, ลบเลย!',
            cancelButtonText: 'ยกเลิก'
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    const response = await fetch('api/delete_evaluation.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'id=' + id
                    });
                    const res = await response.json();
                    if (res.success) {
                        Swal.fire('สำเร็จ!', 'ลบข้อมูลเรียบร้อยแล้ว', 'success').then(() => location.reload());
                    } else {
                        throw new Error(res.message);
                    }
                } catch (e) {
                    Swal.fire('ข้อผิดพลาด', e.message, 'error');
                }
            }
        });
    }

    function exportToExcel() {
        window.location.href = 'api/export_evaluations.php?year=<?php echo $filter_year; ?>';
    }
</script>

</body>
</html>
