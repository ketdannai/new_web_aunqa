<?php
session_start();
require_once "../config.php";
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $check = $link->query("SELECT use_id FROM section WHERE section_id = '$id'")->fetch_assoc();
    if ($_SESSION['use_role'] == 'admin' || $check['use_id'] == $_SESSION['use_id']) {
        $link->query("DELETE FROM section WHERE section_id = '$id'");
        $_SESSION['success'] = "ลบข้อมูลเรียบร้อยแล้ว";
    }
}
header("location: section.php");
exit;