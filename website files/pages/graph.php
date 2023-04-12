<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <title>Document</title>
</head>
<?php
// PHP code to connect to MySQL database
$host = "34.132.166.21";
$username = "root";
$password = "password";
$dbname = "csi4160-grot-db";
    
// Check if connection to database is successful
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
  die("connection failed: " . $conn->connect_error);
}
// Query the database for servo logs within the past 24 hours
$query = "SELECT timestamp, description FROM servo_log WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 1 DAY)";
$result = mysqli_query($conn, $query);

// Initialize arrays for chart data
$labels = array();
$pet_movement_data = array();
$food_dispensed_data = array();

// Loop through the query results and populate the arrays
while ($row = mysqli_fetch_assoc($result)) {
  // Format the timestamp as a string
  $timestamp = date('Y-m-d H:i:s', strtotime($row['timestamp']));
  
  // Check if the timestamp is zero and the description is "Servo Opened" or "Servo Closed"
  if ($row['description'] == "Servo Opened" || $row['description'] == "Servo Closed") {
    // Skip adding the timestamp to the labels array
    continue;
  }
  
  // Add the timestamp to the labels array
  $labels[] = $timestamp;
  
  // Check the description and add the data to the appropriate array
  if ($row['description'] == "Pet Movement") {
    $pet_movement_data[] = 1;
    $food_dispensed_data[] = null;
  } else if ($row['description'] == "Food dispensed") {
    $pet_movement_data[] = null;
    $food_dispensed_data[] = 1;
  } else {
    $pet_movement_data[] = null;
    $food_dispensed_data[] = null;
  }
}

// Close the database connection
mysqli_close($conn);
?>

<!-- Create a bar chart using Chart.js -->
<div style="width: 1500px;"
     >
  <canvas id="myChart"></canvas>
</div>
<script>
var ctx = document.getElementById('myChart').getContext('2d');
var chart = new Chart(ctx, {
  type: 'bar',
  data: {
    labels: <?php echo json_encode($labels); ?>,
    datasets: [{
      label: 'Pet Movement',
      backgroundColor: 'rgba(0, 0, 255, 0.5)', // blue color for "Pet Movement" bars
      data: <?php echo json_encode($pet_movement_data); ?>
    }, {
      label: 'Food Dispensed',
      backgroundColor: 'rgba(255, 0, 0, 0.5)', // red color for "Food dispensed" bars
      data: <?php echo json_encode($food_dispensed_data); ?>
    }]
  },
  options: {
    scales: {
      xAxes: [{
        type: 'time',
        time: {
          unit: 'hour',
          displayFormats: {
            hour: 'HH:mm'
          },
          tooltipFormat: 'YYYY-MM-DD HH:mm:ss'
        },
        ticks: {
          maxRotation: 90,
          minRotation: 90
        }
      }],
      yAxes: [{
        ticks: {
          beginAtZero: true
        }
      }]
    }
  }
});
</script>