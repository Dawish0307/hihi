<?php
$pageTitle  = 'Quản Lý Địa Điểm';
$activePage = 'destinations';
include 'layout.php';

$msg = '';

// Xóa
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM destinations WHERE id=$id");
    header('Location: destinations.php?msg=deleted');
    exit;
}

// Thêm / Sửa
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id          = intval($_POST['id'] ?? 0);
    $name        = $conn->real_escape_string(trim($_POST['name'] ?? ''));
    $region      = $conn->real_escape_string($_POST['region'] ?? '');
    $location    = $conn->real_escape_string(trim($_POST['location'] ?? ''));
    $description = $conn->real_escape_string(trim($_POST['description'] ?? ''));
    $image_url   = $conn->real_escape_string(trim($_POST['image_url'] ?? ''));
    $rating      = floatval($_POST['rating'] ?? 4.5);
    $lat         = floatval($_POST['lat'] ?? 0);
    $lng         = floatval($_POST['lng'] ?? 0);

    if ($id > 0) {
        $conn->query("UPDATE destinations SET name='$name', region='$region', location='$location',
            description='$description', image_url='$image_url', rating=$rating, lat=$lat, lng=$lng
            WHERE id=$id");
        header('Location: destinations.php?msg=updated');
    } else {
        $conn->query("INSERT INTO destinations (name,region,location,description,image_url,rating,lat,lng)
            VALUES ('$name','$region','$location','$description','$image_url',$rating,$lat,$lng)");
        header('Location: destinations.php?msg=added');
    }
    exit;
}

if (isset($_GET['msg'])) {
    $msgs = ['added'=>'Đã thêm địa điểm mới.','updated'=>'Đã cập nhật địa điểm.','deleted'=>'Đã xóa địa điểm.'];
    $msg = $msgs[$_GET['msg']] ?? '';
}

// Lọc
$region = $_GET['region'] ?? '';
$q      = $_GET['q'] ?? '';
$where  = [];
if ($region) $where[] = "region='" . $conn->real_escape_string($region) . "'";
if ($q)      $where[] = "name LIKE '%" . $conn->real_escape_string($q) . "%'";
$sql  = "SELECT * FROM destinations" . ($where ? " WHERE ".implode(' AND ',$where) : '') . " ORDER BY rating DESC";
$rows = $conn->query($sql);

// Lấy 1 địa điểm để sửa
$editDest = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editDest = $conn->query("SELECT * FROM destinations WHERE id=".intval($_GET['edit']))->fetch_assoc();
}

$regionLabel = ['bien'=>'🌊 Biển & Đảo','lichsu'=>'🏛 Di Sản','rung'=>'🌿 Đại Ngàn'];
?>

<?php if ($msg): ?><div class="alert alert-success">✓ <?= htmlspecialchars($msg) ?></div><?php endif; ?>

<div class="data-table-wrap">
    <div class="table-head">
        <div class="table-head-title">Danh Sách Địa Điểm (<?= $rows->num_rows ?>)</div>
        <div class="table-actions">
            <form method="GET" style="display:flex;gap:8px;align-items:center;">
                <input class="search-input" name="q" placeholder="Tìm tên..." value="<?= htmlspecialchars($q) ?>">
                <div class="filter-tabs">
                    <a href="destinations.php" class="filter-tab <?= !$region?'active':'' ?>">Tất cả</a>
                    <a href="destinations.php?region=bien"   class="filter-tab <?= $region==='bien'   ?'active':'' ?>">Biển</a>
                    <a href="destinations.php?region=lichsu" class="filter-tab <?= $region==='lichsu' ?'active':'' ?>">Di Sản</a>
                    <a href="destinations.php?region=rung"   class="filter-tab <?= $region==='rung'   ?'active':'' ?>">Đại Ngàn</a>
                </div>
            </form>
            <button class="btn btn-primary" onclick="openModal('addModal')">+ Thêm Mới</button>
        </div>
    </div>
    <table>
        <thead><tr>
            <th>#</th><th>Ảnh</th><th>Tên địa điểm</th><th>Khu vực</th>
            <th>Đánh giá</th><th>Lượt xem</th><th>Toạ độ</th><th>Thao tác</th>
        </tr></thead>
        <tbody>
        <?php if ($rows->num_rows === 0): ?>
        <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--muted)">Không có dữ liệu.</td></tr>
        <?php else: while ($d = $rows->fetch_assoc()): ?>
        <tr>
            <td class="td-mono"><?= $d['id'] ?></td>
            <td><img src="<?= htmlspecialchars($d['image_url']) ?>" style="width:60px;height:42px;object-fit:cover;border-radius:5px;border:1px solid var(--border)"></td>
            <td><strong><?= htmlspecialchars($d['name']) ?></strong><br><span class="td-muted">📍 <?= htmlspecialchars($d['location']) ?></span></td>
            <td><?php
                $rc = ['bien'=>'badge-blue','lichsu'=>'badge-yellow','rung'=>'badge-green'];
                echo '<span class="badge '.($rc[$d['region']]??'badge-gray').'">'.($regionLabel[$d['region']]??$d['region']).'</span>';
            ?></td>
            <td><span style="color:var(--warn)">★ <?= $d['rating'] ?></span></td>
            <td class="td-mono"><?= number_format($d['visit_count']) ?></td>
            <td class="td-mono" style="font-size:11px">
                <?= $d['lat'] ? number_format($d['lat'],4).', '.number_format($d['lng'],4) : '<span style="color:var(--muted)">chưa có</span>' ?>
            </td>
            <td>
                <div style="display:flex;gap:6px;">
                    <a href="destinations.php?edit=<?= $d['id'] ?>" class="btn btn-info btn-sm">Sửa</a>
                    <a href="destinations.php?delete=<?= $d['id'] ?>" class="btn btn-danger btn-sm"
                       onclick="return confirm('Xóa địa điểm này?')">Xóa</a>
                </div>
            </td>
        </tr>
        <?php endwhile; endif; ?>
        </tbody>
    </table>
</div>

<!-- MODAL THÊM MỚI -->
<div class="modal-overlay" id="addModal">
    <div class="modal">
        <div class="modal-header">
            <h3>Thêm Địa Điểm Mới</h3>
            <button class="modal-close" onclick="closeModal('addModal')">✕</button>
        </div>
        <form method="POST" action="destinations.php">
        <div class="modal-body">
            <div class="form-grid">
                <div class="form-group full"><label>Tên địa điểm</label><input name="name" required placeholder="Eo Gió - Kỳ Co"></div>
                <div class="form-group">
                    <label>Khu vực</label>
                    <select name="region" required>
                        <option value="bien">🌊 Biển & Đảo</option>
                        <option value="lichsu">🏛 Di Sản</option>
                        <option value="rung">🌿 Đại Ngàn</option>
                    </select>
                </div>
                <div class="form-group"><label>Vị trí</label><input name="location" placeholder="Xã Nhơn Lý, Quy Nhơn"></div>
                <div class="form-group"><label>Đánh giá (1-5)</label><input name="rating" type="number" step="0.1" min="1" max="5" value="4.5"></div>
                <div class="form-group"><label>Vĩ độ (lat)</label><input name="lat" type="number" step="0.0000001" placeholder="13.7470"></div>
                <div class="form-group"><label>Kinh độ (lng)</label><input name="lng" type="number" step="0.0000001" placeholder="109.2430"></div>
                <div class="form-group full">
                    <label>URL ảnh</label>
                    <input name="image_url" id="add_img_url" placeholder="https://..." oninput="previewImg(this,'add_preview')">
                    <img id="add_preview" class="img-preview">
                </div>
                <div class="form-group full"><label>Mô tả</label><textarea name="description" rows="4" placeholder="Mô tả địa điểm..."></textarea></div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeModal('addModal')">Hủy</button>
            <button type="submit" class="btn btn-primary">Thêm Địa Điểm</button>
        </div>
        </form>
    </div>
</div>

<!-- MODAL SỬA -->
<?php if ($editDest): ?>
<div class="modal-overlay open" id="editModal">
    <div class="modal">
        <div class="modal-header">
            <h3>Chỉnh Sửa: <?= htmlspecialchars($editDest['name']) ?></h3>
            <button class="modal-close" onclick="window.location='destinations.php'">✕</button>
        </div>
        <form method="POST" action="destinations.php">
        <input type="hidden" name="id" value="<?= $editDest['id'] ?>">
        <div class="modal-body">
            <div class="form-grid">
                <div class="form-group full"><label>Tên địa điểm</label><input name="name" required value="<?= htmlspecialchars($editDest['name']) ?>"></div>
                <div class="form-group">
                    <label>Khu vực</label>
                    <select name="region" required>
                        <option value="bien"   <?= $editDest['region']==='bien'   ?'selected':'' ?>>🌊 Biển & Đảo</option>
                        <option value="lichsu" <?= $editDest['region']==='lichsu' ?'selected':'' ?>>🏛 Di Sản</option>
                        <option value="rung"   <?= $editDest['region']==='rung'   ?'selected':'' ?>>🌿 Đại Ngàn</option>
                    </select>
                </div>
                <div class="form-group"><label>Vị trí</label><input name="location" value="<?= htmlspecialchars($editDest['location']) ?>"></div>
                <div class="form-group"><label>Đánh giá</label><input name="rating" type="number" step="0.1" min="1" max="5" value="<?= $editDest['rating'] ?>"></div>
                <div class="form-group"><label>Vĩ độ (lat)</label><input name="lat" type="number" step="0.0000001" value="<?= $editDest['lat'] ?>"></div>
                <div class="form-group"><label>Kinh độ (lng)</label><input name="lng" type="number" step="0.0000001" value="<?= $editDest['lng'] ?>"></div>
                <div class="form-group full">
                    <label>URL ảnh</label>
                    <input name="image_url" id="edit_img_url" value="<?= htmlspecialchars($editDest['image_url']) ?>" oninput="previewImg(this,'edit_preview')">
                    <img id="edit_preview" class="img-preview" src="<?= htmlspecialchars($editDest['image_url']) ?>" style="display:<?= $editDest['image_url']?'block':'none' ?>">
                </div>
                <div class="form-group full"><label>Mô tả</label><textarea name="description" rows="4"><?= htmlspecialchars($editDest['description']) ?></textarea></div>
            </div>
        </div>
        <div class="modal-footer">
            <a href="destinations.php" class="btn btn-outline">Hủy</a>
            <button type="submit" class="btn btn-primary">Lưu Thay Đổi</button>
        </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
function previewImg(input, previewId) {
    var img = document.getElementById(previewId);
    if (input.value) { img.src = input.value; img.style.display = 'block'; }
    else { img.style.display = 'none'; }
}
</script>

<?php $conn->close(); include 'layout_end.php'; ?>
