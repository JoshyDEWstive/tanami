<?php

  include("constants.php");

  $SQL_HOSTNAME = "localhost";
  $SQL_NAME = "root";
  $SQL_PASS = "root";

//SetupDatabase();

AddSite("https://stackoverflow.com/");

  function SetupDatabase() {
    global $SQL_HOSTNAME,$SQL_NAME,$SQL_PASS;

    AppLog("Loading databases");

    $conn = new mysqli($SQL_HOSTNAME, $SQL_NAME, $SQL_PASS);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "CREATE DATABASE search_engine;"; // Create the DATABASE

    if ($conn->query($sql) === TRUE) {
        echo "Database created successfully ($sql)";
    } else {
        echo "Error creating database: ($sql) <br>" . $conn->error;
    }

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

  function AddSite($url) {
    global $IGNORE_WORDS;
    global $SQL_HOSTNAME,$SQL_NAME,$SQL_PASS;

    AppLog("Adding site {$url}");

    $title = "";
    $keywords = "";
    $description = "";
    $author = "";

    // Get website

    $site_contents = GetPageContents($url);


    // Get sites META tags if any and add to records
    $meta_tags = get_meta_tags($site_contents);
    $author = $meta_tags["author"];
    $keywords = $meta_tags["keywords"];
    $description = $meta_tags["description"];


    // Read the site and gather all relevent keywords
    $title = preg_match('/<title[^>]*>(.*?)<\/title>/ims', $site_contents, $match) ? $match[1] : "";

    AppLog("Website Information: ");
    AppLog("Title: {$title}");
    AppLog("Author: {$author}");
    AppLog("Keywords: {$keywords}");
    AppLog("Description: {$description}");

    //TODO: Read the site to find keywords not just the title

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
    foreach($links as $link) {
      if($link == "/") continue;
      else if($link == $url) continue;
      else if($link == substr($url,0,-1)) continue;
      else if($link[0] == "/") $link = $url.$link;
      else if($link[0] == "#") continue;
      else if(strpos($link, 'http') !== false) continue;

      AddToQueue($link);
    }

    //TODO: Add the information to the database

    AppLog("Reading body");

    $bodies = $doc->getElementsByTagName('body');
    assert($bodies->length === 1);
    $body = $bodies->item(0);
    for ($i = 0; $i < $body->children->length; $i++) {
        $body->remove($body->children->item($i));
    }
    $stringbody = $doc->saveHTML($body);


    $adjusted_body = trim(strtolower(htmlspecialchars(strip_tags($stringbody." ".$keywords))));

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

    $keys = explode(" ", $adjusted_body);
    AppLog("Found ".count($keys)." keys");

    $keys = array_diff($keys,$IGNORE_WORDS);
    $keys = array_filter($keys,'strlen');
    $keys = array_unique($keys);
    AppLog(count($keys)." keys after trimmed");

    $keywords = implode(",",$keys);
    $encoding = mb_detect_encoding($keywords);
    $keywords = htmlspecialchars($keywords, ENT_QUOTES | ENT_SUBSTITUTE, $encoding);
    $title = $title;
    $description = htmlspecialchars($description);
    $author = htmlspecialchars($author);

    $conn = new mysqli($SQL_HOSTNAME, $SQL_NAME, $SQL_PASS,"search_engine");

    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    }


    $sql = "SELECT * FROM `records` WHERE url='{$url}'";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
      // Update
      AppLog("Updating record");
      $sql = "UPDATE `records` SET title='$title', keywords='$keywords',description='$description',author='$author' WHERE url='$url'";
    } else {
      // Insert
      AppLog("Inserting record");
      $sql = "INSERT INTO `records` (`id`, `title`, `keywords`, `description`, `author`, `url`) VALUES (NULL, '$title', '$keywords', '$description', '$author', '{$url}')";

    }
    if ($conn->query($sql) === TRUE) {
        AppLog("Record updated or created successfully");
    } else {
        AppLog("Error creating record: ($sql) <br>" . $conn->error);
    }
    AppLog("Done!");
  }

  function AddToQueue($url) {
    global $SQL_HOSTNAME,$SQL_NAME,$SQL_PASS;

    $conn = new mysqli($SQL_HOSTNAME, $SQL_NAME, $SQL_PASS,"search_engine");

    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    }

    $url = htmlspecialchars($url);
    $sql = "SELECT * FROM `queue` WHERE url='$url'";
    $result = $conn->query($sql);

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

  function GetPageContents($url) {

    echo "Getting page contects for {$url}";

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

  function AppLog($str) {
    echo "Log: $str <br>";
  }
 ?>
