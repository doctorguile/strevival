<?php
require_once('autoload.php');

function processChannelVideoFeeds($sxml) {
    Video::prepareInsertNewVideoStmt();

    $oldVideoExists = false;
    $newVidoes = array();

    foreach ($sxml->entry as $video) {
        $video = Video::parseVideo($video);
        if (!$video) continue;
        if (DB::rowExists('videos', 'yt_id', $video['yt_id'])) {
            $oldVideoExists = true;
            continue;
        }
        DB::$stmt['insertVideo']->bindValue(':yt_id', $video['yt_id']);
        DB::$stmt['insertVideo']->bindValue(':channel', $video['channel']);
        DB::$stmt['insertVideo']->bindValue(':title', $video['title']);
        DB::$stmt['insertVideo']->bindValue(':published', $video['published']);
        DB::$stmt['insertVideo']->bindValue(':content', $video['content']);
        DB::$stmt['insertVideo']->bindValue(':event', $video['event']);
        DB::$stmt['insertVideo']->bindValue(':event_part', $video['event_part']);
        DB::$stmt['insertVideo']->execute();
        $newVidoes[] = $video;
    }
    return array($newVidoes, $oldVideoExists);
}

function scanLatestVideos($channel = 'supersf2turbo') {
    $allVidoes = array();
    $url = "https://gdata.youtube.com/feeds/api/users/$channel/uploads";
    while (true) {
        $content = trim(file_get_contents($url));
        $sxml = simplexml_load_string($content);
        list($newVidoes, $oldVideoExists) = processChannelVideoFeeds($sxml);
        $allVidoes = array_merge($allVidoes, $newVidoes);
        $continueScanning = (count($newVidoes) > 0) && !$oldVideoExists;
        if (!$continueScanning) break;
        $next = $sxml->xpath("*[local-name()='link' and @rel='next']");
        if (empty($next)) {
            break;
        }
        /** @var SimpleXmlElement $node */
        $node = $next[0];
        $url = (string)$node->attributes()->href;
    }
    return $allVidoes;
}

function savefile($i, $content) {
    file_put_contents("xml/$i.xml", $content);
}

//'superturbor'
$newVidoes = scanLatestVideos();

if (count($newVidoes)> 0) {
    echo "Found new videos\n";
    echo "<br>";
    $alltitles = Util::array_pluck('title', $newVidoes);
    echo implode('<br>', $alltitles);
    YouTubeST::processVideos();
    $events = array_unique(Util::array_pluck('event', $newVidoes));
    foreach ($events as $k => $v) {
        YouTubeST::processWinners($k);
    }

} else {
    echo "No new video found";
}
