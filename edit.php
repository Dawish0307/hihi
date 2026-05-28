<?php
// ==================================================
// admin/edit.php — Trang chỉnh sửa địa điểm
// Cách dùng: edit.php?id=5
// ==================================================
session_start();

// Chỉ admin mới vào được
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

require_once '../config/database.php';
$conn = connectDB();

// ==================================================
// Lấy ID từ URL: edit.php?id=5
// $_GET đọc tham số trên URL (khác $_POST đọc từ form)
// intval() đảm bảo id là số nguyên, tránh lỗi bảo mật
// ==================================================
$id = intval($_GET['id'] ?? 0);

// Nếu không có id hợp lệ → quay về danh sách
if ($id <= 0) {
    header('Location: destination.php');
    exit;
}

// Lấy thông tin địa điểm cần sửa từ database
$stmt = $conn->prepare("SELECT * FROM destinations WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$dd = $stmt->get_result()->fetch_assoc(); // Lấy 1 hàng kết quả

// Nếu không tìm thấy → quay về danh sách
if (!$dd) {
    header('Location: destination.php');
    exit;
}

$thongBao = '';
$loiNhap  = [];

// ==================================================
// XỬ LÝ KHI ADMIN BẤM NÚT "LƯU THAY ĐỔI"
// ==================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name        = trim($_POST['name']        ?? '');
    $slug        = trim($_POST['slug']        ?? '');
    $region      = trim($_POST['region']      ?? '');
    $location    = trim($_POST['location']    ?? '');
    $description = trim($_POST['description'] ?? '');
    $image_url   = trim($_POST['image_url']   ?? '');
    $rating      = floatval($_POST['rating']  ?: 4.5);
    $lat         = floatval($_POST['lat']     ?? 0);
    $lng         = floatval($_POST['lng']     ?? 0);
    $featured    = intval($_POST['featured']  ?? 0);

    // Kiểm tra dữ liệu bắt buộc
    if (empty($name))   $loiNhap[] = 'Tên không được để trống!';
    if (empty($slug))   $loiNhap[] = 'Slug không được để trống!';
    if (empty($region)) $loiNhap[] = 'Vui lòng chọn loại!';

    // Kiểm tra slug trùng với địa điểm KHÁC (trừ chính nó)
    // "AND id != ?" nghĩa là: bỏ qua bản ghi đang sửa
    if (empty($loiNhap)) {
        $chk = $conn->prepare("SELECT id FROM destinations WHERE slug = ? AND id != ?");
        $chk->bind_param("si", $slug, $id);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            $loiNhap[] = 'Slug "' . $slug . '" đã được dùng bởi địa điểm khác!';
        }
    }

    // Nếu không có lỗi → cập nhật vào database
    if (empty($loiNhap)) {
        $stmt = $conn->prepare(
            "UPDATE destinations
             SET name=?, slug=?, region=?, location=?, description=?,
                 image_url=?, rating=?, lat=?, lng=?, featured=?
             WHERE id=?"
        );
        // "sssssssddi i": 10 tham số update + 1 id WHERE
        $stmt->bind_param("ssssssdddii",
            $name, $slug, $region, $location, $description,
            $image_url, $rating, $lat, $lng, $featured, $id
        );

        if ($stmt->execute()) {
            $thongBao = 'success';
            // Cập nhật lại biến $dd để form hiển thị giá trị mới
            $dd['name'] = $name; $dd['slug'] = $slug; $dd['region'] = $region;
            $dd['location'] = $location; $dd['description'] = $description;
            $dd['image_url'] = $image_url; $dd['rating'] = $rating;
            $dd['lat'] = $lat; $dd['lng'] = $lng; $dd['featured'] = $featured;
        } else {
            $thongBao = 'error';
        }
        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa: <?= htmlspecialchars($dd['name']) ?></title>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">

    <style>
        body { font-family: 'Inter', sans-serif; background: #f5f5f5; }

        .trang-sua { padding-top: 70px; max-width: 1100px; margin: 0 auto; padding-left: 24px; padding-right: 24px; }

        /* Breadcrumb: Danh sách > Sửa ... */
        .duong-dan {
            padding: 16px 0 12px;
            font-size: 13px; color: #666;
        }
        .duong-dan a { color: #4a90e2; }
        .duong-dan a:hover { text-decoration: underline; }

        /* Tiêu đề trang */
        .tieu-de-trang {
            font-size: 20px; font-weight: 600; color: #222;
            margin-bottom: 20px;
            display: flex; align-items: center; gap: 10px;
        }
        .tieu-de-trang .id-tag {
            font-size: 12px; background: #e0f0ff; color: #1565c0;
            padding: 3px 10px; border-radius: 50px; font-weight: 400;
        }

        /* Layout: form trái, bản đồ phải */
        .khu-vuc-sua {
            display: flex; gap: 20px; align-items: flex-start;
        }
        .cot-form { flex: 1; }
        .cot-ban-do { width: 420px; flex-shrink: 0; }

        /* Bản đồ — PHẢI có height cố định */
        #map-sua { width: 100%; height: 360px; border-radius: 12px; }

        /* Thẻ info bên cạnh bản đồ */
        .the-ban-do {
            background: white; border-radius: 12px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.1); overflow: hidden;
        }
        .the-ban-do-tieu-de {
            padding: 12px 16px; background: #f8f8f8;
            border-bottom: 1px solid #eee;
            font-size: 13px; font-weight: 600; color: #444;
            display: flex; justify-content: space-between; align-items: center;
        }
        .toa-do-hien-tai {
            font-size: 11px; font-family: monospace; color: #4ecdc4;
        }
        .goi-y-ban-do {
            padding: 8px 14px; background: #fffbea;
            border-bottom: 1px solid #f0e68c;
            font-size: 12px; color: #7a6a00;
        }

        /* Form */
        .the-form {
            background: white; border-radius: 12px; padding: 24px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.1);
        }
        .luoi-2-cot {
            display: grid; grid-template-columns: 1fr 1fr; gap: 14px;
        }
        .chiem-het { grid-column: 1 / -1; } /* Chiếm cả 2 cột */

        label { display: block; font-size: 12px; font-weight: 600; color: #555; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.5px; }
        .bat-buoc { color: red; }
        input[type=text], input[type=number], textarea, select {
            width: 100%; padding: 9px 12px;
            border: 1px solid #ddd; border-radius: 8px;
            font-size: 14px; font-family: 'Inter', sans-serif;
            outline: none; transition: border-color .2s;
        }
        input:focus, textarea:focus, select:focus { border-color: #4a90e2; box-shadow: 0 0 0 2px rgba(74,144,226,0.1); }
        textarea { height: 90px; resize: vertical; }

        /* Ô toạ độ */
        .hang-toa-do { display: flex; gap: 8px; }
        .hang-toa-do input { background: #f0fff8; color: #0a7a6a; font-family: monospace; }

        /* Gợi ý xanh */
        .goi-y {
            font-size: 11px; color: #666;
            background: #f0f7ff; border-left: 2px solid #4a90e2;
            padding: 6px 10px; border-radius: 4px; margin-top: 5px;
        }

        /* Khu vực nút */
        .nhom-nut-submit {
            display: flex; gap: 10px; margin-top: 20px; flex-wrap: wrap;
        }
        .btn-luu {
            padding: 11px 28px; background: #4a90e2; color: white;
            border: none; border-radius: 8px; font-size: 15px;
            font-weight: 600; cursor: pointer; font-family: 'Inter', sans-serif;
        }
        .btn-luu:hover { background: #357abd; }
        .btn-huy {
            padding: 11px 20px; background: white; color: #666;
            border: 1px solid #ddd; border-radius: 8px; font-size: 14px;
            cursor: pointer; text-decoration: none; display: inline-block;
        }
        .btn-huy:hover { background: #f5f5f5; }

        /* Thông báo */
        .tb-thanh-cong {
            padding: 12px 16px; border-radius: 8px; margin-bottom: 16px;
            background: #e6f9f0; color: #1a7a4a; border: 1px solid #9fe1cb;
            display: flex; align-items: center; gap: 8px;
        }
        .tb-loi {
            padding: 12px 16px; border-radius: 8px; margin-bottom: 16px;
            background: #fef2f2; color: #b91c1c; border: 1px solid #fca5a5;
        }
        .tb-loi ul { padding-left: 18px; margin-top: 6px; }

        /* Preview ảnh */
        .preview-anh {
            width: 100%; height: 120px; object-fit: cover;
            border-radius: 8px; margin-top: 8px;
            border: 1px solid #eee; display: none;
        }

        @media (max-width: 768px) {
            .khu-vuc-sua { flex-direction: column; }
            .cot-ban-do { width: 100%; }
            .luoi-2-cot { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="logo">⛰ GIA LAI <span>TOURISM</span></div>
        <ul class="menu">
            <li><a href="../index.php">Trang Chủ</a></li>
            <li><a href="destination.php">Quản Lý Địa Điểm</a></li>
        </ul>
        <div class="nav-user">
            <span class="user-name">👤 <?= htmlspecialchars($_SESSION['user_name']) ?></span>
            <form method="POST" action="../api/auth.php" style="display:inline">
                <input type="hidden" name="action" value="logout">
                <button type="submit" class="btn-logout">Đăng Xuất</button>
            </form>
        </div>
    </nav>

    <div class="trang-sua">

        <!-- Đường dẫn điều hướng -->
        <div class="duong-dan">
            <a href="destination.php">← Danh sách địa điểm</a>
            &nbsp;›&nbsp; Sửa: <strong><?= htmlspecialchars($dd['name']) ?></strong>
        </div>

        <!-- Tiêu đề -->
        <div class="tieu-de-trang">
            ✏️ Chỉnh sửa địa điểm
            <span class="id-tag">ID: <?= $dd['id'] ?></span>
        </div>

        <!-- Thông báo kết quả -->
        <?php if ($thongBao === 'success'): ?>
            <div class="tb-thanh-cong">
                ✅ <strong>Lưu thành công!</strong>&nbsp;
                <a href="destination.php" style="color:#1a7a4a">← Quay về danh sách</a>
            </div>
        <?php elseif ($thongBao === 'error'): ?>
            <div class="tb-loi">❌ Có lỗi xảy ra khi lưu, thử lại.</div>
        <?php endif; ?>

        <?php if (!empty($loiNhap)): ?>
            <div class="tb-loi">
                <strong>Vui lòng sửa các lỗi sau:</strong>
                <ul>
                    <?php foreach ($loiNhap as $loi): ?>
                        <li><?= htmlspecialchars($loi) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Layout 2 cột -->
        <div class="khu-vuc-sua">

            <!-- CỘT TRÁI: Form sửa -->
            <div class="cot-form">
                <div class="the-form">
                    <!-- action="" = gửi về chính trang này (edit.php?id=5) -->
                    <form method="POST" action="edit.php?id=<?= $id ?>">

                        <div class="luoi-2-cot">

                            <!-- Tên địa điểm -->
                            <div>
                                <label>Tên địa điểm <span class="bat-buoc">*</span></label>
                                <input type="text" name="name"
                                       value="<?= htmlspecialchars($dd['name']) ?>" required>
                            </div>

                            <!-- Slug -->
                            <div>
                                <label>Slug <span class="bat-buoc">*</span></label>
                                <input type="text" name="slug" id="inp-slug"
                                       value="<?= htmlspecialchars($dd['slug']) ?>" required>
                                <div class="goi-y">Dùng trong URL, không dấu, gạch ngang</div>
                            </div>

                            <!-- Loại -->
                            <div>
                                <label>Loại <span class="bat-buoc">*</span></label>
                                <select name="region" required>
                                    <option value="">— Chọn —</option>
                                    <option value="bien"   <?= $dd['region'] === 'bien'   ? 'selected' : '' ?>>🌊 Biển & Đảo</option>
                                    <option value="rung"   <?= $dd['region'] === 'rung'   ? 'selected' : '' ?>>🌿 Rừng & Núi</option>
                                    <option value="lichsu" <?= $dd['region'] === 'lichsu' ? 'selected' : '' ?>>🏛 Lịch Sử</option>
                                </select>
                            </div>

                            <!-- Rating -->
                            <div>
                                <label>Rating (1–5)</label>
                                <input type="number" name="rating"
                                       min="1" max="5" step="0.1"
                                       value="<?= $dd['rating'] ?>">
                            </div>

                            <!-- Địa chỉ -->
                            <div class="chiem-het">
                                <label>Địa chỉ</label>
                                <input type="text" name="location"
                                       value="<?= htmlspecialchars($dd['location'] ?? '') ?>"
                                       placeholder="VD: Xã Nhơn Lý, TP. Quy Nhơn">
                            </div>

                            <!-- URL ảnh + preview -->
                            <div class="chiem-het">
                                <label>URL ảnh</label>
                                <input type="text" name="image_url" id="inp-anh"
                                       value="<?= htmlspecialchars($dd['image_url'] ?? '') ?>"
                                       placeholder="https://...">
                                <!-- Preview ảnh tự hiện khi có URL -->
                                <img id="preview-anh" class="preview-anh"
                                     src="<?= htmlspecialchars($dd['image_url'] ?? '') ?>"
                                     alt="Preview">
                            </div>

                            <!-- Mô tả -->
                            <div class="chiem-het">
                                <label>Mô tả</label>
                                <textarea name="description"><?= htmlspecialchars($dd['description'] ?? '') ?></textarea>
                            </div>

                            <!-- Nổi bật -->
                            <div>
                                <label>Nổi bật trên trang chủ?</label>
                                <select name="featured">
                                    <option value="0" <?= $dd['featured'] == 0 ? 'selected' : '' ?>>Không</option>
                                    <option value="1" <?= $dd['featured'] == 1 ? 'selected' : '' ?>>Có</option>
                                </select>
                            </div>

                            <!-- Toạ độ GPS -->
                            <div class="chiem-het">
                                <label>Toạ độ GPS</label>
                                <div class="hang-toa-do">
                                    <input type="text" name="lat" id="inp-lat"
                                           value="<?= $dd['lat'] ?? '' ?>"
                                           placeholder="Vĩ độ (lat)" readonly>
                                    <input type="text" name="lng" id="inp-lng"
                                           value="<?= $dd['lng'] ?? '' ?>"
                                           placeholder="Kinh độ (lng)" readonly>
                                </div>
                                <div class="goi-y">🗺 Click vào bản đồ bên phải để cập nhật vị trí</div>
                            </div>

                        </div><!-- /luoi-2-cot -->

                        <!-- Nút lưu và huỷ -->
                        <div class="nhom-nut-submit">
                            <button type="submit" class="btn-luu">💾 Lưu thay đổi</button>
                            <a href="destination.php" class="btn-huy">✕ Huỷ, quay lại</a>
                        </div>

                    </form>
                </div>
            </div>

            <!-- CỘT PHẢI: Bản đồ chọn / cập nhật toạ độ -->
            <div class="cot-ban-do">
                <div class="the-ban-do">
                    <div class="the-ban-do-tieu-de">
                        📍 Vị trí trên bản đồ
                        <span class="toa-do-hien-tai" id="hienThiToadDo">
                            <?php if ($dd['lat'] && $dd['lng']): ?>
                                <?= number_format((float)$dd['lat'], 5) ?>, <?= number_format((float)$dd['lng'], 5) ?>
                            <?php else: ?>
                                Chưa có toạ độ
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="goi-y-ban-do">
                        💡 Click vào bản đồ để đặt vị trí mới — hoặc kéo marker đỏ
                    </div>
                    <!-- Bản đồ Leaflet -->
                    <div id="map-sua"></div>
                </div>
            </div>

        </div><!-- /khu-vuc-sua -->
    </div><!-- /trang-sua -->

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// ==================================================
// PHẦN 1: Bản đồ sửa toạ độ
// ==================================================

// Lấy toạ độ hiện có của địa điểm (từ PHP)
// PHP truyền giá trị vào JS bằng cách nhúng trực tiếp
var latHienTai = <?= $dd['lat'] ? (float)$dd['lat'] : 'null' ?>;
var lngHienTai = <?= $dd['lng'] ? (float)$dd['lng'] : 'null' ?>;

// Nếu có toạ độ thì zoom vào đó, không thì zoom về Gia Lai
var viTriBanDau = (latHienTai && lngHienTai)
    ? [latHienTai, lngHienTai]
    : [13.8079, 108.1094];
var mucZoom = (latHienTai && lngHienTai) ? 13 : 9;

// Tạo bản đồ
var banDo = L.map('map-sua').setView(viTriBanDau, mucZoom);
L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap'
}).addTo(banDo);

// Marker hiện tại (có thể kéo thả để tinh chỉnh)
var markerHienTai = null;
if (latHienTai && lngHienTai) {
    markerHienTai = L.marker([latHienTai, lngHienTai], { draggable: true })
        .addTo(banDo)
        .bindPopup('📍 ' + <?= json_encode($dd['name']) ?>)
        .openPopup();

    // Khi kéo marker → cập nhật toạ độ trong form
    markerHienTai.on('dragend', function() {
        var viTri = markerHienTai.getLatLng();
        capNhatToadDo(viTri.lat, viTri.lng);
    });
}

// Hàm cập nhật ô lat/lng và chữ hiển thị
function capNhatToadDo(lat, lng) {
    var la = parseFloat(lat).toFixed(7);
    var ln = parseFloat(lng).toFixed(7);
    document.getElementById('inp-lat').value = la;
    document.getElementById('inp-lng').value = ln;
    document.getElementById('hienThiToadDo').textContent = la + ', ' + ln;
}

// Khi click bản đồ → đặt/di chuyển marker
banDo.on('click', function(e) {
    capNhatToadDo(e.latlng.lat, e.latlng.lng);

    if (markerHienTai) {
        markerHienTai.setLatLng(e.latlng); // Di chuyển marker cũ
    } else {
        // Tạo marker mới có thể kéo
        markerHienTai = L.marker(e.latlng, { draggable: true }).addTo(banDo);
        markerHienTai.on('dragend', function() {
            var viTri = markerHienTai.getLatLng();
            capNhatToadDo(viTri.lat, viTri.lng);
        });
    }
});

// ==================================================
// PHẦN 2: Preview ảnh khi nhập URL
// ==================================================
var oNhapAnh = document.getElementById('inp-anh');
var anhPreview = document.getElementById('preview-anh');

// Hiện ảnh preview nếu đã có URL
if (oNhapAnh.value) {
    anhPreview.style.display = 'block';
}

// Cập nhật preview khi thay đổi URL ảnh
oNhapAnh.addEventListener('input', function() {
    if (this.value) {
        anhPreview.src = this.value;
        anhPreview.style.display = 'block';
        // Ẩn nếu ảnh lỗi
        anhPreview.onerror = function() { this.style.display = 'none'; };
    } else {
        anhPreview.style.display = 'none';
    }
});

// ==================================================
// PHẦN 3: Tự sinh slug từ tên (giống destination.php)
// ==================================================
var bangChuyenDau = {
    'à':'a','á':'a','ả':'a','ã':'a','ạ':'a',
    'ă':'a','ắ':'a','ằ':'a','ẵ':'a','ặ':'a','ẳ':'a',
    'â':'a','ấ':'a','ầ':'a','ẩ':'a','ẫ':'a','ậ':'a',
    'đ':'d','è':'e','é':'e','ẻ':'e','ẽ':'e','ẹ':'e',
    'ê':'e','ế':'e','ề':'e','ể':'e','ễ':'e','ệ':'e',
    'ì':'i','í':'i','ỉ':'i','ĩ':'i','ị':'i',
    'ò':'o','ó':'o','ỏ':'o','õ':'o','ọ':'o',
    'ô':'o','ố':'o','ồ':'o','ổ':'o','ỗ':'o','ộ':'o',
    'ơ':'o','ớ':'o','ờ':'o','ở':'o','ỡ':'o','ợ':'o',
    'ù':'u','ú':'u','ủ':'u','ũ':'u','ụ':'u',
    'ư':'u','ứ':'u','ừ':'u','ử':'u','ữ':'u','ự':'u',
    'ỳ':'y','ý':'y','ỷ':'y','ỹ':'y','ỵ':'y'
};

document.querySelector('input[name="name"]').addEventListener('input', function() {
    var ten = this.value.toLowerCase();
    var slug = ten.split('').map(function(c) {
        return bangChuyenDau[c] || c;
    }).join('')
    .replace(/[^a-z0-9\s-]/g, '').trim().replace(/\s+/g, '-');
    document.getElementById('inp-slug').value = slug;
});
</script>
</body>
</html>
