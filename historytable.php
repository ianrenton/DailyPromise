<?php

session_start();
require_once('auth.php');
require_once('twitteroauth/twitteroauth.php');
require_once('common.php');
require_once('config.php');

// DB connection
mysql_connect(DB_SERVER,DB_USER,DB_PASS);
@mysql_select_db(DB_NAME) or die( "Unable to select database");

// Get user id for display, either from a username lookup (/user/blah) or from session (/view).
if (isset($_GET['username'])) {
    $query = "SELECT * FROM users WHERE username='" . mysql_real_escape_string($_GET['username']) . "'";
    $userResult = mysql_query($query);
    $row = mysql_fetch_assoc($userResult);
    $uid = $row['uid'];
} else {
    $uid = $_SESSION['uid'];
}

$content = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <head>
	<link type="text/css" rel="stylesheet" media="all" href="/default.css" />

  </head>
  <body>';
$content .= makeHistoryTable($uid);
$content .= '</body></html>';
echo($content);

mysql_close();


function makeHistoryTable($uid) {

    $query = "SELECT * FROM promises WHERE uid='" . mysql_real_escape_string($uid) . "'";
    $promiseResult = mysql_query($query);
    
    $content .= "<table class=\"historytable\"><tr><td></td>";
    
    // Make the month headers
    $months = createDatesArray("F");
    $month1 = $months[0];
    for ($i = 1; $i <= 28; $i++) {
        if ($months[$i] != $month1) {
            $month2 = $months[$i];
            $switchpoint = $i;
            break;
        }
    }
    // Shorten if only 1 day width to fit it in
    if ($switchpoint == 1) {
        $month1 = substr($month1,0,3);
    } else if ($switchpoint == 27) {
        $month2 = substr($month2,0,3);
    }
    $content .= "<td class=\"month\" colspan=" . $switchpoint . ">" . $month1 . "</td>";
    if ($switchpoint < 28) {
        $content .= "<td class=\"month\" colspan=" . (28-$switchpoint) . ">" . $month2 . "</td>";
    }
    $content .= "</tr>";
    
    // Make the date headers
    $content .= "<tr><td></td>";
    $dates = createDatesArray("D<b\\r>jS");
    $today = date("D<b\\r>jS", strtotime("today"));
    foreach ($dates as $date) {
        // Highlight today's date
        if ($date == $today) {
            $content .= "<td class=\"date\"><strong>" . $date . "</strong></td>";
        } else {
            $content .= "<td class=\"date\">" . $date . "</td>";
        }
    }
    $content .= "</tr>";
    $dates = createDatesArray("Y-m-d");
    while ($promise = mysql_fetch_assoc($promiseResult)) {
        $content .= "<tr><td class=\"promise\">" . $promise['promise'] . "</td>";
        foreach ($dates as $date) {
            $query = "SELECT * FROM records WHERE uid='" . mysql_real_escape_string($uid) . "' AND pid='" . mysql_real_escape_string($promise['pid']) . "' AND date='" . mysql_real_escape_string($date) . "'";
            $recordResult = mysql_query($query);
            if (mysql_num_rows($recordResult) > 0) {
                $row = mysql_fetch_assoc($recordResult);
                if ($row['kept'] == "YES") {
                    $color = "#88ff88";
                } else if ($row['kept'] == "NO") {
                    $color = "#ff8888";
                } else /* WAITING */ {
                    $color = "#bbbbbb";
                }
            } else {
                $color = "#dddddd";
            }
            $content .= "<td style=\"background:" . $color . "\">" . $kept . "</td>";
        }
        $content .= "</tr>";
        
    }
    // User is generating their own table, offer a link to add more promises.
    if ($_SESSION['uid'] == $uid) {
        $content .= "<tr><td class=\"promise\"><a href=\"/manage\">add a";
        if (mysql_num_rows($promiseResult) > 0) { $content .= "nother"; }
        $content .= " promise?</a></td></tr>";
    } else if (mysql_num_rows($promiseResult) == 0) {
        // Other user's blank table
        $content .= "<tr><td class=\"promise\">no promises set yet!</td></tr>";
    }
    
    $content .= "</table>";
    
    return $content;
    
}

function createDatesArray($format) {
    $days = array();
    $y = date("y", strtotime("sunday"));
    $m = date("m", strtotime("sunday"));
    $d = date("d", strtotime("sunday"));
    for ($i=27; $i>=0; $i--) {
        $days[] = date($format, mktime(0,0,0,$m,($d-$i),$y));
    }
    return $days;
}
