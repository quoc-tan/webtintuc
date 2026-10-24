<?php
session_start();
require 'connect.php';

// Bắt buộc phải đăng nhập mới được đổi mật khẩu
if (!isset($_SESSION['MaND'])) {
    header("Location: login.php");
    exit();
}

$thongBao = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_doimk'])) {
    $matKhauCu = trim($_POST['matkhau_cu']);
    $matKhauMoi = trim($_POST['matkhau_moi']);
    $xacNhanMK = trim($_POST['xacnhan_mk']);
    $maND = $_SESSION['MaND'];

    if (empty($matKhauCu) || empty($matKhauMoi) || empty($xacNhanMK)) {
        $thongBao = "<div class='alert alert-warning'>Vui lòng điền đầy đủ các trường thông tin!</div>";
    } elseif ($matKhauMoi !== $xacNhanMK) {
        $thongBao = "<div class='alert alert-danger'>Mật khẩu mới và xác nhận mật khẩu không trùng khớp!</div>";
    } else {
        // Kiểm tra mật khẩu hiện tại trong CSDL
        $stmt = $pdo->prepare("SELECT MatKhau FROM NguoiDung WHERE MaND = :mand");
        $stmt->execute(['mand' => $maND]);
        $user = $stmt->fetch();

        if ($user && $matKhauCu === $user['MatKhau']) {
            // Cập nhật mật khẩu mới vào cơ sở dữ liệu
            $stmtUp = $pdo->prepare("UPDATE NguoiDung SET MatKhau = :matkhaumoi WHERE MaND = :mand");
            $stmtUp->execute(['matkhaumoi' => $matKhauMoi, 'mand' => $maND]);
            $thongBao = "<div class='alert alert-success'>🎉 Đổi mật khẩu thành công! Mật khẩu mới đã được cập nhật.</div>";
        } else {
            $thongBao = "<div class='alert alert-danger'>Mật khẩu hiện tại nhập không chính xác!</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đổi Mật Khẩu - TinTức24h</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- Thanh Menu -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">TinTức24h</a>
            <a href="index.php" class="btn btn-outline-light btn-sm">&larr; Về trang chủ</a>
        </div>
    </nav>

    <div class="container my-5" style="max-width: 480px;">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white fw-bold text-center py-3">
                <h5 class="mb-0 fs-5">🔑 ĐỔI MẬT KHẨU TÀI KHOẢN</h5>
            </div>
            <div class="card-body p-4">
                <?= $thongBao ?>

                <form action="doimatkhau.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mật khẩu hiện tại <span class="text-danger">*</span></label>
                        <input type="password" name="matkhau_cu" class="form-control" placeholder="Nhập mật khẩu cũ..." required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mật khẩu mới <span class="text-danger">*</span></label>
                        <input type="password" name="matkhau_moi" class="form-control" placeholder="Nhập mật khẩu mới..." required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Xác nhận mật khẩu mới <span class="text-danger">*</span></label>
                        <input type="password" name="xacnhan_mk" class="form-control" placeholder="Nhập lại mật khẩu mới..." required>
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" name="btn_doimk" class="btn btn-primary fw-bold">Cập Nhật Mật Khẩu</button>
                        <a href="index.php" class="btn btn-outline-secondary">Hủy bỏ</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>
</html>