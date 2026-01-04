<?php
// ไฟล์: research/process_research.php
session_start();
require_once "../config.php";

if (!isset($_SESSION["loggedin"])) exit;
$current_user = $_SESSION["use_id"];
$user_role = $_SESSION["use_role"];

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $res = $link->query("SELECT use_id FROM research WHERE res_id = $id");
    $data = $res->fetch_assoc();
    if ($data && ($user_role == 'admin' || $data['use_id'] == $current_user)) {
        $link->query("DELETE FROM research WHERE res_id = $id");
        $_SESSION["res_success"] = "ลบข้อมูลเรียบร้อยแล้ว";
    }
    header("location: research.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'];
    $use_id = $_POST['use_id'];
    $res_id = intval($_POST['res_id']);
    
    $name = mysqli_real_escape_string($link, $_POST['res_name']);
    $type = mysqli_real_escape_string($link, $_POST['res_type']);
    $date = mysqli_real_escape_string($link, $_POST['res_date']);
    $meet = mysqli_real_escape_string($link, $_POST['res_meet']);
    $pub = mysqli_real_escape_string($link, $_POST['res_publish']);
    $cap = mysqli_real_escape_string($link, $_POST['res_capital']);
    $bud = mysqli_real_escape_string($link, $_POST['res_budget']);

    $updates = []; $cols = []; $vals = [];
    for ($i = 1; $i <= 5; $i++) {
        $t = mysqli_real_escape_string($link, $_POST["res_title$i"]);
        $f = mysqli_real_escape_string($link, $_POST["res_fname$i"]);
        $l = mysqli_real_escape_string($link, $_POST["res_lname$i"]);
        
        $cols[] = "res_title$i"; $cols[] = "res_fname$i"; $cols[] = "res_lname$i";
        $vals[] = "'$t'"; $vals[] = "'$f'"; $vals[] = "'$l'";
        $updates[] = "res_title$i='$t'"; $updates[] = "res_fname$i='$f'"; $updates[] = "res_lname$i='$l'";
    }

    if ($action == 'add') {
        $sql = "INSERT INTO research (res_name, res_type, res_date, res_meet, res_publish, res_capital, res_budget, use_id, " . implode(", ", $cols) . ") 
                VALUES ('$name', '$type', '$date', '$meet', '$pub', '$cap', '$bud', '$use_id', " . implode(", ", $vals) . ")";
    } else {
        $sql = "UPDATE research SET res_name='$name', res_type='$type', res_date='$date', res_meet='$meet', res_publish='$pub', res_capital='$cap', res_budget='$bud', " . implode(", ", $updates) . " 
                WHERE res_id=$res_id";
    }

    if ($link->query($sql)) $_SESSION["res_success"] = "บันทึกข้อมูลวิจัยสำเร็จ";
    header("location: research.php");
    exit;
}