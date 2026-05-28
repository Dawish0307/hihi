<?php
require_once '../config/database.php';

$id = intval($_POST['id'] ?? 0); // Lấy ID từ POST, mặc định là 0 nếu không có 
// intval() giúp đảm bảo rằng $id là một số nguyên, tránh lỗi SQL injection nếu có dữ liệu không hợp lệ
if ($id > 0) {
    $conn = connectDB();
    $stmt = $conn->prepare("DELETE FROM destinations WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}

header('Location: destination.php');
exit;
