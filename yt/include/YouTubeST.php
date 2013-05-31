<?php

class YouTubeST {
    static $VIDEO_STATE_PROCESSED = 'processed';
    static $VIDEO_STATE_CANNOTPARSE = 'cannot parse';
    static $VIDEO_STATE_USERSUBMITTED = 'user contributed';

    static function notSuperTurboMatch($videoDescription) {
        $lowercase = strtolower($videoDescription);
        if (strstr($lowercase, 'hyper street fighter ii')) {
            return true;
        }
    }

    /*
     * special case
    GameSpot Versus 010113

    Part 01
    00:05 PECO (O.Ken) vs. Kondo (Claw)
    01:47 PECO (O.Ken) vs. Nakamura (Cammy)
    03:28 Gantan Shoten. (Guile) vs. Nakamura (Cammy)
    05:15 Sasori (Ryu) vs. Nakamura (Cammy)
    06:59 Sasori (Ryu) vs. Shu (Ken)
    09:02 Sasori (Ryu) vs. TIO (Dhalsim)
    10:14 Keishin (Chun) vs. TIO (Dhalsim)
    11:41 Keishin (Chun) vs. Gucchi (Ryu)
    13:07 Keishin (Chun) vs. Fujinuma (Chun)
    13:59 Keishin (Chun) vs. Seme Musician (DJ)

    Part 02
    00:05 Keishin (Chun) vs. Kikai (Guile)
    01:19 Keishin (Chun) vs. AFO (Blanka)
    02:56 Keishin (Chun) vs. Sashishi (Ryu)
    04:20 Keishin (Chun) vs. Mikado Aio (Dhalsim)
    06:28 Keishin (Chun) vs. yaya (Sagat)
    07:52 Nikaiten (Boxer) vs. yaya (Sagat)
    09:03 Sensei (Claw) vs. yaya (Sagat)


    Exhibition
    10:36 Sensei (Claw) vs. Kurahashi (Ryu)
     */
    static function extractRelevantDescription($videoDescription, $eventpart) {
        $lowercase = strtolower($videoDescription);

        $startdelimiter = sprintf("part %02d", $eventpart);
        $enddelimiter = sprintf("part %02d", $eventpart + 1);

        if (($startpos = strpos($lowercase, $startdelimiter)) !== false) {
            $endpos = strpos($lowercase, $enddelimiter);
            if ($endpos !== false) {
                return substr($videoDescription, $startpos, $endpos - $startpos);
            } else {
                return substr($videoDescription, $startpos);
            }
        }
        return $videoDescription;
    }


    static function extractMatches($videoDescription, $eventpart) {
        $result = array();

        if (self::notSuperTurboMatch($videoDescription)) {
            return $result;
        }

        $videoDescription = self::extractRelevantDescription($videoDescription, $eventpart);

        $lines = explode("\n", $videoDescription);
        foreach ($lines as $line) {
            // 00:06 VIPER (Hawk) vs. Koedo (Dictator)
            if (preg_match("@\\s*(\\d{2}:\\d{2})\\s+(.+)vs\\.(.+)@", $line, $matches)) {
                $result[] = array(Util::convertTimeStamp($matches[1]), new Player($matches[2]), new Player($matches[3]));
            }
        }
        return $result;
    }

    static function calcSortOrder($event_part, $start) {
        return ($event_part * 60 * 60 * 2) + $start;
    }

    static function prepareUpdateVideoStateStmt() {
        if (!isset(DB::$stmt['updateVideoState'])) {
            $update = "UPDATE videos SET state = :state WHERE yt_id = :yt_id";
            DB::$stmt['updateVideoState'] = DB::get()->prepare($update);
        }
    }

    static function updateVideoState($yt_id, $state) {
        self::prepareUpdateVideoStateStmt();
        DB::$stmt['updateVideoState']->bindValue(':yt_id', $yt_id);
        DB::$stmt['updateVideoState']->bindValue(':state', $state);
        DB::$stmt['updateVideoState']->execute();
    }

    static function processVideo($row) {
        DB::$stmt['insertMatch']->bindValue(':yt_id', $row['yt_id']);
        DB::$stmt['insertMatch']->bindValue(':published', $row['published']);
        DB::$stmt['insertMatch']->bindValue(':event', $row['event']);
        DB::$stmt['insertMatch']->bindValue(':event_part', $row['event_part']);
        DB::$stmt['insertMatch']->bindValue(':winner', '');

        self::prepareUpdateVideoStateStmt();
        DB::$stmt['updateVideoState']->bindValue(':yt_id', $row['yt_id']);

        $result = self::extractMatches($row['content'], $row['event_part']);
        if (!empty($result)) {
            foreach ($result as $match) {
                $start = $match[0];
                DB::$stmt['insertMatch']->bindValue(':start', $start);
                DB::$stmt['insertMatch']->bindValue(':player1', $match[1]->name);
                DB::$stmt['insertMatch']->bindValue(':char1', $match[1]->character);
                DB::$stmt['insertMatch']->bindValue(':player2', $match[2]->name);
                DB::$stmt['insertMatch']->bindValue(':char2', $match[2]->character);
                DB::$stmt['insertMatch']->bindValue(':sort_order', self::calcSortOrder($row['event_part'], $start));
                DB::$stmt['insertMatch']->execute();
            }
            DB::$stmt['updateVideoState']->bindValue(':state', self::$VIDEO_STATE_PROCESSED);
        } else {
            DB::$stmt['updateVideoState']->bindValue(':state', self::$VIDEO_STATE_CANNOTPARSE);
        }

        DB::$stmt['updateVideoState']->execute();
    }

    static function processVideos() {
        $insert = "INSERT INTO matches (yt_id, player1, char1, player2, char2, winner, published, start, event, event_part, sort_order) VALUES (:yt_id, :player1, :char1, :player2, :char2, :winner, :published, :start, :event, :event_part, :sort_order)";
        DB::$stmt['insertMatch'] = DB::get()->prepare($insert);

        $result = DB::get()->query("SELECT * FROM videos where state = '' ");
        foreach ($result as $row) {
            self::processVideo($row);
        }
    }

    /**
     * @param PDOStatement $matches
     */
    static function processWinner($matches) {
        // The very last match of an event, we dont know who is the winner
        $candidates = array();
        while ($match = $matches->fetch()) {
            $validWinnerFound = true;
            if (in_array($match['player1'], $candidates)) {
                DB::$stmt['updateWinner']->bindValue(':winner', $match['player1']);
            } elseif (in_array($match['player2'], $candidates)) {
                DB::$stmt['updateWinner']->bindValue(':winner', $match['player2']);
            } else {
                $validWinnerFound = false;
            }
            if ($validWinnerFound) {
                DB::$stmt['updateWinner']->bindValue(':yt_id', $match['yt_id']);
                DB::$stmt['updateWinner']->bindValue(':sort_order', $match['sort_order']);
                DB::$stmt['updateWinner']->execute();
            }
            // so this round is done. we know who stays on
            // working backwards we should know who is the winner of the previous match
            $candidates = array($match['player1'], $match['player2']);
        }
    }

    static function processWinners($event) {
        $sql = 'select * from matches where event = :event order by sort_order desc';
        $cursor = DB::get()->prepare($sql);
        $cursor->bindValue(':event', $event);
        $cursor->execute();

        $update = "UPDATE matches SET winner = :winner WHERE yt_id = :yt_id and sort_order = :sort_order";
        DB::$stmt['updateWinner'] = DB::get()->prepare($update);
        self::processWinner($cursor);
    }

    static function processAllWinners() {
        $events = DB::get()->query('select distinct(event) from matches');
        foreach ($events as $event) {
            self::processWinners($event['event']);
        }
    }

    static function findMatchesByEvent($event) {
        $query = "select * from matches where event = :event order by sort_order";
        $stmt = DB::get()->prepare($query);
        $stmt->bindValue(':event', $event);
        $stmt->execute();
        return $stmt;
    }

    static function findMatchupMatches($c1, $c2) {
        if ($c1 == $c2) {
            $query = "select * from matches where char1 = :c1 and char2 = :c2 order by published, event, sort_order";
        } else {
            $query = "select * from matches where (char1 = :c1 and char2 = :c2) or (char1 = :c2 and char2 = :c1) order by published, event, sort_order";
        }
        $stmt = DB::get()->prepare($query);
        $stmt->bindValue(':c1', $c1);
        $stmt->bindValue(':c2', $c2);
        $stmt->execute();
        return $stmt;
    }

    static function findMissingWinnerMatches() {
        $stmt = "select * from matches where winner = '' order by published, event, sort_order";
        $stmt = DB::get()->prepare($stmt);
        $stmt->execute();
        return $stmt;
    }
//
//    function processXmlFiles() {
//        $files = glob("xml/*.xml");
//        foreach ($files as $file) {
//            $this->processChannelVideoFeeds(simplexml_load_file($file));
//        }
//    }

}

//$yt = new YouTubeST();
//processXmlFiles();
//$yt->processVideos();
//$yt->processWinners();
//$yt->buildStatistic();
//$yt = new YouTubeST();
//$yt->extractUniquePlayersAndCharacters();
