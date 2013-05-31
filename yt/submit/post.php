<?php
require_once('../autoload.php');
$errmsg = "";


$yt_id = Video::sanitizeYoutubeID($_REQUEST['yt_id']);
if (empty($yt_id)) {
    $errmsg = "no video id provided.";
}

$contributor = $_REQUEST['contributor'];
if (!$contributor) {
    $contributor = "Unknown";
} else {
    setcookie("contributor", $contributor, time()+(60 * 60 * 24 * 365));
}

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

if ($data && count($data) > 0) {
    foreach ($data as &$match) {
        foreach ($fields as $f) {
            $match[$f] = trim($match[$f]);
            if (empty($match[$f]) && $f != 'start') {
                $errmsg = "Field $f is empty";
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

if (DB::rowExists('matches', 'yt_id', $yt_id)) {
    $errmsg = 'video already exists in our library';
}

if (empty($errmsg)) {
    $video = UserAnnotation::addContributtedVideo($yt_id, $contributor);
    if (!$video) {
        $errmsg = "Error submitting video";
    } else {
        $general = array(
            'contributor' => $contributor,
        );
        UserAnnotation::prepareInsertUserSubmittedMatchesStmt();
        foreach ($data as &$match) {
            $submit = array_merge($general, $match, $video);
            UserAnnotation::submitUserAnnotation($submit);
        }
        Player::extractUniquePlayersAndCharacters();
    }
}

if (empty($errmsg)) {
    YouTubeST::updateVideoState($yt_id, YouTubeST::$VIDEO_STATE_USERSUBMITTED);
    echo 'ok';
} else {
    echo $errmsg;
}
