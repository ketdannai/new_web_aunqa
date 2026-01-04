<?php
// ไฟล์: teacher/delete_teacher.php
session_start();
require_once "../config.php"; 

// 1. ตรวจสอบการเข้าสู่ระบบเบื้องต้น
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login/login.php");
    exit;
}

// 2. รับค่า ID ที่ต้องการลบ (รับจาก GET ตามที่ปุ่มในหน้าหลักส่งมา)
if (isset($_GET['id'])) {
    $teac_id_to_delete = $_GET['id'];
    $logged_in_user_id = $_SESSION['use_id'];
    $user_role = $_SESSION['use_role'];

    // 3. เตรียมคำสั่ง SQL ตามสิทธิ์
    // ถ้าเป็น admin ลบโดยอ้างอิง teac_id อย่างเดียว
    // ถ้าเป็น user ต้องระบุทั้ง teac_id และ use_id (ของตัวเองเท่านั้น)
    if ($user_role === 'admin') {
        $sql = "DELETE FROM teachers WHERE teac_id = ?";
    } else {
        $sql = "DELETE FROM teachers WHERE teac_id = ? AND use_id = ?";
    }

    if ($stmt = $link->prepare($sql)) {
        if ($user_role === 'admin') {
            $stmt->bind_param("i", $teac_id_to_delete);
        } else {
            $stmt->bind_param("ii", $teac_id_to_delete, $logged_in_user_id);
        }

        // 4. ทำการประหารคำสั่งลบ
        if ($stmt->execute()) {
            // ตรวจสอบว่ามีการลบจริงหรือไม่ (เผื่อกรณี User พยายามแอบลบ ID คนอื่น SQL จะไม่ Error แต่จะลบไม่ได้)
            if ($stmt->affected_rows > 0) {
                $_SESSION["teacher_success"] = "ลบข้อมูลอาจารย์เรียบร้อยแล้ว";
            } else {
                $_SESSION["teacher_error"] = "ไม่พบข้อมูล หรือคุณไม่มีสิทธิ์ลบข้อมูลนี้";
            }
        } else {
            $_SESSION["teacher_error"] = "เกิดข้อผิดพลาด: " . $link->error;
        }
        $stmt->close();
    }
}

// ปิดการเชื่อมต่อและกลับหน้าหลัก
$link->close();
header("location: teacher.php");
exit;