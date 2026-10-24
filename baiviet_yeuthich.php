<?php
session_start();
require 'connect.php';

if (!isset($_SESSION['MaND'])) {
    header("Location: login.php");
    exit();
}

$maND = $_SESSION['MaND'];

// Lấy danh sách các bài viết đã lưu từ bảng YeuThich
$sql = "SELECT bv.*, cm.TenCM, yt.NgayLuu 
        FROM YeuThich yt 
        JOIN BaiViet bv ON yt.MaBV = bv.MaBV 
        LEFT JOIN ChuyenMuc cm ON bv.MaCM = cm.MaCM 
        WHERE yt.MaND = :mand 
        ORDER BY yt.NgayLuu DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute(['mand' => $maND]);
$danhSachDaLuu = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Bài Viết Đã Lưu - TinTức24h</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">TinTức24h</a>
            <div class="d-flex align-items-center">
                <span class="text-white me-3">Thành viên: <strong><?= htmlspecialchars($_SESSION['HoTen']) ?></strong></span>
                <a href="index.php" class="btn btn-outline-light btn-sm">&larr; Về trang chủ</a>
            </div>
        </div>
    </nav>

    <div class="container my-4">
        <h3 class="fw-bold text-primary mb-4">❤️ DANH SÁCH BÀI VIẾT ĐÃ LƯU</h3>

        <div class="row">
            <?php foreach ($danhSachDaLuu as $bv): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body">
                            <span class="badge bg-info text-dark mb-2"><?= htmlspecialchars($bv['TenCM'] ?? 'Chưa rõ') ?></span>
                            <h5 class="card-title fw-bold text-primary"><?= htmlspecialchars($bv['TieuDe']) ?></h5>
                            <h6 class="card-subtitle mb-2 text-muted small">🕒 Đã lưu: <?= $bv['NgayLuu'] ?></h6>
                            <p class="card-text text-secondary mt-2"><?= htmlspecialchars($bv['TomTat']) ?></p>
                        </div>
                        <div class="card-footer bg-white border-0 pb-3 d-flex justify-content-between align-items-center">
                            <a href="chitiet.php?id=<?= $bv['MaBV'] ?>" class="btn btn-outline-primary btn-sm">Đọc tiếp &rarr;</a>
                            <a href="chitiet.php?id=<?= $bv['MaBV'] ?>&action=boluu" class="btn btn-outline-danger btn-sm" onclick="return confirm('Xóa bài này khỏi danh sách đã lưu?');">Xóa lưu</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (empty($danhSachDaLuu)): ?>
                <div class="col-12">
                    <div class="alert alert-info text-center py-4">
                        Anh chưa lưu bài viết nào. Hãy bấm nút 🤍 <strong>Lưu bài viết</strong> khi đọc bài để xem lại sau nhé!
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>