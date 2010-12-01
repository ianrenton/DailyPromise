<?php

date_default_timezone_set('UTC');

/* Load required lib files. */
session_start();
require_once('twitteroauth/twitteroauth.php');
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
    	$content .= '<li><a href="/view">view</a></li>';
    	$content .= '<li><a href="/enter">enter</a></li>';
    	$content .= '<li><a href="/manage">manage</a></li>';
    	$content .= '<li><a href="/configure">configure</a></li>';
    	$content .= '<li><a href="/logout">log out</a></li>';
        $content .= '</ul></div>';
    }
	return $content;
}

mysql_close();
