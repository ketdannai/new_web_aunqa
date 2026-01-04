<?php
// ไฟล์: E:\xampp\htdocs\web_aunqa\edit_user.php

session_start();
 
// ตรวจสอบสิทธิ์ Admin 
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["use_role"] !== 'admin'){
    header("location: dashboard.php");
    exit;
}

require_once "config.php"; 

$edit_id = $title = $fname = $lname = $role = $username = "";
$title_err = $fname_err = $lname_err = $role_err = $username_err = "";

// 1. ดึงข้อมูลผู้ใช้ที่จะแก้ไขเมื่อเข้าหน้า
if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    $edit_id = trim($_GET["id"]);
    
    // ดึงข้อมูลปัจจุบันของ user_id ที่เลือก
    $sql = "SELECT username, use_title, use_fname, use_lname, use_role FROM users WHERE use_id = ?";
    if ($stmt = $link->prepare($sql)) {
        $stmt->bind_param("i", $edit_id);
        if ($stmt->execute()) {
            $stmt->store_result();
            if ($stmt->num_rows == 1) {
                $stmt->bind_result($username, $title, $fname, $lname, $role);
                $stmt->fetch();
            } else {
                // ID ไม่ถูกต้อง
                header("location: manage_users.php");
                exit();
            }
        }
        $stmt->close();
    }
} else if (isset($_POST["id"]) && !empty(trim($_POST["id"]))) {
    $edit_id = trim($_POST["id"]);
    
    // 2. ประมวลผลการอัปเดตเมื่อมีการส่ง POST
    $title = trim($_POST["title"] ?? '');
    $fname = trim($_POST["fname"] ?? '');
    $lname = trim($_POST["lname"] ?? '');
    $role = trim($_POST["role"] ?? '');

    // ตรวจสอบความถูกต้องของข้อมูล (Validation)
    if (empty($title)) { $title_err = "กรุณาเลือกคำนำหน้า"; }
    if (empty($fname)) { $fname_err = "กรุณากรอกชื่อจริง"; }
    if (empty($lname)) { $lname_err = "กรุณากรอกนามสกุล"; }
    if (!in_array($role, ['user', 'admin'])) { $role_err = "สิทธิ์ไม่ถูกต้อง"; }

    // ถ้าไม่มีข้อผิดพลาด
    if (empty($title_err) && empty($fname_err) && empty($lname_err) && empty($role_err)) {
        
        $sql = "UPDATE users SET use_title = ?, use_fname = ?, use_lname = ?, use_role = ? WHERE use_id = ?";
        
        if ($stmt = $link->prepare($sql)) {
            // Bind parameters (s, s, s, s, i)
            $stmt->bind_param("ssssi", $title, $fname, $lname, $role, $edit_id);
            
            if ($stmt->execute()) {
                $_SESSION["manage_users_success"] = "อัปเดตข้อมูลผู้ใช้ ID: " . $edit_id . " สำเร็จ";
                header("location: manage_users.php");
                exit;
            } else {
                $_SESSION["manage_users_error"] = "เกิดข้อผิดพลาดในการอัปเดต: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $_SESSION["manage_users_error"] = "เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL";
        }
    }
} else {
    // ไม่พบ ID
    header("location: manage_users.php");
    exit;
}

if (isset($link) && $link !== false) {
    $link->close();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แก้ไขผู้ใช้ ID: <?php echo htmlspecialchars($edit_id); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container mt-5" style="max-width: 500px;">
        <h2 class="mb-4">แก้ไขข้อมูลผู้ใช้ ID: <?php echo htmlspecialchars($edit_id); ?></h2>
        
        <?php 
        // แสดงข้อความผิดพลาดระหว่างการประมวลผล POST
        if(isset($_SESSION["manage_users_error"])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION["manage_users_error"]; unset($_SESSION["manage_users_error"]); ?></div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($edit_id); ?>">

            <div class="mb-3">
                <label class="form-label">Username:</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($username); ?>" disabled>
                <div class="form-text">ไม่สามารถแก้ไข Username ได้จากหน้านี้</div>
            </div>

            <div class="mb-3">
                <label for="title" class="form-label">คำนำหน้า</label>
                <select id="title" name="title" class="form-select <?php echo (!empty($title_err)) ? 'is-invalid' : ''; ?>">
                    <option value="">เลือก</option>
                    <?php 
                        $titles = ['นาย', 'นาง', 'นางสาว', 'ผศ.', 'ดร.'];
                        foreach ($titles as $t) {
                            $selected = ($title == $t) ? 'selected' : '';
                            echo "<option value='{$t}' {$selected}>{$t}</option>";
                        }
                    ?>
                </select>
                <div class="invalid-feedback"><?php echo $title_err; ?></div>
            </div>

            <div class="mb-3">
                <label for="fname" class="form-label">ชื่อจริง</label>
                <input type="text" name="fname" id="fname" class="form-control <?php echo (!empty($fname_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($fname); ?>">
                <div class="invalid-feedback"><?php echo $fname_err; ?></div>
            </div>

            <div class="mb-3">
                <label for="lname" class="form-label">นามสกุล</label>
                <input type="text" name="lname" id="lname" class="form-control <?php echo (!empty($lname_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($lname); ?>">
                <div class="invalid-feedback"><?php echo $lname_err; ?></div>
            </div>
            
            <div class="mb-3">
                <label for="role" class="form-label">สิทธิ์การใช้งาน (Role)</label>
                <select id="role" name="role" class="form-select <?php echo (!empty($role_err)) ? 'is-invalid' : ''; ?>">
                    <option value="user" <?php echo ($role == 'user') ? 'selected' : ''; ?>>user</option>
                    <option value="admin" <?php echo ($role == 'admin') ? 'selected' : ''; ?>>admin</option>
                </select>
                <div class="invalid-feedback"><?php echo $role_err; ?></div>
            </div>

            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> บันทึกการแก้ไข</button>
                <a href="manage_users.php" class="btn btn-secondary"><i class="bi bi-arrow-left-circle"></i> ยกเลิก</a>
            </div>
        </form>
    </div>
</body>
</html>