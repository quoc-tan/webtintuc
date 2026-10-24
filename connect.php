<?php
$host = '127.0.0.1'; // Hoặc 'localhost'
$db   = 'QuanLyTinTuc'; // Tên cơ sở dữ liệu của bạn
$user = 'root'; // Tên đăng nhập MySQL (Mặc định của XAMPP/WAMP thường là root)
$pass = ''; // Mật khẩu MySQL (Mặc định của XAMPP thường để trống)
$charset = 'utf8mb4'; // Hỗ trợ tiếng Việt có dấu

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    // echo "Kết nối cơ sở dữ liệu thành công!"; 
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>