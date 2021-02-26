<?php

  ///////////////////////////////////////////////////////////
  // Tanami Search Engine Project
  //////////////////////
  // Version 0.1.1
  //
  // DataHandler.php
  //
  // File Change Log
  // 
  // Only keep the latest TWO (2) versions of the change log here.
  // Old ones should be added to Universal Changelog. Include file there.
  //
  // [DataHandler.php] 0.1.1:
  // + Added change log
  // + Added header
  // + Added organisational comments
  // + Added code comments to help understand process
  // + Added more TODOs
  //
  // - Moved SQL login data to Constants.php
  //
  ///////////////////////////////////////////////////////////
  // Openic Development (C) 2021 All Rights Reserved
  // Author: Joshua Mulik
  // Contact: joshydewstive@outlook.com
  ///////////////////////////////////////////////////////////
  
  ///////////////////////////////////////////////////////////
  // Required Includes
  include("constants.php");

  ///////////////////////////////////////////////////////////
  // Initialisation and Main
  
  SetupDatabase();

  AddSite("https://stackoverflow.com/");

  SearchNextQueue();
  var_dump( RunSearch("overflow"));
  ///////////////////////////////////////////////////////////
  // Main Functions
  
  function SearchNextQueue() {
     global $SQL_HOSTNAME,$SQL_NAME,$SQL_PASS;

     $conn = new mysqli($SQL_HOSTNAME, $SQL_NAME, $SQL_PASS,"search_engine");

	  if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
      }

	  $sql = "SELECT * FROM `queue` LIMIT 1";
	  $result = $conn->query($sql);
	  if($result->num_rows > 0) {
		  
		// Loop through every row (should only be one)
		while($row = mysqli_fetch_assoc($result)) {
			
		   AddSite($row['url']);
		   
		   $sql = "DELETE FROM `queue` WHERE `queue`.`id` = ".$row['id'];
		   $conn->query($sql);
		}  
		
	  } else {
		 // No rows found
		 AppLog("Empty queue");
	  }

  }
  // Run the search algorithm
  function RunSearch($search) {
	  global $SQL_HOSTNAME,$SQL_NAME,$SQL_PASS,$RETURN_LIMIT;
	  // TODO: Add thresholds from constants.php to global and use in the scoring instead of constants
	  
	  // Find all SQL entries where title or keywords contain any of the keywords searched (TODO: include synonyms)
	  // From the entries increase the score of the entries with the most keywords (2 points each if in keywords. 4 points if in title. 3 points if in desc)
	  // Increase the score if it has been updated recently (2 point)
	  // Increase the score if any keyword is in the URL (1 point for each)
	  
	  // Connect to database
	  $conn = new mysqli($SQL_HOSTNAME, $SQL_NAME, $SQL_PASS,"search_engine");

	  if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
      }
	  
	  $search = trim(strtolower(htmlspecialchars(strip_tags($search))));

	  AppLog("Searching for $search");
	  // Run SQL search
	  
	  $sql = "SELECT * FROM `records`";
	  
	  $result = $conn->query($sql);
	  
	  // Initialise required variables
	  $urls = array();
	  $count = 0;

	  // If the search returned rows
	  if($result->num_rows > 0) {
		  
		// Loop through every row
		while($row = mysqli_fetch_assoc($result)) {
			
		   // Get row data 
		   $title = $row['title'];
		   $desc = $row['description'];
		   $keywords = $row['keywords'];
		   $url = $row['url'];

		   // Search all data
		   $titleScore = GetStringScore($title,$search) * 4;
		   $descScore = GetStringScore($desc,$search) * 3;
		   $keywordsScore = GetStringScore($keywords,$search) * 2;
		   $urlScore = GetStringScore($url,$search);
		   
		   // Get combined score
		   $totalScore = $titleScore + $descScore + $keywordsScore + $urlScore;
		   
		   // If total score is greater than 2 then add to the list 
		   // TODO: Adjust scores and cutoff
		   if($totalScore > 2) {
			   $urls[$url] = $totalScore;
			   $count = $count + 1;
			   
			   if($count >= $RETURN_LIMIT) break;
			   
		   } 
		   
		   
		}  
		
	  } else {
		 // No rows found
		 return "empty";
	  }
	  arsort($urls);
	  return $urls;
	  
  }
  // End function
  
  // Every time a word in the search is found in the comparator increase the returned score by one.
  function GetStringScore($search,$comparator) {
	  
	  $search = trim(strtolower(htmlspecialchars(strip_tags($search))));
	  $words = explode(" ",$search);
	  if(count($words) <= 0) $words = explode(",",$search);
	  $score = 0;
	  
	  foreach($words as $word) {
		  if(strpos($word,$comparator) !== false) $score = $score + 1;
	  }

	  AppLog("Was searching for $comparator found $score results");
	  return $score;
  }
  
  // Setting up the Search Engine's database
  function SetupDatabase() {
    global $SQL_HOSTNAME,$SQL_NAME,$SQL_PASS;
	
	// Loadding the databases
    AppLog("Loading databases");
	
	// TODO: Remove the database if it exists
	
    $conn = new mysqli($SQL_HOSTNAME, $SQL_NAME, $SQL_PASS);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

	$conn->query("DROP DATABASE IF EXISTS search_engine");

    $sql = "CREATE DATABASE search_engine;"; // Create the DATABASE

    if ($conn->query($sql) === TRUE) {
        echo "Database created successfully ($sql)";
    } else {
        echo "Error creating database: ($sql) <br>" . $conn->error;
    }

	// Creating required tables
	
    AppLog("Creating table");

    // Create records table
    $sql = "CREATE TABLE `search_engine`.`records` (
      `id` BIGINT NOT NULL AUTO_INCREMENT ,
      `title` VARCHAR(2000) NOT NULL ,
      `keywords` MEDIUMTEXT NOT NULL ,
      `description` VARCHAR(40000) NOT NULL ,
      `author` VARCHAR(2000) NOT NULL ,
      `url` VARCHAR(10000) NOT NULL ,
      PRIMARY KEY (`id`)) ENGINE = InnoDB;";

      if ($conn->query($sql) === TRUE) {
          echo "Records created successfully ($sql)";
      } else {
          echo "Error creating records: ($sql) <br>" . $conn->error;
      }

	// Create queue table
      $sql = "CREATE TABLE `search_engine`.`queue`
      ( `id` BIGINT NOT NULL AUTO_INCREMENT ,
         `url` VARCHAR(10000) NOT NULL ,
         PRIMARY KEY (`id`)) ENGINE = InnoDB;";

         if ($conn->query($sql) === TRUE) {
             echo "Queue created successfully ($sql)";
         } else {
             echo "Error creating queue: ($sql) <br>" . $conn->error;
         }

  }
  // End Function

  // Processing a website
  // Get all meta tags
  // Find all links and add to queue
  // Find all relevent keywords
  // Add website to record and update if it exists
  function AddSite($url) {
    global $IGNORE_WORDS;
    global $SQL_HOSTNAME,$SQL_NAME,$SQL_PASS;


    AppLog("Adding site {$url}");

    $title = "";
    $keywords = "";
    $description = "";
    $author = "";

    // Get website contents

    $site_contents = GetPageContents($url);


    // Get sites META tags if any and add to records
    $meta_tags = get_meta_tags($site_contents);
    $author = $meta_tags["author"];
    $keywords = $meta_tags["keywords"];
    $description = $meta_tags["description"];


    // Read the site and gather all relevent keywords
	
	// Find title
    $title = preg_match('/<title[^>]*>(.*?)<\/title>/ims', $site_contents, $match) ? $match[1] : "";

	// Log website data
    AppLog("Website Information: ");
    AppLog("Title: {$title}");
    AppLog("Author: {$author}");
    AppLog("Keywords: {$keywords}");
    AppLog("Description: {$description}");

	
    // Get a list of all the links
    $doc = new DOMDocument();
    $doc->loadHTML($site_contents); //helps if html is well formed and has proper use of html entities!

    $xpath = new DOMXpath($doc);

    $nodes = $xpath->query('//a');

    $links = array();
    foreach($nodes as $node) {
        $links[] = $node->getAttribute('href');
    }
    // Add the links to a queue

    // Add the links to Queue
    // If the link is a relative link add the url to the front of it.
    // Skip if #
	// Skip if it is the website's URl 
	// Skip if it is not an HTTP/HTTPS link
	
	if(substr($url,-1) != "/") $url = $url."/";

    foreach($links as $link) {
		
      if($link == "/") continue;
      else if($link == $url) continue;
      else if($link == substr($url,0,-1)) continue;
      else if($link[0] == "/") $link = $url.substr($link,1);
      else if($link[0] == "#") continue;
      else if(strpos($link, 'http') !== true) continue;

      AddToQueue($link);
    }


	// Reading the website's body to find keywords
    AppLog("Reading body");

	// Find all words in the body
    $bodies = $doc->getElementsByTagName('body');
    assert($bodies->length === 1);
    $body = $bodies->item(0);
    for ($i = 0; $i < $body->children->length; $i++) {
        $body->remove($body->children->item($i));
    }
    $stringbody = $doc->saveHTML($body);

	
	// Remove tags, HTML tags, whitespace and ensure it is lowercase
    $adjusted_body = trim(strtolower(htmlspecialchars(strip_tags($stringbody." ".$keywords))));
	
	// Remove unwanted characters
    $adjusted_body = str_replace(",","",$adjusted_body);
    $adjusted_body = str_replace("\'","",$adjusted_body);
    $adjusted_body = str_replace("\"","",$adjusted_body);
    $adjusted_body = str_replace("["," ",$adjusted_body);
    $adjusted_body = str_replace("]"," ",$adjusted_body);
    $adjusted_body = str_replace("{"," ",$adjusted_body);
    $adjusted_body = str_replace("}"," ",$adjusted_body);
    $adjusted_body = str_replace("("," ",$adjusted_body);
    $adjusted_body = str_replace(")"," ",$adjusted_body);
    $adjusted_body = str_replace("="," ",$adjusted_body);
    $adjusted_body = str_replace(";"," ",$adjusted_body);

	// Turn into an array
    $keys = explode(" ", $adjusted_body);
    AppLog("Found ".count($keys)." keys");

	// Remove duplicate keys, remove the 'ignore' words 
	// Remove empty keys

	for($i = 0; $i < count($keys);$i++) {
		$keys[$i] = trim($keys[$i]);
	}

    $keys = array_diff($keys,$IGNORE_WORDS);
    $keys = array_filter($keys,'strlen');
    $keys = array_unique($keys);
    AppLog(count($keys)." keys after trimmed");

	// Turn back into a string
    $keywords = implode(",",$keys);
	
	// Find encoding
    $encoding = mb_detect_encoding($keywords);
	
	// Remove bad characters
    $keywords = htmlspecialchars($keywords, ENT_QUOTES | ENT_SUBSTITUTE, $encoding);
	
	// Secures inputs
    $title = $title;
    $description = htmlspecialchars($description);
    $author = htmlspecialchars($author);
	
	// Connects to SQL
    $conn = new mysqli($SQL_HOSTNAME, $SQL_NAME, $SQL_PASS,"search_engine");

    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    }

	// Select record if it exists already
    $sql = "SELECT * FROM `records` WHERE url='{$url}'";
	
	// Run SQL Query
    $result = $conn->query($sql);
	
	// If there is a result, update the record
	// if not insert it
    if ($result->num_rows > 0) {
      // Update
      AppLog("Updating record");
      $sql = "UPDATE `records` SET title='$title', keywords='$keywords',description='$description',author='$author' WHERE url='$url'";
    } else {
      // Insert
      AppLog("Inserting record");
      $sql = "INSERT INTO `records` (`id`, `title`, `keywords`, `description`, `author`, `url`) VALUES (NULL, '$title', '$keywords', '$description', '$author', '$url')";

    }
	
	// Run SQL query and check 
    if ($conn->query($sql) === TRUE) {
        AppLog("Record updated or created successfully");
    } else {
        AppLog("Error creating record: ($sql) <br>" . $conn->error);
    }
	
    AppLog("Done!");
  }
  // End function
  
  // Add website to crawler que
  function AddToQueue($url) {
    global $SQL_HOSTNAME,$SQL_NAME,$SQL_PASS;

	// Create SQL connection
    $conn = new mysqli($SQL_HOSTNAME, $SQL_NAME, $SQL_PASS,"search_engine");
	
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    }
	
	// Secure the URL
    $url = htmlspecialchars($url);
	
	// Search if the URL is already in the queue
    $sql = "SELECT * FROM `queue` WHERE url='$url'";
	
	// Run SQL query
    $result = $conn->query($sql);

	// If the URL is not in the queue, add it to the queue
    if (!($result->num_rows > 0)) {
      $sql = "INSERT INTO `queue` (`id`, `url`) VALUES (NULL, '$url')";
      if($conn->query($sql)===TRUE) {
        echo $url." Added to queue. <br>";
      } else {
        echo $url." could not be added to queue (".$conn->error.") <br>";
      }
    } else {
      AppLog("Duplicate URL in queue");
    }

  }
  // End function 
  
  ///////////////////////////////////////////////////////////
  // Utility functions 
  
  // Read the website and get its contents using CURL
  function GetPageContents($url) {

    AppLog("Getting page contects for {$url}");

    // create curl resource
    $ch = curl_init();

    // set url
    curl_setopt($ch, CURLOPT_URL, $url);

    //return the transfer as a string
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');

    // $output contains the output string
    $output = curl_exec($ch);
	 
    // close curl resource to free up system resources
    curl_close($ch);

    return $output;
  }
  // End function

  // Log to the console
  // TODO: Save log to file
  function AppLog($str) {
    echo "Log: $str <br>";
  }
  // End function 
  
  
 ?>
