<?php
session_start();

$error = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = $_POST["username"] ?? '';
    $password = $_POST["password"] ?? '';

    // ⭐ hash password (เหมือน admin page)
    $hash = hash("sha256", $password);

    // ⭐ ดึง firebase
    $url = "https://checkin-system-5b6a4-default-rtdb.asia-southeast1.firebasedatabase.app/employees.json";

    $json = file_get_contents($url);

    $employees = json_decode($json, true);

    if ($employees) {

        foreach ($employees as $key => $emp) {

            if (
                $emp["username"] === $username &&
                $emp["password"] === $hash
            ) {

                // login success
                $_SESSION["employee_id"] = $key;
                $_SESSION["fullname"] = $emp["fullname"];
                $_SESSION["role"] = $emp["role"];

                header("Location: index.php");
                exit;
            }
        }
    }

    $error = "Login ไม่ถูกต้อง";
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
<input type="password" name="password" placeholder="Password">
<button>เข้าสู่ระบบ</button>
</form>

</body>
</html>
