<?php
// ไฟล์: plo/update_plo.php
session_start();
require_once "../config.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["use_role"] !== 'admin') {
    exit;
}

if (isset($_POST['update_plo'])) {
    $plo_id = mysqli_real_escape_string($link, $_POST['plo_id']);
    $plo_code = mysqli_real_escape_string($link, $_POST['plo_code']);
    
    // รวมค่าจาก Checkbox
    $blooms = isset($_POST['blooms']) ? implode(',', $_POST['blooms']) : '';
    $specs  = isset($_POST['specs'])  ? implode(',', $_POST['specs'])  : '';
    $gens   = isset($_POST['gens'])   ? implode(',', $_POST['gens'])   : '';

    $sql = "UPDATE plo SET 
            plo_code = ?, 
            plo_bty = ?, 
            plo_knowledge = ?, 
            plo_skill = ? 
            WHERE plo_id = ?";
    
    if ($stmt = $link->prepare($sql)) {
        $stmt->bind_param("ssssi", $plo_code, $blooms, $specs, $gens, $plo_id);
        if ($stmt->execute()) {
            $_SESSION["plo_success"] = "อัปเดตข้อมูล PLO สำเร็จ";
        }
        $stmt->close();
    }
}
header("location: plo.php");
exit;