<?php
// Dùng chung: include ở đầu mỗi trang admin
// Yêu cầu: $pageTitle, $activePage đã được set trước khi include

session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit;
}

require_once '../config/database.php';
$conn = connectDB();

// Đếm badge
$unreadContacts  = $conn->query("SELECT COUNT(*) as c FROM contacts WHERE is_read = 0")->fetch_assoc()['c'];
$totalUsers      = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$totalDest       = $conn->query("SELECT COUNT(*) as c FROM destinations")->fetch_assoc()['c'];
$totalContacts   = $conn->query("SELECT COUNT(*) as c FROM contacts")->fetch_assoc()['c'];
$totalReviews    = $conn->query("SELECT COUNT(*) as c FROM reviews")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pageTitle) ?> — Admin</title>
    <link rel="stylesheet" href="admin.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
</head>
<body>

<div class="admin-shell">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <span class="brand-icon">⛰</span>
            <div>
                <div class="brand-name">Gia Lai</div>
                <div class="brand-sub">Admin Panel</div>
            </div>
        </div>

        <nav class="sidebar-nav">
            <a href="index.php"        class="nav-item <?= $activePage === 'dashboard'    ? 'active' : '' ?>">
                <span class="nav-icon">◈</span> Tổng Quan
            </a>
            <a href="users.php"        class="nav-item <?= $activePage === 'users'        ? 'active' : '' ?>">
                <span class="nav-icon">◉</span> Người Dùng
                <span class="nav-count"><?= $totalUsers ?></span>
            </a>
            <a href="destinations.php" class="nav-item <?= $activePage === 'destinations' ? 'active' : '' ?>">
                <span class="nav-icon">◎</span> Địa Điểm
                <span class="nav-count"><?= $totalDest ?></span>
            </a>
            <a href="map.php"          class="nav-item <?= $activePage === 'map'          ? 'active' : '' ?>">
                <span class="nav-icon">◐</span> Bản Đồ
            </a>
            <a href="contacts.php"     class="nav-item <?= $activePage === 'contacts'     ? 'active' : '' ?>">
                <span class="nav-icon">◷</span> Liên Hệ
                <?php if ($unreadContacts > 0): ?>
                <span class="nav-badge"><?= $unreadContacts ?></span>
                <?php endif; ?>
            </a>
            <a href="reviews.php"      class="nav-item <?= $activePage === 'reviews'      ? 'active' : '' ?>">
                <span class="nav-icon">◈</span> Đánh Giá
                <span class="nav-count"><?= $totalReviews ?></span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="admin-avatar">
                <span><?= mb_substr($_SESSION['user_name'] ?? 'A', 0, 1) ?></span>
            </div>
            <div class="admin-info">
                <div class="admin-name"><?= htmlspecialchars($_SESSION['user_name'] ?? '') ?></div>
                <div class="admin-role">Administrator</div>
            </div>
            <a href="../api/auth.php?action=logout" class="logout-btn" title="Đăng xuất">⏻</a>
        </div>
    </aside>

    <!-- MAIN -->
    <main class="admin-main">
        <div class="topbar">
            <div class="topbar-title"><?= htmlspecialchars($pageTitle) ?></div>
            <a href="../index.php" class="topbar-link">← Xem trang web</a>
        </div>
        <div class="main-content">
