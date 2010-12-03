<?php

/* Load required lib files. */
session_start();
require_once('auth.php');
require_once('config.php');
require_once('common.php');

// Require authentication for this page
auth();

mysql_connect(DB_SERVER,DB_USER,DB_PASS);
@mysql_select_db(DB_NAME) or die( "Unable to select database");

// Set-invisible GET from the first-time privacy notice
if (isset($_GET['makeinvisible'])) {
    $query = "UPDATE users SET visible='0' WHERE uid='" . mysql_real_escape_string($_SESSION['uid']) . "'";
    mysql_query($query);
    header('Location: /manage/nowinvisible');
    die();
}

if (isset($_POST['visible'])) {
    // Set-visible POST from the Config page
    $query = "UPDATE users SET visible='1' WHERE uid='" . mysql_real_escape_string($_SESSION['uid']) . "'";
    mysql_query($query);
    
} else {
    // Set-invisible POST from the Config page
    $query = "UPDATE users SET visible='0' WHERE uid='" . mysql_real_escape_string($_SESSION['uid']) . "'";
    mysql_query($query);
    
}

header('Location: /configure/visibilityset');
die();

?>