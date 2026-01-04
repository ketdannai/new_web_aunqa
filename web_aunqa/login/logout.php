<?php
// ไฟล์: login/logout.php
session_start();

// 1. ล้างค่าตัวแปร Session ทั้งหมด
$_SESSION = array();

// 2. ทำลาย Session ของผู้ใช้นี้
session_destroy();

// 3. ส่งผู้ใช้กลับไปยังหน้า index.php
// หาก logout.php อยู่ในโฟลเดอร์ login ให้ใช้ ../ เพื่อถอยกลับไปหน้าหลัก
header("location: ../index.php"); 
exit;
?>