<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แบบประเมินความพึงพอใจการจัดงานคืนสู่เหย้า - สวนกุหลาบวิทยาลัย (จิรประวัติ)</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=K2D:wght@400;700&display=swap" rel="stylesheet">
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #f8b4c8;
            --bg-gradient: linear-gradient(135deg, #f8b4c8 0%, #a9d6f5 100%);
        }

        body {
            font-family: 'K2D', sans-serif;
            background: var(--bg-gradient);
            min-height: 100vh;
            color: #333;
            padding-bottom: 2rem;
        }

        .header-banner {
            padding: 1.5rem 1rem;
            text-align: center;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            margin-bottom: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
        }

        .header-banner h1 {
            font-weight: 700;
            font-size: 1.5rem; /* Smaller for mobile */
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .header-banner .lead {
            font-size: 1rem;
        }

        .evaluation-container {
            max-width: 900px;
            margin: 0 10px; /* Small margin on mobile */
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(15px);
            border-radius: 15px;
            padding: 1.2rem; /* Less padding for mobile */
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }

        /* Desktop adjustments */
        @media (min-width: 768px) {
            .header-banner { padding: 3rem 1rem; }
            .header-banner h1 { font-size: 2.2rem; }
            .evaluation-container { margin: 0 auto; padding: 2.5rem; }
        }

        .section-title {
            background: var(--primary-color);
            color: white;
            padding: 8px 15px;
            border-radius: 8px;
            margin: 1.5rem 0 1rem 0;
            display: inline-block;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .question-card {
            background: white;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 0.8rem;
            border: 1px solid #eee;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }

        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-start;
            gap: 12px;
            margin-top: 10px;
        }

        /* Desktop: align stars to the right of text */
        @media (min-width: 768px) {
            .question-card {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 0.8rem 1.5rem;
            }
            .star-rating {
                margin-top: 0;
                justify-content: flex-end;
            }
        }

        .star-rating input {
            display: none;
        }

        .star-rating label {
            cursor: pointer;
            font-size: 2rem; /* Larger touch target for mobile */
            color: #ddd;
            transition: color 0.1s;
        }

        .star-rating label:before {
            content: "\F588" !important; /* star-fill */
            font-family: "bootstrap-icons" !important;
        }

        .star-rating input:checked ~ label,
        .star-rating label:hover,
        .star-rating label:hover ~ label {
            color: #ffc107; /* Yellow for selected */
        }

        .star-rating label {
            color: #e4e4e4; /* Light grey for unselected */
        }

        /* Custom Radio/Checkbox sizing for mobile */
        .form-check-input {
            width: 1.4rem;
            height: 1.4rem;
            margin-top: 0.1em;
        }
        .form-check-label {
            margin-left: 0.5rem;
            padding-top: 0.2rem;
            font-size: 1.05rem;
        }
        .form-check {
            margin-bottom: 0.6rem;
        }

        .btn-submit {
            background: var(--primary-color);
            border: none;
            padding: 1rem 2rem;
            border-radius: 30px;
            font-weight: 700;
            font-size: 1.1rem;
            width: 100%; /* Full width on mobile */
            transition: all 0.3s;
            box-shadow: 0 5px 15px rgba(13, 110, 253, 0.4);
        }

        @media (min-width: 768px) {
            .btn-submit { width: auto; padding: 0.8rem 4rem; }
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(13, 110, 253, 0.6);
            background: #0b5ed7;
        }

        .form-label {
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .luckydraw-notice {
            background: #fff3cd;
            border-left: 5px solid #ffc107;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
        }
    </style>
</head>
<body>
    <header class="header-banner">
        <div class="container">
            <h1>แบบสอบถามความพึงพอใจการจัดงานคืนสู่เหย้า</h1>
            <p class="lead">ร้อยดวงใจแห่งรัก คืนสู่เหย้า ชาว จ.ป. ❤️ จ.ว. ❤️ ส.ก.จ.</p>
        </div>
    </header>

    <div class="container">
        <div class="evaluation-container">
            <div class="luckydraw-notice">
                <i class="bi bi-gift-fill text-warning"></i> <strong>ลุ้นรับรางวัล Lucky Draw!</strong> สำหรับผู้ที่ทำแบบประเมินจนจบและกรอกชื่อ-เบอร์โทรศัพท์ที่ถูกต้อง ทีมงานจะทำการสุ่มจับสลากผู้โชคดีในช่วงท้ายของงานครับ
            </div>

            <form id="evaluationForm">
                <!-- Part 1: General Info -->
                <h4 class="section-title">ตอนที่ 1: ข้อมูลทั่วไป</h4>
                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="full_name" class="form-label">ชื่อ-นามสกุล <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="full_name" name="full_name" required placeholder="ชื่อ-นามสกุลของคุณ">
                    </div>
                    <div class="col-md-6">
                        <label for="phone" class="form-label">เบอร์โทรศัพท์ <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" id="phone" name="phone" required placeholder="เบอร์โทรศัพท์มือถือ">
                    </div>
                    <div class="col-md-6">
                        <label for="enroll_year" class="form-label">เข้าเรียน ปี</label>
                        <input type="text" class="form-control" id="enroll_year" name="enroll_year" placeholder="ปี พ.ศ. ที่เริ่มเข้าเรียน">
                    </div>
                    <div class="col-md-6">
                        <label for="grad_level" class="form-label">ระดับชั้นที่จบ</label>
                        <input type="text" class="form-control" id="grad_level" name="grad_level" placeholder="ระดับชั้นที่จบการศึกษา">
                    </div>

                    <!-- Gender -->
                    <div class="col-12">
                        <label class="form-label me-3">เพศ:</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="gender" id="gender_male" value="ชาย">
                            <label class="form-check-label" for="gender_male">ชาย</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="gender" id="gender_female" value="หญิง">
                            <label class="form-check-label" for="gender_female">หญิง</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="gender" id="gender_other" value="อื่นๆ">
                            <label class="form-check-label" for="gender_other">อื่นๆ</label>
                        </div>
                    </div>

                    <!-- Age -->
                    <div class="col-12">
                        <label class="form-label d-block">อายุ:</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="age_range" id="age1" value="ต่ำกว่า 25 ปี">
                            <label class="form-check-label" for="age1">ต่ำกว่า 25 ปี</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="age_range" id="age2" value="25-35 ปี">
                            <label class="form-check-label" for="age2">25-35 ปี</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="age_range" id="age3" value="36-45 ปี">
                            <label class="form-check-label" for="age3">36-45 ปี</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="age_range" id="age4" value="46-55 ปี">
                            <label class="form-check-label" for="age4">46-55 ปี</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="age_range" id="age5" value="56-65 ปี">
                            <label class="form-check-label" for="age5">56-65 ปี</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="age_range" id="age6" value="มากกว่า 65 ปี">
                            <label class="form-check-label" for="age6">มากกว่า 65 ปี</label>
                        </div>
                    </div>

                    <!-- School -->
                    <div class="col-12">
                        <label class="form-label d-block">ชื่อโรงเรียนในปีที่จบ:</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="grad_school" id="sch1" value="จ.ป.">
                            <label class="form-check-label" for="sch1">จ.ป.</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="grad_school" id="sch2" value="จ.ว.">
                            <label class="form-check-label" for="sch2">จ.ว.</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="grad_school" id="sch3" value="ส.ก.จ.">
                            <label class="form-check-label" for="sch3">ส.ก.จ.</label>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="col-12">
                        <label class="form-label d-block">สถานะ:</label>
                        <div class="row g-2">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="user_status" id="st1" value="ศิษย์เก่า">
                                    <label class="form-check-label" for="st1">ศิษย์เก่า</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="user_status" id="st2" value="ครูอาวุโส">
                                    <label class="form-check-label" for="st2">ครูอาวุโส</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="user_status" id="st3" value="ครู/บุคลากร ปัจจุบัน">
                                    <label class="form-check-label" for="st3">ครู/บุคลากร ปัจจุบัน</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="user_status" id="st4" value="แขกผู้มีเกียรติ">
                                    <label class="form-check-label" for="st4">แขกผู้มีเกียรติ</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="user_status" id="st5" value="ผู้ปกครอง">
                                    <label class="form-check-label" for="st5">ผู้ปกครอง</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="user_status" id="st6" value="บุคลากรเครือสวนกุหลาบ">
                                    <label class="form-check-label" for="st6">บุคลากรเครือสวนกุหลาบ</label>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-check d-flex align-items-center">
                                    <input class="form-check-input" type="radio" name="user_status" id="st7" value="อื่นๆ">
                                    <label class="form-check-label me-2 ms-2" for="st7">อื่นๆ</label>
                                    <input type="text" class="form-control form-control-sm w-50" name="user_status_other" placeholder="ระบุสถานะอื่นๆ">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Occupation -->
                    <div class="col-12">
                        <label class="form-label d-block">ประกอบอาชีพ:</label>
                        <div class="row g-2">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="occupation" id="oc1" value="รับราชการ">
                                    <label class="form-check-label" for="oc1">รับราชการ</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="occupation" id="oc2" value="พนักงานบริษัท">
                                    <label class="form-check-label" for="oc2">พนักงานบริษัท</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="occupation" id="oc3" value="ธุรกิจส่วนตัว">
                                    <label class="form-check-label" for="oc3">ธุรกิจส่วนตัว</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="occupation" id="oc4" value="นักเรียน/นักศึกษา">
                                    <label class="form-check-label" for="oc4">นักเรียน/นักศึกษา</label>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-check d-flex align-items-center">
                                    <input class="form-check-input" type="radio" name="occupation" id="oc5" value="อื่นๆ">
                                    <label class="form-check-label me-2 ms-2" for="oc5">อื่นๆ</label>
                                    <input type="text" class="form-control form-control-sm w-50" name="occupation_other" placeholder="ระบุอาชีพอื่นๆ">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Part 2: Evaluation -->
                <h4 class="section-title">ตอนที่ 2: ความพึงพอใจต่อการดำเนินงาน</h4>
                <p class="text-muted mb-4">(ระดับคะแนน: 5 = ดีมาก, 1 = ควรปรับปรุง)</p>

                <?php
                $sections = [
                    "1. ด้านการจัดงานและพิธีการ" => ["q1_1" => "การประชาสัมพันธ์งาน", "q1_2" => "ความสะดวกในการซื้อบัตรและการรับบัตร", "q1_3" => "ความเหมาะสมของวัน เวลาจัดงาน 
(วันที่ 7 พฤศจิกายน ของทุกปี) 
", "q1_4" => "การต้อนรับของฝ่ายจัดงาน", "q1_5" => "การให้ข้อมูลและการประสานงาน"],
                    "2. ด้านสถานที่และบรรยากาศ" => ["q2_1" => "ความเหมาะสมของสถานที่", "q2_2" => "การตกแต่งสถานที่และบรรยากาศ", "q2_3" => "ความสะอาดและระเบียบเรียบร้อย", "q2_4" => "ระบบแสง เสียง และเวที"],
                    "3. ด้านกิจกรรมภายในงาน" => ["q3_1" => "ความน่าสนใจของกิจกรรม", "q3_2" => "ความหลากหลายของกิจกรรม", "q3_3" => "การเปิดโอกาสให้มีส่วนร่วม", "q3_4" => "การจัดลำดับขั้นตอนของกิจกรรม"],
                    "4. ด้านอาหารและการบริการ" => ["q4_1" => "การบริการอาหารและเครื่องดื่ม", "q4_2" => "รสชาติของอาหาร", "q4_3" => "คุณค่าและปริมาณของอาหาร", "q4_4" => "การบริการจอดรถและการจราจร", "q4_5" => "ความสะอาดของห้องน้ำ"],
                    "5. ด้านการจำหน่ายของที่ระลึก" => ["q5_1" => "ความหลากหลายของสินค้า พระ เสื้อ ลิสแบนด์ สติกเกอร์ติดรถ ", "q5_2" => "ความเหมาะสมของราคา", "q5_3" => "คุณภาพของสินค้า", "q5_4" => "ความสะดวกในการเลือกซื้อ"],
                    "6. ด้านความประทับใจโดยรวม" => ["q6_1" => "ความประทับใจโดยรวม", "q6_2" => "ความคุ้มค่าในการเข้าร่วมงาน", "q6_3" => "ความต้องการเข้าร่วมอีกในอนาคต"]
                ];

                foreach($sections as $title => $qs) {
                    echo "<h5 class='mt-4 mb-2 text-primary fw-bold'>$title</h5>";
                    foreach($qs as $id => $text) {
                        ?>
                        <div class="question-card">
                            <div class="question-text"><?php echo $text; ?></div>
                            <div class="star-rating">
                                <input type="radio" id="<?php echo $id; ?>-5" name="<?php echo $id; ?>" value="5" required/><label for="<?php echo $id; ?>-5"></label>
                                <input type="radio" id="<?php echo $id; ?>-4" name="<?php echo $id; ?>" value="4"/><label for="<?php echo $id; ?>-4"></label>
                                <input type="radio" id="<?php echo $id; ?>-3" name="<?php echo $id; ?>" value="3"/><label for="<?php echo $id; ?>-3"></label>
                                <input type="radio" id="<?php echo $id; ?>-2" name="<?php echo $id; ?>" value="2"/><label for="<?php echo $id; ?>-2"></label>
                                <input type="radio" id="<?php echo $id; ?>-1" name="<?php echo $id; ?>" value="1"/><label for="<?php echo $id; ?>-1"></label>
                            </div>
                        </div>
                        <?php
                    }
                }
                ?>

                <!-- Part 3: Comments -->
                <h4 class="section-title">ตอนที่ 3: ข้อเสนอแนะอื่นๆ</h4>
                <div class="mb-3">
                    <label for="comment_impress" class="form-label">สิ่งที่ท่านประทับใจมากที่สุดครั้งนี้</label>
                    <textarea class="form-control" id="comment_impress" name="comment_impress" rows="3"></textarea>
                </div>
                <div class="mb-3">
                    <label for="comment_improve" class="form-label">สิ่งที่ควรปรับปรุงหรือพัฒนาต่อไป</label>
                    <textarea class="form-control" id="comment_improve" name="comment_improve" rows="3"></textarea>
                </div>
                <div class="mb-3">
                    <label for="comment_more" class="form-label">ข้อเสนอแนะเพิ่มเติม</label>
                    <textarea class="form-control" id="comment_more" name="comment_more" rows="3"></textarea>
                </div>

                <div class="text-center mt-5">
                    <button type="submit" class="btn btn-primary btn-submit">ส่งแบบประเมินและลุ้นรางวัล</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.getElementById('evaluationForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            Swal.fire({
                title: 'กำลังส่งข้อมูล...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            const formData = new FormData(this);

            try {
                const response = await fetch('api/submit_evaluation.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'บันทึกข้อมูลสำเร็จ!',
                        html: `
                            <div class="mt-3 mb-4">
                                <p class="mb-2">ขอบคุณที่ร่วมทำแบบประเมินครับ<br>นี่คือเลขนำโชคของคุณสำหรับลุ้นรางวัล Lucky Draw:</p>
                                <div style="background: linear-gradient(45deg, #ffc107, #ff9800); color: #000; font-size: 3.5rem; font-weight: 800; padding: 15px; border-radius: 15px; letter-spacing: 8px; box-shadow: 0 10px 20px rgba(0,0,0,0.1);">
                                    ${result.lucky_code}
                                </div>
                                <p class="mt-4 text-danger fw-bold"><i class="bi bi-camera-fill"></i> กรุณาแคปหน้าจอเก็บไว้เป็นหลักฐาน<br>เพื่อใช้รับรางวัลบนเวที</p>
                            </div>
                        `,
                        confirmButtonText: 'รับทราบ (กลับหน้าหลัก)',
                        confirmButtonColor: '#0d6efd',
                        allowOutsideClick: false
                    }).then(() => {
                        window.location.href = 'index.html';
                    });
                } else {
                    throw new Error(result.message);
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด',
                    text: error.message
                });
            }
        });
    </script>
</body>
</html>
