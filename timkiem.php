<?php
session_start();
require 'connect.php';

// 1. Lấy danh sách chuyên mục từ Database cho Header
$sqlCM = "SELECT * FROM ChuyenMuc";
$stmtCM = $pdo->query($sqlCM);
$danhSachCM = $stmtCM->fetchAll();

// 2. Xử lý tìm kiếm
$tuKhoa = isset($_GET['tukhoa']) ? trim($_GET['tukhoa']) : '';
$danhSachBaiViet = [];

if (!empty($tuKhoa)) {
    // Tìm kiếm bài viết theo Tiêu đề hoặc Tóm tắt có chứa từ khóa
    $sql = "SELECT bv.*, cm.TenCM 
            FROM BaiViet bv 
            LEFT JOIN ChuyenMuc cm ON bv.MaCM = cm.MaCM 
            WHERE bv.TrangThai = 'DaDang' AND (bv.TieuDe LIKE :tukhoa1 OR bv.TomTat LIKE :tukhoa2) 
            ORDER BY bv.NgayTao DESC";
    $stmt = $pdo->prepare($sql);
    $paramSearch = "%$tuKhoa%";
    $stmt->execute([
        'tukhoa1' => $paramSearch,
        'tukhoa2' => $paramSearch
    ]);
    $danhSachBaiViet = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết quả tìm kiếm: <?= htmlspecialchars($tuKhoa) ?></title>
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
            background-color: #70259f;
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
            background-color: #241d82;
            color: #fff !important;
        }

        /* Styling danh sách bài viết */
        .news-title {
            color: #111;
            font-weight: bold;
            text-decoration: none;
            font-size: 1.2rem;
            transition: 0.2s;
        }
        .news-title:hover {
            color: #d32f2f;
        }
        .news-thumbnail {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 4px;
        }
    </style>
</head>
<body class="bg-light">

    <!-- ==================== HEADER 2 TẦNG ĐỒNG BỘ ==================== -->
    <header class="sticky-top">
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
                        <input class="form-control form-control-sm search-input-pill" type="search" name="tukhoa" value="<?= htmlspecialchars($tuKhoa) ?>" placeholder="Nhập tin tức cần tìm..." required>
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
                                <li><a class="dropdown-item" href="register.php">📝 Đăng ký</a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- TẦNG 2: MENU CHUYÊN MỤC -->
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

    <!-- KHU VỰC HIỂN THỊ KẾT QUẢ TÌM KIẾM -->
    <div class="container my-4" style="max-width: 900px;">
        <h4 class="fw-bold mb-4">🔍 Kết quả tìm kiếm cho: <span class="text-danger">"<?= htmlspecialchars($tuKhoa) ?>"</span></h4>

        <?php if (count($danhSachBaiViet) > 0): ?>
            <div class="row">
                <?php foreach ($danhSachBaiViet as $bv): ?>
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
                    <div class="col-12 mb-3">
                        <div class="card shadow-sm p-3">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <a href="chitiet.php?id=<?= $bv['MaBV'] ?>">
                                        <img src="<?= $imgSrc ?>" class="news-thumbnail" alt="<?= htmlspecialchars($bv['TieuDe']) ?>">
                                    </a>
                                </div>
                                <div class="col-md-9">
                                    <span class="badge bg-info text-dark mb-1"><?= htmlspecialchars($bv['TenCM'] ?? 'Tin tức') ?></span>
                                    <h5>
                                        <a href="chitiet.php?id=<?= $bv['MaBV'] ?>" class="news-title">
                                            <?= htmlspecialchars($bv['TieuDe']) ?>
                                        </a>
                                    </h5>
                                    <p class="text-muted small mb-1"><?= htmlspecialchars($bv['TomTat']) ?></p>
                                    <small class="text-secondary">🕒 <?= $bv['NgayTao'] ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-warning text-center">
                Không tìm thấy bài viết nào phù hợp với từ khóa <strong>"<?= htmlspecialchars($tuKhoa) ?>"</strong>!
            </div>
        <?php endif; ?>
    </div>

</body>
</html>