<?php
session_start();
require_once "../config.php";

$id = $_GET['id'];
$row = $link->query("SELECT * FROM laboratory WHERE lab_id = '$id'")->fetch_assoc();

// เช็คสิทธิ์: ต้องเป็น Admin หรือเจ้าของข้อมูล
if ($_SESSION['use_role'] !== 'admin' && $_SESSION['use_id'] != $row['use_id']) {
    header("location: laboratory.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แก้ไขห้องปฏิบัติการ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-5">
    <div class="container" style="max-width: 600px;">
        <div class="card shadow border-0">
            <div class="card-header bg-warning fw-bold">แก้ไขข้อมูลห้องปฏิบัติการ</div>
            <form action="update_lab.php" method="POST" class="card-body">
                <input type="hidden" name="lab_id" value="<?php echo $row['lab_id']; ?>">
                <div class="mb-3"><label>ชื่อห้อง</label><input type="text" name="lab_name" class="form-control" value="<?php echo $row['lab_name']; ?>" required></div>
                <div class="mb-3"><label>จำนวน</label><input type="text" name="lab_num" class="form-control" value="<?php echo $row['lab_num']; ?>" required></div>
                <div class="mb-3"><label>ครุภัณฑ์</label><textarea name="lab_durable" class="form-control" rows="3"><?php echo $row['lab_durable']; ?></textarea></div>
                <div class="mb-3"><label>สถานะ</label>
                    <select name="lab_status" class="form-select">
                        <option <?php echo ($row['lab_status']=='พร้อมใช้งาน')?'selected':''; ?>>พร้อมใช้งาน</option>
                        <option <?php echo ($row['lab_status']=='ไม่พร้อมใช้งาน')?'selected':''; ?>>ไม่พร้อมใช้งาน</option>
                    </select>
                </div>
                <div class="text-end"><a href="laboratory.php" class="btn btn-secondary">ยกเลิก</a> <button type="submit" class="btn btn-warning">บันทึก</button></div>
            </form>
        </div>
    </div>
</body>
</html>