<?php
session_start();
require 'connect.php';

// Chỉ cho phép Quản trị viên truy cập
if (!isset($_SESSION['MaND']) || $_SESSION['VaiTro'] != 'QuanTriVien') {
    header("Location: admin.php");
    exit();
}

$thongBao = '';

// XỬ LÝ XÓA BÌNH LUẬN
if (isset($_GET['action']) && $_GET['action'] == 'xoa' && isset($_GET['id'])) {
    $maBL_Xoa = $_GET['id'];
    try {
        $sqlDel = "DELETE FROM BinhLuan WHERE MaBL = :mabl";
        $stmtDel = $pdo->prepare($sqlDel);
        $stmtDel->execute(['mabl' => $maBL_Xoa]);
        header("Location: admin_binhluan.php");
        exit();
    } catch (PDOException $e) {
        $thongBao = "<div class='alert alert-danger'>Lỗi khi xóa bình luận: " . $e->getMessage() . "</div>";
    }
}

// LẤY DANH SÁCH BÌNH LUẬN (Kết nối bảng BinhLuan, NguoiDung và BaiViet để lấy tên bài và tên người bình luận)
$sql = "SELECT BinhLuan.*, NguoiDung.HoTen, BaiViet.TieuDe 
        FROM BinhLuan 
        JOIN NguoiDung ON BinhLuan.MaND = NguoiDung.MaND 
        JOIN BaiViet ON BinhLuan.MaBV = BaiViet.MaBV 
        ORDER BY BinhLuan.NgayTao DESC";
$stmt = $pdo->query($sql);
$danhSachBL = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Bình luận - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- Thanh Menu Admin -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="admin.php">Trang Quản Trị</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="adminchuyenmuc.php">Quản lý Chuyên mục</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin_baiviet.php">Quản lý Bài viết</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin_taikhoan.php">Quản lý Người dùng</a></li>
                    <li class="nav-item"><a class="nav-link active" href="admin_binhluan.php">Quản lý Bình luận</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin.php">Bảng Điều Khiển</a></li>
                </ul>
                <span class="text-white me-3">Admin: <strong><?= htmlspecialchars($_SESSION['HoTen']) ?></strong></span>
                <a href="logout.php" class="btn btn-danger btn-sm">Đăng xuất</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h3 class="mb-4 text-primary fw-bold">QUẢN LÝ BÌNH LUẬN ĐỘC GIẢ</h3>
        
        <?= $thongBao ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-dark text-center">
                        <tr>
                            <th>Người bình luận</th>
                            <th>Nội dung bình luận</th>
                            <th>Bài viết được bình luận</th>
                            <th>Thời gian</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($danhSachBL as $bl): ?>
                            <tr>
                                <td class="fw-bold text-primary text-center">
                                    👤 <?= htmlspecialchars($bl['HoTen']) ?>
                                </td>
                                <td>
                                    <?= nl2br(htmlspecialchars($bl['NoiDung'])) ?>
                                </td>
                                <td>
                                    <a href="chitiet.php?id=<?= $bl['MaBV'] ?>" target="_blank" class="text-decoration-none">
                                        📰 <?= htmlspecialchars($bl['TieuDe']) ?>
                                    </a>
                                </td>
                                <td class="text-muted small text-center text-nowrap">
                                    🕒 <?= $bl['NgayTao'] ?>
                                </td>
                                <td class="text-center">
                                    <a href="admin_binhluan.php?action=xoa&id=<?= $bl['MaBL'] ?>" 
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('Anh có chắc muốn xóa bình luận này không?');">
                                       Xóa
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (empty($danhSachBL)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">Chưa có bình luận nào trên hệ thống.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="mt-4 mb-5">
            <a href="admin.php" class="btn btn-outline-secondary">&larr; Quay lại Bảng điều khiển</a>
        </div>
    </div>

</body>
</html>