<?php
// ไฟล์: teacher/update_teacher.php
session_start();
require_once "../config.php";

// 1. ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login/login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $teac_id = $_POST['teac_id'];
    $logged_in_user_id = $_SESSION['use_id'];
    $user_role = $_SESSION['use_role'];

    // รับค่าและตัดช่องว่างหัวท้าย
    $teac_position = trim($_POST['teac_position']);
    $teac_qualification = trim($_POST['teac_qualification']);
    $teac_branch = trim($_POST['teac_branch']);
    $teac_status = trim($_POST['teac_status']);

    // 2. ตรวจสอบสิทธิ์ (Security Check)
    // ค้นหาเจ้าของข้อมูลก่อน
    $owner_id = null;
    $sql_check = "SELECT use_id FROM teachers WHERE teac_id = ?";
    if ($stmt_check = $link->prepare($sql_check)) {
        $stmt_check->bind_param("i", $teac_id);
        $stmt_check->execute();
        $stmt_check->bind_result($owner_id);
        $stmt_check->fetch();
        $stmt_check->close();
    }

    // เงื่อนไข: ต้องเป็น Admin หรือ เป็นเจ้าของข้อมูลเท่านั้น
    if ($user_role == 'admin' || ($owner_id !== null && $owner_id == $logged_in_user_id)) {
        
        // 3. เริ่มทำการ Update ข้อมูล
        // หากเป็น Admin จะอัปเดตด้วย teac_id อย่างเดียว 
        // หากเป็น User จะอัปเดตด้วย teac_id และต้องเช็ค use_id ซ้ำเพื่อความปลอดภัยสูงสุด
        if ($user_role == 'admin') {
            $sql = "UPDATE teachers SET 
                    teac_position = ?, 
                    teac_qualification = ?, 
                    teac_branch = ?, 
                    teac_status = ? 
                    WHERE teac_id = ?";
        } else {
            $sql = "UPDATE teachers SET 
                    teac_position = ?, 
                    teac_qualification = ?, 
                    teac_branch = ?, 
                    teac_status = ? 
                    WHERE teac_id = ? AND use_id = ?";
        }

        if ($stmt = $link->prepare($sql)) {
            if ($user_role == 'admin') {
                $stmt->bind_param("ssssi", $teac_position, $teac_qualification, $teac_branch, $teac_status, $teac_id);
            } else {
                $stmt->bind_param("ssssii", $teac_position, $teac_qualification, $teac_branch, $teac_status, $teac_id, $logged_in_user_id);
            }

            if ($stmt->execute()) {
                $_SESSION["teacher_success"] = "แก้ไขข้อมูลอาจารย์เรียบร้อยแล้ว";
            } else {
                $_SESSION["teacher_error"] = "ไม่สามารถแก้ไขข้อมูลได้: " . $stmt->error;
            }
            $stmt->close();
        }
    } else {
        $_SESSION["teacher_error"] = "คุณไม่มีสิทธิ์แก้ไขข้อมูลนี้";
    }

    $link->close();
    header("location: teacher.php");
    exit;
}