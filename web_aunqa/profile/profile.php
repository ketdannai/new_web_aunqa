<?php
// ไฟล์: profile/profile.php
session_start();
require_once "../config.php";

// 1. ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login/login.php");
    exit;
}

// 2. ดึงข้อมูลจาก Session มาแสดงผล
$title = htmlspecialchars($_SESSION["use_title"] ?? '');
$fname = htmlspecialchars($_SESSION["use_fname"] ?? '');
$lname = htmlspecialchars($_SESSION["use_lname"] ?? '');
$user_role = htmlspecialchars($_SESSION["use_role"] ?? 'user');
$full_name = $_SESSION["use_title"] . $_SESSION["use_fname"] . " " . $_SESSION["use_lname"];

// 3. ดึงข้อความแจ้งเตือน
$success_message = $_SESSION["profile_success"] ?? null;
$error_message = $_SESSION["profile_error"] ?? null;
unset($_SESSION["profile_success"], $_SESSION["profile_error"]);

// ฟังก์ชันสำหรับเมนู Active
function is_active($target_file) {
    return (basename($_SERVER['PHP_SELF']) == $target_file) ? 'active' : '';
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ข้อมูลส่วนตัว | AUN-QA System Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --bg-dark: #222222;
            --bg-content: #ffffff;
            --accent-blue: #007bff;
            --sidebar-link-bg: #343a40;
            --sidebar-active: #cce0ff;
            --text-light: #f8f9fa;
        }

        body { 
            font-family: 'Kanit', sans-serif; 
            background-color: var(--bg-dark); 
            margin: 0; 
        }

        .main-container { min-height: 100vh; display: flex; flex-direction: column; }

        /* Header Style */
        .main-header { 
            background-color: var(--accent-blue); 
            color: white; 
            padding: 15px 20px; 
            box-shadow: 0 2px 4px rgba(0,0,0,0.2); 
            font-weight: 600; 
        }
        .header-top { display: flex; justify-content: space-between; align-items: center; }
        .btn-logout { background-color: #f8f9fa; color: #212529; border: none; font-weight: 600; padding: 5px 15px; border-radius: 3px; text-decoration: none; }

        /* Sidebar Style */
        .content-area { display: flex; flex-grow: 1; }
        .sidebar { width: 250px; background-color: var(--bg-dark); padding: 0; flex-shrink: 0; }
        .sidebar .nav-link { 
            color: var(--text-light); padding: 12px 15px; margin-bottom: 1px; font-size: 1.05rem; 
            font-weight: 400; transition: background-color 0.2s;
            background-color: var(--sidebar-link-bg); box-shadow: 1px 0 0 rgba(0, 0, 0, 0.2) inset, 0 1px 0 rgba(0, 0, 0, 0.2); 
            text-decoration: none; display: block;
        }
        .sidebar .nav-link:hover { background-color: #495057; color: white; }
        .sidebar .nav-link.active { background-color: var(--sidebar-active); color: #212529; font-weight: 600; }

        /* Content Area Style */
        .content { flex-grow: 1; padding: 40px; background-color: var(--bg-content); color: #343a40; box-shadow: -5px 0 10px rgba(0,0,0,0.1); min-height: 100vh; }
        .content h1 { 
            color: var(--accent-blue); 
            font-weight: 700; 
            font-size: 2rem; 
            margin-bottom: 5px; 
        }
        
        .card { border-radius: 12px; border: none; }
        .form-label { font-weight: 600; color: #555; }
        .btn-update { background-color: #0056b3; border: none; font-weight: 600; padding: 10px 30px; }
        .btn-update:hover { background-color: #004494; }
    </style>
</head>
<body>

<div class="main-container">
    <div class="main-header">
        <div class="header-top">
            <p class="mb-0">ยินดีต้อนรับ: <?php echo $full_name; ?></p>
            <a href="../login/logout.php" class="btn btn-sm btn-logout">logout</a>
        </div>
    </div>

    <div class="content-area">
        <div class="sidebar">
            <div class="nav flex-column">
                <a class="nav-link" href="../dashboard.php">หน้าแรก</a>
                <a class="nav-link active" href="profile.php">ข้อมูลส่วนตัว</a>
                <a class="nav-link" href="../teacher/teacher.php">อาจารย์</a>
                <a class="nav-link" href="../course/course.php">รายวิชา</a>
                <a class="nav-link" href="../opencourse/opencourse.php">รายวิชาเปิด</a>
                <a class="nav-link" href="../section/section.php">กลุ่มเรียน</a>
                <a class="nav-link" href="../article/article.php">บทความ</a>
                <a class="nav-link" href="../research/research.php">วิจัย</a>
                <a class="nav-link" href="../development/development.php">พัฒนานักศึกษา</a>
                <a class="nav-link" href="../plo/plo.php">PLO</a>
                <a class="nav-link" href="../clo/clo.php">CLO</a>
                <a class="nav-link" href="../services/services.php">งานบริการวิชาการ</a>
                <a class="nav-link" href="../laboratory/laboratory.php">ห้องปฏิบัติการ</a>
               <?php
                    // ตรวจสอบว่า Session 'use_role' ถูกตั้งค่าเป็น 'admin' หรือไม่
                    if (isset($_SESSION["use_role"]) && $_SESSION["use_role"] == 'admin'):
                    ?>
                        <a class="nav-link" href="../manage_users.php">
                            <i class="bi bi-people-fill me-2"></i> จัดการผู้ใช้งาน
                        </a>
                    <?php endif; ?>
            </div>
        </div>

        <div class="content">
            <h1>ข้อมูลส่วนตัว</h1>

            <?php if ($success_message): ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> <?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow-lg p-4 border-0">
                <div class="card-body">
                    <form action="update_profile.php" method="POST">
                        <div class="row g-4 mb-4">
                            <div class="col-md-3">
                                <label class="form-label">คำนำ</label>
                                <input type="text" name="use_title" class="form-control form-control-lg" value="<?php echo $title; ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">ชื่อจริง</label>
                                <input type="text" name="use_fname" class="form-control form-control-lg" value="<?php echo $fname; ?>" required>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">นามสกุล</label>
                                <input type="text" name="use_lname" class="form-control form-control-lg" value="<?php echo $lname; ?>" required>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-5 p-3 bg-light rounded-3 border">
                            <div>
                                <span class="fw-bold text-muted me-2">ระดับผู้ใช้งาน:</span>
                                <span class="badge bg-primary px-3 py-2" style="font-size: 0.9rem;"><?php echo strtoupper($user_role); ?></span>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg px-5 shadow-sm btn-update">
                                <i class="bi bi-save me-2"></i> บันทึกการแก้ไข
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>