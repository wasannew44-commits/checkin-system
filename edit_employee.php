<?php
session_start();
include "db.php";

/* ต้องเป็น admin */
if (!isset($_SESSION["employee_id"]) || $_SESSION["role"] !== "admin") {
  header("Location: index.php");
  exit;
}

$id = intval($_GET["id"] ?? 0);

/* ดึงข้อมูลพนักงาน */
$stmt = $conn->prepare("
  SELECT id, fullname, username, role, device_id
  FROM employees
  WHERE id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$emp = $stmt->get_result()->fetch_assoc();

if (!$emp) {
  echo "ไม่พบข้อมูลพนักงาน";
  exit;
}

/* ===== บันทึกการแก้ไข ===== */
if (isset($_POST["save"])) {
  $username = $_POST["username"];
  $role = $_POST["role"];

  if (!empty($_POST["password"])) {
    // เปลี่ยนรหัสผ่าน
    $password = $_POST["password"];
    $stmt = $conn->prepare("
      UPDATE employees
      SET username = ?, password = SHA2(?,256), role = ?
      WHERE id = ?
    ");
    $stmt->bind_param("sssi", $username, $password, $role, $id);
  } else {
    // ไม่เปลี่ยนรหัส
    $stmt = $conn->prepare("
      UPDATE employees
      SET username = ?, role = ?
      WHERE id = ?
    ");
    $stmt->bind_param("ssi", $username, $role, $id);
  }

  $stmt->execute();
  header("Location: admin.php");
  exit;
}

/* ===== ลบอุปกรณ์ ===== */
if (isset($_POST["reset_device"])) {
  $stmt = $conn->prepare("
    UPDATE employees SET device_id = NULL WHERE id = ?
  ");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  header("Location: edit_employee.php?id=".$id);
  exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>แก้ไขพนักงาน</title>
<style>
body { font-family: system-ui; padding:20px; }
input, select { padding:6px; width:100%; margin-bottom:8px; }
button { padding:8px 14px; }
</style>
</head>
<body>

<h2>✏️ แก้ไขพนักงาน</h2>

<form method="post">
  <label>Username</label>
  <input name="username" value="<?= htmlspecialchars($emp['username']) ?>" required>

  <label>Password (เว้นว่าง = ไม่เปลี่ยน)</label>
  <input type="password" name="password">

  <label>Role</label>
  <select name="role">
    <option value="user" <?= $emp['role']=='user'?'selected':'' ?>>User</option>
    <option value="admin" <?= $emp['role']=='admin'?'selected':'' ?>>Admin</option>
  </select>

  <button name="save">💾 บันทึก</button>
</form>

<hr>

<h3>📱 อุปกรณ์ที่ผูกไว้</h3>
<p>
  <?= $emp["device_id"] ? $emp["device_id"] : "ยังไม่ผูกอุปกรณ์" ?>
</p>

<form method="post"
      onsubmit="return confirm('ลบอุปกรณ์? ผู้ใช้ต้องล็อกอินใหม่');">
  <button name="reset_device">🔓 ลบอุปกรณ์</button>
</form>

<br>
<a href="admin.php">← กลับหน้า Admin</a>

</body>
</html>
