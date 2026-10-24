<?php
session_start();
require 'connect.php';

// Chỉ cho phép Quản trị viên (Admin) truy cập
if (!isset($_SESSION['MaND']) || $_SESSION['VaiTro'] != 'QuanTriVien') {
    header("Location: admin.php");
    exit();
}

$thongBao = '';

// 1. XỬ LÝ CẬP NHẬT VAI TRÒ (PHÂN QUYỀN)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['capnhat_vaitro'])) {
    $maND_Sua = $_POST['mand'];
    $vaiTroMoi = $_POST['vaitro'];

    try {
        $sqlUp = "UPDATE NguoiDung SET VaiTro = :vaitro WHERE MaND = :mand";
        $stmtUp = $pdo->prepare($sqlUp);
        $stmtUp->execute(['vaitro' => $vaiTroMoi, 'mand' => $maND_Sua]);
        $thongBao = "<div class='alert alert-success'>Cập nhật quyền cho tài khoản thành công!</div>";
    } catch (PDOException $e) {
        $thongBao = "<div class='alert alert-danger'>Lỗi cập nhật: " . $e->getMessage() . "</div>";
    }
}

// 2. XỬ LÝ XÓA TÀI KHOẢN
if (isset($_GET['action']) && $_GET['action'] == 'xoa' && isset($_GET['id'])) {
    $maND_Xoa = $_GET['id'];
    
    // Không cho phép tự xóa tài khoản của chính mình đang đăng nhập
    if ($maND_Xoa == $_SESSION['MaND']) {
        $thongBao = "<div class='alert alert-danger'>Anh không thể tự xóa tài khoản của chính mình đang đăng nhập!</div>";
    } else {
        try {
            $sqlDel = "DELETE FROM NguoiDung WHERE MaND = :mand";
            $stmtDel = $pdo->prepare($sqlDel);
            $stmtDel->execute(['mand' => $maND_Xoa]);
            header("Location: admin_taikhoan.php");
            exit();
        } catch (PDOException $e) {
            $thongBao = "<div class='alert alert-danger'>Không thể xóa tài khoản này vì vướng dữ liệu liên quan (bài viết/bình luận)!</div>";
        }
    }
}

// 3. LẤY DANH SÁCH TẤT CẢ NGƯỜI DÙNG
$sql = "SELECT * FROM NguoiDung ORDER BY NgayTao DESC";
$stmt = $pdo->query($sql);
$danhSachND = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Người Dùng - Admin</title>
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
                    <li class="nav-item"><a class="nav-link active" href="admin_taikhoan.php">Quản lý Người dùng</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin.php">Bảng Điều Khiển</a></li>
                </ul>
                <span class="text-white me-3">Admin: <strong><?= htmlspecialchars($_SESSION['HoTen']) ?></strong></span>
                <a href="logout.php" class="btn btn-danger btn-sm">Đăng xuất</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h3 class="mb-4 text-primary fw-bold">QUẢN LÝ TÀI KHOẢN NGƯỜI DÙNG</h3>
        
        <?= $thongBao ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <table class="table table-bordered table-hover text-center align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Mã ND</th>
                            <th>Tên đăng nhập</th>
                            <th>Họ và tên</th>
                            <th>Email</th>
                            <th>Vai trò hiện tại</th>
                            <th>Đổi vai trò</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($danhSachND as $nd): ?>
                            <tr>
                                <td class="fw-bold"><?= htmlspecialchars($nd['MaND']) ?></td>
                                <td class="text-primary fw-bold"><?= htmlspecialchars($nd['TenDN']) ?></td>
                                <td class="fw-bold"><?= htmlspecialchars($nd['HoTen']) ?></td>
                                <td class="text-muted"><?= htmlspecialchars($nd['Email']) ?></td>
                                <td>
                                    <?php if ($nd['VaiTro'] == 'QuanTriVien'): ?>
                                        <span class="badge bg-danger">Quản Trị Viên</span>
                                    <?php elseif ($nd['VaiTro'] == 'BienTapVien'): ?>
                                        <span class="badge bg-success">Biên Tập Viên</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Thành Viên</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <!-- Form đổi quyền nhanh ngay tại bảng -->
                                    <form action="admin_taikhoan.php" method="POST" class="d-flex justify-content-center align-items-center">
                                        <input type="hidden" name="mand" value="<?= $nd['MaND'] ?>">
                                        <select name="vaitro" class="form-select form-select-sm me-2" style="width: 140px;">
                                            <option value="ThanhVien" <?= $nd['VaiTro'] == 'ThanhVien' ? 'selected' : '' ?>>Thành Viên</option>
                                            <option value="BienTapVien" <?= $nd['VaiTro'] == 'BienTapVien' ? 'selected' : '' ?>>Biên Tập Viên</option>
                                            <option value="QuanTriVien" <?= $nd['VaiTro'] == 'QuanTriVien' ? 'selected' : '' ?>>Quản Trị Viên</option>
                                        </select>
                                        <button type="submit" name="capnhat_vaitro" class="btn btn-warning btn-sm text-nowrap">Lưu</button>
                                    </form>
                                </td>
                                <td>
                                    <?php if ($nd['MaND'] != $_SESSION['MaND']): ?>
                                        <a href="admin_taikhoan.php?action=xoa&id=<?= $nd['MaND'] ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Anh có chắc chắn muốn xóa tài khoản này không?');">
                                           Xóa
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">Tài khoản hiện tại</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
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