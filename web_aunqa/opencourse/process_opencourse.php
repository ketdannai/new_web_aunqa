<?php
// ไฟล์: opencourse/process_opencourse.php
session_start();
require_once "../config.php";

// ตรวจสอบการ Login
if (!isset($_SESSION["loggedin"])) {
    exit;
}

$logged_user_id = $_SESSION["use_id"];
$user_role = $_SESSION["use_role"];

// --- 1. จัดการการลบข้อมูล ---
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // ดึงข้อมูลมาตรวจสอบเจ้าของก่อนลบ
    $res = $link->query("SELECT use_id FROM opencourse WHERE opencourse_id = $id");
    $data = $res->fetch_assoc();

    if ($data) {
        // เงื่อนไข: เป็น Admin ลบได้หมด หรือ เป็นเจ้าของข้อมูลถึงลบได้
        if ($user_role === 'admin' || $data['use_id'] == $logged_user_id) {
            $stmt = $link->prepare("DELETE FROM opencourse WHERE opencourse_id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $_SESSION["oc_success"] = "ลบข้อมูลเรียบร้อยแล้ว";
            }
            $stmt->close();
        }
    }
    header("location: opencourse.php");
    exit;
}

// --- 2. จัดการการเพิ่มและแก้ไขข้อมูล ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'];
    $oc_id = isset($_POST['opencourse_id']) ? intval($_POST['opencourse_id']) : null;
    $course_id = intval($_POST['course_id']);
    $section_id = intval($_POST['section_id']);
    $term_id = intval($_POST['term_id']); // รับค่า term_id แทนปีการศึกษาเดิม
    
    // รับค่า use_id (ถ้าเป็น Admin จะมาจาก Select, ถ้าเป็น User จะมาจาก Hidden input)
    $target_use_id = intval($_POST['use_id']);

    if ($action == 'add') {
        // เพิ่มข้อมูลใหม่
        $sql = "INSERT INTO opencourse (course_id, use_id, section_id, term_id) VALUES (?, ?, ?, ?)";
        if ($stmt = $link->prepare($sql)) {
            $stmt->bind_param("iiii", $course_id, $target_use_id, $section_id, $term_id);
            if ($stmt->execute()) {
                $_SESSION["oc_success"] = "เพิ่มข้อมูลวิชาเปิดสอนสำเร็จ";
            }
            $stmt->close();
        }
    } 
    else if ($action == 'edit') {
        // ตรวจสอบสิทธิ์ก่อนแก้ไข
        $check_res = $link->query("SELECT use_id FROM opencourse WHERE opencourse_id = $oc_id");
        $check_data = $check_res->fetch_assoc();

        if ($check_data) {
            if ($user_role === 'admin' || $check_data['use_id'] == $logged_user_id) {
                // ถ้าเป็น Admin สามารถเปลี่ยน use_id (ผู้สอน) ได้ด้วย
                if ($user_role === 'admin') {
                    $sql = "UPDATE opencourse SET course_id=?, section_id=?, term_id=?, use_id=? WHERE opencourse_id=?";
                    $stmt = $link->prepare($sql);
                    $stmt->bind_param("iiiii", $course_id, $section_id, $term_id, $target_use_id, $oc_id);
                } else {
                    // ถ้าเป็น User ทั่วไป แก้ไขได้แค่ข้อมูลวิชา/กลุ่ม/เทอม ของตัวเองเท่านั้น
                    $sql = "UPDATE opencourse SET course_id=?, section_id=?, term_id=? WHERE opencourse_id=? AND use_id=?";
                    $stmt = $link->prepare($sql);
                    $stmt->bind_param("iiiii", $course_id, $section_id, $term_id, $oc_id, $logged_user_id);
                }

                if ($stmt->execute()) {
                    $_SESSION["oc_success"] = "แก้ไขข้อมูลเรียบร้อยแล้ว";
                }
                $stmt->close();
            }
        }
    }

    header("location: opencourse.php");
    exit;
}
?>