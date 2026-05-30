<?php
$pageTitle  = 'Tổng Quan';
$activePage = 'dashboard';
include 'layout.php';

// Stats
$recentContacts = $conn->query("SELECT name, subject, created_at FROM contacts ORDER BY created_at DESC LIMIT 5");
$recentUsers    = $conn->query("SELECT full_name, email, created_at FROM users ORDER BY created_at DESC LIMIT 5");
$topDest        = $conn->query("SELECT name, rating, visit_count FROM destinations ORDER BY visit_count DESC LIMIT 5");
$totalReviews   = $conn->query("SELECT COUNT(*) as c FROM reviews")->fetch_assoc()['c'];
?>

<div class="stat-grid">
    <div class="stat-card green">
        <div class="stat-label">Người Dùng</div>
        <div class="stat-value"><?= $totalUsers ?></div>
        <div class="stat-sub">Tài khoản đã đăng ký</div>
    </div>
    <div class="stat-card blue">
        <div class="stat-label">Địa Điểm</div>
        <div class="stat-value"><?= $totalDest ?></div>
        <div class="stat-sub">Điểm đến trên hệ thống</div>
    </div>
    <div class="stat-card yellow">
        <div class="stat-label">Liên Hệ</div>
        <div class="stat-value"><?= $totalContacts ?></div>
        <div class="stat-sub"><?= $unreadContacts ?> chưa đọc</div>
    </div>
    <div class="stat-card red">
        <div class="stat-label">Đánh Giá</div>
        <div class="stat-value"><?= $totalReviews ?></div>
        <div class="stat-sub">Từ người dùng</div>
    </div>
</div>

<div class="recent-grid">
    <!-- Liên hệ mới -->
    <div class="data-table-wrap">
        <div class="table-head">
            <div class="table-head-title">Liên Hệ Mới Nhất</div>
            <a href="contacts.php" class="btn btn-outline btn-sm">Xem tất cả</a>
        </div>
        <table>
            <thead><tr>
                <th>Tên</th><th>Chủ đề</th><th>Thời gian</th>
            </tr></thead>
            <tbody>
            <?php while ($r = $recentContacts->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($r['name']) ?></td>
                <td><span class="badge badge-blue"><?= htmlspecialchars($r['subject'] ?: '—') ?></span></td>
                <td class="td-muted"><?= date('d/m H:i', strtotime($r['created_at'])) ?></td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Top địa điểm -->
    <div class="data-table-wrap">
        <div class="table-head">
            <div class="table-head-title">Địa Điểm Nổi Bật</div>
            <a href="destinations.php" class="btn btn-outline btn-sm">Xem tất cả</a>
        </div>
        <table>
            <thead><tr>
                <th>Tên</th><th>Đánh giá</th><th>Lượt xem</th>
            </tr></thead>
            <tbody>
            <?php while ($d = $topDest->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($d['name']) ?></td>
                <td><span style="color:var(--warn)">★ <?= $d['rating'] ?></span></td>
                <td class="td-mono"><?= number_format($d['visit_count']) ?></td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php $conn->close(); include 'layout_end.php'; ?>
