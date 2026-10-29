<?php
session_start();
require 'connect.php'; // Gọi file kết nối PDO

// 1. Lấy danh sách chuyên mục từ Database cho Header
$sqlCM = "SELECT * FROM ChuyenMuc";
$stmtCM = $pdo->query($sqlCM);
$danhSachCM = $stmtCM->fetchAll();

$thongBao = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tenDN = trim($_POST['tendn']);
    $matKhau = trim($_POST['matkhau']);

    // Truy vấn tìm tài khoản trong CSDL
    $sql = "SELECT * FROM NguoiDung WHERE TenDN = :tendn";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['tendn' => $tenDN]);
    $user = $stmt->fetch();

    // Nếu tìm thấy tài khoản
    if ($user) {
        // So sánh mật khẩu
        if ($matKhau == $user['MatKhau']) {
            // Đăng nhập thành công, lưu thông tin vào Session
            $_SESSION['MaND'] = $user['MaND'];
            $_SESSION['HoTen'] = $user['HoTen'];
            $_SESSION['VaiTro'] = $user['VaiTro'];

            // Kiểm tra phân quyền để chuyển hướng
            if ($user['VaiTro'] == 'QuanTriVien' || $user['VaiTro'] == 'BienTapVien') {
                header("Location: admin.php"); // Chuyển vào trang quản trị
            } else {
                header("Location: index.php"); // Thành viên thì về trang chủ
            }
            exit();
        } else {
            $thongBao = "Mật khẩu không chính xác!";
        }
    } else {
        $thongBao = "Tài khoản không tồn tại!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập thành viên - TanTu Tin Tức</title>
    <!-- Nhúng Bootstrap 5 CSS & JS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- FontAwesome icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        html, body {
            max-width: 100%;
            overflow-x: hidden;
            background-color: #f8f9fa;
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

        /* CSS LOGO STYLING PHONG CÁCH TANTU */
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
            border: 3px solid #b10b0b;
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

        /* TẦNG 2: MENU CHUYÊN MỤC */
        .header-menu-bar {
            background-color: #ac0808;
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
            background-color: #d31417;
            color: #fff !important;
        }

        /* GIAO DIỆN TRANG ĐĂNG NHẬP CHIA ĐÔI */
        .login-wrapper {
            min-height: 75vh;
        }
        .form-section {
            padding: 30px 40px;
            background: #ffffff;
        }
        .btn-google {
            border: 1px solid #ced4da;
            background: #fff;
            color: #333;
            font-weight: 500;
            border-radius: 8px;
        }
        .btn-google:hover {
            background: #f8f9fa;
        }
        
        /* CỘT PHẢI: HIỂN THỊ ẢNH LOGO1 */
        .banner-image-section {
            background-color: #ffffff;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            border-top-right-radius: 0.75rem;
            border-bottom-right-radius: 0.75rem;
        }
        .banner-image-section img {
            max-width: 100%;
            max-height: 500px;
            object-fit: contain;
        }
    </style>
</head>
<body>

    <!-- ==================== HEADER 2 TẦNG ĐỒNG BỘ ==================== -->
    <header class="sticky-top mb-3">
        <!-- TẦNG 1: LOGO STYLING TANTU + TÌM KIẾM + MENU TÀI KHOẢN -->
        <div class="header-top">
            <div class="container d-flex align-items-center justify-content-between">
                <!-- Logo TanTu Tin Tức -->
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
                                <li><a class="dropdown-item" href="doimatkhau.php">🔑 Đổi mật khẩu</a></li>
                                
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
                                <li><a class="dropdown-item fw-bold text-success active" href="login.php">👤 Đăng nhập</a></li>
                                <li><a class="dropdown-item fw-bold text-primary" href="register.php">📝 Đăng ký</a></li>
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

    <!-- ==================== NỘI DUNG FORM ĐĂNG NHẬP CHIA ĐÔI ==================== -->
    <div class="container my-4 login-wrapper">
        <div class="row align-items-stretch justify-content-center bg-white shadow-sm rounded-4 border overflow-hidden">
            
            <!-- CỘT TRÁI: FORM ĐĂNG NHẬP -->
            <div class="col-md-6 form-section d-flex flex-column justify-content-center">
                <div class="mb-3">
                    <h3 class="fw-bold text-dark">Đăng nhập tài khoản</h3>
                </div>

                <!-- Nút đăng nhập nhanh bằng Google -->
                <button type="button" class="btn btn-google w-100 py-2 mb-3 d-flex align-items-center justify-content-center gap-2">
                    <i class="fa-brands fa-google text-danger"></i> Đăng nhập bằng Google
                </button>

                <div class="text-center text-muted my-3 position-relative">
                    <hr>
                    <span class="position-absolute top-50 start-50 translate-middle bg-white px-3 text-muted small">Hoặc đăng nhập bằng</span>
                </div>

                <!-- Thông báo trạng thái -->
                <?php if ($thongBao != ''): ?>
                    <div class="alert alert-danger py-2 small">
                        <?= $thongBao ?>
                    </div>
                <?php endif; ?>

                <!-- Form xử lý PHP Đăng Nhập -->
                <form action="login.php" method="POST">
                    <div class="mb-2">
                        <label class="form-label text-muted small fw-bold">Tên đăng nhập</label>
                        <input type="text" name="tendn" class="form-control" required placeholder="Nhập tên tài khoản...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Mật khẩu</label>
                        <input type="password" name="matkhau" class="form-control" required placeholder="Nhập mật khẩu...">
                    </div>

                    <button type="submit" class="btn text-white w-100 py-2 fw-bold" style="background-color: #d32f2f;">Đăng nhập</button>
                </form>

                <div class="text-center mt-3 small">
                    Chưa có tài khoản thành viên? <a href="register.php" class="text-danger fw-bold text-decoration-none">Đăng ký ngay</a>
                </div>
            </div>

            <!-- CỘT PHẢI: HIỂN THỊ ẢNH LOGO1 TỪ THƯ MỤC UPLOAD -->
            <div class="col-md-6 d-none d-md-block p-0">
                <div class="banner-image-section">
                    <img src="upload/logo1.jpg" alt="Minh họa TanTu Tin Tức" class="img-fluid">
                </div>
            </div>

        </div>
    </div>

</body>
</html>