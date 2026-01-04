<?php
// เริ่มต้น Session
session_start();

// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบอยู่หรือไม่
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login/login.php"); // ถ้ายังไม่ Login ให้กลับไปหน้า Login
    exit;
}

// สร้างชื่อเต็มสำหรับแสดงผล
$full_name = htmlspecialchars($_SESSION["use_title"]) . htmlspecialchars($_SESSION["use_fname"]) . " " . htmlspecialchars($_SESSION["use_lname"]);
$user_role = htmlspecialchars($_SESSION["use_role"]);

// กำหนดชื่อหน้าที่ Active เพื่อเน้นเมนู
$current_page = basename($_SERVER['PHP_SELF']);

// ฟังก์ชันสำหรับแสดง Active Class
function is_active($target_file)
{
    // ใช้ basename เพื่อให้สามารถเปรียบเทียบชื่อไฟล์ได้ตรงกัน (เช่น 'dashboard.php')
    return (basename($_SERVER['PHP_SELF']) == $target_file) ? 'active' : '';
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>หน้าหลัก | AUN-QA System Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        /* CSS จากโค้ด Dark/Layout Matching */
        :root {
            --bg-dark: #222222;
            /* พื้นหลัง Sidebar และ Body หลัก */
            --bg-content: #ffffff;
            /* พื้นหลัง Content เป็นสีขาวเพื่อให้ตัดกันเหมือนในรูป */
            --accent-blue: #007bff;
            /* สีน้ำเงินสำหรับการเน้น (Active, Header) */
            --sidebar-link-bg: #343a40;
            /* พื้นหลังเมนูที่ไม่ได้เลือก */
            --sidebar-active: #cce0ff;
            /* สีฟ้าอ่อนสำหรับลิงก์เมนูที่เลือก */
            --text-light: #f8f9fa;
        }

        body {
            font-family: 'Kanit', sans-serif;
            background-color: var(--bg-dark);
        }

        .main-container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* 1. Header Bar */
        .main-header {
            background-color: var(--accent-blue);
            color: white;
            padding: 15px 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            font-weight: 600;
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-welcome {
            font-size: 1.1rem;
            margin-bottom: 0;
        }

        .btn-logout {
            background-color: #f8f9fa;
            color: #212529;
            border: none;
            font-weight: 600;
            padding: 5px 15px;
            border-radius: 3px;
        }

        /* Layout Area */
        .content-area {
            display: flex;
            flex-grow: 1;
        }

        /* 2. Sidebar (Dark Background, Light Text) */
        .sidebar {
            width: 250px;
            background-color: var(--bg-dark);
            padding: 0;
            flex-shrink: 0;
        }

        .sidebar .nav-link {
            color: var(--text-light);
            padding: 12px 15px;
            margin-bottom: 1px;
            font-size: 1.05rem;
            transition: background-color 0.2s;
            background-color: var(--sidebar-link-bg);
            box-shadow: 1px 0 0 rgba(0, 0, 0, 0.2) inset, 0 1px 0 rgba(0, 0, 0, 0.2);
        }

        .sidebar .nav-link:hover {
            background-color: #495057;
            color: white;
        }

        /* Active Link (สีฟ้าอ่อน) */
        .sidebar .nav-link.active {
            background-color: var(--sidebar-active);
            color: #212529;
            font-weight: 600;
            box-shadow: 1px 0 0 rgba(0, 0, 0, 0.2) inset, 0 1px 0 rgba(0, 0, 0, 0.2);
        }

        /* 3. Content Area (White Background) */
        .content {
            flex-grow: 1;
            padding: 40px;
            background-color: var(--bg-content);
            color: #343a40;
            box-shadow: -5px 0 10px rgba(0, 0, 0, 0.1);
        }

        .content h1 {
            color: var(--accent-blue);
            font-weight: 700;
            font-size: 2rem;
            margin-bottom: 5px;
        }

        .content h5 {
            color: #6c757d;
            font-size: 1rem;
            margin-bottom: 30px;
        }

        .info-box {
            background-color: #e9f5ff;
            border-left: 5px solid var(--accent-blue);
            padding: 20px;
            border-radius: 5px;
            line-height: 1.8;
            font-size: 1.05rem;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>

<body>

    <div class="main-container">

       <div class="main-header">
    <div class="header-top">
        <p class="mb-0">ยินดีต้อนรับ: <?php echo $full_name; ?></p>
        <a href="login/logout.php" class="btn btn-sm btn-logout">logout</a>
    </div>
</div>
        <div class="content-area">
            <div class="sidebar">
                <div class="nav-flex-column">

                    <a class="nav-link <?php echo is_active('dashboard.php'); ?>" href="dashboard.php">หน้าแรก</a>
                    <a class="nav-link" href="profile/profile.php">ข้อมูลส่วนตัว</a>
                    <a class="nav-link" href="teacher/teacher.php">อาจารย์</a>
                    <a class="nav-link" href="course/course.php">รายวิชา</a>
                    <a class="nav-link" href="opencourse/opencourse.php">รายวิชาเปิด</a>

                    <a class="nav-link" href="section/section.php">กลุ่มเรียน</a>

                    <a class="nav-link" href="article/article.php">บทความ</a>
                    <a class="nav-link" href="research/research.php">วิจัย</a>
                    <a class="nav-link" href="development/development.php">พัฒนานักศึกษา</a>
                    <a class="nav-link" href="plo/plo.php">PLO</a>
                    <a class="nav-link" href="clo/clo.php">CLO</a>
                    <a class="nav-link" href="services/services.php">งานบริการวิชาการ</a>
                    <a class="nav-link" href="laboratory/laboratory.php">ห้องปฏิบัติการ</a>

                    <?php
                    // ตรวจสอบว่า Session 'use_role' ถูกตั้งค่าเป็น 'admin' หรือไม่
                    if (isset($_SESSION["use_role"]) && $_SESSION["use_role"] == 'admin'):
                    ?>
                        <a class="nav-link" href="manage_users.php">
                            <i class="bi bi-people-fill me-2"></i> จัดการผู้ใช้งาน
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="content">
                <h1>ยินดีต้อนรับเข้าสู่เว็บไซต์</h1>
                <h5>ระบบสารสนเทศ เพื่อการจัดการข้อมูลการประกันคุณภาพหลักสูตร AUN</h5>

                <div class="info-box">
                    <p>
                        เว็บไซต์ระบบสารสนเทศเพื่อการจัดการข้อมูลการประกันคุณภาพหลักสูตร AUN-QA
                        จัดทำขึ้นเพื่อเป็นเครื่องมือสนับสนุนการดำเนินงานด้านการประกันคุณภาพการศึกษาในระดับหลักสูตร
                        ให้มีประสิทธิภาพ โปร่งใส และตรวจสอบได้ โดยระบบจะช่วยรวบรวม จัดเก็บ
                        และบริหารจัดการข้อมูลที่เกี่ยวข้องกับเกณฑ์ AUN-QA อย่างเป็นระบบ
                        เพื่อให้คณาจารย์ บุคลากร และผู้บริหารสามารถเข้าถึงข้อมูล วิเคราะห์
                        และติดตามผลการประเมินคุณภาพได้อย่างสะดวกและรวดเร็ว
                        อันจะนำไปสู่การพัฒนาคุณภาพหลักสูตรอย่างต่อเนื่องและยั่งยืน
                    </p>
                </div>

            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>