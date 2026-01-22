<?php
session_start();
include "db.php";

/* ต้องเป็น admin เท่านั้น */
if (!isset($_SESSION["employee_id"]) || $_SESSION["role"] !== "admin") {
  echo "DENIED";
  exit;
}

/* รับ employee_id ที่จะลบ */
$employee_id = intval($_POST["employee_id"]);

/* ลบเฉพาะเช็คอิน "วันนี้" ของพนักงานคนนั้น */
$stmt = $conn->prepare("
  DELETE FROM checkins
  WHERE employee_id = ?
  AND DATE(checkin_time) = CURDATE()
");
$stmt->bind_param("i", $employee_id);

if ($stmt->execute()) {
  echo "OK";
} else {
  echo "ERROR";
}
