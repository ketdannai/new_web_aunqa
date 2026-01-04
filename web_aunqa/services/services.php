<?php
// ไฟล์: services/services.php
session_start();
require_once "../config.php";

// 1. ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login/login.php");
    exit;
}

// 2. ดึงข้อมูลจาก Session
$logged_in_user_id = $_SESSION["use_id"];
$full_name = htmlspecialchars($_SESSION["use_title"] . $_SESSION["use_fname"] . " " . $_SESSION["use_lname"]);
$user_role = htmlspecialchars($_SESSION["use_role"] ?? 'user');
$is_admin = ($user_role == 'admin');

// 3. ดึงข้อมูลงานบริการวิชาการ (Join กับ Users เพื่อแสดงชื่อผู้รับผิดชอบ)
$services = [];
$sql = "SELECT s.*, CONCAT(u.use_title, u.use_fname, ' ', u.use_lname) AS owner_name 
        FROM services s
        LEFT JOIN users u ON s.use_id = u.use_id
        ORDER BY s.serv_id DESC";

if ($result = $link->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $services[] = $row;
    }
    $result->free();
}

// ฟังก์ชันสำหรับเช็ค Active Menu
function is_active($target_file) {
    return (basename($_SERVER['PHP_SELF']) == $target_file) ? 'active' : '';
}

$success_message = $_SESSION["serv_success"] ?? null;
unset($_SESSION["serv_success"]);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>งานบริการวิชาการ | AUN-QA System Dashboard</title>
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
        
        /* Header Bar */
        .main-header { background-color: var(--accent-blue); color: white; padding: 15px 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.2); font-weight: 600; }
        .header-top { display: flex; justify-content: space-between; align-items: center; }
        .btn-logout { background-color: #f8f9fa; color: #212529; border: none; font-weight: 600; padding: 5px 15px; border-radius: 3px; text-decoration: none; }

        /* Sidebar สไตล์ Dashboard */
        .content-area { display: flex; flex-grow: 1; }
        .sidebar { width: 250px; background-color: var(--bg-dark); padding: 0; flex-shrink: 0; }
        .sidebar .nav-link { 
            color: var(--text-light); padding: 12px 15px; margin-bottom: 1px; font-size: 1.05rem; transition: background-color 0.2s;
            background-color: var(--sidebar-link-bg); box-shadow: 1px 0 0 rgba(0, 0, 0, 0.2) inset, 0 1px 0 rgba(0, 0, 0, 0.2); 
            text-decoration: none; display: block;
        }
        .sidebar .nav-link:hover { background-color: #495057; color: white; }
        .sidebar .nav-link.active { background-color: var(--sidebar-active); color: #212529; font-weight: 600; }

        /* Content Area */
        .content { flex-grow: 1; padding: 40px; background-color: var(--bg-content); color: #343a40; box-shadow: -5px 0 10px rgba(0,0,0,0.1); min-height: 100vh; }
        .content h1 { color: var(--accent-blue); font-weight: 700; font-size: 2rem; }
        
        .table-standard thead th { background-color: var(--accent-blue); color: white; text-align: center; border: 1px solid #dee2e6; padding: 12px; }
        .table-standard td { border: 1px solid #dee2e6; vertical-align: middle; }
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
                <a class="nav-link" href="../research/research.php">วิจัย</a>
                <a class="nav-link" href="../development/development.php">พัฒนานักศึกษา</a>
                <a class="nav-link" href="../plo/plo.php">PLO</a>
                <a class="nav-link" href="../clo/clo.php">CLO</a>
                <a class="nav-link active" href="services.php">งานบริการวิชาการ</a>
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
            <h1>งานบริการวิชาการ</h1>
            <?php if ($success_message): ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> <?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow-lg p-4 border-0">
                <div class="card-body">
                    <div class="mb-4">
                        <button class="btn btn-primary shadow-sm" onclick="openServiceModal('add')" style="background-color: #0056b3; font-weight: 600;">
                            <i class="bi bi-plus-circle me-1"></i> เพิ่มงานบริการวิชาการใหม่
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover table-standard">
                            <thead>
                                <tr>
                                    <th>ชื่องานบริการวิชาการ</th>
                                    <th>ผู้รับผิดชอบ</th>
                                    <th class="text-center" style="width: 120px;">ดำเนินการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($services) > 0): ?>
                                    <?php foreach ($services as $serv): 
                                        $is_owner = ($serv['use_id'] == $logged_in_user_id);
                                    ?>
                                        <tr>
                                            <td class="fw-bold text-primary"><?php echo htmlspecialchars($serv['serv_name']); ?></td>
                                            <td><?php echo htmlspecialchars($serv['owner_name']); ?></td>
                                            <td class="text-center">
                                                <?php if ($is_admin || $is_owner): ?>
                                                    <div class="d-flex justify-content-center gap-1">
                                                        <button class="btn btn-warning btn-sm shadow-sm" onclick='openServiceModal("edit", <?php echo json_encode($serv); ?>)'>
                                                            <i class="bi bi-pencil-fill"></i>
                                                        </button>
                                                        <a href="process_service.php?delete=<?php echo $serv['serv_id']; ?>" class="btn btn-danger btn-sm shadow-sm" onclick="return confirm('ยืนยันการลบข้อมูล?')">
                                                            <i class="bi bi-trash-fill"></i>
                                                        </a>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted"><i class="bi bi-lock-fill"></i></span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="3" class="text-center py-4">ไม่พบข้อมูล</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="serviceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="serviceModalTitle">กรอกข้อมูลงานบริการวิชาการ</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="process_service.php" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="action" id="servAction" value="add">
                    <input type="hidden" name="serv_id" id="servId">

                    <div class="mb-4">
                        <label class="form-label fw-bold">ผู้รับผิดชอบงาน</label>
                        <input type="text" class="form-control bg-light" value="<?php echo $full_name; ?>" readonly>
                        <input type="hidden" name="use_id" value="<?php echo $logged_in_user_id; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="serv_name" class="form-label fw-bold">ชื่องานบริการวิชาการ</label>
                        <textarea name="serv_name" id="servName" class="form-control" rows="4" placeholder="ระบุชื่องานบริการวิชาการ..." required></textarea>
                    </div>
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
const serviceModal = new bootstrap.Modal(document.getElementById('serviceModal'));

function openServiceModal(mode, data = null) {
    document.getElementById('servAction').value = mode;
    if (mode === 'add') {
        document.getElementById('serviceModalTitle').innerText = 'เพิ่มงานบริการวิชาการใหม่';
        document.getElementById('servId').value = '';
        document.getElementById('servName').value = '';
    } else {
        document.getElementById('serviceModalTitle').innerText = 'แก้ไขข้อมูลงานบริการวิชาการ';
        document.getElementById('servId').value = data.serv_id;
        document.getElementById('servName').value = data.serv_name;
    }
    serviceModal.show();
}
</script>
</body>
</html>