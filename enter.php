<?php

session_start();
require_once('auth.php');
require_once('config.php');
require_once('common.php');

// Require authentication for this page
auth();

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

// Build the main display
$content = makeOldEntryChecker($date);
$content .= makeTodaysEntry($date);

// If we're coming back from the callback
if (isset($_GET['done'])) {
    $content .= makeTweetBoxes($date);
    $content .= makeReturnLink();
}

include('html.inc');

mysql_close();



function makeOldEntryChecker($date) {
    // Find the earliest "WAITING" record.
    $query = "SELECT * FROM records WHERE uid='" . mysql_real_escape_string($_SESSION['uid']) . "' AND kept='WAITING' ORDER BY date ASC";
    $recordResult = mysql_query($query);
    if (mysql_num_rows($recordResult) > 0) {
        $record = mysql_fetch_assoc($recordResult);
        $earliestDate = $record['date'];
        // If it's not today, prompt to fill in a past date.
        $today = date("Y-m-d", strtotime("today"));
        if (($earliestDate != $today) && ($earliestDate != $date)) {
            $content .= "<div class=\"missingentries\"><span class=\"missingentries\">You still have missing entries from " . date("l jS F", strtotime($earliestDate)) . ".  <a href=\"/enter/" . $earliestDate . "\">Click here to complete them!</a></span></div>";
        }
    }
    return $content;
}





function makeTodaysEntry($date) {

    $query = "SELECT * FROM promises WHERE uid='" . mysql_real_escape_string($_SESSION['uid']) . "' AND active='1'";
    $promiseResult = mysql_query($query);
    
    // Friendly date header
    $today = date("Y-m-d", strtotime("today"));
    if ($date == $today) {
        $content = "<div class=\"centeredlistheader\">Today, " . date("l jS F", strtotime($date)) . ", I...</div>";
    } else {
        $content = "<div class=\"centeredlistheader\">On " . date("l jS F", strtotime($date)) . ", I...</div>";
    }
    
    $content .= "<form method=\"post\" action=\"/entercallback.php\"><ul class=\"centeredlist\">";
    
    if (mysql_num_rows($promiseResult) == 0) {
        $content .= "<li class=\"noitem\">You don't have any promises set up.  <a href=\"/manage\">Click here to do that now.</a></li>";
    }
    
    while ($promise = mysql_fetch_assoc($promiseResult)) {
    
        // Fetch the record from the DB in case there's already an entry for today.
        $query = "SELECT * FROM records WHERE uid='" . mysql_real_escape_string($_SESSION['uid']) . "' AND pid='" . mysql_real_escape_string($promise['pid']) . "' AND date='" . mysql_real_escape_string($date) . "'";
        $recordResult = mysql_query($query);
        if (mysql_num_rows($recordResult) > 0) {
            $row = mysql_fetch_assoc($recordResult);
            if ($row['kept'] == "YES") {
                $yes = " checked";
                $no = "";
            } else if ($row['kept'] == "NO") {
                $yes = "";
                $no = " checked";
            } else /* "WAITING" */ {
                $yes = "";
                $no = "";
            }
        } else {
            $yes = "";
            $no = "";
        }
    
        $content .= "<li class=\"item\"><span class=\"floatright\">";
        $content .= "<input type=\"radio\" name=\"" . $promise['pid'] . "\" id=\"" . $promise['pid'] . "yes\" value=\"true\" " . $yes . " /><label for=\"" . $promise['pid'] . "yes\" class=\"yes\">Yes</label>";
        $content .= "<input type=\"radio\" name=\"" . $promise['pid'] . "\" id=\"" . $promise['pid'] . "no\" value=\"false\" " . $no . " /><label for=\"" . $promise['pid'] . "no\" class=\"no\">No</label>";
        $content .= "</span>" . $promise['promise'] . "</li>";
    }
    
    $content .= "</ul>";
    $content .= "<input type=\"hidden\" name=\"date\" value=\"" . $date . "\">";
    $content .= "<div class=\"entersubmitbutton\"><input type=\"submit\" name=\"Submit\" class=\"entersubmitbutton\" value=\"Submit\"></div>";
    $content .= "</form>";
    
    return $content;
}



function makeTweetBoxes($date) {

    //$content = "<div class=\"thanks\">Thanks!</div>";
    
    // How many are complete today?
    $kept = 0;
    $unkept = 0;
    $waiting = 0;
    $query = "SELECT * FROM records WHERE uid='" . mysql_real_escape_string($_SESSION['uid']) . "' AND date='" . mysql_real_escape_string($date) . "'";
    $recordResult = mysql_query($query);
    while ($record = mysql_fetch_assoc($recordResult)) {
        if ($record['kept'] == "YES") $kept++;
        if ($record['kept'] == "NO") $unkept++;
        if ($record['kept'] == "WAITING") $waiting++;
    }
	$today = date("Y-m-d", strtotime("today"));
    

	// Offer tweet box if we're done and it's today, otherwise just a plain summary.
	if (($waiting == 0) && ($date == $today)) {
		$tweet = "Today I kept " . $kept . " of my " . ($kept+$unkept+$waiting) . " promise";
    	$tweet .= (($kept+$unkept+$waiting)==1)?".":"s.";
		$query = "SELECT * FROM users WHERE uid='" . mysql_real_escape_string($_SESSION['uid']) . "'";
        $userResult = mysql_query($query);
        $row = mysql_fetch_assoc($userResult);
		$tweet .= "  Follow my progress at http://dp.onlydreaming.net/user/" . $row['username'];
		$content .= '<div id="tweetbox" class="tweetbox"><img src="/images/ajax-loader.gif" /> Loading Tweet form...</div>
						<script type="text/javascript">
						  twttr.anywhere(function (T) {
						    T("#tweetbox").tweetBox({
						      height: 50,
						      width: 600,
							  label: "Today\'s record is complete. Tweet about it?",
						      defaultContent: "' . $tweet . '"
						    });
						  });
						</script>';
	} else {
		$content .= "<div class=\"entersummary\">";
	    if ($date == $today) {
	        $content .= "Today";
	    } else {
	        $content .= "On " . date("l jS F", strtotime($date));
	    }
	    $content .= " you kept " . $kept . " of your " . ($kept+$unkept+$waiting) . " promise";
	    $content .= (($kept+$unkept+$waiting)==1)?".":"s.";
	    if ($waiting > 0) {
	        $content .= "We're still waiting for information on " . $waiting . " of them.";
	    }
	    $content .= "</div>";
	}
    return $content;
}

function makeReturnLink() {
    $content .= "<div class=\"backtoview\"><a href=\"/view\">Back to your monthly view</a></div>";
    return $content;
}

?>