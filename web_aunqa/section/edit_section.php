<?php
session_start();
require_once "../config.php";

$sec_id = $_GET['id'];
$logged_in_user_id = $_SESSION["use_id"];
$is_admin = ($_SESSION["use_role"] == 'admin');

$sql = "SELECT s.*, u.use_title, u.use_fname, u.use_lname FROM section s INNER JOIN users u ON s.use_id = u.use_id WHERE s.section_id = ?";
$stmt = $link->prepare($sql);
$stmt->bind_param("i", $sec_id);
$stmt->execute();
$sec = $stmt->get_result()->fetch_assoc();

if (!$sec || (!$is_admin && $sec['use_id'] != $logged_in_user_id)) {
    header("location: section.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8"><title>แก้ไขกลุ่มเรียน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600&display=swap" rel="stylesheet">
    <style> body { font-family: 'Kanit', sans-serif; background-color: #222222; padding-top: 50px; } .card { border-radius: 12px; } </style>
</head>
<body>
<div class="container" style="max-width: 600px;">
    <div class="card shadow-lg border-0">
        <div class="card-header bg-warning fw-bold py-3 text-center">แก้ไขข้อมูลกลุ่มเรียน</div>
        <form action="update_section.php" method="POST" class="card-body p-4">
            <input type="hidden" name="section_id" value="<?php echo $sec['section_id']; ?>">
            <div class="mb-4"><label class="fw-bold">ผู้รับผิดชอบงาน</label><input type="text" class="form-control bg-light" value="<?php echo $sec['use_title'].$sec['use_fname']." ".$sec['use_lname']; ?>" readonly></div>
            <div class="mb-3"><label class="fw-bold">ชื่อกลุ่มเรียน</label><input type="text" name="section_name" class="form-control" value="<?php echo htmlspecialchars($sec['section_name']); ?>" required></div>
            <div class="row mb-4">
                <div class="col-md-6"><label class="fw-bold">จำนวนคน (section_num)</label><input type="number" name="section_num" class="form-control" value="<?php echo $sec['section_num']; ?>" required></div>
                <div class="col-md-6"><label class="fw-bold">ปีการศึกษา</label><input type="number" name="section_year" class="form-control" value="<?php echo $sec['section_year']; ?>" required></div>
            </div>
            <div class="text-end border-top pt-3"><a href="section.php" class="btn btn-secondary me-2">ยกเลิก</a><button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">บันทึกการแก้ไข</button></div>
        </form>
    </div>
</div>
</body>
</html>