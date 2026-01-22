<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["employee_id"])) {
  echo "NO_SESSION";
  exit;
}

$employee_id = $_SESSION["employee_id"];
$distance = isset($_POST["distance"]) ? floatval($_POST["distance"]) : 0;

$today = date("Y-m-d");
$time  = date("H:i:s");

/* เช็คว่ามีเช็คอินวันนี้แล้วหรือยัง */
$check = $conn->prepare("
  SELECT id FROM checkins
  WHERE employee_id = ?
  AND checkin_date = ?
");
$check->bind_param("is", $employee_id, $today);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
  echo "ALREADY";
  exit;
}

/* บันทึกเช็คอิน */
$stmt = $conn->prepare("
  INSERT INTO checkins (employee_id, checkin_date, checkin_time, distance)
  VALUES (?, ?, ?, ?)
");

if (!$stmt) {
  echo "PREPARE_ERROR";
  exit;
}

$stmt->bind_param(
  "issd",
  $employee_id,
  $today,
  $time,
  $distance
);

if ($stmt->execute()) {
  echo "OK";
} else {
  echo "ERROR";
}
