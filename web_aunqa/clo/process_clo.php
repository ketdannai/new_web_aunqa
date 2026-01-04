<?php
// ไฟล์: clo/process_clo.php
session_start();
require_once "../config.php";

if (!isset($_SESSION["loggedin"])) exit;
$current_user = $_SESSION["use_id"];
$user_role = $_SESSION["use_role"];

// 1. การลบข้อมูล (ลบยกชุดที่เป็น CLO เดียวกันของวิชานั้น)
if (isset($_GET['delete_course']) && isset($_GET['delete_code'])) {
    $cid = intval($_GET['delete_course']);
    $code = mysqli_real_escape_string($link, $_GET['delete_code']);
    
    $check = $link->query("SELECT use_id FROM clo WHERE course_id = $cid AND clo_code = '$code' LIMIT 1")->fetch_assoc();
    if ($check && ($user_role == 'admin' || $check['use_id'] == $current_user)) {
        $link->query("DELETE FROM clo WHERE course_id = $cid AND clo_code = '$code'");
        $_SESSION["clo_success"] = "ลบข้อมูลเรียบร้อยแล้ว";
    }
    header("location: clo.php");
    exit;
}

// 2. การเพิ่มและแก้ไขข้อมูล
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'];
    $use_id = $_POST['use_id'];
    $course_id = intval($_POST['course_id']);
    $clo_code = mysqli_real_escape_string($link, $_POST['clo_code']);
    $plo_ids = $_POST['plo_id']; // ค่าที่ได้มาเป็น Array

    // กรณีแก้ไข: ลบของเก่าชุดนี้ออกก่อน เพื่อบันทึกชุดใหม่เข้าไปแทน
    if ($action == 'edit') {
        $old_cid = intval($_POST['old_course_id']);
        $old_code = mysqli_real_escape_string($link, $_POST['old_clo_code']);
        
        $check = $link->query("SELECT use_id FROM clo WHERE course_id = $old_cid AND clo_code = '$old_code' LIMIT 1")->fetch_assoc();
        if ($check && ($user_role == 'admin' || $check['use_id'] == $current_user)) {
            $link->query("DELETE FROM clo WHERE course_id = $old_cid AND clo_code = '$old_code'");
        } else {
            header("location: clo.php"); exit;
        }
    }

    // บันทึกข้อมูล PLO ทุกตัวที่เลือกมา (วนลูป Insert ทีละแถว)
    if (is_array($plo_ids)) {
        foreach ($plo_ids as $p_id) {
            $p_id = intval($p_id);
            $sql = "INSERT INTO clo (plo_id, course_id, use_id, clo_code) VALUES ($p_id, $course_id, $use_id, '$clo_code')";
            $link->query($sql);
        }
        $_SESSION["clo_success"] = "บันทึกข้อมูลสำเร็จเรียบร้อย";
    }
    header("location: clo.php");
    exit;
}