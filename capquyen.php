<?php
require 'connect.php';

// Cài đặt thông tin tài khoản Admin mặc định
$mand = 'ADMIN' . time(); // Mã người dùng
$username = 'admin'; // Tên đăng nhập
$password = '123456'; // Mật khẩu
$matKhauMaHoa = password_hash($password, PASSWORD_DEFAULT); // Mã hóa mật khẩu
$email = 'admin@tintuc.com';
$hoten = 'Sếp Tổng';
$vaitro = 'QuanTriVien';

try {
    // Thêm trực tiếp vào cơ sở dữ liệu
    $sql = "INSERT INTO NguoiDung (MaND, TenDN, MatKhau, Email, HoTen, VaiTro) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$mand, $username, $matKhauMaHoa, $email, $hoten, $vaitro]);

    echo "<h2 style='color: green;'>Tạo tài khoản Admin thành công!</h2>";
    echo "<p>Tên đăng nhập của anh là: <strong>admin</strong></p>";
    echo "<p>Mật khẩu của anh là: <strong>123456</strong></p>";
    echo "<a href='login.php' style='font-size: 20px; font-weight: bold;'>Bấm vào đây để đi tới trang Đăng nhập</a>";

} catch (PDOException $e) {
    echo "<h2 style='color: red;'>Lỗi: Tài khoản admin này đã được tạo rồi hoặc có lỗi Database!</h2>";
}
?>