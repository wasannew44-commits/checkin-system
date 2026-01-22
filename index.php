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
<title>ระบบเช็คเวลาเข้างาน</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
  body {
    font-family: system-ui, -apple-system, sans-serif;
    padding: 20px;
    background: #f9fafb;
  }
  h2 { margin-bottom: 10px; }

  .btn {
    padding: 10px 18px;
    font-size: 16px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    color: #fff;
    text-decoration: none;
    display: inline-block;
    margin-right: 5px;
    margin-bottom: 8px;
  }
  .btn-blue { background: #2563eb; }
  .btn-green { background: #16a34a; }
  .btn-red { background: #dc2626; }
  .btn-gray { background: #6b7280; }

  #status {
    margin-top: 15px;
    font-size: 16px;
    white-space: pre-line;
    background: #fff;
    padding: 12px;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
  }
</style>
</head>

<body>

<h2>ระบบเช็คเวลาเข้างาน</h2>

<p>
  ผู้ใช้งาน:
  <b><?php echo htmlspecialchars($_SESSION["fullname"]); ?></b>
</p>

<button class="btn btn-blue" onclick="checkIn()">📍 เช็คอิน</button>

<a href="report.php" class="btn btn-green">📄 ดูประวัติการเข้างาน</a>

<?php if (isset($_SESSION["role"]) && $_SESSION["role"] === "admin"): ?>
  <a href="admin.php" class="btn btn-gray">👑 หน้า Admin</a>
<?php endif; ?>

<a href="logout.php" class="btn btn-red">🚪 ออกจากระบบ</a>

<p id="status">ยังไม่ได้เช็คอิน</p>

<script>
// ================== ตั้งค่าพิกัดบริษัท ==================
const officeLat = 16.32803442485856;
const officeLng = 103.30575654156942;
const allowedRadius = 150;   // เมตร
const maxAccuracy = 100;     // เมตร

// ⏰ เวลาเริ่มงาน (ปรับได้)
const workStartTime = "08:00:00";

function checkIn() {
  const status = document.getElementById("status");
  status.innerText = "📍 กำลังตรวจสอบตำแหน่ง...";

  if (!navigator.geolocation) {
    status.innerText = "❌ อุปกรณ์นี้ไม่รองรับ GPS";
    return;
  }

  navigator.geolocation.getCurrentPosition(
    function(position) {
      const userLat = position.coords.latitude;
      const userLng = position.coords.longitude;
      const accuracy = position.coords.accuracy;

      // ❌ GPS ไม่แม่น
      if (accuracy > maxAccuracy) {
        status.innerText =
          "⚠️ สัญญาณ GPS ยังไม่แม่นพอ\n" +
          "Accuracy: " + accuracy.toFixed(1) + " เมตร\n" +
          "กรุณาไปที่โล่งแล้วลองใหม่";
        return;
      }

      const distance = getDistance(
        userLat, userLng,
        officeLat, officeLng
      );

      // ❌ อยู่นอกพื้นที่
      if (distance > allowedRadius) {
        status.innerText =
          "❌ อยู่นอกพื้นที่ทำงาน\n" +
          "ระยะห่าง: " + distance.toFixed(1) + " เมตร\n" +
          "Accuracy: " + accuracy.toFixed(1) + " เมตร";
        return;
      }

      // ✅ ส่งข้อมูลไปบันทึก
      status.innerText = "💾 กำลังบันทึกข้อมูล...";

      fetch("save_checkin.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        body:
          "lat=" + encodeURIComponent(userLat) +
          "&lng=" + encodeURIComponent(userLng) +
          "&distance=" + encodeURIComponent(distance)
      })
      .then(res => res.text())
      .then(result => {
        if (result === "OK") {
  const now = new Date();
  const time =
    now.getHours().toString().padStart(2, '0') + ":" +
    now.getMinutes().toString().padStart(2, '0') + ":" +
    now.getSeconds().toString().padStart(2, '0');

  status.innerText =
    "✅ เช็คอินสำเร็จ\n" +
    "เวลา: " + time + "\n" +
    "ระยะห่าง: " + distance.toFixed(1) + " เมตร\n" +
    "Accuracy: " + accuracy.toFixed(1) + " เมตร\n\n" +
    lateMessage(time);
}
        else if (result === "ALREADY") {
          status.innerText = "⚠️ วันนี้คุณเช็คอินไปแล้ว";
        }
        else {
          status.innerText = "❌ บันทึกข้อมูลไม่สำเร็จ";
        }
      })
      .catch(() => {
        status.innerText = "❌ ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้";
      });
    },
    function(error) {
      status.innerText =
        "❌ ไม่สามารถดึงตำแหน่งได้\n" + error.message;
    },
    {
      enableHighAccuracy: true,
      timeout: 15000,
      maximumAge: 0
    }
  );
}

// ================== Haversine ==================
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
function lateMessage(time) {
  if (time > workStartTime) {
    return "⚠️ ทำไมถึงมาทำงานสายย\nกรุณาเข้างานให้ตรงเวลานะจ๊ะ มีสะสมเวลา";
  } else {
    return "👏 ยอดเยี่ยมมาก!\nวันนี้คุณมาตรงเวลา";
  }}
</script>

</body>
</html>
