<?php
session_start();
require 'connect.php';

// 1. Lấy danh sách chuyên mục từ Database
$sqlCM = "SELECT * FROM ChuyenMuc";
$stmtCM = $pdo->query($sqlCM);
$danhSachCM = $stmtCM->fetchAll();

// =================== XỬ LÝ PHÂN TRANG ===================
$limit = 6; // 6 bài viết/trang (3 bài dọc + 3 bài ngang)
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// 2. Lấy danh sách bài viết
if (isset($_GET['macm'])) {
    $maCM = $_GET['macm'];
    
    $sqlCount = "SELECT COUNT(*) FROM BaiViet WHERE TrangThai = 'DaDang' AND MaCM = :macm";
    $stmtCount = $pdo->prepare($sqlCount);
    $stmtCount->execute(['macm' => $maCM]);
    $totalRecords = $stmtCount->fetchColumn();
    
    $sql = "SELECT MaBV, TieuDe, TomTat, NgayTao, HinhAnh FROM BaiViet WHERE TrangThai = 'DaDang' AND MaCM = :macm ORDER BY NgayTao DESC LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':macm', $maCM, PDO::PARAM_STR);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
} else {
    $sqlCount = "SELECT COUNT(*) FROM BaiViet WHERE TrangThai = 'DaDang'";
    $stmtCount = $pdo->query($sqlCount);
    $totalRecords = $stmtCount->fetchColumn();
    
    $sql = "SELECT MaBV, TieuDe, TomTat, NgayTao, HinhAnh FROM BaiViet WHERE TrangThai = 'DaDang' ORDER BY NgayTao DESC LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
}

$danhSachBaiViet = $stmt->fetchAll();
$totalPages = ceil($totalRecords / $limit);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Chủ - Website Tin Tức</title>
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
            padding: 8px 0;
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

        /* TẦNG 2: MENU CHUYÊN MỤC (NỀN XANH LÁ) */
        .header-menu-bar {
            background-color: #589f25;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .category-nav {
            display: flex;
            flex-direction: row;
            justify-content: space-between; /* Trải đều từ trái sang phải */
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
            font-size: 0.8rem; /* Thu nhỏ cỡ chữ để vừa 100% màn hình */
            padding: 10px 8px; /* Tối ưu khoảng cách */
            white-space: nowrap;
            transition: background 0.2s;
        }
        .category-nav .nav-link:hover, .category-nav .nav-link.active {
            background-color: #46821d;
            color: #fff !important;
        }

        /* CSS bài viết Timeline dọc */
        .news-timeline {
            border-left: 2px solid #eaeaea; 
            padding-left: 30px;
            margin-left: 15px;
        }
        .news-item {
            position: relative;
            margin-bottom: 35px;
        }
        .news-dot {
            position: absolute;
            left: -37px; 
            top: 15px;
            width: 12px;
            height: 12px;
            background-color: #a6204c; 
            border-radius: 50%;
        }
        .news-title {
            color: #111;
            font-weight: bold;
            text-decoration: none;
            font-size: 1.35rem;
            line-height: 1.4;
            transition: 0.2s;
        }
        .news-title:hover {
            color: #a6204c;
        }
        .news-thumbnail {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-radius: 4px;
        }
        .news-summary {
            color: #555;
            font-size: 1rem;
            margin-top: 8px;
            line-height: 1.5;
        }

        /* CSS Phân trang */
        .custom-pagination {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            margin-top: 40px;
            border-top: 1px solid #eaeaea;
            padding-top: 20px;
        }
        .custom-pagination a, .custom-pagination span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            text-decoration: none;
            color: #666;
            font-size: 1rem;
            transition: all 0.2s;
        }
        .custom-pagination a:hover {
            background-color: #f8f9fa;
            color: #a6204c;
        }
        .custom-pagination a.active {
            background-color: #a6204c; 
            color: white;
            border-color: #a6204c;
        }
    </style>
</head>
<body class="bg-white">

    <!-- ==================== HEADER 2 TẦNG ==================== -->
    <header class="sticky-top">
        <!-- TẦNG 1: LOGO + TÌM KIẾM + NÚT MENU TÀI KHOẢN (3 GẠCH) -->
        <div class="header-top">
            <div class="container d-flex align-items-center justify-content-between">
                <!-- Logo bên trái -->
                <a class="navbar-brand fw-bold text-success fs-3 d-flex align-items-center" href="index.php">
                    <span class="bg-success text-white px-2 py-1 rounded-3 me-1 fs-4">TanTu</span> TinTức
                </a>
                
                <!-- Cụm Tìm kiếm & Menu tài khoản bên phải -->
                <div class="d-flex align-items-center gap-3">
                    <form class="d-flex" action="timkiem.php" method="GET">
                        <input class="form-control form-control-sm search-input-pill" type="search" name="tukhoa" placeholder="Nhập tin tức cần tìm..." required>
                        <button class="btn btn-outline-success btn-sm search-btn-pill" type="submit">🔍</button>
                    </form>
                    
                    <!-- MENU SỔ XUỐNG DẠNG 3 GẠCH (DROPDOWN) -->
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
                                <li><a class="dropdown-item" href="baiviet_yeuthich.php">❤️ Tin đã lưu</a></li>
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

        <!-- TẦNG 2: THANH MENU CHUYÊN MỤC (Dàn vừa khít 100%) -->
        <nav class="header-menu-bar">
            <div class="container">
                <ul class="category-nav">
                    <li class="nav-item">
                        <a class="nav-link <?= !isset($_GET['macm']) ? 'active' : '' ?>" href="index.php">🏠 Tất cả</a>
                    </li>
                    <?php foreach ($danhSachCM as $cm): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= (isset($_GET['macm']) && $_GET['macm'] == $cm['MaCM']) ? 'active' : '' ?>" 
                               href="index.php?macm=<?= $cm['MaCM'] ?>">
                               <?= htmlspecialchars($cm['TenCM']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </nav>
    </header>

    <!-- Nội dung chính -->
    <div class="container mt-4 mb-5">
        <div class="row">
            <div class="col-lg-9 col-md-10 mx-auto mx-lg-0">
                
                <?php if (count($danhSachBaiViet) > 0): ?>
                    
                    <?php 
                    $total = count($danhSachBaiViet);
                    for ($i = 0; $i < $total; $i++): 
                        $baiViet = $danhSachBaiViet[$i];
                        $chuKy = $i % 6; // Chu kỳ 3 dọc + 3 ngang
                        
                        $imgSrc = 'https://via.placeholder.com/400x250/cccccc/969696?text=Tin+Tuc';
                        if (!empty($baiViet['HinhAnh'])) {
                            if (strpos($baiViet['HinhAnh'], 'upload/') !== false || strpos($baiViet['HinhAnh'], 'uploads/') !== false) {
                                $imgSrc = htmlspecialchars($baiViet['HinhAnh']);
                            } else {
                                $imgSrc = 'upload/' . htmlspecialchars($baiViet['HinhAnh']);
                            }
                        }
                    ?>
                        
                        <!-- MỞ BLOCK 3 BÀI DỌC -->
                        <?php if ($chuKy == 0): ?>
                            <div class="news-timeline">
                        <?php endif; ?>

                        <!-- GIAO DIỆN BÀI DỌC (Bài 1, 2, 3) -->
                        <?php if ($chuKy < 3): ?>
                            <div class="news-item">
                                <div class="news-dot"></div>
                                <div class="row align-items-start">
                                    <div class="col-md-4 col-sm-12 mb-3 mb-md-0">
                                        <a href="chitiet.php?id=<?= $baiViet['MaBV'] ?>">
                                            <img src="<?= $imgSrc ?>" class="news-thumbnail" alt="<?= htmlspecialchars($baiViet['TieuDe']) ?>">
                                        </a>
                                    </div>
                                    <div class="col-md-8 col-sm-12">
                                        <h4 class="mb-2">
                                            <a href="chitiet.php?id=<?= $baiViet['MaBV'] ?>" class="news-title">
                                                <?= htmlspecialchars($baiViet['TieuDe']) ?>
                                            </a>
                                        </h4>
                                        <p class="news-summary">
                                            <?= htmlspecialchars($baiViet['TomTat']) ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- ĐÓNG BLOCK 3 BÀI DỌC -->
                        <?php if ($chuKy == 2 || ($chuKy < 3 && $i == $total - 1)): ?>
                            </div> 
                        <?php endif; ?>

                        <!-- MỞ BLOCK 3 BÀI NGANG -->
                        <?php if ($chuKy == 3): ?>
                            <div class="row mt-4 mb-5 pb-4 border-bottom">
                        <?php endif; ?>

                        <!-- GIAO DIỆN BÀI NGANG (Bài 4, 5, 6) -->
                        <?php if ($chuKy >= 3): ?>
                            <div class="col-md-4 col-sm-6 mb-4">
                                <a href="chitiet.php?id=<?= $baiViet['MaBV'] ?>">
                                    <img src="<?= $imgSrc ?>" class="w-100" style="height: 160px; object-fit: cover; border-radius: 4px;" alt="<?= htmlspecialchars($baiViet['TieuDe']) ?>">
                                </a>
                                <h5 class="mt-3">
                                    <a href="chitiet.php?id=<?= $baiViet['MaBV'] ?>" class="news-title" style="font-size: 1.1rem; line-height: 1.4;">
                                        <?= htmlspecialchars($baiViet['TieuDe']) ?>
                                    </a>
                                </h5>
                            </div>
                        <?php endif; ?>

                        <!-- ĐÓNG BLOCK 3 BÀI NGANG -->
                        <?php if ($chuKy == 5 || ($chuKy >= 3 && $i == $total - 1)): ?>
                            </div>
                        <?php endif; ?>

                    <?php endfor; ?>

                    <!-- THANH PHÂN TRANG -->
                    <?php if ($totalPages > 1): ?>
                        <div class="custom-pagination">
                            <?php 
                            $urlQuery = isset($_GET['macm']) ? "&macm=" . $_GET['macm'] : "";
                            $maxVisible = 10;
                            $startPage = max(1, $page - floor($maxVisible / 2));
                            $endPage = $startPage + $maxVisible - 1;
                            
                            if ($endPage > $totalPages) {
                                $endPage = $totalPages;
                                $startPage = max(1, $endPage - $maxVisible + 1);
                            }
                            ?>

                            <?php if ($page > 1): ?>
                                <a href="?page=<?= ($page - 1) ?><?= $urlQuery ?>" title="Trang trước">&lt;</a>
                            <?php endif; ?>

                            <?php if ($startPage > 1): ?>
                                <a href="?page=1<?= $urlQuery ?>">1</a>
                                <?php if ($startPage > 2): ?>
                                    <span style="border: none; width: auto; padding: 0 5px;">...</span>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                <a href="?page=<?= $i ?><?= $urlQuery ?>" class="<?= ($i == $page) ? 'active' : '' ?>">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>

                            <?php if ($endPage < $totalPages): ?>
                                <?php if ($endPage < $totalPages - 1): ?>
                                    <span style="border: none; width: auto; padding: 0 5px;">...</span>
                                <?php endif; ?>
                                <a href="?page=<?= $totalPages ?><?= $urlQuery ?>"><?= $totalPages ?></a>
                            <?php endif; ?>

                            <?php if ($page < $totalPages): ?>
                                <a href="?page=<?= ($page + 1) ?><?= $urlQuery ?>" title="Trang tiếp theo">&gt;</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="alert alert-warning text-center">Hiện chưa có bài viết nào!</div>
                <?php endif; ?>

            </div>
        </div>
    </div>

</body>
</html>