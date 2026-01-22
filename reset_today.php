<?php
session_start();
require_once "db.php"; // ⭐ สำคัญมาก

/* ต้องเป็น admin เท่านั้น */
if (!isset($_SESSION["employee_id"]) || $_SESSION["role"] !== "admin") {
  echo "DENIED";
  exit;
}

/* รับ employee_id */
if (!isset($_POST["employee_id"])) {
  echo "NO_ID";
  exit;
}

$employee_id = intval($_POST["employee_id"]);

/* ลบเฉพาะเช็คอิน "วันนี้" */
$stmt = $conn->prepare("
  DELETE FROM checkins
  WHERE employee_id = ?
  AND checkin_date = CURDATE()
");

if (!$stmt) {
  echo "PREPARE_ERROR";
  exit;
}

$stmt->bind_param("i", $employee_id);

if ($stmt->execute()) {
  echo "OK";
} else {
  echo "ERROR";
}
