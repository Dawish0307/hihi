<?php
$pageTitle  = 'Quản Lý Người Dùng';
$activePage = 'users';
include 'layout.php';

$msg = '';

// Đổi role
if (isset($_GET['toggle_role']) && is_numeric($_GET['toggle_role'])) {
    $uid  = intval($_GET['toggle_role']);
    $curr = $conn->query("SELECT role FROM users WHERE id=$uid")->fetch_assoc()['role'];
    $new  = $curr === 'admin' ? 'user' : 'admin';
    $conn->query("UPDATE users SET role='$new' WHERE id=$uid");
    header('Location: users.php?msg=role');
    exit;
}

// Xóa người dùng
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $uid = intval($_GET['delete']);
    if ($uid != $_SESSION['user_id']) {
        $conn->query("DELETE FROM users WHERE id=$uid");
    }
    header('Location: users.php?msg=deleted');
    exit;
}

if (isset($_GET['msg'])) {
    $msgs = ['role' => 'Đã cập nhật quyền người dùng.', 'deleted' => 'Đã xóa người dùng.'];
    $msg = $msgs[$_GET['msg']] ?? '';
}

// Lọc + tìm kiếm
$q    = $_GET['q'] ?? '';
$role = $_GET['role'] ?? '';
$where = [];
if ($q)    $where[] = "(full_name LIKE '%" . $conn->real_escape_string($q) . "%' OR email LIKE '%" . $conn->real_escape_string($q) . "%')";
if ($role) $where[] = "role = '" . $conn->real_escape_string($role) . "'";
$sql = "SELECT * FROM users" . ($where ? " WHERE " . implode(' AND ', $where) : '') . " ORDER BY created_at DESC";
$users = $conn->query($sql);
?>

<?php if ($msg): ?><div class="alert alert-success">✓ <?= htmlspecialchars($msg) ?></div><?php endif; ?>

<div class="data-table-wrap">
    <div class="table-head">
        <div class="table-head-title">Danh Sách Người Dùng (<?= $users->num_rows ?>)</div>
        <div class="table-actions">
            <form method="GET" style="display:flex;gap:8px;">
                <input class="search-input" name="q" placeholder="Tìm tên, email..." value="<?= htmlspecialchars($q) ?>">
                <div class="filter-tabs">
                    <a href="users.php" class="filter-tab <?= !$role ? 'active' : '' ?>">Tất cả</a>
                    <a href="users.php?role=admin" class="filter-tab <?= $role==='admin' ? 'active' : '' ?>">Admin</a>
                    <a href="users.php?role=user"  class="filter-tab <?= $role==='user'  ? 'active' : '' ?>">User</a>
                </div>
            </form>
        </div>
    </div>
    <table>
        <thead><tr>
            <th>#</th><th>Họ tên</th><th>Email</th><th>Điện thoại</th>
            <th>Quyền</th><th>Ngày đăng ký</th><th>Thao tác</th>
        </tr></thead>
        <tbody>
        <?php if ($users->num_rows === 0): ?>
        <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--muted)">Không tìm thấy người dùng nào.</td></tr>
        <?php else: while ($u = $users->fetch_assoc()): ?>
        <tr>
            <td class="td-mono"><?= $u['id'] ?></td>
            <td><strong><?= htmlspecialchars($u['full_name']) ?></strong></td>
            <td class="td-muted"><?= htmlspecialchars($u['email']) ?></td>
            <td class="td-muted"><?= htmlspecialchars($u['phone'] ?: '—') ?></td>
            <td>
                <?php if ($u['role'] === 'admin'): ?>
                    <span class="badge badge-green">Admin</span>
                <?php else: ?>
                    <span class="badge badge-gray">User</span>
                <?php endif; ?>
            </td>
            <td class="td-muted"><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
            <td>
                <div style="display:flex;gap:6px;">
                    <?php if ($u['id'] != $_SESSION['user_id']): ?>
                    <a href="users.php?toggle_role=<?= $u['id'] ?>" class="btn btn-info btn-sm"
                       onclick="return confirm('Đổi quyền người dùng này?')">
                        <?= $u['role'] === 'admin' ? '↓ Hạ User' : '↑ Lên Admin' ?>
                    </a>
                    <a href="users.php?delete=<?= $u['id'] ?>" class="btn btn-danger btn-sm"
                       onclick="return confirm('Xóa người dùng này?')">Xóa</a>
                    <?php else: ?>
                    <span class="td-muted" style="font-size:11px">Tài khoản bạn</span>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
        <?php endwhile; endif; ?>
        </tbody>
    </table>
</div>

<?php $conn->close(); include 'layout_end.php'; ?>
