<?php
//$SLOW_LOG_START = microtime(TRUE);
require_once('autoload.php');

$matches = array();
if (isset($_REQUEST['c1']) && isset($_REQUEST['c2'])) {
    $matches = YouTubeST::findMatchupMatches($_REQUEST['c1'], $_REQUEST['c2']);
    require('videolinks.php');
} elseif(isset($_REQUEST['player'])) {
    $matches = Player::findMatches($_REQUEST['player']);
    require('videolinks.php');
} elseif(isset($_REQUEST['missing'])) {
    $matches = YouTubeST::findMissingWinnerMatches();
    require('videolinks.php');
} else {
    require('tabularview.php');
}

//$SLOW_LOG_END = microtime(TRUE);

//echo '<hr>'. ($SLOW_LOG_END - $SLOW_LOG_START);