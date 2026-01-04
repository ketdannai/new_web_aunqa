<?php
// ไฟล์: article/process_article.php
session_start();
require_once "../config.php";

if (!isset($_SESSION["loggedin"])) exit;
$current_user = $_SESSION["use_id"];
$user_role = $_SESSION["use_role"];

// 1. ลบข้อมูล
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $res = $link->query("SELECT use_id FROM article WHERE art_id = $id");
    $data = $res->fetch_assoc();

    if ($data && ($user_role == 'admin' || $data['use_id'] == $current_user)) {
        $link->query("DELETE FROM article WHERE art_id = $id");
        $_SESSION["art_success"] = "ลบข้อมูลเรียบร้อยแล้ว";
    }
    header("location: article.php");
    exit;
}

// 2. เพิ่ม/แก้ไขข้อมูล
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'];
    $use_id = $_POST['use_id'];
    $art_id = intval($_POST['art_id']);
    
    $name = mysqli_real_escape_string($link, $_POST['art_name']);
    $type = mysqli_real_escape_string($link, $_POST['art_type']);
    $meet = mysqli_real_escape_string($link, $_POST['art_meet']);
    $evid = mysqli_real_escape_string($link, $_POST['art_evidence']);

    // สร้าง SQL อัตโนมัติสำหรับผู้เขียนทั้ง 5 คน
    $cols = []; $vals = []; $updates = [];
    for ($i = 1; $i <= 5; $i++) {
        $t = mysqli_real_escape_string($link, $_POST["art_title$i"]);
        $f = mysqli_real_escape_string($link, $_POST["art_fname$i"]);
        $l = mysqli_real_escape_string($link, $_POST["art_lname$i"]);
        
        $cols[] = "art_title$i"; $cols[] = "art_fname$i"; $cols[] = "art_lname$i";
        $vals[] = "'$t'"; $vals[] = "'$f'"; $vals[] = "'$l'";
        $updates[] = "art_title$i='$t'"; $updates[] = "art_fname$i='$f'"; $updates[] = "art_lname$i='$l'";
    }

    if ($action == 'add') {
        $sql = "INSERT INTO article (art_name, art_type, art_meet, art_evidence, use_id, " . implode(", ", array_slice($cols, 0, 15)) . ") 
                VALUES ('$name', '$type', '$meet', '$evid', '$use_id', " . implode(", ", array_slice($vals, 0, 15)) . ")";
    } else {
        $sql = "UPDATE article SET art_name='$name', art_type='$type', art_meet='$meet', art_evidence='$evid', " . implode(", ", $updates) . " 
                WHERE art_id=$art_id";
    }

    if ($link->query($sql)) $_SESSION["art_success"] = "บันทึกข้อมูลสำเร็จ";
    header("location: article.php");
    exit;
}