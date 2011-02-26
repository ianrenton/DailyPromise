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

// Old promise activation
if (isset($_GET['activate'])) {
    $query = "UPDATE promises SET active='1' WHERE uid='" . mysql_real_escape_string($_SESSION['uid']) . "' AND pid='" . mysql_real_escape_string($_GET['activate']) . "'";
    mysql_query($query);
    // Add a "WAITING" for today, if there's no data currently
    $query = "SELECT * FROM records WHERE uid='" . mysql_real_escape_string($_SESSION['uid']) . "' AND pid='" . mysql_real_escape_string($_GET['activate']) . "' AND date='" . mysql_real_escape_string(date("Y-m-d", strtotime("today"))) . "'";
    $recordResult = mysql_query($query);
    if (mysql_num_rows($recordResult) == 0) {
        $query = "INSERT INTO records VALUES(NULL, '" . mysql_real_escape_string($_SESSION['uid']) . "', '" . mysql_real_escape_string($_GET['activate']) . "', '" . mysql_real_escape_string(date("Y-m-d", strtotime("today"))) . "', 'WAITING')";
        mysql_query($query);
        // Something has changed, update the cached stats
        updateCachedStats($_SESSION['uid']);
    }
	mysql_close();
	header('Location: /manage/done/' . $_GET['activate']);
	die();
}

// Current promise deactivation
if (isset($_GET['deactivate'])) {
    $query = "UPDATE promises SET active='0' WHERE uid='" . mysql_real_escape_string($_SESSION['uid']) . "' AND pid='" . mysql_real_escape_string($_GET['deactivate']) . "'";
    mysql_query($query);
    // Remove all "WAITING" fields
    $query = "DELETE FROM records WHERE uid='" . mysql_real_escape_string($_SESSION['uid']) . "' AND pid='" . mysql_real_escape_string($_GET['deactivate']) . "' AND kept='WAITING'";
    mysql_query($query);
    // Something has changed, update the cached stats
    updateCachedStats($_SESSION['uid']);
	mysql_close();
	header('Location: /manage/done');
	die();
}

// New promise adding
if (isset($_POST['newpromise'])) {
    // Get the right PID
    $query = "SELECT MAX(pid) FROM promises WHERE uid='" . mysql_real_escape_string($_SESSION['uid']) . "'";
    $result = mysql_query($query);
    $row = mysql_fetch_assoc($result);
    $newPID = $row['MAX(pid)'] + 1;
    
    // Add promise to table, activate and add a "WAITING" for today.
    $query = "INSERT INTO promises VALUES(NULL, '" . mysql_real_escape_string($_SESSION['uid']) . "', '" . mysql_real_escape_string($newPID) . "', '" . mysql_real_escape_string($_POST['newpromise']) . "', '1', '" . mysql_real_escape_string($_POST['days']) . "')";
    mysql_query($query);
    $query = "INSERT INTO records VALUES(NULL, '" . mysql_real_escape_string($_SESSION['uid']) . "', '" . mysql_real_escape_string($newPID) . "', '" . mysql_real_escape_string(date("Y-m-d", strtotime("today"))) . "', 'WAITING')";
    mysql_query($query);
    // Add "WAITING" for yesterday if requested.
    if (isset($_POST['doyesterday'])) {
        $query = "INSERT INTO records VALUES(NULL, '" . mysql_real_escape_string($_SESSION['uid']) . "', '" . mysql_real_escape_string($newPID) . "', '" . mysql_real_escape_string(date("Y-m-d", strtotime("yesterday"))) . "', 'WAITING')";
        mysql_query($query);
    }
    // Something has changed, update the cached stats
    updateCachedStats($_SESSION['uid']);
	mysql_close();
	header('Location: /manage/done/' . $newPID);
	die();
}

// Promise deletion
if (isset($_GET['delete'])) {
    $query = "DELETE FROM promises WHERE uid='" . mysql_real_escape_string($_SESSION['uid']) . "' AND pid='" . mysql_real_escape_string($_GET['delete']) . "'";
    mysql_query($query);
    // Remove all records
    $query = "DELETE FROM records WHERE uid='" . mysql_real_escape_string($_SESSION['uid']) . "' AND pid='" . mysql_real_escape_string($_GET['delete']) . "'";
    mysql_query($query);
	mysql_close();
	header('Location: /manage/done');
	die();
}

mysql_close();
header('Location: /manage');
die();