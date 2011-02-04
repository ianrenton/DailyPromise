<?php

// Twitter API stuff.  If you're hosting your own version of SuccessWhale (or
// something based on it), register your app with Twitter.  Once you're done,
// a consumer key and secret will be given to you.
define("CONSUMER_KEY", "");
define("CONSUMER_SECRET", "");

// Change this to point to your web server
define("OAUTH_CALLBACK", "http://dailypromise/callback.php");

// MySQL Database Stuff.  Leave these blank if you don't want to use a database.
// (with no DB, you won't be able to save user column settings, cache links, or
// cache authentication tokens.)
define("DB_SERVER", "localhost");
define("DB_NAME", "dailypromise");
define("DB_USER", "dailypromise");
define("DB_PASS", "dailypromise");

// Twitter access token for the twitter bot
define('BOT_ACCESS_TOKEN', '');

?>
