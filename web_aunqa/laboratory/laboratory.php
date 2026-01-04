<?php
// ไฟล์: laboratory/laboratory.php
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

// 3. ดึงข้อมูลห้องปฏิบัติการ
$labs = [];
$sql = "SELECT l.*, CONCAT(u.use_title, u.use_fname, ' ', u.use_lname) AS owner_name 
        FROM laboratory l
        LEFT JOIN users u ON l.use_id = u.use_id
        ORDER BY l.lab_id DESC";

if ($result = $link->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $labs[] = $row;
    }
    $result->free();
}

// ฟังก์ชันสำหรับเช็ค Active Menu
function is_active($target_file)
{
    return (basename($_SERVER['PHP_SELF']) == $target_file) ? 'active' : '';
}

$success_message = $_SESSION["lab_success"] ?? null;
unset($_SESSION["lab_success"]);
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ห้องปฏิบัติการ | AUN-QA System Dashboard</title>
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

        /* Header */
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

        /* Sidebar */
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
            transition: background-color 0.2s;
            background-color: var(--sidebar-link-bg);
            box-shadow: 1px 0 0 rgba(0, 0, 0, 0.2) inset, 0 1px 0 rgba(0, 0, 0, 0.2);
            text-decoration: none;
            display: block;
        }

        .sidebar .nav-link:hover {
            background-color: #495057;
            color: white;
        }

        .sidebar .nav-link.active {
            background-color: var(--sidebar-active);
            color: #212529;
            font-weight: 600;
        }

        /* Content */
        .content {
            flex-grow: 1;
            padding: 40px;
            background-color: var(--bg-content);
            color: #343a40;
            box-shadow: -5px 0 10px rgba(0, 0, 0, 0.1);
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
            padding: 12px;
        }

        .table-standard td {
            border: 1px solid #dee2e6;
            vertical-align: middle;
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
                    <a class="nav-link" href="../clo/clo.php">CLO</a>
                    <a class="nav-link" href="../services/services.php">งานบริการวิชาการ</a>
                    <a class="nav-link active" href="laboratory.php">ห้องปฏิบัติการ</a>
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
                <h1>ห้องปฏิบัติการ</h1>
                <?php if ($success_message): ?>
                    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i> <?php echo $success_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card shadow-lg p-4 border-0">
                    <div class="card-body">
                        <div class="mb-4">
                            <button class="btn btn-primary shadow-sm" onclick="openLabModal('add')" style="background-color: #0056b3; font-weight: 600;">
                                <i class="bi bi-plus-circle me-1"></i> เพิ่มห้องปฏิบัติการใหม่
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover table-standard">
                                <thead>
                                    <tr>
                                        <th>ชื่อห้อง</th>
                                        <th>ครุภัณฑ์</th>
                                        <th class="text-center">จำนวน</th>
                                        <th class="text-center">สถานะ</th>
                                        <th class="text-center" style="width: 120px;">ดำเนินการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($labs) > 0): ?>
                                        <?php foreach ($labs as $lab):
                                            $is_owner = ($lab['use_id'] == $logged_in_user_id);
                                        ?>
                                            <tr>
                                                <td class="fw-bold text-primary"><?php echo htmlspecialchars($lab['lab_name']); ?></td>
                                                <td><?php echo nl2br(htmlspecialchars($lab['lab_durable'] ?? '-')); ?></td>
                                                <td class="text-center"><?php echo htmlspecialchars($lab['lab_num']); ?></td>
                                                <td class="text-center">
                                                    <span class="badge <?php echo ($lab['lab_status'] == 'พร้อมใช้งาน') ? 'bg-success' : 'bg-danger'; ?>">
                                                        <?php echo htmlspecialchars($lab['lab_status']); ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <?php if ($is_admin || $is_owner): ?>
                                                        <div class="d-flex justify-content-center gap-1">
                                                            <button class="btn btn-warning btn-sm shadow-sm" onclick='openLabModal("edit", <?php echo json_encode($lab); ?>)'>
                                                                <i class="bi bi-pencil-fill"></i>
                                                            </button>
                                                            <a href="process_lab.php?delete=<?php echo $lab['lab_id']; ?>" class="btn btn-danger btn-sm shadow-sm" onclick="return confirm('ยืนยันการลบ?')">
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
                                        <tr>
                                            <td colspan="5" class="text-center py-4">ไม่พบข้อมูล</td>
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

    <div class="modal fade" id="labModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold" id="labModalTitle">กรอกข้อมูลห้องปฏิบัติการ</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="process_lab.php" method="POST">
                    <div class="modal-body p-4">
                        <input type="hidden" name="action" id="labAction" value="add">
                        <input type="hidden" name="lab_id" id="labId">

                        <div class="mb-4">
                            <label class="form-label fw-bold">ผู้รับผิดชอบงาน</label>
                            <input type="text" class="form-control bg-light" value="<?php echo $full_name; ?>" readonly>
                            <input type="hidden" name="use_id" value="<?php echo $logged_in_user_id; ?>">
                        </div>

                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label fw-bold">ชื่อห้องปฏิบัติการ</label>
                                <input type="text" name="lab_name" id="labName" class="form-control" placeholder="ระบุชื่อห้อง..." required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">จำนวนเครื่อง</label>
                                <input type="text" name="lab_num" id="labNum" class="form-control" placeholder="เช่น 40" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">ครุภัณฑ์</label>
                            <textarea name="lab_durable" id="labDurable" class="form-control" rows="3" placeholder="ระบุรายการครุภัณฑ์..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">สถานะการใช้งาน</label>
                            <select name="lab_status" id="labStatus" class="form-select">
                                <option value="พร้อมใช้งาน">พร้อมใช้งาน</option>
                                <option value="ไม่พร้อมใช้งาน">ไม่พร้อมใช้งาน</option>
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
        const labModal = new bootstrap.Modal(document.getElementById('labModal'));

        function openLabModal(mode, data = null) {
            document.getElementById('labAction').value = mode;
            if (mode === 'add') {
                document.getElementById('labModalTitle').innerText = 'เพิ่มข้อมูลห้องปฏิบัติการใหม่';
                document.getElementById('labId').value = '';
                document.getElementById('labName').value = '';
                document.getElementById('labNum').value = '';
                document.getElementById('labDurable').value = '';
                document.getElementById('labStatus').value = 'พร้อมใช้งาน';
            } else {
                document.getElementById('labModalTitle').innerText = 'แก้ไขข้อมูลห้องปฏิบัติการ';
                document.getElementById('labId').value = data.lab_id;
                document.getElementById('labName').value = data.lab_name;
                document.getElementById('labNum').value = data.lab_num;
                document.getElementById('labDurable').value = data.lab_durable;
                document.getElementById('labStatus').value = data.lab_status;
            }
            labModal.show();
        }
    </script>
</body>

</html>