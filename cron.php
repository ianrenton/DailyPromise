<?php

session_start();
require_once('config.php');
require_once('common.php');

// DB connection
mysql_connect(DB_SERVER,DB_USER,DB_PASS);
@mysql_select_db(DB_NAME) or die( "Unable to select database");


$query = "SELECT * FROM users";
$userResult = mysql_query($query);
while ($user = mysql_fetch_assoc($userResult)) {
    updateCachedStats($user['uid']);
	addTodaysWaitingRecords($user['uid']);
}


mysql_close();

?>

