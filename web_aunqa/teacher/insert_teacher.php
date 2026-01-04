<?php
// ไฟล์: E:\xampp\htdocs\web_aunqa\teacher\insert_teacher.php

session_start();
require_once "../config.php"; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // ดึงค่าที่ส่งมาจาก Modal
    $user_id = $_POST['user_id'] ?? null; // ID ผู้ใช้งานที่ถูกส่งมาจาก Hidden Field
    $teac_position = trim($_POST['teac_position'] ?? '');
    $teac_qualification = trim($_POST['teac_qualification'] ?? '');
    $teac_branch = trim($_POST['teac_branch'] ?? '');
    $teac_status = trim($_POST['teac_status'] ?? '');

    // ตรวจสอบความถูกต้องของข้อมูลพื้นฐาน
    if (empty($user_id) || empty($teac_position) || empty($teac_qualification)) {
        $_SESSION["teacher_error"] = "กรุณากรอกข้อมูลให้ครบถ้วน";
        header("location: teacher.php");
        exit;
    }
    
    // คำสั่ง SQL: บันทึกข้อมูลอาจารย์ โดยผูกกับ use_id (ID ผู้ใช้งาน)
    $sql = "INSERT INTO teachers (use_id, teac_position, teac_qualification, teac_branch, teac_status) 
            VALUES (?, ?, ?, ?, ?)";
    
    if ($stmt = $link->prepare($sql)) {
        $stmt->bind_param("issss", $user_id, $teac_position, $teac_qualification, $teac_branch, $teac_status);
        
        if ($stmt->execute()) {
            $_SESSION["teacher_success"] = "บันทึกข้อมูลอาจารย์ใหม่สำเร็จแล้ว";
        } else {
            $_SESSION["teacher_error"] = "เกิดข้อผิดพลาดในการบันทึกข้อมูล: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION["teacher_error"] = "เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL: " . $link->error;
    }
    
    $link->close();
    header("location: teacher.php");
    exit;
} else {
    header("location: teacher.php");
    exit;
}
?>