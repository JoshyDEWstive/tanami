<?php

	include('DataHandler.php');
	
	if(isset($_GET['action'])) {
		if($_GET['action'] == "next-queue") {
			SearchNextQueue();
			header("Location: index.php");
			echo "<meta http-equiv='refresh' content='0;url=index.php'>";
		}
	}
	

?>