<?php
// ไฟล์: teacher/teacher.php
session_start();
require_once "../config.php";

// 1. ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login/login.php");
    exit;
}

// 2. ดึงข้อมูลจาก Session
$logged_in_user_id = $_SESSION["use_id"];
$user_role = $_SESSION["use_role"] ?? 'user';
$is_admin = ($user_role == 'admin');

// สร้างชื่อเต็มสำหรับแสดงผลที่ Header
$full_name = htmlspecialchars(($_SESSION["use_title"] ?? '') . ($_SESSION["use_fname"] ?? '') . " " . ($_SESSION["use_lname"] ?? ''));

// 2.1 ตรวจสอบว่า User ปัจจุบันมีข้อมูลในตาราง teachers หรือยัง
$has_teacher_profile = false;
$check_sql = "SELECT COUNT(*) FROM teachers WHERE use_id = ?";
if ($check_stmt = $link->prepare($check_sql)) {
    $check_stmt->bind_param("i", $logged_in_user_id);
    $check_stmt->execute();
    $check_stmt->bind_result($count);
    $check_stmt->fetch();
    $check_stmt->close();
    if ($count > 0) $has_teacher_profile = true;
}

// 2.2 ถ้าเป็น Admin: ดึงรายชื่อ User ทั้งหมดที่ "ยังไม่มี" ข้อมูลในตาราง teachers เพื่อให้ Admin เลือกเพิ่มได้
$user_options = [];
if ($is_admin) {
    $sql_users = "SELECT use_id, use_title, use_fname, use_lname FROM users 
                  WHERE use_id NOT IN (SELECT use_id FROM teachers)
                  ORDER BY use_fname ASC";
    if ($res_users = $link->query($sql_users)) {
        while ($u_row = $res_users->fetch_assoc()) {
            $user_options[] = $u_row;
        }
    }
}

// เงื่อนไขสิทธิ์การเพิ่มข้อมูล: Admin เพิ่มได้ตลอด / User เพิ่มได้เฉพาะถ้ายังไม่มีข้อมูลตัวเอง
$can_add_teacher = $is_admin || !$has_teacher_profile;

// 3. ดึงข้อมูลอาจารย์ทั้งหมดมาแสดงในตาราง
$teachers = [];
$sql = "SELECT t.*, u.use_title, u.use_fname, u.use_lname 
        FROM teachers t 
        INNER JOIN users u ON t.use_id = u.use_id 
        ORDER BY t.teac_id ASC";
if ($result = $link->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $teachers[] = $row;
    }
    $result->free();
}

$success_message = $_SESSION["teacher_success"] ?? null;
unset($_SESSION["teacher_success"]);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ข้อมูลอาจารย์ | AUN-QA System</title>
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
        .main-header { background-color: var(--accent-blue); color: white; padding: 15px 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.2); font-weight: 600; }
        .header-top { display: flex; justify-content: space-between; align-items: center; }
        .btn-logout { background-color: #f8f9fa; color: #212529; border: none; font-weight: 600; padding: 5px 15px; border-radius: 3px; text-decoration: none; }
        .content-area { display: flex; flex-grow: 1; }
        .sidebar { width: 250px; background-color: var(--bg-dark); padding: 0; flex-shrink: 0; }
        .sidebar .nav-link { 
            color: var(--text-light); padding: 12px 15px; margin-bottom: 1px; font-size: 1.05rem; 
            background-color: var(--sidebar-link-bg); text-decoration: none; display: block;
        }
        .sidebar .nav-link.active { background-color: var(--sidebar-active); color: #212529; font-weight: 600; }
        .content { flex-grow: 1; padding: 40px; background-color: var(--bg-content); color: #343a40; min-height: 100vh; }
        .content h1 { color: var(--accent-blue); font-weight: 700; font-size: 2rem; }
        .table-teacher thead th { background-color: var(--accent-blue); color: white; text-align: center; border: 1px solid #dee2e6; padding: 12px; }
        .table-teacher td { border: 1px solid #dee2e6; vertical-align: middle; }
    </style>
</head>
<body>

<div class="main-container">
    <div class="main-header">
         <div class="header-top">
            <p class="mb-0">ยินดีต้อนรับ: <?php echo $full_name; ?> (สถานะ: <?php echo $user_role; ?>)</p>
            <a href="../login/logout.php" class="btn btn-sm btn-logout">logout</a>
        </div>
    </div>

    <div class="content-area">
        <div class="sidebar">
            <div class="nav flex-column">
                <a class="nav-link" href="../dashboard.php">หน้าแรก</a>
                <a class="nav-link" href="../profile/profile.php">ข้อมูลส่วนตัว</a>
                <a class="nav-link active" href="teacher.php">อาจารย์</a>
                <a class="nav-link" href="../course/course.php">รายวิชา</a>
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
            <h1>ข้อมูลอาจารย์</h1>

            <?php if ($success_message): ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> <?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow-lg p-4 border-0">
                <div class="card-body">
                    <?php if ($can_add_teacher): ?>
                        <div class="mb-4">
                            <button class="btn btn-primary shadow-sm px-4" data-bs-toggle="modal" data-bs-target="#addTeacherModal" style="background-color: #0056b3; font-weight: 600;">
                                <i class="bi bi-plus-circle me-1"></i> กรอกข้อมูลอาจารย์ใหม่
                            </button>
                        </div>
                    <?php endif; ?>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover table-teacher align-middle">
                            <thead>
                                <tr>
                                    <th>ชื่อ-นามสกุล</th>
                                    <th>ตำแหน่งวิชาการ</th>
                                    <th>วุฒิการศึกษา</th>
                                    <th>สาขา</th>
                                    <th>สถานะ</th>
                                    <th class="text-center" style="width: 120px;">ดำเนินการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($teachers) > 0): ?>
                                    <?php foreach ($teachers as $t): 
                                        $is_owner = ($t['use_id'] == $logged_in_user_id);
                                    ?>
                                        <tr>
                                            <td class="fw-bold text-primary">
                                                <?php echo htmlspecialchars($t['use_title'].$t['use_fname']." ".$t['use_lname']); ?>
                                                <?php if($is_owner): ?> <span class="badge bg-info text-dark ms-1" style="font-size: 0.7rem;">คุณ</span> <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($t['teac_position']); ?></td>
                                            <td><?php echo htmlspecialchars($t['teac_qualification']); ?></td>
                                            <td><?php echo htmlspecialchars($t['teac_branch']); ?></td>
                                            <td class="text-center">
                                                <span class="badge bg-success"><?php echo htmlspecialchars($t['teac_status']); ?></span>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($is_admin || $is_owner): ?>
                                                    <div class="d-flex justify-content-center gap-1">
                                                        <button class="btn btn-warning btn-sm shadow-sm" onclick='openEditTeacherModal(<?php echo json_encode($t); ?>)'>
                                                            <i class="bi bi-pencil-fill"></i>
                                                        </button>
                                                        <a href="delete_teacher.php?id=<?php echo $t['teac_id']; ?>" class="btn btn-danger btn-sm shadow-sm" onclick="return confirm('ยืนยันการลบข้อมูลนี้?')">
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
                                    <tr><td colspan="6" class="text-center py-4 text-muted">ไม่พบข้อมูลอาจารย์ในระบบ</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addTeacherModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">กรอกข้อมูลโปรไฟล์อาจารย์</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="insert_teacher.php" method="POST">
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label fw-bold">ชื่ออาจารย์ที่ต้องการเพิ่มข้อมูล</label>
                        <?php if ($is_admin): ?>
                            <select name="user_id" class="form-select border-primary" required>
                                <option value="">-- กรุณาเลือกรายชื่อผู้ใช้งาน --</option>
                                <?php if(!$has_teacher_profile): ?>
                                    <option value="<?php echo $logged_in_user_id; ?>"><?php echo $full_name; ?> (ข้อมูลของคุณเอง)</option>
                                <?php endif; ?>
                                <?php foreach($user_options as $opt): ?>
                                    <option value="<?php echo $opt['use_id']; ?>">
                                        <?php echo $opt['use_title'].$opt['use_fname']." ".$opt['use_lname']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">เฉพาะรายชื่อผู้ใช้ที่ยังไม่มีข้อมูลอาจารย์ในระบบเท่านั้น</div>
                        <?php else: ?>
                            <input type="text" class="form-control bg-light" value="<?php echo $full_name; ?>" readonly>
                            <input type="hidden" name="user_id" value="<?php echo $logged_in_user_id; ?>">
                        <?php endif; ?>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">ตำแหน่งทางวิชาการ</label>
                            <input type="text" name="teac_position" class="form-control" placeholder="เช่น ผศ.ดร." required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">วุฒิการศึกษาสูงสุด</label>
                            <input type="text" name="teac_qualification" class="form-control" placeholder="เช่น ปร.ด. (วิศวกรรมซอฟต์แวร์)" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">สาขา/ความเชี่ยวชาญ</label>
                            <input type="text" name="teac_branch" class="form-control" placeholder="เช่น วิศวกรรมคอมพิวเตอร์" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">สถานะ</label>
                            <input type="text" name="teac_status" class="form-control" value="ปฏิบัติงานปกติ" required>
                        </div>
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

<div class="modal fade" id="editTeacherModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold">แก้ไขข้อมูลอาจารย์</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="update_teacher.php" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="teac_id" id="edit_teac_id">
                    <div class="mb-4">
                        <label class="form-label fw-bold">ชื่อ-นามสกุลอาจารย์</label>
                        <input type="text" id="edit_full_name" class="form-control bg-light" readonly>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">ตำแหน่งทางวิชาการ</label>
                            <input type="text" name="teac_position" id="edit_teac_position" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">วุฒิการศึกษาสูงสุด</label>
                            <input type="text" name="teac_qualification" id="edit_teac_qualification" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">สาขา/ความเชี่ยวชาญ</label>
                            <input type="text" name="teac_branch" id="edit_teac_branch" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">สถานะ</label>
                            <input type="text" name="teac_status" id="edit_teac_status" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 justify-content-center">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-warning px-5 shadow-sm">บันทึกการแก้ไข</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function openEditTeacherModal(data) {
    document.getElementById('edit_teac_id').value = data.teac_id;
    document.getElementById('edit_full_name').value = data.use_title + data.use_fname + ' ' + data.use_lname;
    document.getElementById('edit_teac_position').value = data.teac_position;
    document.getElementById('edit_teac_qualification').value = data.teac_qualification;
    document.getElementById('edit_teac_branch').value = data.teac_branch;
    document.getElementById('edit_teac_status').value = data.teac_status;
    
    var editModal = new bootstrap.Modal(document.getElementById('editTeacherModal'));
    editModal.show();
}
</script>
</body>
</html>