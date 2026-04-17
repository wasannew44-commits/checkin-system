<?php
session_start();

if(!isset($_SESSION["employee_id"]) || $_SESSION["role"]!=="admin"){
 header("Location:index.php");
 exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>รายงานเข้างาน</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
body{
  font-family:system-ui;
  padding:20px;
  background:#f3f4f6;
  color:#111827;
}
h2,h3{
  margin:0 0 12px;
}
.card{
  background:#fff;
  border-radius:12px;
  padding:16px;
  margin-bottom:18px;
  box-shadow:0 1px 3px rgba(0,0,0,.08);
}
.row{
  display:grid;
  grid-template-columns:repeat(auto-fit,minmax(180px,1fr));
  gap:10px;
  margin-bottom:10px;
}
input,select,button{
  padding:10px;
  border:1px solid #d1d5db;
  border-radius:8px;
  font:inherit;
}
button{
  border:none;
  cursor:pointer;
}
.btn{
  background:#2563eb;
  color:#fff;
}
.btn-green{
  background:#16a34a;
  color:#fff;
}
.btn-gray{
  background:#6b7280;
  color:#fff;
}
table{
  width:100%;
  background:#fff;
  border-collapse:collapse;
  margin-top:12px;
}
th,td{
  padding:10px;
  border:1px solid #e5e7eb;
  text-align:center;
  font-size:14px;
}
th{
  background:#f9fafb;
}
.late{
  background:#fecaca;
}
.early{
  background:#bbf7d0;
}
.summary{
  font-size:16px;
  font-weight:bold;
  margin:10px 0 0;
}
.muted{
  color:#6b7280;
  font-size:13px;
}
.flex{
  display:flex;
  gap:8px;
  flex-wrap:wrap;
}
.checkbox-grid{
  display:grid;
  grid-template-columns:repeat(auto-fit,minmax(180px,1fr));
  gap:8px;
  margin-top:10px;
}
.checkbox-item{
  background:#f9fafb;
  border:1px solid #e5e7eb;
  border-radius:8px;
  padding:8px 10px;
  text-align:left;
}
.checkbox-item input{
  width:auto;
  margin-right:6px;
}
.kpi{
  display:grid;
  grid-template-columns:repeat(auto-fit,minmax(180px,1fr));
  gap:10px;
  margin-top:12px;
}
.kpi-box{
  background:#f9fafb;
  border:1px solid #e5e7eb;
  border-radius:10px;
  padding:12px;
}
.kpi-box .label{
  font-size:13px;
  color:#6b7280;
}
.kpi-box .value{
  font-size:22px;
  font-weight:700;
  margin-top:4px;
}
a{
  text-decoration:none;
  color:#2563eb;
}
.small{
  font-size:12px;
}
</style>
</head>
<body>

<h2>📊 รายงานเวลาเข้างาน / ค่าแรง / ค่าคอม</h2>
<p>ผู้ดูแลระบบ: <b><?= htmlspecialchars($_SESSION["fullname"]) ?></b></p>
<p>
  <a href="index.php">← กลับหน้าเช็คอิน</a> |
  <a href="admin.php">👑 จัดการพนักงาน</a>
</p>

<div class="card">
  <h3>ตัวกรองรายงาน</h3>

  <div class="row">
    <select id="userSelect"></select>
    <input type="month" id="monthSelect">
    <input type="date" id="startDate">
    <input type="date" id="endDate">
  </div>

  <div class="flex">
    <button class="btn" id="applyMonthBtn">ใช้เดือนที่เลือก</button>
    <button class="btn-gray" id="clearDateBtn">ล้างช่วงวันที่</button>
  </div>

  <div id="summary" class="summary"></div>

  <div class="kpi">
    <div class="kpi-box">
      <div class="label">จำนวนวันมาทำงาน</div>
      <div class="value" id="workDaysBox">0</div>
    </div>
    <div class="kpi-box">
      <div class="label">จำนวนวันหยุด</div>
      <div class="value" id="offDaysBox">0</div>
    </div>
    <div class="kpi-box">
      <div class="label">ค่าแรง/เงินเดือนหลังหัก</div>
      <div class="value" id="salaryBox">0</div>
    </div>
    <div class="kpi-box">
      <div class="label">ค่าคอมรวม</div>
      <div class="value" id="commissionBox">0</div>
    </div>
    <div class="kpi-box">
      <div class="label">รายได้รวม</div>
      <div class="value" id="finalBox">0</div>
    </div>
  </div>

  <p class="muted">
    หมายเหตุ: พนักงานประจำหยุดได้ 1 วัน/สัปดาห์ ระบบนี้จะคิดสิทธิหยุดตามช่วงวันที่ที่เลือก
  </p>
</div>

<div class="card">
  <h3>เพิ่มค่าคอมรายวัน</h3>

  <div class="row">
    <input type="date" id="commissionDate">
    <input type="number" id="cupsSold" placeholder="จำนวนแก้วที่ขายได้">
    <input type="number" id="totalCommission" placeholder="ค่าคอมรวมของวันนั้น (บาท)">
  </div>

  <div class="flex">
    <button class="btn-gray" id="loadAttendanceBtn">ดึงคนที่มาเข้างานวันนี้อัตโนมัติ</button>
  </div>

  <div id="commissionEmployeeList" class="checkbox-grid"></div>

  <div class="flex" style="margin-top:12px;">
    <button class="btn-green" id="saveCommissionBtn">บันทึกค่าคอม</button>
  </div>

  <p class="muted">
    ระบบจะหารค่าคอมเท่ากันตามจำนวนคนที่เลือกในวันนั้น
  </p>
</div>

<div class="card">
  <h3>ตารางเข้างาน</h3>
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
</div>

<div class="card">
  <h3>ตารางค่าคอมของพนักงานที่เลือก</h3>
  <table>
    <thead>
      <tr>
        <th>วันที่</th>
        <th>จำนวนแก้ว</th>
        <th>ค่าคอมรวมทั้งวัน</th>
        <th>จำนวนคนที่หาร</th>
        <th>ค่าคอมที่ได้</th>
      </tr>
    </thead>
    <tbody id="commissionBody"></tbody>
  </table>
</div>

<script type="module">
import { initializeApp } from "https://www.gstatic.com/firebasejs/10.12.2/firebase-app.js";
import { getDatabase, ref, onValue, push, get } from "https://www.gstatic.com/firebasejs/10.12.2/firebase-database.js";

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

const userSelect = document.getElementById("userSelect");
const monthSelect = document.getElementById("monthSelect");
const startDate = document.getElementById("startDate");
const endDate = document.getElementById("endDate");

const body = document.getElementById("reportBody");
const summary = document.getElementById("summary");
const commissionBody = document.getElementById("commissionBody");

const workDaysBox = document.getElementById("workDaysBox");
const offDaysBox = document.getElementById("offDaysBox");
const salaryBox = document.getElementById("salaryBox");
const commissionBox = document.getElementById("commissionBox");
const finalBox = document.getElementById("finalBox");

const commissionDate = document.getElementById("commissionDate");
const cupsSold = document.getElementById("cupsSold");
const totalCommission = document.getElementById("totalCommission");
const commissionEmployeeList = document.getElementById("commissionEmployeeList");
const loadAttendanceBtn = document.getElementById("loadAttendanceBtn");
const saveCommissionBtn = document.getElementById("saveCommissionBtn");
const applyMonthBtn = document.getElementById("applyMonthBtn");
const clearDateBtn = document.getElementById("clearDateBtn");

let employees = {};
let checkins = {};
let commissions = {};

function thaiMoney(num){
  return Number(num || 0).toLocaleString("th-TH");
}

function timeToMinutes(t){
  const [h,m] = String(t || "00:00:00").split(":").map(Number);
  return (h || 0) * 60 + (m || 0);
}

function formatDateInput(date){
  const d = new Date(date);
  const y = d.getFullYear();
  const m = String(d.getMonth()+1).padStart(2,"0");
  const day = String(d.getDate()).padStart(2,"0");
  return `${y}-${m}-${day}`;
}

function formatThaiDate(dateStr){
  return new Date(dateStr + "T00:00:00").toLocaleDateString("th-TH");
}

function normalizeDateOnly(value){
  const d = new Date(value);
  return new Date(d.getFullYear(), d.getMonth(), d.getDate());
}

function getCurrentRange(){
  let start = null;
  let end = null;

  if(startDate.value && endDate.value){
    start = new Date(startDate.value + "T00:00:00");
    end = new Date(endDate.value + "T23:59:59");
    return { start, end };
  }

  if(monthSelect.value){
    const [y,m] = monthSelect.value.split("-").map(Number);
    start = new Date(y, m - 1, 1, 0, 0, 0);
    end = new Date(y, m, 0, 23, 59, 59);
    return { start, end };
  }

  return { start:null, end:null };
}

function isInRange(dateObj){
  const { start, end } = getCurrentRange();
  if(!start || !end) return false;
  return dateObj >= start && dateObj <= end;
}

function getEmployeeIdByCheckin(checkin){
  if(checkin.employee_id && employees[checkin.employee_id]){
    return checkin.employee_id;
  }

  const found = Object.entries(employees).find(([id,emp]) => emp.fullname === checkin.employee);
  return found ? found[0] : null;
}

function getEmployeeName(empId){
  return employees[empId]?.fullname || "-";
}

function buildEmployeeSelect(){
  userSelect.innerHTML = `<option value="">-- เลือกพนักงาน --</option>`;
  Object.entries(employees).forEach(([id, emp])=>{
    userSelect.innerHTML += `<option value="${id}">${emp.fullname}</option>`;
  });
}

function buildCommissionEmployeeChecklist(selectedIds=[]){
  commissionEmployeeList.innerHTML = "";
  Object.entries(employees).forEach(([id, emp])=>{
    const checked = selectedIds.includes(id) ? "checked" : "";
    commissionEmployeeList.innerHTML += `
      <label class="checkbox-item">
        <input type="checkbox" class="commission-emp" value="${id}" ${checked}>
        ${emp.fullname}
      </label>
    `;
  });
}

function getFilteredCheckinsByEmployee(empId){
  const rows = [];

  Object.values(checkins).forEach(c=>{
    const checkDate = new Date(c.timestamp);
    if(!isInRange(checkDate)) return;

    const rowEmpId = getEmployeeIdByCheckin(c);
    if(!rowEmpId || rowEmpId !== empId) return;

    rows.push({
      ...c,
      employee_id: rowEmpId,
      dateObj: checkDate
    });
  });

  rows.sort((a,b)=>a.dateObj - b.dateObj);
  return rows;
}

function getUniqueWorkDates(filteredRows){
  const set = new Set();
  filteredRows.forEach(r=>{
    set.add(formatDateInput(r.dateObj));
  });
  return Array.from(set).sort();
}

function diffDaysInclusive(start, end){
  const s = normalizeDateOnly(start);
  const e = normalizeDateOnly(end);
  const diff = Math.round((e - s) / 86400000);
  return diff + 1;
}

function calculateSalary(empId, workDateList){
  const emp = employees[empId];
  if(!emp) return { salaryPay:0, offDays:0, deductibleOffDays:0, weeksAllowed:0 };

  const { start, end } = getCurrentRange();
  if(!start || !end){
    return { salaryPay:0, offDays:0, deductibleOffDays:0, weeksAllowed:0 };
  }

  const totalDaysInRange = diffDaysInclusive(start, end);
  const workDays = workDateList.length;
  const offDays = Math.max(0, totalDaysInRange - workDays);

  // พาร์ทไทม์ = จำนวนวันที่มาทำงาน x ค่าแรงรายวัน
  if(emp.employee_type === "parttime"){
    const daily = Number(emp.daily_wage || 0);
    return {
      salaryPay: workDays * daily,
      offDays,
      deductibleOffDays: 0,
      weeksAllowed: 0
    };
  }

  // พนักงานประจำ = คิดเฉพาะช่วงวันที่เลือก
  const monthlySalary = Number(emp.salary || 0);
  const perDay = monthlySalary / 30;

  // เงินพื้นฐานเฉพาะช่วงวันที่เลือก
  const baseSalaryInRange = perDay * totalDaysInRange;

  // หยุดได้ 1 วัน / สัปดาห์ ตามช่วงวันที่เลือก
  const allowedOffDays = Math.ceil(totalDaysInRange / 7);

  // ถ้าช่วงสั้นกว่า 7 วัน ให้หยุดฟรี 0 วัน
  const deductibleOffDays = Math.max(0, offDays - allowedOffDays);

  // หักเฉพาะวันที่เกินสิทธิ
  const deduction = deductibleOffDays * perDay;

  const salaryPay = Math.max(0, baseSalaryInRange - deduction);

  return {
    salaryPay,
    offDays,
    deductibleOffDays,
    weeksAllowed: allowedOffDays
  };
}

  const monthlySalary = Number(emp.salary || 0);
  const perDay = monthlySalary / 30;
  const allowedOffDays = Math.ceil(totalDaysInRange / 7);
  const deductibleOffDays = Math.max(0, offDays - allowedOffDays);
  const deduction = deductibleOffDays * perDay;
  const salaryPay = Math.max(0, monthlySalary - deduction);

  return {
    salaryPay,
    offDays,
    deductibleOffDays,
    weeksAllowed: allowedOffDays
  };
}

function getCommissionRowsForEmployee(empId){
  const rows = [];

  Object.entries(commissions).forEach(([cid, item])=>{
    if(!item?.date) return;

    const d = new Date(item.date + "T12:00:00");
    if(!isInRange(d)) return;

    const shares = item.shares || {};
    if(shares[empId] == null) return;

    const selectedEmployees = item.employees ? Object.keys(item.employees).length : 0;

    rows.push({
      id: cid,
      date: item.date,
      cups: Number(item.cups || 0),
      total_commission: Number(item.total_commission || 0),
      people_count: selectedEmployees,
      my_share: Number(shares[empId] || 0)
    });
  });

  rows.sort((a,b)=>a.date.localeCompare(b.date));
  return rows;
}

function renderAttendanceTable(empId){
  body.innerHTML = "";

  const rows = getFilteredCheckinsByEmployee(empId);
  const workMin = timeToMinutes(WORK_TIME);

  let totalDiff = 0;

  rows.forEach(c=>{
    const checkMin = timeToMinutes(c.time);
    const diff = checkMin - workMin;
    totalDiff += diff;

    const status = diff > 0 ? "มาสาย" : diff < 0 ? "มาเร็ว" : "ตรงเวลา";

    const tr = document.createElement("tr");
    tr.className = diff > 0 ? "late" : diff < 0 ? "early" : "";

    tr.innerHTML = `
      <td>${c.dateObj.toLocaleDateString("th-TH")}</td>
      <td>${getEmployeeName(empId)}</td>
      <td>${c.time || "-"}</td>
      <td>${status}</td>
      <td>${Math.abs(diff)}</td>
    `;

    body.appendChild(tr);
  });

  if(rows.length === 0){
    body.innerHTML = `<tr><td colspan="5">ไม่พบข้อมูลในช่วงวันที่เลือก</td></tr>`;
  }

  if(totalDiff < 0){
    summary.innerHTML = `✅ รวมมาเร็วก่อนเวลา ${Math.abs(totalDiff)} นาที`;
    summary.style.color = "green";
  }else if(totalDiff > 0){
    summary.innerHTML = `⚠️ รวมมาสาย ${totalDiff} นาที`;
    summary.style.color = "red";
  }else{
    summary.innerHTML = `ตรงเวลา`;
    summary.style.color = "#111827";
  }

  return rows;
}

function renderCommissionTable(empId){
  commissionBody.innerHTML = "";
  const rows = getCommissionRowsForEmployee(empId);

  let totalCommissionValue = 0;

  rows.forEach(r=>{
    totalCommissionValue += r.my_share;

    commissionBody.innerHTML += `
      <tr>
        <td>${formatThaiDate(r.date)}</td>
        <td>${thaiMoney(r.cups)}</td>
        <td>${thaiMoney(r.total_commission)}</td>
        <td>${thaiMoney(r.people_count)}</td>
        <td>${thaiMoney(r.my_share)}</td>
      </tr>
    `;
  });

  if(rows.length === 0){
    commissionBody.innerHTML = `<tr><td colspan="5">ไม่พบข้อมูลค่าคอมในช่วงวันที่เลือก</td></tr>`;
  }

  return totalCommissionValue;
}

function render(){
  const empId = userSelect.value;

  body.innerHTML = "";
  commissionBody.innerHTML = "";
  summary.innerHTML = "";
  workDaysBox.textContent = "0";
  offDaysBox.textContent = "0";
  salaryBox.textContent = "0";
  commissionBox.textContent = "0";
  finalBox.textContent = "0";

  if(!empId) return;

  const rows = renderAttendanceTable(empId);
  const workDateList = getUniqueWorkDates(rows);
  const salaryData = calculateSalary(empId, workDateList);
  const commissionTotal = renderCommissionTable(empId);
  const finalTotal = salaryData.salaryPay + commissionTotal;

  workDaysBox.textContent = thaiMoney(workDateList.length);
  offDaysBox.textContent = thaiMoney(salaryData.offDays);
  salaryBox.textContent = thaiMoney(salaryData.salaryPay);
  commissionBox.textContent = thaiMoney(commissionTotal);
  finalBox.textContent = thaiMoney(finalTotal);
}

async function autoSelectAttendanceEmployees(){
  const dateValue = commissionDate.value;
  if(!dateValue){
    alert("กรุณาเลือกวันที่ก่อน");
    return;
  }

  const selectedIds = [];

  Object.values(checkins).forEach(c=>{
    const d = new Date(c.timestamp);
    const day = formatDateInput(d);
    if(day !== dateValue) return;

    const empId = getEmployeeIdByCheckin(c);
    if(empId && !selectedIds.includes(empId)){
      selectedIds.push(empId);
    }
  });

  buildCommissionEmployeeChecklist(selectedIds);

  if(selectedIds.length === 0){
    alert("วันนั้นยังไม่พบข้อมูลเข้างานอัตโนมัติ คุณสามารถติ๊กเลือกเองได้");
  }
}

async function saveCommission(){
  const date = commissionDate.value;
  const cups = Number(cupsSold.value || 0);
  const total = Number(totalCommission.value || 0);

  const selectedNodes = [...document.querySelectorAll(".commission-emp:checked")];
  const selectedIds = selectedNodes.map(el=>el.value);

  if(!date){
    alert("กรุณาเลือกวันที่");
    return;
  }

  if(total <= 0){
    alert("กรุณาใส่ค่าคอมรวม");
    return;
  }

  if(selectedIds.length === 0){
    alert("กรุณาเลือกพนักงานที่ได้ค่าคอม");
    return;
  }

  const perPerson = total / selectedIds.length;
  const employeeMap = {};
  const shares = {};

  selectedIds.forEach(id=>{
    employeeMap[id] = true;
    shares[id] = Number(perPerson.toFixed(2));
  });

  await push(ref(db, "commissions"), {
    date,
    cups,
    total_commission: total,
    employees: employeeMap,
    shares
  });

  alert("บันทึกค่าคอมเรียบร้อย");
  cupsSold.value = "";
  totalCommission.value = "";
  buildCommissionEmployeeChecklist([]);
  render();
}

function applyMonthToDateRange(){
  if(!monthSelect.value){
    alert("กรุณาเลือกเดือน");
    return;
  }

  const [y,m] = monthSelect.value.split("-").map(Number);
  const start = new Date(y, m - 1, 1);
  const end = new Date(y, m, 0);

  startDate.value = formatDateInput(start);
  endDate.value = formatDateInput(end);

  render();
}

function clearDateRange(){
  startDate.value = "";
  endDate.value = "";
  render();
}

onValue(ref(db,"employees"), snap=>{
  employees = snap.val() || {};
  buildEmployeeSelect();
  buildCommissionEmployeeChecklist([]);
  render();
});

onValue(ref(db,"checkins"), snap=>{
  checkins = snap.val() || {};
  render();
});

onValue(ref(db,"commissions"), snap=>{
  commissions = snap.val() || {};
  render();
});

userSelect.onchange = render;
monthSelect.onchange = render;
startDate.onchange = render;
endDate.onchange = render;

loadAttendanceBtn.onclick = autoSelectAttendanceEmployees;
saveCommissionBtn.onclick = saveCommission;
applyMonthBtn.onclick = applyMonthToDateRange;
clearDateBtn.onclick = clearDateRange;

// ตั้งค่าเดือนปัจจุบันเริ่มต้น
const now = new Date();
monthSelect.value = `${now.getFullYear()}-${String(now.getMonth()+1).padStart(2,"0")}`;
applyMonthToDateRange();
</script>

</body>
</html>
