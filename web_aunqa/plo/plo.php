<?php
// ไฟล์: plo/plo.php
session_start();
require_once "../config.php";

// 1. ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login/login.php");
    exit;
}

// 2. ข้อมูลผู้ใช้จาก Session
$full_name = htmlspecialchars($_SESSION["use_title"] . $_SESSION["use_fname"] . " " . $_SESSION["use_lname"]);
$user_role = htmlspecialchars($_SESSION["use_role"] ?? 'user');
$is_admin = ($user_role == 'admin');

// รายการตัวเลือกสำหรับ Bloom's Taxonomy
$bloom_options = ["R" => "Remember", "U" => "Understand", "Ap" => "Apply", "An" => "Analyze", "Ev" => "Evaluate", "C" => "Create"];

// 3. ดึงข้อมูล PLO ทั้งหมด
$plo_list = [];
$sql = "SELECT * FROM plo ORDER BY plo_id ASC";
if ($result = $link->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $plo_list[] = $row;
    }
    $result->free();
}

$success_message = $_SESSION["plo_success"] ?? null;
unset($_SESSION["plo_success"]);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PLO | AUN-QA System Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --bg-dark: #222222;
            --bg-content: #ffffff;
            --accent-blue: #007bff;
            --sidebar-link-bg: #343a40;
            --sidebar-active: #cce0ff;
            --text-light: #f8f9fa;
        }
        body { font-family: 'Kanit', sans-serif; background-color: var(--bg-dark); margin: 0; }
        .main-container { min-height: 100vh; display: flex; flex-direction: column; }
        .main-header { background-color: var(--accent-blue); color: white; padding: 15px 20px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2); font-weight: 600; }
        .header-top { display: flex; justify-content: space-between; align-items: center; }
        .btn-logout { background-color: #f8f9fa; color: #212529; border: none; font-weight: 600; padding: 5px 15px; border-radius: 3px; text-decoration: none; }
        
        .content-area { display: flex; flex-grow: 1; }
        
        /* Sidebar Styles */
        .sidebar { width: 250px; background-color: var(--bg-dark); padding: 0; flex-shrink: 0; }
        .sidebar .nav-link { 
            color: var(--text-light); padding: 12px 15px; margin-bottom: 1px; font-size: 1.05rem; 
            background-color: var(--sidebar-link-bg); text-decoration: none; display: block;
            transition: 0.2s;
        }
        .sidebar .nav-link:hover { background-color: #495057; }
        .sidebar .nav-link.active { background-color: var(--sidebar-active); color: #212529; font-weight: 600; }
        
        /* Content Styles */
        .content { flex-grow: 1; padding: 40px; background-color: var(--bg-content); color: #343a40; min-height: 100vh; }
        .content h1 { color: var(--accent-blue); font-weight: 700; font-size: 2rem; margin-bottom: 25px; }

        /* การจัดการตารางให้ข้อมูลพอดีกัน */
        .table-standard {
            table-layout: fixed; /* บังคับสัดส่วนคอลัมน์ */
            width: 100%;
        }
        .table-standard thead th { 
            background-color: var(--accent-blue); 
            color: white; 
            text-align: center; 
            vertical-align: middle; 
            padding: 15px 10px;
        }
        .table-standard td { 
            vertical-align: top; 
            padding: 15px !important; 
            word-wrap: break-word; /* ตัดคำอัตโนมัติ */
            overflow-wrap: break-word;
            white-space: normal;
            line-height: 1.6;
        }

        /* กำหนดความกว้างคอลัมน์ % */
        .col-plo { width: 30%; } 
        .col-spec { width: 25%; }
        .col-gen { width: 25%; }
        .col-bloom { width: 10%; text-align: center; }
        .col-action { width: 10%; text-align: center; }

        /* Modal Card Selection */
        .category-card { cursor: pointer; border: 2px solid #eee; border-radius: 10px; padding: 15px; transition: 0.2s; }
        .category-card:hover { border-color: var(--accent-blue); background-color: #f8fbff; }
        .form-check-input:checked + .category-label .category-card { border-color: var(--accent-blue); background-color: #e7f1ff; }
    </style>
</head>
<body>

<div class="main-container">
    <div class="main-header">
         <div class="header-top">
            <p class="mb-0"><i class="bi bi-person-circle me-2"></i>ยินดีต้อนรับ: <?php echo $full_name; ?></p>
            <a href="../login/logout.php" class="btn btn-sm btn-logout">logout</a>
        </div>
    </div>

    <div class="content-area">
        <div class="sidebar">
            <div class="nav flex-column">
                <a class="nav-link" href="../dashboard.php">หน้าแรก</a>
                <a class="nav-link" href="../profile/profile.php">ข้อมูลส่วนตัว</a>
                <a class="nav-link" href="../teacher/teacher.php">อาจารย์</a>
                <a class="nav-link" href="../course/course.php">รายวิชา</a>
                <a class="nav-link" href="../opencourse/opencourse.php">รายวิชาเปิด</a>
                <a class="nav-link" href="../section/section.php">กลุ่มเรียน</a>
                <a class="nav-link" href="../article/article.php">บทความ</a>
                <a class="nav-link" href="../research/research.php">วิจัย</a>
                <a class="nav-link" href="../development/development.php">พัฒนานักศึกษา</a>
                <a class="nav-link active" href="plo.php">PLO</a>
                <a class="nav-link" href="../clo/clo.php">CLO</a>
                <a class="nav-link" href="../services/services.php">งานบริการวิชาการ</a>
                <a class="nav-link" href="../laboratory/laboratory.php">ห้องปฏิบัติการ</a>
                <?php if ($is_admin): ?>
                    <a class="nav-link" href="../manage_users.php">
                        <i class="bi bi-people-fill me-2"></i> จัดการผู้ใช้งาน
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="content">
            <h1>PLO</h1>
            <?php if ($success_message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> <?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm border-0 p-4">
                <?php if ($is_admin): ?>
                    <div class="mb-4">
                        <button class="btn btn-primary px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#addPloModal">
                            <i class="bi bi-plus-circle me-2"></i>เพิ่ม PLO ใหม่
                        </button>
                    </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-standard">
                        <thead>
                            <tr>
                                <th class="col-plo">PLO</th>
                                <th class="col-spec">ความรู้และทักษะเฉพาะทาง</th>
                                <th class="col-gen">ความรู้และทักษะทั่วไป</th>
                                <th class="col-bloom">Bloom's Taxonomy</th>
                                <?php if ($is_admin): ?><th class="col-action">ดำเนินการ</th><?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($plo_list) > 0): ?>
                                <?php foreach ($plo_list as $plo): ?>
                                    <tr>
                                        <td class="fw-bold text-primary"><?php echo nl2br(htmlspecialchars($plo['plo_code'])); ?></td>
                                        <td><?php echo nl2br(htmlspecialchars($plo['plo_knowledge'])); ?></td>
                                        <td><?php echo nl2br(htmlspecialchars($plo['plo_skill'])); ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($plo['plo_bty']); ?></span>
                                        </td>
                                        <?php if ($is_admin): ?>
                                            <td class="text-center">
                                                <div class="btn-group">
                                                    <a href="edit_plo.php?id=<?php echo $plo['plo_id']; ?>" class="btn btn-warning btn-sm"><i class="bi bi-pencil-fill"></i></a>
                                                    <a href="delete_plo.php?id=<?php echo $plo['plo_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('ยืนยันการลบ?');"><i class="bi bi-trash-fill"></i></a>
                                                </div>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center py-5 text-muted">ไม่พบข้อมูลในระบบ</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($is_admin): ?>
<div class="modal fade" id="addPloModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-plus-lg me-2"></i>บันทึกข้อมูล PLO</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="process_plo.php" method="POST">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">รายละเอียด PLO (ข้อความยาว)</label>
                        <textarea name="plo_code" class="form-control" rows="4" placeholder="กรอกรายละเอียด PLO ที่นี่..." required></textarea>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold">Bloom's Taxonomy (เลือกได้หลายระดับ)</label>
                        <div class="d-flex flex-wrap gap-2 p-2 border rounded bg-light">
                            <?php foreach($bloom_options as $key => $val): ?>
                                <div class="form-check me-2">
                                    <input class="form-check-input" type="checkbox" name="blooms[]" value="<?php echo $key; ?>" id="bl_<?php echo $key; ?>">
                                    <label class="form-check-label" for="bl_<?php echo $key; ?>"><?php echo $key; ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">เลือกประเภทและกรอกรายละเอียดทักษะ</label>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <input type="radio" class="form-check-input d-none" name="plo_type" id="type_spec" value="specific" required>
                                <label class="category-label w-100" for="type_spec">
                                    <div class="category-card">
                                        <div class="fw-bold text-primary mb-2">ความรู้และทักษะเฉพาะทาง</div>
                                        <textarea name="text_spec" class="form-control" rows="3" placeholder="กรอกรายละเอียดเฉพาะทาง..."></textarea>
                                    </div>
                                </label>
                            </div>
                            <div class="col-md-6">
                                <input type="radio" class="form-check-input d-none" name="plo_type" id="type_gen" value="general">
                                <label class="category-label w-100" for="type_gen">
                                    <div class="category-card">
                                        <div class="fw-bold text-secondary mb-2">ความรู้และทักษะทั่วไป</div>
                                        <textarea name="text_gen" class="form-control" rows="3" placeholder="กรอกรายละเอียดทั่วไป..."></textarea>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-link text-muted" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" name="save_plo" class="btn btn-primary px-5 shadow-sm">บันทึกข้อมูล</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>