<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Password Hash</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=K2D:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'K2D', sans-serif;
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .container {
            background-color: #ffffff;
            padding: 2.5rem;
            border-radius: 0.8rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
        }
        h2 {
            margin-bottom: 1.5rem;
            color: #0d6efd;
            text-align: center;
            font-weight: 700;
        }
        .form-label {
            font-weight: 600;
        }
        .btn-primary {
            width: 100%;
            padding: 0.75rem;
            font-size: 1.1rem;
            font-weight: 700;
        }
        .result-box {
            background-color: #e9ecef;
            padding: 1rem;
            border-radius: 0.3rem;
            word-break: break-all;
            font-family: 'monospace';
            margin-top: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>สร้าง Password Hash</h2>
        <form method="POST">
            <div class="mb-3">
                <label for="password" class="form-label">รหัสผ่านที่ต้องการสร้าง Hash</label>
                <input type="text" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">สร้าง Hash</button>
        </form>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
            $password = $_POST['password'];
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            echo '<div class="result-box">';
            echo '<strong>Password Hash ที่สร้าง:</strong><br>';
            echo htmlspecialchars($hashed_password);
            echo '</div>';
        }
        ?>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
