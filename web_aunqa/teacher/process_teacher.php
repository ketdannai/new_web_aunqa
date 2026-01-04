<?php
// เริ่มต้น Session
session_start();

// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบอยู่หรือไม่
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    $_SESSION["teacher_error"] = "กรุณาเข้าสู่ระบบก่อนดำเนินการ";
    header("location: ../login/login.php"); 
    exit;
}

// นำเข้าไฟล์เชื่อมต่อฐานข้อมูล
require_once "../config.php"; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. รับและทำความสะอาดข้อมูลที่ส่งมาจากฟอร์ม
    $use_id = $_SESSION["use_id"] ?? 0; 
    
    // *** แก้ไข Warning Undefined array key "teac_id" ที่นี่ ***
    // ดึงค่า teac_id_existing จากฟอร์ม
    $teac_id_existing = trim($_POST["teac_id_existing"] ?? ''); 
    
    $teac_position = trim($_POST["teac_position"] ?? '');
    $teac_status = trim($_POST["teac_status"] ?? '');
    $teac_qualification = trim($_POST["teac_qualification"] ?? '');
    
    // *** ชื่อตัวแปรต้องตรงกับชื่อฟิลด์ในฟอร์ม: teac_gradute ***
    $teac_gradute = trim($_POST["teac_gradute"] ?? ''); 
    $teac_branch = trim($_POST["teac_branch"] ?? '');

    // ตรวจสอบความถูกต้องเบื้องต้น
    if ($use_id == 0 || empty($teac_position) || empty($teac_status) || empty($teac_qualification) || empty($teac_gradute) || empty($teac_branch)) {
        $_SESSION["teacher_error"] = "ข้อมูลไม่สมบูรณ์ หรือ User ID ไม่ถูกต้อง กรุณากรอกข้อมูลให้ครบถ้วน";
        header("location: teacher.php");
        exit;
    }

    $link_ok = isset($link) && $link !== false;

    // 2. ตรวจสอบว่าเป็นการ INSERT หรือ UPDATE
    if (!empty($teac_id_existing) && $link_ok) {
        // *********************
        // 2.1 UPDATE ข้อมูลที่มีอยู่ 
        // *********************
        // *** แก้ไข Fatal Error: เปลี่ยนชื่อคอลัมน์ teac_gradute ให้ถูกต้องตาม DB ***
        $sql = "UPDATE teachers SET teac_position=?, teac_status=?, teac_qualification=?, teac_gradute=?, teac_branch=? WHERE teac_id=? AND use_id=?";
        
        if ($stmt = mysqli_prepare($link, $sql)) {
            // s s s s s s i (position, status, qualification, gradute, branch, teac_id, use_id)
            mysqli_stmt_bind_param($stmt, "ssssssi", $teac_position, $teac_status, $teac_qualification, $teac_gradute, $teac_branch, $teac_id_existing, $use_id);
            
            if (mysqli_stmt_execute($stmt)) {
                if (mysqli_stmt_affected_rows($stmt) > 0) {
                    $_SESSION["teacher_success"] = "แก้ไขข้อมูลอาจารย์สำเร็จแล้ว";
                } else {
                    $_SESSION["teacher_error"] = "แก้ไขข้อมูลไม่สำเร็จ: ไม่พบข้อมูลอาจารย์ที่ตรงกับ User ID นี้ หรือไม่มีการเปลี่ยนแปลงข้อมูล";
                }
            } else {
                $_SESSION["teacher_error"] = "แก้ไขข้อมูลไม่สำเร็จ: " . mysqli_error($link);
            }
            mysqli_stmt_close($stmt);

        } else {
            $_SESSION["teacher_error"] = "เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL (Update)";
        }
        
    } elseif ($link_ok) {
        // *********************
        // 2.2 INSERT ข้อมูลใหม่
        // *********************
        // *** แก้ไข Fatal Error: เปลี่ยนชื่อคอลัมน์ teac_gradute ให้ถูกต้องตาม DB ***
        $sql = "INSERT INTO teachers (use_id, teac_position, teac_status, teac_qualification, teac_gradute, teac_branch) VALUES (?, ?, ?, ?, ?, ?)";

        if ($stmt = mysqli_prepare($link, $sql)) { // <--- บรรทัดที่ 83 อาจจะอยู่แถวนี้
            // i s s s s s (use_id, position, status, qualification, gradute, branch)
            mysqli_stmt_bind_param($stmt, "isssss", $use_id, $teac_position, $teac_status, $teac_qualification, $teac_gradute, $teac_branch);
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION["teacher_success"] = "บันทึกข้อมูลอาจารย์ใหม่สำเร็จแล้ว (รหัส: " . mysqli_insert_id($link) . ")";
            } else {
                $_SESSION["teacher_error"] = "บันทึกข้อมูลไม่สำเร็จ: ข้อมูลอาจารย์สำหรับ User นี้อาจมีอยู่แล้ว หรือมีข้อผิดพลาดทางเทคนิค: " . mysqli_error($link);
            }
            mysqli_stmt_close($stmt);

        } else {
             $_SESSION["teacher_error"] = "เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL (Insert)";
        }
    } else {
         $_SESSION["teacher_error"] = "ERROR: ไม่สามารถเชื่อมต่อฐานข้อมูลได้";
    }
    
} else {
    $_SESSION["teacher_error"] = "เกิดข้อผิดพลาดในการส่งข้อมูล (ไม่ใช่ POST)";
}

// Redirect กลับไปหน้า teacher.php
header("location: teacher.php");
exit;
?>