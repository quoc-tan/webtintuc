<?php
session_start();
require 'connect.php';

// 1. Lấy danh sách chuyên mục từ Database cho Header
$sqlCM = "SELECT * FROM ChuyenMuc";
$stmtCM = $pdo->query($sqlCM);
$danhSachCM = $stmtCM->fetchAll();

// Bắt buộc phải đăng nhập mới được đổi mật khẩu
if (!isset($_SESSION['MaND'])) {
    header("Location: login.php");
    exit();
}

$thongBao = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_doimk'])) {
    $matKhauCu = trim($_POST['matkhau_cu']);
    $matKhauMoi = trim($_POST['matkhau_moi']);
    $xacNhanMK = trim($_POST['xacnhan_mk']);
    $maND = $_SESSION['MaND'];

    if (empty($matKhauCu) || empty($matKhauMoi) || empty($xacNhanMK)) {
        $thongBao = "<div class='alert alert-warning'>Vui lòng điền đầy đủ các trường thông tin!</div>";
    } elseif ($matKhauMoi !== $xacNhanMK) {
        $thongBao = "<div class='alert alert-danger'>Mật khẩu mới và xác nhận mật khẩu không trùng khớp!</div>";
    } else {
        // Kiểm tra mật khẩu hiện tại trong CSDL
        $stmt = $pdo->prepare("SELECT MatKhau FROM NguoiDung WHERE MaND = :mand");
        $stmt->execute(['mand' => $maND]);
        $user = $stmt->fetch();

        if ($user && $matKhauCu === $user['MatKhau']) {
            // Cập nhật mật khẩu mới vào cơ sở dữ liệu
            $stmtUp = $pdo->prepare("UPDATE NguoiDung SET MatKhau = :matkhaumoi WHERE MaND = :mand");
            $stmtUp->execute(['matkhaumoi' => $matKhauMoi, 'mand' => $maND]);
            $thongBao = "<div class='alert alert-success'>🎉 Đổi mật khẩu thành công! Mật khẩu mới đã được cập nhật.</div>";
        } else {
            $thongBao = "<div class='alert alert-danger'>Mật khẩu hiện tại nhập không chính xác!</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đổi Mật Khẩu - Website Tin Tức</title>
    <!-- Nhúng Bootstrap 5 CSS & JS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        html, body {
            max-width: 100%;
            overflow-x: hidden;
        }

        /* TẦNG 1: HEADER TRÊN (NỀN TRẮNG) */
        .header-top {
            background-color: #ffffff;
            border-bottom: 1px solid #e5e5e5;
            padding: 10px 0;
        }
        .search-input-pill {
            border-radius: 20px 0 0 20px;
            padding-left: 15px;
            border-right: none;
            width: 220px;
        }
        .search-btn-pill {
            border-radius: 0 20px 20px 0;
            padding-right: 15px;
        }

        /* CSS LOGO STYLING PHONG CÁCH CAFEBIZ */
        .brand-logo {
            display: inline-flex;
            flex-direction: column;
            align-items: flex-start;
            text-decoration: none !important;
            line-height: 1;
        }
        .brand-main {
            font-family: 'Arial Black', 'Trebuchet MS', sans-serif;
            font-size: 2.2rem;
            font-weight: 900;
            letter-spacing: -1px;
        }
        .brand-c {
            display: inline-block;
            position: relative;
            color: #d32f2f;
            font-size: 2.5rem;
            margin-right: 2px;
        }
        .brand-c::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-52%, -50%) rotate(-15deg);
            width: 40px;
            height: 40px;
            border: 3px solid #d32f2f;
            border-radius: 50%;
            border-right-color: transparent;
            pointer-events: none;
        }
        .brand-text-red { color: #d32f2f; }
        .brand-text-dark { color: #333333; }
        .brand-sub {
            font-size: 0.65rem;
            color: #666666;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-top: 3px;
        }

        /* TẦNG 2: MENU CHUYÊN MỤC (NỀN TÍM) */
        .header-menu-bar {
            background-color: #8e24aa;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .category-nav {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
            margin: 0;
            padding: 0;
            list-style: none;
            width: 100%;
        }
        .category-nav .nav-link {
            color: #ffffff !important;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.8rem;
            padding: 10px 8px;
            white-space: nowrap;
            transition: background 0.2s;
        }
        .category-nav .nav-link:hover, .category-nav .nav-link.active {
            background-color: #6a1b9a;
            color: #fff !important;
        }

        /* CUSTOM KHUNG MÀU TÍM CHO TRANG ĐỔI MẬT KHẨU */
        .bg-purple {
            background-color: #8e24aa !important;
        }
        .btn-purple {
            background-color: #8e24aa !important;
            color: #ffffff !important;
            border: none;
            transition: background 0.2s;
        }
        .btn-purple:hover {
            background-color: #7b1fa2 !important;
        }
    </style>
</head>
<body class="bg-light">

    <!-- ==================== HEADER 2 TẦNG ĐỒNG BỘ ==================== -->
    <header class="sticky-top mb-4">
        <!-- TẦNG 1: LOGO STYLING CAFEBIZ + TÌM KIẾM + MENU TÀI KHOẢN -->
        <div class="header-top">
            <div class="container d-flex align-items-center justify-content-between">
                <!-- Logo bên trái phong cách CafeBiz -->
                <a href="index.php" class="brand-logo">
                    <div class="brand-main">
                        <span class="brand-c">T</span><span class="brand-text-red">an</span><span class="brand-text-dark">Tu</span>
                    </div>
                    <div class="brand-sub">Tin Tức</div>
                </a>
                
                <!-- Cụm Tìm kiếm & Menu tài khoản bên phải -->
                <div class="d-flex align-items-center gap-3">
                    <form class="d-flex" action="timkiem.php" method="GET">
                        <input class="form-control form-control-sm search-input-pill" type="search" name="tukhoa" placeholder="Nhập tin tức cần tìm..." required>
                        <button class="btn btn-outline-success btn-sm search-btn-pill" type="submit">🔍</button>
                    </form>
                    
                    <!-- MENU SỔ XUỐNG 3 GẠCH -->
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle d-flex align-items-center gap-1" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="fs-5 lh-1">☰</span>
                            <?php if (isset($_SESSION['MaND'])): ?>
                                <small class="fw-bold d-none d-sm-inline"><?= htmlspecialchars($_SESSION['HoTen']) ?></small>
                            <?php endif; ?>
                        </button>
                        
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <?php if (isset($_SESSION['MaND'])): ?>
                                <li class="dropdown-header">Chào, <strong><?= htmlspecialchars($_SESSION['HoTen']) ?></strong></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="baiviet_yeuthich.php">❤️ Bài viết đã lưu</a></li>
                                <li><a class="dropdown-item active" href="doimatkhau.php">🔑 Đổi mật khẩu</a></li>
                                
                                <?php if (isset($_SESSION['VaiTro'])): ?>
                                    <?php if ($_SESSION['VaiTro'] == 'QuanTriVien'): ?>
                                        <li><a class="dropdown-item fw-bold text-warning" href="admin.php">⚙️ Trang Quản Trị</a></li>
                                    <?php elseif ($_SESSION['VaiTro'] == 'BienTapVien'): ?>
                                        <li><a class="dropdown-item fw-bold text-info" href="admin_baiviet.php">✍️ Trang Biên Tập</a></li>
                                    <?php endif; ?>
                                <?php endif; ?>
                                
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="logout.php">🚪 Đăng xuất</a></li>
                            <?php else: ?>
                                <li><a class="dropdown-item fw-bold text-success" href="login.php">👤 Đăng nhập</a></li>
                                <li><a class="dropdown-item" href="register.php">📝 Đăng ký</a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- TẦNG 2: THANH MENU CHUYÊN MỤC -->
        <nav class="header-menu-bar">
            <div class="container">
                <ul class="category-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">🏠 Tất cả</a>
                    </li>
                    <?php foreach ($danhSachCM as $cm): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?macm=<?= $cm['MaCM'] ?>">
                               <?= htmlspecialchars($cm['TenCM']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </nav>
    </header>

    <!-- FORM ĐỔI MẬT KHẨU (ĐÃ SỬA SANG MÀU TÍM) -->
    <div class="container my-5" style="max-width: 480px;">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-purple text-white fw-bold text-center py-3">
                <h5 class="mb-0 fs-5">🔑 ĐỔI MẬT KHẨU TÀI KHOẢN</h5>
            </div>
            <div class="card-body p-4">
                <?= $thongBao ?>

                <form action="doimatkhau.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mật khẩu hiện tại <span class="text-danger">*</span></label>
                        <input type="password" name="matkhau_cu" class="form-control" placeholder="Nhập mật khẩu cũ..." required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mật khẩu mới <span class="text-danger">*</span></label>
                        <input type="password" name="matkhau_moi" class="form-control" placeholder="Nhập mật khẩu mới..." required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Xác nhận mật khẩu mới <span class="text-danger">*</span></label>
                        <input type="password" name="xacnhan_mk" class="form-control" placeholder="Nhập lại mật khẩu mới..." required>
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" name="btn_doimk" class="btn btn-purple fw-bold py-2">Cập Nhật Mật Khẩu</button>
                        <a href="index.php" class="btn btn-outline-secondary">Hủy bỏ</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>
</html>