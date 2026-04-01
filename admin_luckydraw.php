<?php
session_start();
include_once 'api/connectDB.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$admin_name = $_SESSION['admin_name'];
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบจับรางวัล Lucky Draw - Lottery Style</title>
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
    <!-- Canvas Confetti -->
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    <style>
        body {
            font-family: 'K2D', sans-serif;
            background-color: #1a1a2e;
            background-image: radial-gradient(circle at 10% 20%, rgba(0, 107, 214, 0.1) 0%, rgba(107, 0, 214, 0.1) 90%);
            color: #fff;
            min-height: 100vh;
        }

        .lucky-draw-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 3rem 1rem;
            text-align: center;
        }

        .title-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            padding: 2rem;
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 3rem;
        }

        .title-card h1 {
            color: #ffc107;
            font-weight: 700;
            font-size: 2.5rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 10px;
        }

        .stats-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.1);
            padding: 5px 15px;
            border-radius: 50px;
            margin: 5px;
            font-size: 0.9rem;
        }

        .draw-box {
            background: rgba(255, 255, 255, 0.05);
            border: 2px dashed rgba(255, 255, 255, 0.2);
            border-radius: 30px;
            padding: 4rem 2rem;
            margin-bottom: 3rem;
            position: relative;
            overflow: hidden;
        }

        .digit-box {
            background-color: #222;
            color: #fff;
            width: 4rem;
            height: 6rem;
            font-size: 3.5rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            margin: 0 5px;
            box-shadow: inset 0 0 15px rgba(0,0,0,0.5);
            border: 2px solid #444;
            transition: all 0.1s;
        }

        @media (max-width: 768px) {
            .digit-box { width: 2.5rem; height: 4rem; font-size: 2rem; }
        }

        .digit-box.winner {
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            color: #000;
            border-color: #fff;
            box-shadow: 0 0 20px rgba(255, 193, 7, 0.4);
        }

        .btn-draw-main {
            background: linear-gradient(45deg, #ffc107, #ff9800);
            border: none;
            color: #000;
            padding: 1.5rem 5rem;
            font-size: 2rem;
            font-weight: 700;
            border-radius: 60px;
            box-shadow: 0 10px 30px rgba(255, 152, 0, 0.4);
            transition: all 0.3s;
            text-transform: uppercase;
            width: auto;
        }

        .btn-draw-main:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 15px 40px rgba(255, 152, 0, 0.6);
            background: linear-gradient(45deg, #ffeb3b, #ffc107);
        }

        .winner-display {
            display: none;
            animation: bounceIn 0.8s;
        }

        @keyframes bounceIn {
            0% { transform: scale(0.3); opacity: 0; }
            50% { transform: scale(1.05); }
            70% { transform: scale(0.9); }
            100% { transform: scale(1); opacity: 1; }
        }

        .winner-display h2 {
            color: #ffc107;
            font-size: 5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        @media (max-width: 768px) {
            .winner-display h2 { font-size: 2.5rem; }
        }

        .nav-buttons {
            position: absolute;
            top: 20px;
            left: 20px;
            z-index: 100;
        }
    </style>
</head>
<body>
    <div class="nav-buttons">
        <a href="admin.php" class="btn btn-outline-light btn-sm"><i class="bi bi-speedometer2"></i> แผงควบคุม</a>
    </div>

    <div class="lucky-draw-container">
        <div class="title-card">
            <h1><i class="bi bi-stars"></i> Lucky Draw System</h1>
            <p class="lead">ระบบสุ่มเลขนำโชค 6 หลัก สำหรับผู้ทำแบบประเมิน</p>
            <div>
                <span class="stats-badge">จำนวนผู้ร่วมกิจกรรม: <span id="totalParticipants">0</span></span>
                <span class="stats-badge">ผู้ได้รับรางวัลไปแล้ว: <span id="winnersCount">0</span></span>
                <button class="btn btn-outline-danger btn-sm rounded-pill ms-3" onclick="confirmResetLuckydraw()">
                    <i class="bi bi-trash3-fill"></i> รีเซ็ตตัวเลข/ชื่อทั้งหมด
                </button>
            </div>
        </div>

        <div class="draw-box shadow-lg">
            <div id="drawView">
                <h3 class="mb-4 text-white-50"><i class="bi bi-gear-fill"></i> หมุนวงล้อตัวเลข 6 หลัก</h3>
                
                <div class="d-flex justify-content-center gap-2 mb-5">
                    <div class="digit-box" id="d1">0</div>
                    <div class="digit-box" id="d2">0</div>
                    <div class="digit-box" id="d3">0</div>
                    <div class="digit-box" id="d4">0</div>
                    <div class="digit-box" id="d5">0</div>
                    <div class="digit-box" id="d6">0</div>
                </div>

                <div class="mt-4">
                    <button id="btnStart" class="btn btn-draw-main" onclick="startDraw()">
                        <i class="bi bi-play-circle-fill"></i> เริ่มหมุนวงล้อ
                    </button>
                    <div id="drawTimerDisplay" class="mt-3 text-white-50" style="display:none;">กำลังสุ่ม... กรุณารอสักครู่ (5 วินาที)</div>
                </div>
            </div>

            <div id="winnerView" class="winner-display text-center">
                <div class="mb-3">
                    <i class="bi bi-trophy-fill text-warning display-1"></i>
                </div>
                <h4 class="text-white-50">ยินดีด้วยกับเลขนำโชค!</h4>
                
                <div class="d-flex justify-content-center gap-2 mb-4">
                    <div class="digit-box winner" id="wd1">0</div>
                    <div class="digit-box winner" id="wd2">0</div>
                    <div class="digit-box winner" id="wd3">0</div>
                    <div class="digit-box winner" id="wd4">0</div>
                    <div class="digit-box winner" id="wd5">0</div>
                    <div class="digit-box winner" id="wd6">0</div>
                </div>

                <div id="winnerPhoneDisplay" class="text-white-50 fs-3 mb-2">---</div>

                <div id="winnerDetails" style="display:none;">
                    <h2 id="winnerName" class="text-warning display-4 mb-2">---</h2>
                </div>

                <div id="winnerDetailsHidden" class="mb-4">
                    <button id="btnReveal" class="btn btn-warning btn-lg px-5 rounded-pill shadow-lg" onclick="revealWinnerDetails()" style="font-size: 1.5rem;">
                        <i class="bi bi-person-bounding-box"></i> เฉลยชื่อผู้โชคดี!
                    </button>
                    <p class="mt-3 text-white-50">ใครเป็นเจ้าของเบอร์นี้? รอลุ้นชื่อกันเลย!</p>
                </div>
                
                <div class="mt-5 d-flex justify-content-center gap-3">
                    <button class="btn btn-success btn-lg px-5 rounded-pill shadow" onclick="saveCurrentWinner()">
                        <i class="bi bi-check-circle-fill"></i> บันทึกและสุ่มต่อ
                    </button>
                    <button class="btn btn-outline-light btn-lg px-5 rounded-pill" onclick="resetDraw()">
                        <i class="bi bi-arrow-counterclockwise"></i> ยกเลิก/สุ่มใหม่
                    </button>
                </div>
            </div>
        </div>

        <div class="title-card mt-5">
            <h2 class="text-warning mb-4"><i class="bi bi-list-check"></i> รายชื่อผู้ได้รับรางวัล</h2>
            <div id="winnersContainer">
                <!-- Winners list will be loaded here -->
                <p class='text-center text-muted p-4'>กำลังโหลดข้อมูลผู้ชนะ...</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let drawPool = [];
        let drawInterval = null;
        let currentWinner = null;
        let isDrawing = false;

        async function loadWinners() {
            try {
                const response = await fetch('api/get_winners.php');
                const result = await response.json();
                
                const container = document.getElementById('winnersContainer');
                if (result.success && result.data.length > 0) {
                    let html = `<div class="table-responsive">
                        <table class="table table-dark table-hover mb-0">
                            <thead>
                                <tr class="text-warning text-center">
                                    <th>#</th>
                                    <th>เลขนำโชค</th>
                                    <th>ชื่อ-นามสกุล</th>
                                    <th>เบอร์โทรศัพท์</th>
                                </tr>
                            </thead>
                            <tbody>`;
                    result.data.forEach((w, index) => {
                        html += `
                            <tr class="text-center">
                                <td>${result.data.length - index}</td>
                                <td><span class="badge bg-warning text-dark px-3 py-1 font-monospace fs-6">${w.lucky_code}</span></td>
                                <td class="fw-bold">${w.full_name}</td>
                                <td>${w.phone.substring(0, 3)}-XXX-${w.phone.substring(7)}</td>
                            </tr>
                        `;
                    });
                    html += `</tbody></table></div>`;
                    container.innerHTML = html;
                } else {
                    container.innerHTML = `<p class='text-center text-muted p-4'>ยังไม่มีข้อมูลผู้ชนะที่ถูกบันทึกไว้</p>`;
                }
            } catch (e) {
                console.error("Load winners error:", e);
            }
        }

        async function fetchPool() {
            try {
                const response = await fetch('api/get_lucky_draw_pool.php');
                const result = await response.json();
                if (result.success) {
                    drawPool = result.data;
                    document.getElementById('totalParticipants').innerText = result.total;
                    document.getElementById('winnersCount').innerText = result.winners;
                    loadWinners(); // Load winners list here when refreshing pool
                }
            } catch (e) { console.error(e); }
        }

        function startDraw() {
            if (drawPool.length === 0) {
                Swal.fire('ไม่มีรายชื่อ', 'ไม่พบรายชื่อผู้ที่ยังไม่ได้รับรางวัล', 'warning');
                return;
            }

            isDrawing = true;
            document.getElementById('btnStart').style.display = 'none';
            document.getElementById('drawTimerDisplay').style.display = 'block';

            drawInterval = setInterval(() => {
                for (let i = 1; i <= 6; i++) {
                    document.getElementById('d' + i).innerText = Math.floor(Math.random() * 10);
                }
            }, 60);

            // Auto stop after 5 seconds
            setTimeout(() => {
                stopDraw();
                document.getElementById('drawTimerDisplay').style.display = 'none';
            }, 5000);
        }

        function stopDraw() {
            if (!isDrawing) return;
            clearInterval(drawInterval);
            isDrawing = false;

            const winnerIdx = Math.floor(Math.random() * drawPool.length);
            currentWinner = drawPool[winnerIdx];

            // Immediately start reveal without resetting to '-'
            revealWinnerCode(currentWinner.lucky_code);
        }

        function revealWinnerCode(code) {
            const digits = code.toString().padStart(6, '0').split('');
            let revealedCount = 0;

            // Start individual spin intervals for boxes that haven't stopped yet
            const individualIntervals = [];
            for (let i = 1; i <= 6; i++) {
                const box = document.getElementById('d' + i);
                const interval = setInterval(() => {
                    box.innerText = Math.floor(Math.random() * 10);
                }, 50 + (i * 10)); // Variable speed for natural effect
                individualIntervals.push(interval);
            }

            // Reveal digits one by one
            const revealInterval = setInterval(() => {
                revealedCount++;
                const box = document.getElementById('d' + revealedCount);
                
                // Stop the individual spin for this box
                clearInterval(individualIntervals[revealedCount - 1]);
                
                // Set the real digit and mark as winner
                box.innerText = digits[revealedCount - 1];
                box.classList.add('winner');
                
                // If last digit is revealed
                if (revealedCount === 6) {
                    clearInterval(revealInterval);
                    setTimeout(showWinner, 1200);
                }
            }, 800); // Reveal each digit every 0.8s
        }

        function showWinner() {
            document.getElementById('drawView').style.display = 'none';
            document.getElementById('winnerView').style.display = 'block';
            
            // Hide real details first for suspense
            document.getElementById('winnerDetails').style.display = 'none';
            document.getElementById('winnerDetailsHidden').style.display = 'block';

            document.getElementById('winnerName').innerText = currentWinner.full_name;
            document.getElementById('winnerPhoneDisplay').innerText = currentWinner.phone.replace(/(\d{3})(\d{3})(\d{4})/, '$1-XXX-$3');
            
            const digits = currentWinner.lucky_code.toString().padStart(6, '0').split('');
            for (let i = 1; i <= 6; i++) {
                document.getElementById('wd' + i).innerText = digits[i - 1];
            }

            confetti({
                particleCount: 150,
                spread: 70,
                origin: { y: 0.6 }
            });
        }

        function revealWinnerDetails() {
            document.getElementById('winnerDetails').style.display = 'block';
            document.getElementById('winnerDetailsHidden').style.display = 'none';
            
            // Extra confetti on reveal!
            confetti({
                particleCount: 100,
                spread: 80,
                origin: { y: 0.7 },
                colors: ['#ffc107', '#ffffff']
            });
        }

        function resetDraw() {
            document.getElementById('drawView').style.display = 'block';
            document.getElementById('winnerView').style.display = 'none';
            document.getElementById('btnStart').style.display = 'inline-block';
            document.getElementById('btnStop').style.display = 'none';
            
            for (let i = 1; i <= 6; i++) {
                const box = document.getElementById('d' + i);
                box.innerText = '0';
                box.classList.remove('winner');
            }
            fetchPool();
        }

        async function saveCurrentWinner() {
            if (!currentWinner) return;

            const response = await fetch('api/save_winner.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${currentWinner.id}`
            });

            const result = await response.json();
            if (result.success) {
                Swal.fire({ icon: 'success', title: 'บันทึกสำเร็จ', timer: 1500, showConfirmButton: false });
                resetDraw();
            }
        }

        async function confirmResetLuckydraw() {
            const { isConfirmed } = await Swal.fire({
                title: 'ยืนยันการรีเซ็ต?',
                text: "รายชื่อผู้ร่วมสนุกและผู้ได้รับรางวัลทั้งหมดจะถูกลบออก เพื่อเริ่มงานใหม่ครั้งต่อไป!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'ใช่, รีเซ็ตเลย!',
                cancelButtonText: 'ยกเลิก'
            });

            if (isConfirmed) {
                try {
                    const formData = new FormData();
                    formData.append('action', 'reset_luckydraw');

                    const response = await fetch('api/reset_system.php', {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();

                    if (result.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'เรียบร้อย!',
                            text: result.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        throw new Error(result.message);
                    }
                } catch (error) {
                    Swal.fire('เกิดข้อผิดพลาด', error.message, 'error');
                }
            }
        }

        window.onload = fetchPool;
    </script>
</body>
</html>
