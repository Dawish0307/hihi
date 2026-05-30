<?php
$pageTitle  = 'Chỉnh Sửa Bản Đồ';
$activePage = 'map';
include 'layout.php';

$msg = '';

// Lưu toạ độ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dest_id'])) {
    $id  = intval($_POST['dest_id']);
    $lat = floatval($_POST['lat']);
    $lng = floatval($_POST['lng']);
    $conn->query("UPDATE destinations SET lat=$lat, lng=$lng WHERE id=$id");
    echo '<script>window.savedMsg="Đã lưu toạ độ cho địa điểm #'.$id.'";</script>';
}

$dests = $conn->query("SELECT id, name, region, location, lat, lng FROM destinations ORDER BY name ASC");
$destArr = [];
while ($r = $dests->fetch_assoc()) $destArr[] = $r;
$conn->close();
?>

<p class="map-hint">Chọn địa điểm bên trái → Click trên bản đồ để đặt toạ độ → Nhấn <strong style="color:var(--accent)">Lưu</strong>.</p>

<div id="saveAlert" class="alert alert-success" style="display:none"></div>

<div class="map-editor-wrap">
    <!-- Danh sách -->
    <div class="map-dest-list" id="destList">
        <?php foreach ($destArr as $d): ?>
        <div class="map-dest-item" data-id="<?= $d['id'] ?>" data-lat="<?= $d['lat'] ?>" data-lng="<?= $d['lng'] ?>"
             data-name="<?= htmlspecialchars($d['name']) ?>"
             onclick="selectDest(this)">
            <strong><?= htmlspecialchars($d['name']) ?></strong>
            <span><?= htmlspecialchars($d['location']) ?></span>
            <span id="coord-<?= $d['id'] ?>" style="color:<?= $d['lat'] ? 'var(--accent)' : 'var(--danger)' ?>">
                <?= $d['lat'] ? '✓ '.number_format($d['lat'],4).', '.number_format($d['lng'],4) : '✗ Chưa có toạ độ' ?>
            </span>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Bản đồ -->
    <div id="adminMap"></div>
</div>

<!-- Form ẩn -->
<form method="POST" action="map.php" id="coordForm" style="display:none">
    <input type="hidden" name="dest_id" id="f_dest_id">
    <input type="hidden" name="lat"     id="f_lat">
    <input type="hidden" name="lng"     id="f_lng">
</form>

<div style="margin-top:14px;display:flex;gap:10px;align-items:center;">
    <div id="selectedInfo" style="font-size:13px;color:var(--muted)">Chưa chọn địa điểm nào</div>
    <button class="btn btn-primary" id="saveBtn" style="display:none" onclick="saveCoord()">💾 Lưu Toạ Độ</button>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<script>
var dests = <?= json_encode($destArr) ?>;
var map = L.map('adminMap').setView([13.76, 109.15], 9);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap'
}).addTo(map);

var selectedId  = null;
var selectedLat = null;
var selectedLng = null;
var tempMarker  = null;
var existingMarkers = {};

// Vẽ marker hiện có
dests.forEach(function(d) {
    if (d.lat && d.lng) {
        var m = L.circleMarker([parseFloat(d.lat), parseFloat(d.lng)], {
            radius: 7, color: '#3ecf8e', fillColor: '#3ecf8e', fillOpacity: 0.8, weight: 2
        }).addTo(map).bindTooltip(d.name);
        existingMarkers[d.id] = m;
    }
});

// Click bản đồ → đặt marker tạm
map.on('click', function(e) {
    if (!selectedId) return;
    selectedLat = e.latlng.lat.toFixed(7);
    selectedLng = e.latlng.lng.toFixed(7);
    if (tempMarker) map.removeLayer(tempMarker);
    tempMarker = L.marker([selectedLat, selectedLng]).addTo(map)
        .bindPopup('<b>' + document.querySelector('[data-id="'+selectedId+'"]').dataset.name + '</b><br>'+selectedLat+', '+selectedLng).openPopup();
    document.getElementById('selectedInfo').innerHTML =
        '<span style="color:var(--text)">Toạ độ mới: </span>' +
        '<span style="color:var(--accent);font-family:monospace">'+selectedLat+', '+selectedLng+'</span>';
    document.getElementById('saveBtn').style.display = 'inline-flex';
});

function selectDest(el) {
    document.querySelectorAll('.map-dest-item').forEach(function(x){ x.classList.remove('selected'); });
    el.classList.add('selected');
    selectedId  = el.dataset.id;
    selectedLat = null;
    selectedLng = null;
    if (tempMarker) { map.removeLayer(tempMarker); tempMarker = null; }
    document.getElementById('saveBtn').style.display = 'none';
    document.getElementById('selectedInfo').innerHTML =
        'Đang chỉnh: <strong style="color:var(--text)">' + el.dataset.name + '</strong> — Click bản đồ để đặt vị trí';

    var lat = parseFloat(el.dataset.lat), lng = parseFloat(el.dataset.lng);
    if (lat && lng) map.setView([lat, lng], 13);
}

function saveCoord() {
    if (!selectedId || !selectedLat) return;
    document.getElementById('f_dest_id').value = selectedId;
    document.getElementById('f_lat').value     = selectedLat;
    document.getElementById('f_lng').value     = selectedLng;

    // Cập nhật UI
    var coordEl = document.getElementById('coord-' + selectedId);
    if (coordEl) {
        coordEl.style.color = 'var(--accent)';
        coordEl.textContent = '✓ ' + parseFloat(selectedLat).toFixed(4) + ', ' + parseFloat(selectedLng).toFixed(4);
    }
    if (existingMarkers[selectedId]) map.removeLayer(existingMarkers[selectedId]);
    existingMarkers[selectedId] = L.circleMarker([selectedLat, selectedLng], {
        radius: 7, color: '#3ecf8e', fillColor: '#3ecf8e', fillOpacity: 0.8, weight: 2
    }).addTo(map).bindTooltip(document.querySelector('[data-id="'+selectedId+'"]').dataset.name);
    if (tempMarker) { map.removeLayer(tempMarker); tempMarker = null; }

    document.getElementById('coordForm').submit();
}

// Hiện thông báo sau khi lưu
if (typeof savedMsg !== 'undefined') {
    var al = document.getElementById('saveAlert');
    al.textContent = '✓ ' + savedMsg;
    al.style.display = 'block';
    setTimeout(function(){ al.style.display='none'; }, 3000);
}
</script>

<?php include 'layout_end.php'; ?>
