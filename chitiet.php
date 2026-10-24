<?php
session_start();
require 'connect.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$maBV = $_GET['id'];
$maND = $_SESSION['MaND'] ?? null;
$thongBao = "";

// 1. Tăng lượt xem & lấy chi tiết bài viết
$pdo->prepare("UPDATE BaiViet SET LuotXem = LuotXem + 1 WHERE MaBV = :mabv")->execute(['mabv' => $maBV]);

$stmt = $pdo->prepare("SELECT bv.*, cm.TenCM, nd.HoTen AS TacGia 
                       FROM BaiViet bv 
                       LEFT JOIN ChuyenMuc cm ON bv.MaCM = cm.MaCM 
                       LEFT JOIN NguoiDung nd ON bv.MaND = nd.MaND 
                       WHERE bv.MaBV = :mabv");
$stmt->execute(['mabv' => $maBV]);
$baiViet = $stmt->fetch();

if (!$baiViet) {
    die("<div class='container mt-5 alert alert-danger'>Bài viết không tồn tại! <a href='index.php'>Về trang chủ</a></div>");
}

// 2. Xử lý Lưu / Bỏ lưu bài viết vào bảng YeuThich
if (isset($_GET['action']) && $maND) {
    if ($_GET['action'] == 'luu') {
        try {
            $stmtLuu = $pdo->prepare("INSERT INTO YeuThich (MaND, MaBV, NgayLuu) VALUES (:mand, :mabv, NOW())");
            $stmtLuu->execute(['mand' => $maND, 'mabv' => $maBV]);
            $thongBao = "<div class='alert alert-success'>Đã lưu bài viết vào danh sách yêu thích!</div>";
        } catch (PDOException $e) {
            $thongBao = "<div class='alert alert-info'>Bài viết này đã được lưu trước đó.</div>";
        }
    } elseif ($_GET['action'] == 'boluu') {
        $stmtBoLuu = $pdo->prepare("DELETE FROM YeuThich WHERE MaND = :mand AND MaBV = :mabv");
        $stmtBoLuu->execute(['mand' => $maND, 'mabv' => $maBV]);
        $thongBao = "<div class='alert alert-warning'>Đã xóa bài viết khỏi danh sách yêu thích!</div>";
    }
}

// 3. Kiểm tra bài viết đã được người dùng này lưu chưa
$daLuu = false;
if ($maND) {
    $stmtCheck = $pdo->prepare("SELECT * FROM YeuThich WHERE MaND = :mand AND MaBV = :mabv");
    $stmtCheck->execute(['mand' => $maND, 'mabv' => $maBV]);
    if ($stmtCheck->rowCount() > 0) {
        $daLuu = true;
    }
}

// 4. Xử lý gửi bình luận
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_binhluan']) && $maND) {
    $noiDung = trim($_POST['noidung']);
    if (!empty($noiDung)) {
        $mabl = 'BL' . time();
        $stmtBL = $pdo->prepare("INSERT INTO BinhLuan (MaBL, MaBV, MaND, NoiDung, NgayTao) VALUES (:mabl, :mabv, :mand, :noidung, NOW())");
        $stmtBL->execute(['mabl' => $mabl, 'mabv' => $maBV, 'mand' => $maND, 'noidung' => $noiDung]);
        header("Location: chitiet.php?id=$maBV");
        exit();
    }
}

// 5. Lấy danh sách bình luận
$stmtDSBL = $pdo->prepare("SELECT bl.*, nd.HoTen FROM BinhLuan bl JOIN NguoiDung nd ON bl.MaND = nd.MaND WHERE bl.MaBV = :mabv ORDER BY bl.NgayTao DESC");
$stmtDSBL->execute(['mabv' => $maBV]);
$danhSachBL = $stmtDSBL->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($baiViet['TieuDe']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">TinTức24h</a>
            <a href="index.php" class="btn btn-outline-light btn-sm">&larr; Trang chủ</a>
        </div>
    </nav>

    <div class="container my-4" style="max-width: 900px;">
        <?= $thongBao ?>

        <div class="card shadow-sm p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="badge bg-info text-dark"><?= htmlspecialchars($baiViet['TenCM']) ?></span>
                
                <!-- NÚT LƯU BÀI VIẾT -->
                <?php if ($maND): ?>
                    <?php if ($daLuu): ?>
                        <a href="chitiet.php?id=<?= $maBV ?>&action=boluu" class="btn btn-danger btn-sm fw-bold">❤️ Đã lưu (Bấm để Bỏ lưu)</a>
                    <?php else: ?>
                        <a href="chitiet.php?id=<?= $maBV ?>&action=luu" class="btn btn-outline-danger btn-sm fw-bold">🤍 Lưu bài viết</a>
                    <?php endif; ?>
                <?php else: ?>
                    <small class="text-muted"><a href="login.php">Đăng nhập</a> để lưu bài viết</small>
                <?php endif; ?>
            </div>

            <h1 class="fw-bold text-primary mb-3"><?= htmlspecialchars($baiViet['TieuDe']) ?></h1>
            <div class="text-muted small mb-3">
                ✍️ Tác giả: <strong><?= htmlspecialchars($baiViet['TacGia'] ?? 'Ẩn danh') ?></strong> | 
                🕒 <?= $baiViet['NgayTao'] ?> | 
                👁 <?= $baiViet['LuotXem'] ?> lượt xem
            </div>

            <?php if (!empty($baiViet['HinhAnh'])): ?>
                <div class="text-center my-3">
                    <img src="upload/<?= htmlspecialchars($baiViet['HinhAnh']) ?>" class="img-fluid rounded shadow-sm" style="max-height: 400px;">
                </div>
            <?php endif; ?>

            <p class="fw-bold fs-5 text-secondary border-start border-4 border-primary ps-3 my-3">
                <?= nl2br(htmlspecialchars($baiViet['TomTat'])) ?>
            </p>

            <div class="lh-lg fs-6 mt-3">
                <?= nl2br(htmlspecialchars($baiViet['NoiDung'])) ?>
            </div>
        </div>

        <!-- KHU VỰC BÌNH LUẬN -->
        <div class="card shadow-sm p-4">
            <h4 class="fw-bold mb-3">💬 Bình luận độc giả</h4>

            <?php if ($maND): ?>
                <form action="chitiet.php?id=<?= $maBV ?>" method="POST" class="mb-4">
                    <div class="mb-2">
                        <textarea name="noidung" class="form-control" rows="3" placeholder="Nhập bình luận của anh..." required></textarea>
                    </div>
                    <button type="submit" name="btn_binhluan" class="btn btn-primary btn-sm">Gửi bình luận</button>
                </form>
            <?php else: ?>
                <div class="alert alert-secondary text-center">
                    Vui lòng <a href="login.php">đăng nhập</a> để gửi bình luận.
                </div>
            <?php endif; ?>

            <div class="list-group list-group-flush">
                <?php foreach ($danhSachBL as $bl): ?>
                    <div class="list-group-item px-0">
                        <div class="d-flex justify-content-between">
                            <strong class="text-primary"><?= htmlspecialchars($bl['HoTen']) ?></strong>
                            <small class="text-muted"><?= $bl['NgayTao'] ?></small>
                        </div>
                        <p class="mb-0 mt-1"><?= nl2br(htmlspecialchars($bl['NoiDung'])) ?></p>
                    </div>
                <?php endforeach; ?>

                <?php if (empty($danhSachBL)): ?>
                    <p class="text-muted text-center py-2 mb-0">Chưa có bình luận nào.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

</body>
</html>