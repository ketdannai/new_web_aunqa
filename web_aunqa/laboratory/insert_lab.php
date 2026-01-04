<?php
session_start();
require_once "../config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $use_id = $_POST['user_id'];
    $name = $_POST['lab_name'];
    $num = $_POST['lab_num'];
    $durable = $_POST['lab_durable'];
    $status = $_POST['lab_status'];

    $sql = "INSERT INTO laboratory (use_id, lab_name, lab_num, lab_durable, lab_status) VALUES (?, ?, ?, ?, ?)";
    if ($stmt = $link->prepare($sql)) {
        $stmt->bind_param("issss", $use_id, $name, $num, $durable, $status);
        if ($stmt->execute()) {
            $_SESSION["lab_success"] = "เพิ่มข้อมูลห้องปฏิบัติการสำเร็จ";
        } else {
            $_SESSION["lab_error"] = "เกิดข้อผิดพลาด: " . $link->error;
        }
        $stmt->close();
    }
}
header("location: laboratory.php");
exit;