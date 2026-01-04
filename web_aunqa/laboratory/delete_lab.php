<?php
session_start();
require_once "../config.php";

if (isset($_GET['id'])) {
    $lab_id = $_GET['id'];
    $user_id = $_SESSION["use_id"];
    $is_admin = ($_SESSION["use_role"] == 'admin');

    // ตรวจสอบสิทธิ์ก่อนลบ (ต้องเป็นเจ้าของหรือ Admin)
    $check_sql = "SELECT use_id FROM laboratory WHERE lab_id = ?";
    $stmt = $link->prepare($check_sql);
    $stmt->bind_param("i", $lab_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();

    if ($row && ($is_admin || $row['use_id'] == $user_id)) {
        $del_sql = "DELETE FROM laboratory WHERE lab_id = ?";
        $del_stmt = $link->prepare($del_sql);
        $del_stmt->bind_param("i", $lab_id);
        if ($del_stmt->execute()) {
            $_SESSION["success"] = "ลบข้อมูลเรียบร้อยแล้ว";
        }
        $del_stmt->close();
    } else {
        $_SESSION["error"] = "คุณไม่มีสิทธิ์ลบข้อมูลนี้";
    }
}
header("location: laboratory.php");
exit;