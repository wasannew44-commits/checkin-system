<?php
session_start();

if (!isset($_SESSION["employee_id"])) {
  header("Location: login.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>ประวัติการเช็คอิน</title>

<style>

body{
font-family:system-ui;
background:#f3f4f6;
padding:20px;
}

.container{
max-width:600px;
margin:auto;
background:#fff;
padding:20px;
border-radius:12px;
}

.card{
padding:12px;
margin-top:10px;
border-radius:8px;
background:#f9fafb;
}

</style>
</head>

<body>

<div class="container">

<h2>📄 ประวัติการเช็คอิน</h2>

<div id="list">กำลังโหลด...</div>

<a href="index.php">⬅ กลับหน้าเช็คอิน</a>

</div>

<!-- ⭐ ส่งชื่อ user จาก PHP -> JS -->
<script>
const myName = <?= json_encode($_SESSION["fullname"]) ?>;
</script>


<script type="module">

import { initializeApp } from "https://www.gstatic.com/firebasejs/10.12.2/firebase-app.js";
import { getDatabase, ref, onValue } from "https://www.gstatic.com/firebasejs/10.12.2/firebase-database.js";


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

const list = document.getElementById("list");

const checkinsRef = ref(db,"checkins");

onValue(checkinsRef,(snapshot)=>{

const data = snapshot.val();

if(!data){
list.innerHTML="ยังไม่มีข้อมูล";
return;
}

let html="";

Object.values(data)
.reverse()
.forEach(item=>{

// ⭐ สำคัญมาก (ล็อคให้เห็นเฉพาะตัวเอง)
if(item.employee !== myName) return;

html += `
<div class="card">
👤 ${item.employee}<br>
⏰ ${item.time}<br>
📍 ระยะ ${Number(item.distance).toFixed(1)} m
</div>
`;

});

if(html===""){
 list.innerHTML="ยังไม่มีประวัติของคุณ";
}else{
 list.innerHTML=html;
}

});

</script>

</body>
</html>
