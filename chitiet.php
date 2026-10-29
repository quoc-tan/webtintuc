<?php
session_start();
require 'connect.php';

// 1. Lấy danh sách chuyên mục từ Database cho Header
$sqlCM = "SELECT * FROM ChuyenMuc";
$stmtCM = $pdo->query($sqlCM);
$danhSachCM = $stmtCM->fetchAll();

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$maBV = $_GET['id'];
$maND = $_SESSION['MaND'] ?? null;
$thongBao = "";

// 2. Tăng lượt xem & lấy chi tiết bài viết
$pdo->prepare("UPDATE BaiViet SET LuotXem = LuotXem + 1 WHERE MaBV = :mabv")->execute(['mabv' => $maBV]);

$stmt = $pdo->prepare("SELECT bv.*, cm.TenCM, nd.HoTen AS TacGia 
                       FROM BaiViet bv 
                       LEFT JOIN ChuyenMuc cm ON bv.MaCM = cm.MaCM 
                       LEFT JOIN NguoiDung nd ON bv.MaND = nd.MaND 
                       WHERE bv.MaBV = :mabv");
$stmt->execute(['mabv' => $maBV]);
$baiViet = $stmt->fetch();

if (!$baiViet) {
    die("<div class='container mt-5 alert alert-danger'>Bài viết không tồn tại! <a href='index.php'>Về trang chủ</a></div>");
}

// 3. Xử lý Lưu / Bỏ lưu bài viết vào bảng YeuThich
if (isset($_GET['action']) && $maND) {
    if ($_GET['action'] == 'luu') {
        try {
            $stmtLuu = $pdo->prepare("INSERT INTO YeuThich (MaND, MaBV, NgayLuu) VALUES (:mand, :mabv, NOW())");
            $stmtLuu->execute(['mand' => $maND, 'mabv' => $maBV]);
            $thongBao = "<div class='alert alert-success'>Đã lưu bài viết vào danh sách yêu thích!</div>";
        } catch (PDOException $e) {
            $thongBao = "<div class='alert alert-info'>Bài viết này đã được lưu trước đó.</div>";
        }
    } elseif ($_GET['action'] == 'boluu') {
        $stmtBoLuu = $pdo->prepare("DELETE FROM YeuThich WHERE MaND = :mand AND MaBV = :mabv");
        $stmtBoLuu->execute(['mand' => $maND, 'mabv' => $maBV]);
        $thongBao = "<div class='alert alert-warning'>Đã xóa bài viết khỏi danh sách yêu thích!</div>";
    }
}

// 4. Kiểm tra bài viết đã được người dùng này lưu chưa
$daLuu = false;
if ($maND) {
    $stmtCheck = $pdo->prepare("SELECT * FROM YeuThich WHERE MaND = :mand AND MaBV = :mabv");
    $stmtCheck->execute(['mand' => $maND, 'mabv' => $maBV]);
    if ($stmtCheck->rowCount() > 0) {
        $daLuu = true;
    }
}

// 5. Xử lý gửi bình luận
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_binhluan']) && $maND) {
    $noiDung = trim($_POST['noidung']);
    if (!empty($noiDung)) {
        $mabl = 'BL' . time();
        $stmtBL = $pdo->prepare("INSERT INTO BinhLuan (MaBL, MaBV, MaND, NoiDung, NgayTao) VALUES (:mabl, :mabv, :mand, :noidung, NOW())");
        $stmtBL->execute(['mabl' => $mabl, 'mabv' => $maBV, 'mand' => $maND, 'noidung' => $noiDung]);
        header("Location: chitiet.php?id=$maBV");
        exit();
    }
}

// 6. Lấy danh sách bình luận
$stmtDSBL = $pdo->prepare("SELECT bl.*, nd.HoTen FROM BinhLuan bl JOIN NguoiDung nd ON bl.MaND = nd.MaND WHERE bl.MaBV = :mabv ORDER BY bl.NgayTao DESC");
$stmtDSBL->execute(['mabv' => $maBV]);
$danhSachBL = $stmtDSBL->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($baiViet['TieuDe']) ?></title>
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
            background-color: #cb1313;
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
            background-color: #d31616;
            color: #fff !important;
        }
    </style>
</head>
<body class="bg-light">

    <!-- ==================== HEADER 2 TẦNG ĐỒNG BỘ ==================== -->
    <header class="sticky-top">
        <!-- TẦNG 1: LOGO STYLING CAFEBIZ + TÌM KIẾM + NÚT MENU TÀI KHOẢN -->
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

        <!-- TẦNG 2: THANH MENU CHUYÊN MỤC -->
        <nav class="header-menu-bar">
            <div class="container">
                <ul class="category-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">🏠 Tất cả</a>
                    </li>
                    <?php foreach ($danhSachCM as $cm): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= ($baiViet['MaCM'] == $cm['MaCM']) ? 'active' : '' ?>" 
                               href="index.php?macm=<?= $cm['MaCM'] ?>">
                               <?= htmlspecialchars($cm['TenCM']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </nav>
    </header>

    <!-- NỘI DUNG CHI TIẾT BÀI VIẾT -->
    <div class="container my-4" style="max-width: 900px;">
        <?= $thongBao ?>

        <div class="card shadow-sm p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="badge bg-info text-dark"><?= htmlspecialchars($baiViet['TenCM']) ?></span>
                
                <!-- NÚT LƯU BÀI VIẾT -->
                <?php if ($maND): ?>
                    <?php if ($daLuu): ?>
                        <a href="chitiet.php?id=<?= $maBV ?>&action=boluu" class="btn btn-danger btn-sm fw-bold">❤️ Đã lưu (Bấm để Bỏ lưu)</a>
                    <?php else: ?>
                        <a href="chitiet.php?id=<?= $maBV ?>&action=luu" class="btn btn-outline-danger btn-sm fw-bold">🤍 Lưu bài viết</a>
                    <?php endif; ?>
                <?php else: ?>
                    <small class="text-muted"><a href="login.php">Đăng nhập</a> để lưu bài viết</small>
                <?php endif; ?>
            </div>

            <h1 class="fw-bold text-primary mb-3"><?= htmlspecialchars($baiViet['TieuDe']) ?></h1>
            <div class="text-muted small mb-3">
                ✍️ Tác giả: <strong><?= htmlspecialchars($baiViet['TacGia'] ?? 'Ẩn danh') ?></strong> | 
                🕒 <?= $baiViet['NgayTao'] ?> | 
                👁 <?= $baiViet['LuotXem'] ?> lượt xem
            </div>

            <?php if (!empty($baiViet['HinhAnh'])): ?>
                <?php 
                    $imgSrc = 'upload/' . htmlspecialchars($baiViet['HinhAnh']);
                    if (strpos($baiViet['HinhAnh'], 'upload/') !== false || strpos($baiViet['HinhAnh'], 'uploads/') !== false) {
                        $imgSrc = htmlspecialchars($baiViet['HinhAnh']);
                    }
                ?>
                <div class="text-center my-3">
                    <img src="<?= $imgSrc ?>" class="img-fluid rounded shadow-sm" style="max-height: 400px;">
                </div>
            <?php endif; ?>

            <p class="fw-bold fs-5 text-secondary border-start border-4 border-primary ps-3 my-3">
                <?= nl2br(htmlspecialchars($baiViet['TomTat'])) ?>
            </p>

            <div class="lh-lg fs-6 mt-3">
                <?= nl2br(htmlspecialchars($baiViet['NoiDung'])) ?>
            </div>
        </div>

        <!-- KHU VỰC BÌNH LUẬN -->
        <div class="card shadow-sm p-4">
            <h4 class="fw-bold mb-3">💬 Bình luận độc giả</h4>

            <?php if ($maND): ?>
                <form action="chitiet.php?id=<?= $maBV ?>" method="POST" class="mb-4">
                    <div class="mb-2">
                        <textarea name="noidung" class="form-control" rows="3" placeholder="Nhập bình luận của anh..." required></textarea>
                    </div>
                    <button type="submit" name="btn_binhluan" class="btn btn-primary btn-sm">Gửi bình luận</button>
                </form>
            <?php else: ?>
                <div class="alert alert-secondary text-center">
                    Vui lòng <a href="login.php">đăng nhập</a> để gửi bình luận.
                </div>
            <?php endif; ?>

            <div class="list-group list-group-flush">
                <?php foreach ($danhSachBL as $bl): ?>
                    <div class="list-group-item px-0">
                        <div class="d-flex justify-content-between">
                            <strong class="text-primary"><?= htmlspecialchars($bl['HoTen']) ?></strong>
                            <small class="text-muted"><?= $bl['NgayTao'] ?></small>
                        </div>
                        <p class="mb-0 mt-1"><?= nl2br(htmlspecialchars($bl['NoiDung'])) ?></p>
                    </div>
                <?php endforeach; ?>

                <?php if (empty($danhSachBL)): ?>
                    <p class="text-muted text-center py-2 mb-0">Chưa có bình luận nào.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

</body>
</html>