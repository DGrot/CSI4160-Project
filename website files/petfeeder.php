<!DOCTYPE html>

<html>

<head>

    <meta charset="utf-8" />

    <link rel="stylesheet" href="style.css" />

    <title>Team Catbug Pet Feeder</title>

</head>

    <div class="navbar">

        <ul>

            <li><a href="#">Pet Feeder</a></li>

        </ul>

    </div>
<img src="catbug.png" alt="Catbug">
    
    <div class="logs">
<h1>Pet Feeder Logs</h1> 

<?php

$host = "34.132.166.21";
$username = "root";
$password = "password";
$dbname = "csi4160-grot-db";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
  die("connection failed: " . $conn->connect_error);
}

    
$select_query = "SELECT * FROM servo_log";
$result = $conn->query($select_query);
if ($result->num_rows > 0) {
echo "<table border='3'>

<tr>
<th>Timestamp</th>
<th>Description</th>
</tr>";
      
while($row = $result->fetch_assoc())

  {
  echo "<tr>";
  echo "<td>" . $row['timestamp'] . "</td>";
  echo "<td>" . $row['description'] . "</td>";
  echo "</tr>";
}
    echo "</table>";
    }
?>
        </div>
</html>