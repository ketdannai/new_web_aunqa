<?php
session_start();
require_once "../config.php";

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    // ตรวจสอบสิทธิ์ก่อนลบ
    $check = $link->query("SELECT use_id FROM services WHERE serv_id = '$id'")->fetch_assoc();
    if ($_SESSION['use_role'] == 'admin' || $check['use_id'] == $_SESSION['use_id']) {
        $link->query("DELETE FROM services WHERE serv_id = '$id'");
        $_SESSION["serv_success"] = "ลบข้อมูลเรียบร้อยแล้ว";
    }
}
header("location: services.php");
?>