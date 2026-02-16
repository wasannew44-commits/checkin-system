<?php
session_start();

$error = null;

function getDeviceId(){

 return hash(
   "sha256",
   ($_SERVER['HTTP_USER_AGENT'] ?? '') .
   ($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '')
 );
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

 $username = $_POST["username"] ?? '';
 $password = hash("sha256", $_POST["password"] ?? '');

 $device_id = getDeviceId();

 $url = "https://checkin-system-5b6a4-default-rtdb.asia-southeast1.firebasedatabase.app/employees.json";

 $json = file_get_contents($url);
 $employees = json_decode($json,true);

 if($employees){

  foreach($employees as $id=>$user){

   if(
     $user["username"] === $username &&
     $user["password"] === $password
   ){

    // ⭐ ถ้ายังไม่เคยผูกเครื่อง
    if(empty($user["device_id"])){

      $update_url = "https://checkin-system-5b6a4-default-rtdb.asia-southeast1.firebasedatabase.app/employees/$id/device_id.json";

      file_put_contents($update_url,json_encode($device_id));

    }
    elseif($user["device_id"] !== $device_id){

      $error="บัญชีนี้ถูกล็อคกับเครื่องอื่น";
      break;
    }

    $_SESSION["employee_id"]=$id;
    $_SESSION["fullname"]=$user["fullname"];
    $_SESSION["role"]=$user["role"];

    header("Location:index.php");
    exit;
   }
  }
 }

 $error="ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
}
?>
