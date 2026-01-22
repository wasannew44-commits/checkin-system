<?php
session_start();
if (!isset($_SESSION["employee_id"]) || $_SESSION["role"] !== "admin") {
  header("Location: index.php");
  exit;
}

include "db.php";

/* ===== ค่าเริ่มต้น ===== */
$month = $_GET["month"] ?? date("m");
$year  = $_GET["year"] ?? date("Y");
$employee_id = $_GET["employee_id"] ?? "";

/* ===== ดึงรายชื่อพนักงาน ===== */
$emp_list = $conn->query("
  SELECT id, fullname
  FROM employees
  ORDER BY fullname
");

/* ===== ดึงข้อมูลเช็คอิน ===== */
$rows = [];
if ($employee_id) {
  $stmt = $conn->prepare("
    SELECT c.checkin_date, c.checkin_time, e.fullname
    FROM checkins c
    JOIN employees e ON c.employee_id = e.id
    WHERE c.employee_id = ?
      AND MONTH(c.checkin_date) = ?
      AND YEAR(c.checkin_date) = ?
    ORDER BY c.checkin_date
  ");
  $stmt->bind_param("iii", $employee_id, $month, $year);
  $stmt->execute();
  $rows = $stmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>รายงานการเข้างาน (Admin)</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body { font-family: system-ui; padding:20px; }
select, button { padding:6px; }
table { width:100%; border-collapse:collapse; margin-top:15px; }
th,td { border:1px solid #ddd; padding:8px; text-align:center; }
th { background:#f3f4f6; }
.late { color:#dc2626; font-weight:bold; }
</style>
</head>
<body>

<h2>📊 รายงานการเข้างาน (Admin)</h2>
<a href="admin.php">← กลับหน้า Admin</a>

<form method="get" style="margin-top:15px;">
  พนักงาน:
  <select name="employee_id" required>
    <option value="">-- เลือกพนักงาน --</option>
    <?php while ($e = $emp_list->fetch_assoc()): ?>
      <option value="<?= $e['id'] ?>" <?= $employee_id==$e['id']?'selected':'' ?>>
        <?= htmlspecialchars($e['fullname']) ?>
      </option>
    <?php endwhile; ?>
  </select>

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

  <button type="submit">ดูรายงาน</button>
</form>

<?php if ($employee_id): ?>
<table>
<tr>
  <th>วันที่</th>
  <th>เวลาเข้างาน</th>
  <th>สถานะ</th>
</tr>

<?php if ($rows->num_rows == 0): ?>
<tr><td colspan="3">ไม่มีข้อมูล</td></tr>
<?php else: ?>
<?php while ($r = $rows->fetch_assoc()): 
  $late = ($r["checkin_time"] > "08:00:00"); // ⏰ ปรับเวลาได้
?>
<tr>
  <td><?= $r["checkin_date"] ?></td>
  <td><?= $r["checkin_time"] ?></td>
  <td class="<?= $late?'late':'' ?>">
    <?= $late ? "⏰มาสาย" : "✅ปกติ" ?>
  </td>
</tr>
<?php endwhile; ?>
<?php endif; ?>
</table>
<?php endif; ?>

</body>
</html>
