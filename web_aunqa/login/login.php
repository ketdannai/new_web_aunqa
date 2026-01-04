<?php
// เริ่มต้น Session
session_start();

// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบแล้วหรือไม่ (ถ้าเข้าสู่ระบบแล้ว ให้ไปที่หน้า Dashboard)
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: ../dashboard.php");
    exit;
}

// นำเข้าไฟล์เชื่อมต่อฐานข้อมูล (ต้องใช้ ../ เพื่อย้อนกลับไปที่โฟลเดอร์หลัก)
require_once "../config.php"; 

// กำหนดตัวแปร
$username = $password = "";
$username_err = $password_err = $login_err = "";
$success_message = $_SESSION["login_success"] ?? ''; // ดึงข้อความแจ้งเตือนจากการลงทะเบียนสำเร็จ
unset($_SESSION["login_success"]); // ล้างข้อความแจ้งเตือน

// ประมวลผลเมื่อมีการส่งข้อมูลฟอร์ม
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. ตรวจสอบ Username
    if (empty(trim($_POST["username"] ?? ''))) {
        $username_err = "กรุณากรอกชื่อผู้ใช้";
    } else {
        $username = trim($_POST["username"]);
    }

    // 2. ตรวจสอบ Password
    if (empty(trim($_POST["password"] ?? ''))) {
        $password_err = "กรุณากรอกรหัสผ่าน";
    } else {
        $password = trim($_POST["password"]);
    }

    // 3. ตรวจสอบความผิดพลาดในการกรอกข้อมูลก่อนตรวจสอบในฐานข้อมูล
    if (empty($username_err) && empty($password_err)) {
        
        // ตรวจสอบความพร้อมของฐานข้อมูลและ $link
        if (isset($link) && $link !== false) {
            
            // เตรียมคำสั่ง SQL เพื่อดึงข้อมูลผู้ใช้
            // *** แก้ไข: ใช้คอลัมน์ 'username' ตามฐานข้อมูล ***
            $sql = "SELECT use_id, username, password, use_title, use_fname, use_lname, use_role FROM users WHERE username = ?";
            
            // *** แก้ไข Fatal Error: เปลี่ยน $mysqli เป็น $link ***
            if ($stmt = $link->prepare($sql)) { 
                
                // Bind parameters
                $stmt->bind_param("s", $username);
                
                if ($stmt->execute()) {
                    $stmt->store_result();
                    
                    // ตรวจสอบว่าพบผู้ใช้หรือไม่
                    if ($stmt->num_rows == 1) {
                        // Bind result variables
                        $stmt->bind_result($use_id, $db_username, $hashed_password, $use_title, $use_fname, $use_lname, $use_role);
                        
                        if ($stmt->fetch()) {
                            // ตรวจสอบรหัสผ่านที่กรอกกับรหัสผ่านที่ถูก Hash
                            if (password_verify($password, $hashed_password)) {
                                
                                // รหัสผ่านถูกต้อง บันทึกข้อมูลลงใน Session
                                $_SESSION["loggedin"] = true;
                                $_SESSION["use_id"] = $use_id;
                                $_SESSION["username"] = $db_username;
                                $_SESSION["use_title"] = $use_title;
                                $_SESSION["use_fname"] = $use_fname;
                                $_SESSION["use_lname"] = $use_lname;
                                $_SESSION["use_role"] = $use_role;
                                
                                // Redirect ไปหน้า Dashboard
                                header("location: ../dashboard.php");
                                exit;
                            } else {
                                // รหัสผ่านไม่ถูกต้อง
                                $login_err = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
                            }
                        }
                    } else {
                        // ไม่พบชื่อผู้ใช้
                        $login_err = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
                    }
                } else {
                    $login_err = "เกิดข้อผิดพลาดในการดำเนินการฐานข้อมูล";
                }
                
                $stmt->close();
            } else {
                $login_err = "เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL";
            }
        } else {
             $login_err = "ไม่สามารถเชื่อมต่อฐานข้อมูลได้ กรุณาติดต่อผู้ดูแลระบบ";
        }
    }
    
    // ปิดการเชื่อมต่อเมื่อทำงานเสร็จ
    if (isset($link) && $link !== false) {
        $link->close();
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ | AUN-QA System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        /* CSS สำหรับหน้า Login */
        :root {
            --primary-color: #007bff;
            --secondary-bg: #f8f9fa;
        }
        body { 
            font-family: 'Kanit', sans-serif; 
            background-color: var(--secondary-bg); 
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 30px;
            border-radius: 10px;
            background-color: #ffffff;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .header-title {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 25px;
            font-size: 1.8rem;
            text-align: center;
        }
        .btn-login-submit {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            font-weight: 600;
        }
        .form-control.is-invalid {
            border-color: #dc3545;
        }
        .invalid-feedback, .alert {
            font-size: 0.875em;
        }
    </style>
</head>
<body>

<div class="login-container">
    <h3 class="header-title"><i class="bi bi-box-arrow-in-right me-2"></i>เข้าสู่ระบบ</h3>
    <p class="text-center text-muted">กรุณากรอกชื่อผู้ใช้และรหัสผ่านเพื่อเข้าสู่ระบบ</p>

    <?php 
    if (!empty($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-1"></i><?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; 

    if (!empty($login_err)): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle-fill me-1"></i><?php echo $login_err; ?>
        </div>
    <?php endif; ?>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        
        <div class="mb-3">
            <label for="username" class="form-label">ชื่อผู้ใช้ (Username)</label>
            <input type="text" name="username" id="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($username); ?>">
            <div class="invalid-feedback"><?php echo $username_err; ?></div>
        </div>
        
        <div class="mb-4">
            <label for="password" class="form-label">รหัสผ่าน</label>
            <input type="password" name="password" id="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
            <span class="invalid-feedback"><?php echo $password_err; ?></span>
        </div>
        
        <div class="d-grid mb-3">
            <button type="submit" class="btn btn-login-submit btn-lg">เข้าสู่ระบบ</button>
        </div>

        <p class="text-center mt-3">ยังไม่มีบัญชีผู้ใช้? <a href="register.php">ลงทะเบียนที่นี่</a></p>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>