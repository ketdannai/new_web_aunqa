<?php
session_start();
require_once "../config.php";

if ($_SESSION["use_role"] !== 'admin') { header("location: plo.php"); exit; }

if (isset($_GET['id'])) {
    $sql = "DELETE FROM plo WHERE plo_id = ?";
    $stmt = $link->prepare($sql);
    $stmt->bind_param("i", $_GET['id']);
    if ($stmt->execute()) { $_SESSION["plo_success"] = "ลบ PLO สำเร็จ"; }
    header("location: plo.php");
}
?>