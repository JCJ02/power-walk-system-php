<?php
$connection = mysqli_connect("localhost", "root", "", "power_walk_db");

// Check Connection
if (mysqli_connect_errno()) {
  echo "Failed to Connect to MySQL: " . mysqli_connect_error();
}
date_default_timezone_set('Asia/Manila');
