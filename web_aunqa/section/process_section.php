<?php
// ไฟล์: section/process_section.php
session_start();
require_once "../config.php";

if (!isset($_SESSION["loggedin"])) exit;
$current_user = $_SESSION["use_id"];
$user_role = $_SESSION["use_role"];

// 1. จัดการการลบข้อมูล
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $res = $link->query("SELECT use_id FROM section WHERE section_id = $id");
    $data = $res->fetch_assoc();

    if ($data && ($user_role == 'admin' || $data['use_id'] == $current_user)) {
        if ($link->query("DELETE FROM section WHERE section_id = $id")) {
            $_SESSION["sec_success"] = "ลบข้อมูลกลุ่มเรียนเรียบร้อยแล้ว";
        }
    }
    header("location: section.php");
    exit;
}

// 2. จัดการการเพิ่มและแก้ไขข้อมูล
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'];
    $use_id = $_POST['use_id'];
    $section_name = mysqli_real_escape_string($link, $_POST['section_name']);
    $section_num = intval($_POST['section_num']);
    $section_year = mysqli_real_escape_string($link, $_POST['section_year']);

    if ($action == 'add') {
        $sql = "INSERT INTO section (section_name, section_num, section_year, use_id) 
                VALUES ('$section_name', '$section_num', '$section_year', '$use_id')";
        if ($link->query($sql)) $_SESSION["sec_success"] = "เพิ่มกลุ่มเรียนสำเร็จ";
    } else if ($action == 'edit') {
        $sec_id = intval($_POST['section_id']);
        $check = $link->query("SELECT use_id FROM section WHERE section_id = $sec_id")->fetch_assoc();
        
        if ($check && ($user_role == 'admin' || $check['use_id'] == $current_user)) {
            $sql = "UPDATE section SET 
                    section_name = '$section_name', 
                    section_num = '$section_num', 
                    section_year = '$section_year' 
                    WHERE section_id = $sec_id";
            if ($link->query($sql)) $_SESSION["sec_success"] = "แก้ไขข้อมูลกลุ่มเรียนสำเร็จ";
        }
    }
    header("location: section.php");
    exit;
}