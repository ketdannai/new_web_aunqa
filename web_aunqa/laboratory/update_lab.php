<?php
session_start();
require_once "../config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['lab_id'];
    $sql = "UPDATE laboratory SET lab_name=?, lab_num=?, lab_durable=?, lab_status=? WHERE lab_id=?";
    if ($stmt = $link->prepare($sql)) {
        $stmt->bind_param("ssssi", $_POST['lab_name'], $_POST['lab_num'], $_POST['lab_durable'], $_POST['lab_status'], $id);
        if ($stmt->execute()) { $_SESSION["lab_success"] = "แก้ไขสำเร็จ"; }
        $stmt->close();
    }
    header("location: laboratory.php");
}
?>