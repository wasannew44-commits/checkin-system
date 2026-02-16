<?php
session_start();

$error = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = $_POST["username"] ?? '';
    $password = $_POST["password"] ?? '';

    // ⭐ ดึงข้อมูลจาก Firebase
    $url = "https://checkin-system-5b6a4-default-rtdb.asia-southeast1.firebasedatabase.app/employees.json";

    $json = file_get_contents($url);
    $employees = json_decode($json, true);

    if ($employees) {

        foreach ($employees as $id => $user) {

            if (
                $user["username"] === $username &&
                $user["password"] === hash("sha256",$password)
            ) {

                $_SESSION["employee_id"] = $id;
                $_SESSION["fullname"] = $user["fullname"];
                $_SESSION["role"] = $user["role"];

                header("Location: index.php");
                exit;
            }
        }
    }

    $error = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
}
?>
