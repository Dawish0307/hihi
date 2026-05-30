<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['user_name'] ?? '';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'config/database.php';

    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($message)) {
        $error = 'Vui lòng điền đầy đủ thông tin bắt buộc.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không hợp lệ.';
    } else {
        $conn = connectDB();
        $stmt = $conn->prepare("INSERT INTO contacts (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $email, $phone, $subject, $message);
        if ($stmt->execute()) {
            $success = 'Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi trong vòng 24 giờ.';
            // Xóa dữ liệu form sau khi gửi thành công
            $_POST = [];
        } else {
            $error = 'Gửi thất bại, vui lòng thử lại.';
        }
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Liên Hệ - Du Lịch Gia Lai</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/contact.css">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
    <a href="index.php">
        <div class="logo">GIA LAI <span>TOURISM</span> <span class="b">NÈ</span></div>
    </a>
    <ul class="menu">
        <li><a href="index.php">Trang Chủ</a></li>
        <li><a href="map.php">Bản Đồ</a></li>
        <li><a href="about.php">Giới Thiệu</a></li>
        <li><a href="dt/destinations.php">Điểm Đến</a></li>
        <li><a href="contact.php" class="active">Liên Hệ</a></li>
    </ul>
    <form class="search-bar" action="search.php" method="GET">
        <input type="text" name="q" placeholder="Tìm địa điểm, sự kiện...">
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

<!-- TIÊU ĐỀ TRANG -->
<div class="contact-header">
    <h1>Liên Hệ Với Chúng Tôi</h1>
    <p>Chúng tôi luôn sẵn sàng hỗ trợ bạn lên kế hoạch du lịch tuyệt vời</p>
</div>

<!-- NỘI DUNG CHÍNH -->
<div class="contact-container">

    <!-- Thông tin liên hệ -->
    <div class="contact-info">
        <h2>Thông Tin Liên Hệ</h2>

        <div class="info-item">
            <span class="info-icon">📍</span>
            <div>
                <strong>Địa chỉ</strong>
                <p>01 Phan Đình Phùng, TP. Pleiku, Gia Lai</p>
            </div>
        </div>

        <div class="info-item">
            <span class="info-icon">📞</span>
            <div>
                <strong>Điện thoại</strong>
                <p>1800 599 920 (Miễn phí)</p>
                <p>(0269) 382 4888</p>
            </div>
        </div>

        <div class="info-item">
            <span class="info-icon">✉</span>
            <div>
                <strong>Email</strong>
                <p>info@gialai-tourism.vn</p>
                <p>hotro@gialai-tourism.vn</p>
            </div>
        </div>

        <div class="info-item">
            <span class="info-icon">🕐</span>
            <div>
                <strong>Giờ làm việc</strong>
                <p>Thứ 2 – Thứ 6: 7:30 – 17:00</p>
                <p>Thứ 7: 7:30 – 11:30</p>
            </div>
        </div>

        <div class="social-links">
            <p><strong>Theo dõi chúng tôi:</strong></p>
            <div class="social-row">
                <a href="#">Facebook</a>
                <a href="#">Zalo</a>
                <a href="#">YouTube</a>
            </div>
        </div>
    </div>

    <!-- Form liên hệ -->
    <div class="contact-form-wrap">
        <h2>Gửi Tin Nhắn</h2>

        <?php if ($success): ?>
            <div class="msg-box success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="msg-box error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="contact.php">
            <div class="form-row">
                <div class="form-group">
                    <label>Họ và Tên <span class="required">*</span></label>
                    <input type="text" name="name" placeholder="Nguyễn Văn A"
                           value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Email <span class="required">*</span></label>
                    <input type="email" name="email" placeholder="email@example.com"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Số điện thoại</label>
                    <input type="tel" name="phone" placeholder="0912 345 678"
                           value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Chủ đề</label>
                    <select name="subject">
                        <option value="">-- Chọn chủ đề --</option>
                        <option value="Đặt Tour"    <?= ($_POST['subject'] ?? '') === 'Đặt Tour'    ? 'selected' : '' ?>>Đặt Tour</option>
                        <option value="Đặt Phòng"   <?= ($_POST['subject'] ?? '') === 'Đặt Phòng'   ? 'selected' : '' ?>>Đặt Phòng</option>
                        <option value="Thông Tin"   <?= ($_POST['subject'] ?? '') === 'Thông Tin'   ? 'selected' : '' ?>>Thông Tin Du Lịch</option>
                        <option value="Khác"        <?= ($_POST['subject'] ?? '') === 'Khác'        ? 'selected' : '' ?>>Khác</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Nội dung <span class="required">*</span></label>
                <textarea name="message" rows="5" placeholder="Nhập nội dung tin nhắn..." required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
            </div>

            <button type="submit" class="btn-submit">Gửi Tin Nhắn</button>
        </form>
    </div>

</div>

<!-- BẢN ĐỒ -->
<div class="map-wrap">
    <iframe
        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3884.8!2d108.0!3d13.98!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x316b5b5e5e5e5e5f%3A0x5e5e5e5e5e5e5e5e!2sSở%20Du%20lịch%20Gia%20Lai!5e0!3m2!1svi!2svn!4v1"
        width="100%" height="300" style="border:0;" allowfullscreen="" loading="lazy">
    </iframe>
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

</body>
</html>
