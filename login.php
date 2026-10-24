<?php
session_start();
require 'connect.php'; // Gọi file kết nối PDO

$thongBao = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tenDN = trim($_POST['tendn']);
    $matKhau = trim($_POST['matkhau']);

    // Truy vấn tìm tài khoản trong CSDL
    $sql = "SELECT * FROM NguoiDung WHERE TenDN = :tendn";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['tendn' => $tenDN]);
    $user = $stmt->fetch();

    // Nếu tìm thấy tài khoản
    if ($user) {
        
        // TRẢ VỀ CÁCH SO SÁNH MẬT KHẨU CŨ CỦA ANH
        if ($matKhau == $user['MatKhau']) {
            
            // Đăng nhập thành công, lưu thông tin vào Session
            $_SESSION['MaND'] = $user['MaND'];
            $_SESSION['HoTen'] = $user['HoTen'];
            $_SESSION['VaiTro'] = $user['VaiTro'];

            // Kiểm tra phân quyền để chuyển hướng
            if ($user['VaiTro'] == 'QuanTriVien' || $user['VaiTro'] == 'BienTapVien') {
                header("Location: admin.php"); // Chuyển vào trang quản trị
            } else {
                header("Location: index.php"); // Thành viên thì về trang chủ
            }
            exit();
        } else {
            $thongBao = "Mật khẩu không chính xác!";
        }
    } else {
        $thongBao = "Tài khoản không tồn tại!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Website Tin Tức</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- Thanh Menu -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-5">
        <div class="container">
            <a class="navbar-brand" href="index.php">TinTức24h</a>
        </div>
    </nav>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white text-center">
                        <h4>Đăng Nhập</h4>
                    </div>
                    <div class="card-body p-4">
                        
                        <?php if ($thongBao != ''): ?>
                            <div class="alert alert-danger text-center">
                                <?= $thongBao ?>
                            </div>
                        <?php endif; ?>

                        <form action="login.php" method="POST">
                            <div class="mb-3">
                                <label class="form-label">Tên đăng nhập</label>
                                <input type="text" name="tendn" class="form-control" required placeholder="Nhập tên tài khoản...">
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Mật khẩu</label>
                                <input type="password" name="matkhau" class="form-control" required placeholder="Nhập mật khẩu...">
                            </div>
                            <button type="submit" class="btn btn-success w-100">Đăng nhập</button>
                        </form>
                        
                    </div>
                    <div class="card-footer text-center">
                        Chưa có tài khoản? <a href="register.php" class="text-decoration-none">Đăng ký ngay</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>