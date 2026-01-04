<?php
// ไฟล์: course/process_course.php
session_start();
require_once "../config.php";

if (!isset($_SESSION["loggedin"])) { exit; }

$user_id = $_SESSION["use_id"];
$role = $_SESSION["use_role"];

// --- กรณีลบข้อมูล ---
if (isset($_GET['delete'])) {
    $course_id = $_GET['delete'];
    
    $check_sql = ($role === 'admin') 
        ? "DELETE FROM course WHERE course_id = ?" 
        : "DELETE FROM course WHERE course_id = ? AND use_id = ?";
    
    if ($stmt = $link->prepare($check_sql)) {
        if ($role === 'admin') {
            $stmt->bind_param("i", $course_id);
        } else {
            $stmt->bind_param("ii", $course_id, $user_id);
        }
        
        if ($stmt->execute()) {
            $_SESSION["course_success"] = "ลบข้อมูลเรียบร้อยแล้ว";
        }
        $stmt->close();
    }
    header("location: course.php");
    exit;
}

// --- กรณีเพิ่มหรือแก้ไขข้อมูล ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'];
    $code = $_POST['course_code'];
    $name = $_POST['course_name'];
    $credit = $_POST['course_credit'];
    
    // *** ส่วนที่เพิ่มเข้ามาใหม่: รับค่าหมวดหมู่จากฟอร์ม ***
    $category_id = $_POST['category_id'];
    $categorycourse_id = $_POST['categorycourse_id'];

    if ($action === 'add') {
        // เพิ่มคอลัมน์ category_id และ categorycourse_id ใน SQL
        $sql = "INSERT INTO course (course_code, course_name, course_credit, category_id, categorycourse_id, use_id) VALUES (?, ?, ?, ?, ?, ?)";
        if ($stmt = $link->prepare($sql)) {
            // bind_param: s (string), i (integer) -> ssii i i
            $stmt->bind_param("sssiii", $code, $name, $credit, $category_id, $categorycourse_id, $user_id);
            if ($stmt->execute()) {
                $_SESSION["course_success"] = "เพิ่มรายวิชาใหม่เรียบร้อยแล้ว";
            }
            $stmt->close();
        }
    } 
    elseif ($action === 'edit') {
        $course_id = $_POST['course_id'];
        
        // เพิ่มการ SET ค่าหมวดหมู่ใน SQL
        $sql = ($role === 'admin')
            ? "UPDATE course SET course_code=?, course_name=?, course_credit=?, category_id=?, categorycourse_id=? WHERE course_id=?"
            : "UPDATE course SET course_code=?, course_name=?, course_credit=?, category_id=?, categorycourse_id=? WHERE course_id=? AND use_id=?";
            
        if ($stmt = $link->prepare($sql)) {
            if ($role === 'admin') {
                // ผูกตัวแปร 6 ตัว
                $stmt->bind_param("sssiii", $code, $name, $credit, $category_id, $categorycourse_id, $course_id);
            } else {
                // ผูกตัวแปร 7 ตัว
                $stmt->bind_param("sssiiii", $code, $name, $credit, $category_id, $categorycourse_id, $course_id, $user_id);
            }
            
            if ($stmt->execute()) {
                $_SESSION["course_success"] = "แก้ไขข้อมูลเรียบร้อยแล้ว";
            }
            $stmt->close();
        }
    }
    
    header("location: course.php");
    exit;
}