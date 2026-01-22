<?php
date_default_timezone_set('Asia/Bangkok');

$host = getenv('DB_HOST');
$user = getenv('DB_USER');
$pass = getenv('DB_PASSWORD');
$db   = getenv('DB_NAME');
$port = getenv('DB_PORT');

$conn = mysqli_init();

/* ใช้ SSL (Railway / Render ต้องใช้) */
$conn->ssl_set(NULL, NULL, NULL, NULL, NULL);

$conn->real_connect(
    $host,
    $user,
    $pass,
    $db,
    (int)$port,
    NULL,
    MYSQLI_CLIENT_SSL
);

if ($conn->connect_errno) {
    die("❌ DB Connection failed: " . $conn->connect_error);
}

