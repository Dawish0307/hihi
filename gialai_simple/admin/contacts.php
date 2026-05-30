<?php
$pageTitle  = 'Quản Lý Liên Hệ';
$activePage = 'contacts';
include 'layout.php';

$msg = '';

// Đánh dấu đã đọc
if (isset($_GET['read']) && is_numeric($_GET['read'])) {
    $conn->query("UPDATE contacts SET is_read=1 WHERE id=".intval($_GET['read']));
    header('Location: contacts.php'); exit;
}
// Đánh dấu tất cả đã đọc
if (isset($_GET['readall'])) {
    $conn->query("UPDATE contacts SET is_read=1");
    header('Location: contacts.php?msg=readall'); exit;
}
// Xóa
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $conn->query("DELETE FROM contacts WHERE id=".intval($_GET['delete']));
    header('Location: contacts.php?msg=deleted'); exit;
}

if (isset($_GET['msg'])) {
    $msgs = ['deleted'=>'Đã xóa liên hệ.','readall'=>'Đã đánh dấu tất cả là đã đọc.'];
    $msg = $msgs[$_GET['msg']] ?? '';
}

$filter = $_GET['filter'] ?? 'all';
$sql = $filter === 'unread'
    ? "SELECT * FROM contacts WHERE is_read=0 ORDER BY created_at DESC"
    : "SELECT * FROM contacts ORDER BY created_at DESC";
$contacts = $conn->query($sql);
$conn->close();

$subjectLabel = ['Đặt Tour'=>'🗺 Đặt Tour','Đặt Phòng'=>'🏨 Đặt Phòng','Thông Tin'=>'ℹ Thông Tin','Khác'=>'💬 Khác'];
?>

<?php if ($msg): ?><div class="alert alert-success">✓ <?= htmlspecialchars($msg) ?></div><?php endif; ?>

<div class="data-table-wrap">
    <div class="table-head">
        <div class="table-head-title">
            Danh Sách Liên Hệ (<?= $contacts->num_rows ?>)
            <?php if ($unreadContacts > 0): ?>
            <span class="badge badge-red" style="margin-left:8px"><?= $unreadContacts ?> chưa đọc</span>
            <?php endif; ?>
        </div>
        <div class="table-actions">
            <div class="filter-tabs">
                <a href="contacts.php?filter=all"    class="filter-tab <?= $filter==='all'    ?'active':'' ?>">Tất cả</a>
                <a href="contacts.php?filter=unread" class="filter-tab <?= $filter==='unread' ?'active':'' ?>">Chưa đọc</a>
            </div>
            <?php if ($unreadContacts > 0): ?>
            <a href="contacts.php?readall=1" class="btn btn-outline btn-sm">✓ Đọc tất cả</a>
            <?php endif; ?>
        </div>
    </div>
    <table>
        <thead><tr>
            <th>#</th><th>Họ tên</th><th>Email</th><th>SĐT</th>
            <th>Chủ đề</th><th>Nội dung</th><th>Thời gian</th><th>Trạng thái</th><th>Thao tác</th>
        </tr></thead>
        <tbody>
        <?php if ($contacts->num_rows === 0): ?>
        <tr><td colspan="9" style="text-align:center;padding:40px;color:var(--muted)">Không có liên hệ nào.</td></tr>
        <?php else: while ($r = $contacts->fetch_assoc()): ?>
        <tr style="<?= !$r['is_read'] ? 'background:rgba(62,207,142,0.04)' : '' ?>">
            <td class="td-mono"><?= $r['id'] ?></td>
            <td><strong <?= !$r['is_read'] ? 'style="color:var(--accent)"' : '' ?>><?= htmlspecialchars($r['name']) ?></strong></td>
            <td class="td-muted"><a href="mailto:<?= htmlspecialchars($r['email']) ?>" style="color:var(--accent2)"><?= htmlspecialchars($r['email']) ?></a></td>
            <td class="td-muted"><?= htmlspecialchars($r['phone'] ?: '—') ?></td>
            <td><?php
                $sub = $r['subject'] ?? '';
                echo '<span class="badge badge-blue">'.htmlspecialchars($subjectLabel[$sub] ?? ($sub ?: '—')).'</span>';
            ?></td>
            <td class="msg-cell">
                <span class="msg-preview" title="<?= htmlspecialchars($r['message']) ?>">
                    <?= htmlspecialchars(mb_substr($r['message'], 0, 55)) ?><?= mb_strlen($r['message'])>55?'…':'' ?>
                </span>
            </td>
            <td class="td-muted"><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></td>
            <td>
                <?php if ($r['is_read']): ?>
                <span class="badge badge-gray">Đã đọc</span>
                <?php else: ?>
                <span class="badge badge-red">Chưa đọc</span>
                <?php endif; ?>
            </td>
            <td>
                <div style="display:flex;gap:5px;">
                    <?php if (!$r['is_read']): ?>
                    <a href="contacts.php?read=<?= $r['id'] ?>" class="btn btn-info btn-sm">✓</a>
                    <?php endif; ?>
                    <button class="btn btn-outline btn-sm" onclick="viewMsg(<?= htmlspecialchars(json_encode($r)) ?>)">👁</button>
                    <a href="contacts.php?delete=<?= $r['id'] ?>" class="btn btn-danger btn-sm"
                       onclick="return confirm('Xóa liên hệ này?')">🗑</a>
                </div>
            </td>
        </tr>
        <?php endwhile; endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal xem nội dung đầy đủ -->
<div class="modal-overlay" id="viewModal">
    <div class="modal" style="max-width:480px">
        <div class="modal-header">
            <h3 id="vm-name">—</h3>
            <button class="modal-close" onclick="closeModal('viewModal')">✕</button>
        </div>
        <div class="modal-body" style="display:flex;flex-direction:column;gap:12px">
            <div><span style="color:var(--muted);font-size:11px;text-transform:uppercase">Email</span><br><span id="vm-email" style="color:var(--accent2)"></span></div>
            <div><span style="color:var(--muted);font-size:11px;text-transform:uppercase">Điện thoại</span><br><span id="vm-phone"></span></div>
            <div><span style="color:var(--muted);font-size:11px;text-transform:uppercase">Chủ đề</span><br><span id="vm-subject"></span></div>
            <div><span style="color:var(--muted);font-size:11px;text-transform:uppercase">Nội dung</span><br><p id="vm-msg" style="font-size:13px;line-height:1.7;margin-top:4px;white-space:pre-wrap"></p></div>
        </div>
        <div class="modal-footer">
            <a id="vm-reply" href="#" class="btn btn-primary">✉ Trả lời</a>
            <button class="btn btn-outline" onclick="closeModal('viewModal')">Đóng</button>
        </div>
    </div>
</div>

<script>
function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
function viewMsg(r) {
    document.getElementById('vm-name').textContent    = r.name;
    document.getElementById('vm-email').textContent   = r.email;
    document.getElementById('vm-phone').textContent   = r.phone || '—';
    document.getElementById('vm-subject').textContent = r.subject || '—';
    document.getElementById('vm-msg').textContent     = r.message;
    document.getElementById('vm-reply').href = 'mailto:' + r.email + '?subject=Re: ' + encodeURIComponent(r.subject || 'Liên hệ');
    openModal('viewModal');
}
</script>

<?php include 'layout_end.php'; ?>
