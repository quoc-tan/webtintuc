<?php
session_start();
require 'connect.php';

// ĐÃ SỬA: CHỈ CHO PHÉP QUẢN TRỊ VIÊN VÀO TRANG NÀY
if (!isset($_SESSION['MaND']) || $_SESSION['VaiTro'] != 'QuanTriVien') {
    header("Location: admin.php");
    exit();
}

$thongBao = '';

// 1. XỬ LÝ THÊM CHUYÊN MỤC MỚI
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['them_cm'])) {
    $maCM = trim($_POST['macm']);
    $tenCM = trim($_POST['tencm']);
    $moTa = trim($_POST['mota']);

    if (!empty($maCM) && !empty($tenCM)) {
        try {
            $sqlInsert = "INSERT INTO ChuyenMuc (MaCM, TenCM, MoTa) VALUES (:macm, :tencm, :mota)";
            $stmtInsert = $pdo->prepare($sqlInsert);
            $stmtInsert->execute(['macm' => $maCM, 'tencm' => $tenCM, 'mota' => $moTa]);
            $thongBao = "<div class='alert alert-success'>Thêm chuyên mục thành công!</div>";
        } catch (PDOException $e) {
            $thongBao = "<div class='alert alert-danger'>Lỗi: Mã chuyên mục đã tồn tại hoặc lỗi CSDL!</div>";
        }
    } else {
        $thongBao = "<div class='alert alert-warning'>Vui lòng nhập đủ Mã và Tên chuyên mục!</div>";
    }
}

// 2. XỬ LÝ XÓA CHUYÊN MỤC
if (isset($_GET['action']) && $_GET['action'] == 'xoa' && isset($_GET['id'])) {
    $maCM_Xoa = $_GET['id'];
    try {
        $sqlDelete = "DELETE FROM ChuyenMuc WHERE MaCM = :macm";
        $stmtDelete = $pdo->prepare($sqlDelete);
        $stmtDelete->execute(['macm' => $maCM_Xoa]);
        header("Location: adminchuyenmuc.php");
        exit();
    } catch (PDOException $e) {
        $thongBao = "<div class='alert alert-danger'>Không thể xóa chuyên mục này vì đang có bài viết thuộc chuyên mục!</div>";
    }
}

// 3. LẤY DANH SÁCH CHUYÊN MỤC HIỂN THỊ RA BẢNG
$sql = "SELECT * FROM ChuyenMuc ORDER BY MaCM ASC";
$stmt = $pdo->query($sql);
$danhSachCM = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý chuyên mục - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="adminchuyenmuc.php">Trang Quản Trị</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link active" href="adminchuyenmuc.php">Quản lý Chuyên mục</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin.php">Bảng Điều Khiển</a></li>
                </ul>
                <span class="text-white me-3">Admin: <strong><?= htmlspecialchars($_SESSION['HoTen']) ?></strong></span>
                <a href="logout.php" class="btn btn-danger btn-sm">Đăng xuất</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h3 class="mb-4 text-primary fw-bold">QUẢN LÝ CHUYÊN MỤC</h3>
        <?= $thongBao ?>
        <div class="row">
            <div class="col-md-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success text-white fw-bold">Thêm Chuyên Mục Mới</div>
                    <div class="card-body">
                        <form action="adminchuyenmuc.php" method="POST">
                            <div class="mb-3">
                                <label class="form-label">Mã chuyên mục (VD: CM01)</label>
                                <input type="text" name="macm" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tên chuyên mục</label>
                                <input type="text" name="tencm" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Mô tả</label>
                                <textarea name="mota" class="form-control" rows="3"></textarea>
                            </div>
                            <button type="submit" name="them_cm" class="btn btn-success w-100">Thêm mới</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <table class="table table-bordered table-hover text-center align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>Mã CM</th>
                                    <th>Tên chuyên mục</th>
                                    <th>Mô tả</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($danhSachCM as $cm): ?>
                                    <tr>
                                        <td class="fw-bold text-primary"><?= htmlspecialchars($cm['MaCM']) ?></td>
                                        <td class="fw-bold"><?= htmlspecialchars($cm['TenCM']) ?></td>
                                        <td><?= htmlspecialchars($cm['MoTa']) ?></td>
                                        <td>
                                            <a href="adminchuyenmuc.php?action=xoa&id=<?= $cm['MaCM'] ?>" 
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('Anh có chắc chắn muốn xóa chuyên mục này không?');">Xóa</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($danhSachCM)): ?>
                                    <tr><td colspan="4" class="text-muted">Chưa có chuyên mục nào.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>