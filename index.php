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
<title>ระบบเช็คเวลาเข้างาน</title>

<style>

body{
font-family:system-ui;
background:#f3f4f6;
padding:20px;
}

.container{
max-width:450px;
margin:auto;
background:#fff;
padding:20px;
border-radius:12px;
}

.btn{
display:block;
width:100%;
padding:14px;
margin-top:10px;
border:none;
border-radius:8px;
color:#fff;
cursor:pointer;
}

.blue{background:#2563eb;}
.green{background:#16a34a;}
.red{background:#dc2626;}
.gray{background:#6b7280;}

#status{
margin-top:20px;
padding:12px;
background:#f9fafb;
border-radius:8px;
text-align:center;
white-space:pre-line;
}

</style>
</head>

<body>

<div class="container">

<h2>ระบบเช็คเวลาเข้างาน</h2>

<p>ผู้ใช้งาน: <b><?= htmlspecialchars($_SESSION["fullname"]) ?></b></p>

<button class="btn blue" onclick="checkIn()">📍 เช็คอิน</button>

<a href="report.php" class="btn green">📄 ดูประวัติ</a>

<?php if($_SESSION["role"]==="admin"): ?>
<a href="admin.php" class="btn gray">👑 Admin</a>
<?php endif; ?>

<a href="logout.php" class="btn red">🚪 Logout</a>

<div id="status">ยังไม่ได้เช็คอิน</div>

</div>


<!-- ส่งชื่อ PHP ไป JS -->
<script>
const employeeName = <?= json_encode($_SESSION["fullname"] ?? "") ?>;
</script>


<script type="module">

import { initializeApp } from "https://www.gstatic.com/firebasejs/10.12.2/firebase-app.js";
import { getDatabase, ref, push } from "https://www.gstatic.com/firebasejs/10.12.2/firebase-database.js";


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

const officeLat = 16.32803442485856;
const officeLng = 103.30575654156942;
const allowedRadius = 150;
const maxAccuracy = 100;
const workStartTime = "08:00:00";


window.checkIn=function(){

const status=document.getElementById("status");

status.innerText="📍 กำลังตรวจสอบตำแหน่ง...";

navigator.geolocation.getCurrentPosition(

(pos)=>{

const {latitude,longitude,accuracy}=pos.coords;

if(accuracy>maxAccuracy){
status.innerText=`⚠️ GPS ยังไม่แม่น (${accuracy.toFixed(1)} m)`;
return;
}

const distance=getDistance(latitude,longitude,officeLat,officeLng);

if(distance>allowedRadius){
status.innerText=`❌ อยู่นอกพื้นที่ (${distance.toFixed(1)} m)`;
return;
}

const now=new Date();

const time=
now.getHours().toString().padStart(2,"0")+":"+
now.getMinutes().toString().padStart(2,"0")+":"+
now.getSeconds().toString().padStart(2,"0");

const checkinRef=ref(db,"checkins");

push(checkinRef,{
employee:employeeName,
time:time,
timestamp:Date.now(),
lat:latitude,
lng:longitude,
distance:distance
});

const late=
time>workStartTime
?"⚠️ ทำไมถึงมาทำงานสายยย"
:"👏 ทำดีก็ทำได้สุดยอด!!";

status.innerText=
"✅ เช็คอินสำเร็จ\n"+
"เวลา: "+time+"\n\n"+
late;

},

()=>status.innerText="❌ ไม่สามารถดึง GPS ได้"

);

};


function getDistance(lat1,lon1,lat2,lon2){

const R=6371000;

const dLat=(lat2-lat1)*Math.PI/180;
const dLon=(lon2-lon1)*Math.PI/180;

const a=
Math.sin(dLat/2)**2+
Math.cos(lat1*Math.PI/180)*
Math.cos(lat2*Math.PI/180)*
Math.sin(dLon/2)**2;

return R*(2*Math.atan2(Math.sqrt(a),Math.sqrt(1-a)));

}

</script>

</body>
</html>
