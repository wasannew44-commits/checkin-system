<?php
session_start();

if(!isset($_SESSION["employee_id"]) || $_SESSION["role"]!=="admin"){
 header("Location:index.php");
 exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>รายงานเข้างาน</title>

<style>

body{font-family:system-ui;padding:20px;background:#f3f4f6;}

table{
width:100%;
background:white;
border-collapse:collapse;
}

th,td{
padding:10px;
border:1px solid #ddd;
text-align:center;
}

.late{background:#fecaca;}     /* แดง */
.early{background:#bbf7d0;}    /* เขียว */

</style>
</head>
<body>

<h2>📊 รายงานเวลาเข้างาน</h2>

<select id="userSelect"></select>

<input type="month" id="monthSelect">

<br><br>

<table>
<thead>
<tr>
<th>วันที่</th>
<th>ชื่อ</th>
<th>เวลา</th>
<th>สถานะ</th>
<th>ต่างจากเวลา (นาที)</th>
</tr>
</thead>
<tbody id="reportBody"></tbody>
</table>


<script type="module">

import { initializeApp } from "https://www.gstatic.com/firebasejs/10.12.2/firebase-app.js";
import { getDatabase, ref, onValue } from "https://www.gstatic.com/firebasejs/10.12.2/firebase-database.js";

const firebaseConfig = {
apiKey:"AIza...",
databaseURL:"https://checkin-system-5b6a4-default-rtdb.asia-southeast1.firebasedatabase.app"
};

const app = initializeApp(firebaseConfig);
const db = getDatabase(app);

const WORK_TIME = "08:00:00";

const userSelect = document.getElementById("userSelect");
const monthSelect = document.getElementById("monthSelect");
const body = document.getElementById("reportBody");

let employees = {};
let checkins = {};


// โหลด user
onValue(ref(db,"employees"),snap=>{
 employees = snap.val() || {};

 userSelect.innerHTML="";

 Object.values(employees).forEach(u=>{
  userSelect.innerHTML+=`<option>${u.fullname}</option>`;
 });

 render();
});


// โหลด checkin
onValue(ref(db,"checkins"),snap=>{
 checkins = snap.val() || {};
 render();
});


userSelect.onchange = render;
monthSelect.onchange = render;


function timeToMinutes(t){

 const [h,m,s]=t.split(":").map(Number);
 return h*60+m;
}


function render(){

 body.innerHTML="";

 const selectedUser = userSelect.value;
 const selectedMonth = monthSelect.value;

 if(!selectedUser || !selectedMonth) return;

 const workMin = timeToMinutes(WORK_TIME);

 Object.values(checkins).forEach(c=>{

  if(c.employee !== selectedUser) return;

  const date = new Date(c.timestamp);

  const ym = date.toISOString().slice(0,7);

  if(ym !== selectedMonth) return;

  const checkMin = timeToMinutes(c.time);

  const diff = checkMin - workMin;

  const status = diff>0 ? "มาสาย" : "มาเร็ว";

  const tr = document.createElement("tr");

  tr.className = diff>0 ? "late":"early";

  tr.innerHTML=`
  <td>${date.toLocaleDateString()}</td>
  <td>${c.employee}</td>
  <td>${c.time}</td>
  <td>${status}</td>
  <td>${Math.abs(diff)}</td>
  `;

  body.appendChild(tr);

 });

}

</script>

</body>
</html>
