<?php
session_start();
require 'connect.php';

// Kiểm tra quyền Admin hoặc Biên tập viên
if (!isset($_SESSION['MaND']) || !in_array($_SESSION['VaiTro'], ['QuanTriVien', 'BienTapVien'])) {
    header("Location: index.php");
    exit();
}

$maND = $_SESSION['MaND'];
$vaiTro = $_SESSION['VaiTro'];
$thongBao = "";

// 1. LẤY DANH SÁCH CHUYÊN MỤC CHO DROPDOWN FORM
$stmtCM = $pdo->query("SELECT * FROM ChuyenMuc");
$danhSachCM = $stmtCM->fetchAll();

// 2. XỬ LÝ DUYỆT BÀI VIẾT (CHỈ QUẢN TRỊ VIÊN MỚI ĐƯỢC DUYỆT)
if (isset($_GET['action']) && $_GET['action'] == 'duyet' && isset($_GET['id'])) {
    if ($vaiTro === 'QuanTriVien') {
        $idDuyet = $_GET['id'];
        $stmtUpdate = $pdo->prepare("UPDATE BaiViet SET TrangThai = 'DaDang' WHERE MaBV = :mabv");
        $stmtUpdate->execute(['mabv' => $idDuyet]);
    }
    header("Location: admin_baiviet.php");
    exit();
}

// 3. XỬ LÝ TÌM KIẾM BÀI VIẾT (ƯU TIÊN BÀI CHỜ DUYỆT & BÀI MỚI NHẤT LÊN ĐẦU)
$tuKhoa = isset($_GET['tukhoa']) ? trim($_GET['tukhoa']) : '';

if (!empty($tuKhoa)) {
    $sql = "SELECT bv.*, cm.TenCM, nd.HoTen AS TacGia 
            FROM BaiViet bv 
            LEFT JOIN ChuyenMuc cm ON bv.MaCM = cm.MaCM 
            LEFT JOIN NguoiDung nd ON bv.MaND = nd.MaND 
            WHERE bv.TieuDe LIKE :tukhoa OR nd.HoTen LIKE :tukhoa 
            ORDER BY CASE WHEN bv.TrangThai = 'ChoDuyet' THEN 1 ELSE 2 END, bv.NgayTao DESC, bv.MaBV DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['tukhoa' => "%$tuKhoa%"]);
} else {
    $sql = "SELECT bv.*, cm.TenCM, nd.HoTen AS TacGia 
            FROM BaiViet bv 
            LEFT JOIN ChuyenMuc cm ON bv.MaCM = cm.MaCM 
            LEFT JOIN NguoiDung nd ON bv.MaND = nd.MaND 
            ORDER BY CASE WHEN bv.TrangThai = 'ChoDuyet' THEN 1 ELSE 2 END, bv.NgayTao DESC, bv.MaBV DESC";
    $stmt = $pdo->query($sql);
}
$danhSachBaiViet = $stmt->fetchAll();

// 4. XỬ LÝ ĐĂNG BÀI VIẾT MỚI
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_dangbai'])) {
    $tieuDe = trim($_POST['tieude']);
    $maCM = $_POST['macm'];
    $tomTat = trim($_POST['tomtat']);
    $noiDung = trim($_POST['noidung']);
    
    // Quản trị viên đăng thì trực tiếp Đã đăng, Biên tập viên đăng thì Chờ duyệt
    $trangThai = ($vaiTro == 'QuanTriVien') ? 'DaDang' : 'ChoDuyet';

    // Xử lý Upload Ảnh minh họa
    $hinhAnh = "";
    if (isset($_FILES['hinhanh']) && $_FILES['hinhanh']['error'] == 0) {
        $targetDir = "upload/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $fileName = time() . '_' . basename($_FILES['hinhanh']['name']);
        $targetFilePath = $targetDir . $fileName;

        if (move_uploaded_file($_FILES['hinhanh']['tmp_name'], $targetFilePath)) {
            $hinhAnh = $fileName;
        }
    }

    if (!empty($tieuDe) && !empty($maCM) && !empty($noiDung)) {
        $maBV = 'BV' . time();
        $sqlInsert = "INSERT INTO BaiViet (MaBV, TieuDe, MaCM, TomTat, NoiDung, HinhAnh, MaND, TrangThai, NgayTao) 
                      VALUES (:mabv, :tieude, :macm, :tomtat, :noidung, :hinhanh, :mand, :trangthai, NOW())";
        $stmtInsert = $pdo->prepare($sqlInsert);
        $stmtInsert->execute([
            'mabv' => $maBV,
            'tieude' => $tieuDe,
            'macm' => $maCM,
            'tomtat' => $tomTat,
            'noidung' => $noiDung,
            'hinhanh' => $hinhAnh,
            'mand' => $maND,
            'trangthai' => $trangThai
        ]);
        header("Location: admin_baiviet.php");
        exit();
    } else {
        $thongBao = "<div class='alert alert-danger'>Vui lòng nhập đầy đủ các thông tin bắt buộc!</div>";
    }
}

// 5. XỬ LÝ XÓA BÀI VIẾT
if (isset($_GET['action']) && $_GET['action'] == 'xoa' && isset($_GET['id'])) {
    $idXoa = $_GET['id'];
    $stmtXoa = $pdo->prepare("DELETE FROM BaiViet WHERE MaBV = :mabv");
    $stmtXoa->execute(['mabv' => $idXoa]);
    header("Location: admin_baiviet.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Bài Viết</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- Header Topbar chuẩn vai trò -->
    <nav class="navbar navbar-dark bg-primary mb-4 shadow-sm">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold fs-4" href="<?= $vaiTro == 'QuanTriVien' ? 'admin.php' : 'index.php' ?>">
                <?= $vaiTro == 'QuanTriVien' ? 'Trang Quản Trị' : 'Trang Biên Tập' ?>
            </a>
            <div class="d-flex align-items-center text-white gap-3">
                <span>
                    <?= $vaiTro == 'QuanTriVien' ? '👑 Admin:' : '✍️ Biên tập viên:' ?> 
                    <strong><?= htmlspecialchars($_SESSION['HoTen']) ?></strong>
                </span>
                <a href="logout.php" class="btn btn-danger btn-sm">Đăng xuất</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4 my-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="fw-bold text-primary">📝 QUẢN LÝ BÀI VIẾT</h3>
            <span class="badge bg-secondary fs-6">Quyền: <?= $vaiTro == 'QuanTriVien' ? 'Quản Trị Viên (Toàn quyền)' : 'Biên Tập Viên' ?></span>
        </div>

        <?= $thongBao ?>

        <div class="row g-4">
            <!-- CỘT BÊN TRÁI: FORM ĐĂNG BÀI -->
            <div class="col-lg-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-success text-white fw-bold">
                        Đăng Bài Viết Mới
                    </div>
                    <div class="card-body">
                        <form action="admin_baiviet.php" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Chuyên mục <span class="text-danger">*</span></label>
                                <select name="macm" class="form-select" required>
                                    <option value="">-- Chọn chuyên mục --</option>
                                    <?php foreach ($danhSachCM as $cm): ?>
                                        <option value="<?= $cm['MaCM'] ?>"><?= htmlspecialchars($cm['TenCM']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Tiêu đề bài viết <span class="text-danger">*</span></label>
                                <input type="text" name="tieude" class="form-control" placeholder="Nhập tiêu đề..." required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Tóm tắt ngắn</label>
                                <textarea name="tomtat" class="form-control" rows="3" placeholder="Tóm tắt nội dung chính..."></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Nội dung chi tiết <span class="text-danger">*</span></label>
                                <textarea name="noidung" class="form-control" rows="6" placeholder="Nội dung bài viết..." required></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Hình ảnh minh họa</label>
                                <input type="file" name="hinhanh" class="form-control" accept="image/*">
                            </div>

                            <button type="submit" name="btn_dangbai" class="btn btn-success w-100 fw-bold">
                                <?= $vaiTro == 'QuanTriVien' ? '➕ Đăng bài viết' : '📤 Gửi bài chờ duyệt' ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- CỘT BÊN PHẢI: DANH SÁCH BÀI VIẾT -->
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-2">
                        <span class="fw-bold">Danh Sách Bài Viết <?= !empty($tuKhoa) ? '(Kết quả lọc)' : '(Tất cả)' ?></span>
                        
                        <form action="admin_baiviet.php" method="GET" class="d-flex gap-1" style="max-width: 320px;">
                            <input type="search" name="tukhoa" class="form-control form-control-sm" placeholder="Tìm tiêu đề, tác giả..." value="<?= htmlspecialchars($tuKhoa) ?>">
                            <button type="submit" class="btn btn-primary btn-sm text-nowrap">🔍 Tìm</button>
                            <?php if (!empty($tuKhoa)): ?>
                                <a href="admin_baiviet.php" class="btn btn-secondary btn-sm text-nowrap" title="Bỏ lọc">✕</a>
                            <?php endif; ?>
                        </form>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 text-center">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 35%;">Tiêu đề & Ngày tạo</th>
                                        <th>Chuyên mục</th>
                                        <th>Tác giả</th>
                                        <th>Trạng thái</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($danhSachBaiViet) > 0): ?>
                                        <?php foreach ($danhSachBaiViet as $bv): ?>
                                            <tr>
                                                <td class="text-start">
                                                    <a href="chitiet.php?id=<?= $bv['MaBV'] ?>" target="_blank" class="fw-bold text-decoration-none text-primary">
                                                        <?= htmlspecialchars($bv['TieuDe']) ?>
                                                    </a>
                                                    <div class="text-muted small">🕒 <?= $bv['NgayTao'] ?></div>
                                                </td>
                                                <td><span class="badge bg-info text-dark"><?= htmlspecialchars($bv['TenCM'] ?? 'Chưa xếp') ?></span></td>
                                                <td><?= htmlspecialchars($bv['TacGia'] ?? 'Ẩn danh') ?></td>
                                                <td>
                                                    <?php if ($bv['TrangThai'] == 'DaDang'): ?>
                                                        <span class="badge bg-success">Đã đăng</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning text-dark fw-bold">Chờ duyệt</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <!-- CHỈ CÓ QUẢN TRỊ VIÊN (QuanTriVien) MỚI THẤY NÚT DUYỆT BÀI -->
                                                    <?php if ($vaiTro === 'QuanTriVien' && $bv['TrangThai'] == 'ChoDuyet'): ?>
                                                        <a href="admin_baiviet.php?action=duyet&id=<?= $bv['MaBV'] ?>" class="btn btn-success btn-sm fw-bold me-1" title="Duyệt bài đăng">✓ Duyệt</a>
                                                    <?php endif; ?>

                                                    <a href="admin_suabaiviet.php?id=<?= $bv['MaBV'] ?>" class="btn btn-warning btn-sm text-dark fw-bold me-1">Sửa</a>
                                                    <a href="admin_baiviet.php?action=xoa&id=<?= $bv['MaBV'] ?>" class="btn btn-danger btn-sm fw-bold" onclick="return confirm('Anh có chắc muốn xóa bài viết này không?')">Xóa</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="py-4 text-muted">Không tìm thấy bài viết nào phù hợp!</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>