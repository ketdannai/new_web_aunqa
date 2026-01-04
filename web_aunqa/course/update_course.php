<?php
// ไฟล์: course/update_course.php
session_start();
require_once "../config.php";

if (isset($_POST['update_course'])) {
    $id = $_POST['course_id'];
    $code = $_POST['course_code'];
    $name = $_POST['course_name'];
    $credit = $_POST['course_credit'];

    $sql = "UPDATE course SET course_code=?, course_name=?, course_credit=? WHERE course_id=?";
    if ($stmt = $link->prepare($sql)) {
        $stmt->bind_param("ssii", $code, $name, $credit, $id);
        if ($stmt->execute()) {
            $_SESSION["course_success"] = "แก้ไขข้อมูลรายวิชาเรียบร้อยแล้ว";
        }
        $stmt->close();
    }
}
header("location: course.php");
exit;