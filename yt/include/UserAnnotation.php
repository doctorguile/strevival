<?php

class UserAnnotation {
    static $fields = array(
        "yt_id",
        "contributor",
        "char1",
        "char2",
        "player1",
        "player2",
        "start",
        "winner",
        'ipaddress',
        'event',
        'event_part',
        'sort_order',
        'published',
        'contributor',
    );

    static function prepareInsertUserSubmittedMatchesStmt() {
        if (!isset(DB::$stmt['insertUserSubmittedMatches'])) {
            $sql = "insert or replace into matches (" .
                implode(", ", self::$fields)
                . ") values (" . implode(",", array_map(create_function('$a', 'return ":" . $a;'), self::$fields))
                . ")";
            DB::$stmt['insertUserSubmittedMatches'] = DB::get()->prepare($sql);

        }
    }

    static function submitUserAnnotation($match) {
        $match['event_part'] = 1;

        if ($_SERVER && isset($_SERVER['REMOTE_ADDR'])) {
            $match['ipaddress'] = $_SERVER['REMOTE_ADDR'];
        } else {
            $match['ipaddress'] = '';
        }

        $match['sort_order'] = YouTubeST::calcSortOrder($match['event_part'], $match['start']);

        foreach (self::$fields as $f) {
            DB::$stmt['insertUserSubmittedMatches']->bindValue(":$f", $match[$f]);
        }
        DB::$stmt['insertUserSubmittedMatches']->execute();
    }

    static function addContributtedVideo($video, $contributor = '') {
        Video::prepareInsertNewVideoStmt();
        $video = Video::parseVideo($video);
        if (!$video) return null;
        if (!DB::rowExists('videos', 'yt_id', $video['yt_id'])) {
            DB::$stmt['insertVideo']->bindValue(':yt_id', $video['yt_id']);
            DB::$stmt['insertVideo']->bindValue(':channel', $video['channel']);
            DB::$stmt['insertVideo']->bindValue(':title', $video['title']);
            DB::$stmt['insertVideo']->bindValue(':published', $video['published']);
            DB::$stmt['insertVideo']->bindValue(':content', $video['content']);
            DB::$stmt['insertVideo']->bindValue(':event', $video['event']);
            DB::$stmt['insertVideo']->bindValue(':event_part', $video['event_part']);
            DB::$stmt['insertVideo']->bindValue(':contributor', $contributor);
            DB::$stmt['insertVideo']->bindValue(':state', YouTubeST::$VIDEO_STATE_USERSUBMITTED);
            DB::$stmt['insertVideo']->execute();
        }
        return $video;
    }
}