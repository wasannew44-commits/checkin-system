<?php
session_start();
if (!isset($_SESSION["employee_id"])) {
  header("Location: login.php");
  exit;
}

include "db.php";

$employee_id = $_SESSION["employee_id"];
$fullname = $_SESSION["fullname"];

$month = $_GET["month"] ?? date("m");
$year  = $_GET["year"] ?? date("Y");

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=checkin_{$fullname}_{$month}_{$year}.xls");
header("Pragma: no-cache");
header("Expires: 0");

echo "ชื่อพนักงาน\tวันที่\tเวลา\tระยะ (เมตร)\n";

$stmt = $conn->prepare("
  SELECT checkin_date, checkin_time, distance
  FROM checkins
  WHERE employee_id = ?
    AND MONTH(checkin_date) = ?
    AND YEAR(checkin_date) = ?
  ORDER BY checkin_date ASC
");
$stmt->bind_param("iii", $employee_id, $month, $year);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
  echo
    $fullname . "\t" .
    $row["checkin_date"] . "\t" .
    $row["checkin_time"] . "\t" .
    number_format($row["distance"], 1) . "\n";
}
exit;
