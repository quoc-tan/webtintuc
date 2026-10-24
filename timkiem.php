<?php
session_start();
require 'connect.php';

// Lấy từ khóa người dùng gõ vào
$tuKhoa = isset($_GET['tukhoa']) ? trim($_GET['tukhoa']) : '';
$danhSachKetQua = [];

if ($tuKhoa != '') {
    // Tìm kiếm bài viết có chứa từ khóa trong Tiêu đề hoặc Tóm tắt
    $sql = "SELECT MaBV, TieuDe, TomTat, NgayTao 
            FROM BaiViet 
            WHERE TrangThai = 'DaDang' AND (TieuDe LIKE ? OR TomTat LIKE ?) 
            ORDER BY NgayTao DESC";
    $stmt = $pdo->prepare($sql);
    
    // Thêm dấu % ở 2 đầu để tìm từ khóa nằm ở bất kỳ đâu
    $searchTerm = "%" . $tuKhoa . "%";
    $stmt->execute([$searchTerm, $searchTerm]);
    $danhSachKetQua = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Kết quả tìm kiếm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="index.php">⬅ Quay lại Trang Chủ</a>
        </div>
    </nav>

    <div class="container mt-4">
        <h3 class="mb-4">🔍 Kết quả tìm kiếm cho từ khóa: <span class="text-danger">"<?= htmlspecialchars($tuKhoa) ?>"</span></h3>
        <p>Tìm thấy <strong><?= count($danhSachKetQua) ?></strong> bài viết.</p>
        
        <div class="row">
            <?php if (count($danhSachKetQua) > 0): ?>
                <?php foreach ($danhSachKetQua as $baiViet): ?>
                    <div class="col-md-4 mb-3">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title text-primary"><?= htmlspecialchars($baiViet['TieuDe']) ?></h5>
                                <h6 class="card-subtitle mb-2 text-muted">Ngày đăng: <?= $baiViet['NgayTao'] ?></h6>
                                <p class="card-text"><?= htmlspecialchars($baiViet['TomTat']) ?></p>
                                <a href="chitiet.php?id=<?= $baiViet['MaBV'] ?>" class="btn btn-outline-primary btn-sm">Đọc tiếp</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-warning">Không tìm thấy bài viết nào phù hợp. Anh hãy thử lại với từ khóa khác nhé!</div>
                </div>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>