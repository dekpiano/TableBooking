<?php
session_start();
include_once 'connectDB.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    die('Unauthorized access.');
}

$filter_year = isset($_GET['year']) ? (int)$_GET['year'] : 2025;

// Build query to fetch all data
$sql = "SELECT * FROM tb_evaluations WHERE event_year = $filter_year ORDER BY id ASC";
$res = $conn->query($sql);

if (!$res) {
    die("Error fetching data: " . $conn->error);
}

$filename = "evaluations_report_" . $filter_year . "_" . date("Ymd_His") . ".csv";

// Output headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

// Add UTF-8 BOM for Thai characters in Excel
echo "\xEF\xBB\xBF";

$output = fopen('php://output', 'w');

// Mapping for more readable results in the CSV
$q_texts = [
    "q1_1" => "1.1 การประชาสัมพันธ์งาน", "q1_2" => "1.2 ความสะดวกในการซื้อบัตร", "q1_3" => "1.3 ความเหมาะสมของวันเวลา", "q1_4" => "1.4 การต้อนรับ", "q1_5" => "1.5 การให้ข้อมูล",
    "q2_1" => "2.1 ความเหมาะสมสถานที่", "q2_2" => "2.2 การตกแต่งสถานที่", "q2_3" => "2.3 ความสะอาด", "q2_4" => "2.4 ระบบแสงเสียงเและวที",
    "q3_1" => "3.1 ความน่าสนใจกิจกรรม", "q3_2" => "3.2 ความหลากหลายกิจกรรม", "q3_3" => "3.3 การมีส่วนร่วม", "q3_4" => "3.4 ลำดับขั้นตอนกิจกรรม",
    "q4_1" => "4.1 บริการอาหารและดื่ม", "q4_2" => "4.2 รสชาติอาหาร", "q4_3" => "4.3 คุณค่าและปริมาณอาหาร", "q4_4" => "4.4 บริการที่จอดรถ", "q4_5" => "4.5 ความสะอาดห้องน้ำ",
    "q5_1" => "5.1 ความหลากหลายสินค้า", "q5_2" => "5.2 ความเหมาะสมราคา", "q5_3" => "5.3 คุณภาพสินค้า", "q5_4" => "5.4 ความสะดวกเลือกซื้อ",
    "q6_1" => "6.1 ความประทับใจโดยรวม", "q6_2" => "6.2 ความคุ้มค่าเข้าร่วม", "q6_3" => "6.3 ความต้องการเข้าร่วมในอนาคต"
];

$header_fields = [
    'ID', 'ชื่อ-นามสกุล', 'เบอร์โทร', 'เลขนำโชค', 'ปีการศึกษา', 'เข้าเรียนปี', 'ระดับที่จบ', 'เพศ', 'อายุ', 'โรงเรียน', 'สถานะ', 'อาชีพ', 'แหล่งข้อมูลข่าวสาร'
];

// Add rating field names to header
foreach($q_texts as $key => $val) {
    $header_fields[] = $val;
}

// Add comment field names
$header_fields[] = 'สิ่งที่ประทับใจ';
$header_fields[] = 'สิ่งที่ควรปรับปรุง';
$header_fields[] = 'ข้อเสนอแนะเพิ่มเติม';
$header_fields[] = 'เวลาที่บันทึก';

fputcsv($output, $header_fields);

// Data rows
while($row = $res->fetch_assoc()) {
    $data_row = [
        $row['id'], $row['full_name'], $row['phone'], $row['lucky_code'], $row['event_year'], $row['enroll_year'], $row['grad_level'], $row['gender'], $row['age_range'], $row['grad_school'], $row['user_status'], $row['occupation'], $row['info_source']
    ];
    
    // Add ratings
    foreach($q_texts as $key => $val) {
        $data_row[] = $row[$key];
    }
    
    // Add comments
    $data_row[] = $row['comment_impress'];
    $data_row[] = $row['comment_improve'];
    $data_row[] = $row['comment_more'];
    $data_row[] = $row['created_at'];
    
    fputcsv($output, $data_row);
}

fclose($output);
$conn->close();
?>
