<?php

date_default_timezone_set('UTC');

/* Load required lib files. */
session_start();
require_once('config.php');

mysql_connect(DB_SERVER,DB_USER,DB_PASS);
@mysql_select_db(DB_NAME) or die( "Unable to select database");


// Setup for every page
date_default_timezone_set('UTC');
$headerContent = makeLinksForm();


// Generates the top-right links for logged in users
function makeLinksForm() {
	
    if (isset($_SESSION['uid'])) {
    	$content = '<div><ul id="topmenu">';
        $content .= '<li><a href="/view" class="username">@' . $_SESSION['thisUser'] . '</a></li>';
    	$content .= '<li><a href="/view">view</a></li>';
    	$content .= '<li><a href="/enter">enter</a></li>';
    	$content .= '<li><a href="/manage">manage</a></li>';
    	$content .= '<li><a href="/configure">configure</a></li>';
    	$content .= '<li><a href="/logout">log out</a></li>';
        $content .= '</ul></div>';
    }
	return $content;
}



// Update's a user's cached stats (for the top users table, etc.)
function updateCachedStats($uid) {
    $query = "SELECT * FROM records WHERE uid='" . mysql_real_escape_string($uid) . "' AND date>'" . mysql_real_escape_string(date("Y-m-d", strtotime("last sunday"))) . "'";
    $recordResult = mysql_query($query);
    
    $total = 0;
    $kept = 0;
    while ($record = mysql_fetch_assoc($recordResult)) {
        if ($record['kept'] == "YES") {
            $kept++;
            $total++;
        }
        if ($record['kept'] == "NO") {
            $total++;
        }
    }
    $recordsThisWeek = $total;
    
    if ($total > 0) {
        $percentThisWeek = round($kept/$total*100);
    } else {
        $percentThisWeek = 0;
    }
    
    $query = "SELECT * FROM promises WHERE uid='" . mysql_real_escape_string($uid) . "' AND active='1'";
    $promiseResult = mysql_query($query);
    $activePromises = mysql_num_rows($promiseResult);
    
    $query = "UPDATE users SET activepromises='" . mysql_real_escape_string($activePromises) . "' WHERE uid='" . mysql_real_escape_string($uid) . "'";
    mysql_query($query);
    
    $query = "UPDATE users SET percentthisweek='" . mysql_real_escape_string($percentThisWeek) . "' WHERE uid='" . mysql_real_escape_string($uid) . "'";
    mysql_query($query);
}

mysql_close();
