<?php
// ไฟล์: development/process_development.php
session_start();
require_once "../config.php";

if (!isset($_SESSION["loggedin"])) exit;

$user_id = $_SESSION["use_id"];
$user_role = $_SESSION["use_role"];

// 1. จัดการการลบข้อมูล
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $res = $link->query("SELECT use_id FROM development WHERE dev_id = $id");
    $data = $res->fetch_assoc();

    if ($data && ($user_role == 'admin' || $data['use_id'] == $user_id)) {
        if ($link->query("DELETE FROM development WHERE dev_id = $id")) {
            $_SESSION["dev_success"] = "ลบข้อมูลสำเร็จ";
        }
    }
    header("location: development.php");
    exit;
}

// 2. จัดการการเพิ่มและแก้ไข
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'];
    $dev_id = intval($_POST['dev_id']);
    $use_id = $_POST['use_id'];
    
    // แก้ไขจุดนี้: ถ้า section_id ว่าง ให้ส่งค่า NULL แบบไม่มีเครื่องหมายคำพูดครอบ
    $section_id = !empty($_POST['section_id']) ? intval($_POST['section_id']) : "NULL";
    
    $dev_name = mysqli_real_escape_string($link, $_POST['dev_name']);
    $dev_date = mysqli_real_escape_string($link, $_POST['dev_date']);
    $dev_at = mysqli_real_escape_string($link, $_POST['dev_at']);
    $dev_obj = mysqli_real_escape_string($link, $_POST['dev_obj']);

    if ($action == 'add') {
        // สังเกตตรง $section_id จะไม่ใส่เครื่องหมาย ' ครอบ เพราะถ้าเป็นค่า NULL จะใส่ครอบไม่ได้
        $sql = "INSERT INTO development (use_id, section_id, dev_name, dev_date, dev_at, dev_obj) 
                VALUES ('$use_id', $section_id, '$dev_name', '$dev_date', '$dev_at', '$dev_obj')";
        
        if ($link->query($sql)) {
            $_SESSION["dev_success"] = "เพิ่มข้อมูลสำเร็จ";
        } else {
            die("Error: " . $link->error); // แสดง Error หากบันทึกไม่ได้
        }
    } 
    elseif ($action == 'edit') {
        $check = $link->query("SELECT use_id FROM development WHERE dev_id = $dev_id")->fetch_assoc();
        if ($check && ($user_role == 'admin' || $check['use_id'] == $user_id)) {
            $sql = "UPDATE development SET 
                    section_id = $section_id, 
                    dev_name = '$dev_name', 
                    dev_date = '$dev_date', 
                    dev_at = '$dev_at', 
                    dev_obj = '$dev_obj' 
                    WHERE dev_id = $dev_id";
            
            if ($link->query($sql)) {
                $_SESSION["dev_success"] = "แก้ไขข้อมูลสำเร็จ";
            } else {
                die("Error: " . $link->error);
            }
        }
    }
    header("location: development.php");
    exit;
}
?>