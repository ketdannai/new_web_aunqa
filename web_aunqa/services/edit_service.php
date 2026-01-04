<?php
// ไฟล์: services/edit_service.php
session_start();
require_once "../config.php";

// 1. ตรวจสอบการเข้าสู่ระบบ
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: ../login/login.php");
    exit;
}

$logged_in_user_id = $_SESSION["use_id"] ?? null;
$user_role = htmlspecialchars($_SESSION["use_role"] ?? 'user');
$is_admin = ($user_role == 'admin'); 

$serv_id_to_edit = $_GET['id'] ?? null;
$service_data = null;
$error_message = null;
$access_denied = false;

if (!$serv_id_to_edit) {
    $error_message = "ไม่พบรหัสข้อมูลที่ต้องการแก้ไข";
    $access_denied = true;
} else {
    // 2. ดึงข้อมูลงานบริการวิชาการ
    $sql = "SELECT * FROM services WHERE serv_id = ?";
    if ($stmt = $link->prepare($sql)) {
        $stmt->bind_param("i", $serv_id_to_edit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $service_data = $result->fetch_assoc();
            // ตรวจสอบสิทธิ์ (Admin หรือ เจ้าของงาน)
            if (!$is_admin && ($service_data['use_id'] != $logged_in_user_id)) {
                $error_message = "คุณไม่มีสิทธิ์แก้ไขข้อมูลนี้";
                $access_denied = true;
            }
        } else {
            $error_message = "ไม่พบข้อมูลงานบริการวิชาการ";
            $access_denied = true;
        }
        $stmt->close();
    }
}

// 3. ดึงรายชื่อผู้ใช้งานทั้งหมด (เพื่อใช้ใน Dropdown เลือกผู้รับผิดชอบ)
$user_list = [];
$user_sql = "SELECT use_id, use_title, use_fname, use_lname FROM users ORDER BY use_fname ASC";
if ($u_result = $link->query($user_sql)) {
    while ($u_row = $u_result->fetch_assoc()) {
        $user_list[] = $u_row;
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขงานบริการวิชาการ | AUN-QA System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root { --bg-dark: #222222; --accent-blue: #007bff; }
        body { font-family: 'Kanit', sans-serif; background-color: var(--bg-dark); }
        .content { padding: 40px; background-color: #ffffff; min-height: 100vh; }
        .card { border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .form-label { font-weight: 600; color: #333; }
    </style>
</head>
<body>

<div class="content">
    <div class="container">
        <h2 class="fw-bold text-primary mb-4"><i class="bi bi-pencil-square me-2"></i>แก้ไขข้อมูลงานบริการวิชาการ</h2>

        <?php if ($error_message): ?>
            <div class="alert alert-danger shadow-sm"><?php echo $error_message; ?></div>
            <a href="services.php" class="btn btn-secondary">กลับหน้าหลัก</a>
        <?php endif; ?>

        <?php if ($service_data && !$access_denied): ?>
        <div class="card p-4">
            <form action="update_service.php" method="POST">
                <input type="hidden" name="serv_id" value="<?php echo $service_data['serv_id']; ?>">

                <div class="mb-4">
                    <label for="use_id" class="form-label">ผู้รับผิดชอบงาน</label>
                    <select class="form-select" id="use_id" name="use_id" <?php echo $is_admin ? '' : 'disabled'; ?> required>
                        <?php foreach($user_list as $user): ?>
                            <option value="<?php echo $user['use_id']; ?>" <?php echo ($user['use_id'] == $service_data['use_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($user['use_title'] . $user['use_fname'] . " " . $user['use_lname']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if(!$is_admin): ?>
                        <input type="hidden" name="use_id" value="<?php echo $service_data['use_id']; ?>">
                        <div class="form-text">เฉพาะ Admin เท่านั้นที่สามารถเปลี่ยนผู้รับผิดชอบได้</div>
                    <?php endif; ?>
                </div>

                <div class="mb-4">
                    <label for="serv_name" class="form-label">ชื่องานบริการวิชาการ (serv_name)</label>
                    <textarea class="form-control" id="serv_name" name="serv_name" rows="4" required><?php echo htmlspecialchars($service_data['serv_name']); ?></textarea>
                </div>

                <div class="d-flex justify-content-end gap-2 border-top pt-4">
                    <a href="services.php" class="btn btn-secondary px-4">ยกเลิก</a>
                    <button type="submit" name="update_service" class="btn btn-primary px-5 fw-bold shadow-sm">
                        บันทึกการแก้ไข
                    </button>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>