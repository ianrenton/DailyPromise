<?php

// Load required lib files.
session_start();
require_once('auth.php');
require_once('config.php');
require_once('common.php');

// Require authentication for this page
auth();

// DB Connection
mysql_connect(DB_SERVER,DB_USER,DB_PASS);
@mysql_select_db(DB_NAME) or die( "Unable to select database");

// Get list of promises
$query = "SELECT * FROM promises WHERE uid='" . mysql_real_escape_string($_SESSION['uid']) . "' AND active='1'";
$promiseResult = mysql_query($query);

// For each promise...
while ($promise = mysql_fetch_assoc($promiseResult)) {
  // Get the value returned
  $kept = $_POST[$promise['pid']];
  
  if (strcmp($kept, "YES") == 0 || $promise["days"] == 7) {
    update($promise, $kept);
  } else {
    
    $query = "SELECT count(*) FROM records WHERE uid='" . mysql_real_escape_string($_SESSION['uid']) . "' AND pid='" . mysql_real_escape_string($promise['pid']) . "' AND kept='NA' AND date > '" . date("Y-m-d", strtotime("last sunday", strtotime($_POST['date']))) . "' AND date < '" . mysql_real_escape_string($_POST['date']) . "'";
    // Look up how many times this has been kept and decide whether the entry is NA or NO.
    $nacount = mysql_fetch_assoc(mysql_query($query));
    $nacount = (int) $nacount['count(*)'];
    $daystokeep = (int) $promise['days'];
    if ($nacount < (7 - $daystokeep)) {
      update($promise, 'NA');
    } else {
      update($promise, 'NO');
    }
  }
}

mysql_close();

header('Location: /enter/' . $_POST['date'] . '/done');
die();


function update($promise, $kept) {
        // Check for existing entries for that day
        $query = "SELECT * FROM records WHERE uid='" . mysql_real_escape_string($_SESSION['uid']) . "' AND pid='" . mysql_real_escape_string($promise['pid']) . "' AND date='" . mysql_real_escape_string($_POST['date']) . "'";
        $recordResult = mysql_query($query);
        if (mysql_num_rows($recordResult) > 0) {
            // Update the record if it exists
            $query = "UPDATE records SET kept='" . $kept . "' WHERE uid='" . mysql_real_escape_string($_SESSION['uid']) . "' AND pid='" . mysql_real_escape_string($promise['pid']) . "' AND date='" . mysql_real_escape_string($_POST['date']) . "'";
            mysql_query($query);
        } else {
            // Otherwise, add a record to the database
            $query = "INSERT INTO records VALUES(NULL, '" . mysql_real_escape_string($_SESSION['uid']) . "', '" . mysql_real_escape_string($promise['pid']) . "', '" . mysql_real_escape_string($_POST['date']) . "', '" . $kept . "')";
            mysql_query($query);
        }
        // Something has changed, update the cached stats
        updateCachedStats($_SESSION['uid']);
}