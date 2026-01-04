<?php
// ไฟล์: clo/clo.php
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

// 3. ดึงข้อมูล CLO
$sql = "SELECT c.course_code, c.course_name, cl.clo_code, cl.course_id, cl.use_id,
        GROUP_CONCAT(p.plo_code ORDER BY p.plo_id SEPARATOR ' ') AS all_plo_codes,
        GROUP_CONCAT(p.plo_id) AS all_plo_ids
        FROM clo cl
        LEFT JOIN course c ON cl.course_id = c.course_id
        LEFT JOIN plo p ON cl.plo_id = p.plo_id
        GROUP BY cl.course_id, cl.clo_code
        ORDER BY cl.course_id DESC";

$result = $link->query($sql);

// ดึงข้อมูลสำหรับ Dropdown ใน Modal
$courses_res = $link->query("SELECT course_id, course_code, course_name FROM course");
$plos_res = $link->query("SELECT plo_id, plo_code FROM plo");

$success_message = $_SESSION["clo_success"] ?? null;
unset($_SESSION["clo_success"]);
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการ CLO | AUN-QA System Dashboard</title>
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

        body {
            font-family: 'Kanit', sans-serif;
            background-color: var(--bg-dark);
            margin: 0;
        }

        .main-container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .main-header {
            background-color: var(--accent-blue);
            color: white;
            padding: 15px 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            font-weight: 600;
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-logout {
            background-color: #f8f9fa;
            color: #212529;
            border: none;
            font-weight: 600;
            padding: 5px 15px;
            border-radius: 3px;
            text-decoration: none;
        }

        .content-area {
            display: flex;
            flex-grow: 1;
        }

        .sidebar {
            width: 250px;
            background-color: var(--bg-dark);
            padding: 0;
            flex-shrink: 0;
        }

        .sidebar .nav-link {
            color: var(--text-light);
            padding: 12px 15px;
            margin-bottom: 1px;
            font-size: 1.05rem;
            background-color: var(--sidebar-link-bg);
            text-decoration: none;
            display: block;
        }

        .sidebar .nav-link.active {
            background-color: var(--sidebar-active);
            color: #212529;
            font-weight: 600;
        }

        .content {
            flex-grow: 1;
            padding: 40px;
            background-color: var(--bg-content);
            color: #343a40;
            min-height: 100vh;
        }

        .content h1 {
            color: var(--accent-blue);
            font-weight: 700;
            font-size: 2rem;
        }

        .table-standard thead th {
            background-color: var(--accent-blue);
            color: white;
            text-align: center;
            border: 1px solid #dee2e6;
            padding: 15px 12px;
        }

        .table-standard td {
            border: 1px solid #dee2e6;
            vertical-align: middle;
            padding: 12px;
        }

        .form-label {
            font-weight: 600;
        }
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
                    <a class="nav-link active" href="clo.php">CLO</a>
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
                <h1>CLO</h1>
                <?php if ($success_message): ?>
                    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i> <?php echo $success_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card shadow-lg p-4 border-0">
                    <div class="card-body">
                        <div class="mb-4 text-start">
                            <button class="btn btn-primary shadow-sm px-4" onclick="openCloModal('add')" style="background-color: #0056b3; font-weight: 600;">
                                <i class="bi bi-plus-circle me-1"></i> เพิ่ม CLO ใหม่
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover table-standard align-middle">
                                <thead>
                                    <tr>
                                        <th style="width: 15%;">รหัสรายวิชา</th>
                                        <th>ชื่อวิชา</th>
                                        <th style="width: 15%;">CLO Code</th>
                                        <th>PLO ที่เชื่อมโยง</th>
                                        <th class="text-center" style="width: 120px;">จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result && $result->num_rows > 0): ?>
                                        <?php while ($item = $result->fetch_assoc()):
                                            $is_owner = ($item['use_id'] == $logged_in_user_id);
                                        ?>
                                            <tr>
                                                <td class="text-center fw-bold text-primary"><?php echo htmlspecialchars($item['course_code']); ?></td>
                                                <td><?php echo htmlspecialchars($item['course_name']); ?></td>
                                                <td class="text-center fw-bold"><?php echo htmlspecialchars($item['clo_code']); ?></td>
                                                <td class="text-center">
                                                    <?php
                                                    if (!empty($item['all_plo_codes'])) {
                                                        $plos = explode(' ', $item['all_plo_codes']);
                                                        foreach ($plos as $p) {
                                                            echo '<span class="badge bg-info text-dark me-1">' . $p . '</span>';
                                                        }
                                                    }
                                                    ?>
                                                </td>
                                                <td class="text-center">
                                                    <?php if ($is_admin || $is_owner): ?>
                                                        <div class="d-flex justify-content-center gap-1">
                                                            <button class="btn btn-warning btn-sm shadow-sm" onclick='openCloModal("edit", <?php echo json_encode($item); ?>)'>
                                                                <i class="bi bi-pencil-fill"></i>
                                                            </button>
                                                            <a href="process_clo.php?delete_course=<?php echo $item['course_id']; ?>&delete_code=<?php echo $item['clo_code']; ?>" class="btn btn-danger btn-sm shadow-sm" onclick="return confirm('ยืนยันการลบข้อมูลชุดนี้?')">
                                                                <i class="bi bi-trash-fill"></i>
                                                            </a>
                                                        </div>
                                                    <?php else: ?>
                                                        <i class="bi bi-lock-fill text-muted"></i>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">ไม่พบข้อมูลในระบบ</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="cloModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold" id="cloModalTitle">กรอกข้อมูล CLO</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="process_clo.php" method="POST">
                    <div class="modal-body p-4">
                        <input type="hidden" name="action" id="cloAction" value="add">
                        <input type="hidden" name="old_course_id" id="oldCourseId">
                        <input type="hidden" name="old_clo_code" id="oldCloCode">
                        <input type="hidden" name="use_id" value="<?php echo $logged_in_user_id; ?>">

                        <div class="mb-3">
                            <label class="form-label">อาจารย์ผู้รับผิดชอบ</label>
                            <input type="text" class="form-control bg-light" value="<?php echo $full_name; ?>" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">เลือกรายวิชา</label>
                            <select name="course_id" id="cloCourseId" class="form-select" required>
                                <option value="">-- กรุณาเลือกรายวิชา --</option>
                                <?php
                                $courses_res->data_seek(0);
                                while ($c = $courses_res->fetch_assoc()): ?>
                                    <option value="<?php echo $c['course_id']; ?>"><?php echo htmlspecialchars($c['course_code'] . " " . $c['course_name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">CLO Code</label>
                            <input type="text" name="clo_code" id="cloCode" class="form-control" placeholder="ระบุรหัส CLO เช่น CLO1" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">PLO ที่เกี่ยวข้อง</label>
                            <p class="text-muted small">* กด Ctrl ค้างไว้เพื่อเลือกหลายรายการ</p>
                            <select name="plo_id[]" id="cloPloId" class="form-select" multiple style="height: 180px;" required>
                                <?php
                                $plos_res->data_seek(0);
                                while ($p = $plos_res->fetch_assoc()): ?>
                                    <option value="<?php echo $p['plo_id']; ?>"><?php echo htmlspecialchars($p['plo_code']); ?></option>
                                <?php endwhile; ?>
                            </select>
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
        const cloModal = new bootstrap.Modal(document.getElementById('cloModal'));

        function openCloModal(mode, data = null) {
            document.getElementById('cloAction').value = mode;
            const ploSelect = document.getElementById('cloPloId');

            if (mode === 'add') {
                document.getElementById('cloModalTitle').innerText = 'เพิ่มข้อมูล CLO ใหม่';
                document.getElementById('oldCourseId').value = '';
                document.getElementById('oldCloCode').value = '';
                document.getElementById('cloCourseId').value = '';
                document.getElementById('cloCode').value = '';
                ploSelect.selectedIndex = -1;
            } else {
                document.getElementById('cloModalTitle').innerText = 'แก้ไขข้อมูล CLO';
                document.getElementById('oldCourseId').value = data.course_id;
                document.getElementById('oldCloCode').value = data.clo_code;
                document.getElementById('cloCourseId').value = data.course_id;
                document.getElementById('cloCode').value = data.clo_code;

                // ล้างการเลือกเดิม
                Array.from(ploSelect.options).forEach(opt => opt.selected = false);

                // เลือกรายการ PLO ใหม่จากข้อมูลที่ได้รับ
                if (data.all_plo_ids) {
                    const ids = data.all_plo_ids.split(',');
                    Array.from(ploSelect.options).forEach(opt => {
                        opt.selected = ids.includes(opt.value);
                    });
                }
            }
            cloModal.show();
        }
    </script>
</body>

</html>