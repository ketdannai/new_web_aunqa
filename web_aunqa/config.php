<?php
/* Database credentials. Assuming you are running MySQL
server with default setting (user 'root' and no password) */
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root'); // ตรวจสอบผู้ใช้
define('DB_PASSWORD', '');     // ตรวจสอบรหัสผ่าน (เว้นว่างถ้าไม่มี)
define('DB_NAME', 'web_aunqa'); // ตรวจสอบชื่อฐานข้อมูล

/* Attempt to connect to MySQL database */
// กำหนดตัวแปร $link เป็น Global variable เพื่อให้ทุกไฟล์เข้าถึงได้
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if($link === false){
    // ใช้ die() หรือ throw exception เพื่อหยุดการทำงานหากเชื่อมต่อไม่ได้
    // แต่ควรส่งข้อความ error ที่ชัดเจนไปที่หน้าหลักแทน
    die("ERROR: Could not connect. " . mysqli_connect_error()); 
    // ถ้าคุณต้องการให้หน้าเว็บแสดงผลต่อแม้เชื่อมต่อไม่ได้ ให้เปลี่ยนเป็น
    // $link = false; // และไฟล์อื่นต้องจัดการเมื่อ $link เป็น false
}
?>