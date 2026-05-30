<?php
$pageTitle  = 'Quản Lý Đánh Giá';
$activePage = 'reviews';
include 'layout.php';

$msg = '';

// Xóa đánh giá
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $conn->query("DELETE FROM reviews WHERE id=".intval($_GET['delete']));
    header('Location: reviews.php?msg=deleted'); exit;
}
if (isset($_GET['msg'])) $msg = $_GET['msg']==='deleted' ? 'Đã xóa đánh giá.' : '';

$q = $_GET['q'] ?? '';
$star = $_GET['star'] ?? '';
$where = [];
if ($q)    $where[] = "(u.full_name LIKE '%".$conn->real_escape_string($q)."%' OR d.name LIKE '%".$conn->real_escape_string($q)."%')";
if ($star) $where[] = "r.rating=".intval($star);

$sql = "SELECT r.id, r.rating, r.comment, r.created_at, u.full_name, d.name as dest_name
        FROM reviews r
        JOIN users u ON r.user_id = u.id
        JOIN destinations d ON r.destination_id = d.id"
     . ($where ? " WHERE ".implode(' AND ',$where) : '')
     . " ORDER BY r.created_at DESC";
$reviews = $conn->query($sql);
$conn->close();
?>

<?php if ($msg): ?><div class="alert alert-success">✓ <?= htmlspecialchars($msg) ?></div><?php endif; ?>

<div class="data-table-wrap">
    <div class="table-head">
        <div class="table-head-title">Danh Sách Đánh Giá (<?= $reviews->num_rows ?>)</div>
        <div class="table-actions">
            <form method="GET" style="display:flex;gap:8px">
                <input class="search-input" name="q" placeholder="Tìm người dùng, địa điểm..." value="<?= htmlspecialchars($q) ?>">
                <div class="filter-tabs">
                    <a href="reviews.php" class="filter-tab <?= !$star?'active':'' ?>">Tất cả</a>
                    <?php for ($i=5;$i>=1;$i--): ?>
                    <a href="reviews.php?star=<?= $i ?>" class="filter-tab <?= $star==$i?'active':'' ?>"><?= $i ?>★</a>
                    <?php endfor; ?>
                </div>
            </form>
        </div>
    </div>
    <table>
        <thead><tr>
            <th>#</th><th>Người dùng</th><th>Địa điểm</th><th>Sao</th><th>Nội dung</th><th>Ngày</th><th>Thao tác</th>
        </tr></thead>
        <tbody>
        <?php if ($reviews->num_rows === 0): ?>
        <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--muted)">Không có đánh giá nào.</td></tr>
        <?php else: while ($r = $reviews->fetch_assoc()): ?>
        <tr>
            <td class="td-mono"><?= $r['id'] ?></td>
            <td><?= htmlspecialchars($r['full_name']) ?></td>
            <td class="td-muted"><?= htmlspecialchars($r['dest_name']) ?></td>
            <td>
                <?php
                $colors = [1=>'badge-red',2=>'badge-red',3=>'badge-yellow',4=>'badge-blue',5=>'badge-green'];
                echo '<span class="badge '.($colors[$r['rating']]??'badge-gray').'">'.str_repeat('★',$r['rating']).'</span>';
                ?>
            </td>
            <td class="msg-cell">
                <span class="msg-preview"><?= htmlspecialchars(mb_substr($r['comment'],0,60)) ?><?= mb_strlen($r['comment'])>60?'…':'' ?></span>
            </td>
            <td class="td-muted"><?= date('d/m/Y', strtotime($r['created_at'])) ?></td>
            <td>
                <a href="reviews.php?delete=<?= $r['id'] ?>" class="btn btn-danger btn-sm"
                   onclick="return confirm('Xóa đánh giá này?')">Xóa</a>
            </td>
        </tr>
        <?php endwhile; endif; ?>
        </tbody>
    </table>
</div>

<?php include 'layout_end.php'; ?>
