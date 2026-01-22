<?php
date_default_timezone_set('Asia/Bangkok');

$host = getenv('DB_HOST');
$user = getenv('DB_USER');
$pass = getenv('DB_PASSWORD');
$db   = getenv('DB_NAME');
$port = getenv('DB_PORT');

$mysqli = mysqli_init();

/* ใช้ SSL */
$mysqli->ssl_set(NULL, NULL, NULL, NULL, NULL);

$mysqli->real_connect(
    $host,
    $user,
    $pass,
    $db,
    (int)$port,
    null,
    MYSQLI_CLIENT_SSL
);

if ($mysqli->connect_errno) {
    die("DB connection failed");
}

