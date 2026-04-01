<?php
header('Content-Type: application/json');
include_once 'connectDB.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $conn->real_escape_string($_POST['full_name']);
    $phone = $conn->real_escape_string($_POST['phone']);

    // Get current event year from settings
    $res_year = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key = 'current_event_year'");
    $event_year = ($res_year && $res_year->num_rows > 0) ? (int)$res_year->fetch_assoc()['setting_value'] : 2025;

    // Check if phone already exists in THIS YEAR to prevent duplicate entries for Lucky Draw
    $checkSql = "SELECT id FROM tb_evaluations WHERE phone = '$phone' AND event_year = $event_year";
    $checkResult = $conn->query($checkSql);
    if ($checkResult && $checkResult->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => "ขออภัย เบอร์โทรศัพท์ $phone นี้ได้ใช้ทำแบบประเมินและลุ้นสิทธิ์รางวัลสของปี $event_year ไปแล้ว ไม่สามารถทำซ้ำได้ครับ"]);
        exit();
    }

    $enroll_year = $conn->real_escape_string($_POST['enroll_year'] ?? '');

    // Generate Unique 6-digit Lucky Code (Lottery-style)
    $lucky_code = "";
    while(true) {
        $lucky_code = sprintf('%06d', mt_rand(0, 999999));
        $checkCode = $conn->query("SELECT id FROM tb_evaluations WHERE lucky_code = '$lucky_code'");
        if ($checkCode && $checkCode->num_rows == 0) break;
    }

    $grad_level = $conn->real_escape_string($_POST['grad_level'] ?? '');
    $gender = $conn->real_escape_string($_POST['gender'] ?? '');
    $age_range = $conn->real_escape_string($_POST['age_range'] ?? '');
    $grad_school = $conn->real_escape_string($_POST['grad_school'] ?? '');
    $user_status = $conn->real_escape_string($_POST['user_status'] ?? '');
    $user_status_other = $conn->real_escape_string($_POST['user_status_other'] ?? '');
    $occupation = $conn->real_escape_string($_POST['occupation'] ?? '');
    $occupation_other = $conn->real_escape_string($_POST['occupation_other'] ?? '');
    $info_source = $conn->real_escape_string($_POST['info_source'] ?? '');
    
    // Rating questions (25 total)
    $q1_1 = (int)($_POST['q1_1'] ?? 0); $q1_2 = (int)($_POST['q1_2'] ?? 0); $q1_3 = (int)($_POST['q1_3'] ?? 0); $q1_4 = (int)($_POST['q1_4'] ?? 0); $q1_5 = (int)($_POST['q1_5'] ?? 0);
    $q2_1 = (int)($_POST['q2_1'] ?? 0); $q2_2 = (int)($_POST['q2_2'] ?? 0); $q2_3 = (int)($_POST['q2_3'] ?? 0); $q2_4 = (int)($_POST['q2_4'] ?? 0);
    $q3_1 = (int)($_POST['q3_1'] ?? 0); $q3_2 = (int)($_POST['q3_2'] ?? 0); $q3_3 = (int)($_POST['q3_3'] ?? 0); $q3_4 = (int)($_POST['q3_4'] ?? 0);
    $q4_1 = (int)($_POST['q4_1'] ?? 0); $q4_2 = (int)($_POST['q4_2'] ?? 0); $q4_3 = (int)($_POST['q4_3'] ?? 0); $q4_4 = (int)($_POST['q4_4'] ?? 0); $q4_5 = (int)($_POST['q4_5'] ?? 0);
    $q5_1 = (int)($_POST['q5_1'] ?? 0); $q5_2 = (int)($_POST['q5_2'] ?? 0); $q5_3 = (int)($_POST['q5_3'] ?? 0); $q5_4 = (int)($_POST['q5_4'] ?? 0);
    $q6_1 = (int)($_POST['q6_1'] ?? 0); $q6_2 = (int)($_POST['q6_2'] ?? 0); $q6_3 = (int)($_POST['q6_3'] ?? 0);

    $comment_impress = $conn->real_escape_string($_POST['comment_impress'] ?? '');
    $comment_improve = $conn->real_escape_string($_POST['comment_improve'] ?? '');
    $comment_more = $conn->real_escape_string($_POST['comment_more'] ?? '');

    $sql = "INSERT INTO tb_evaluations (
                full_name, phone, lucky_code, event_year, enroll_year, grad_level, gender, age_range, grad_school, user_status, user_status_other, occupation, occupation_other, info_source,
                q1_1, q1_2, q1_3, q1_4, q1_5,
                q2_1, q2_2, q2_3, q2_4,
                q3_1, q3_2, q3_3, q3_4,
                q4_1, q4_2, q4_3, q4_4, q4_5,
                q5_1, q5_2, q5_3, q5_4,
                q6_1, q6_2, q6_3,
                comment_impress, comment_improve, comment_more
            ) VALUES (
                '$full_name', '$phone', '$lucky_code', $event_year, '$enroll_year', '$grad_level', '$gender', '$age_range', '$grad_school', '$user_status', '$user_status_other', '$occupation', '$occupation_other', '$info_source',
                $q1_1, $q1_2, $q1_3, $q1_4, $q1_5,
                $q2_1, $q2_2, $q2_3, $q2_4,
                $q3_1, $q3_2, $q3_3, $q3_4,
                $q4_1, $q4_2, $q4_3, $q4_4, $q4_5,
                $q5_1, $q5_2, $q5_3, $q5_4,
                $q6_1, $q6_2, $q6_3,
                '$comment_impress', '$comment_improve', '$comment_more'
            )";

    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'message' => 'บันทึกข้อมูลเรียบร้อยแล้ว!', 'lucky_code' => $lucky_code]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>
