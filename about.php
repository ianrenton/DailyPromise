<?php

session_start();
require_once('auth.php');
require_once('config.php');
require_once('common.php');

// Titlebar text
$titleText = " - About";

$content = "<div class=\"centeredlistheader\">About Daily Promise / Frequently Asked Questions</div><div class=\"about\"";

$content .= '<p>Daily Promise is a website that helps you track your goals, day by day.  Users set up promises that they wish to keep, and every day, they return to the site to indicate whether they\'ve kept each promise.  They get a nice chart and summaries of how they\'ve done, and they can compete against their friends or strangers to be the best at keeping their promises!</p>';
$content .= '<p>The site is heavily tied into Twitter for retrieving friend lists and so on.  For the moment, you must have a Twitter account to join Daily Promise.</p>';
$content .= '<h3>Who made Daily Promise?</h3>';
$content .= '<p>Daily Promise was written by <a href="http://www.onlydreaming.net/about-me">Ian Renton</a>, a software engineer and self-confessed net-junkie who regularly fails to keep every health-related promise he sets.</p>';
$content .= '<p>He blogged about the development of Daily Promise - you can <a href="http://www.onlydreaming.net/tag/daily-promise">read those entries</a> on his website, <a href="http://www.onlydreaming.net/">Only Dreaming</a>.  He is <a href="http://twitter.com/tsuki_chama">@tsuki_chama</a> on Twitter.</p>';
$content .= '<p>The "n times per week" and promise editing features were contributed by <a href="http://everblankslate.com/about.html">Mark Harris</a>.</p>';
$content .= '<h3>Can I request features / report bugs?</h3>';
$content .= '<p>Of course.  You can either use our <a href="/contact">contact form</a>, or if you have a Github account, your can access the <a href="https://github.com/tsuki/DailyPromise/issues">issue tracker</a> directly.</p>';
$content .= '<h3>Is Daily Promise free?</h3>';
$content .= '<p>In every sense.</p>';
$content .= '<p>You don\'t need to pay to use Daily Promise at any point, and it has no adverts.</p>';
$content .= '<p>It\'s also free in the sense that the code behind it is licenced under the <a href="http://www.gnu.org/licenses/gpl.html">GNU GPL</a>, and is available for download at <a href="https://github.com/tsuki/DailyPromise">Github</a>.</p>';
$content .= '<p>If for some crazy reason you would like to throw money around to express gratitude, please make a donation to the <a href="http://www.openrightsgroup.org/donate">Open Rights Group</a> or the <a href="https://my.fsf.org/donate">Free Software Foundation</a>.</p>';

$content .= '</div>';

include('html.inc');

?>