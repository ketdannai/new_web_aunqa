<?php
// ไฟล์: research/research.php
session_start();
require_once "../config.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login/login.php");
    exit;
}

$logged_in_user_id = $_SESSION["use_id"];
$full_name = htmlspecialchars($_SESSION["use_title"] . $_SESSION["use_fname"] . " " . $_SESSION["use_lname"]);
$user_role = htmlspecialchars($_SESSION["use_role"] ?? 'user');
$is_admin = ($user_role == 'admin');

// ดึงข้อมูลวิจัยทั้งหมด
$research_list = [];
$sql = "SELECT * FROM research ORDER BY res_id DESC";
if ($result = $link->query($sql)) {
    while ($row = $result->fetch_assoc()) { $research_list[] = $row; }
    $result->free();
}

$success_message = $_SESSION["res_success"] ?? null;
unset($_SESSION["res_success"]);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>งานวิจัย | AUN-QA System Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root { --bg-dark: #222222; --bg-content: #ffffff; --accent-blue: #007bff; --sidebar-link-bg: #343a40; --sidebar-active: #cce0ff; --text-light: #f8f9fa; }
        body { font-family: 'Kanit', sans-serif; background-color: var(--bg-dark); margin: 0; }
        .main-container { min-height: 100vh; display: flex; flex-direction: column; }
        .main-header { background-color: var(--accent-blue); color: white; padding: 15px 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.2); font-weight: 600; }
        .header-top { display: flex; justify-content: space-between; align-items: center; }
        .btn-logout { background-color: #f8f9fa; color: #212529; border: none; font-weight: 600; padding: 5px 15px; border-radius: 3px; text-decoration: none; }
        .content-area { display: flex; flex-grow: 1; }
        .sidebar { width: 250px; background-color: var(--bg-dark); flex-shrink: 0; }
        .sidebar .nav-link { color: var(--text-light); padding: 12px 15px; margin-bottom: 1px; font-size: 1.05rem; transition: background-color 0.2s; background-color: var(--sidebar-link-bg); text-decoration: none; display: block; box-shadow: 1px 0 0 rgba(0, 0, 0, 0.2) inset, 0 1px 0 rgba(0, 0, 0, 0.2); }
        .sidebar .nav-link:hover { background-color: #495057; }
        .sidebar .nav-link.active { background-color: var(--sidebar-active); color: #212529; font-weight: 600; }
        .content { flex-grow: 1; padding: 40px; background-color: var(--bg-content); color: #343a40; min-height: 100vh; box-shadow: -5px 0 10px rgba(0,0,0,0.1); }
        .table-standard thead th { background-color: var(--accent-blue); color: white; text-align: center; border: 1px solid #dee2e6; padding: 12px; }
        .table-standard td { border: 1px solid #dee2e6; vertical-align: middle; }
        .author-list { font-size: 0.85rem; color: #666; }
    </style>
</head>
<body>

<div class="main-container">
    <div class="main-header">
        <div class="header-top">
            <p class="mb-0">ยินดีต้อนรับ: <?php echo $full_name; ?></p>
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
                <a class="nav-link active" href="research.php">วิจัย</a>
                <a class="nav-link" href="../development/development.php">พัฒนานักศึกษา</a>
                <a class="nav-link" href="../plo/plo.php">PLO</a>
                <a class="nav-link" href="../dashboard.php">CLO</a>
                <a class="nav-link" href="../services/services.php">งานบริการวิชาการ</a>
                <a class="nav-link" href="../laboratory/laboratory.php">ห้องปฏิบัติการ</a>
               <?php
                    // ตรวจสอบว่า Session 'use_role' ถูกตั้งค่าเป็น 'admin' หรือไม่
                    if (isset($_SESSION["use_role"]) && $_SESSION["use_role"] == 'admin'):
                    ?>
                        <a class="nav-link" href="../manage_users.php">
                            <i class="bi bi-people-fill me-2"></i> จัดการผู้ใช้งาน
                        </a>
                    <?php endif; ?>
            </div>
        </div>

        <div class="content">
            <h1>งานวิจัย</h1>
            <?php if ($success_message): ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> <?php echo $success_message; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow-lg p-4 border-0">
                <div class="card-body">
                    <div class="mb-4 text-start">
                        <button class="btn btn-primary shadow-sm px-4" onclick="openResModal('add')" style="background-color: #0056b3;">
                            <i class="bi bi-plus-circle me-1"></i> เพิ่มงานวิจัยใหม่
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover table-standard align-middle">
                            <thead>
                                <tr>
                                    <th>ชื่องานวิจัย</th>
                                    <th>ผู้ทำวิจัย</th>
                                    <th>วันที่</th>
                                    <th>แหล่งเผยแพร่/การประชุม</th>
                                    <th>แหล่งทุน/งบประมาณ</th>
                                    <th class="text-center">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($research_list) > 0): ?>
                                    <?php foreach ($research_list as $res): 
                                        $is_owner = ($res['use_id'] == $logged_in_user_id);
                                        
                                        $authors = [];
                                        for ($i = 1; $i <= 5; $i++) {
                                            if (!empty($res["res_fname$i"])) {
                                                $authors[] = htmlspecialchars($res["res_title$i"] . $res["res_fname$i"] . " " . $res["res_lname$i"]);
                                            }
                                        }
                                        $display_authors = implode(", ", $authors);
                                    ?>
                                        <tr>
                                            <td class="fw-bold text-primary"><?php echo htmlspecialchars($res['res_name']); ?></td>
                                            <td class="author-list small"><?php echo $display_authors; ?></td>
                                            <td class="text-center small"><?php echo htmlspecialchars($res['res_date']); ?></td>
                                            <td class="small"><?php echo htmlspecialchars($res['res_meet'] ?: '-'); ?></td>
                                            <td class="small">
                                                <div>ทุน: <?php echo htmlspecialchars($res['res_capital'] ?: '-'); ?></div>
                                                <div class="text-success fw-bold">งบ: <?php echo htmlspecialchars($res['res_budget'] ?: '0'); ?></div>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($is_admin || $is_owner): ?>
                                                    <div class="d-flex justify-content-center gap-1">
                                                        <button class="btn btn-warning btn-sm shadow-sm" onclick='openResModal("edit", <?php echo json_encode($res); ?>)'><i class="bi bi-pencil-fill"></i></button>
                                                        <a href="process_research.php?delete=<?php echo $res['res_id']; ?>" class="btn btn-danger btn-sm shadow-sm" onclick="return confirm('ยืนยันการลบข้อมูลวิจัย?')"><i class="bi bi-trash-fill"></i></a>
                                                    </div>
                                                <?php else: ?><i class="bi bi-lock-fill text-muted"></i><?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="6" class="text-center py-4">ไม่พบข้อมูลงานวิจัยในระบบ</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="resModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="resModalTitle">กรอกข้อมูลงานวิจัย</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="process_research.php" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="action" id="resAction" value="add">
                    <input type="hidden" name="res_id" id="resId">
                    <input type="hidden" name="use_id" value="<?php echo $logged_in_user_id; ?>">

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">ชื่องานวิจัย (res_name)</label>
                            <input type="text" name="res_name" id="resName" class="form-control" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">ประเภท</label>
                            <input type="text" name="res_type" id="resType" class="form-control" placeholder="เช่น วิจัยสถาบัน" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">วันที่เผยแพร่/ตีพิมพ์</label>
                            <input type="text" name="res_date" id="resDate" class="form-control" placeholder="YYYY-MM-DD" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">แหล่งเผยแพร่/การประชุม</label>
                            <input type="text" name="res_meet" id="resMeet" class="form-control" placeholder="ระบุชื่อการประชุมหรือวารสารที่ตีพิมพ์" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">แหล่งตีพิมพ์สำรอง</label>
                            <input type="text" name="res_publish" id="resPublish" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">แหล่งทุน</label>
                            <input type="text" name="res_capital" id="resCapital" class="form-control">
                        </div>
                        <div class="col-md-4 mb-4">
                            <label class="form-label fw-bold">งบประมาณ</label>
                            <input type="text" name="res_budget" id="resBudget" class="form-control">
                        </div>
                    </div>

                    <div class="alert alert-secondary py-2 fw-bold small"><i class="bi bi-people-fill me-2"></i>ผู้ทำวิจัย</div>
                    
                    <?php for($i=1; $i<=5; $i++): ?>
                    <div class="row g-2 mb-2 align-items-end small">
                        <div class="col-md-1 pb-1 text-center"><span class="badge bg-primary">คนที่ <?php echo $i; ?></span></div>
                        <div class="col-md-2">
                            <label class="small text-muted">คำนำหน้า</label>
                            <input type="text" name="res_title<?php echo $i; ?>" id="res_title<?php echo $i; ?>" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4">
                            <label class="small text-muted">ชื่อจริง</label>
                            <input type="text" name="res_fname<?php echo $i; ?>" id="res_fname<?php echo $i; ?>" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-5">
                            <label class="small text-muted">นามสกุล</label>
                            <input type="text" name="res_lname<?php echo $i; ?>" id="res_lname<?php echo $i; ?>" class="form-control form-control-sm">
                        </div>
                    </div>
                    <?php endfor; ?>

                </div>
                <div class="modal-footer border-0 justify-content-center">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary px-5 shadow-sm">บันทึกข้อมูล</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const resModal = new bootstrap.Modal(document.getElementById('resModal'));
function openResModal(mode, data = null) {
    document.getElementById('resAction').value = mode;
    if (mode === 'add') {
        document.getElementById('resModalTitle').innerText = 'เพิ่มข้อมูลงานวิจัยใหม่';
        document.getElementById('resId').value = '';
        document.getElementById('resName').value = '';
        document.getElementById('resType').value = '';
        document.getElementById('resDate').value = '';
        document.getElementById('resMeet').value = '';
        document.getElementById('resPublish').value = '';
        document.getElementById('resCapital').value = '';
        document.getElementById('resBudget').value = '';
        for(let i=1; i<=5; i++) {
            document.getElementById('res_title'+i).value = '';
            document.getElementById('res_fname'+i).value = '';
            document.getElementById('res_lname'+i).value = '';
        }
    } else {
        document.getElementById('resModalTitle').innerText = 'แก้ไขข้อมูลงานวิจัย';
        document.getElementById('resId').value = data.res_id;
        document.getElementById('resName').value = data.res_name;
        document.getElementById('resType').value = data.res_type;
        document.getElementById('resDate').value = data.res_date;
        document.getElementById('resMeet').value = data.res_meet;
        document.getElementById('resPublish').value = data.res_publish;
        document.getElementById('resCapital').value = data.res_capital;
        document.getElementById('resBudget').value = data.res_budget;
        for(let i=1; i<=5; i++) {
            document.getElementById('res_title'+i).value = data['res_title'+i] || '';
            document.getElementById('res_fname'+i).value = data['res_fname'+i] || '';
            document.getElementById('res_lname'+i).value = data['res_lname'+i] || '';
        }
    }
    resModal.show();
}
</script>
</body>
</html>