<?php
session_start();
require 'connect.php'; // Gọi file kết nối PDO của anh

$thongBao = '';
$loaiThongBao = 'danger';

// Xử lý khi người dùng bấm nút Đăng ký
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $hoTen = trim($_POST['hoten']);
    $email = trim($_POST['email']);
    $tenDN = trim($_POST['tendn']);
    $matKhau = trim($_POST['matkhau']); // Lấy mật khẩu người dùng nhập

    // Kiểm tra xem tên đăng nhập hoặc email đã tồn tại chưa
    $sqlCheck = "SELECT MaND FROM NguoiDung WHERE TenDN = :tendn OR Email = :email";
    $stmtCheck = $pdo->prepare($sqlCheck);
    $stmtCheck->execute(['tendn' => $tenDN, 'email' => $email]);

    if ($stmtCheck->rowCount() > 0) {
        $thongBao = "Tên đăng nhập hoặc Email đã được sử dụng. Vui lòng chọn tên khác!";
    } else {
        // Tạo mã người dùng ngẫu nhiên
        $maND = 'ND' . time(); 
        
        $vaiTro = 'ThanhVien';
        $ngayTao = date('Y-m-d H:i:s');

        // Thêm dữ liệu vào bảng NguoiDung (LƯU MẬT KHẨU BÌNH THƯỜNG)
        $sqlInsert = "INSERT INTO NguoiDung (MaND, TenDN, MatKhau, Email, HoTen, VaiTro, NgayTao) 
                      VALUES (:mand, :tendn, :matkhau, :email, :hoten, :vaitro, :ngaytao)";
        $stmtInsert = $pdo->prepare($sqlInsert);
        
        $ketQua = $stmtInsert->execute([
            'mand' => $maND,
            'tendn' => $tenDN,
            'matkhau' => $matKhau, // Đưa thẳng mật khẩu chữ bình thường vào Database
            'email' => $email,
            'hoten' => $hoTen,
            'vaitro' => $vaiTro,
            'ngaytao' => $ngayTao
        ]);

        if ($ketQua) {
            $thongBao = "Đăng ký tài khoản thành công! Anh có thể đăng nhập ngay bây giờ.";
            $loaiThongBao = 'success';
        } else {
            $thongBao = "Đã xảy ra lỗi trong quá trình tạo tài khoản. Vui lòng thử lại.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - Website Tin Tức</title>
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
            <div class="col-md-5">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white text-center">
                        <h4>Đăng Ký Thành Viên</h4>
                    </div>
                    <div class="card-body p-4">
                        
                        <?php if ($thongBao != ''): ?>
                            <div class="alert alert-<?= $loaiThongBao ?>">
                                <?= $thongBao ?>
                                <?php if ($loaiThongBao == 'success') echo '<br><a href="login.php" class="btn btn-sm btn-success mt-2">Đến trang Đăng nhập</a>'; ?>
                            </div>
                        <?php endif; ?>

                        <form action="register.php" method="POST">
                            <div class="mb-3">
                                <label class="form-label">Họ và tên</label>
                                <input type="text" name="hoten" class="form-control" required placeholder="Nhập họ và tên...">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required placeholder="Nhập địa chỉ email...">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tên đăng nhập</label>
                                <input type="text" name="tendn" class="form-control" required placeholder="Nhập tên tài khoản...">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Mật khẩu</label>
                                <input type="password" name="matkhau" class="form-control" required placeholder="Nhập mật khẩu...">
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Đăng ký ngay</button>
                        </form>
                        
                    </div>
                    <div class="card-footer text-center">
                        Đã có tài khoản? <a href="login.php" class="text-decoration-none">Đăng nhập tại đây</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>