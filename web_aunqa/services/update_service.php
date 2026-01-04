<?php
// ไฟล์: services/update_service.php
session_start();
require_once "../config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_service'])) {
    $serv_id = $_POST['serv_id'];
    $use_id = $_POST['use_id']; // รับค่า use_id ใหม่จาก Dropdown
    $serv_name = mysqli_real_escape_string($link, $_POST['serv_name']);
    
    $logged_in_user_id = $_SESSION["use_id"];
    $is_admin = ($_SESSION["use_role"] == 'admin');

    // ตรวจสอบสิทธิ์อีกครั้ง (Admin หรือ เจ้าของเดิม)
    $check_sql = "SELECT use_id FROM services WHERE serv_id = ?";
    $stmt = $link->prepare($check_sql);
    $stmt->bind_param("i", $serv_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if ($row && ($is_admin || $row['use_id'] == $logged_in_user_id)) {
        // อัปเดตทั้งผู้รับผิดชอบและชื่องาน
        $update_sql = "UPDATE services SET use_id = ?, serv_name = ? WHERE serv_id = ?";
        if ($up_stmt = $link->prepare($update_sql)) {
            $up_stmt->bind_param("isi", $use_id, $serv_name, $serv_id);
            if ($up_stmt->execute()) {
                $_SESSION["serv_success"] = "อัปเดตข้อมูลสำเร็จแล้ว";
            }
            $up_stmt->close();
        }
    }
    $stmt->close();
}
header("location: services.php");
exit;