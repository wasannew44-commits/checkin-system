<?php
session_start();
if (!isset($_SESSION["employee_id"]) || $_SESSION["role"] !== "admin") {
  header("Location: index.php");
  exit;
}

include "db.php";

$work_start = "08:00:00";

// เดือน / ปี
$month = $_GET["month"] ?? date("m");
$year  = $_GET["year"]  ?? date("Y");

$stmt = $conn->prepare("
  SELECT 
    e.fullname,
    c.checkin_date,
    c.checkin_time,
    TIMESTAMPDIFF(
      MINUTE,
      CONCAT(c.checkin_date,' ', ?),
      CONCAT(c.checkin_date,' ', c.checkin_time)
    ) AS late_minutes
  FROM checkins c
  JOIN employees e ON c.employee_id = e.id
  WHERE TIME(c.checkin_time) > ?
    AND MONTH(c.checkin_date) = ?
    AND YEAR(c.checkin_date) = ?
  ORDER BY c.checkin_date DESC, late_minutes DESC
");

$stmt->bind_param("ssii", $work_start, $work_start, $month, $year);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>รายงานมาสาย</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body { font-family: system-ui; padding:20px; background:#f9fafb; }
table { width:100%; border-collapse: collapse; margin-top:15px; background:#fff; }
th,td { border:1px solid #ddd; padding:8px; text-align:center; }
th { background:#fee2e2; }
h2 { margin-top:0; }
.bad { color:#dc2626; font-weight:bold; }
</style>
</head>
<body>

<h2>⏰ รายงานมาสาย</h2>
<p>เวลาเริ่มงาน: <b>08:00 น.</b></p>

<form method="get">
  เดือน:
  <select name="month">
    <?php for($m=1;$m<=12;$m++): ?>
      <option value="<?= $m ?>" <?= $m==$month?'selected':'' ?>><?= $m ?></option>
    <?php endfor; ?>
  </select>

  ปี:
  <select name="year">
    <?php for($y=date("Y");$y>=date("Y")-5;$y--): ?>
      <option value="<?= $y ?>" <?= $y==$year?'selected':'' ?>><?= $y ?></option>
    <?php endfor; ?>
  </select>

  <button>ดูรายงาน</button>
</form>

<table>
<tr>
  <th>ชื่อพนักงาน</th>
  <th>วันที่</th>
  <th>เวลาเข้า</th>
  <th>มาสาย (นาที)</th>
</tr>

<?php if ($result->num_rows === 0): ?>
<tr><td colspan="4">🎉 ไม่มีใครมาสายในช่วงนี้</td></tr>
<?php else: ?>
<?php while($row = $result->fetch_assoc()): ?>
<tr>
  <td><?= htmlspecialchars($row["fullname"]) ?></td>
  <td><?= $row["checkin_date"] ?></td>
  <td><?= $row["checkin_time"] ?></td>
  <td class="bad"><?= $row["late_minutes"] ?></td>
</tr>
<?php endwhile; ?>
<?php endif; ?>
</table>

<br>
<a href="admin.php">← กลับหน้า Admin</a>

</body>
</html>
