<?php
session_start();
if (!isset($_SESSION["employee_id"]) || $_SESSION["role"] !== "admin") {
  header("Location: index.php");
  exit;
}
require_once "db.php";


/* ========== เพิ่มพนักงาน ========== */
if (isset($_POST["add_employee"])) {
  $fullname = $_POST["fullname"];
  $username = $_POST["username"];
  $password = $_POST["password"];
  $role     = $_POST["role"];

  $stmt = $conn->prepare("
    INSERT INTO employees (fullname, username, password, role)
    VALUES (?, ?, SHA2(?,256), ?)
  ");
  $stmt->bind_param("ssss", $fullname, $username, $password, $role);
  $stmt->execute();

  header("Location: admin.php");
  exit;
}

/* ========== ลบพนักงาน ========== */
if (isset($_GET["delete"])) {
  $delete_id = intval($_GET["delete"]);

  if ($delete_id != $_SESSION["employee_id"]) {
    $stmt = $conn->prepare("DELETE FROM employees WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
  }

  header("Location: admin.php");
  exit;
}

/* ========== ดึงพนักงาน ========== */
$employees = $conn->query("
  SELECT id, fullname, username, role
  FROM employees
  ORDER BY fullname
");
if (!$employees) {
  die("Query failed: " . $conn->error);
}
  
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>Admin | จัดการพนักงาน</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body { font-family: system-ui; padding:20px; background:#f9fafb; }
table { width:100%; border-collapse:collapse; background:#fff; margin-top:15px; }
th,td { border:1px solid #ddd; padding:8px; text-align:center; }
th { background:#f3f4f6; }
input,select { padding:6px; width:100%; margin-bottom:6px; }
button { padding:6px 12px; border:none; border-radius:5px; cursor:pointer; }
.btn-add { background:#16a34a; color:#fff; }
.btn-del { background:#dc2626; color:#fff; }
.btn-edit { background:#2563eb; color:#fff; }
.btn-reset { background:#f59e0b; color:#fff; }
.warn { color:#dc2626; font-size:13px; }
a { text-decoration:none; }
</style>
</head>
<body>

<h2>👑 Admin : จัดการพนักงาน</h2>
<p>ผู้ดูแลระบบ: <b><?= htmlspecialchars($_SESSION["fullname"]) ?></b></p>

<a href="index.php">← กลับหน้าเช็คอิน</a> |
<a href="late_report.php">⏰ รายงานมาสาย</a>
<a href="admin_report.php">📊 รายงานการเข้างาน</a>
<a href="admin_report.php">
  <button>📊 รายงานรายบุคคล</button>
</a>


<hr>

<h3>➕ เพิ่มพนักงาน</h3>
<form method="post">
  <input name="fullname" placeholder="ชื่อ-สกุล" required>
  <input name="username" placeholder="Username" required>
  <input type="password" name="password" placeholder="Password" required>
  <select name="role">
    <option value="user">User</option>
    <option value="admin">Admin</option>
  </select>
  <button class="btn-add" name="add_employee">เพิ่มพนักงาน</button>
</form>

<hr>

<h3>👥 รายชื่อพนักงาน</h3>
<table>
<tr>
  <th>ID</th>
  <th>ชื่อ</th>
  <th>Username</th>
  <th>Role</th>
  <th>จัดการ</th>
</tr>

<?php while ($row = $employees->fetch_assoc()): ?>
<tr>
  <td><?= $row["id"] ?></td>
  <td><?= htmlspecialchars($row["fullname"]) ?></td>
  <td><?= htmlspecialchars($row["username"]) ?></td>
  <td><?= $row["role"] ?></td>
  <td>

    <!-- แก้ไข -->
    <a href="edit_employee.php?id=<?= $row['id'] ?>">
      <button class="btn-edit">✏️ แก้ไข</button>
    </a>

    <!-- ลบเช็คอินวันนี้ -->
    <button class="btn-reset"
      onclick="resetToday(<?= $row['id'] ?>)">
      ⏰ ลบเช็คอินวันนี้
    </button>

    <!-- ลบพนักงาน -->
    <?php if ($row["id"] != $_SESSION["employee_id"]): ?>
      <a href="admin.php?delete=<?= $row['id'] ?>"
         onclick="return confirm('ยืนยันการลบพนักงาน?')">
        <button class="btn-del">🗑️ ลบ</button>
      </a>
    <?php else: ?>
      <div class="warn">ลบบัญชีตัวเองไม่ได้</div>
    <?php endif; ?>

  </td>
</tr>
<?php endwhile; ?>
</table>

<script>
function resetToday(id) {
  if (!confirm("ลบเวลาเช็คอินของวันนี้?")) return;

  fetch("reset_today.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: "employee_id=" + id
  })
  .then(r => r.text())
  .then(res => {
    if (res === "OK") {
      alert("ลบเรียบร้อย");
      location.reload();
    } else {
      alert("ไม่สามารถลบได้");
    }
  });
}
</script>

</body>
</html>

