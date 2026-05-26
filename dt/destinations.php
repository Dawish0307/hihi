<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['user_name'] ?? '';

require_once '../config/database.php';
$conn = connectDB();

// Lấy tất cả địa điểm
$sql = "SELECT * FROM destinations ORDER BY rating DESC";
$result = $conn->query($sql);
$destinations = [];
while ($row = $result->fetch_assoc()) {
    $destinations[] = $row;
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Điểm Đến - Du Lịch Gia Lai</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/destinations.css">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
    <div class="logo">⛰ GIA LAI <span>TOURISM</span></div>
    <ul class="menu">
        <li><a href="../index.php">Trang Chủ</a></li>
        <li><a href="../map.php">Bản Đồ</a></li>
        <li><a href="../about.php">Giới Thiệu</a></li>
        <li><a href="destinations.php" class="active">Điểm Đến</a></li>
        <li><a href="../contact.php">Liên Hệ</a></li>
    </ul>
    <form class="search-bar" action="destinations.php" method="GET">
        <input type="text" name="q" placeholder="Tìm địa điểm..." autocomplete="off"
               value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
        <button type="submit">Tìm</button>
    </form>
    <div class="nav-user">
        <?php if ($isLoggedIn): ?>
            <span class="user-name">👤 <?= htmlspecialchars($userName) ?></span>
            <a href="index.php?action=logout" class="btn-logout">Đăng Xuất</a>
        <?php else: ?>
            <a href="../login.php">Đăng Nhập</a>
            <a href="../register.php" class="btn-register">Đăng Ký</a>
        <?php endif; ?>
    </div>
</nav>

<!-- TIÊU ĐỀ TRANG -->
<div class="dest-header">
    <h1>Điểm Đến Nổi Bật</h1>
    <p>Khám phá những địa danh đẹp nhất từ biển xanh đến đại ngàn</p>
</div>

<!-- BỘ LỌC -->
<div class="dest-filter-wrap">
    <div class="dest-container">
        <div class="dest-filter">
            <a href="destinations.php" class="filter-btn <?= !isset($_GET['loai']) ? 'active' : '' ?>">Tất Cả</a>
            <a href="destinations.php?loai=bien" class="filter-btn <?= ($_GET['loai'] ?? '') === 'bien' ? 'active' : '' ?>">🌊 Biển & Đảo</a>
            <a href="destinations.php?loai=lichsu" class="filter-btn <?= ($_GET['loai'] ?? '') === 'lichsu' ? 'active' : '' ?>">🏛 Di Sản</a>
            <a href="destinations.php?loai=rung" class="filter-btn <?= ($_GET['loai'] ?? '') === 'rung' ? 'active' : '' ?>">🌿 Đại Ngàn</a>
        </div>
        <p class="dest-count">
            <?php
            // Đếm số địa điểm theo bộ lọc
            $conn2 = connectDB();
            $loai = $_GET['loai'] ?? '';
            $q    = $_GET['q'] ?? '';
            if ($loai && $q) {
                $s = $conn2->prepare("SELECT COUNT(*) as total FROM destinations WHERE region = ? AND (name LIKE ? OR location LIKE ?)");
                $kw = '%'.$q.'%';
                $s->bind_param("sss", $loai, $kw, $kw);
            } elseif ($loai) {
                $s = $conn2->prepare("SELECT COUNT(*) as total FROM destinations WHERE region = ?");
                $s->bind_param("s", $loai);
            } elseif ($q) {
                $s = $conn2->prepare("SELECT COUNT(*) as total FROM destinations WHERE name LIKE ?");
                $kw = '%'.$q.'%';
                $s->bind_param("s", $kw);
            } else {
                $s = $conn2->prepare("SELECT COUNT(*) as total FROM destinations");
            }
            $s->execute();
            $total = $s->get_result()->fetch_assoc()['total'];
            $conn2->close();
            echo $total . ' địa điểm';
            ?>
        </p>
    </div>
</div>

<!-- DANH SÁCH ĐỊA ĐIỂM -->
<div class="dest-container" style="padding: 30px 20px 60px;">

    <?php
    // Truy vấn lại theo bộ lọc
    $conn3 = connectDB();
    $loai = $_GET['loai'] ?? '';
    $q    = $_GET['q'] ?? '';

    if ($loai && $q) {
        $stmt = $conn3->prepare("SELECT * FROM destinations WHERE region = ? AND (name LIKE ? OR location LIKE ?) ORDER BY rating DESC");
        $kw = '%'.$q.'%';
        $stmt->bind_param("sss", $loai, $kw, $kw);
    } elseif ($loai) {
        $stmt = $conn3->prepare("SELECT * FROM destinations WHERE region = ? ORDER BY rating DESC");
        $stmt->bind_param("s", $loai);
    } elseif ($q) {
        $stmt = $conn3->prepare("SELECT * FROM destinations WHERE name LIKE ?");
        $kw = '%'.$q.'%';
        $stmt->bind_param("s", $kw);
    } else {
        $stmt = $conn3->prepare("SELECT * FROM destinations ORDER BY rating DESC");
    }
    $stmt->execute();
    $rows = $stmt->get_result();

    $regionLabel = ['bien' => '🌊 Biển & Đảo', 'lichsu' => '🏛 Di Sản', 'rung' => '🌿 Đại Ngàn'];

    if ($rows->num_rows === 0):
    ?>
        <div style="text-align:center; padding:60px 0; color:#888;">
            <p style="font-size:40px;">🔍</p>
            <p style="font-size:16px; margin-top:10px;">Không tìm thấy địa điểm nào.</p>
            <a href="destinations.php" style="color:#1a6b4a; font-weight:bold;">Xem tất cả địa điểm</a>
        </div>
    <?php else: ?>

    <div class="dest-grid">
        <?php while ($d = $rows->fetch_assoc()): ?>
        <div class="dest-card">
            <div class="dest-card-img">
                <img src="<?= htmlspecialchars($d['image_url']) ?>" alt="<?= htmlspecialchars($d['name']) ?>" loading="lazy">
                <span class="dest-region-tag"><?= $regionLabel[$d['region']] ?? $d['region'] ?></span>
                <span class="dest-rating">⭐ <?= number_format($d['rating'], 1) ?></span>
            </div>
            <div class="dest-card-body">
                <h3><?= htmlspecialchars($d['name']) ?></h3>
                <p class="dest-location">📍 <?= htmlspecialchars($d['location']) ?></p>
                <p class="dest-desc"><?= htmlspecialchars($d['description']) ?></p>
                <a href="destination-detail.php?id=<?= $d['id'] ?>" class="dest-btn">Xem Chi Tiết</a>
            </div>
        </div>
        <?php endwhile; ?>
    </div>

    <?php endif; ?>
    <?php $conn3->close(); ?>

</div>

<!-- FOOTER -->
<footer class="footer">
    <div class="footer-grid">
        <div>
            <div class="brand-name">⛰ GIA LAI TOURISM</div>
            <p>Năm Du Lịch Quốc Gia 2026<br>"Đại Ngàn Chạm Biển Xanh"</p>
        </div>
        <div>
            <h4>Điểm Đến</h4>
            <ul>
                <li><a href="#">Eo Gió - Kỳ Co</a></li>
                <li><a href="#">Ghềnh Ráng Tiên Sa</a></li>
                <li><a href="#">Hòn Khô</a></li>
                <li><a href="#">Cù Lao Xanh</a></li>
            </ul>
        </div>
        <div>
            <h4>Khám Phá</h4>
            <ul>
                <li><a href="about.php">Giới Thiệu</a></li>
                <li><a href="destinations.php">Điểm Đến</a></li>
                <li><a href="map.php">Bản Đồ</a></li>
                <li><a href="contact.php">Liên Hệ</a></li>
            </ul>
        </div>
        <div>
            <h4>Tài Khoản</h4>
            <ul>
                <li><a href="login.php">Đăng Nhập</a></li>
                <li><a href="register.php">Đăng Ký</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <p>© 2026 Gia Lai Tourism — Sở Du Lịch tỉnh Gia Lai</p>
    </div>
</footer>

</body>
</html>
