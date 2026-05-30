<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['user_name'] ?? '';

require_once 'config/database.php';
$conn = connectDB();

// Lấy tất cả địa điểm để hiển thị trên bản đồ
$result = $conn->query("SELECT id, name, location, region, rating, image_url, lat, lng FROM destinations ORDER BY rating DESC");
$destinations = [];
while ($row = $result->fetch_assoc()) {
    $destinations[] = $row;
}
$conn->close();

$regionLabel = ['bien' => 'Biển & Đảo', 'lichsu' => 'Di Sản', 'rung' => 'Đại Ngàn'];
$regionColor = ['bien' => '#0bb8cf', 'lichsu' => '#e67e22', 'rung' => '#1a6b4a'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Bản Đồ - Du Lịch Gia Lai</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/map.css">
    <!-- Leaflet.js -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
    <a href="index.php">
        <div class="logo">GIA LAI <span>TOURISM</span> <span class="b">NÈ</span></div>
    </a>
    <ul class="menu">
        <li><a href="index.php">Trang Chủ</a></li>
        <li><a href="map.php" class="active">Bản Đồ</a></li>
        <li><a href="about.php">Giới Thiệu</a></li>
        <li><a href="dt/destinations.php">Điểm Đến</a></li>
        <li><a href="contact.php">Liên Hệ</a></li>
    </ul>
    <form class="search-bar" action="search.php" method="GET">
        <input type="text" name="q" placeholder="Tìm địa điểm...">
        <button type="submit">Tìm</button>
    </form>
    <div class="nav-user">
        <?php if ($isLoggedIn): ?>
            <span class="user-name">Xin chào, <?= htmlspecialchars($userName) ?></span>
            <a href="api/auth.php?action=logout" class="btn-logout">Đăng Xuất</a>
        <?php else: ?>
            <a href="login.php">Đăng Nhập</a>
            <a href="register.php" class="btn-register">Đăng Ký</a>
        <?php endif; ?>
    </div>
</nav>

<!-- TIÊU ĐỀ -->
<div class="map-header">
    <h1>Bản Đồ Du Lịch</h1>
    <p>Khám phá các điểm đến nổi bật trên bản đồ tương tác</p>
</div>

<!-- BỘ LỌC -->
<div class="map-filter-wrap">
    <button class="map-filter-btn active" onclick="filterMap('all', this)">Tất Cả</button>
    <button class="map-filter-btn" onclick="filterMap('bien', this)" style="--accent:#0bb8cf">🌊 Biển & Đảo</button>
    <button class="map-filter-btn" onclick="filterMap('lichsu', this)" style="--accent:#e67e22">🏛 Di Sản</button>
    <button class="map-filter-btn" onclick="filterMap('rung', this)" style="--accent:#1a6b4a">🌿 Đại Ngàn</button>
</div>

<!-- LAYOUT: BẢN ĐỒ + DANH SÁCH -->
<div class="map-layout">

    <!-- Danh sách địa điểm -->
    <div class="map-list" id="mapList">
        <?php foreach ($destinations as $d): ?>
        <div class="map-list-item" data-region="<?= $d['region'] ?>" data-id="<?= $d['id'] ?>"
             onclick="focusMarker(<?= $d['id'] ?>)">
            <img src="<?= htmlspecialchars($d['image_url']) ?>" alt="<?= htmlspecialchars($d['name']) ?>">
            <div class="map-list-info">
                <span class="map-list-tag" style="background-color:<?= $regionColor[$d['region']] ?? '#888' ?>">
                    <?= $regionLabel[$d['region']] ?? $d['region'] ?>
                </span>
                <strong><?= htmlspecialchars($d['name']) ?></strong>
                <span>📍 <?= htmlspecialchars($d['location']) ?></span>
                <span>⭐ <?= number_format($d['rating'], 1) ?>/5</span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Bản đồ -->
    <div id="map"></div>

</div>

<!-- Chú thích -->
<div class="map-legend">
    <span class="legend-item"><span class="legend-dot" style="background:#0bb8cf"></span> Biển & Đảo</span>
    <span class="legend-item"><span class="legend-dot" style="background:#e67e22"></span> Di Sản</span>
    <span class="legend-item"><span class="legend-dot" style="background:#1a6b4a"></span> Đại Ngàn</span>
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
        <p>© 2026 Gia Lai Tourism — Sở Du Lịch tỉnh Gia Lai</p>
    </div>
</footer>

<script>
// Dữ liệu địa điểm từ PHP
var destinations = <?= json_encode($destinations) ?>;

var regionColor = {
    'bien':   '#0bb8cf',
    'lichsu': '#e67e22',
    'rung':   '#1a6b4a'
};

// Khởi tạo bản đồ, tập trung vào khu vực Quy Nhơn - Gia Lai
var map = L.map('map').setView([13.98, 108.24], 9);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
}).addTo(map);

// Lưu marker theo id
var markers = {};

// Tạo custom icon tròn có màu
function makeIcon(color) {
    return L.divIcon({
        className: '',
        html: '<div style="width:16px;height:16px;background:' + color + ';border:3px solid white;border-radius:50%;box-shadow:0 2px 6px rgba(0,0,0,0.35);"></div>',
        iconSize: [16, 16],
        iconAnchor: [8, 8],
        popupAnchor: [0, -10]
    });
}

// Thêm marker cho từng địa điểm
destinations.forEach(function(d) {
    // Nếu không có lat/lng trong DB, dùng toạ độ mặc định vùng Quy Nhơn/Gia Lai
    var lat = parseFloat(d.lat) || (13.7 + Math.random() * 0.8);
    var lng = parseFloat(d.lng) || (108.1 + Math.random() * 0.5);

    var color = regionColor[d.region] || '#888';
    var icon  = makeIcon(color);

    var popup = '<div style="min-width:180px;">'
        + '<img src="' + d.image_url + '" style="width:100%;height:100px;object-fit:cover;border-radius:6px;margin-bottom:8px;">'
        + '<strong style="font-size:14px;">' + d.name + '</strong><br>'
        + '<span style="font-size:12px;color:#666;">📍 ' + d.location + '</span><br>'
        + '<span style="font-size:12px;color:#666;">⭐ ' + parseFloat(d.rating).toFixed(1) + '/5</span><br>'
        + '<a href="dt/destination-detail.php?id=' + d.id + '" style="display:inline-block;margin-top:8px;padding:5px 12px;background:#1a6b4a;color:white;border-radius:12px;font-size:12px;font-weight:bold;text-decoration:none;">Xem Chi Tiết</a>'
        + '</div>';

    var marker = L.marker([lat, lng], { icon: icon })
        .addTo(map)
        .bindPopup(popup);

    markers[d.id] = { marker: marker, region: d.region, lat: lat, lng: lng };
});

// Focus vào marker khi click danh sách
function focusMarker(id) {
    var m = markers[id];
    if (!m) return;
    map.setView([m.lat, m.lng], 13);
    m.marker.openPopup();

    // Highlight item danh sách
    document.querySelectorAll('.map-list-item').forEach(function(el) {
        el.classList.remove('active');
    });
    var el = document.querySelector('[data-id="' + id + '"]');
    if (el) {
        el.classList.add('active');
        el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}

// Lọc theo loại
var currentFilter = 'all';

function filterMap(region, btn) {
    currentFilter = region;

    // Cập nhật nút active
    document.querySelectorAll('.map-filter-btn').forEach(function(b) {
        b.classList.remove('active');
    });
    btn.classList.add('active');

    // Hiển thị/ẩn marker
    Object.keys(markers).forEach(function(id) {
        var m = markers[id];
        if (region === 'all' || m.region === region) {
            m.marker.addTo(map);
        } else {
            map.removeLayer(m.marker);
        }
    });

    // Hiển thị/ẩn list item
    document.querySelectorAll('.map-list-item').forEach(function(el) {
        if (region === 'all' || el.dataset.region === region) {
            el.style.display = 'flex';
        } else {
            el.style.display = 'none';
        }
    });
}
</script>

</body>
</html>
