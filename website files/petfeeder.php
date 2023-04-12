<!DOCTYPE html>

<html>

<head>

    <meta charset="utf-8" />

    <link rel="stylesheet" href="css/style.css" />

    <title>Team Catbug Pet Feeder</title>

</head>

    <div class="navbar">

        <ul>

            <li><a href="#">Pet Feeder</a></li>

        </ul>

    </div>
<img src="images/catbug.png" alt="Catbug">
    
    <div class="logs">
<h1>Pet Feeder Logs</h1> 
<button><a href="pages/graph.php">Go to Graph</a></button>
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
<?php
// SQL query to retrieve data from the database
$sql = "SELECT description, timestamp FROM servo_log ORDER BY timestamp DESC LIMIT 10";
$result = mysqli_query($conn, $sql);

// Create two arrays to hold the labels and data for the chart
$labels = array();
$data = array();

// Loop through the query results and populate the arrays
while ($row = mysqli_fetch_assoc($result)) {
  $labels[] = $row['timestamp'];
  if ($row['description'] == "Pet Movement") {
    $data[] = 10; // set fixed height for "Pet Movement" bars
  } else if ($row['description'] == "Food dispensed") {
    $data[] = 15; // set fixed height for "Food dispensed" bars
  }
}

// Create a bar chart using Chart.js
?>
<div>
  <canvas id="myChart"></canvas>
</div>
<script>
var ctx = document.getElementById('myChart').getContext('2d');
var chart = new Chart(ctx, {
  type: 'bar',
  data: {
    labels: <?php echo json_encode($labels); ?>,
    datasets: [{
      label: 'Servo Log',
      backgroundColor: 'rgba(0, 0, 255, 0.5)', // blue color for "Pet Movement" bars
      data: <?php echo json_encode($data); ?>
    }, {
      label: 'Servo Log',
      backgroundColor: 'rgba(255, 0, 0, 0.5)', // red color for "Food dispensed" bars
      data: <?php echo json_encode($data); ?>
    }]
  },
  options: {
    scales: {
      yAxes: [{
        ticks: {
          beginAtZero: true
        }
      }]
    }
  }
});
</script>
</html>