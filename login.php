<?php
session_start();
require_once "db.php";

// ฟังก์ชันสร้าง device_id
function getDeviceId() {
    return hash(
        'sha256',
        ($_SERVER['HTTP_USER_AGENT'] ?? '') .
        ($_SERVER['REMOTE_ADDR'] ?? '') .
        ($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '')
    );
}

$error = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username  = $_POST["username"] ?? '';
    $password  = $_POST["password"] ?? '';
    $device_id = getDeviceId();

    // ✅ ใช้ $mysqli เท่านั้น
    $stmt = $mysqli->prepare("
        SELECT id, fullname, role, device_id
        FROM employees
        WHERE username = ? AND password = SHA2(?,256)
    ");

    if (!$stmt) {
        die("SQL prepare failed: " . $mysqli->error);
    }

    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {

        // 🔐 ยังไม่เคยผูกอุปกรณ์
        if (empty($user["device_id"])) {
            $update = $mysqli->prepare(
                "UPDATE employees SET device_id = ? WHERE id = ?"
            );

            if (!$update) {
                die("Update prepare failed: " . $mysqli->error);
            }

            $update->bind_param("si", $device_id, $user["id"]);
            $update->execute();

        // ❌ เครื่องไม่ตรง
        } elseif ($user["device_id"] !== $device_id) {
            $error = "บัญชีนี้ถูกผูกกับอุปกรณ์อื่น กรุณาติดต่อผู้ดูแลระบบ";
        }

        // ✅ เข้าได้
        if (!$error) {
            $_SESSION["employee_id"] = $user["id"];
            $_SESSION["fullname"]    = $user["fullname"];
            $_SESSION["role"]        = $user["role"];

            header("Location: index.php");
            exit;
        }

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

