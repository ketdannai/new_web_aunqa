<?php
// ไฟล์: course/edit_course.php
session_start();
require_once "../config.php";

if(!isset($_SESSION["loggedin"])){ header("location: ../login/login.php"); exit; }

$course_id = $_GET['id'];
$logged_in_user_id = $_SESSION["use_id"];
$is_admin = ($_SESSION["use_role"] == 'admin');

// ดึงข้อมูลวิชาและชื่อเจ้าของ
$sql = "SELECT c.*, u.use_title, u.use_fname, u.use_lname 
        FROM course c 
        INNER JOIN users u ON c.use_id = u.use_id 
        WHERE c.course_id = ?";
$stmt = $link->prepare($sql);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();

// ตรวจสอบสิทธิ์ (ต้องเป็นเจ้าของ หรือ เป็น Admin)
if (!$course || (!$is_admin && $course['use_id'] != $logged_in_user_id)) {
    header("location: course.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แก้ไขรายวิชา</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600&display=swap" rel="stylesheet">
    <style> body { font-family: 'Kanit', sans-serif; background-color: #222; padding-top: 50px; } .card { border-radius: 12px; } </style>
</head>
<body>
<div class="container" style="max-width: 700px;">
    <div class="card shadow-lg border-0">
        <div class="card-header bg-warning fw-bold py-3">แก้ไขข้อมูลรายวิชา</div>
        <form action="update_course.php" method="POST" class="card-body p-4">
            <input type="hidden" name="course_id" value="<?php echo $course['course_id']; ?>">
            
            <div class="mb-4">
                <label class="form-label fw-bold">ผู้รับผิดชอบงาน (แก้ไขไม่ได้)</label>
                <input type="text" class="form-control bg-light" 
                       value="<?php echo $course['use_title'].$course['use_fname']." ".$course['use_lname']; ?>" readonly>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="fw-bold">รหัสวิชา</label>
                    <input type="text" name="course_code" class="form-control" value="<?php echo $course['course_code']; ?>" required>
                </div>
                <div class="col-md-8">
                    <label class="fw-bold">ชื่อวิชา</label>
                    <input type="text" name="course_name" class="form-control" value="<?php echo $course['course_name']; ?>" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="fw-bold">หน่วยกิต</label>
                <input type="number" name="course_credit" class="form-control" value="<?php echo $course['course_credit']; ?>" required>
            </div>

            <div class="text-end border-top pt-3">
                <a href="course.php" class="btn btn-secondary me-2">ยกเลิก</a>
                <button type="submit" name="update_course" class="btn btn-primary px-4 fw-bold">บันทึกการแก้ไข</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>