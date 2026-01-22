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
  border-radius: 16px;
  box-shadow: 0 10px 25px rgba(0,0,0,.08);
}

h2 {
  text-align: center;
  margin-bottom: 5px;
}

.user {
  text-align: center;
  color: #555;
  margin-bottom: 20px;
}

.btn {
  width: 100%;
  padding: 14px;
  font-size: 16px;
  border-radius: 12px;
  border: none;
  cursor: pointer;
  color: #fff;
  margin-bottom: 10px;
}

.btn-blue { background: #2563eb; }
.btn-green { background: #16a34a; }
.btn-red { background: #dc2626; }
.btn-gray { background: #6b7280; }

#status {
  margin-top: 15px;
  background: #f8fafc;
  padding: 14px;
  border-radius: 12px;
  border: 1px solid #e5e7eb;
  font-size: 15px;
  white-space: pre-line;
}
</style>
</head>

<body>

<h2>ระบบเช็คเวลาเข้างาน</h2>

<p>
  ผู้ใช้งาน:
  <b><?= htmlspecialchars($_SESSION["fullname"]) ?></b>
</p>

<button class="btn btn-blue" onclick="checkIn()">📍 เช็คอิน</button>
<a href="report.php" class="btn btn-green">📄 ดูประวัติการเข้างาน</a>

<?php if ($_SESSION["role"] === "admin"): ?>
  <a href="admin.php" class="btn btn-gray">👑 หน้า Admin</a>
<?php endif; ?>

<a href="logout.php" class="btn btn-red">🚪 ออกจากระบบ</a>

<p id="status">ยังไม่ได้เช็คอิน</p>

<script>
const officeLat = 16.32803442485856;
const officeLng = 103.30575654156942;
const allowedRadius = 150;
const maxAccuracy = 100;
const workStartTime = "08:00:00";

function checkIn() {
  const status = document.getElementById("status");
  status.innerText = "📍 กำลังตรวจสอบตำแหน่ง...";

  navigator.geolocation.getCurrentPosition(
    pos => {
      const lat = pos.coords.latitude;
      const lng = pos.coords.longitude;
      const accuracy = pos.coords.accuracy;

      if (accuracy > maxAccuracy) {
        status.innerText = "⚠️ GPS ยังไม่แม่น (" + accuracy.toFixed(1) + " m)";
        return;
      }

      const distance = getDistance(lat, lng, officeLat, officeLng);
      if (distance > allowedRadius) {
        status.innerText = "❌ อยู่นอกพื้นที่ (" + distance.toFixed(1) + " m)";
        return;
      }

      status.innerText = "💾 กำลังบันทึกข้อมูล...";

      fetch("save_checkin.php", {
  method: "POST",
  credentials: "same-origin", // ⭐ ส่ง session cookie ไปด้วย
  headers: {"Content-Type": "application/x-www-form-urlencoded"},
  body: "distance=" + encodeURIComponent(distance)
})
      .then(r => r.text())
.then(r => {
  r = r.trim();
  console.log("SERVER:", r);

  if (r.trim() === "OK") {
          const now = new Date().toTimeString().substring(0,8);
          status.innerText =
            "✅ เช็คอินสำเร็จ\n" +
            "เวลา: " + now + "\n" +
            "ระยะ: " + distance.toFixed(1) + " เมตร\n\n" +
            (now > workStartTime
              ? "⚠️ ทำไมถึงมาทำงานสายย"
              : "👏 ทำดีก็ทำได้");
        } else if (r === "ALREADY") {
          status.innerText = "⚠️ วันนี้คุณเช็คอินแล้ว";
        } else {
          status.innerText = "❌ บันทึกไม่สำเร็จ";
        }
      });
    },
    err => status.innerText = "❌ ไม่สามารถดึง GPS ได้"
  );
}

function getDistance(lat1, lon1, lat2, lon2) {
  const R = 6371000;
  const dLat = (lat2-lat1)*Math.PI/180;
  const dLon = (lon2-lon1)*Math.PI/180;
  const a =
    Math.sin(dLat/2)**2 +
    Math.cos(lat1*Math.PI/180) *
    Math.cos(lat2*Math.PI/180) *
    Math.sin(dLon/2)**2;
  return R * (2*Math.atan2(Math.sqrt(a),Math.sqrt(1-a)));
}
</script>

</body>
</html>


