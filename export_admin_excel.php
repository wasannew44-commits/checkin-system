<?php
session_start();
if (!isset($_SESSION["employee_id"]) || $_SESSION["role"] !== "admin") {
  header("Location: index.php");
  exit;
}

include "db.php";

$employee_id = $_GET["employee_id"] ?? "all";
$month = $_GET["month"] ?? date("m");
$year  = $_GET["year"]  ?? date("Y");

// Header สำหรับ Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=checkin_all_{$month}_{$year}.xls");
header("Pragma: no-cache");
header("Expires: 0");

echo "ชื่อพนักงาน\tวันที่\tเวลา\tระยะ (เมตร)\n";

// SQL
$sql = "
SELECT e.fullname, c.checkin_date, c.checkin_time, c.distance
FROM checkins c
JOIN employees e ON c.employee_id = e.id
WHERE MONTH(c.checkin_date) = ? AND YEAR(c.checkin_date) = ?
";

$params = [$month, $year];
$types = "ii";

if ($employee_id !== "all") {
  $sql .= " AND e.id = ?";
  $params[] = $employee_id;
  $types .= "i";
}

$sql .= " ORDER BY e.fullname, c.checkin_date ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
  echo
    $row["fullname"] . "\t" .
    $row["checkin_date"] . "\t" .
    $row["checkin_time"] . "\t" .
    number_format($row["distance"], 1) . "\n";
}
exit;
