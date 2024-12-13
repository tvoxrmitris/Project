<?php
// Database connection details
$host = "localhost";
$username = "root";
$password = "";
$dbname = "perfume";

// Create connection
$conn = mysqli_connect($host, $username, $password, $dbname);

// Check connection
if ($conn) {
 
} else {
  echo "Connection failed: " . mysqli_connect_error();
}


?>
