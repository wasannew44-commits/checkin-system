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
  color:#111827;
}

.container{
  max-width:800px;
  margin:auto;
  background:#fff;
  padding:20px;
  border-radius:12px;
  box-shadow:0 1px 3px rgba(0,0,0,.08);
}

.card{
  padding:12px;
  margin-top:10px;
  border-radius:10px;
  background:#f9fafb;
  border:1px solid #e5e7eb;
}

.topbar{
  display:grid;
  grid-template-columns:repeat(auto-fit,minmax(180px,1fr));
  gap:10px;
  margin:16px 0;
}

input,button{
  padding:10px;
  border-radius:8px;
  border:1px solid #d1d5db;
  font:inherit;
}

button{
  background:#2563eb;
  color:#fff;
  border:none;
  cursor:pointer;
}

.summary{
  margin:14px 0;
  padding:12px;
  border-radius:10px;
  background:#eff6ff;
  border:1px solid #bfdbfe;
}

.small{
  font-size:13px;
  color:#6b7280;
}

.row{
  display:flex;
  justify-content:space-between;
  gap:10px;
  flex-wrap:wrap;
}

.badge{
  display:inline-block;
  padding:4px 8px;
  border-radius:999px;
  font-size:12px;
  font-weight:600;
}

.late{
  background:#fee2e2;
  color:#991b1b;
}

.early{
  background:#dcfce7;
  color:#166534;
}

.ontime{
  background:#e5e7eb;
  color:#374151;
}

.empty{
  text-align:center;
  color:#6b7280;
  padding:20px 0;
}

a{
  text-decoration:none;
  color:#2563eb;
}
</style>
</head>

<body>

<div class="container">

  <h2>📄 ประวัติการเช็คอิน</h2>
  <p>ผู้ใช้งาน: <b><?= htmlspecialchars($_SESSION["fullname"]) ?></b></p>
  <p class="small">รหัสพนักงาน: <?= htmlspecialchars($_SESSION["employee_id"]) ?></p>

  <div class="topbar">
    <input type="month" id="monthSelect">
    <input type="date" id="startDate">
    <input type="date" id="endDate">
    <button id="clearBtn">ล้างตัวกรอง</button>
  </div>

  <div id="summary" class="summary">กำลังโหลด...</div>

  <div id="list">กำลังโหลด...</div>

  <p style="margin-top:18px;">
    <a href="index.php">⬅ กลับหน้าเช็คอิน</a>
  </p>

</div>

<script>
const myId = <?= json_encode($_SESSION["employee_id"] ?? "") ?>;
const myName = <?= json_encode($_SESSION["fullname"] ?? "") ?>;
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

const WORK_TIME = "08:00:00";

const list = document.getElementById("list");
const summary = document.getElementById("summary");
const monthSelect = document.getElementById("monthSelect");
const startDate = document.getElementById("startDate");
const endDate = document.getElementById("endDate");
const clearBtn = document.getElementById("clearBtn");

let allCheckins = {};

function timeToMinutes(t){
  const parts = String(t || "00:00:00").split(":").map(Number);
  return (parts[0] || 0) * 60 + (parts[1] || 0);
}

function formatDateInput(date){
  const d = new Date(date);
  const y = d.getFullYear();
  const m = String(d.getMonth() + 1).padStart(2, "0");
  const day = String(d.getDate()).padStart(2, "0");
  return `${y}-${m}-${day}`;
}

function getStatusLabel(diff){
  if(diff > 0){
    return `<span class="badge late">มาสาย ${diff} นาที</span>`;
  }
  if(diff < 0){
    return `<span class="badge early">มาเร็ว ${Math.abs(diff)} นาที</span>`;
  }
  return `<span class="badge ontime">ตรงเวลา</span>`;
}

function inFilterRange(dateObj){
  if(startDate.value){
    const s = new Date(startDate.value + "T00:00:00");
    if(dateObj < s) return false;
  }

  if(endDate.value){
    const e = new Date(endDate.value + "T23:59:59");
    if(dateObj > e) return false;
  }

  if(monthSelect.value){
    const ym = dateObj.toISOString().slice(0,7);
    if(ym !== monthSelect.value) return false;
  }

  return true;
}

function getMyRows(){
  return Object.values(allCheckins)
    .filter(item => {
      if(item.employee_id){
        return item.employee_id === myId;
      }
      return item.employee === myName;
    })
    .map(item => {
      const dateObj = new Date(item.timestamp);
      return { ...item, dateObj };
    })
    .filter(item => inFilterRange(item.dateObj))
    .sort((a,b) => b.timestamp - a.timestamp);
}

function render(){
  const rows = getMyRows();

  if(rows.length === 0){
    list.innerHTML = `<div class="empty">ยังไม่มีประวัติของคุณในช่วงที่เลือก</div>`;
    summary.innerHTML = `ไม่พบข้อมูล`;
    return;
  }

  let totalLate = 0;
  let totalEarly = 0;
  const uniqueDays = new Set();

  list.innerHTML = rows.map(item => {
    const diff = timeToMinutes(item.time) - timeToMinutes(WORK_TIME);

    if(diff > 0) totalLate += diff;
    if(diff < 0) totalEarly += Math.abs(diff);

    uniqueDays.add(item.dateObj.toISOString().slice(0,10));

    return `
      <div class="card">
        <div class="row">
          <div><b>👤 ${item.employee || myName}</b></div>
          <div>${getStatusLabel(diff)}</div>
        </div>

        <div style="margin-top:8px;">
          📅 ${item.dateObj.toLocaleDateString("th-TH")}<br>
          ⏰ เวลา ${item.time || "-"}<br>
          📍 ระยะ ${Number(item.distance || 0).toFixed(1)} m
        </div>
      </div>
    `;
  }).join("");

  summary.innerHTML = `
    <b>สรุป</b><br>
    จำนวนวันที่เช็คอิน: ${uniqueDays.size} วัน<br>
    มาสายรวม: ${totalLate} นาที<br>
    มาเร็วก่อนเวลา: ${totalEarly} นาที
  `;
}

onValue(ref(db,"checkins"), (snapshot) => {
  allCheckins = snapshot.val() || {};
  render();
});

monthSelect.onchange = render;
startDate.onchange = render;
endDate.onchange = render;

clearBtn.onclick = () => {
  monthSelect.value = "";
  startDate.value = "";
  endDate.value = "";
  render();
};

// ตั้งค่าเดือนปัจจุบันเริ่มต้น
const now = new Date();
monthSelect.value = `${now.getFullYear()}-${String(now.getMonth()+1).padStart(2,"0")}`;
</script>

</body>
</html>
