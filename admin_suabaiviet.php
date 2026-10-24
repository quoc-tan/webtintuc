<?php
session_start();
require 'connect.php';

// Kiểm tra quyền truy cập (Admin hoặc Biên tập viên)
if (!isset($_SESSION['MaND']) || ($_SESSION['VaiTro'] != 'QuanTriVien' && $_SESSION['VaiTro'] != 'BienTapVien')) {
    header("Location: index.php");
    exit();
}

$vaiTro = $_SESSION['VaiTro'];
$maND = $_SESSION['MaND'];
$thongBao = "";

if (!isset($_GET['id'])) {
    header("Location: admin_baiviet.php");
    exit();
}

$maBV = $_GET['id'];

// LẤY THÔNG TIN BÀI VIẾT KÈM THEO KIỂM TRA QUYỀN
if ($vaiTro == 'QuanTriVien') {
    // Admin sửa được bất kỳ bài nào
    $stmt = $pdo->prepare("SELECT * FROM BaiViet WHERE MaBV = :mabv");
    $stmt->execute(['mabv' => $maBV]);
} else {
    // Biên tập viên CHỈ sửa được bài do chính mình viết
    $stmt = $pdo->prepare("SELECT * FROM BaiViet WHERE MaBV = :mabv AND MaND = :mand");
    $stmt->execute(['mabv' => $maBV, 'mand' => $maND]);
}

$baiViet = $stmt->fetch();

if (!$baiViet) {
    die("<div class='container mt-5 alert alert-danger text-center'>
            <h4>Bài viết không tồn tại hoặc anh không có quyền chỉnh sửa bài viết này!</h4>
            <a href='admin_baiviet.php' class='btn btn-primary mt-2'>&larr; Quay lại danh sách</a>
         </div>");
}

// LẤY DANH SÁCH CHUYÊN MỤC
$stmtCM = $pdo->query("SELECT * FROM ChuyenMuc ORDER BY TenCM ASC");
$danhSachCM = $stmtCM->fetchAll();

// XỬ LÝ CẬP NHẬT BÀI VIẾT
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_capnhat'])) {
    $tieude = trim($_POST['tieude']);
    $tomtat = trim($_POST['tomtat']);
    $noidung = trim($_POST['noidung']);
    $macm = $_POST['macm'];
    $duongDanAnh = $baiViet['HinhAnh']; // Mặc định giữ ảnh cũ

    // Nếu người dùng chọn ảnh mới thì mới update ảnh mới
    if (isset($_FILES['hinhanh']) && $_FILES['hinhanh']['error'] == 0) {
        $thuMucUpload = 'upload/';
        if (!is_dir($thuMucUpload)) {
            mkdir($thuMucUpload, 0777, true);
        }
        $tenFile = time() . '_' . basename($_FILES['hinhanh']['name']);
        if (move_uploaded_file($_FILES['hinhanh']['tmp_name'], $thuMucUpload . $tenFile)) {
            $duongDanAnh = $tenFile;
        }
    }

    if (!empty($tieude) && !empty($noidung) && !empty($macm)) {
        try {
            // BTV sửa bài xong sẽ chuyển về 'ChoDuyet', Admin sửa thì giữ nguyên
            $trangThaiMoi = ($vaiTro == 'BienTapVien') ? 'ChoDuyet' : $baiViet['TrangThai'];

            $sqlUp = "UPDATE BaiViet 
                      SET TieuDe = :tieude, TomTat = :tomtat, NoiDung = :noidung, HinhAnh = :hinhanh, MaCM = :macm, TrangThai = :trangthai 
                      WHERE MaBV = :mabv";
            $stmtUp = $pdo->prepare($sqlUp);
            $stmtUp->execute([
                'tieude' => $tieude,
                'tomtat' => $tomtat,
                'noidung' => $noidung,
                'hinhanh' => $duongDanAnh,
                'macm' => $macm,
                'trangthai' => $trangThaiMoi,
                'mabv' => $maBV
            ]);

            header("Location: admin_baiviet.php");
            exit();
        } catch (PDOException $e) {
            $thongBao = "<div class='alert alert-danger'>Lỗi cập nhật: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    } else {
        $thongBao = "<div class='alert alert-warning'>Vui lòng nhập đủ Chuyên mục, Tiêu đề và Nội dung!</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh Sửa Bài Viết</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <div class="container my-5" style="max-width: 800px;">
        <div class="card shadow">
            <div class="card-header bg-warning text-dark fw-bold d-flex justify-content-between align-items-center">
                <h4 class="mb-0 fs-5">✏️ CHỈNH SỬA BÀI VIẾT</h4>
                <a href="admin_baiviet.php" class="btn btn-sm btn-dark">&larr; Quay lại</a>
            </div>
            <div class="card-body">
                <?= $thongBao ?>

                <form action="admin_suabaiviet.php?id=<?= $maBV ?>" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Chuyên mục <span class="text-danger">*</span></label>
                        <select name="macm" class="form-select" required>
                            <?php foreach ($danhSachCM as $cm): ?>
                                <option value="<?= htmlspecialchars($cm['MaCM']) ?>" <?= ($cm['MaCM'] == $baiViet['MaCM']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cm['TenCM']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tiêu đề bài viết <span class="text-danger">*</span></label>
                        <input type="text" name="tieude" class="form-control" value="<?= htmlspecialchars($baiViet['TieuDe']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tóm tắt ngắn</label>
                        <textarea name="tomtat" class="form-control" rows="3"><?= htmlspecialchars($baiViet['TomTat']) ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nội dung chi tiết <span class="text-danger">*</span></label>
                        <textarea name="noidung" class="form-control" rows="8" required><?= htmlspecialchars($baiViet['NoiDung']) ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Hình ảnh minh họa</label>
                        <?php if (!empty($baiViet['HinhAnh'])): ?>
                            <div class="mb-2">
                                <small class="text-muted">Ảnh hiện tại:</small><br>
                                <img src="upload/<?= htmlspecialchars($baiViet['HinhAnh']) ?>" style="max-height: 120px;" class="img-thumbnail rounded mt-1">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="hinhanh" class="form-control" accept="image/*">
                        <small class="text-muted fs-7">(Để trống nếu không muốn đổi ảnh mới)</small>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="admin_baiviet.php" class="btn btn-secondary">Hủy bỏ</a>
                        <button type="submit" name="btn_capnhat" class="btn btn-warning fw-bold">Cập Nhật Bài Viết</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>
</html>