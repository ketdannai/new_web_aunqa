<?php
// ไฟล์: E:\xampp\htdocs\web_aunqa\manage_users.php

session_start();
 
// ตรวจสอบการเข้าสู่ระบบและสิทธิ์ Admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["use_role"] !== 'admin'){
    // ถ้าไม่ใช่ Admin ให้ Redirect กลับไปหน้า Dashboard
    header("location: dashboard.php");
    exit;
}

// นำเข้าไฟล์เชื่อมต่อฐานข้อมูล
require_once "config.php"; 

$users = [];
$error_message = "";

if ($link === false) {
    $error_message = "ERROR: ไม่สามารถเชื่อมต่อฐานข้อมูลได้";
} else {
    // เตรียมคำสั่ง SQL เพื่อดึงข้อมูลผู้ใช้ทั้งหมด ยกเว้นตัวเอง (use_id = 11) หรือยกเว้น Admin คนอื่น
    $sql = "SELECT use_id, username, use_title, use_fname, use_lname, use_role FROM users ORDER BY use_id";

    if ($result = $link->query($sql)) {
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()){
                // กรองไม่ให้ Admin จัดการตัวเอง (ทางเลือก)
                if ($row['use_id'] != $_SESSION['use_id']) { 
                    $users[] = $row;
                }
            }
        }
        $result->free();
    } else {
        $error_message = "เกิดข้อผิดพลาดในการดึงข้อมูล: " . $link->error;
    }
    
    $link->close();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการผู้ใช้ | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { font-family: 'Kanit', sans-serif; padding: 20px; }
        .table-admin th { background-color: #007bff; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="mb-4">จัดการข้อมูลผู้ใช้งานทั้งหมด</h2>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <?php if (count($users) > 0): ?>
        <table class="table table-bordered table-striped table-admin">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>ชื่อ-นามสกุล</th>
                    <th>Role</th>
                    <th>ดำเนินการ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo $user['use_id']; ?></td>
                    <td><?php echo $user['username']; ?></td>
                    <td><?php echo $user['use_title'] . $user['use_fname'] . " " . $user['use_lname']; ?></td>
                    <td><span class="badge bg-<?php echo ($user['use_role'] == 'admin' ? 'danger' : 'success'); ?>"><?php echo $user['use_role']; ?></span></td>
                    <td>
                        <a href="edit_user.php?id=<?php echo $user['use_id']; ?>" class="btn btn-warning btn-sm me-2">
                            <i class="bi bi-pencil"></i> แก้ไข
                        </a>
                        <form action="delete_user.php" method="POST" style="display:inline;" onsubmit="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบผู้ใช้ ID: <?php echo $user['use_id']; ?>?');">
                            <input type="hidden" name="id" value="<?php echo $user['use_id']; ?>">
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="bi bi-trash"></i> ลบ
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <div class="alert alert-info">ไม่พบผู้ใช้งานอื่น ๆ ในระบบ</div>
        <?php endif; ?>
        
        <p><a href="dashboard.php" class="btn btn-secondary mt-3">กลับหน้า Dashboard</a></p>
    </div>
</body>
</html>