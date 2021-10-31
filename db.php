<?php
include "autoload.php";

$servername = env('DB_HOST');//"localhost";
$username = env('DB_USERNAME');//"root";
$password = env('DB_PASSWORD');//"toor";
$dbname = env('DB_NAME');//"project1";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}