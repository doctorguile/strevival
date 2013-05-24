<?php
require('db.php');

$matches = array();
if (isset($_REQUEST['c1']) && isset($_REQUEST['c2'])) {
    $yt = new YouTubeST();
    $matches = $yt->findMatches($_REQUEST['c1'], $_REQUEST['c2']);
}

if (empty($matches)) {
    require('tabularview.php');
} else {
    require('videolinks.php');
}

