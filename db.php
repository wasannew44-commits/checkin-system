<?php
$conn = new mysqli("localhost", "root", "", "checkin_db");
$conn->set_charset("utf8");

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>
