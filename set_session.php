<?php
session_start();

$_SESSION["employee_id"] = $_POST["id"];
$_SESSION["fullname"] = $_POST["fullname"];
$_SESSION["role"] = $_POST["role"];

echo "OK";
