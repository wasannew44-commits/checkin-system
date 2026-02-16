<?php
session_start();

$error=null;

if($_SERVER["REQUEST_METHOD"]==="POST"){

$username=$_POST["username"]??"";
$password=$_POST["password"]??"";

$data=json_decode(file_get_contents(
"https://checkin-system-5b6a4-default-rtdb.asia-southeast1.firebasedatabase.app/employees.json"
),true);

if($data){

$hash=hash("sha256",$password);

foreach($data as $key=>$user){

if(
$user["username"]===$username &&
$user["password"]===$hash
){

$_SESSION["employee_id"]=$key;
$_SESSION["fullname"]=$user["fullname"];
$_SESSION["role"]=$user["role"];

header("Location:index.php");
exit;

}

}

}

$error="Login ไม่ถูกต้อง";

}
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>เข้าสู่ระบบ</title>

<style>

body{
margin:0;
font-family:system-ui;
background:#f3f4f6;
display:flex;
justify-content:center;
align-items:center;
height:100vh;
}

/* กล่อง login */
.login-box{
background:white;
padding:30px;
border-radius:14px;
width:90%;
max-width:380px;
text-align:center;
box-shadow:0 8px 20px rgba(0,0,0,0.1);
}

/* โลโก้ */
.logo{
width:90px;
margin-bottom:15px;
}

/* input */
input{
width:100%;
padding:14px;
margin-top:10px;
border-radius:8px;
border:1px solid #ddd;
font-size:16px;
}

/* ปุ่ม */
button{
width:100%;
margin-top:15px;
padding:14px;
background:#2563eb;
border:none;
border-radius:8px;
color:white;
font-size:16px;
cursor:pointer;
}

button:hover{
background:#1d4ed8;
}

.error{
color:red;
margin-top:10px;
}

</style>
</head>

<body>

<div class="login-box">

<!-- ⭐ ใส่โลโก้ตรงนี้ -->
<img src="logo.png" class="logo">

<h2>เข้าสู่ระบบ</h2>

<?php if ($error): ?>
<div class="error"><?= $error ?></div>
<?php endif; ?>

<form method="post">

<input name="username" placeholder="Username" required>

<input name="password" type="password" placeholder="Password" required>

<button>เข้าสู่ระบบ</button>

</form>

</div>

</body>
</html>

