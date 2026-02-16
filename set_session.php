<?php
session_start();

$data=json_decode(file_get_contents("php://input"),true);

$_SESSION["employee_id"]=$data["id"];
$_SESSION["fullname"]=$data["fullname"];
$_SESSION["role"]=$data["role"];
