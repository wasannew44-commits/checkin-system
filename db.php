<?php
date_default_timezone_set('Asia/Bangkok');

$host = getenv('DB_HOST');
$user = getenv('DB_USER');
$pass = getenv('DB_PASSWORD');
$db   = getenv('DB_NAME');
$port = getenv('DB_PORT');

$conn = new mysqli(
    $host,
    $user,
    $pass,
    $db,
    (int)$port
);

if ($conn->connect_error) {
    die("DB connect failed: " . $conn->connect_error);
}
