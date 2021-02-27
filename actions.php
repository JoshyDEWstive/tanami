<?php

	include('DataHandler.php');
	
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
		if(isset($_POST['return']) && isset($_POST['action'])) {
			
			$action = htmlspecialchars($_POST['action']);
			
			if($action == "next-queue") {
				SearchNextQueue();
				echo "done";
			} else if($action == "record-count") {
				$conn = new mysqli($SQL_HOSTNAME, $SQL_NAME, $SQL_PASS,"search_engine");

				if ($conn->connect_error) {
					die("Connection failed: " . $conn->connect_error);
				}

				$sql = "SELECT Count(*) FROM records";
				
				$recordsCount = $conn->query($sql);
				if($recordsCount->num_rows > 0) while ($row = mysqli_fetch_assoc($recordsCount)) { $recordsCount = $row['Count(*)']; break; }
				echo $recordsCount;
			}
			
		}
	}
	
	AddSite("https://en.wikipedia.org/wiki/Main_Page");
?>