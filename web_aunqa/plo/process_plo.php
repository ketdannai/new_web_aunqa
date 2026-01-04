<?php
// ไฟล์: plo/process_plo.php
session_start();
require_once "../config.php";

if ($_SESSION["use_role"] !== 'admin') { exit; }

if (isset($_POST['save_plo'])) {
    $code   = mysqli_real_escape_string($link, $_POST['plo_code']);
    
    // รวมค่า Checkbox เป็น String คั่นด้วยเครื่องหมายจุลภาค (,)
    $blooms = isset($_POST['blooms']) ? implode(',', $_POST['blooms']) : '';
    $specs  = isset($_POST['specs'])  ? implode(',', $_POST['specs'])  : '';
    $gens   = isset($_POST['gens'])   ? implode(',', $_POST['gens'])   : '';

    // ตารางคุณมีคอลัมน์ plo_code, plo_bty, plo_knowledge, plo_skill
    $sql = "INSERT INTO plo (plo_code, plo_bty, plo_knowledge, plo_skill) VALUES (?, ?, ?, ?)";
    
    if ($stmt = $link->prepare($sql)) {
        $stmt->bind_param("ssss", $code, $blooms, $specs, $gens);
        if ($stmt->execute()) {
            $_SESSION["plo_success"] = "บันทึกข้อมูล PLO และทักษะเรียบร้อยแล้ว";
        }
        $stmt->close();
    }
    header("location: plo.php");
}
?>