<?php
session_start();
header("Content-Type: text/plain");

if (!isset($_SESSION["employee_id"])) {
  echo "NOLOGIN";
  exit;
}

include "db.php";

$employee_id = $_SESSION["employee_id"];
$distance = $_POST["distance"] ?? null;

if ($distance === null) {
  echo "NODATA";
  exit;
}

$today = date("Y-m-d");
$now   = date("H:i:s");

/* กันเช็คอินซ้ำ */
$chk = $conn->prepare(
  "SELECT id FROM checkins WHERE employee_id = ? AND checkin_date = ?"
);
$chk->bind_param("is", $employee_id, $today);
$chk->execute();
$chk->store_result();

if ($chk->num_rows > 0) {
  echo "ALREADY";
  exit;
}

/* บันทึก */
$stmt = $conn->prepare(
  "INSERT INTO checkins (employee_id, checkin_date, checkin_time, distance)
   VALUES (?, ?, ?, ?)"
);
$stmt->bind_param("issd", $employee_id, $today, $now, $distance);

echo $stmt->execute() ? "OK" : "DBERROR";
