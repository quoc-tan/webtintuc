<?php
session_start();
require 'connect.php'; // Gọi file kết nối PDO

// 1. Lấy danh sách chuyên mục từ Database cho Header
$sqlCM = "SELECT * FROM ChuyenMuc";
$stmtCM = $pdo->query($sqlCM);
$danhSachCM = $stmtCM->fetchAll();

$thongBao = '';
$loaiThongBao = 'danger';

// Xử lý khi người dùng bấm nút Đăng ký
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $hoTen = trim($_POST['hoten']);
    $email = trim($_POST['email']);
    $tenDN = trim($_POST['tendn']);
    $matKhau = trim($_POST['matkhau']); // Lấy mật khẩu người dùng nhập

    // Kiểm tra xem tên đăng nhập hoặc email đã tồn tại chưa
    $sqlCheck = "SELECT MaND FROM NguoiDung WHERE TenDN = :tendn OR Email = :email";
    $stmtCheck = $pdo->prepare($sqlCheck);
    $stmtCheck->execute(['tendn' => $tenDN, 'email' => $email]);

    if ($stmtCheck->rowCount() > 0) {
        $thongBao = "Tên đăng nhập hoặc Email đã được sử dụng. Vui lòng chọn tên khác!";
    } else {
        // Tạo mã người dùng ngẫu nhiên
        $maND = 'ND' . time(); 
        
        $vaiTro = 'ThanhVien';
        $ngayTao = date('Y-m-d H:i:s');

        // Thêm dữ liệu vào bảng NguoiDung
        $sqlInsert = "INSERT INTO NguoiDung (MaND, TenDN, MatKhau, Email, HoTen, VaiTro, NgayTao) 
                      VALUES (:mand, :tendn, :matkhau, :email, :hoten, :vaitro, :ngaytao)";
        $stmtInsert = $pdo->prepare($sqlInsert);
        
        $ketQua = $stmtInsert->execute([
            'mand' => $maND,
            'tendn' => $tenDN,
            'matkhau' => $matKhau,
            'email' => $email,
            'hoten' => $hoTen,
            'vaitro' => $vaiTro,
            'ngaytao' => $ngayTao
        ]);

        if ($ketQua) {
            $thongBao = "Đăng ký tài khoản thành công!";
            $loaiThongBao = 'success';
        } else {
            $thongBao = "Đã xảy ra lỗi trong quá trình tạo tài khoản. Vui lòng thử lại.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - Website Tin Tức</title>
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

        /* TẦNG 2: MENU CHUYÊN MỤC (NỀN XANH LÁ) */
        .header-menu-bar {
            background-color: #9f2587;
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
            background-color: #821d6c;
            color: #fff !important;
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
                                <li><a class="dropdown-item fw-bold text-success" href="login.php">👤 Đăng nhập</a></li>
                                <li><a class="dropdown-item fw-bold text-primary active" href="register.php">📝 Đăng ký</a></li>
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

    <!-- FORM ĐĂNG KÝ -->
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header text-white text-center" style="background-color: #9d259f;">
    <h3>ĐĂNG KÝ THÀNH VIÊN</h3>
</div>
                    <div class="card-body p-4">
                        
                        <?php if ($thongBao != ''): ?>
                            <div class="alert alert-<?= $loaiThongBao ?>">
                                <?= $thongBao ?>
                                <?php if ($loaiThongBao == 'success') echo '<br><a href="login.php" class="btn btn-sm btn-success mt-2 fw-bold">Đến trang Đăng nhập</a>'; ?>
                            </div>
                        <?php endif; ?>

                        <form action="register.php" method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Họ và tên</label>
                                <input type="text" name="hoten" class="form-control" required placeholder="Nhập họ và tên...">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Email</label>
                                <input type="email" name="email" class="form-control" required placeholder="Nhập địa chỉ email...">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Tên đăng nhập</label>
                                <input type="text" name="tendn" class="form-control" required placeholder="Nhập tên tài khoản...">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Mật khẩu</label>
                                <input type="password" name="matkhau" class="form-control" required placeholder="Nhập mật khẩu...">
                            </div>
                           <button type="submit" class="btn text-white w-100" style="background-color: #98099b; border-color: #9d259f;">Đăng ký ngay</button>
                        </form>
                        
                    </div>
                    <div class="card-footer text-center bg-white py-3 border-0">
                        Đã có tài khoản? <a href="login.php" class="text-primary fw-bold text-decoration-none">Đăng nhập tại đây</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>