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

// กำหนดตัวแปรสำหรับรับค่าจากฟอร์มและข้อผิดพลาด
$username = $title = $fname = $lname = $password = $confirm_password = "";
$username_err = $title_err = $fname_err = $lname_err = $password_err = $confirm_password_err = "";
$param_role = 'user'; // กำหนดค่าเริ่มต้นเป็น 'user'

// ประมวลผลเมื่อมีการส่งข้อมูลฟอร์ม
if ($_SERVER["REQUEST_METHOD"] == "POST") {
 
    // ************************
    // 1. ตรวจสอบ Username 
    // ************************
    if (empty(trim($_POST["username"] ?? ''))) {
        $username_err = "กรุณากรอกชื่อผู้ใช้";
    } else {
        $username = trim($_POST["username"]);
        
        // ตรวจสอบความพร้อมของฐานข้อมูลและ $link
        if (isset($link) && $link !== false) {
            
            // เตรียมคำสั่ง SQL เพื่อตรวจสอบว่า Username มีอยู่แล้วหรือไม่
            // *** ใช้ชื่อคอลัมน์ 'username' ตามฐานข้อมูล ***
            $sql = "SELECT use_id FROM users WHERE username = ?";
            
            if ($stmt = $link->prepare($sql)) {
                
                // Bind parameters
                $stmt->bind_param("s", $username);
                
                if ($stmt->execute()) {
                    $stmt->store_result();
                    
                    if ($stmt->num_rows == 1) {
                        $username_err = "ชื่อผู้ใช้นี้มีผู้ลงทะเบียนแล้ว";
                    }
                } else {
                    echo "เกิดข้อผิดพลาดในการตรวจสอบชื่อผู้ใช้: " . $stmt->error;
                }
                
                $stmt->close();
            } else {
                echo "เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL: " . $link->error;
            }
        } else {
             $username_err = "ไม่สามารถเชื่อมต่อฐานข้อมูลได้ กรุณาติดต่อผู้ดูแลระบบ";
        }
    }
    
    // ************************
    // 2. ตรวจสอบข้อมูลส่วนตัว (คำนำหน้า, ชื่อ, นามสกุล)
    // ************************
    $title = trim($_POST["title"] ?? '');
    if (empty($title)) {
        $title_err = "กรุณาเลือกคำนำหน้า";
    }
    
    $fname = trim($_POST["fname"] ?? '');
    if (empty($fname)) {
        $fname_err = "กรุณากรอกชื่อจริง";
    }
    
    $lname = trim($_POST["lname"] ?? '');
    if (empty($lname)) {
        $lname_err = "กรุณากรอกนามสกุล";
    }

    // ************************
    // 3. ตรวจสอบ Password
    // ************************
    $password = $_POST["password"] ?? '';
    if (empty($password)) {
        $password_err = "กรุณากรอกรหัสผ่าน";     
    } elseif (strlen($password) < 6) {
        $password_err = "รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร";
    }
    
    // ************************
    // 4. ตรวจสอบ Confirm Password
    // ************************
    $confirm_password = $_POST["confirm_password"] ?? '';
    if (empty($confirm_password)) {
        $confirm_password_err = "กรุณายืนยันรหัสผ่าน";     
    } elseif ($password != $confirm_password) {
        $confirm_password_err = "รหัสผ่านไม่ตรงกัน";
    }
    
    // ************************
    // 5. บันทึกข้อมูลลงฐานข้อมูล (เมื่อไม่มีข้อผิดพลาด)
    // ************************
    if (empty($username_err) && empty($title_err) && empty($fname_err) && empty($lname_err) && empty($password_err) && empty($confirm_password_err)) {
        
        // ตรวจสอบความพร้อมของฐานข้อมูลและ $link อีกครั้งก่อนบันทึก
        if (isset($link) && $link !== false) {
            
            // เตรียมคำสั่ง SQL สำหรับ Insert
            // *** ใช้ชื่อคอลัมน์ที่ถูกต้องทั้งหมด: username, password, use_title, use_fname, use_lname, use_role ***
            $sql = "INSERT INTO users (username, password, use_title, use_fname, use_lname, use_role) VALUES (?, ?, ?, ?, ?, ?)";
            
            if ($stmt_insert = $link->prepare($sql)) {
                
                // Hash รหัสผ่านก่อนบันทึก
                $param_password = password_hash($password, PASSWORD_DEFAULT); 
                
                // Bind 6 parameters: (username, password, title, fname, lname, role)
                $stmt_insert->bind_param("ssssss", $username, $param_password, $title, $fname, $lname, $param_role);
                
                // ดำเนินการ
                if ($stmt_insert->execute()) {
                    // ลงทะเบียนสำเร็จ ไปหน้า Login
                    $_SESSION["login_success"] = "ลงทะเบียนสำเร็จแล้ว! กรุณาเข้าสู่ระบบ";
                    header("location: login.php");
                    exit;
                } else {
                    echo "<div class='alert alert-danger'>เกิดข้อผิดพลาดในการบันทึกข้อมูล: " . $stmt_insert->error . "</div>";
                }

                $stmt_insert->close();
            } else {
                echo "<div class='alert alert-danger'>เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL: " . $link->error . "</div>";
            }
        }
    }
    
    // ปิดการเชื่อมต่อเมื่อทำงานเสร็จ (ถ้า $link ถูกสร้างสำเร็จ)
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
    <title>ลงทะเบียน | AUN-QA System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        /* CSS สำหรับหน้า Register */
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
        .register-container {
            width: 100%;
            max-width: 500px;
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
        .btn-register-submit {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            font-weight: 600;
        }
        .form-control.is-invalid {
            border-color: #dc3545;
        }
        .invalid-feedback {
            font-size: 0.875em;
        }
    </style>
</head>
<body>

<div class="register-container">
    <h3 class="header-title"><i class="bi bi-person-plus-fill me-2"></i>ลงทะเบียนผู้ใช้งานใหม่</h3>
    <p class="text-center text-muted">กรุณากรอกข้อมูลเพื่อสร้างบัญชีผู้ใช้งาน</p>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        
        <div class="row">
             <div class="col-md-4 mb-3">
                <label for="title" class="form-label">คำนำหน้า</label>
                <select id="title" name="title" class="form-select <?php echo (!empty($title_err)) ? 'is-invalid' : ''; ?>">
                    <option value="">เลือก</option>
                    <option value="นาย" <?php echo ($title == 'นาย') ? 'selected' : ''; ?>>นาย</option>
                    <option value="นาง" <?php echo ($title == 'นาง') ? 'selected' : ''; ?>>นาง</option>
                    <option value="นางสาว" <?php echo ($title == 'นางสาว') ? 'selected' : ''; ?>>นางสาว</option>
                    <option value="อจ." <?php echo ($title == 'อจ.') ? 'selected' : ''; ?>>อจ.</option>
                    <option value="ผศ." <?php echo ($title == 'ผศ.') ? 'selected' : ''; ?>>ผศ.</option>
                    <option value="ดร." <?php echo ($title == 'ดร.') ? 'selected' : ''; ?>>ดร.</option>
                    </select>
                <div class="invalid-feedback"><?php echo $title_err; ?></div>
            </div>
            <div class="col-md-8 mb-3">
                <label for="fname" class="form-label">ชื่อจริง</label>
                <input type="text" name="fname" id="fname" class="form-control <?php echo (!empty($fname_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($fname); ?>">
                <div class="invalid-feedback"><?php echo $fname_err; ?></div>
            </div>
            <div class="col-12 mb-3">
                <label for="lname" class="form-label">นามสกุล</label>
                <input type="text" name="lname" id="lname" class="form-control <?php echo (!empty($lname_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($lname); ?>">
                <div class="invalid-feedback"><?php echo $lname_err; ?></div>
            </div>
        </div>

        <div class="mb-3">
            <label for="username" class="form-label">ชื่อผู้ใช้ (Username)</label>
            <input type="text" name="username" id="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($username); ?>">
            <div class="invalid-feedback"><?php echo $username_err; ?></div>
        </div>
        
        <div class="mb-3">
            <label for="password" class="form-label">รหัสผ่าน</label>
            <input type="password" name="password" id="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
            <span class="invalid-feedback"><?php echo $password_err; ?></span>
        </div>
        
        <div class="mb-4">
            <label for="confirm_password" class="form-label">ยืนยันรหัสผ่าน</label>
            <input type="password" name="confirm_password" id="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>">
            <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
        </div>
        
        <div class="d-grid mb-3">
            <button type="submit" class="btn btn-register-submit btn-lg">ลงทะเบียน</button>
        </div>

        <p class="text-center mt-3">มีบัญชีผู้ใช้แล้ว? <a href="login.php">เข้าสู่ระบบที่นี่</a></p>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>