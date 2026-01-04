<?php
// ไฟล์: E:\xampp\htdocs\web_aunqa\delete_user.php

session_start();
 
// ตรวจสอบสิทธิ์ Admin 
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["use_role"] !== 'admin'){
    header("location: dashboard.php");
    exit;
}

// นำเข้าไฟล์เชื่อมต่อฐานข้อมูล
require_once "config.php"; 

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $user_id_to_delete = $_POST['id'];

    if ($link === false) {
        $_SESSION["manage_users_error"] = "ERROR: ไม่สามารถเชื่อมต่อฐานข้อมูลได้";
    } else {
        // ป้องกัน Admin ลบบัญชีตัวเอง
        if ($user_id_to_delete == $_SESSION['use_id']) {
            $_SESSION["manage_users_error"] = "ไม่สามารถลบบัญชีผู้ดูแลระบบของคุณเองได้";
        } else {
            // เตรียมคำสั่ง SQL สำหรับการลบ
            $sql = "DELETE FROM users WHERE use_id = ?";
            
            if ($stmt = $link->prepare($sql)) {
                $stmt->bind_param("i", $user_id_to_delete);
                
                if ($stmt->execute()) {
                    $_SESSION["manage_users_success"] = "ลบผู้ใช้ ID: " . $user_id_to_delete . " สำเร็จแล้ว";
                } else {
                    $_SESSION["manage_users_error"] = "เกิดข้อผิดพลาดในการลบ: " . $stmt->error;
                }
                
                $stmt->close();
            } else {
                $_SESSION["manage_users_error"] = "เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL";
            }
        }
    }
    
    if (isset($link) && $link !== false) {
        $link->close();
    }
    header("location: manage_users.php");
    exit;

} else {
    // ถ้าเข้าถึงโดยตรงหรือไม่มี ID
    header("location: manage_users.php");
    exit;
}
?>