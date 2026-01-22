<?php
session_start();
include "db.php";

if (!isset($_SESSION["employee_id"])) {
  echo "NOLOGIN";
  exit;
}

$employee_id = $_SESSION["employee_id"];
$lat = $_POST["lat"];
$lng = $_POST["lng"];
$distance = $_POST["distance"];

// เช็คว่าคนนี้เช็คอินวันนี้ไปแล้วหรือยัง
$stmt = $conn->prepare("
  SELECT id FROM checkins
  WHERE employee_id = ?
  AND checkin_date = CURDATE()
");
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
  echo "ALREADY";
  exit;
}

// บันทึกข้อมูล (⭐ สำคัญ: employee_id)
$stmt = $conn->prepare("
  INSERT INTO checkins
  (employee_id, checkin_date, checkin_time, latitude, longitude, distance)
  VALUES (?, CURDATE(), CURTIME(), ?, ?, ?)
");
$stmt->bind_param("iddd", $employee_id, $lat, $lng, $distance);

if ($stmt->execute()) {
  echo "OK";
} else {
  echo "ERROR";
}
