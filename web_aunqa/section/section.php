<?php
// ไฟล์: section/section.php
session_start();
require_once "../config.php";

// 1. ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login/login.php");
    exit;
}

// 2. ข้อมูลผู้ใช้ปัจจุบัน
$logged_in_user_id = $_SESSION["use_id"];
$user_role = $_SESSION["use_role"] ?? 'user';
$is_admin = ($user_role == 'admin');
$full_name = htmlspecialchars($_SESSION["use_title"] . $_SESSION["use_fname"] . " " . $_SESSION["use_lname"]);

// 2.1 สำหรับ Admin: ดึงรายชื่อ User ทั้งหมด เพื่อให้เลือกเป็นอาจารย์ที่ปรึกษาใน Modal
$user_options = [];
if ($is_admin) {
    $sql_users = "SELECT use_id, use_title, use_fname, use_lname FROM users ORDER BY use_fname ASC";
    if ($res_users = $link->query($sql_users)) {
        while ($u_row = $res_users->fetch_assoc()) {
            $user_options[] = $u_row;
        }
    }
}

// 3. ดึงข้อมูลกลุ่มเรียน JOIN กับตาราง users เพื่อเอาชื่ออาจารย์ที่ปรึกษา
$sql = "SELECT s.*, u.use_title, u.use_fname, u.use_lname 
        FROM section s 
        LEFT JOIN users u ON s.use_id = u.use_id 
        ORDER BY s.section_id DESC";
$result = $link->query($sql);

$success_message = $_SESSION["sec_success"] ?? null;
unset($_SESSION["sec_success"]);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>กลุ่มเรียน | AUN-QA System Dashboard</title>
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
        .content { flex-grow: 1; padding: 40px; background-color: var(--bg-content); color: #343a40; min-height: 100vh;}
        .content h1 { color: var(--accent-blue); font-weight: 700; font-size: 2rem; }
        .table-standard thead th { background-color: var(--accent-blue); color: white; text-align: center; border: 1px solid #dee2e6; padding: 15px 12px; }
        .table-standard td { border: 1px solid #dee2e6; vertical-align: middle; padding: 12px; }
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
                <a class="nav-link" href="../teacher/teacher.php">อาจารย์</a>
                <a class="nav-link" href="../course/course.php">รายวิชา</a>
                <a class="nav-link" href="../opencourse/opencourse.php">รายวิชาเปิด</a>
                <a class="nav-link active" href="section.php">กลุ่มเรียน</a>
                <a class="nav-link" href="../article/article.php">บทความ</a>
                <a class="nav-link" href="../research/research.php">วิจัย</a>
                <a class="nav-link" href="../development/development.php">พัฒนานักศึกษา</a>
                <a class="nav-link" href="../plo/plo.php">PLO</a>
                <a class="nav-link" href="../dashboard.php">CLO</a>
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
            <h1>กลุ่มเรียน</h1>
            <?php if ($success_message): ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> <?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow-lg p-4 border-0">
                <div class="card-body">
                    <div class="mb-4 text-start">
                        <button class="btn btn-primary shadow-sm px-4" onclick="openSectionModal('add')" style="background-color: #0056b3; font-weight: 600;">
                            <i class="bi bi-plus-circle me-1"></i> เพิ่มกลุ่มเรียนใหม่
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover table-standard align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 20%;">กลุ่มเรียน</th>
                                    <th class="text-center" style="width: 15%;">จำนวนนักศึกษา</th>
                                    <th class="text-center" style="width: 15%;">ปีการศึกษา</th>
                                    <th style="width: 30%;">อาจารย์ที่ปรึกษา</th>
                                    <th class="text-center" style="width: 20%;">ดำเนินการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result && $result->num_rows > 0): ?>
                                    <?php while($row = $result->fetch_assoc()): 
                                        $is_owner = ($row['use_id'] == $logged_in_user_id);
                                    ?>
                                        <tr>
                                            <td class="fw-bold text-primary"><?php echo htmlspecialchars($row['section_name']); ?></td>
                                            <td class="text-center"><?php echo htmlspecialchars($row['section_num']); ?> คน</td>
                                            <td class="text-center"><?php echo htmlspecialchars($row['section_year']); ?></td>
                                            <td>
                                                <?php echo htmlspecialchars($row['use_title'] . $row['use_fname'] . " " . $row['use_lname']); ?>
                                                <?php if($is_owner): ?> <span class="badge bg-info text-dark ms-1" style="font-size: 0.7rem;">คุณ</span> <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($is_admin || $is_owner): ?>
                                                    <div class="d-flex justify-content-center gap-1">
                                                        <button class="btn btn-warning btn-sm shadow-sm" onclick='openSectionModal("edit", <?php echo json_encode($row); ?>)'>
                                                            <i class="bi bi-pencil-fill"></i> แก้ไข
                                                        </button>
                                                        <a href="process_section.php?delete=<?php echo $row['section_id']; ?>" class="btn btn-danger btn-sm shadow-sm" onclick="return confirm('ยืนยันการลบข้อมูลกลุ่มเรียน?')">
                                                            <i class="bi bi-trash-fill"></i> ลบ
                                                        </a>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted"><i class="bi bi-lock-fill"></i> สิทธิ์เฉพาะเจ้าของ</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="text-center py-4 text-muted">ไม่พบข้อมูลในระบบ</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="sectionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white" id="modalHeaderColor">
                <h5 class="modal-title fw-bold" id="sectionModalTitle">กรอกข้อมูลกลุ่มเรียน</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="process_section.php" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="action" id="secAction" value="add">
                    <input type="hidden" name="section_id" id="secId">

                    <div class="mb-4">
                        <label class="form-label fw-bold">อาจารย์ที่ปรึกษา</label>
                        <?php if ($is_admin): ?>
                            <select name="use_id" id="secUserId" class="form-select border-primary" required>
                                <option value="">-- เลือกอาจารย์ที่ปรึกษา --</option>
                                <?php foreach($user_options as $opt): ?>
                                    <option value="<?php echo $opt['use_id']; ?>">
                                        <?php echo $opt['use_title'].$opt['use_fname']." ".$opt['use_lname']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php else: ?>
                            <input type="text" class="form-control bg-light" value="<?php echo $full_name; ?>" readonly>
                            <input type="hidden" name="use_id" id="secUserId" value="<?php echo $logged_in_user_id; ?>">
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">กลุ่มเรียน</label>
                        <input type="text" name="section_name" id="secName" class="form-control" placeholder="เช่น 64/45" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">จำนวนคน (นักศึกษา)</label>
                            <input type="number" name="section_num" id="secNum" class="form-control" placeholder="ระบุจำนวนนักศึกษา" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">ปีการศึกษา</label>
                            <input type="text" name="section_year" id="secYear" class="form-control" placeholder="เช่น 2567" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 justify-content-center">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary px-5 shadow-sm" id="btnSubmit">บันทึกข้อมูล</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const sectionModal = new bootstrap.Modal(document.getElementById('sectionModal'));

function openSectionModal(mode, data = null) {
    const actionInput = document.getElementById('secAction');
    const titleText = document.getElementById('sectionModalTitle');
    const header = document.getElementById('modalHeaderColor');
    const btnSubmit = document.getElementById('btnSubmit');
    const userIdSelect = document.getElementById('secUserId');

    actionInput.value = mode;

    if (mode === 'add') {
        titleText.innerText = 'เพิ่มกลุ่มเรียนใหม่';
        header.className = 'modal-header bg-primary text-white';
        btnSubmit.className = 'btn btn-primary px-5 shadow-sm';
        btnSubmit.innerText = 'บันทึกข้อมูล';
        
        document.getElementById('secId').value = '';
        document.getElementById('secName').value = '';
        document.getElementById('secNum').value = '';
        document.getElementById('secYear').value = '';
        if(userIdSelect.tagName === 'SELECT') userIdSelect.value = '';
    } else {
        titleText.innerText = 'แก้ไขข้อมูลกลุ่มเรียน';
        header.className = 'modal-header bg-warning text-dark';
        btnSubmit.className = 'btn btn-warning px-5 shadow-sm';
        btnSubmit.innerText = 'บันทึกการแก้ไข';

        document.getElementById('secId').value = data.section_id;
        document.getElementById('secName').value = data.section_name;
        document.getElementById('secNum').value = data.section_num;
        document.getElementById('secYear').value = data.section_year;
        // ถ้าเป็น Admin ให้เลือก Value ใน Dropdown ให้ตรงกับข้อมูลเก่า
        if(userIdSelect.tagName === 'SELECT') userIdSelect.value = data.use_id;
    }
    sectionModal.show();
}
</script>
</body>
</html>