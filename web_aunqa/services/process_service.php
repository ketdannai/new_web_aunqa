<?php
// ไฟล์: services/process_service.php
session_start();
require_once "../config.php";

if (!isset($_SESSION["loggedin"])) exit;
$user_id = $_SESSION["use_id"];
$user_role = $_SESSION["use_role"];

// 1. จัดการการลบข้อมูล
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $res = $link->query("SELECT use_id FROM services WHERE serv_id = $id");
    $data = $res->fetch_assoc();

    if ($data && ($user_role == 'admin' || $data['use_id'] == $user_id)) {
        $link->query("DELETE FROM services WHERE serv_id = $id");
        $_SESSION["serv_success"] = "ลบข้อมูลสำเร็จ";
    }
    header("location: services.php");
    exit;
}

// 2. จัดการการเพิ่มและแก้ไขข้อมูล
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'];
    $serv_name = mysqli_real_escape_string($link, $_POST['serv_name']);
    $use_id = $_POST['use_id'];

    if ($action == 'add') {
        $sql = "INSERT INTO services (serv_name, use_id) VALUES ('$serv_name', '$use_id')";
        if ($link->query($sql)) $_SESSION["serv_success"] = "เพิ่มข้อมูลสำเร็จ";
    } else if ($action == 'edit') {
        $sid = intval($_POST['serv_id']);
        $check = $link->query("SELECT use_id FROM services WHERE serv_id = $sid")->fetch_assoc();
        
        if ($check && ($user_role == 'admin' || $check['use_id'] == $user_id)) {
            $sql = "UPDATE services SET serv_name = '$serv_name' WHERE serv_id = $sid";
            if ($link->query($sql)) $_SESSION["serv_success"] = "แก้ไขข้อมูลสำเร็จ";
        }
    }
    header("location: services.php");
    exit;
}