<?php
session_start(); // Bắt đầu session để nhận diện người dùng hiện tại

// Xóa tất cả các biến đã lưu trong session (như MaND, HoTen...)
session_unset();

// Hủy hoàn toàn phiên làm việc
session_destroy();

// Chuyển hướng người dùng quay lại trang chủ
header("Location: index.php");
exit();
?>