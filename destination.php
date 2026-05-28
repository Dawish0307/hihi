<?php
// ==================================================
// admin/destination.php — Trang quản lý địa điểm
// Chức năng: Xem danh sách, Thêm mới, Xoá
// Trang Sửa nằm ở file edit.php riêng
// ==================================================
session_start();

// Kiểm tra quyền admin — chỉ admin mới vào được
// Nếu không phải admin thì chuyển về trang chủ
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
  header('Location: ../index.php');
  exit;
}

require_once '../config/database.php';
$conn = connectDB();

$thongBao = ''; // Lưu kết quả: 'success' hoặc 'error'
$loiNhap  = []; // Mảng lưu danh sách lỗi nhập liệu

// ==================================================
// XỬ LÝ KHI ADMIN BẤM NÚT "THÊM ĐỊA ĐIỂM"
// $_SERVER['REQUEST_METHOD'] === 'POST' nghĩa là
// form đã được gửi (bấm nút submit)
// ==================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // Lấy dữ liệu từ form, trim() bỏ khoảng trắng thừa
  $name        = trim($_POST['name']        ?? '');
  $slug        = trim($_POST['slug']        ?? '');
  $region      = trim($_POST['region']      ?? '');
  $location    = trim($_POST['location']    ?? '');
  $description = trim($_POST['description'] ?? '');
  $image_url   = trim($_POST['image_url']   ?? '');
  $rating      = floatval($_POST['rating']  ?: 4.5); // floatval chuyển sang số thực
  $lat         = floatval($_POST['lat']     ?? 0);
  $lng         = floatval($_POST['lng']     ?? 0);
  $featured    = intval($_POST['featured']  ?? 0);   // intval chuyển sang số nguyên

  // --- Kiểm tra dữ liệu bắt buộc ---
  if (empty($name))   $loiNhap[] = 'Tên địa điểm không được để trống!';
  if (empty($slug))   $loiNhap[] = 'Slug không được để trống!';
  if (empty($region)) $loiNhap[] = 'Vui lòng chọn loại địa điểm!';
  if ($lat == 0)      $loiNhap[] = 'Chưa chọn vị trí trên bản đồ!';

  // Kiểm tra slug có bị trùng không
  // (slug là tên rút gọn, phải duy nhất — VD: "eo-gio-ky-co")
  if (empty($loiNhap)) {
    $chk = $conn->prepare("SELECT id FROM destinations WHERE slug = ?");
    $chk->bind_param("s", $slug); // "s" = kiểu string
    $chk->execute();
    if ($chk->get_result()->num_rows > 0) {
      $loiNhap[] = 'Slug "' . $slug . '" đã tồn tại! Hãy đổi tên khác.';
    }
  }

  // Nếu không có lỗi → lưu vào database
  if (empty($loiNhap)) {
    $stmt = $conn->prepare(
      "INSERT INTO destinations
             (name, slug, region, location, description, image_url, rating, lat, lng, featured)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    // "sssssssddi": s=string, d=decimal(số thực), i=integer(số nguyên)
    $stmt->bind_param(
      "sssssssddi",
      $name,
      $slug,
      $region,
      $location,
      $description,
      $image_url,
      $rating,
      $lat,
      $lng,
      $featured
    );

    if ($stmt->execute()) {
      $thongBao = 'success'; // Thành công
    } else {
      $thongBao = 'error';   // Thất bại
    }
    $stmt->close();
  }
}

// Lấy toàn bộ địa điểm để hiển thị trong bảng
$danhSach = $conn->query("SELECT * FROM destinations ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>
<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin — Quản Lý Địa Điểm</title>

  <!-- CSS Leaflet (thư viện bản đồ) -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/admin_destinations.css">

  <style>
    /* Đẩy nội dung xuống dưới navbar cố định */
    .trang-admin {
      padding-top: 70px;
    }

    /* Khu vực form + bản đồ nằm cạnh nhau */
    .khu-vuc-them {
      display: flex;
      gap: 20px;
      padding: 24px 30px;
      background: #f5f5f5;
    }

    /* Cột trái: form nhập liệu */
    .cot-form {
      width: 380px;
      flex-shrink: 0;
    }

    /* Cột phải: bản đồ chọn toạ độ */
    .cot-ban-do {
      flex: 1;
    }

    /* Bản đồ Leaflet — PHẢI có chiều cao cố định */
    #map-chon {
      width: 100%;
      height: 420px;
      border-radius: 12px;
    }

    /* Thẻ chứa form */
    .the-form {
      background: white;
      border-radius: 12px;
      padding: 20px;
      box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
    }

    .the-form h2 {
      font-size: 16px;
      color: #333;
      margin-bottom: 16px;
    }

    /* Nhãn và ô nhập */
    label {
      display: block;
      font-size: 13px;
      color: #555;
      margin: 10px 0 4px;
    }

    input[type=text],
    input[type=number],
    textarea,
    select {
      width: 100%;
      padding: 9px 12px;
      border: 1px solid #ddd;
      border-radius: 8px;
      font-size: 14px;
      font-family: 'Inter', sans-serif;
      outline: none;
      transition: border-color .2s;
    }

    input:focus,
    textarea:focus,
    select:focus {
      border-color: #4a90e2;
    }

    textarea {
      height: 80px;
      resize: vertical;
    }

    /* Hàng toạ độ: lat và lng nằm cạnh nhau */
    .hang-toa-do {
      display: flex;
      gap: 8px;
    }

    .hang-toa-do input {
      background: #f9f9f9;
    }

    /* Gợi ý nhỏ màu xanh */
    .goi-y {
      font-size: 12px;
      color: #666;
      background: #f0f7ff;
      border-left: 3px solid #4a90e2;
      padding: 8px 10px;
      border-radius: 6px;
      margin-top: 6px;
    }

    /* Nút thêm */
    .btn-them {
      width: 100%;
      margin-top: 14px;
      padding: 11px;
      background: #4a90e2;
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 15px;
      cursor: pointer;
      font-family: 'Inter', sans-serif;
    }

    .btn-them:hover {
      background: #357abd;
    }

    /* Thông báo thành công / lỗi */
    .tb-thanh-cong {
      padding: 10px 14px;
      border-radius: 8px;
      margin-bottom: 12px;
      background: #e6f9f0;
      color: #1a7a4a;
      border: 1px solid #9fe1cb;
    }

    .tb-loi {
      padding: 10px 14px;
      border-radius: 8px;
      margin-bottom: 12px;
      background: #fef2f2;
      color: #b91c1c;
      border: 1px solid #fca5a5;
    }

    .tb-loi ul {
      padding-left: 16px;
      margin-top: 4px;
    }

    /* Khu vực bảng danh sách */
    .khu-vuc-bang {
      padding: 0 30px 30px;
    }

    /* Thanh lọc + tìm kiếm */
    .toolbar {
      display: flex;
      align-items: center;
      gap: 10px;
      background: #9dbcee;
      border-radius: 12px;
      padding: 10px 20px;
      margin-bottom: 12px;
      flex-wrap: wrap;
    }

    .o-tim {
      flex: 1;
      min-width: 180px;
      padding: 8px 14px;
      border: none;
      border-radius: 50px;
      background: rgba(255, 255, 255, 0.8);
      font-size: 13px;
      outline: none;
    }

    .tag {
      padding: 6px 14px;
      border-radius: 50px;
      cursor: pointer;
      border: 1px solid rgba(0, 0, 0, 0.2);
      background: transparent;
      color: #1a1a1a;
      font-size: 12px;
      font-family: 'Inter', sans-serif;
      transition: all .2s;
    }

    .tag:hover {
      background: rgba(78, 205, 196, 0.2);
      border-color: #4ecdc4;
    }

    .tag.active {
      background: #4ecdc4;
      color: white;
      border-color: #4ecdc4;
    }

    .so-dem {
      font-size: 12px;
      color: #333;
      margin-left: auto;
    }

    .so-dem b {
      color: #0a4a8a;
    }

    /* Bảng */
    .bang-wrapper {
      background: white;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    thead th {
      background: #f0f0f0;
      padding: 10px 14px;
      font-size: 12px;
      text-align: left;
      color: #555;
      border-bottom: 1px solid #e0e0e0;
    }

    tbody td {
      padding: 10px 14px;
      font-size: 13px;
      border-bottom: 1px solid #f0f0f0;
      vertical-align: middle;
    }

    tbody tr:last-child td {
      border-bottom: none;
    }

    tbody tr:hover td {
      background: #fafafa;
    }

    /* Ảnh nhỏ trong bảng */
    .anh-nho {
      width: 56px;
      height: 40px;
      object-fit: cover;
      border-radius: 6px;
      background: #eee;
      display: block;
    }

    /* Badge loại */
    .badge {
      padding: 3px 10px;
      border-radius: 50px;
      font-size: 11px;
      font-weight: 700;
    }

    .badge-bien {
      background: #dbeafe;
      color: #1e40af;
    }

    .badge-rung {
      background: #dcfce7;
      color: #166534;
    }

    .badge-lichsu {
      background: #fef9c3;
      color: #854d0e;
    }

    /* Nút sửa / xoá */
    .nhom-nut {
      display: flex;
      gap: 6px;
    }

    .btn-sua {
      padding: 5px 12px;
      background: #e0f0ff;
      color: #1565c0;
      border: 1px solid #90caf9;
      border-radius: 6px;
      font-size: 12px;
      cursor: pointer;
      text-decoration: none;
      display: inline-block;
    }

    .btn-sua:hover {
      background: #bbdefb;
    }

    .btn-xoa {
      padding: 5px 12px;
      background: #fee2e2;
      color: #b91c1c;
      border: 1px solid #fca5a5;
      border-radius: 6px;
      font-size: 12px;
      cursor: pointer;
    }

    .btn-xoa:hover {
      background: #fca5a5;
    }

    /* Toạ độ */
    .toa-do {
      font-size: 11px;
      color: #888;
      font-family: monospace;
    }

    .toa-do.co-toa-do {
      color: #0a7a6a;
    }

    @media (max-width: 768px) {
      .khu-vuc-them {
        flex-direction: column;
      }

      .cot-form {
        width: 100%;
      }
    }
  </style>
</head>

<body>
  <div class="trang-admin">

    <!-- NAVBAR (dùng chung style.css) -->
    <nav class="navbar">
      <div class="logo">⛰ GIA LAI <span>TOURISM</span></div>
      <ul class="menu">
        <li><a href="../index.php">Trang Chủ</a></li>
        <li><a href="destination.php" class="active">Quản Lý Địa Điểm</a></li>
      </ul>
      <div class="nav-user">
        <span class="user-name">👤 <?= htmlspecialchars($_SESSION['user_name']) ?></span>
        <!-- Logout dùng form POST vì auth.php đọc $_POST -->
        <form method="POST" action="../api/auth.php" style="display:inline">
          <input type="hidden" name="action" value="logout">
          <button type="submit" class="btn-logout">Đăng Xuất</button>
        </form>
      </div>
    </nav>

    <!-- ============================================
         KHU VỰC THÊM MỚI: Form bên trái, Bản đồ bên phải
    ============================================ -->
    <div class="khu-vuc-them">

      <!-- CỘT TRÁI: Form nhập thông tin địa điểm mới -->
      <div class="cot-form">

        <!-- Hiện thông báo kết quả (nếu có) -->
        <?php if ($thongBao === 'success'): ?>
          <div class="tb-thanh-cong">✅ Thêm địa điểm thành công!</div>
        <?php elseif ($thongBao === 'error'): ?>
          <div class="tb-loi">❌ Có lỗi xảy ra, thử lại.</div>
        <?php endif; ?>

        <!-- Hiện danh sách lỗi nhập liệu (nếu có) -->
        <?php if (!empty($loiNhap)): ?>
          <div class="tb-loi">
            <ul>
              <?php foreach ($loiNhap as $loi): ?>
                <li><?= htmlspecialchars($loi) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <div class="the-form">
          <h2>➕ Thêm địa điểm mới</h2>

          <!-- action="" = gửi về chính trang này, method="POST" -->
          <form method="POST" action="destination.php">

            <label>Tên địa điểm <span style="color:red">*</span></label>
            <input type="text" name="name"
              value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
              placeholder="VD: Eo Gió - Kỳ Co" required>

            <!-- Slug: tên rút gọn không dấu, dùng trong URL -->
            <label>Slug <span style="color:red">*</span></label>
            <input type="text" name="slug" id="inp-slug"
              value="<?= htmlspecialchars($_POST['slug'] ?? '') ?>"
              placeholder="eo-gio-ky-co" required>
            <div class="goi-y">💡 Tự động tạo từ tên — hoặc tự nhập</div>

            <label>Loại <span style="color:red">*</span></label>
            <select name="region" required>
              <option value="">— Chọn loại —</option>
              <option value="bien" <?= ($_POST['region'] ?? '') === 'bien'   ? 'selected' : '' ?>>🌊 Biển & Đảo</option>
              <option value="rung" <?= ($_POST['region'] ?? '') === 'rung'   ? 'selected' : '' ?>>🌿 Rừng & Núi</option>
              <option value="lichsu" <?= ($_POST['region'] ?? '') === 'lichsu' ? 'selected' : '' ?>>🏛 Lịch Sử</option>
            </select>

            <label>Địa chỉ</label>
            <input type="text" name="location"
              value="<?= htmlspecialchars($_POST['location'] ?? '') ?>"
              placeholder="VD: Xã Nhơn Lý, Quy Nhơn">

            <label>URL ảnh</label>
            <input type="text" name="image_url"
              value="<?= htmlspecialchars($_POST['image_url'] ?? '') ?>"
              placeholder="https://...">

            <label>Mô tả ngắn</label>
            <textarea name="description"
              placeholder="Giới thiệu về địa điểm..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>

            <label>Rating (1–5)</label>
            <input type="number" name="rating" min="1" max="5" step="0.1"
              value="<?= $_POST['rating'] ?? '4.5' ?>">

            <label>Nổi bật trên trang chủ?</label>
            <select name="featured">
              <option value="0">Không</option>
              <option value="1">Có</option>
            </select>

            <!-- Toạ độ — tự điền khi click bản đồ bên phải -->
            <label>Toạ độ GPS <span style="color:red">*</span></label>
            <div class="hang-toa-do">
              <input type="text" name="lat" id="inp-lat"
                value="<?= $_POST['lat'] ?? '' ?>"
                placeholder="Vĩ độ (lat)" readonly>
              <input type="text" name="lng" id="inp-lng"
                value="<?= $_POST['lng'] ?? '' ?>"
                placeholder="Kinh độ (lng)" readonly>
            </div>
            <div class="goi-y">🗺 Click vào bản đồ bên phải để tự điền toạ độ</div>

            <button type="submit" class="btn-them">➕ Thêm địa điểm</button>
          </form>
        </div>
      </div>

      <!-- CỘT PHẢI: Bản đồ để click chọn toạ độ -->
      <div class="cot-ban-do">
        <div id="map-chon"></div>
      </div>
    </div>

    <!-- ============================================
         BẢNG DANH SÁCH ĐỊA ĐIỂM
    ============================================ -->
    <div class="khu-vuc-bang">

      <!-- Thanh tìm kiếm + lọc loại -->
      <div class="toolbar">
        <strong style="color:#333; white-space:nowrap">📋 Danh sách địa điểm</strong>
        <input type="text" id="oTimKiem" class="o-tim" placeholder="🔍 Tìm tên địa điểm...">

        <!-- Các nút lọc — data-r khớp với cột "region" trong database -->
        <div style="display:flex; gap:6px; flex-wrap:wrap">
          <button class="tag active" data-r="all">🌏 Tất cả</button>
          <button class="tag" data-r="bien">🌊 Biển & Đảo</button>
          <button class="tag" data-r="rung">🌿 Rừng & Núi</button>
          <button class="tag" data-r="lichsu">🏛 Lịch Sử</button>
        </div>

        <!-- PHP đếm tổng, JS sẽ cập nhật khi lọc -->
        <span class="so-dem" id="soDem"><b><?= count($danhSach) ?></b> địa điểm</span>
      </div>

      <!-- Bảng -->
      <div class="bang-wrapper">
        <table id="bangDiaDiem">
          <thead>
            <tr>
              <th>Ảnh</th>
              <th>Tên địa điểm</th>
              <th>Loại</th>
              <th>Địa chỉ</th>
              <th>Toạ độ</th>
              <th>Rating</th>
              <th>Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($danhSach as $dd): ?>
              <!-- Lưu dữ liệu vào data-* để JS lọc không cần reload -->
              <tr data-ten="<?= htmlspecialchars($dd['name'], ENT_QUOTES) ?>"
                data-loai="<?= $dd['region'] ?>">

                <!-- Ảnh -->
                <td>
                  <?php if ($dd['image_url']): ?>
                    <img class="anh-nho"
                      src="<?= htmlspecialchars($dd['image_url']) ?>"
                      alt="<?= htmlspecialchars($dd['name']) ?>"
                      onerror="this.style.opacity=0.2">
                  <?php else: ?>
                    <div class="anh-nho" style="display:flex;align-items:center;justify-content:center;color:#aaa">📷</div>
                  <?php endif; ?>
                </td>

                <!-- Tên -->
                <td><strong><?= htmlspecialchars($dd['name']) ?></strong></td>

                <!-- Loại (badge màu) -->
                <td>
                  <span class="badge badge-<?= $dd['region'] ?>">
                    <?= ['bien' => 'Biển', 'rung' => 'Rừng', 'lichsu' => 'Lịch sử'][$dd['region']] ?? $dd['region'] ?>
                  </span>
                </td>

                <!-- Địa chỉ -->
                <td><?= htmlspecialchars($dd['location'] ?? '—') ?></td>

                <!-- Toạ độ -->
                <td>
                  <?php if ($dd['lat'] && $dd['lng']): ?>
                    <div class="toa-do co-toa-do">
                      <?= number_format((float)$dd['lat'], 5) ?><br>
                      <?= number_format((float)$dd['lng'], 5) ?>
                    </div>
                  <?php else: ?>
                    <span class="toa-do">Chưa có</span>
                  <?php endif; ?>
                </td>

                <!-- Rating -->
                <td>⭐ <?= $dd['rating'] ?></td>

                <!-- Nút thao tác -->
                <td>
                  <div class="nhom-nut">
                    <!-- Nút SỬA: chuyển sang edit.php và truyền id qua URL -->
                    <!-- ?id=5 nghĩa là truyền biến id=5 vào URL để edit.php đọc -->
                    <a href="edit.php?id=<?= $dd['id'] ?>" class="btn-sua">✏️ Sửa</a>

                    <!-- Nút XOÁ: gửi form POST đến xoa.php -->
                    <form method="POST" action="xoa.php"
                      onsubmit="return confirm('Xoá địa điểm <?= addslashes($dd['name']) ?>?')">
                      <input type="hidden" name="id" value="<?= $dd['id'] ?>">
                      <button type="submit" class="btn-xoa">🗑️ Xoá</button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div><!-- /trang-admin -->

  <!-- JS Leaflet cho bản đồ -->
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script>
    // ==================================================
    // PHẦN 1: Bản đồ chọn toạ độ
    // ==================================================

    // Tạo bản đồ, đặt trung tâm tại Gia Lai, zoom mức 9
    var banDo = L.map('map-chon').setView([13.8079, 108.1094], 9);

    // Thêm nền bản đồ từ OpenStreetMap (miễn phí)
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© OpenStreetMap'
    }).addTo(banDo);

    // Hiển thị marker cho các địa điểm đã có trong database
    // PHP xuất mảng $danhSach ra JS (đây là trường hợp đặc biệt cần dùng json_encode
    // vì không có cách nào khác truyền mảng PHP sang JS mà không dùng JSON)
    var dsDiaDiem = <?= json_encode($danhSach, JSON_UNESCAPED_UNICODE) ?>;
    dsDiaDiem.forEach(function(dd) {
      if (dd.lat && dd.lng) {
        L.marker([dd.lat, dd.lng])
          .addTo(banDo)
          .bindPopup('<b>' + dd.name + '</b>'); // Popup khi click marker
      }
    });

    // Marker đang chọn (khi admin click để thêm địa điểm mới)
    var markerDangChon = null;

    // Khi admin click vào bản đồ → đặt marker và điền toạ độ vào form
    banDo.on('click', function(e) {
      var lat = e.latlng.lat.toFixed(7); // Làm tròn 7 chữ số thập phân
      var lng = e.latlng.lng.toFixed(7);

      // Điền vào ô input trong form
      document.getElementById('inp-lat').value = lat;
      document.getElementById('inp-lng').value = lng;

      // Xoá marker cũ rồi vẽ marker mới
      if (markerDangChon) banDo.removeLayer(markerDangChon);
      markerDangChon = L.marker([lat, lng], {
        // divIcon: tạo marker hình tròn đỏ tuỳ chỉnh
        icon: L.divIcon({
          className: '',
          html: '<div style="width:14px;height:14px;background:#e74c3c;border:2px solid #fff;border-radius:50%;box-shadow:0 2px 4px rgba(0,0,0,0.3)"></div>',
          iconAnchor: [7, 7] // Điểm neo của icon (căn giữa)
        })
      }).addTo(banDo).bindPopup('📍 Vị trí đã chọn').openPopup();
    });

    // ==================================================
    // PHẦN 2: Tự sinh slug từ tên địa điểm
    // ==================================================

    // Bảng chuyển chữ có dấu → không dấu tiếng Việt
    var bangChuyenDau = {
      'à': 'a',
      'á': 'a',
      'ả': 'a',
      'ã': 'a',
      'ạ': 'a',
      'ă': 'a',
      'ắ': 'a',
      'ằ': 'a',
      'ẵ': 'a',
      'ặ': 'a',
      'ẳ': 'a',
      'â': 'a',
      'ấ': 'a',
      'ầ': 'a',
      'ẩ': 'a',
      'ẫ': 'a',
      'ậ': 'a',
      'đ': 'd',
      'è': 'e',
      'é': 'e',
      'ẻ': 'e',
      'ẽ': 'e',
      'ẹ': 'e',
      'ê': 'e',
      'ế': 'e',
      'ề': 'e',
      'ể': 'e',
      'ễ': 'e',
      'ệ': 'e',
      'ì': 'i',
      'í': 'i',
      'ỉ': 'i',
      'ĩ': 'i',
      'ị': 'i',
      'ò': 'o',
      'ó': 'o',
      'ỏ': 'o',
      'õ': 'o',
      'ọ': 'o',
      'ô': 'o',
      'ố': 'o',
      'ồ': 'o',
      'ổ': 'o',
      'ỗ': 'o',
      'ộ': 'o',
      'ơ': 'o',
      'ớ': 'o',
      'ờ': 'o',
      'ở': 'o',
      'ỡ': 'o',
      'ợ': 'o',
      'ù': 'u',
      'ú': 'u',
      'ủ': 'u',
      'ũ': 'u',
      'ụ': 'u',
      'ư': 'u',
      'ứ': 'u',
      'ừ': 'u',
      'ử': 'u',
      'ữ': 'u',
      'ự': 'u',
      'ỳ': 'y',
      'ý': 'y',
      'ỷ': 'y',
      'ỹ': 'y',
      'ỵ': 'y'
    };

    // Lắng nghe khi gõ vào ô "Tên địa điểm"
    document.querySelector('input[name="name"]').addEventListener('input', function() {
      var ten = this.value.toLowerCase(); // Chuyển thành chữ thường
      // Duyệt từng ký tự, nếu có trong bảng thì thay thế
      var slug = ten.split('').map(function(c) {
          return bangChuyenDau[c] || c;
        }).join('')
        .replace(/[^a-z0-9\s-]/g, '') // Xoá ký tự đặc biệt
        .trim()
        .replace(/\s+/g, '-'); // Khoảng trắng → gạch ngang
      document.getElementById('inp-slug').value = slug;
    });

    // ==================================================
    // PHẦN 3: Lọc bảng (không cần reload trang)
    // ==================================================
    var boLocHienTai = 'all'; // Đang lọc loại nào

    // Hàm lọc — chạy mỗi khi thay đổi tag hoặc từ khoá
    function locBang() {
      var tuKhoa = document.getElementById('oTimKiem').value.toLowerCase();
      var demHien = 0;

      // Duyệt từng hàng trong bảng
      document.querySelectorAll('#bangDiaDiem tbody tr').forEach(function(hang) {
        var ten = hang.dataset.ten.toLowerCase();
        var loai = hang.dataset.loai;

        // Kiểm tra 2 điều kiện:
        // 1. Loại có khớp không? ('all' = hiện tất cả)
        // 2. Tên có chứa từ khoá không?
        var hopLoai = (boLocHienTai === 'all') || (loai === boLocHienTai);
        var hopTuKhoa = !tuKhoa || ten.includes(tuKhoa);

        if (hopLoai && hopTuKhoa) {
          hang.style.display = ''; // Hiện hàng
          demHien++;
        } else {
          hang.style.display = 'none'; // Ẩn hàng
        }
      });

      // Cập nhật số đếm
      var tongTatCa = <?= count($danhSach) ?>;
      if (demHien === tongTatCa) {
        document.getElementById('soDem').innerHTML = '<b>' + tongTatCa + '</b> địa điểm';
      } else {
        document.getElementById('soDem').innerHTML = '<b>' + demHien + '</b> / ' + tongTatCa + ' địa điểm';
      }
    }

    // Khi click vào nút tag lọc
    document.querySelectorAll('.tag').forEach(function(nut) {
      nut.addEventListener('click', function() {
        // Bỏ active tất cả, thêm vào nút vừa click
        document.querySelectorAll('.tag').forEach(function(n) {
          n.classList.remove('active');
        });
        this.classList.add('active');
        boLocHienTai = this.dataset.r; // Lấy giá trị data-r
        locBang();
      });
    });

    // Khi gõ vào ô tìm kiếm
    document.getElementById('oTimKiem').addEventListener('input', locBang);
  </script>
</body>

</html>