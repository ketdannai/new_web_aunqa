<?php
session_start();
require_once '../config.php';
$conn = $link;

// เตรียมชื่อผู้สอนจาก Session เพื่อแสดงใน Input (แบบ Readonly)
$teacher_name = $_SESSION['use_title'] . $_SESSION['use_fname'] . " " . $_SESSION['use_lname'];
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เพิ่มรายวิชา</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card shadow border-0">
            <div class="card-header bg-success text-white">
                <h5>เพิ่มรายวิชาใหม่</h5>
            </div>
            <div class="card-body">
                <form action="process_course.php" method="POST">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">ผู้รับผิดชอบรายวิชา</label>
                        <input type="text" class="form-control bg-light" value="<?php echo $teacher_name; ?>" readonly>
                        <div class="form-text text-muted">ระบบจะบันทึกเป็นชื่อบัญชีของคุณโดยอัตโนมัติ</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label>รหัสวิชา</label>
                            <input type="text" name="course_code" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label>ชื่อวิชา</label>
                            <input type="text" name="course_name" class="form-control" required>
                        </div>
                        <div class="col-md-2">
                            <label>หน่วยกิต</label>
                            <input type="number" name="course_credit" class="form-control" required>
                        </div>
                    </div>
                    
                    <a href="course.php" class="btn btn-secondary">ยกเลิก</a>
                    <button type="submit" name="save_course" class="btn btn-success">บันทึกข้อมูล</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>