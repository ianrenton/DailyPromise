<?php

session_start();
require_once('auth.php');
require_once('config.php');
require_once('common.php');

// Require authentication for this page
auth();

// Titlebar text
$titleText = " - Manage Promises";

// DB connection
mysql_connect(DB_SERVER,DB_USER,DB_PASS);
@mysql_select_db(DB_NAME) or die( "Unable to select database");

// Get requested date, using today if none / future specified.
$date = date("Y-m-d", strtotime("today"));
if (isset($_GET['date'])) {
    if ($_GET['date'] < $date) {
        $date = $_GET['date'];
    }
}

// First-timer info
if (isset($_GET['response'])) {
    if ($_GET['response'] == "firsttime") {
        $content .= makeFirstTimeInfo();
    } else if ($_GET['response'] == "nowinvisible") {
        $content .= '<div class="good"><p><strong>Your profile is now invisible.  Your records, promises and the very existence of your account will be hidden from other users and the public.</strong></p></div>';
    }
}

// Build the main display
$content .= makeCurrentPromises();

// If we're coming back from the callback
if (isset($_GET['newpid'])) {
    $content .= makeTweetBoxes($_GET['newpid']);
}

$content .= makeNewPromises();
$content .= makeOldPromises();

// If we're coming back from the callback
if (isset($_GET['done'])) {
    $content .= makeReturnLink($date);
}

include('html.inc');

mysql_close();




function makeFirstTimeInfo() {
    $content .= '<div class="good"><h4>Thanks for joining Daily Promise!</h4>
    <p>This is the "manage" page, where you set your promises.  Go ahead and add some now!  If you\'d like to get filling in your records now, don\'t forget the checkbox in the "Add another" area, which will let you fill in your results from yesterday too.  It\'s selected by default.</p>
    <p>Once you\'re happy with your promises, use the links in the top-right to get around the site.  "enter" is where you fill in your records every day, and "view" shows you how you\'re getting on.  "friends" shows you how well people you follow on Twitter are doing - invite your friends, and compete to keep the most promises!</p>
    </div>
    <div class="error"><p>Your profile is visible to others, because we think it\'s easier to keep your promises if everybody knows about them!</p>
    <p>If you\'d prefer to keep your promises private, you <a href="/configure/makeinvisible">click here to make your profile invisible</a>.  No-one but you will know that you are a Daily Promise user.  (You can change this later on the "configuration" page.)</p>
    </div>';
    
    return $content;
}



function makeCurrentPromises() {

    // Get all active promises
    $query = "SELECT * FROM promises WHERE uid='" . mysql_real_escape_string($_SESSION['uid']) . "' AND active='1'";
    $promiseResult = mysql_query($query);
    
    $content .= "<div class=\"centeredlistheader\">Currently running:</div><ul class=\"centeredlist\">";
    
    if (mysql_num_rows($promiseResult) == 0) {
        $content .= "<li class=\"noitem\">You don't have any promises set up.  Use the \"Add another\" box below to add one.</li>";
    }
    
    while ($promise = mysql_fetch_assoc($promiseResult)) {
    
        $content .= "<li class=\"item\">";
    
        // Work out number of passes
        $query = "SELECT COUNT(*) FROM records WHERE uid='" . mysql_real_escape_string($_SESSION['uid']) . "' AND pid='" . mysql_real_escape_string($promise['pid']) . "' AND kept='YES'";
        $result = mysql_query($query);
        $row = mysql_fetch_assoc($result);
        $pass = $row['COUNT(*)'];
        // Work out number of fails
        $query = "SELECT COUNT(*) FROM records WHERE uid='" . mysql_real_escape_string($_SESSION['uid']) . "' AND pid='" . mysql_real_escape_string($promise['pid']) . "' AND kept='NO'";
        $result = mysql_query($query);
        $row = mysql_fetch_assoc($result);
        $fail = $row['COUNT(*)'];
        // Work out earliest date
        $query = "SELECT MIN(date) FROM records WHERE uid='" . mysql_real_escape_string($_SESSION['uid']) . "' AND pid='" . mysql_real_escape_string($promise['pid']) . "'";
        $result = mysql_query($query);
        $row = mysql_fetch_assoc($result);
        $earliestDate = $row['MIN(date)'];

        if ($pass+$fail <= 0) {
            $content .= "<span class=\"floatright\">(No data for this promise yet.)";
        } else {
            $content .= "<span class=\"floatright\">(" . round($pass/($pass+$fail)*100) . "% success rate since " . date("j M Y", strtotime($earliestDate)) . ".)";
        }
	$content .= "<a href=\"/edit/" . $promise["pid"] . "\" class=\"remove\">Edit</a> ";
        $content .= "<a href=\"/manage/deactivate/" . $promise['pid'] . "\" class=\"remove\">Remove</a></span>";
        $content .= "" . $promise['promise'] . "</li>";
    }
    
    $content .= "</ul>";
    
    return $content;
}



function makeNewPromises() {
    
    $content .= "<div class=\"centeredlistheader\">Add another:</div>";
    
    $content .= "<form class=\"centeredform\" method=\"post\" action=\"/managecallback.php\">";
    $content .= "I promise to ";
    $content .= "<input type=\"text\" name=\"newpromise\" class=\"newpromisefield\"/>";
    $content .= " at least ";
    $content .= "<select name=\"days\" class=\"newpromisefield\">";
    $content .= "<option value=\"1\">1</option>";
    $content .= "<option value=\"2\">2</option>";
    $content .= "<option value=\"3\">3</option>";
    $content .= "<option value=\"4\">4</option>";
    $content .= "<option value=\"5\">5</option>";
    $content .= "<option value=\"6\">6</option>";
    $content .= "<option value=\"7\" selected=\"yes\">7</option>";
    $content .= "</select>";
    $content .= " times a week. ";
    $content .= "<input type=\"submit\" name=\"Submit\" value=\"Add\"><br/>";
    $content .= "<input type=\"checkbox\" name=\"doyesterday\" id=\"doyesterday\" checked/><label for=\"doyesterday\">I want to get started right away - let me add yesterday's data too!</label>";
    $content .= "</form>";
    
    return $content;
}



function makeOldPromises() {

    // Get all inactive promises
    $query = "SELECT * FROM promises WHERE uid='" . mysql_real_escape_string($_SESSION['uid']) . "' AND active='0'";
    $promiseResult = mysql_query($query);
    
    $content .= "<div class=\"centeredlistheader\">Fallen by the wayside:</div><ul class=\"centeredlist\">";
    
    if (mysql_num_rows($promiseResult) == 0) {
        $content .= "<li class=\"noitem\">You haven't deactivated any of your promises. Well done!</li>";
    }
    
    while ($promise = mysql_fetch_assoc($promiseResult)) {
    
        $content .= "<li class=\"item\">";
    
        // Work out number of passes
        $query = "SELECT COUNT(*) FROM records WHERE uid='" . mysql_real_escape_string($_SESSION['uid']) . "' AND pid='" . mysql_real_escape_string($promise['pid']) . "' AND kept='YES'";
        $result = mysql_query($query);
        $row = mysql_fetch_assoc($result);
        $pass = $row['COUNT(*)'];
        // Work out number of fails
        $query = "SELECT COUNT(*) FROM records WHERE uid='" . mysql_real_escape_string($_SESSION['uid']) . "' AND pid='" . mysql_real_escape_string($promise['pid']) . "' AND kept='NO'";
        $result = mysql_query($query);
        $row = mysql_fetch_assoc($result);
        $fail = $row['COUNT(*)'];
        // Work out earliest and latest date
        $query = "SELECT MIN(date), MAX(date) FROM records WHERE uid='" . mysql_real_escape_string($_SESSION['uid']) . "' AND pid='" . mysql_real_escape_string($promise['pid']) . "'";
        $result = mysql_query($query);
        $row = mysql_fetch_assoc($result);
        $earliestDate = $row['MIN(date)'];
        $latestDate = $row['MAX(date)'];
        
        if ($pass+$fail <= 0) {
            $content .= "<span class=\"floatright\">(No data for this promise yet.)";
        } else {
            $content .= "<span class=\"floatright\">(" . round($pass/($pass+$fail)*100) . "% success rate from " . date("j M Y", strtotime($earliestDate)) . " to " . date("j M Y", strtotime($latestDate)) . ".)";
        }
        $content .= "<a href=\"/manage/activate/" . $promise['pid'] . "\" class=\"add\">Restore</a><a href=\"/manage/delete/" . $promise['pid'] . "\" class=\"remove\">Delete</a></span>";
        $content .= "" . $promise['promise'] . "</li>";
    }
    
    $content .= "</ul>";
    
    return $content;
}


function makeTweetBoxes($pid) {
	$query = "SELECT * FROM promises WHERE uid='" . mysql_real_escape_string($_SESSION['uid']) . "' AND pid='" . mysql_real_escape_string($pid) . "'";
    $promiseResult = mysql_query($query);
	$promise = mysql_fetch_assoc($promiseResult);
	$query = "SELECT * FROM users WHERE uid='" . mysql_real_escape_string($_SESSION['uid']) . "'";
    $userResult = mysql_query($query);
    $row = mysql_fetch_assoc($userResult);

	$tweet = "I just made a new promise, \\\"";
   	$tweet .= $promise['promise'];
	$tweet .= "\\\"!  Follow my progress at http://dp.onlydreaming.net/user/" . $row['username'];
	$content .= '<div id="tweetbox" class="tweetbox"></div>
					<script type="text/javascript">
					  twttr.anywhere(function (T) {
					    T("#tweetbox").tweetBox({
					      height: 50,
					      width: 600,
						  label: "You activated a new promise! Tweet about it?",
					      defaultContent: "' . $tweet . '"
					    });
					  });
					</script>';
    return $content;
}



function makeReturnLink() {
    $content .= "<div class=\"backtoview\"><a href=\"/view\">Back to your monthly view</a></div>";
    return $content;
}

?>