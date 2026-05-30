<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
$userName   = $_SESSION['user_name'] ?? '';
$userId     = $_SESSION['user_id'] ?? 0;

require_once '../config/database.php';
$conn = connectDB();

// Lấy id từ URL
$id = intval($_GET['id'] ?? 0);
if ($id === 0) {
    header('Location: destinations.php');
    exit;
}

// Lấy thông tin địa điểm
$stmt = $conn->prepare("SELECT * FROM destinations WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$d = $stmt->get_result()->fetch_assoc();

if (!$d) {
    header('Location: destinations.php');
    exit;
}

// Xử lý gửi đánh giá
$reviewMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isLoggedIn) {
    $rating  = intval($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');

    if ($rating < 1 || $rating > 5) {
        $reviewMsg = 'error:Vui lòng chọn số sao từ 1 đến 5.';
    } elseif (empty($comment)) {
        $reviewMsg = 'error:Vui lòng nhập nội dung đánh giá.';
    } else {
        $ins = $conn->prepare("INSERT INTO reviews (user_id, destination_id, rating, comment) VALUES (?, ?, ?, ?)");
        $ins->bind_param("iiis", $userId, $id, $rating, $comment);
        if ($ins->execute()) {
            $reviewMsg = 'success:Đánh giá của bạn đã được gửi!';
        } else {
            $reviewMsg = 'error:Gửi đánh giá thất bại, vui lòng thử lại.';
        }
    }
}

// Lấy danh sách đánh giá
$rev = $conn->prepare("
    SELECT r.rating, r.comment, r.created_at, u.full_name
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    WHERE r.destination_id = ?
    ORDER BY r.created_at DESC
");
$rev->bind_param("i", $id);
$rev->execute();
$reviews = $rev->get_result();

// Lấy địa điểm liên quan
$related = $conn->prepare("SELECT * FROM destinations WHERE region = ? AND id != ? LIMIT 3");
$related->bind_param("si", $d['region'], $id);
$related->execute();
$relatedRows = $related->get_result();

$regionLabel = ['bien' => '🌊 Biển & Đảo', 'lichsu' => '🏛 Di Sản', 'rung' => '🌿 Đại Ngàn'];

$conn->close();
?>
<html>
<head>
    <title><?= htmlspecialchars($d['name']) ?> - Du Lịch Gia Lai</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/destinations.css">
    <link rel="stylesheet" href="../css/detail.css">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
    <a href="../index.php">
        <div class="logo">GIA LAI <span>TOURISM</span> <span class ="b">NÈ</span></div>
    </a>
    <ul class="menu">
        <li><a href="../index.php">Trang Chủ</a></li>
        <li><a href="../map.php">Bản Đồ</a></li>
        <li><a href="../about.php">Giới Thiệu</a></li>
        <li><a href="../destinations.php" class="active">Điểm Đến</a></li>
        <li><a href="../contact.php">Liên Hệ</a></li>
    </ul>
    <form class="search-bar" action="destinations.php" method="GET">
        <input type="text" name="q" placeholder="Tìm địa điểm..." autocomplete="off">
        <button type="submit">Tìm</button>
    </form>
    <div class="nav-user">
        <?php if ($isLoggedIn): ?>
            <span class="user-name">👤 <?= htmlspecialchars($userName) ?></span>
            <a href="../index.php?action=logout" class="btn-logout">Đăng Xuất</a>
        <?php else: ?>
            <a href="../login.php">Đăng Nhập</a>
            <a href="../register.php" class="btn-register">Đăng Ký</a>
        <?php endif; ?>
    </div>
</nav>

<!-- ẢNH CHÍNH -->
<div class="detail-hero">
    <img src="<?= htmlspecialchars($d['image_url']) ?>" alt="<?= htmlspecialchars($d['name']) ?>">
    <div class="detail-hero-overlay"></div>
    <div class="detail-hero-text">
        <span class="detail-region"><?= $regionLabel[$d['region']] ?? '' ?></span>
        <h1><?= htmlspecialchars($d['name']) ?></h1>
        <p>📍 <?= htmlspecialchars($d['location']) ?> &nbsp;|&nbsp; ⭐ <?= number_format($d['rating'], 1) ?>/5</p>
    </div>
</div>

<!-- NỘI DUNG CHÍNH -->
<div class="detail-container">

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="index.php">Trang Chủ</a> &rsaquo;
        <a href="destinations.php">Điểm Đến</a> &rsaquo;
        <span><?= htmlspecialchars($d['name']) ?></span>
    </div>

    <div class="detail-layout">

        <!-- CỘT TRÁI: Nội dung -->
        <div class="detail-main">

            <div class="detail-box">
                <h2>Giới Thiệu</h2>
                <p><?= nl2br(htmlspecialchars($d['description'])) ?></p>
            </div>

            <!-- Thông tin nhanh -->
            <div class="detail-box">
                <h2>Thông Tin</h2>
                <table class="info-table">
                    <tr>
                        <td>Địa điểm</td>
                        <td><?= htmlspecialchars($d['location']) ?></td>
                    </tr>
                    <tr>
                        <td>Khu vực</td>
                        <td><?= $regionLabel[$d['region']] ?? '' ?></td>
                    </tr>
                    <tr>
                        <td>Đánh giá</td>
                        <td><?= number_format($d['rating'], 1) ?> / 5</td>
                    </tr>
                    <tr>
                        <td>Lượt xem</td>
                        <td><?= number_format($d['visit_count']) ?></td>
                    </tr>
                </table>
            </div>

            <!-- ĐÁNH GIÁ -->
            <div class="detail-box">
                <h2>Đánh Giá (<?= $reviews->num_rows ?>)</h2>

                <?php if ($isLoggedIn): ?>
                <!-- Form gửi đánh giá -->
                <?php
                    $msgType = '';
                    $msgText = '';
                    if ($reviewMsg) {
                        [$msgType, $msgText] = explode(':', $reviewMsg, 2);
                    }
                ?>
                <?php if ($msgText): ?>
                    <p style="color: <?= $msgType === 'success' ? 'green' : 'red' ?>; margin-bottom:12px;">
                        <?= htmlspecialchars($msgText) ?>
                    </p>
                <?php endif; ?>

                <form method="POST" action="destination-detail.php?id=<?= $id ?>">
                    <div class="review-stars" id="starRow">
                        <span>Chọn số sao:</span>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                        <label>
                            <input type="radio" name="rating" value="<?= $i ?>" required>
                            ⭐
                        </label>
                        <?php endfor; ?>
                    </div>
                    <div class="form-group">
                        <textarea name="comment" rows="3" placeholder="Chia sẻ cảm nhận của bạn..." required
                            style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px; font-family:inherit; font-size:14px; resize:vertical;"></textarea>
                    </div>
                    <button type="submit" class="dest-btn">Gửi Đánh Giá</button>
                </form>

                <?php else: ?>
                <p style="color:#888; font-size:14px; margin-bottom:16px;">
                    <a href="../login.php" style="color:#1a6b4a; font-weight:bold;">Đăng nhập</a> để gửi đánh giá.
                </p>
                <?php endif; ?>

                <!-- Danh sách đánh giá -->
                <?php if ($reviews->num_rows === 0): ?>
                <p style="color:#888; font-size:14px;">Chưa có đánh giá nào. Hãy là người đầu tiên!</p>
                <?php else: ?>
                <div class="review-list">
                    <?php while ($r = $reviews->fetch_assoc()): ?>
                    <div class="review-item">
                        <div class="review-top">
                            <span class="review-name">👤 <?= htmlspecialchars($r['full_name']) ?></span>
                            <span class="review-stars-display">
                                <?= str_repeat('⭐', $r['rating']) ?>
                            </span>
                            <span class="review-date"><?= date('d/m/Y', strtotime($r['created_at'])) ?></span>
                        </div>
                        <p class="review-comment"><?= nl2br(htmlspecialchars($r['comment'])) ?></p>
                    </div>
                    <?php endwhile; ?>
                </div>
                <?php endif; ?>
            </div>

        </div>

        <!-- CỘT PHẢI: Địa điểm liên quan -->
        <div class="detail-sidebar">
            <div class="detail-box">
                <h2>Địa Điểm Liên Quan</h2>
                <?php if ($relatedRows->num_rows === 0): ?>
                <p style="color:#888; font-size:13px;">Không có địa điểm liên quan.</p>
                <?php else: ?>
                <div class="related-list">
                    <?php while ($rel = $relatedRows->fetch_assoc()): ?>
                    <a href="destination-detail.php?id=<?= $rel['id'] ?>" class="related-item">
                        <img src="<?= htmlspecialchars($rel['image_url']) ?>" alt="<?= htmlspecialchars($rel['name']) ?>">
                        <div>
                            <strong><?= htmlspecialchars($rel['name']) ?></strong>
                            <span>📍 <?= htmlspecialchars($rel['location']) ?></span>
                            <span>⭐ <?= number_format($rel['rating'], 1) ?></span>
                        </div>
                    </a>
                    <?php endwhile; ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="detail-box">
                <h2>Liên Hệ Tư Vấn</h2>
                <p style="font-size:13px; color:#555; margin-bottom:14px;">Cần hỗ trợ lên kế hoạch tham quan?</p>
                <p style="font-size:14px; margin-bottom:8px;">📞 1800 599 920</p>
                <p style="font-size:14px; margin-bottom:16px;">✉ info@gialai-tourism.vn</p>
                <a href="contact.php" class="dest-btn" style="display:block; text-align:center;">Liên Hệ Ngay</a>
            </div>
        </div>

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
            <h4>Khám Phá</h4>
            <ul>
                <li><a href="about.php">Giới Thiệu</a></li>
                <li><a href="destinations.php">Điểm Đến</a></li>
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
