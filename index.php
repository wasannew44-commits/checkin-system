<!-- ส่งชื่อพนักงานจาก PHP -> JS -->
<script>
const employeeName = <?php echo json_encode($_SESSION["fullname"]); ?>;
</script>


<script type="module">

import { initializeApp } from "https://www.gstatic.com/firebasejs/10.12.2/firebase-app.js";
import { getDatabase, ref, push } from "https://www.gstatic.com/firebasejs/10.12.2/firebase-database.js";

const officeLat = 16.32803442485856;
const officeLng = 103.30575654156942;
const allowedRadius = 150;
const maxAccuracy = 100;
const workStartTime = "08:00:00";

const firebaseConfig = {
  apiKey: "AIzaSyBr6DpIWx4lws1fHvTSoePy5fcthnybZD8",
  authDomain: "checkin-system-5b6a4.firebaseapp.com",
  databaseURL: "https://checkin-system-5b6a4-default-rtdb.asia-southeast1.firebasedatabase.app",
  projectId: "checkin-system-5b6a4",
  storageBucket: "checkin-system-5b6a4.firebasestorage.app",
  messagingSenderId: "45265472142",
  appId: "1:45265472142:web:bc0e732b3968efa42dd7df"
};

const app = initializeApp(firebaseConfig);
const db = getDatabase(app);


/* ⭐ สำคัญ ต้องประกาศ global */
window.checkIn = function() {

  const status = document.getElementById("status");
  status.innerText = "📍 กำลังตรวจสอบตำแหน่ง...";

  navigator.geolocation.getCurrentPosition(

    (pos) => {

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

      const now = new Date();

      const time =
        now.getHours().toString().padStart(2,"0")+":"+
        now.getMinutes().toString().padStart(2,"0")+":"+
        now.getSeconds().toString().padStart(2,"0");

      const checkinRef = ref(db,"checkins");

      push(checkinRef,{
        employee: employeeName,
        time: time,
        timestamp: Date.now(),
        lat: latitude,
        lng: longitude,
        distance: distance
      });

      const late =
        time > workStartTime
          ? "⚠️ ทำไมถึงมาทำงานสายยย"
          : "👏 ทำดีก็ทำได้สุดยอด!!";

      status.innerText =
        "✅ เช็คอินสำเร็จ\n"+
        "เวลา: "+time+"\n\n"+
        late;
    },

    () => status.innerText = "❌ ไม่สามารถดึง GPS ได้"

  );
}


function getDistance(lat1, lon1, lat2, lon2) {

  const R = 6371000;

  const dLat = (lat2 - lat1) * Math.PI / 180;
  const dLon = (lon2 - lon1) * Math.PI / 180;

  const a =
    Math.sin(dLat/2)**2 +
    Math.cos(lat1*Math.PI/180) *
    Math.cos(lat2*Math.PI/180) *
    Math.sin(dLon/2)**2;

  return R * (2*Math.atan2(Math.sqrt(a),Math.sqrt(1-a)));
}

</script>


