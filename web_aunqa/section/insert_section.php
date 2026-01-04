<?php
// section/insert_section.php
session_start();
require_once "../config.php";

if (!isset($_SESSION["loggedin"])) { header("location: ../login/login.php"); exit; }

// เตรียมชื่ออาจารย์ที่จะแสดงในฟอร์ม
$advisor_name = $_SESSION['use_title'] . $_SESSION['use_fname'] . " " . $_SESSION['use_lname'];
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เพิ่มกลุ่มเรียน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600&display=swap" rel="stylesheet">
    <style> body { font-family: 'Kanit', sans-serif; background-color: #f8f9fa; } </style>
</head>
<body>
    <div class="container mt-5">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">กรอกข้อมูลกลุ่มเรียนใหม่</h5>
            </div>
            <div class="card-body">
                <form action="process_section.php" method="POST">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">อาจารย์ที่ปรึกษา (Advisor)</label>
                        <input type="text" class="form-control bg-light" value="<?php echo $advisor_name; ?>" readonly>
                        <div class="form-text">ระบบจะบันทึกเป็นชื่อบัญชีของคุณโดยอัตโนมัติ</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">ชื่อกลุ่มเรียน (Section Name)</label>
                            <input type="text" name="section_name" class="form-control" required placeholder="เช่น กลุ่มเรียนปกติ">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">จำนวน (Section No.)</label>
                            <input type="text" name="section_num" class="form-control" required placeholder="01">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">ปีการศึกษา (Year)</label>
                            <input type="text" name="section_year" class="form-control" required placeholder="2567">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="section.php" class="btn btn-secondary">ยกเลิก</a>
                        <button type="submit" name="save_section" class="btn btn-success">บันทึกข้อมูล</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>