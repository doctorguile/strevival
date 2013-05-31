<?php
require_once('../autoload.php');
$errmsg = '';
$yt_id = null;

if (isset($_REQUEST['yt_id'])) {
    $yt_id = Video::extractYoutubeIDFromUrl($_REQUEST['yt_id']);
    if (!$yt_id) {
        $errmsg = 'invalid youtube video ' . Util::htmlescape($_REQUEST['yt_id']);
    } elseif (DB::rowExists('matches', 'yt_id', $yt_id)) {
        $errmsg = 'video ' . Util::htmlescape($yt_id['yt_id']) . ' already exists in our library';
        $yt_id = null;
    }
}

if ($yt_id) {
    require('addtimestamp.php');
} else {
    if ($errmsg) {
        echo "<strong>$errmsg</strong><br><br>";
    }
    require('selectvideo.php');
}

