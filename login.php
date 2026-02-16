<?php
session_start();
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>เข้าสู่ระบบ</title>
</head>

<body>

<h2>เข้าสู่ระบบ</h2>

<input id="username" placeholder="Username"><br><br>
<input id="password" type="password" placeholder="Password"><br><br>

<button onclick="login()">เข้าสู่ระบบ</button>

<p id="error" style="color:red"></p>

<script type="module">

import { initializeApp } from "https://www.gstatic.com/firebasejs/10.12.2/firebase-app.js";
import { getDatabase, ref, get, child, update } from "https://www.gstatic.com/firebasejs/10.12.2/firebase-database.js";

const firebaseConfig = {
 apiKey:"AIzaSyBr6DpIWx4lws1fHvTSoePy5fcthnybZD8",
 authDomain:"checkin-system-5b6a4.firebaseapp.com",
 databaseURL:"https://checkin-system-5b6a4-default-rtdb.asia-southeast1.firebasedatabase.app",
 projectId:"checkin-system-5b6a4",
 storageBucket:"checkin-system-5b6a4.firebasestorage.app",
 messagingSenderId:"45265472142",
 appId:"1:45265472142:web:bc0e732b3968efa42dd7df"
};

const app = initializeApp(firebaseConfig);
const db = getDatabase(app);

async function sha256(str){

 const buf = await crypto.subtle.digest(
  "SHA-256",
  new TextEncoder().encode(str)
 );

 return Array.from(new Uint8Array(buf))
  .map(b=>b.toString(16).padStart(2,"0"))
  .join("");
}

function getDeviceId(){

 return btoa(
   navigator.userAgent +
   screen.width +
   screen.height
 );
}

window.login = async function(){

 const username=document.getElementById("username").value;
 const password=document.getElementById("password").value;
 const error=document.getElementById("error");

 const hash = await sha256(password);

 const snapshot = await get(ref(db,"employees"));

 if(!snapshot.exists()){
   error.innerText="ไม่มีข้อมูล";
   return;
 }

 const data=snapshot.val();

 for(const key in data){

   const user=data[key];

   if(user.username===username && user.password===hash){

      const device=getDeviceId();

      // ⭐ lock device
      if(user.device_id && user.device_id!==device){

        error.innerText="บัญชีถูกล็อคกับเครื่องอื่น";
        return;
      }

      if(!user.device_id){

        await update(ref(db,"employees/"+key),{
          device_id:device
        });
      }

      // ส่ง session ไป PHP
      fetch("set_session.php",{
        method:"POST",
        headers:{ "Content-Type":"application/x-www-form-urlencoded" },
        body:
        "fullname="+encodeURIComponent(user.fullname)+
        "&role="+user.role+
        "&id="+key
      }).then(()=>{
        location="index.php";
      });

      return;
   }
 }

 error.innerText="Login ไม่ถูกต้อง";

}

</script>

</body>
</html>
