<?php
// เริ่มต้น Session
session_start();
 
// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบอยู่หรือไม่
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// สร้างชื่อเต็ม
$full_name = htmlspecialchars($_SESSION["use_title"]) . htmlspecialchars($_SESSION["use_fname"]) . " " . htmlspecialchars($_SESSION["use_lname"]);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>หน้าต้อนรับ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Kanit', sans-serif; background-color: #f4f6f9; min-height: 100vh; }
        .navbar { background-color: #007bff; }
        .welcome-card { max-width: 600px; margin-top: 50px; padding: 30px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); background-color: #ffffff; }
        .role-badge { font-size: 1rem; padding: 0.5em 1em; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container">
        <a class="navbar-brand" href="../index.php">AUN-QA SYSTEM</a>
        <div class="ms-auto">
            <span class="navbar-text me-3 text-white">
                สวัสดี, <strong><?php echo $full_name; ?></strong>
            </span>
            <a href="logout.php" class="btn btn-warning">ออกจากระบบ (Logout)</a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="welcome-card text-center mx-auto">
                <h1 class="mb-4 text-primary">🎉 ยินดีต้อนรับเข้าสู่ระบบ!</h1>
                <h3 class="mb-3">
                    ผู้ใช้งาน: **<?php echo $full_name; ?>**
                </h3>
                
                <p class="lead">
                    สถานะผู้ใช้งานของคุณ:
                </p>
                <span class="badge <?php echo ($_SESSION["use_role"] == 'admin') ? 'bg-danger' : 'bg-success'; ?> role-badge mb-4">
                    <?php echo strtoupper(htmlspecialchars($_SESSION["use_role"])); ?>
                </span>

                <hr>
                
                <a href="../index.php" class="btn btn-outline-primary me-2">
                    กลับหน้าหลัก
                </a>
                
                <a href="logout.php" class="btn btn-danger">
                    ออกจากระบบ
                </a>
            </div>
        </div>
    </div>
</div>
</body>
</html>