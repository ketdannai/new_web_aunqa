<?php
// ไฟล์: course/course.php
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

// 1. ดึงข้อมูลรายวิชา พร้อม JOIN เพื่อเอาชื่อหมวด (category) และชื่อหมวดย่อย (categorycourse)
$courses = [];
$sql = "SELECT c.*, cat.category_name, sub.categorycourse_name 
        FROM course c
        LEFT JOIN category cat ON c.category_id = cat.category_id
        LEFT JOIN categorycourse sub ON c.categorycourse_id = sub.categorycourse_id
        ORDER BY c.course_id DESC";

if ($result = $link->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }
    $result->free();
}

// 2. ดึงข้อมูลหมวดทั้งหมด (สำหรับ Dropdown ใน Modal)
$categories = $link->query("SELECT * FROM category ORDER BY category_name ASC");
$sub_categories = $link->query("SELECT * FROM categorycourse ORDER BY categorycourse_name ASC");

$success_message = $_SESSION["course_success"] ?? null;
unset($_SESSION["course_success"]);
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายวิชา | AUN-QA System Dashboard</title>
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
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .content-area {
            display: flex;
            flex-grow: 1;
        }

        .sidebar {
            width: 250px;
            background-color: var(--bg-dark);
        }

        .sidebar .nav-link {
            color: var(--text-light);
            padding: 12px 15px;
            background-color: var(--sidebar-link-bg);
            text-decoration: none;
            display: block;
            margin-bottom: 1px;
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
            min-height: 100vh;
        }

        .table-standard thead th {
            background-color: var(--accent-blue);
            color: white;
            text-align: center;
            border: 1px solid #dee2e6;
            padding: 12px;
        }
    </style>
</head>

<body>

    <div class="main-container">
        <div class="main-header">
            <div class="header-top">
                <p class="mb-0">ยินดีต้อนรับ: <?php echo $full_name; ?></p>
                <a href="../login/logout.php" class="btn btn-sm btn-light">logout</a>
            </div>
        </div>

        <div class="content-area">
            <div class="sidebar">
                <div class="nav flex-column">
                    <a class="nav-link" href="../dashboard.php">หน้าแรก</a>
                    <a class="nav-link" href="../profile/profile.php">ข้อมูลส่วนตัว</a>
                    <a class="nav-link" href="../teacher/teacher.php">อาจารย์</a>
                    <a class="nav-link active" href="course.php">รายวิชา</a>
                    <a class="nav-link" href="../opencourse/opencourse.php">รายวิชาเปิด</a>
                    <a class="nav-link" href="../section/section.php">กลุ่มเรียน</a>
                    <a class="nav-link" href="../article/article.php">บทความ</a>
                    <a class="nav-link" href="../research/research.php">วิจัย</a>
                    <a class="nav-link" href="../development/development.php">พัฒนานักศึกษา</a>
                    <a class="nav-link" href="../plo/plo.php">PLO</a>
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
                <h1 class="mb-4 text-primary fw-bold">รายวิชา</h1>

                <?php if ($success_message): ?>
                    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i> <?php echo $success_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card shadow-lg p-4 border-0">
                    <div class="mb-4">
                        <button class="btn btn-primary px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#addCourseModal">
                            <i class="bi bi-plus-circle me-1"></i> เพิ่มรายวิชาใหม่
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle table-standard">
                            <thead>
                                <tr>
                                    <th style="width: 10%;">รหัสวิชา</th>
                                    <th style="width: 25%;">ชื่อวิชา</th>
                                    <th>หมวด</th>
                                    <th>หมวดย่อย</th>
                                    <th style="width: 12%;">หน่วยกิต</th>
                                    <th style="width: 12%;">ดำเนินการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($courses) > 0): ?>
                                    <?php foreach ($courses as $course):
                                        $is_owner = (isset($course['use_id']) && $course['use_id'] == $logged_in_user_id);
                                    ?>
                                        <tr>
                                            <td class="text-center fw-bold text-primary"><?php echo htmlspecialchars($course['course_code']); ?></td>
                                            <td class="fw-semibold"><?php echo htmlspecialchars($course['course_name']); ?></td>
                                            <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($course['category_name'] ?? 'ไม่มีหมวด'); ?></span></td>
                                            <td><?php echo htmlspecialchars($course['categorycourse_name'] ?? '-'); ?></td>
                                            <td class="text-center"><?php echo htmlspecialchars($course['course_credit']); ?></td>
                                            <td class="text-center">
                                                <?php if ($is_admin || $is_owner): ?>
                                                    <button class="btn btn-warning btn-sm" onclick="openEditModal(
                                                        '<?php echo $course['course_id']; ?>', 
                                                        '<?php echo htmlspecialchars($course['course_code']); ?>', 
                                                        '<?php echo htmlspecialchars($course['course_name']); ?>', 
                                                        '<?php echo htmlspecialchars($course['course_credit']); ?>',
                                                        '<?php echo $course['category_id']; ?>',
                                                        '<?php echo $course['categorycourse_id']; ?>'
                                                    )">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <a href="process_course.php?delete=<?php echo $course['course_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('ยืนยันการลบ?')">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <i class="bi bi-lock-fill text-muted"></i>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">ไม่พบข้อมูล</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addCourseModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <form action="process_course.php" method="POST">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">เพิ่มรายวิชาใหม่</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="use_id" value="<?php echo $logged_in_user_id; ?>">

                        <div class="mb-3">
                            <label class="form-label">รหัสวิชา</label>
                            <input type="text" name="course_code" class="form-control"  required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">ชื่อวิชา</label>
                            <input type="text" name="course_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">หมวด</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">-- เลือกหมวด --</option>
                                <?php $categories->data_seek(0);
                                while ($cat = $categories->fetch_assoc()): ?>
                                    <option value="<?php echo $cat['category_id']; ?>"><?php echo $cat['category_name']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">หมวดย่อย</label>
                            <select name="categorycourse_id" class="form-select" required>
                                <option value="">-- เลือกหมวดย่อย --</option>
                                <?php $sub_categories->data_seek(0);
                                while ($sub = $sub_categories->fetch_assoc()): ?>
                                    <option value="<?php echo $sub['categorycourse_id']; ?>"><?php echo $sub['categorycourse_name']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">หน่วยกิต</label>
                            <input type="text" name="course_credit" class="form-control"  required>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-primary">บันทึกข้อมูล</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editCourseModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <form action="process_course.php" method="POST">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title">แก้ไขข้อมูลรายวิชา</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="course_id" id="edit_course_id">

                        <div class="mb-3">
                            <label class="form-label">รหัสวิชา</label>
                            <input type="text" name="course_code" id="edit_course_code" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">ชื่อวิชา</label>
                            <input type="text" name="course_name" id="edit_course_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">หมวด</label>
                            <select name="category_id" id="edit_category_id" class="form-select" required>
                                <?php $categories->data_seek(0);
                                while ($cat = $categories->fetch_assoc()): ?>
                                    <option value="<?php echo $cat['category_id']; ?>"><?php echo $cat['category_name']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">หมวดย่อย</label>
                            <select name="categorycourse_id" id="edit_categorycourse_id" class="form-select" required>
                                <?php $sub_categories->data_seek(0);
                                while ($sub = $sub_categories->fetch_assoc()): ?>
                                    <option value="<?php echo $sub['categorycourse_id']; ?>"><?php echo $sub['categorycourse_name']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">หน่วยกิต</label>
                            <input type="text" name="course_credit" id="edit_course_credit" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-warning">บันทึกการแก้ไข</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function openEditModal(id, code, name, credit, catId, subCatId) {
            document.getElementById('edit_course_id').value = id;
            document.getElementById('edit_course_code').value = code;
            document.getElementById('edit_course_name').value = name;
            document.getElementById('edit_course_credit').value = credit;
            document.getElementById('edit_category_id').value = catId;
            document.getElementById('edit_categorycourse_id').value = subCatId;
            new bootstrap.Modal(document.getElementById('editCourseModal')).show();
        }
    </script>
</body>
</html>