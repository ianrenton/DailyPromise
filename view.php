<?php

session_start();
require_once('twitteroauth/twitteroauth.php');
require_once('auth.php');
require_once('common.php');
require_once('config.php');

// DB connection
mysql_connect(DB_SERVER,DB_USER,DB_PASS);
@mysql_select_db(DB_NAME) or die( "Unable to select database");

// Get user id for display, either from a username lookup (/user/blah) or from session (/view).
if (isset($_GET['username'])) {
    $query = "SELECT * FROM users WHERE username='" . mysql_real_escape_string($_GET['username']) . "' AND visible='1'"; // Only visible users!
    $userResult = mysql_query($query);
    $row = mysql_fetch_assoc($userResult);
    $uid = $row['uid'];
    $twitter_uid = $row['twitter_uid'];
    $titleText = " - @" . $_GET['username'] . "'s Profile";
} else {
	// Require authentication for this page if running from session id
	auth();
    $uid = $_SESSION['uid'];
    $titleText = " - My View";
    
    // Insert today's "waiting" records for active user if they're not already there
    addTodaysWaitingRecords($uid);
}

// Build the main display
if ($uid != "") {
    if ($uid != $_SESSION['uid']) {
        $content .= makeUserBio($uid, $twitter_uid);
    }
    $content .= makeHistoryTable($uid);
    if ($uid == $_SESSION['uid']) {
        $content .= makeEnterLink();
    }
    $content .= makeSummary($uid);
} else {
    $content = "<div class=\"centeredlistheader\">@" . $_GET['username'] . " is not registered on Daily Promise.</div>";
    $content .= "<div class=\"backtoview\"><a href=\"/\">Go back to the home page</a></div>";
}

include('html.inc');

mysql_close();



function makeUserBio($uid, $twitter_uid) {
	// Get a Twitter object
	$to = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET);

	// Get avatar etc
	$lookupResult = $to->get('users/show', array('user_id' => $twitter_uid));
	$avatarURL = $lookupResult['profile_image_url'];
	$bio = $lookupResult['description'];
	
	// Get User since
	$query = "SELECT * FROM records WHERE uid='" . mysql_real_escape_string($uid) . "' ORDER BY date ASC";
    $recordResult = mysql_query($query);
	$row = mysql_fetch_assoc($recordResult);
	$userSince = date("jS F Y", strtotime($row['date']));
	if ($userSince == "1st January 1970") {
		$userSince = "Daily Promise lurker";
	} else {
		$userSince = "Daily Promise user since " . $userSince;
	}
	
	$followboxHTML = "<div class=\"followbox\"><span id=\"follow-twitterapi\"></span></div>
						<script type=\"text/javascript\">
						  twttr.anywhere(function (T) {
						    T('#follow-twitterapi').followButton(\"" . $_GET['username'] . "\");
						  });
						</script>";
						
    $content .= "<div class=\"usernameheader\">" . $followboxHTML . "<div class=\"avatar\"><img src=\"" . $avatarURL . "\" /></div><div class=\"username\">@" . $_GET['username'] . "</div><div class=\"bio\">" . $bio . "</div><div class=\"bio\">" . $userSince . "</div></div>";
    
    return $content;
}



function makeHistoryTable($uid) {

    $query = "SELECT * FROM promises WHERE uid='" . mysql_real_escape_string($uid) . "' AND active='1'";
    $promiseResult = mysql_query($query);
    
    $content .= "<div class=\"historytable\"><table class=\"historytable\"><tr><td></td>";
    
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
    $displayDates = createDatesArray("D<b\\r>jS");
    $checkDates = createDatesArray("j F");
    $today = date("D<b\\r>jS", strtotime("today"));
	for ($i = 0; $i < 28; $i++) {
        // Highlight today's date, or render icons
        if ($checkDates[$i] == "1 January") {
            $content .= "<td class=\"date " . $class . "\"><img src=\"/images/dayicons/newyear.png\" title=\"New Year's Day\" /></td>";
        } else if ($checkDates[$i] == "5 February") {
            $content .= "<td class=\"date " . $class . "\"><img src=\"/images/dayicons/candlemas.png\" title=\"Candlemas\" /></td>";
        } else if ($checkDates[$i] == "14 February") {
            $content .= "<td class=\"date " . $class . "\"><img src=\"/images/dayicons/valentines.png\" title=\"Valentines Day\" /></td>";
        } else if ($checkDates[$i] == "21 June") {
            $content .= "<td class=\"date " . $class . "\"><img src=\"/images/dayicons/midsummer.png\" title=\"Midsummer\" /></td>";
        } else if ($checkDates[$i] == "31 October") {
            $content .= "<td class=\"date " . $class . "\"><img src=\"/images/dayicons/halloween.png\" title=\"Hallowe'en\" /></td>";
        } else if ($checkDates[$i] == "11 November") {
            $content .= "<td class=\"date " . $class . "\"><img src=\"/images/dayicons/rememberance.png\" title=\"Rememberance Day\" /></td>";
        } else if ($checkDates[$i] == "21 December") {
            $content .= "<td class=\"date " . $class . "\"><img src=\"/images/dayicons/midwinter.png\" title=\"Midwinter\" /></td>";
        } else if ($checkDates[$i] == "25 December") {
            $content .= "<td class=\"date " . $class . "\"><img src=\"/images/dayicons/christmas.png\" title=\"Christmas Day\" /></td>";
        } else if ($date == $today) {
            $content .= "<td class=\"date " . $class . "\"><strong>" . $displayDates[$i] . "</strong></td>";
        } else {
            $content .= "<td class=\"date " . $class . "\">" . $displayDates[$i] . "</td>";
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
                } else if ($row['kept'] == "NA") {
                    $color = "#dddddd";
                } else /* WAITING */ {
                    $color = "#bbbbbb";
                }
            } else {
                $color = "#eeeeee";
            }
            $content .= "<td style=\"background:" . $color . "\">" . $kept . "</td>";
        }
        $content .= "</tr>";
        
    }
    // User is generating their own table, offer a link to add more promises.
    if ($_SESSION['uid'] == $uid) {
        $content .= "<tr><td class=\"promise\"><a href=\"/manage\" target=\"_top\">add a";
        if (mysql_num_rows($promiseResult) > 0) { $content .= "nother"; }
        $content .= " promise?</a></td>";
    } else if (mysql_num_rows($promiseResult) == 0) {
        // Other user's blank table
        $content .= "<tr><td class=\"promise\">no promises set yet!</td>";
    } else {
        $content .= "<tr><td></td>";
    }
    
    
    // Make the "this week" footer
    $content .= "<td colspan=21></td>";
    $content .= "<td class=\"date thisweek\" colspan=7>This week</td>";
    $content .= "</tr>";
    
    
    
    $content .= "</table></div>";
    
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
