<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

$error = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = $_POST["username"] ?? '';
    $password = $_POST["password"] ?? '';

    // TEMP LOGIN
    if ($username === "admin" && $password === "1234") {

        $_SESSION["employee_id"] = 1;
        $_SESSION["fullname"] = "ผู้ดูแลระบบ";
        $_SESSION["role"] = "admin";

        header("Location: index.php");
        exit;
    }

    $error = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
}
?>

<!DOCTYPE html>
<html>
<body>

<h2>เข้าสู่ระบบ</h2>

<?php if($error): ?>
<p style="color:red"><?= $error ?></p>
<?php endif; ?>

<form method="post">
<input name="username" placeholder="Username">
<input name="password" type="password" placeholder="Password">
<button>Login</button>
</form>

</body>
</html>
