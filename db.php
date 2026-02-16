<?php

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host = "maglev.proxy.rlwy.net"; // ใส่ host จริงจาก Railway/Render
$user = "root";
$pass = "railway";
$db   = "railway";
$port = 30701;

$conn = mysqli_init();

mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);

mysqli_real_connect(
    $conn,
    $host,
    $user,
    $pass,
    $db,
    $port,
    NULL,
    MYSQLI_CLIENT_SSL
);

$conn->set_charset("utf8mb4");

?>
