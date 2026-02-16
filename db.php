<?php
date_default_timezone_set('Asia/Bangkok');

$host = getenv('DB_HOST');
$user = getenv('DB_USER');
$pass = getenv('DB_PASSWORD');
$db   = getenv('DB_NAME');
$port = getenv('DB_PORT');

$conn = mysqli_init();

/* ⭐ ไม่ต้อง SSL */
mysqli_real_connect(
    $conn,
    $host,
    $user,
    $pass,
    $db,
    (int)$port
);

if (mysqli_connect_errno()) {
    die("DB connect failed: " . mysqli_connect_error());
}
