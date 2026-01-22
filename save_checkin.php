<?php
session_start();
require_once "db.php";

header("Content-Type: text/plain; charset=utf-8");

/* ตรวจ session */
if (!isset($_SESSION["employee_id"])) {
    echo "NO_SESSION";
    exit;
}

$employee_id = $_SESSION["employee_id"];
$distance = $_POST["distance"] ?? null;

if ($distance === null) {
    echo "NO_DISTANCE";
    exit;
}

$today = date("Y-m-d");
$time  = date("H:i:s");

/* เช็คว่ามีการเช็คอินวันนี้แล้วหรือยัง */
$check = $mysqli->prepare("
    SELECT id FROM checkins
    WHERE employee_id = ? AND checkin_date = ?
");
$check->bind_param("is", $employee_id, $today);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo "ALREADY";
    exit;
}

/* บันทึกข้อมูล */
$stmt = $mysqli->prepare("
    INSERT INTO checkins (employee_id, checkin_date, checkin_time, distance)
    VALUES (?, ?, ?, ?)
");

if (!$stmt) {
    echo "SQL_ERROR";
    exit;
}

$stmt->bind_param("issd", $employee_id, $today, $time, $distance);
$stmt->execute();

echo "OK|$time";
