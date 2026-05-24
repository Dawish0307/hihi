<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';
if (isset($_GET['registered'])) {
    $success = 'Đăng ký thành công! Vui lòng đăng nhập.';
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'config/database.php';
    
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Vui lòng nhập email và mật khẩu.';
    } else {
        $conn = connectDB();
        $stmt = $conn->prepare("SELECT id, full_name, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $conn->close();

        if ($row && password_verify($password, $row['password'])) {
            $_SESSION['user_id']   = $row['id'];
            $_SESSION['user_name'] = $row['full_name'];
            $_SESSION['msg']       = 'Chào mừng ' . $row['full_name'] . ' đã đăng nhập!';
            header('Location: index.php');
            exit;
        } else {
            $error = 'Email hoặc mật khẩu không đúng.';
            
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng Nhập - Du Lịch Gia Lai</title>
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
        <h2>Đăng Nhập</h2>
        <p class="auth-sub">Chào mừng bạn trở lại!</p>

        <?php if ($error): ?>
            <div class="msg-box error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="msg-box success"><?= $success ?></div>
        <?php endif; ?>
        <form method="POST" action="login.php">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="Nhập email của bạn" 
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Mật khẩu</label>
                <input type="password" name="password" placeholder="Nhập mật khẩu" required>
            </div>
            <button type="submit" class="btn-submit">Đăng Nhập</button>
        </form>

        <p class="auth-link">Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a></p>
    </div>
</div>

</body>
</html>