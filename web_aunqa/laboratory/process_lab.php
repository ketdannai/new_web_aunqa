<?php
session_start();
require_once "../config.php";

if (!isset($_SESSION["loggedin"])) exit;
$current_user = $_SESSION["use_id"];
$user_role = $_SESSION["use_role"];

// 1. จัดการการลบข้อมูล
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $res = $link->query("SELECT use_id FROM laboratory WHERE lab_id = $id");
    $data = $res->fetch_assoc();

    if ($data && ($user_role == 'admin' || $data['use_id'] == $current_user)) {
        $link->query("DELETE FROM laboratory WHERE lab_id = $id");
        $_SESSION["lab_success"] = "ลบข้อมูลสำเร็จ";
    }
    header("location: laboratory.php");
    exit;
}

// 2. จัดการการเพิ่มและแก้ไขข้อมูล
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'];
    $lab_id = intval($_POST['lab_id']);
    $use_id = $_POST['use_id'];
    $lab_name = mysqli_real_escape_string($link, $_POST['lab_name']);
    $lab_num = mysqli_real_escape_string($link, $_POST['lab_num']);
    $lab_durable = mysqli_real_escape_string($link, $_POST['lab_durable']);
    $lab_status = $_POST['lab_status'];

    if ($action == 'add') {
        $sql = "INSERT INTO laboratory (lab_name, lab_num, lab_durable, lab_status, use_id) 
                VALUES ('$lab_name', '$lab_num', '$lab_durable', '$lab_status', '$use_id')";
        if ($link->query($sql)) $_SESSION["lab_success"] = "เพิ่มข้อมูลสำเร็จ";
    } else if ($action == 'edit') {
        $check = $link->query("SELECT use_id FROM laboratory WHERE lab_id = $lab_id")->fetch_assoc();
        if ($check && ($user_role == 'admin' || $check['use_id'] == $current_user)) {
            $sql = "UPDATE laboratory SET lab_name='$lab_name', lab_num='$lab_num', 
                    lab_durable='$lab_durable', lab_status='$lab_status' WHERE lab_id=$lab_id";
            if ($link->query($sql)) $_SESSION["lab_success"] = "แก้ไขข้อมูลสำเร็จ";
        }
    }
    header("location: laboratory.php");
    exit;
}