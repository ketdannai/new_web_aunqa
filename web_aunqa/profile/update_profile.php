<?php
// ไฟล์: profile/update_profile.php
session_start();
require_once "../config.php";

// ตรวจสอบสิทธิ์การเข้าถึง
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login/login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. รับค่าจากฟอร์ม
    $user_id = $_SESSION["use_id"];
    $new_title = mysqli_real_escape_string($link, $_POST['use_title']);
    $new_fname = mysqli_real_escape_string($link, $_POST['use_fname']);
    $new_lname = mysqli_real_escape_string($link, $_POST['use_lname']);

    // 2. อัปเดตข้อมูลในฐานข้อมูล
    $sql = "UPDATE users SET use_title = ?, use_fname = ?, use_lname = ? WHERE use_id = ?";
    
    if ($stmt = $link->prepare($sql)) {
        $stmt->bind_param("sssi", $new_title, $new_fname, $new_lname, $user_id);
        
        if ($stmt->execute()) {
            // *** ขั้นตอนสำคัญ: อัปเดตค่าใน SESSION ใหม่ทันทีเพื่อให้หน้าเว็บเปลี่ยนตาม ***
            $_SESSION["use_title"] = $new_title;
            $_SESSION["use_fname"] = $new_fname;
            $_SESSION["use_lname"] = $new_lname;

            $_SESSION["profile_success"] = "อัปเดตข้อมูลส่วนตัวเรียบร้อยแล้ว";
        } else {
            $_SESSION["profile_error"] = "เกิดข้อผิดพลาด: ไม่สามารถบันทึกข้อมูลได้";
        }
        $stmt->close();
    }
    
    // 3. กลับไปหน้าเดิม
    header("location: profile.php");
    exit;
}