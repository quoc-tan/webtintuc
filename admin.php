<?php
session_start();
require 'connect.php';

// Kiểm tra quyền Admin
if (!isset($_SESSION['MaND']) || $_SESSION['VaiTro'] !== 'QuanTriVien') {
    header("Location: index.php");
    exit();
}

// Lấy số lượng thống kê cho Dashboard
$countCM = $pdo->query("SELECT COUNT(*) FROM ChuyenMuc")->fetchColumn();
$countBV = $pdo->query("SELECT COUNT(*) FROM BaiViet")->fetchColumn();
$countND = $pdo->query("SELECT COUNT(*) FROM NguoiDung")->fetchColumn();
$countBL = $pdo->query("SELECT COUNT(*) FROM BinhLuan")->fetchColumn();

// Lấy 5 bài viết mới nhất để hiển thị bảng theo dõi nhanh
$stmtRecent = $pdo->query("SELECT bv.MaBV, bv.TieuDe, bv.NgayTao, cm.TenCM 
                          FROM BaiViet bv 
                          LEFT JOIN ChuyenMuc cm ON bv.MaCM = cm.MaCM 
                          ORDER BY bv.NgayTao DESC LIMIT 5");
$recentArticles = $stmtRecent->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Trị - Hệ Thống Website Tin Tức</title>
    <!-- Nhúng Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <style>
        :root {
            --sidebar-width: 250px;
            --primary-color: #4f46e5;
            --bg-color: #f8fafc;
        }

        body {
            background-color: var(--bg-color);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        /* Sidebar bên trái */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #0f172a;
            color: #94a3b8;
            padding-top: 20px;
            z-index: 1000;
        }

        .sidebar-brand {
            padding: 0 20px 20px 20px;
            font-size: 1.25rem;
            font-weight: 700;
            color: #ffffff;
            border-bottom: 1px solid #1e293b;
        }

        .sidebar-menu {
            list-style: none;
            padding: 15px 10px;
            margin: 0;
        }

        .sidebar-menu .nav-link {
            color: #94a3b8;
            padding: 10px 15px;
            border-radius: 8px;
            font-weight: 500;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.2s;
        }

        .sidebar-menu .nav-link:hover, .sidebar-menu .nav-link.active {
            background-color: var(--primary-color);
            color: #ffffff;
        }

        /* Phần nội dung chính */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 30px;
        }

        /* Top Bar */
        .top-navbar {
            background: #ffffff;
            border-radius: 12px;
            padding: 15px 25px;
            margin-bottom: 30px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        /* Stat Cards */
        .stat-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .table-custom {
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }
    </style>
</head>
<body>

    <!-- SIDEBAR ĐIỀU HƯỚNG BÊN TRÁI -->
    <aside class="sidebar">
        <div class="sidebar-brand d-flex align-items-center gap-2">
            <span class="fs-4">⚙️</span> Quản Trị
        </div>

        <ul class="sidebar-menu">
            <li>
                <a href="admin.php" class="nav-link active">
                    <span>📊</span> Tổng quan
                </a>
            </li>
            <!-- Đã sửa thành adminchuyenmuc.php -->
            <li>
                <a href="adminchuyenmuc.php" class="nav-link">
                    <span>📁</span> Chuyên mục
                </a>
            </li>
            <li>
                <a href="admin_baiviet.php" class="nav-link">
                    <span>📝</span> Bài viết
                </a>
            </li>
            <!-- Đã sửa thành admin_taikhoan.php -->
            <li>
                <a href="admin_taikhoan.php" class="nav-link">
                    <span>👥</span> Người dùng
                </a>
            </li>
            <li>
                <a href="admin_binhluan.php" class="nav-link">
                    <span>💬</span> Bình luận
                </a>
            </li>
            <li class="mt-4 pt-3 border-top border-secondary">
                <a href="index.php" class="nav-link text-info">
                    <span>🌐</span> Xem trang chủ
                </a>
            </li>
            <li>
                <a href="logout.php" class="nav-link text-danger">
                    <span>🚪</span> Đăng xuất
                </a>
            </li>
        </ul>
    </aside>

    <!-- NỘI DUNG CHÍNH (MAIN CONTENT) -->
    <main class="main-content">
        
        <!-- TOPBAR THÔNG TIN TÀI KHOẢN -->
        <div class="top-navbar d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold mb-0 text-dark">Bảng điều khiển</h4>
                <small class="text-muted">Chào mừng anh trở lại làm việc!</small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-indigo-subtle text-indigo fw-bold px-3 py-2 rounded-pill bg-light text-primary border">
                    👤 <?= htmlspecialchars($_SESSION['HoTen']) ?> (Admin)
                </span>
            </div>
        </div>

        <!-- CÁC THẺ THỐNG KÊ TỐI GIẢN (STAT CARDS) -->
        <div class="row g-4 mb-4">
            <!-- Chuyên mục -->
            <div class="col-xl-3 col-sm-6">
                <a href="adminchuyenmuc.php" class="text-decoration-none">
                    <div class="stat-card d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-medium">Chuyên mục</span>
                            <h3 class="fw-bold text-dark mb-0 mt-1"><?= $countCM ?></h3>
                        </div>
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary">📁</div>
                    </div>
                </a>
            </div>

            <!-- Bài viết -->
            <div class="col-xl-3 col-sm-6">
                <a href="admin_baiviet.php" class="text-decoration-none">
                    <div class="stat-card d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-medium">Bài viết</span>
                            <h3 class="fw-bold text-dark mb-0 mt-1"><?= $countBV ?></h3>
                        </div>
                        <div class="stat-icon bg-success bg-opacity-10 text-success">📝</div>
                    </div>
                </a>
            </div>

            <!-- Người dùng -->
            <div class="col-xl-3 col-sm-6">
                <a href="admin_taikhoan.php" class="text-decoration-none">
                    <div class="stat-card d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-medium">Người dùng</span>
                            <h3 class="fw-bold text-dark mb-0 mt-1"><?= $countND ?></h3>
                        </div>
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning">👥</div>
                    </div>
                </a>
            </div>

            <!-- Bình luận -->
            <div class="col-xl-3 col-sm-6">
                <a href="admin_binhluan.php" class="text-decoration-none">
                    <div class="stat-card d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-medium">Bình luận</span>
                            <h3 class="fw-bold text-dark mb-0 mt-1"><?= $countBL ?></h3>
                        </div>
                        <div class="stat-icon bg-info bg-opacity-10 text-info">💬</div>
                    </div>
                </a>
            </div>
        </div>

        <!-- BẢNG BÀI VIẾT MỚI ĐĂNG -->
        <div class="table-custom p-4 bg-white">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-dark mb-0">📰 Bài viết mới cập nhật</h5>
                <a href="admin_baiviet.php" class="btn btn-sm btn-outline-primary rounded-pill">Xem tất cả &rarr;</a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Mã BV</th>
                            <th>Tiêu đề bài viết</th>
                            <th>Chuyên mục</th>
                            <th>Ngày tạo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($recentArticles) > 0): ?>
                            <?php foreach ($recentArticles as $art): ?>
                                <tr>
                                    <td><span class="badge bg-secondary"><?= $art['MaBV'] ?></span></td>
                                    <td class="fw-bold text-dark"><?= htmlspecialchars($art['TieuDe']) ?></td>
                                    <td><span class="badge bg-info text-dark"><?= htmlspecialchars($art['TenCM'] ?? 'Chưa xếp') ?></span></td>
                                    <td class="text-muted small"><?= $art['NgayTao'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">Chưa có bài viết nào trong hệ thống.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

</body>
</html>