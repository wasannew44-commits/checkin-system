<?php
session_start();
if (!isset($_SESSION["employee_id"])) {
  header("Location: login.php");
  exit;
}

include "db.php";

$employee_id = $_SESSION["employee_id"];
$fullname = $_SESSION["fullname"];

// เดือน / ปี (ค่าเริ่มต้น = ปัจจุบัน)
$month = $_GET["month"] ?? date("m");
$year  = $_GET["year"]  ?? date("Y");

require_once "db.php";
$stmt = $conn->prepare("
  SELECT checkin_date, checkin_time, distance
  FROM checkins
  WHERE employee_id = ?
    AND MONTH(checkin_date) = ?
    AND YEAR(checkin_date) = ?
  ORDER BY checkin_date DESC
");
$stmt->bind_param("iii", $employee_id, $month, $year);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>ประวัติการเข้างาน</title>

<style>
body {
  font-family: system-ui, -apple-system, sans-serif;
  padding: 20px;
}
h2 { margin-bottom: 5px; }
.form-box {
  margin-top: 10px;
}
select, button {
  padding: 8px 12px;
  font-size: 15px;
}
button {
  border-radius: 6px;
  border: none;
  cursor: pointer;
}
.btn-view { background: #2563eb; color: #fff; }
.btn-excel { background: #16a34a; color: #fff; }
.btn-back { color: #2563eb; text-decoration: none; }

table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 15px;
}
th, td {
  border: 1px solid #ddd;
  padding: 8px;
  text-align: center;
}
th {
  background: #f3f4f6;
}
</style>
</head>
<body>

<h2>ประวัติการเข้างาน</h2>
<p>ผู้ใช้งาน: <b><?= htmlspecialchars($fullname) ?></b></p>

<form method="get" class="form-box">
  เดือน:
  <select name="month">
    <?php for ($m=1;$m<=12;$m++): ?>
      <option value="<?= $m ?>" <?= ($m==$month?'selected':'') ?>>
        <?= $m ?>
      </option>
    <?php endfor; ?>
  </select>

  ปี:
  <select name="year">
    <?php for ($y=date("Y");$y>=date("Y")-5;$y--): ?>
      <option value="<?= $y ?>" <?= ($y==$year?'selected':'') ?>>
        <?= $y ?>
      </option>
    <?php endfor; ?>
  </select>

  <button type="submit" class="btn-view">ดูข้อมูล</button>

  <!-- ✅ Export Excel -->
  <a href="export_excel.php?month=<?= $month ?>&year=<?= $year ?>">
    <button type="button" class="btn-excel">📥 Export Excel</button>
  </a>
</form>

<table>
  <tr>
    <th>วันที่</th>
    <th>เวลา</th>
    <th>ระยะ (เมตร)</th>
  </tr>

  <?php if ($result->num_rows === 0): ?>
    <tr>
      <td colspan="3">ไม่มีข้อมูล</td>
    </tr>
  <?php else: ?>
    <?php while ($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?= $row["checkin_date"] ?></td>
        <td><?= $row["checkin_time"] ?></td>
        <td><?= number_format($row["distance"], 1) ?></td>
      </tr>
    <?php endwhile; ?>
  <?php endif; ?>
</table>

<br>
<a href="index.php" class="btn-back">← กลับหน้าเช็คอิน</a>

</body>
</html>

