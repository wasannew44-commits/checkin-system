<?php
ini_set('session.cookie_samesite', 'None');
ini_set('session.cookie_secure', '1');
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
<title>ระบบเช็คเวลาเข้างาน</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
/* ===== Base ===== */
* {
  box-sizing: border-box;
}

body {
  font-family: 'Inter', system-ui, -apple-system, sans-serif;
  background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
  padding: 20px;
}

.container {
  max-width: 480px;
  margin: auto;
  background: #fff;
  padding: 24px;
  border-radius: 18px;
  box-shadow: 0 10px 30px rgba(0,0,0,.1);
}

/* ===== Header ===== */
h2 {
  text-align: center;
  margin-bottom: 6px;
}

.user {
  text-align: center;
  color: #555;
  font-size: 15px;
  margin-bottom: 20px;
}

/* ===== Actions ===== */
.actions {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.btn {
  display: block;
  width: 100%;
  padding: 14px;
  font-size: 16px;
  border-radius: 14px;
  border: none;
  cursor: pointer;
  color: #fff;
  text-align: center;
  text-decoration: none;
}

.btn-blue  { background: #2563eb; }
.btn-green { background: #16a34a; }
.btn-gray  { background: #6b7280; }
.btn-red   { background: #dc2626; }

/* ===== Status ===== */
#status {
  margin-top: 20px;
  background: #f8fafc;
  padding: 14px;
  border-radius: 14px;
  border: 1px solid #e5e7eb;
  font-size: 15px;
  white-space: pre-line;
  text-align: center;
}

/* ===== Mobile ===== */
@media (max-width: 480px) {
  body {
    padding: 12px;
  }

  .container {
    padding: 18px;
    border-radius: 14px;
  }

  h2 {
    font-size: 20px;
  }

  .btn {
    font-size: 15px;
    padding: 12px;
  }

  #status {
    font-size: 14px;
  }
}
</style>
</head>

<body>

<div class="container">

  <h2>ระบบเช็คเวลาเข้างาน</h2>

  <div class="user">
    ผู้ใช้งาน: <b><?= htmlspecialchars($_SESSION["fullname"]) ?></b>
  </div>

  <div class="actions">
    <button class="btn btn-blue" onclick="checkIn()">📍 เช็คอิน</button>

    <a href="report.php" class="btn btn-green">
      📄 ดูประวัติการเข้างาน
    </a>

    <?php if ($_SESSION["role"] === "admin"): ?>
      <a href="admin.php" class="btn btn-gray">
        👑 หน้า Admin
      </a>
    <?php endif; ?>

    <a href="logout.php" class="btn btn-red">
      🚪 ออกจากระบบ
    </a>
  </div>

  <div id="status">ยังไม่ได้เช็คอิน</div>

</div>

<script>
const officeLat = 16.32803442485856;
const officeLng = 103.30575654156942;
const allowedRadius = 150;
const maxAccuracy = 100;

function checkIn() {
  const status = document.getElementById("status");
  status.innerText = "📍 กำลังตรวจสอบตำแหน่ง...";

  navigator.geolocation.getCurrentPosition(
    pos => {
      const { latitude, longitude, accuracy } = pos.coords;

      if (accuracy > maxAccuracy) {
        status.innerText = `⚠️ GPS ยังไม่แม่น (${accuracy.toFixed(1)} m)`;
        return;
      }

      const distance = getDistance(latitude, longitude, officeLat, officeLng);

      if (distance > allowedRadius) {
        status.innerText = `❌ อยู่นอกพื้นที่ (${distance.toFixed(1)} m)`;
        return;
      }

      status.innerText = "💾 กำลังบันทึกข้อมูล...";

     fetch("save_checkin.php", {
  method: "POST",
  credentials: "include",
  headers: {
    "Content-Type": "application/x-www-form-urlencoded"
  },
  body: "distance=" + encodeURIComponent(distance)
})
.then(r => r.text())
.then(r => {
  r = r.trim();
  console.log("SERVER:", r);

  if (r.startsWith("OK")) {
    status.innerText = "✅ เช็คอินสำเร็จ\nเวลา: " + r.split("|")[1];
  } else if (r === "ALREADY") {
    status.innerText = "⚠️ วันนี้คุณเช็คอินแล้ว";
  } else {
    status.innerText = "❌ บันทึกไม่สำเร็จ\n" + r;
  }
});
    },
    () => status.innerText = "❌ ไม่สามารถดึง GPS ได้"
  );
}

function getDistance(lat1, lon1, lat2, lon2) {
  const R = 6371000;
  const dLat = (lat2 - lat1) * Math.PI / 180;
  const dLon = (lon2 - lon1) * Math.PI / 180;

  const a =
    Math.sin(dLat / 2) ** 2 +
    Math.cos(lat1 * Math.PI / 180) *
    Math.cos(lat2 * Math.PI / 180) *
    Math.sin(dLon / 2) ** 2;

  return R * (2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a)));
}
</script>

</body>
</html>

