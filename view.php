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
    
    // Insert today's "waiting" records for active user if they're not already there
    addTodaysWaitingRecords();
}

// Build the main display
if ($uid != $_SESSION['uid']) {
    $content .= makeUserBio();
}
$content .= makeHistoryTable($uid);
if ($uid == $_SESSION['uid']) {
    $content .= makeEnterLink();
}
$content .= makeSummary($uid);
include('html.inc');

mysql_close();



function makeUserBio() {

    $content .= "<div class=\"centeredlistheader\">" . $_GET['username'] . "</div>";
    
    return $content;
}




function addTodaysWaitingRecords() {
    $date = date("Y-m-d", strtotime("today"));
    
    // Get list of promises
    $query = "SELECT * FROM promises WHERE uid='" . mysql_real_escape_string($_SESSION['uid']) . "' AND active='1'";
    $promiseResult = mysql_query($query);
    
    // For each promise...
    while ($promise = mysql_fetch_assoc($promiseResult)) {
        // Check for existing entry for today
        $query = "SELECT * FROM records WHERE uid='" . mysql_real_escape_string($_SESSION['uid']) . "' AND pid='" . mysql_real_escape_string($promise['pid']) . "' AND date='" . mysql_real_escape_string($date) . "'";
        $recordResult = mysql_query($query);
        
        // If absent, add a "waiting" entry.
        if (mysql_num_rows($recordResult) == 0) {
            $query = "INSERT INTO records VALUES(NULL, '" . mysql_real_escape_string($_SESSION['uid']) . "', '" . mysql_real_escape_string($promise['pid']) . "', '" . mysql_real_escape_string($date) . "', 'WAITING')";
            mysql_query($query);
        }
    }
}



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


function makeEnterLink() {
    $date = date("Y-m-d", strtotime("today"));
    
    $content = "<ul class=\"enterlinks\">";
    
    // Find the earliest "WAITING" record.
    $query = "SELECT * FROM records WHERE uid='" . mysql_real_escape_string($_SESSION['uid']) . "' AND kept='WAITING' ORDER BY date ASC";
    $recordResult = mysql_query($query);
    if (mysql_num_rows($recordResult) > 0) {
        $record = mysql_fetch_assoc($recordResult);
        $earliestDate = $record['date'];
        // If it's not today, prompt to fill in a past date.
        if ($earliestDate != $date) {
            $content .= "<li><a href=\"/enter/" . $earliestDate . "\">Fill in data for " . date("l jS F", strtotime($earliestDate)) . "</a></li>";
        }
    }
    
    // Does today have any data yet?
    $query = "SELECT * FROM records WHERE uid='" . mysql_real_escape_string($_SESSION['uid']) . "' AND date='" . mysql_real_escape_string($date) . "' AND kept!='WAITING'";
    $recordResult = mysql_query($query);
    if (mysql_num_rows($recordResult) > 0) {
        $content .= "<li><a href=\"/enter/" . $date . "\">Update today's data</a></li>";
    } else {
        $content .= "<li><a href=\"/enter/" . $date . "\">Add data for today</a></li>";
    }
            
    $content .= "</ul>";
    
    return $content;
}


function makeSummary($uid) {
    
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
    
    // Grammar for looking at other users' profiles
    $userString = "you";
    $hasString = "you have";
    if ($uid != $_SESSION['uid']) {
        $query = "SELECT * FROM users WHERE uid='" . mysql_real_escape_string($uid) . "'";
        $userResult = mysql_query($query);
        $row = mysql_fetch_assoc($userResult);
        $userString = $row['username'];
        $hasString = $row['username'] . " has";
    }
    
    if ($total > 0) {
        $thisWeek = round($kept/$total*100);
        $content .= "<div class=\"summary\"><div>So far this week " . $hasString . " scored " . $kept . " points out of a maximum of " . $total . ", or:</div>";
        $content .= "<p class=\"percentage\">" . $thisWeek . "%</p>";
    } else {
        $content .= "<div class=\"summary\"><div>Nothing has been logged yet this week.</div>";
    }
    
    $query = "SELECT * FROM records WHERE uid='" . mysql_real_escape_string($uid) . "' AND date<='" . mysql_real_escape_string(date("Y-m-d", strtotime("last sunday"))) . "' AND date>'" . mysql_real_escape_string(date("Y-m-d", strtotime("last sunday - 7"))) . "'";
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
    
    if ($total > 0) {
        $lastWeek = round($kept/$total*100);
        $content .= "<div>Last week " . $userString . " scored " . $kept . " points out of a maximum of " . $total . ", or:</div>";
        $content .= "<p class=\"percentage\">" . $lastWeek . "%</p>";
    } else {
        $content .= "<div>Nothing was logged last week.</div>";
    }
    
    $content .= "<p class=\"improvement\">";
    if ($recordsThisWeek == 0) {
        $content .= "Let's see how you do this week.";
    } else if (($lastWeek == $thisWeek) && ($lastWeek == 100)) {
        $content .= "Perfect all around, you're an inspiration!";
    } else if (($lastWeek > $thisWeek) && ($lastWeek == 100)) {
        $content .= "I guess perfection is hard to live up to!";
    } else if (($lastWeek > $thisWeek) && ($lastWeek < $thisWeek + 15)) {
        $content .= "Come on, you can get there!";
    } else if ($lastWeek > $thisWeek) {
        $content .= "You've got some work to do!";
    } else if (($lastWeek > $thisWeek) && ($lastWeek > $thisWeek + 50)) {
        $content .= "Are you having a difficult week?";
    } else if ($thisWeek == 100) {
        $content .= "You're on top form this week!";
    } else if (($lastWeek < $thisWeek) && ($lastWeek < $thisWeek - 40)) {
        $content .= "What an improvement! Keep it up!";
    } else if ($lastWeek < $thisWeek) {
        $content .= "Getting better by the day!";
    } else {
        $content .= "No news is good news?";
    }
    $content .= "</p></div>";
    
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
