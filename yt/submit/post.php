<?php
require("../db.php");
$errmsg = "";

$yt_id = trim($_REQUEST['yt_id']);
if (empty($yt_id)) {
    $errmsg = "no video id provided.";
}
$name = $_REQUEST['name'];
if (!$name) $name = "Unknown";

$data = $_REQUEST['data'];
$data = json_decode($data, true);
$fields = array(
    "char1",
    "char2",
    "player1",
    "player2",
    "start",
    "winner"
);

$yt = new YouTubeST('sqlite:../yt.sqlite3');

if ($data && count($data) > 0) {
    foreach ($data as &$match) {
        foreach ($fields as $f) {
            $match[$f] = trim($match[$f]);
            if (empty($match[$f])) {
                $errmsg = "Field is empty";
                break;
            }
            if ($f == 'start') {
                $match[$f] = intval($match[$f]);
            }
        }
        if (!(($match['winner'] == $match['player1']) || ($match['winner'] == $match['player2']))) {
            $errmsg = "Winner is invalid";
        }
    }
    unset($match);
} else {
    $errmsg = "No data submitted";
}

if (empty($errmsg)) {
    foreach ($data as &$match) {
        $yt->submitUserAnnotation($yt_id, $name, $match);
    }
}

if (empty($errmsg)) {
    $yt->updateVideoState($yt_id, 'user contributed');
    echo 'ok';
} else {
    echo $errmsg;
}
