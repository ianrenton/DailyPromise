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
    // If non-blank, i.e. the user did actually check "Yes" or "No"...
    if ($_POST[$promise['pid']] != "") {
        // Get the value returned
        $kept = ($_POST[$promise['pid']] == "true")?"YES":"NO";
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
}

mysql_close();

header('Location: /enter/' . $_POST['date'] . '/done');
die();


