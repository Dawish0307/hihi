<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'webhtdl');

function connectDB() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die(json_encode(['error' => 'Kết nối thất bại: ' . $conn->connect_error]));
    }
    $conn->set_charset("utf8mb4");
    return $conn;
}
?>
