<?php
$conn = new mysqli("localhost","root","","credit_tracker");
if($conn->connect_error){
  die("DB Connection Failed");
}
session_start();
?>
