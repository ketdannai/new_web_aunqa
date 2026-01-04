<?php
session_start();
require_once "../config.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login/login.php");
    exit;
}

$logged_in_user_id = $_SESSION["use_id"];
$full_name = htmlspecialchars($_SESSION["use_title"] . $_SESSION["use_fname"] . " " . $_SESSION["use_lname"]);
$user_role = $_SESSION["use_role"] ?? 'user';
$is_admin = ($user_role == 'admin');

// 1. ดึงข้อมูลวิชาเปิดสอน
$opencourses = [];
$sql = "SELECT o.*, c.course_name, c.course_code, 
        CONCAT(u.use_title, u.use_fname, ' ', u.use_lname) AS teacher_name, 
        s.section_name, t.term_year 
        FROM opencourse o
        LEFT JOIN course c ON o.course_id = c.course_id
        LEFT JOIN users u ON o.use_id = u.use_id
        LEFT JOIN section s ON o.section_id = s.section_id
        LEFT JOIN term t ON o.term_id = t.term_id
        ORDER BY o.opencourse_id DESC";

if ($result = $link->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $opencourses[] = $row;
    }
    $result->free();
}

// 2. ดึงข้อมูลสำหรับ Dropdown ต่างๆ
$courses_res = $link->query("SELECT course_id, course_name, course_code FROM course ORDER BY course_code ASC");
$sections_res = $link->query("SELECT section_id, section_name FROM section ORDER BY section_name ASC");
$terms_res = $link->query("SELECT term_id, term_year FROM term ORDER BY term_year DESC");
// ดึงรายชื่ออาจารย์ (สำหรับ Admin เลือก)
$users_res = $link->query("SELECT use_id, use_title, use_fname, use_lname FROM users ORDER BY use_fname ASC");

$success_message = $_SESSION["oc_success"] ?? null;
unset($_SESSION["oc_success"]);
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>รายวิชาเปิดสอน | AUN-QA System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --bg-dark: #222222;
            --accent-blue: #007bff;
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
        }

        .content-area {
            display: flex;
            flex-grow: 1;
        }

        .sidebar {
            width: 250px;
            background-color: var(--bg-dark);
            flex-shrink: 0;
        }

        .sidebar .nav-link {
            color: #f8f9fa;
            padding: 12px 15px;
            background-color: #343a40;
            text-decoration: none;
            display: block;
            margin-bottom: 1px;
        }

        .sidebar .nav-link.active {
            background-color: #cce0ff;
            color: #212529;
            font-weight: 600;
        }

        .content {
            flex-grow: 1;
            padding: 40px;
            background-color: #ffffff;
            min-height: 100vh;
        }

        .table-standard thead th {
            background-color: var(--accent-blue);
            color: white;
            text-align: center;
        }
    </style>
</head>

<body>

    <div class="main-container">
        <div class="main-header d-flex justify-content-between align-items-center">
            <p class="mb-0">ยินดีต้อนรับ: <?php echo $full_name; ?> (<?php echo strtoupper($user_role); ?>)</p>
            <a href="../login/logout.php" class="btn btn-sm btn-light fw-bold text-dark">LOGOUT</a>
        </div>

        <div class="content-area">

            <div class="sidebar">

                <div class="nav flex-column">

                    <a class="nav-link" href="../dashboard.php">หน้าแรก</a>

                    <a class="nav-link" href="../profile/profile.php">ข้อมูลส่วนตัว</a>

                    <a class="nav-link" href="../teacher/teacher.php">อาจารย์</a>

                    <a class="nav-link" href="../course/course.php">รายวิชา</a>

                    <a class="nav-link active" href="opencourse.php">รายวิชาเปิด</a>

                    <a class="nav-link" href="../section/section.php">กลุ่มเรียน</a>

                    <a class="nav-link" href="../article/article.php">บทความ</a>

                    <a class="nav-link" href="../research/research.php">วิจัย</a>

                    <a class="nav-link" href="../development/development.php">พัฒนานักศึกษา</a>

                    <a class="nav-link" href="../plo/plo.php">PLO</a>

                    <a class="nav-link" href="../clo/clo.php">CLO</a>

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
                <h1 class="text-primary fw-bold mb-4">รายวิชาเปิดสอน</h1>

                <?php if ($success_message): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i> <?php echo $success_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card shadow-lg p-4 border-0">
                    <div class="mb-4">
                        <button class="btn btn-primary px-4 shadow-sm" onclick="openOCModal('add')">
                            <i class="bi bi-plus-circle me-1"></i> เพิ่มรายวิชาเปิดใหม่
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle table-standard">
                            <thead>
                                <tr>
                                    <th style="width: 30%;">รายวิชา</th>
                                    <th style="width: 15%;">กลุ่มเรียน</th>
                                    <th style="width: 20%;">ปีการศึกษา/เทอม</th>
                                    <th style="width: 20%;">ผู้สอน</th>
                                    <th style="width: 15%;">ดำเนินการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($opencourses as $oc):
                                    $is_owner = ($oc['use_id'] == $logged_in_user_id);
                                ?>
                                    <tr>
                                        <td class="fw-bold text-primary"><?php echo htmlspecialchars($oc['course_code'] . " " . $oc['course_name']); ?></td>
                                        <td class="text-center"><?php echo htmlspecialchars($oc['section_name']); ?></td>
                                        <td class="text-center"><span class="badge bg-info text-dark"><?php echo htmlspecialchars($oc['term_year']); ?></span></td>
                                        <td><?php echo htmlspecialchars($oc['teacher_name']); ?></td>
                                        <td class="text-center">
                                            <?php if ($is_admin || $is_owner): ?>
                                                <button class="btn btn-warning btn-sm" onclick='openOCModal("edit", <?php echo json_encode($oc); ?>)'>
                                                    <i class="bi bi-pencil-fill"></i>
                                                </button>
                                                <a href="process_opencourse.php?delete=<?php echo $oc['opencourse_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('ลบรายการนี้?')">
                                                    <i class="bi bi-trash-fill"></i>
                                                </a>
                                            <?php else: ?>
                                                <i class="bi bi-lock-fill text-muted"></i>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="ocModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form action="process_opencourse.php" method="POST" class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="ocModalTitle">ข้อมูลวิชาเปิดสอน</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="action" id="ocAction">
                    <input type="hidden" name="opencourse_id" id="ocId">

                    <div class="mb-3">
                        <label class="form-label">อาจารย์ผู้สอน</label>
                        <?php if ($is_admin): ?>
                            <select name="use_id" id="ocUserId" class="form-select" required>
                                <?php while ($u = $users_res->fetch_assoc()): ?>
                                    <option value="<?php echo $u['use_id']; ?>">
                                        <?php echo $u['use_title'] . $u['use_fname'] . " " . $u['use_lname']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        <?php else: ?>
                            <input type="text" class="form-control bg-light" value="<?php echo $full_name; ?>" readonly>
                            <input type="hidden" name="use_id" value="<?php echo $logged_in_user_id; ?>">
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">เลือกรายวิชา</label>
                        <select name="course_id" id="ocCourse" class="form-select" required>
                            <option value="">-- เลือกวิชา --</option>
                            <?php $courses_res->data_seek(0);
                            while ($c = $courses_res->fetch_assoc()): ?>
                                <option value="<?php echo $c['course_id']; ?>"><?php echo $c['course_code'] . " " . $c['course_name']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">กลุ่มเรียน</label>
                            <select name="section_id" id="ocSection" class="form-select" required>
                                <?php $sections_res->data_seek(0);
                                while ($s = $sections_res->fetch_assoc()): ?>
                                    <option value="<?php echo $s['section_id']; ?>"><?php echo $s['section_name']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">ปีการศึกษา/เทอม</label>
                            <select name="term_id" id="ocTermId" class="form-select" required>
                                <?php $terms_res->data_seek(0);
                                while ($t = $terms_res->fetch_assoc()): ?>
                                    <option value="<?php echo $t['term_id']; ?>"><?php echo $t['term_year']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary px-4">บันทึกข้อมูล</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const ocModal = new bootstrap.Modal(document.getElementById('ocModal'));

        function openOCModal(mode, data = null) {
            document.getElementById('ocAction').value = mode;
            if (mode === 'add') {
                document.getElementById('ocModalTitle').innerText = 'เพิ่มรายวิชาเปิดสอน';
                document.getElementById('ocId').value = '';
                if (document.getElementById('ocUserId')) document.getElementById('ocUserId').value = '<?php echo $logged_in_user_id; ?>';
            } else {
                document.getElementById('ocModalTitle').innerText = 'แก้ไขวิชาเปิดสอน';
                document.getElementById('ocId').value = data.opencourse_id;
                document.getElementById('ocCourse').value = data.course_id;
                document.getElementById('ocSection').value = data.section_id;
                document.getElementById('ocTermId').value = data.term_id;
                if (document.getElementById('ocUserId')) document.getElementById('ocUserId').value = data.use_id;
            }
            ocModal.show();
        }
    </script>
</body>

</html>