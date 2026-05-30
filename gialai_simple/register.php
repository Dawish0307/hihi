<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'config/database.php';

    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $password  = $_POST['password'] ?? '';
    $confirm   = $_POST['confirm_password'] ?? '';

    if (empty($full_name) || empty($email) || empty($password)) {
        $error = 'Vui lòng điền đầy đủ thông tin bắt buộc.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không hợp lệ.';
    } elseif (strlen($password) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 ký tự.';
    } elseif ($password !== $confirm) {
        $error = 'Mật khẩu xác nhận không khớp.';
    } else {
        $conn = connectDB();
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();

        if ($check->get_result()->num_rows > 0) {
            $error = 'Email này đã được đăng ký.';
        } else {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, phone) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $full_name, $email, $hashed, $phone);

            if ($stmt->execute()) {
                header('Location: login.php?registered=1');
                exit;
            } else {
                $error = 'Đăng ký thất bại, vui lòng thử lại.';
            }
        }
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng Ký - Du Lịch Gia Lai</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/auth.css">
</head>
<body>

<nav class="navbar">
    <div class="logo">⛰ GIA LAI <span>TOURISM</span></div>
    <ul class="menu">
        <li><a href="index.php">Trang Chủ</a></li>
        <li><a href="about.php">Giới Thiệu</a></li>
        <li><a href="destinations.php">Điểm Đến</a></li>
        <li><a href="contact.php">Liên Hệ</a></li>
    </ul>
    <div class="nav-user">
        <a href="login.php">Đăng Nhập</a>
        <a href="register.php" class="btn-register">Đăng Ký</a>
    </div>
</nav>

<div class="auth-page">
    <div class="auth-box">
        <h2>Tạo Tài Khoản</h2>
        <p class="auth-sub">Tham gia cùng cộng đồng du lịch Gia Lai</p>

        <?php if ($error): ?>
            <div class="msg-box error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="register.php">
            <div class="form-group">
                <label>Họ và Tên</label>
                <input type="text" name="full_name" placeholder="Nguyễn Văn A"
                       value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="email@example.com"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Số điện thoại</label>
                <input type="tel" name="phone" placeholder="0912 345 678"
                       value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Mật khẩu</label>
                <input type="password" name="password" placeholder="Tối thiểu 6 ký tự" required>
            </div>
            <div class="form-group">
                <label>Xác nhận mật khẩu</label>
                <input type="password" name="confirm_password" placeholder="Nhập lại mật khẩu" required>
            </div>
            <button type="submit" class="btn-submit">Đăng Ký</button>
        </form>

        <p class="auth-link">Đã có tài khoản? <a href="login.php">Đăng nhập</a></p>
    </div>
</div>

</body>
</html>