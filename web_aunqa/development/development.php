<?php
// ไฟล์: development/development.php
session_start();
require_once "../config.php";

// 1. ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login/login.php");
    exit;
}

// 2. ข้อมูลผู้ใช้ปัจจุบัน
$logged_in_user_id = $_SESSION["use_id"];
$full_name = htmlspecialchars($_SESSION["use_title"] . $_SESSION["use_fname"] . " " . $_SESSION["use_lname"]);
$user_role = htmlspecialchars($_SESSION["use_role"] ?? 'user');
$is_admin = ($user_role == 'admin');

// 3. ดึงข้อมูลงานพัฒนาวิชาการ (Join Users และ Section)
$dev_list = [];
$sql = "SELECT d.*, u.use_title, u.use_fname, u.use_lname, s.section_name 
        FROM development d 
        LEFT JOIN users u ON d.use_id = u.use_id 
        LEFT JOIN section s ON d.section_id = s.section_id 
        ORDER BY d.dev_id DESC";

if ($result = $link->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $dev_list[] = $row;
    }
    $result->free();
}

// ดึงข้อมูลกลุ่มเรียนสำหรับ Dropdown
$sections_res = $link->query("SELECT section_id, section_name FROM section");

function is_active($target_file)
{
    return (basename($_SERVER['PHP_SELF']) == $target_file) ? 'active' : '';
}

$success_message = $_SESSION["dev_success"] ?? null;
unset($_SESSION["dev_success"]);
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>พัฒนาวิชาการ | AUN-QA System Dashboard</title>
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

        /* ตั้งค่าฟอนต์ Kanit ทั้งหน้า */
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

        /* Header Bar */
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

        /* Sidebar Style */
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
            font-weight: 400;
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

        /* Content Area */
        .content {
            flex-grow: 1;
            padding: 40px;
            background-color: var(--bg-content);
            color: #343a40;
            box-shadow: -5px 0 10px rgba(0, 0, 0, 0.1);
            min-height: 100vh;
        }

        .content h1 {
            color: var(--accent-blue);
            font-weight: 700;
            /* ตัวหนาพิเศษเหมือนหน้า Teacher */
            font-size: 2rem;
            margin-bottom: 5px;
        }

        /* Table Style - ปรับหัวตารางให้เหมือน Teacher */
        .table-standard thead th {
            background-color: var(--accent-blue);
            color: white;
            font-weight: 600;
            /* หัวตารางตัวหนา */
            text-align: center;
            border: 1px solid #dee2e6;
            padding: 15px 12px;
        }

        .table-standard td {
            font-weight: 400;
            border: 1px solid #dee2e6;
            vertical-align: middle;
            padding: 12px;
        }

        /* Modal Styles */
        .form-label {
            font-weight: 600;
        }

        .modal-title {
            font-weight: 700;
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
                    <a class="nav-link active" href="development.php">พัฒนานักศึกษา</a>
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
                <h1>งานพัฒนาวิชาการ</h1>
                <?php if ($success_message): ?>
                    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i> <?php echo $success_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card shadow-lg p-4 border-0">
                    <div class="card-body">
                        <div class="mb-4 text-start">
                            <button class="btn btn-primary shadow-sm px-4" onclick="openDevModal('add')" style="background-color: #0056b3; font-weight: 600;">
                                <i class="bi bi-plus-circle me-1"></i> เพิ่มงานพัฒนาวิชาการใหม่
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover table-standard align-middle">
                                <thead>
                                    <tr>
                                        <th>ชื่อ</th>
                                        <th class="text-center">กลุ่มเรียน</th>
                                        <th>ชื่องาน/โครงการ</th>
                                        <th class="text-center">วันที่</th>
                                        <th>สถานที่</th>
                                        <th>วัตถุประสงค์</th>
                                        <th class="text-center" style="width: 110px;">ดำเนินการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($dev_list) > 0): ?>
                                        <?php foreach ($dev_list as $dev):
                                            $is_owner = ($dev['use_id'] == $logged_in_user_id);
                                        ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($dev['use_title'] . $dev['use_fname'] . " " . $dev['use_lname']); ?></td>
                                                <td class="text-center"><?php echo $dev['section_name'] ? htmlspecialchars($dev['section_name']) : '-'; ?></td>
                                                <td class="fw-bold text-primary"><?php echo htmlspecialchars($dev['dev_name']); ?></td>
                                                <td class="text-center small"><?php echo htmlspecialchars($dev['dev_date']); ?></td>
                                                <td class="small"><?php echo htmlspecialchars($dev['dev_at']); ?></td>
                                                <td class="small"><?php echo htmlspecialchars($dev['dev_obj']); ?></td>
                                                <td class="text-center">
                                                    <?php if ($is_admin || $is_owner): ?>
                                                        <div class="d-flex justify-content-center gap-1">
                                                            <button class="btn btn-warning btn-sm shadow-sm" onclick='openDevModal("edit", <?php echo json_encode($dev); ?>)'><i class="bi bi-pencil-fill"></i></button>
                                                            <a href="process_development.php?delete=<?php echo $dev['dev_id']; ?>" class="btn btn-danger btn-sm shadow-sm" onclick="return confirm('ยืนยันการลบข้อมูล?')"><i class="bi bi-trash-fill"></i></a>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="text-muted"><i class="bi bi-lock-fill"></i></span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted">ไม่พบข้อมูลในระบบ</td>
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

    <div class="modal fade" id="devModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold" id="devModalTitle">กรอกข้อมูลงานพัฒนาวิชาการ</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="process_development.php" method="POST">
                    <div class="modal-body p-4">
                        <input type="hidden" name="action" id="devAction" value="add">
                        <input type="hidden" name="dev_id" id="devId">

                        <div class="mb-4">
                            <label class="form-label fw-bold">ผู้รับผิดชอบงาน</label>
                            <input type="text" class="form-control bg-light" value="<?php echo $full_name; ?>" readonly>
                            <input type="hidden" name="use_id" value="<?php echo $logged_in_user_id; ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">ชื่องาน/โครงการ</label>
                            <input type="text" name="dev_name" id="devName" class="form-control" placeholder="ระบุชื่องานหรือโครงการ..." required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">วันที่</label>
                                <input type="text" name="dev_date" id="devDate" class="form-control" placeholder="เช่น 12 ม.ค. 2567" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">สถานที่</label>
                                <input type="text" name="dev_at" id="devAt" class="form-control" placeholder="ระบุสถานที่จัดงาน..." required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">กลุ่มเรียนที่เข้าร่วม (ถ้ามี)</label>
                            <select name="section_id" id="devSection" class="form-select">
                                <option value="">--(เลือกกลุ่มเรียน)--</option>
                                <?php
                                $sections_res->data_seek(0);
                                while ($s = $sections_res->fetch_assoc()):
                                ?>
                                    <option value="<?php echo $s['section_id']; ?>"><?php echo htmlspecialchars($s['section_name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">วัตถุประสงค์</label>
                            <textarea name="dev_obj" id="devObj" class="form-control" rows="3" placeholder="ระบุวัตถุประสงค์ของงาน..."></textarea>
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
        const myModal = new bootstrap.Modal(document.getElementById('devModal'));

        function openDevModal(mode, data = null) {
            document.getElementById('devAction').value = mode;
            if (mode === 'add') {
                document.getElementById('devModalTitle').innerText = 'เพิ่มงานพัฒนาวิชาการใหม่';
                document.getElementById('devId').value = '';
                document.getElementById('devName').value = '';
                document.getElementById('devDate').value = '';
                document.getElementById('devAt').value = '';
                document.getElementById('devSection').value = '';
                document.getElementById('devObj').value = '';
            } else {
                document.getElementById('devModalTitle').innerText = 'แก้ไขข้อมูลงานพัฒนาวิชาการ';
                document.getElementById('devId').value = data.dev_id;
                document.getElementById('devName').value = data.dev_name;
                document.getElementById('devDate').value = data.dev_date;
                document.getElementById('devAt').value = data.dev_at;
                document.getElementById('devSection').value = data.section_id || '';
                document.getElementById('devObj').value = data.dev_obj;
            }
            myModal.show();
        }
    </script>
</body>

</html>