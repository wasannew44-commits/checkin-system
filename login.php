<?php
session_start();

$error = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = $_POST["username"] ?? '';
    $password = $_POST["password"] ?? '';

    // ⭐ TEMP LOGIN (ยังไม่ใช้ DB)
    // เปลี่ยนได้ตามต้องการ

    if ($username === "admin" && $password === "1234") {

        $_SESSION["employee_id"] = 1;
        $_SESSION["fullname"] = "ผู้ดูแลระบบ";
        $_SESSION["role"] = "admin";

        header("Location: index.php");
        exit;

    } else {
        $error = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>เข้าสู่ระบบ</title>
</head>
<body>

<h2>เข้าสู่ระบบ</h2>

<?php if (!empty($error)) : ?>
    <p style="color:red"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="post">
    <input name="username" placeholder="Username" required><br><br>
    <input name="password" type="password" placeholder="Password" required><br><br>
    <button type="submit">เข้าสู่ระบบ</button>
</form>

</body>
</html>
