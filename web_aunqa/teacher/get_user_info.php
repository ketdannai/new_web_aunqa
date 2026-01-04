<?php
// กำหนด Content Type เป็น JSON
header('Content-Type: application/json');

// นำเข้าไฟล์เชื่อมต่อฐานข้อมูล
require_once "../config.php";

// ตรวจสอบว่ามีการส่ง use_id มาหรือไม่
if (!isset($_GET['use_id']) || empty($_GET['use_id'])) {
    echo json_encode(['error' => 'Missing User ID']);
    exit;
}

$use_id = intval($_GET['use_id']); // แปลงเป็นเลขจำนวนเต็มเพื่อความปลอดภัย

if (isset($link) && $link !== false) {
    // เตรียมคำสั่ง SQL เพื่อป้องกัน SQL Injection
    $sql = "SELECT use_title, use_fname, use_lname FROM users WHERE use_id = ?";
    
    if ($stmt = mysqli_prepare($link, $sql)) {
        // ผูกตัวแปร
        mysqli_stmt_bind_param($stmt, "i", $use_id);
        
        // รันคำสั่ง
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            
            if (mysqli_num_rows($result) == 1) {
                // พบข้อมูลผู้ใช้
                $user_info = mysqli_fetch_assoc($result);
                echo json_encode(['success' => true, 'data' => $user_info]);
            } else {
                // ไม่พบผู้ใช้
                echo json_encode(['success' => false, 'error' => 'ไม่พบ User ID ที่ระบุ']);
            }
            mysqli_free_result($result);
        } else {
            // Error ในการรันคำสั่ง
            echo json_encode(['success' => false, 'error' => 'DB Query Failed']);
        }
        mysqli_stmt_close($stmt);
    } else {
        // Error ในการเตรียมคำสั่ง
        echo json_encode(['success' => false, 'error' => 'SQL Prepare Failed']);
    }
    mysqli_close($link);
} else {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
}

exit;
?>