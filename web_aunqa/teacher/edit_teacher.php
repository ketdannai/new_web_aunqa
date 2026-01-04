<?php
// ไฟล์: E:\xampp\htdocs\web_aunqa\teacher\edit_teacher.php

session_start();
require_once "../config.php"; // ไฟล์เชื่อมต่อฐานข้อมูล

// 1. ตรวจสอบการเข้าสู่ระบบ
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: ../login/login.php");
    exit;
}

// 2. ดึงข้อมูลผู้ใช้ปัจจุบันและกำหนดสิทธิ์
$logged_in_user_id = $_SESSION["use_id"] ?? null;
$user_role = $_SESSION["use_role"] ?? 'user';
$is_admin = ($user_role == 'admin'); 

// 3. กำหนดตัวแปรและดึง teac_id ที่ต้องการแก้ไข
$teacher_id_to_edit = $_GET['id'] ?? null;
$teacher_data = null;
$user_data = null;
$error_message = null;
$access_denied = false;

if (!$teacher_id_to_edit) {
    // ถ้าไม่มี ID ส่งมา
    $error_message = "ไม่พบรหัสข้อมูลอาจารย์ที่ต้องการแก้ไข";
    $access_denied = true;
} else {
    // 4. ดึงข้อมูลอาจารย์และผู้ใช้ที่เกี่ยวข้อง
    $sql = "SELECT 
                t.*, /* ดึงข้อมูลทั้งหมดจากตาราง teachers */
                u.use_title, 
                u.use_fname, 
                u.use_lname /* ดึงข้อมูลชื่อจากตาราง users */
            FROM 
                teachers t
            INNER JOIN 
                users u ON t.use_id = u.use_id
            WHERE 
                t.teac_id = ?";
    
    if ($stmt = $link->prepare($sql)) {
        $stmt->bind_param("i", $teacher_id_to_edit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $teacher_data = $result->fetch_assoc();
            
            // 5. ตรวจสอบสิทธิ์การเข้าถึง/แก้ไข
            $owner_user_id = $teacher_data['use_id'];
            $is_owner = ($owner_user_id == $logged_in_user_id);
            
            if (!$is_admin && !$is_owner) {
                // ถ้าไม่ใช่ Admin และไม่ใช่เจ้าของข้อมูล
                $error_message = "คุณไม่มีสิทธิ์แก้ไขข้อมูลอาจารย์ท่านนี้";
                $access_denied = true;
            }
        } else {
            $error_message = "ไม่พบข้อมูลอาจารย์ ID: " . $teacher_id_to_edit;
            $access_denied = true;
        }
        $stmt->close();
    } else {
        $error_message = "เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL: " . $link->error;
        $access_denied = true;
    }
}
@$link->close(); // ปิดการเชื่อมต่อ DB

// ดึงข้อมูลผู้ใช้จาก Session สำหรับ Header
$title = htmlspecialchars($_SESSION["use_title"] ?? '');
$fname = htmlspecialchars($_SESSION["use_fname"] ?? '');
$lname = htmlspecialchars($_SESSION["use_lname"] ?? '');
$current_page = basename(__FILE__); 

function is_active($target_file) {
    return (basename($_SERVER['PHP_SELF']) == $target_file) ? 'active' : '';
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขข้อมูลอาจารย์ | AUN-QA System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        /* CSS จากโค้ด Dark/Layout Matching */
        :root { /* ... โค้ด CSS variables ที่คุณมี ... */ }
        body { font-family: 'Kanit', sans-serif; background-color: #222222; }
        .main-container { min-height: 100vh; display: flex; flex-direction: column; }
        /* ... โค้ด CSS อื่นๆ ที่เกี่ยวข้องกับ Layout ... */
        .content { flex-grow: 1; padding: 40px; background-color: #ffffff; color: #343a40; box-shadow: -5px 0 10px rgba(0,0,0,0.1); }
        .content h1 { color: #007bff; font-weight: 700; }
        .content h5 { color: #6c757d; }
    </style>
</head>
<body>

<div class="main-container">
    
    <div class="main-header">
        <div class="header-top">
            <p class="header-welcome">ยินดีต้อนรับ</p>
            <a href="../login/logout.php" class="btn btn-sm btn-logout">logout</a>
        </div>
    </div>

    <div class="content-area">
        
        <?php // ส่วน Sidebar ที่คุณมี ?>

        <div class="content">
            <h1>แก้ไขข้อมูลอาจารย์</h1>
            <h5>ID: <?php echo htmlspecialchars($teacher_id_to_edit); ?> | ข้อมูลของ: <?php echo htmlspecialchars($teacher_data['use_fname'] ?? 'ไม่พบ'); ?></h5>
            
            <?php 
            // แสดงข้อความแจ้งเตือน Error
            if ($error_message): ?>
                <div class="alert alert-danger" role="alert"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <?php if ($teacher_data && !$access_denied): ?>
            <div class="card shadow-lg p-4">
                <div class="card-body">
                    
                    <form action="update_teacher.php" method="POST">
                        <input type="hidden" name="teac_id" value="<?php echo htmlspecialchars($teacher_data['teac_id']); ?>">
                        <input type="hidden" name="use_id" value="<?php echo htmlspecialchars($teacher_data['use_id']); ?>">
                        
                        <h4 class="mb-4">ข้อมูลผู้ใช้งาน (Admin เท่านั้นที่ควรแก้ไขชื่อ-สกุลได้)</h4>
                        <div class="row mb-4">
                            <div class="col-md-3 mb-3">
                                <label for="use_title" class="form-label">คำนำหน้าชื่อ</label>
                                <input type="text" class="form-control" id="use_title" name="use_title" 
                                       value="<?php echo htmlspecialchars($teacher_data['use_title']); ?>" 
                                       <?php echo $is_admin ? '' : 'disabled'; ?> required>
                            </div>
                            <div class="col-md-5 mb-3">
                                <label for="use_fname" class="form-label">ชื่อ</label>
                                <input type="text" class="form-control" id="use_fname" name="use_fname" 
                                       value="<?php echo htmlspecialchars($teacher_data['use_fname']); ?>" 
                                       <?php echo $is_admin ? '' : 'disabled'; ?> required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="use_lname" class="form-label">นามสกุล</label>
                                <input type="text" class="form-control" id="use_lname" name="use_lname" 
                                       value="<?php echo htmlspecialchars($teacher_data['use_lname']); ?>" 
                                       <?php echo $is_admin ? '' : 'disabled'; ?> required>
                            </div>
                        </div>

                        <h4 class="mb-4">ข้อมูลอาจารย์ (Admin และเจ้าของข้อมูลแก้ไขได้)</h4>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="teac_position" class="form-label">ตำแหน่งทางวิชาการ</label>
                                <input type="text" class="form-control" id="teac_position" name="teac_position" 
                                       value="<?php echo htmlspecialchars($teacher_data['teac_position']); ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="teac_qualification" class="form-label">วุฒิการศึกษาสูงสุด</label>
                                <input type="text" class="form-control" id="teac_qualification" name="teac_qualification" 
                                       value="<?php echo htmlspecialchars($teacher_data['teac_qualification']); ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="teac_branch" class="form-label">สาขา/ความเชี่ยวชาญ</label>
                                <input type="text" class="form-control" id="teac_branch" name="teac_branch" 
                                       value="<?php echo htmlspecialchars($teacher_data['teac_branch']); ?>" required>
                            </div>
                             <div class="col-md-4 mb-3">
                                <label for="teac_status" class="form-label">สถานะ</label>
                                <input type="text" class="form-control" id="teac_status" name="teac_status" 
                                       value="<?php echo htmlspecialchars($teacher_data['teac_status']); ?>" required>
                            </div>
                        </div>

                        <div class="mt-4 text-end">
                            <a href="teacher.php" class="btn btn-secondary me-2">ยกเลิก</a>
                            <button type="submit" class="btn btn-primary btn-lg">บันทึกการแก้ไข</button>
                        </div>
                    </form>

                </div>
            </div>
            <?php endif; ?>
            
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>