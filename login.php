<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Login</title>
</head>

<body>

<h2>เข้าสู่ระบบ</h2>

<input id="username" placeholder="Username">
<input id="password" type="password" placeholder="Password">

<button onclick="login()">เข้าสู่ระบบ</button>

<p id="error" style="color:red"></p>

<script type="module">

import { initializeApp } from "https://www.gstatic.com/firebasejs/10.12.2/firebase-app.js";
import { getDatabase, ref, get } from "https://www.gstatic.com/firebasejs/10.12.2/firebase-database.js";

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

window.login = async function(){

const username=document.getElementById("username").value;
const password=document.getElementById("password").value;

const passHash = await sha256(password);

const snap = await get(ref(db,"employees"));

const data = snap.val();

let found=null;
let id=null;

Object.entries(data).forEach(([key,row])=>{

 if(row.username===username && row.password===passHash){
  found=row;
  id=key;
 }

});

if(!found){

document.getElementById("error").innerText="Login ไม่ถูกต้อง";
return;

}

localStorage.setItem("employee_id",id);
localStorage.setItem("fullname",found.fullname);
localStorage.setItem("role",found.role);

location.href="index.php";

}

</script>

</body>
</html>
