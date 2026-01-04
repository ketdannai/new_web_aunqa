<?php
session_start();
require_once '../config.php';
$conn = $link;

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // 1. เช็คชื่อ Session: ถ้า 'user_id' ไม่มี ให้ลองใช้ 'use_id'
    // หรือคุณต้องกลับไปดูไฟล์ login ว่าตั้งชื่อ session ว่าอะไร
    $session_uid = isset($_SESSION['use_id']) ? $_SESSION['use_id'] : (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null);
    $session_role = isset($_SESSION['use_role']) ? $_SESSION['use_role'] : '';

    if (!$session_uid) {
        $_SESSION['error'] = "กรุณาล็อกอินใหม่ เซสชันอาจหมดอายุ";
        header("Location: course.php");
        exit();
    }

    // 2. ใช้ชื่อตาราง 'course' (ตามที่ error แจ้งว่า course_id ไม่มีอยู่จริง)
    $check_sql = "SELECT use_id FROM course WHERE course_id = '$id'";
    $check_result = mysqli_query($conn, $check_sql);
    
    if ($check_result && mysqli_num_rows($check_result) > 0) {
        $row = mysqli_fetch_assoc($check_result);
        
        // 3. ตรวจสอบสิทธิ์: เป็น admin หรือ เป็นเจ้าของ (ID ตรงกัน)
        if ($session_role == 'admin' || $session_uid == $row['use_id']) {
            
            $sql = "DELETE FROM course WHERE course_id = '$id'";
            
            if (mysqli_query($conn, $sql)) {
                $_SESSION['success'] = "ลบข้อมูลเรียบร้อยแล้ว";
            } else {
                $_SESSION['error'] = "ลบไม่สำเร็จ: " . mysqli_error($conn);
            }
        } else {
            $_SESSION['error'] = "คุณไม่มีสิทธิ์ลบข้อมูลนี้ (ไม่ใช่เจ้าของ)";
        }
    } else {
        $_SESSION['error'] = "ไม่พบข้อมูลรายวิชานี้ในระบบ";
    }
}

header("Location: course.php");
exit();
?>