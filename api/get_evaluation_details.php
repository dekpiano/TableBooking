<?php
session_start();
include_once 'connectDB.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    die('<div class="p-4 text-danger text-center">Session ล่วงเวลา กรุณาเข้าสู่ระบบใหม่</div>');
}

if (!isset($_GET['id'])) {
    die('<div class="p-4 text-warning text-center">ไม่พบ ID ที่ระบุ</div>');
}

$id = (int)$_GET['id'];
$sql = "SELECT * FROM tb_evaluations WHERE id = $id";
$res = $conn->query($sql);

if (!$res || $res->num_rows == 0) {
    die('<div class="p-4 text-warning text-center">ไม่พบข้อมูลแบบประเมินนี้</div>');
}

$data = $res->fetch_assoc();

// Question text mapping
$q_texts = [
    "q1_1" => "การประชาสัมพันธ์งาน", "q1_2" => "ความสะดวกในการซื้อบัตรและการรับบัตร", "q1_3" => "ความเหมาะสมของวัน เวลาจัดงาน", "q1_4" => "การต้อนรับของฝ่ายจัดงาน", "q1_5" => "การให้ข้อมูลและการประสานงาน",
    "q2_1" => "ความเหมาะสมของสถานที่", "q2_2" => "การตกแต่งสถานที่และบรรยากาศ", "q2_3" => "ความสะอาดและระเบียบเรียบร้อย", "q2_4" => "ระบบแสง เสียง และเวที",
    "q3_1" => "ความน่าสนใจของกิจกรรม", "q3_2" => "ความหลากหลายของกิจกรรม", "q3_3" => "การเปิดโอกาสให้มีส่วนร่วม", "q3_4" => "การจัดลำดับขั้นตอนของกิจกรรม",
    "q4_1" => "การบริการอาหารและเครื่องดื่ม", "q4_2" => "รสชาติของอาหาร", "q4_3" => "คุณค่าและปริมาณของอาหาร", "q4_4" => "การบริการจอดรถและการจราจร", "q4_5" => "ความสะอาดของห้องน้ำ",
    "q5_1" => "ความหลากหลายของสินค้า", "q5_2" => "ความเหมาะสมของราคา", "q5_3" => "คุณภาพของสินค้า", "q5_4" => "ความสะดวกในการเลือกซื้อ",
    "q6_1" => "ความประทับใจโดยรวม", "q6_2" => "ความคุ้มค่าในการเข้าร่วมงาน", "q6_3" => "ความต้องการเข้าร่วมอีกในอนาคต"
];

$sections = [
    "1. ด้านการจัดงานและพิธีการ" => ["q1_1", "q1_2", "q1_3", "q1_4", "q1_5"],
    "2. ด้านสถานที่และบรรยากาศ" => ["q2_1", "q2_2", "q2_3", "q2_4"],
    "3. ด้านกิจกรรมภายในงาน" => ["q3_1", "q3_2", "q3_3", "q3_4"],
    "4. ด้านอาหารและการบริการ" => ["q4_1", "q4_2", "q4_3", "q4_4", "q4_5"],
    "5. ด้านการจำหน่ายของที่ระลึก" => ["q5_1", "q5_2", "q5_3", "q5_4"],
    "6. ด้านความประทับใจโดยรวม" => ["q6_1", "q6_2", "q6_3"]
];
?>

<div class="modal-header bg-primary text-white">
    <h5 class="modal-title"><i class="bi bi-person-fill"></i> ข้อมูลผู้ทำแบบประเมิน: <?php echo htmlspecialchars($data['full_name']); ?></h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <div class="row mb-4">
        <hr>
        <div class="col-md-6 border-end">
            <p><i class="bi bi-person"></i> <b>ชื่อ-นามสกุล:</b> <?php echo htmlspecialchars($data['full_name']); ?></p>
            <p><i class="bi bi-phone"></i> <b>เบอร์โทรศัพท์:</b> <?php echo htmlspecialchars($data['phone']); ?></p>
            <p><i class="bi bi-card-text"></i> <b>เลขนำโชค:</b> <span class="badge bg-warning text-dark"><?php echo htmlspecialchars($data['lucky_code']); ?></span></p>
            <p><i class="bi bi-mortarboard-fill"></i> <b>เข้าเรียน ปี:</b> <?php echo htmlspecialchars($data['enroll_year'] ?: '-'); ?></p>
            <p><i class="bi bi-award-fill"></i> <b>ระดับชั้นที่จบ:</b> <?php echo htmlspecialchars($data['grad_level'] ?: '-'); ?></p>
        </div>
        <div class="col-md-6">
            <p><i class="bi bi-gender-ambiguous"></i> <b>เพศ:</b> <?php echo htmlspecialchars($data['gender'] ?: '-'); ?></p>
            <p><i class="bi bi-calendar-range"></i> <b>อายุ:</b> <?php echo htmlspecialchars($data['age_range'] ?: '-'); ?></p>
            <p><i class="bi bi-building"></i> <b>โรงเรียนที่จบ:</b> <?php echo htmlspecialchars($data['grad_school'] ?: '-'); ?></p>
            <p><i class="bi bi-briefcase"></i> <b>สถานะ:</b> <?php echo htmlspecialchars($data['user_status'] ?: '-'); ?> <?php echo $data['user_status_other'] ? "(".htmlspecialchars($data['user_status_other']).")" : ""; ?></p>
            <p><i class="bi bi-tools"></i> <b>อาชีพ:</b> <?php echo htmlspecialchars($data['occupation'] ?: '-'); ?> <?php echo $data['occupation_other'] ? "(".htmlspecialchars($data['occupation_other']).")" : ""; ?></p>
        </div>
        <hr>
    </div>

    <!-- Rating Results -->
    <div class="row">
        <?php foreach($sections as $title => $qs): ?>
            <div class="col-12 mb-3">
                <h6 class="text-primary fw-bold mb-2 shadow-sm p-2 bg-light rounded"><?php echo $title; ?></h6>
            </div>
            <?php foreach($qs as $qid): ?>
                <div class="col-md-8 mb-2 border-bottom pb-1 small">
                    <?php echo $q_texts[$qid]; ?>
                </div>
                <div class="col-md-4 mb-2 border-bottom pb-1 text-end">
                    <?php 
                    $stars = (int)$data[$qid];
                    for($i=1; $i<=5; $i++){
                        if($i <= $stars) echo '<i class="bi bi-star-fill text-warning"></i>';
                        else echo '<i class="bi bi-star text-muted opacity-50"></i>';
                    }
                    echo " ($stars)";
                    ?>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>

    <!-- Comments -->
    <div class="row mt-4">
        <div class="col-12">
            <h6 class="text-info fw-bold mb-2 p-2 bg-light rounded"><i class="bi bi-chat-left-dots-fill"></i> ความคิดเห็นและข้อเสนอแนะ</h6>
            <div class="card mb-3 border-0 bg-light-subtle">
                <div class="card-body">
                    <p class="mb-1 fw-bold small text-muted">สิ่งที่ประทับใจ:</p>
                    <p class="mb-3"><?php echo nl2br(htmlspecialchars($data['comment_impress'] ?: '-')); ?></p>
                    
                    <p class="mb-1 fw-bold small text-muted">สิ่งที่ควรปรับปรุง:</p>
                    <p class="mb-3"><?php echo nl2br(htmlspecialchars($data['comment_improve'] ?: '-')); ?></p>
                    
                    <p class="mb-1 fw-bold small text-muted">ข้อเสนอแนะเพิ่มเติม:</p>
                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($data['comment_more'] ?: '-')); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิดหน้าต่าง</button>
</div>
<?php $conn->close(); ?>
