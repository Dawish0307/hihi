<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['user_name'] ?? '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giới Thiệu - Du Lịch Gia Lai</title>
    <link href="https://fonts.googleapis.com/css2?family=Pinyon+Script&family=Great+Vibes&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/about.css">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
    <div class="logo">⛰ GIA LAI <span>TOURISM</span></div>
    <ul class="menu">
        <li><a href="index.php">Trang Chủ</a></li>
        <li><a href="map.php">Bản Đồ</a></li>
        <li><a href="about.php" class="active">Giới Thiệu</a></li>
        <li><a href="dt/destinations.php">Điểm Đến</a></li>
        <li><a href="contact.php">Liên Hệ</a></li>
    </ul>
    <form class="search-bar" action="dt/destinations.php" method="GET">
        <input type="text" name="q" placeholder="Tìm địa điểm..." autocomplete="off">
        <button type="submit">Tìm</button>
    </form>
    <div class="nav-user">
        <?php if ($isLoggedIn): ?>
            <span class="user-name">👤 <?= htmlspecialchars($userName) ?></span>
            <a href="index.php?action=logout" class="btn-logout">Đăng Xuất</a>
        <?php else: ?>
            <a href="login.php">Đăng Nhập</a>
            <a href="register.php" class="btn-register">Đăng Ký</a>
        <?php endif; ?>
    </div>
</nav>

<!-- ẢNH BÌA -->
<div class="about-cover">
    <img src="https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=1600&q=80" alt="Gia Lai">
    <div class="about-cover-overlay"></div>
    <div class="about-cover-text">
        <h1>Giới Thiệu</h1>
        <h2>Gia Lai — Đại Ngàn Chạm Biển Xanh</h2>
    </div>
</div>

<!-- TỔNG QUAN -->
<div class="ab-section">
    <div class="ab-container">
        <h2 class="ab-title">Tổng Quan Về Gia Lai</h2>
        <div class="ab-two-col">
            <div>
                <p>Năm 2025, tỉnh Gia Lai và Bình Định chính thức hợp nhất tạo nên một vùng đất đặc biệt — nơi đại ngàn Tây Nguyên hùng vĩ gặp gỡ bờ biển Quy Nhơn xanh trong thơ mộng.</p>
                <br>
                <p>Với diện tích hơn 20.000 km² và đường bờ biển dài 134km, đây là một trong những tỉnh có cảnh quan đa dạng nhất Việt Nam — từ rừng nguyên sinh, cao nguyên xanh mát đến hải đảo hoang sơ.</p>
                <br>
                <p>Gia Lai cũng là vùng đất của 38 dân tộc anh em, nơi lưu giữ di sản văn hóa cồng chiêng Tây Nguyên và hào khí võ cổ truyền Bình Định.</p>
            </div>
            <div>
                <img src="https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=600&q=80" alt="Đại ngàn" class="ab-img">
            </div>
        </div>
    </div>
</div>

<!-- THỐNG KÊ -->
<div class="ab-stats">
    <div class="ab-container">
        <div class="ab-stats-row">
            <div class="ab-stat">
                <span class="ab-stat-num">20.000+</span>
                <span class="ab-stat-label">km² diện tích</span>
            </div>
            <div class="ab-stat">
                <span class="ab-stat-num">134km</span>
                <span class="ab-stat-label">Đường bờ biển</span>
            </div>
            <div class="ab-stat">
                <span class="ab-stat-num">38</span>
                <span class="ab-stat-label">Dân tộc anh em</span>
            </div>
            <div class="ab-stat">
                <span class="ab-stat-num">2</span>
                <span class="ab-stat-label">Di sản UNESCO</span>
            </div>
            <div class="ab-stat">
                <span class="ab-stat-num">12.4M</span>
                <span class="ab-stat-label">Lượt khách 2025</span>
            </div>
        </div>
    </div>
</div>

<!-- LỊCH SỬ -->
<div class="ab-section" style="background-color:#f9f9f9;">
    <div class="ab-container">
        <h2 class="ab-title">Lịch Sử Hình Thành</h2>
        <div class="ab-timeline">

            <div class="ab-tl-item">
                <div class="ab-tl-year">Thế kỷ 11–13</div>
                <div class="ab-tl-dot"></div>
                <div class="ab-tl-content">
                    <h3>Vương Quốc Chăm Pa</h3>
                    <p>Bình Định là trung tâm của vương quốc Chăm Pa. Hệ thống tháp Chăm được xây dựng và nhiều tháp vẫn còn đến ngày nay.</p>
                </div>
            </div>

            <div class="ab-tl-item">
                <div class="ab-tl-year">1771</div>
                <div class="ab-tl-dot"></div>
                <div class="ab-tl-content">
                    <h3>Khởi Nghĩa Tây Sơn</h3>
                    <p>Ba anh em Nguyễn Huệ dấy binh tại đất Tây Sơn, mở ra trang sử hào hùng của dân tộc Việt Nam.</p>
                </div>
            </div>

            <div class="ab-tl-item">
                <div class="ab-tl-year">2003</div>
                <div class="ab-tl-dot"></div>
                <div class="ab-tl-content">
                    <h3>Di Sản UNESCO Cồng Chiêng</h3>
                    <p>Không gian văn hóa Cồng Chiêng Tây Nguyên được UNESCO công nhận là Di sản Văn hóa Phi vật thể của nhân loại.</p>
                </div>
            </div>

            <div class="ab-tl-item">
                <div class="ab-tl-year">2021</div>
                <div class="ab-tl-dot"></div>
                <div class="ab-tl-content">
                    <h3>Khu Dự Trữ Sinh Quyển Kon Hà Nừng</h3>
                    <p>UNESCO công nhận khu rừng nguyên sinh lớn nhất Đông Nam Á tại Gia Lai.</p>
                </div>
            </div>

            <div class="ab-tl-item ab-tl-highlight">
                <div class="ab-tl-year">2025</div>
                <div class="ab-tl-dot"></div>
                <div class="ab-tl-content">
                    <h3>Hợp Nhất Lịch Sử</h3>
                    <p>Gia Lai và Bình Định hợp nhất — đại ngàn chạm biển xanh, mở ra kỷ nguyên phát triển mới.</p>
                </div>
            </div>

            <div class="ab-tl-item ab-tl-gold">
                <div class="ab-tl-year">2026</div>
                <div class="ab-tl-dot"></div>
                <div class="ab-tl-content">
                    <h3>Năm Du Lịch Quốc Gia</h3>
                    <p>Gia Lai tổ chức Năm Du Lịch Quốc Gia 2026 với chủ đề "Đại Ngàn Chạm Biển Xanh".</p>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- VĂN HÓA -->
<div class="ab-section">
    <div class="ab-container">
        <h2 class="ab-title">Di Sản Văn Hóa</h2>
        <p class="ab-desc">Kho tàng văn hóa từ Tây Nguyên đến đất võ Bình Định</p>
        <div class="ab-card-row">
            <div class="ab-card">
                <img src="https://images.unsplash.com/photo-1528360983277-13d401cdc186?w=500&q=80" alt="Cồng Chiêng">
                <div class="ab-card-body">
                    <span class="ab-tag">UNESCO</span>
                    <h3>Cồng Chiêng Tây Nguyên</h3>
                    <p>Di sản văn hóa phi vật thể của nhân loại, âm thanh vang vọng từ ngàn đời.</p>
                </div>
            </div>
            <div class="ab-card">
                <img src="https://images.unsplash.com/photo-1548013146-72479768bada?w=500&q=80" alt="Tháp Chăm">
                <div class="ab-card-body">
                    <span class="ab-tag">Di Tích</span>
                    <h3>Di Sản Chăm Pa</h3>
                    <p>Tháp Đôi, Bánh Ít, Dương Long — công trình ngàn năm còn sừng sững.</p>
                </div>
            </div>
            <div class="ab-card">
                <img src="https://images.unsplash.com/photo-1532274402911-5a369e4c4bb5?w=500&q=80" alt="Tây Sơn">
                <div class="ab-card-body">
                    <span class="ab-tag">Lịch Sử</span>
                    <h3>Hào Khí Tây Sơn</h3>
                    <p>Đất võ Bình Định — quê hương của vua Quang Trung và võ cổ truyền nổi tiếng.</p>
                </div>
            </div>
            <div class="ab-card">
                <img src="https://images.unsplash.com/photo-1504280390367-361c6d9f38f4?w=500&q=80" alt="Làng chài">
                <div class="ab-card-body">
                    <span class="ab-tag">Dân Gian</span>
                    <h3>Văn Hóa Làng Chài</h3>
                    <p>Tục thờ cá Ông, lễ hội cầu ngư — nét đẹp ngư dân ven biển Quy Nhơn.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ẨM THỰC -->
<div class="ab-section" style="background-color:#f9f9f9;">
    <div class="ab-container">
        <h2 class="ab-title">Ẩm Thực Nổi Bật</h2>
        <p class="ab-desc">Từ phở khô Pleiku đến hải sản tươi sống Quy Nhơn</p>
        <table class="ab-food-table">
            <thead>
                <tr>
                    <th>Món ăn</th>
                    <th>Đặc điểm</th>
                    <th>Địa phương</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>🍜 Phở Khô Pleiku</td>
                    <td>Bánh phở dai, nước dùng xương hầm đậm đà, thịt bò tái</td>
                    <td>Pleiku</td>
                </tr>
                <tr>
                    <td>🦐 Bún Chả Cá Quy Nhơn</td>
                    <td>Chả cá thu tươi, nước dùng cá thơm ngọt</td>
                    <td>Quy Nhơn</td>
                </tr>
                <tr>
                    <td>🦞 Hải Sản Tươi Sống</td>
                    <td>Tôm hùm, cua ghẹ, mực một nắng giá bình dân</td>
                    <td>Làng chài</td>
                </tr>
                <tr>
                    <td>🥘 Bánh Xèo Tôm Nhảy</td>
                    <td>Bánh giòn rụm, nhân tôm tươi, ăn kèm rau sống</td>
                    <td>Bình Định</td>
                </tr>
                <tr>
                    <td>☕ Cà Phê Tây Nguyên</td>
                    <td>Arabica nguyên chất, xuất khẩu 40+ quốc gia</td>
                    <td>Tây Nguyên</td>
                </tr>
                <tr>
                    <td>🍶 Rượu Cần</td>
                    <td>Ủ từ gạo nếp, uống trong lễ hội dân tộc</td>
                    <td>Bản làng</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- KHÍ HẬU -->
<div class="ab-section">
    <div class="ab-container">
        <h2 class="ab-title">Khí Hậu & Thời Điểm Du Lịch</h2>
        <div class="ab-two-col">
            <div>
                <h3 style="color:#1a6b4a; margin-bottom:10px;">🏔 Vùng Cao Nguyên (Pleiku)</h3>
                <p>Nhiệt độ trung bình 18–25°C quanh năm. Mùa khô từ tháng 11 đến tháng 4 là thời điểm đẹp nhất để tham quan.</p>
                <br>
                <h3 style="color:#1a6b4a; margin-bottom:10px;">🌊 Vùng Ven Biển (Quy Nhơn)</h3>
                <p>Khí hậu nhiệt đới ấm áp. Mùa du lịch biển đẹp nhất từ tháng 1 đến tháng 8. Tránh tháng 9–11 vì hay có mưa bão.</p>
            </div>
            <div>
                <table class="ab-weather-table">
                    <thead>
                        <tr>
                            <th>Tháng</th>
                            <th>Pleiku</th>
                            <th>Quy Nhơn</th>
                            <th>Ghi chú</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1 – 4</td>
                            <td>20–25°C</td>
                            <td>24–28°C</td>
                            <td>✅ Rất đẹp</td>
                        </tr>
                        <tr>
                            <td>5 – 8</td>
                            <td>18–22°C</td>
                            <td>28–32°C</td>
                            <td>✅ Biển đẹp</td>
                        </tr>
                        <tr>
                            <td>9 – 11</td>
                            <td>18–21°C</td>
                            <td>22–26°C</td>
                            <td>⚠️ Hay mưa</td>
                        </tr>
                        <tr>
                            <td>12</td>
                            <td>17–22°C</td>
                            <td>22–25°C</td>
                            <td>✅ Mát mẻ</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- NÚT ĐIỀU HƯỚNG -->
<div class="ab-cta">
    <h2>Sẵn Sàng Khám Phá?</h2>
    <p>Xem các địa điểm du lịch nổi bật của Gia Lai</p>
    <a href="dt/destinations.php" class="ab-btn">Xem Điểm Đến</a>
    <a href="contact.php" class="ab-btn-outline">Liên Hệ Tư Vấn</a>
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
                <li><a href="dt/destinations.php">Điểm Đến</a></li>
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
