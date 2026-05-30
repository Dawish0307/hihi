// Đếm ngược đến Festival Biển Quy Nhơn 2026
function demNguoc() {
    var ngayDich = new Date('2026-06-28T08:00:00');
    var hienTai = new Date();
    var khoangCach = ngayDich - hienTai;

    if (khoangCach <= 0) return;

    var ngay = Math.floor(khoangCach / (1000 * 60 * 60 * 24));
    var gio  = Math.floor((khoangCach % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    var phut = Math.floor((khoangCach % (1000 * 60 * 60)) / (1000 * 60));
    var giay = Math.floor((khoangCach % (1000 * 60)) / 1000);

    // Thêm số 0 ở đầu nếu nhỏ hơn 10
    if (document.getElementById('cd-days'))  document.getElementById('cd-days').textContent  = ngay < 10  ? '0' + ngay  : ngay;
    if (document.getElementById('cd-hours')) document.getElementById('cd-hours').textContent = gio  < 10  ? '0' + gio   : gio;
    if (document.getElementById('cd-mins'))  document.getElementById('cd-mins').textContent  = phut < 10  ? '0' + phut  : phut;
    if (document.getElementById('cd-secs'))  document.getElementById('cd-secs').textContent  = giay < 10  ? '0' + giay  : giay;
}

// Chạy đếm ngược mỗi giây
setInterval(demNguoc, 1000);
demNguoc();

// Lọc sự kiện theo danh mục
function filterEvents(cat, btn) {
    // Bỏ active tất cả nút
    var buttons = document.querySelectorAll('.event-filters button');
    for (var i = 0; i < buttons.length; i++) {
        buttons[i].classList.remove('active');
    }
    // Thêm active cho nút được bấm
    btn.classList.add('active');

    // Hiện/ẩn card
    var cards = document.querySelectorAll('.event-card');
    for (var i = 0; i < cards.length; i++) {
        if (cat === 'all' || cards[i].getAttribute('data-cat') === cat) {
            cards[i].style.display = 'block';
        } else {
            cards[i].style.display = 'none';
        }
    }
}

// Hiện thông báo toast
function showToast(msg, type) {
    var toast = document.getElementById('toast');
    if (!toast) return;
    toast.textContent = msg;
    toast.className = 'toast show';
    if (type === 'error') toast.className = 'toast show error';
    setTimeout(function() {
        toast.className = 'toast';
    }, 3000);
}
