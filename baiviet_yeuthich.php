<?php
session_start();
require 'connect.php'; // Gọi file kết nối PDO

// 1. Lấy danh sách chuyên mục từ Database cho Header
$sqlCM = "SELECT * FROM ChuyenMuc";
$stmtCM = $pdo->query($sqlCM);
$danhSachCM = $stmtCM->fetchAll();

// Bắt buộc phải đăng nhập mới được xem bài viết đã lưu
if (!isset($_SESSION['MaND'])) {
    header("Location: login.php");
    exit();
}

$maND = $_SESSION['MaND'];

// 2. Lấy danh sách các bài viết đã lưu từ bảng YeuThich
$sql = "SELECT bv.*, cm.TenCM, yt.NgayLuu 
        FROM YeuThich yt 
        JOIN BaiViet bv ON yt.MaBV = bv.MaBV 
        LEFT JOIN ChuyenMuc cm ON bv.MaCM = cm.MaCM 
        WHERE yt.MaND = :mand 
        ORDER BY yt.NgayLuu DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute(['mand' => $maND]);
$danhSachDaLuu = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bài Viết Đã Lưu - Website Tin Tức</title>
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
            background-color: #9f258d;
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
            background-color: #761d82;
            color: #fff !important;
        }

        .news-thumbnail-fav {
            width: 100%;
            height: 160px;
            object-fit: cover;
            border-top-left-radius: 6px;
            border-top-right-radius: 6px;
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
                                <li><a class="dropdown-item active" href="baiviet_yeuthich.php">❤️ Bài viết đã lưu</a></li>
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

    <!-- DANH SÁCH BÀI VIẾT ĐÃ LƯU -->
    <div class="container my-4">
        <h4 class="fw-bold text-danger mb-4">❤️ DANH SÁCH BÀI VIẾT ĐÃ LƯU</h4>

        <div class="row">
            <?php foreach ($danhSachDaLuu as $bv): ?>
                <?php 
                    $imgSrc = 'https://via.placeholder.com/400x250/cccccc/969696?text=Tin+Tuc';
                    if (!empty($bv['HinhAnh'])) {
                        if (strpos($bv['HinhAnh'], 'upload/') !== false || strpos($bv['HinhAnh'], 'uploads/') !== false) {
                            $imgSrc = htmlspecialchars($bv['HinhAnh']);
                        } else {
                            $imgSrc = 'upload/' . htmlspecialchars($bv['HinhAnh']);
                        }
                    }
                ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm border-0 rounded-3">
                        <a href="chitiet.php?id=<?= $bv['MaBV'] ?>">
                            <img src="<?= $imgSrc ?>" class="news-thumbnail-fav" alt="<?= htmlspecialchars($bv['TieuDe']) ?>">
                        </a>
                        <div class="card-body">
                            <span class="badge bg-info text-dark mb-2"><?= htmlspecialchars($bv['TenCM'] ?? 'Chưa rõ') ?></span>
                            <h5 class="card-title fw-bold">
                                <a href="chitiet.php?id=<?= $bv['MaBV'] ?>" class="text-decoration-none text-dark">
                                    <?= htmlspecialchars($bv['TieuDe']) ?>
                                </a>
                            </h5>
                            <h6 class="card-subtitle mb-2 text-muted small">🕒 Đã lưu: <?= $bv['NgayLuu'] ?></h6>
                            <p class="card-text text-secondary mt-2 small"><?= htmlspecialchars($bv['TomTat']) ?></p>
                        </div>
                        <div class="card-footer bg-white border-0 pb-3 d-flex justify-content-between align-items-center">
                            <a href="chitiet.php?id=<?= $bv['MaBV'] ?>" class="btn btn-outline-primary btn-sm fw-bold">Đọc tiếp &rarr;</a>
                            <a href="chitiet.php?id=<?= $bv['MaBV'] ?>&action=boluu" class="btn btn-outline-danger btn-sm fw-bold" onclick="return confirm('Xóa bài này khỏi danh sách đã lưu?');">Xóa lưu</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (empty($danhSachDaLuu)): ?>
                <div class="col-12">
                    <div class="alert alert-info text-center py-4 rounded-3 shadow-sm">
                        Anh chưa lưu bài viết nào. Hãy bấm nút 🤍 <strong>Lưu bài viết</strong> khi đọc bài để xem lại sau nhé!
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>