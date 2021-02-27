<?php

  ///////////////////////////////////////////////////////////
  // Tanami Search Engine Project
  //////////////////////
  // Version 0.1.1
  //
  // constants.php
  //
  // File Change Log
  // 
  // Only keep the latest TWO (2) versions of the change log here.
  // Old ones should be added to Universal Changelog. Include file there.
  //
  // [constants.php] 0.1.1:
  // + Added SQL login information
  // + Added RETURN_LIMIT variable
  // + Added Sorting multipliers
  // + Added SEARCH_THRESHOLD variable
  //
  ///////////////////////////////////////////////////////////
  // Openic Development (C) 2021 All Rights Reserved
  // Author: Joshua Mulik
  // Contact: joshydewstive@outlook.com
  ///////////////////////////////////////////////////////////
  
  $IGNORE_WORDS = array('i','a','about',
  'an','and','are','as','at','be','by',
  'com','de','en','for','from','in',
  'is','it','la','of','on','or','that','the',
  'this','to','with','und','the','www');

  $SQL_HOSTNAME = "localhost";
  $SQL_NAME = "root";
  $SQL_PASS = "root";
  
  $RETURN_LIMIT = 100; // How many urls a search can provide

  // Sorting multipliers
  $TITLE_MULTIPLIER = 4;
  $DESC_MULTIPLIER = 3;
  $KEYWORD_MULTIPLIER = 2;
  $URL_MULTIPLIER = 1;
  $DATE_MULTIPLIER = 2;
  
  $SEARCH_THRESHOLD = 2;
  
  $CURRENT_STATUS = "free";
  
 ?>
