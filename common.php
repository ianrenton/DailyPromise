<?php

/* Load required lib files. */
session_start();
require_once('config.php');

mysql_connect(DB_SERVER,DB_USER,DB_PASS);
@mysql_select_db(DB_NAME) or die( "Unable to select database");


// Setup for every page
setTimeZoneByOffset($_SESSION['utcOffset']);
$headerContent = makeLinksForm();


// Hack to set the PHP timezone from Twitter's UTC offset value.
// For non-logged-in users, $offset will be null (=>0) so it
// will use Europe/London.
function setTimeZoneByOffset($offset) {
	$offset = ((float)$offset)/3600.0;
	$timezones = array( 
        '-12'=>'Pacific/Kwajalein', 
        '-11'=>'Pacific/Samoa', 
        '-10'=>'Pacific/Honolulu', 
        '-9'=>'America/Juneau', 
        '-8'=>'America/Los_Angeles', 
        '-7'=>'America/Denver', 
        '-6'=>'America/Mexico_City', 
        '-5'=>'America/New_York', 
        '-4'=>'America/Caracas', 
        '-3.5'=>'America/St_Johns', 
        '-3'=>'America/Argentina/Buenos_Aires', 
        '-2'=>'Atlantic/Azores',// no cities here so just picking an hour ahead 
        '-1'=>'Atlantic/Azores', 
        '0'=>'Europe/London', 
        '1'=>'Europe/Paris', 
        '2'=>'Europe/Helsinki', 
        '3'=>'Europe/Moscow', 
        '3.5'=>'Asia/Tehran', 
        '4'=>'Asia/Baku', 
        '4.5'=>'Asia/Kabul', 
        '5'=>'Asia/Karachi', 
        '5.5'=>'Asia/Calcutta', 
        '6'=>'Asia/Colombo', 
        '7'=>'Asia/Bangkok', 
        '8'=>'Asia/Singapore', 
        '9'=>'Asia/Tokyo', 
        '9.5'=>'Australia/Darwin', 
        '10'=>'Pacific/Guam', 
        '11'=>'Asia/Magadan', 
        '12'=>'Asia/Kamchatka');
	date_default_timezone_set($timezones[$offset]);
} 



// Generates the top-right links for logged in users
function makeLinksForm() {
	
    if (isset($_SESSION['uid'])) {
    	$content = '<div><ul id="topmenu">';
        $content .= '<li><a href="/view" class="username">@' . $_SESSION['thisUser'] . '</a></li>';
    	$content .= '<li><a href="/view">view</a></li>';
    	$content .= '<li><a href="/enter">enter</a></li>';
    	$content .= '<li><a href="/manage">manage</a></li>';
        $content .= '<li><a href="/friends">friends</a></li>';
    	$content .= '<li><a href="/configure" class="lowpriority">configure</a></li>';
    	$content .= '<li><a href="/logout" class="lowpriority">log out</a></li>';
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


function addTodaysWaitingRecords($uid) {
    $date = date("Y-m-d", strtotime("today"));
    
    // Get list of promises
    $query = "SELECT * FROM promises WHERE uid='" . mysql_real_escape_string($uid) . "' AND active='1'";
    $promiseResult = mysql_query($query);
    
    // For each promise...
    while ($promise = mysql_fetch_assoc($promiseResult)) {
        // Check for existing entry for today
        $query = "SELECT * FROM records WHERE uid='" . mysql_real_escape_string($uid) . "' AND pid='" . mysql_real_escape_string($promise['pid']) . "' AND date='" . mysql_real_escape_string($date) . "'";
        $recordResult = mysql_query($query);
        
        // If absent, add a "waiting" entry.
        if (mysql_num_rows($recordResult) == 0) {
            $query = "INSERT INTO records VALUES(NULL, '" . mysql_real_escape_string($uid) . "', '" . mysql_real_escape_string($promise['pid']) . "', '" . mysql_real_escape_string($date) . "', 'WAITING')";
            mysql_query($query);
        }
    }
}

mysql_close();
