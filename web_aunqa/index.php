<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>หน้าหลัก | ระบบ AUN-QA Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        /* กำหนดโทนสีใหม่ให้ดูน่าสนใจยิ่งขึ้น */
        :root {
            --primary-color: #007bff; /* สีน้ำเงินหลัก */
            --primary-dark: #0056b3;  /* สีน้ำเงินเข้ม */
            --secondary-bg: #e9ecef;  /* สีพื้นหลังเทาอ่อนมาก */
            --card-shadow: 0 10px 30px rgba(0, 0, 0, 0.08); /* เงาที่ชัดเจนขึ้น */
        }
        body { 
            font-family: 'Kanit', sans-serif; 
            background-color: var(--secondary-bg); 
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Navbar/Header */
        .main-navbar {
            background-color: var(--primary-color);
            padding: 0.75rem 0;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }
        .navbar-brand {
            color: white !important;
            font-weight: 700;
            font-size: 1.6rem;
        }
        .btn-login {
            background-color: #ffc107; /* สีเหลืองทอง */
            border-color: #ffc107;
            color: #212529;
            font-weight: 600;
        }
        .btn-register {
            background-color: white;
            border-color: white;
            color: var(--primary-dark);
            font-weight: 600;
        }

        /* Hero Section (ปรับปรุงการจัดวาง) */
        .hero-section {
            flex-grow: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 80px 0; /* เพิ่มระยะห่าง */
        }
        .hero-title {
            color: var(--primary-dark);
            font-size: 3.5rem; /* ตัวใหญ่ขึ้น */
            font-weight: 700;
            margin-bottom: 10px;
        }
        .hero-subtitle {
            font-size: 1.4rem;
            color: #495057; /* สีเทาเข้ม */
            margin-bottom: 40px;
            font-weight: 400;
        }
        .main-card {
            border: none;
            border-radius: 12px; /* มุมโค้งมนขึ้น */
            box-shadow: var(--card-shadow);
            max-width: 900px;
            padding: 40px;
            background-color: #ffffff; /* พื้นหลังสีขาวชัดเจน */
        }
        .card-body p {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #343a40;
        }
        .btn-hero {
            padding: 12px 30px;
            font-size: 1.15rem;
            border-radius: 8px;
            font-weight: 600;
        }
        
        /* Footer */
        .main-footer {
            background-color: var(--primary-dark);
            color: white;
            padding: 15px 0;
            text-align: center;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

<nav class="main-navbar">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-patch-check-fill me-2"></i>AUN-QA System
            </a>
            <div class="navbar-actions">
                <a href="login/login.php" class="btn btn-sm btn-action btn-login">
                    <i class="bi bi-box-arrow-in-right me-1"></i>เข้าสู่ระบบ
                </a>
                <a href="login/register.php" class="btn btn-sm btn-action btn-register">
                    <i class="bi bi-person-plus-fill me-1"></i>ลงทะเบียน
                </a>
            </div>
        </div>
    </div>
</nav>

<div class="hero-section">
    <div class="container">
        <div class="main-card mx-auto">
            <div class="card-body text-center">
                <h1 class="hero-title">ระบบสารสนเทศ AUN-QA</h1>
                <p class="hero-subtitle">เครื่องมือหลักสำหรับการจัดการและติดตามข้อมูลการประกันคุณภาพหลักสูตร</p>
                
                <p class="mb-4">
                    ยินดีต้อนรับสู่ระบบบริหารจัดการข้อมูลการประกันคุณภาพการศึกษาตามเกณฑ์ AUN-QA ระบบนี้ได้รับการออกแบบมาเพื่อช่วยให้คณาจารย์และบุคลากรสามารถรวบรวม วิเคราะห์ และติดตามผลการประเมินคุณภาพหลักสูตรได้อย่างเป็นระบบ และง่ายต่อการเตรียมรายงานเพื่อรับการประเมิน
                </p>
                
                <div class="mt-5">
                    <a href="login/login.php" class="btn btn-primary btn-hero me-4 shadow">
                        <i class="bi bi-door-open-fill me-2"></i>เริ่มใช้งาน / เข้าสู่ระบบ
                    </a>
                    <a href="login/register.php" class="btn btn-outline-secondary btn-hero">
                        <i class="bi bi-clipboard-check me-2"></i>สมัครสมาชิกใหม่
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<footer class="main-footer">
    <div class="container">
        &copy; <?php echo date("Y"); ?> Information System for Curriculum Quality Assurance based on AUN-QA Criteria (AUNIT)
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>