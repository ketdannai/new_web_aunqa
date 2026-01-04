<?php
// ไฟล์: plo/edit_plo.php
session_start();
require_once "../config.php";

// ตรวจสอบสิทธิ์ Admin เท่านั้น
if (!isset($_SESSION["loggedin"]) || $_SESSION["use_role"] !== 'admin') {
    header("location: plo.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("location: plo.php");
    exit;
}

$plo_id = mysqli_real_escape_string($link, $_GET['id']);
$sql = "SELECT * FROM plo WHERE plo_id = '$plo_id'";
$result = $link->query($sql);
$row = $result->fetch_assoc();

if (!$row) {
    echo "ไม่พบข้อมูล";
    exit;
}

// ข้อมูลตัวเลือก (ต้องตรงกับหน้า plo.php)
$bloom_options = ["R" => "Remember", "U" => "Understand", "Ap" => "Apply", "An" => "Analyze", "Ev" => "Evaluate", "C" => "Create"];
$spec_options = ["ความรู้พื้นฐานวิชาชีพ", "ความรู้เฉพาะทาง", "ความรู้ด้านกฎหมายที่เกี่ยวข้อง", "การบูรณาการความรู้", "ความรู้ในสถานการณ์", "การประยุกต์ใช้ทฤษฎี"];
$gen_options = ["ทักษะการสื่อสาร", "ทักษะการใช้เทคโนโลยี (IT)", "การคิดวิเคราะห์ (Critical Thinking)", "การแก้ปัญหา (Problem Solving)", "การทำงานเป็นทีม", "ทักษะภาษาอังกฤษ"];

// แปลงข้อมูลจากฐานข้อมูลเป็น Array เพื่อเช็คค่า
$current_blooms = explode(',', $row['plo_bty'] ?? '');
$current_specs = explode(',', $row['plo_knowledge'] ?? '');
$current_gens = explode(',', $row['plo_skill'] ?? '');
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แก้ไข PLO | AUN-QA System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Kanit', sans-serif; background-color: #f4f7f6; }
        .card { border: none; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .form-label { font-weight: 600; color: #333; }
        .section-title { border-left: 5px solid #007bff; padding-left: 10px; margin-bottom: 15px; color: #007bff; }
    </style>
</head>
<body class="py-5">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0"><i class="bi bi-pencil-square"></i> แก้ไขข้อมูล PLO: <?php echo $row['plo_code']; ?></h5>
                </div>
                <form action="update_plo.php" method="POST">
                    <div class="card-body p-4">
                        <input type="hidden" name="plo_id" value="<?php echo $row['plo_id']; ?>">

                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="form-label">รหัส PLO</label>
                                <input type="text" name="plo_code" class="form-control" value="<?php echo htmlspecialchars($row['plo_code']); ?>" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h6 class="section-title text-danger" style="border-color: #dc3545;">Bloom's Taxonomy</h6>
                            <div class="d-flex flex-wrap gap-3 p-3 border rounded bg-light">
                                <?php foreach($bloom_options as $key => $val): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="blooms[]" value="<?php echo $key; ?>" id="bl_<?php echo $key; ?>" 
                                        <?php echo in_array($key, $current_blooms) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="bl_<?php echo $key; ?>"><?php echo $val; ?> (<?php echo $key; ?>)</label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <h6 class="section-title">ด้านความรู้และทักษะเฉพาะทาง</h6>
                                <div class="p-3 border rounded bg-light">
                                    <?php foreach($spec_options as $s): ?>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" name="specs[]" value="<?php echo $s; ?>" id="sp_<?php echo md5($s); ?>"
                                            <?php echo in_array($s, $current_specs) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="sp_<?php echo md5($s); ?>"><?php echo $s; ?></label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <h6 class="section-title text-secondary" style="border-color: #6c757d;">ด้านความรู้และทักษะทั่วไป</h6>
                                <div class="p-3 border rounded bg-light">
                                    <?php foreach($gen_options as $g): ?>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" name="gens[]" value="<?php echo $g; ?>" id="ge_<?php echo md5($g); ?>"
                                            <?php echo in_array($g, $current_gens) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="ge_<?php echo md5($g); ?>"><?php echo $g; ?></label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-white text-end py-3">
                        <a href="plo.php" class="btn btn-secondary px-4 me-2">ยกเลิก</a>
                        <button type="submit" name="update_plo" class="btn btn-primary px-4">บันทึกการแก้ไข</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>