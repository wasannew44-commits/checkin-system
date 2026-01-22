<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["employee_id"])) {
  echo "NOSESSION";
  exit;
}

$employee_id = $_SESSION["employee_id"];
$distance = $_POST["distance"] ?? 0;

$date = date("Y-m-d");
$time = date("H:i:s");

/* เช็คซ้ำ */
$chk = $mysqli->prepare("
  SELECT id FROM checkins
  WHERE employee_id = ? AND checkin_date = ?
");
$chk->bind_param("is", $employee_id, $date);
$chk->execute();
$chk->store_result();

if ($chk->num_rows > 0) {
  echo "ALREADY";
  exit;
}

$stmt = $mysqli->prepare("
  INSERT INTO checkins (employee_id, checkin_date, checkin_time, distance)
  VALUES (?, ?, ?, ?)
");
$stmt->bind_param("issd", $employee_id, $date, $time, $distance);
$stmt->execute();

/* ส่งเวลาจาก server กลับไป */
echo "OK|เวลา: $time\nระยะ: " . number_format($distance,1) . " เมตร";
