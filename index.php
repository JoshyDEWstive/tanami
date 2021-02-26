<?php
	
	include('DataHandler.php');
	
	
	$conn = new mysqli($SQL_HOSTNAME, $SQL_NAME, $SQL_PASS,"search_engine");

	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
    }
	
	$sql = "SELECT Count(*) FROM queue";
	
	$queueCount = $conn->query($sql);
	if($queueCount->num_rows > 0) while ($row = mysqli_fetch_assoc($queueCount)) { $queueCount = $row['Count(*)']; break; }
	
	$sql = "SELECT Count(*) FROM records";
	
	$recordsCount = $conn->query($sql);
	if($recordsCount->num_rows > 0) while ($row = mysqli_fetch_assoc($recordsCount)) { $recordsCount = $row['Count(*)']; break; }
	
?>

<html>

  <head>
    <title>Tanami Control Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Exo+2:wght@300&family=Press+Start+2P&display=swap" rel="stylesheet">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

    <link rel="stylesheet" type="text/css" href="css/main.css">

  </head>

  <body>
    
	<div class="row info-bar">
		
		<div class='col-xs-12 text-center'>
			<h1>Tanami Control Panel</h1>

			<table class="table">
				<thead>
					<tr>
						<th>Queue Size</th>
						<th>Records Size</th>
						<th>Database Size (MB)</th>
						<th>Records Size (MB)</th>
						<th>Queue Size (MB)</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><?php echo $queueCount; ?></td>
						<td><?php echo $recordsCount; ?></td>
						<td>0</td>
						<td>0</td>
						<td>0</td>
					</tr>
				</tbody>
			</table>
			
		</div>
	</div>
	
	<div class='row'>
	
		<div class="col-xs-12 col-md-4 changelog text-center">
			<div class="btn-group">
			  <a type="button" class="btn btn-primary" href='index.php' >Reload Page</a>
			  <a type="button" class="btn btn-success" href='actions.php?action=next-queue'>Next Queue</a>
			  <a type="button" class="btn btn-danger">Delete Queue</a>
			</div>
			
			<hr>
			
		</div>
		
		<div class="col-xs-12 col-md-8 searchtest">
		
		</div>
	
	</div>
	
  </body>

</html>
