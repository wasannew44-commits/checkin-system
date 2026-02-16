<?php
session_start();

if (!isset($_SESSION["employee_id"]) || $_SESSION["role"] !== "admin") {
  header("Location: index.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>Admin | จัดการพนักงาน</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
body { font-family: system-ui; padding:20px; background:#f9fafb; }
table { width:100%; border-collapse:collapse; background:#fff; margin-top:15px; }
th,td { border:1px solid #ddd; padding:8px; text-align:center; }
th { background:#f3f4f6; }
input,select { padding:6px; width:100%; margin-bottom:6px; }
button { padding:6px 12px; border:none; border-radius:5px; cursor:pointer; }
.btn-add { background:#16a34a; color:#fff; }
.btn-del { background:#dc2626; color:#fff; }
.btn-edit { background:#2563eb; color:#fff; }
.warn { color:#dc2626; font-size:13px; }
a { text-decoration:none; }
</style>

</head>
<body>

<h2>👑 Admin : จัดการพนักงาน</h2>
<p>ผู้ดูแลระบบ: <b><?= htmlspecialchars($_SESSION["fullname"]) ?></b></p>

<a href="index.php">← กลับหน้าเช็คอิน</a>

<hr>

<h3>➕ เพิ่มพนักงาน</h3>

<input id="fullname" placeholder="ชื่อ-สกุล">
<input id="username" placeholder="Username">
<input id="password" type="password" placeholder="Password">

<select id="role">
<option value="user">User</option>
<option value="admin">Admin</option>
</select>

<button class="btn-add" onclick="addEmployee()">เพิ่มพนักงาน</button>

<hr>

<h3>👥 รายชื่อพนักงาน</h3>

<table>

<thead>
<tr>
<th>ID</th>
<th>ชื่อ</th>
<th>Username</th>
<th>Role</th>
<th>จัดการ</th>
</tr>
</thead>

<tbody id="employeeList"></tbody>

</table>


<script type="module">

import { initializeApp } from "https://www.gstatic.com/firebasejs/10.12.2/firebase-app.js";
import { getDatabase, ref, push, onValue, remove } from "https://www.gstatic.com/firebasejs/10.12.2/firebase-database.js";

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

const employeeRef = ref(db,"employees");


// ⭐ hash password
async function sha256(str){

 const buf = await crypto.subtle.digest(
  "SHA-256",
  new TextEncoder().encode(str)
 );

 return Array.from(new Uint8Array(buf))
  .map(b=>b.toString(16).padStart(2,"0"))
  .join("");
}


// ⭐ เพิ่มพนักงาน
window.addEmployee = async function(){

 const fullname=document.getElementById("fullname").value.trim();
 const username=document.getElementById("username").value.trim();
 const password=document.getElementById("password").value;
 const role=document.getElementById("role").value;

 if(!fullname || !username || !password){
   alert("กรอกข้อมูลให้ครบ");
   return;
 }

 push(employeeRef,{
   fullname,
   username,
   password: await sha256(password),
   role,
   device_id:""
 });

 alert("เพิ่มพนักงานแล้ว");

};


// ⭐ โหลดรายชื่อ
const list=document.getElementById("employeeList");

onValue(employeeRef,(snapshot)=>{

 list.innerHTML="";

 const data=snapshot.val();
 if(!data) return;

 Object.entries(data).forEach(([key,row])=>{

 list.innerHTML+=`
<tr>
<td>${key}</td>
<td>${row.fullname}</td>
<td>${row.username}</td>
<td>${row.role}</td>
<td>
<button class="btn-del" onclick="deleteEmployee('${key}')">🗑️ ลบ</button>
</td>
</tr>
`;

 });

});


// ⭐ ลบ
window.deleteEmployee=function(id){

 if(!confirm("ลบพนักงาน?")) return;

 remove(ref(db,"employees/"+id));

};

</script>

</body>
</html>

