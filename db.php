<?php
$host = getenv('DB_HOST');
$user = getenv('DB_USER');
$pass = getenv('DB_PASSWORD');
$db   = getenv('DB_NAME');
$port = getenv('DB_PORT');

$mysqli = mysqli_init();

/* บังคับใช้ SSL */
$mysqli->ssl_set(NULL, NULL, NULL, NULL, NULL);

$mysqli->real_connect(
    $host,
    $user,
    $pass,
    $db,
    $port,
    NULL,
    MYSQLI_CLIENT_SSL
);

if ($mysqli->connect_errno) {
    die("❌ DB Connection failed: " . $mysqli->connect_error);
}
