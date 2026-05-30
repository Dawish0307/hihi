<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['user_name'] ?? '';
?>
<html>
<head>
    <title>Du Lịch Gia Lai</title>
    <link href="https://fonts.googleapis.com/css2?family=Pinyon+Script&family=Great+Vibes&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php if (isset($_SESSION['msg'])): ?>
    <div class="msg-top success"><?= htmlspecialchars($_SESSION['msg']) ?></div>
    <?php unset($_SESSION['msg']); ?>
<?php endif; ?>
<!-- NAVBAR -->
<nav class="navbar">
    <a href="index.php">
        <div class="logo">GIA LAI <span>TOURISM</span> <span class ="b">NÈ</span></div>
    </a>

    <ul class="menu">
        <li><a href="index.php" class="active">Trang Chủ</a></li>
        <li><a href="map.php">Bản Đồ</a></li>
        <li><a href="about.php">Giới Thiệu</a></li>
        <li><a href="dt/destinations.php">Điểm Đến</a></li>
        <li><a href="contact.php">Liên Hệ</a></li>
    </ul>

    <!-- Thanh tìm kiếm -->
    <form class="search-bar" action="search.php" method="GET">
        <input type="text" name="q" placeholder="Tìm địa điểm, sự kiện...">
        <button type="submit">Tìm</button>
    </form>

    <!-- User -->
    <div class="nav-user">

        <?php if ($isLoggedIn): ?>
            <div class="nav-user">
                    <span class="user-name">Xin chào, <?= htmlspecialchars($userName) ?></span>
                    <a href="api/auth.php?action=logout" class="btn-logout">Đăng Xuất</a>
            </div>
        <?php else: ?>
            <a href="login.php">Đăng Nhập</a>
            <a href="register.php" class="btn-register">Đăng Ký</a>
        <?php endif; ?>
    </div>
</nav>

<section class="t1">
    <div class="t1-text">
        <h1>Đại Ngàn</h1>
        <h2>chạm</h2>
        <h1 class ="bx">Biển Xanh</h1> 
        <a href="dt/destinations.php" class="btn-t1">Khám Phá Ngay</a>
        <a href="about.php" class="btn-t1-outline">Tìm Hiểu Thêm</a>
    </div>
</section>

<!-- THỐNG KÊ -->
<div class="sl">
    <div class="sl-1">
        <span class="num">Số liệu</span>
        <span class="label">Lượt Khách 2025</span>
    </div>
    <div class="sl-1">
        <span class="num">Top</span>
        <span class="label">Điểm Đến Thế Giới</span>
    </div>
    <div class="sl-1">
        <span class="num">2</span>
        <span class="label">Sân Bay</span>
    </div>
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
            <h4>Hỗ Trợ</h4>
            <ul>
                <li><a href="#">Đặt Tour</a></li>
                <li><a href="#">Đặt Phòng</a></li>
                <li><a href="#">Thuê Xe</a></li>
                <li><a href="contact.php">Liên Hệ</a></li>
            </ul>
        </div>
        <div>
            <h4>Tài Khoản</h4>
            <ul>
                <li><a href="login.php">Đăng Nhập</a></li>
                <li><a href="register.php">Đăng Ký</a></li>
                <li><a href="profile.php">Hồ Sơ</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <p>2026 Gia Lai Tourism — Sở Du Lịch tỉnh Gia Lai</p>
    </div>
</footer>

<div class="toast" id="toast"></div>

<script src="js/main.js"></script>
</body>

</html>
