<?php
session_start();
require_once "../config.php";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sql = "UPDATE section SET course_id=?, section_name=?, section_num=?, section_year=? WHERE section_id=?";
    if ($stmt = $link->prepare($sql)) {
        $stmt->bind_param("isiii", $_POST['course_id'], $_POST['section_name'], $_POST['section_num'], $_POST['section_year'], $_POST['section_id']);
        if ($stmt->execute()) { $_SESSION['success'] = "แก้ไขข้อมูลสำเร็จ"; }
        $stmt->close();
    }
}
header("location: section.php");
exit;