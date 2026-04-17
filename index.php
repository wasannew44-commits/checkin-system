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
  box-shadow:0 1px 3px rgba(0,0,0,.08);
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
  text-decoration:none;
  text-align:center;
  box-sizing:border-box;
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
  border:1px solid #e5e7eb;
}
.small{
  font-size:13px;
  color:#6b7280;
}
</style>
</head>

<body>

<div class="container">

  <h2>ระบบเช็คเวลาเข้างาน</h2>

  <p>ผู้ใช้งาน: <b><?= htmlspecialchars($_SESSION["fullname"]) ?></b></p>
  <p class="small">รหัสพนักงาน: <?= htmlspecialchars($_SESSION["employee_id"]) ?></p>

  <button class="btn blue" onclick="checkIn()">📍 เช็คอิน</button>

  <a href="report.php" class="btn green">📄 ดูประวัติ</a>

  <?php if(($_SESSION["role"] ?? "") === "admin"): ?>
    <a href="admin.php" class="btn gray">👑 Admin</a>
    <a href="admin_report.php" class="btn gray">📊 รายงานแอดมิน</a>
  <?php endif; ?>

  <a href="logout.php" class="btn red">🚪 Logout</a>

  <div id="status">ยังไม่ได้เช็คอิน</div>

</div>

<script>
const employeeId = <?= json_encode($_SESSION["employee_id"] ?? "") ?>;
const employeeName = <?= json_encode($_SESSION["fullname"] ?? "") ?>;
const employeeRole = <?= json_encode($_SESSION["role"] ?? "") ?>;
</script>

<script type="module">
import { initializeApp } from "https://www.gstatic.com/firebasejs/10.12.2/firebase-app.js";
import { getDatabase, ref, push, get } from "https://www.gstatic.com/firebasejs/10.12.2/firebase-database.js";

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
const allowedRadius = 100;
const maxAccuracy = 100;
const workStartTime = "08:00:00";

async function checkIn(){
  const status = document.getElementById("status");
  status.innerText = "📍 กำลังตรวจสอบตำแหน่ง...";

  if(!employeeId || !employeeName){
    status.innerText = "❌ ไม่พบข้อมูลพนักงานใน session";
    return;
  }

  navigator.geolocation.getCurrentPosition(
    async (pos) => {
      const { latitude, longitude, accuracy } = pos.coords;

      if(accuracy > maxAccuracy){
        status.innerText = `⚠️ GPS ยังไม่แม่น (${accuracy.toFixed(1)} m)`;
        return;
      }

      const distance = getDistance(latitude, longitude, officeLat, officeLng);

      if(distance > allowedRadius){
        status.innerText = `❌ อยู่นอกพื้นที่ (${distance.toFixed(1)} m)`;
        return;
      }

      status.innerText = "🔎 กำลังตรวจสอบว่ามีการเช็คอินวันนี้แล้วหรือยัง...";

      const snapshot = await get(ref(db,"checkins"));
      const data = snapshot.val();
      const today = new Date().toISOString().slice(0,10);

      if(data){
        const already = Object.values(data).some(c => {
          const rowDate = new Date(c.timestamp).toISOString().slice(0,10);

          if(c.employee_id){
            return c.employee_id === employeeId && rowDate === today;
          }

          return c.employee === employeeName && rowDate === today;
        });

        if(already){
          status.innerText = "❌ วันนี้คุณเช็คอินแล้ว";
          return;
        }
      }

      status.innerText = "💾 กำลังบันทึกข้อมูล...";

      const now = new Date();

      const time =
        now.getHours().toString().padStart(2,"0") + ":" +
        now.getMinutes().toString().padStart(2,"0") + ":" +
        now.getSeconds().toString().padStart(2,"0");

      await push(ref(db,"checkins"), {
        employee_id: employeeId,
        employee: employeeName,
        role: employeeRole,
        time: time,
        timestamp: Date.now(),
        lat: latitude,
        lng: longitude,
        distance: Number(distance.toFixed(2))
      });

      const lateMessage =
        time > workStartTime
          ? "⚠️ วันนี้มาสายนะ"
          : "👏 เช็คอินเรียบร้อย ทำดีมาก";

      status.innerText =
        "✅ เช็คอินสำเร็จ\n" +
        "เวลา: " + time + "\n" +
        "ระยะห่าง: " + distance.toFixed(1) + " เมตร\n\n" +
        lateMessage;
    },
    () => {
      document.getElementById("status").innerText = "❌ ไม่สามารถดึง GPS ได้";
    },
    {
      enableHighAccuracy: true,
      timeout: 15000,
      maximumAge: 0
    }
  );
}

window.checkIn = checkIn;

function getDistance(lat1, lon1, lat2, lon2){
  const R = 6371000;
  const dLat = (lat2-lat1) * Math.PI / 180;
  const dLon = (lon2-lon1) * Math.PI / 180;

  const a =
    Math.sin(dLat/2) ** 2 +
    Math.cos(lat1 * Math.PI / 180) *
    Math.cos(lat2 * Math.PI / 180) *
    Math.sin(dLon/2) ** 2;

  return R * (2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)));
}
</script>

</body>
</html>
