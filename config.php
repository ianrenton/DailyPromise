<?php

// Imports Daily Promise's config, either from a .env file if one exists, or
// from the system environment variables. Generally on a server (such as
// Heroku) we have the environment variables set. You could also set them in
// an Apache conf file using the SetEnv directive, but that's a pain. This
// loading method allows you to just rename sample.env to .env and load the
// variables from there.

// Load required lib files.
require_once('libraries/Dotenv.php');

// If .env exists, load from it
if(file_exists("./.env")) {
  Dotenv::load(__DIR__);
}

// Take an example var and check that it has been set somewhere
$testVar = getenv("DB_SERVER");
if (isset($testVar) && !empty($testVar)) {
  define("DB_SERVER", getenv("DB_SERVER"));
  define("DB_NAME", getenv("DB_NAME"));
  define("DB_USER", getenv("DB_USER"));
  define("DB_PASS", getenv("DB_PASS"));
  define("CONSUMER_KEY", getenv("CONSUMER_KEY"));
  define("CONSUMER_SECRET", getenv("CONSUMER_SECRET"));
  define("OAUTH_CALLBACK", getenv("OAUTH_CALLBACK"));
  define("BOT_ACCESS_TOKEN", getenv("BOT_ACCESS_TOKEN"));
} else {
  die ("Environment variables were not set. Either set them in a <code>.env</code> file (see <code>sample.env</code>) or ensure that the parameters in <code>sample.env</code> are set as system environment variables.");
}

?>
