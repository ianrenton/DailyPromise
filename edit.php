<?php

session_start();
require_once("auth.php");
require_once("config.php");
require_once("common.php");

// Require authentication
auth();

mysql_connect(DB_SERVER,DB_USER,DB_PASS);
@mysql_select_db(DB_NAME) or die( "Unable to connect to the database.");
 
if (isset($_POST["promisetext"]) and isset($_POST["days"]) and isset($_POST["promise"])) {

  $query="UPDATE promises SET promise=\"" . mysql_real_escape_string($_POST["promisetext"]) . "\", days=\"" . mysql_real_escape_string($_POST["days"]) . "\" WHERE uid = \"" . mysql_real_escape_string($_SESSION["uid"]) . "\" AND pid=\"" . mysql_real_escape_string($_POST["promise"]) . "\"";
  mysql_query($query);
  updateCachedStats($_SESSION["uid"]);
  header("Location: /manage");
  mysql_close();
  die();
}

if (isset($_GET["promise"]) and is_numeric($_GET["promise"])) {

  $query="SELECT * FROM promises where uid='" . mysql_real_escape_string($_SESSION['uid']) . "' AND pid='" . mysql_real_escape_string($_GET["promise"]) . "'";

  $result = mysql_query($query);
  if (mysql_num_rows($result) == 0) {
    $content = "<div class=\"centeredlistheader\">You have no promises to edit. <a href=\"/manage/\">Why not add some?</a></div>";
    include("html.inc");
    mysql_close();
    die();

  }
  $promise=mysql_fetch_assoc($result);

  $titleText="Edit promise";
  $content = "<form class=\"centeredform\" method=\"post\" action=\"/edit\">";
  $content .= "<input type=\"hidden\" name=\"promise\" value=\"" . $_GET["promise"] . "\">";
  $content .= "I promised to ";
  $content .= "<input type=\"text\" name=\"promisetext\" class=\"newpromisefield\" value=\"" . $promise["promise"] . "\"/>";
  $content .= " at least ";
  $content .= "<select name=\"days\" class=\"newpromisefield\">";
  for ($i=1;$i<=7;++$i) {
    $content .= "<option value=\"" . $i . "\" ";
    if ($i == $promise["days"]) {
      $content .= "selected=\"yes\" ";
    }
    $content .= ">" . $i . "</option>";
  }
  $content .= "</select>";
  $content .= " times a week. ";
  $content .= "<input type=\"submit\" name=\"Submit\" value=\"Edit\"> ";
  $content .= "<a href=\"/manage\">cancel</a>";
  $content .= "</form>";

  include("html.inc");
  mysql_close();
} else {
  // lolwhut?
  header("Location: /manage");
}