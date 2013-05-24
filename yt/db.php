<?php

class Player {
    public $name;
    public $character;
    public $text;

    public function __construct($text) {
        $this->text = $text;
        $text = trim($text);
        if (preg_match("@\\(.+\\)@", $text, $matches)) {
            $this->character = self::parseCharacter($matches[0]);
            $this->name = trim(str_replace($matches[0], "", $text));
        } else {
            $this->character = self::parseCharacter($text);
            $this->name = $text;
        }
    }

    static function parseCharacter($text) {
        $text = trim(strtolower($text));
        $char = null;
        $altcharacters = array(
            'ryu' => array(),
            'ken' => array(),
            'ehonda' => array('honda'),
            'chunli' => array('chun'),
            'blanka' => array(),
            'zangief' => array(),
            'guile' => array(),
            'dhalsim' => array('sim'),
            'thawk' => array('hawk'),
            'cammy' => array(),
            'feilong' => array('fei'),
            'deejay' => array('dj'),
            'boxer' => array(),
            'claw' => array(),
            'sagat' => array(),
            'dictator' => array('dict')
        );
        foreach ($altcharacters as $k => $alts) {
            if (strstr($text, $k)) {
                $char = $k;
                break;
            }
            foreach ($alts as $n) {
                if (strstr($text, $n)) {
                    $char = $k;
                    break;
                }
            }
            if ($char) break;
        }

        if (!$char) $char = 'unknown';
        if (strstr($text, "o.")) {
            $char = "o.$char";
        }
        return $char;
    }

}


class YouTubeST {
    static $dbpath = 'sqlite:yt.sqlite3';

    static $characters = array('ryu', 'ken', 'ehonda', 'chunli', 'blanka', 'zangief', 'guile', 'dhalsim', 'thawk', 'cammy', 'feilong', 'deejay', 'boxer', 'claw', 'sagat', 'dictator');

    static function convertTimeStamp($start) {
        if (strstr($start, ':')) {
            $parts = explode(":", $start);
            $start = (intval($parts[0]) * 60) + intval($parts[1]);
        } else {
            $start = intval($start);
        }
        return $start;
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
    static function extractMatches($text, $eventpart) {
        $result = array();

        $lctext = strtolower($text);
        if (strstr($lctext, 'hyper street fighter ii')) {
            return $result;
        }

        $startdelimiter = sprintf("part %02d", $eventpart);
        $enddelimiter = sprintf("part %02d", $eventpart + 1);

        if (($startpos = strpos($lctext, $startdelimiter)) !== false) {
            $endpos = strpos($lctext, $enddelimiter);
            if ($endpos !== false) {
                $text = substr($text, $startpos, $endpos - $startpos);
            } else {
                $text = substr($text, $startpos);
            }
        }

        // 00:06 VIPER (Hawk) vs. Koedo (Dictator)
        $lines = explode("\n", $text);
        foreach ($lines as $line) {
            if (preg_match("@\\s*(\\d{2}:\\d{2})\\s+(.+)vs\\.(.+)@", $line, $matches)) {
                $result[] = array(self::convertTimeStamp($matches[1]), new Player($matches[2]), new Player($matches[3]));
            }
        }
        return $result;
    }

    static function calcSortOrder($event_part, $start) {
        return ($event_part * 60 * 60 * 2) + $start;
    }

    /** @var PDOStatement $insertMatchStmt */
    private $insertMatchStmt;
    /** @var PDOStatement $updateVideoStateStmt */
    private $updateVideoStateStmt;
    /** @var PDOStatement $updateWinnerStmt */
    private $updateWinnerStmt;

    function __construct() {
        $this->db = new PDO(self::$dbpath);
    }

    function processVideo($row) {
        $this->insertMatchStmt->bindValue(':yt_id', $row['yt_id']);
        $this->insertMatchStmt->bindValue(':published', $row['published']);
        $this->insertMatchStmt->bindValue(':event', $row['event']);
        $this->insertMatchStmt->bindValue(':event_part', $row['event_part']);
        $this->insertMatchStmt->bindValue(':winner', '');

        $this->updateVideoStateStmt->bindValue(':yt_id', $row['yt_id']);

        $result = self::extractMatches($row['content'], $row['event_part']);
        if (!empty($result)) {
            foreach ($result as $match) {
                $start = $match[0];
                $this->insertMatchStmt->bindValue(':start', $start);
                $this->insertMatchStmt->bindValue(':player1', $match[1]->name);
                $this->insertMatchStmt->bindValue(':char1', $match[1]->character);
                $this->insertMatchStmt->bindValue(':player2', $match[2]->name);
                $this->insertMatchStmt->bindValue(':char2', $match[2]->character);
                $this->insertMatchStmt->bindValue(':sort_order', self::calcSortOrder($row['event_part'], $start));
                $this->insertMatchStmt->execute();
            }
            $this->updateVideoStateStmt->bindValue(':state', 'processed');
        } else {
            $this->updateVideoStateStmt->bindValue(':state', 'cannot parse');
        }

        $this->updateVideoStateStmt->execute();
    }

    function processVideos() {
        $insert = "INSERT INTO matches (yt_id, player1, char1, player2, char2, winner, published, start, event, event_part, sort_order) VALUES (:yt_id, :player1, :char1, :player2, :char2, :winner, :published, :start, :event, :event_part, :sort_order)";
        $this->insertMatchStmt = $this->db->prepare($insert);

        $update = "UPDATE videos SET state = :state WHERE yt_id = :yt_id";
        $this->updateVideoStateStmt = $this->db->prepare($update);

        $result = $this->db->query('SELECT * FROM videos');
        foreach ($result as $row) {
            $this->processVideo($row);
        }
    }

    /**
     * @param PDOStatement $matches
     */
    function processWinner($matches) {
        $candidates = array();

        while ($match = $matches->fetch()) {
            $ok = true;
            if (in_array($match['player1'], $candidates)) {
                $this->updateWinnerStmt->bindValue(':winner', $match['player1']);
            } elseif (in_array($match['player2'], $candidates)) {
                $this->updateWinnerStmt->bindValue(':winner', $match['player2']);
            } else {
                $ok = false;
            }
            $candidates = array($match['player1'], $match['player2']);
            if ($ok) {
                $this->updateWinnerStmt->bindValue(':yt_id', $match['yt_id']);
                $this->updateWinnerStmt->bindValue(':sort_order', $match['sort_order']);
                $this->updateWinnerStmt->execute();
            }
        }
    }

    function processWinners() {
        $events = $this->db->query('select distinct(event) from matches');

        $selectstmt = 'select * from matches where event = :event order by sort_order desc';
        $selectsql = $this->db->prepare($selectstmt);

        $update = "UPDATE matches SET winner = :winner WHERE yt_id = :yt_id and sort_order = :sort_order";
        $this->updateWinnerStmt = $this->db->prepare($update);

        foreach ($events as $event) {
            $selectsql->bindValue(':event', $event['event']);
            $selectsql->execute();
            $this->processWinner($selectsql);
        }
    }
/*
CREATE TABLE playercharacters (
player TEXT,
character TEXT,
PRIMARY KEY (player, character)
);
*/
    function extractUniquePlayersAndCharacters() {
        $updatestmt = "insert or replace into playercharacters (player, character) values (:player, :character)";
        $updatestmt = $this->db->prepare($updatestmt);
        for($i=1;$i<=2;$i++) {
            $pairs = $this->db->query("select distinct player$i, char$i from matches");
            foreach ($pairs as $pair) {
                $updatestmt->bindValue(':player', $pair["player$i"]);
                $updatestmt->bindValue(':character', $pair["char$i"]);
                $updatestmt->execute();
            }
        }
    }

    function getPlayersAndCharacters() {
        $map = array();
        $pairs = $this->db->query("select player, character from playercharacters");
        foreach ($pairs as $pair) {
            $p = $pair["player"];
            if (!isset($map[$p])) {
                $map[$p] = array();
            }
            $map[$p][] = $pair["character"];
        }
        return $map;
    }

    function buildStatistic() {
        $map = array();
        foreach (self::$characters as $c1) {
            $map[$c1] = array();
            $map['o.' . $c1] = array();
            foreach (self::$characters as $c2) {
                $map[$c1][$c2] = array('count' => 0, 'wins' => 0, 'loses' => 0);
                $map['o.' . $c1][$c2] = array('count' => 0, 'wins' => 0, 'loses' => 0);
                $map[$c1]['o.' . $c2] = array('count' => 0, 'wins' => 0, 'loses' => 0);
                $map['o.' . $c1]['o.' . $c2] = array('count' => 0, 'wins' => 0, 'loses' => 0);
            }
        }
        $matches = $this->db->query("select player1, player2, winner, char1, char2 from matches where char1 != 'unknown' and char2 != 'unknown'");
        foreach ($matches as $match) {
            $p1 = $match['player1'];
            $p2 = $match['player2'];
            $c1 = $match['char1'];
            $c2 = $match['char2'];
            $winner = $match['winner'];
            if ($c1 == $c2) {
                $map[$c1][$c2]['count']++;
                $map[$c1][$c2]['wins'] += 0.5;
                $map[$c1][$c2]['loses'] += 0.5;
            } else {
                $map[$c1][$c2]['count']++;
                $map[$c2][$c1]['count']++;
                if ($winner == $p1) {
                    $map[$c1][$c2]['wins']++;
                    $map[$c2][$c1]['loses']++;
                } elseif ($winner == $p2) {
                    $map[$c2][$c1]['wins']++;
                    $map[$c1][$c2]['loses']++;
                }
            }
        }

        $oldCharsToRemove = array();
        foreach (self::$characters as $c1) {
            $count = 0;
            foreach (self::$characters as $c2) {
                $count += $map['o.' . $c1][$c2]['count'];
                $count += $map['o.' . $c1]['o.' . $c2]['count'];
            }
            if ($count == 0) {
                $oldCharsToRemove[] = 'o.' . $c1;
            }
        }
        foreach (self::$characters as $c1) {
            foreach ($oldCharsToRemove as $c2) {
                unset($map[$c1][$c2]);
                unset($map['o.' . $c1][$c2]);
            }
        }
        foreach ($oldCharsToRemove as $c) {
            unset($map[$c]);
        }

        file_put_contents('stats.txt', var_export($map, true));
    }

    function findMatches($c1, $c2) {
        // $c1 = preg_replace("/[^a-z\\.]/", "", $c1);
        // $c2 = preg_replace("/[^a-z\\.]/", "", $c2);
        // if (empty($c1) || empty($c2)) {
        //     return array();
        // }
        if ($c1 == $c2) {
            $query = "select * from matches where char1 = :c1 and char2 = :c2 order by published, event, sort_order";
        } else {
            $query = "select * from matches where (char1 = :c1 and char2 = :c2) or (char1 = :c2 and char2 = :c1) order by published, event, sort_order";
        }
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':c1', $c1);
        $stmt->bindValue(':c2', $c2);
        $stmt->execute();
        return $stmt;
    }

    function findPlayerMatches($player) {
        $stmt = "select * from matches where (player1 = :player) or (player2 = :player) order by published, event, sort_order";
        $stmt = $this->db->prepare($stmt);
        $stmt->bindValue(':player', $player);
        $stmt->execute();
        return $stmt;
    }

    function findNotProcessedVideos() {
        $stmt = "select yt_id, title, content from videos where state != 'processed' order by published, title";
        $stmt = $this->db->prepare($stmt);
        $stmt->execute();
        return $stmt;
    }

    function processXmlFile($file) {
        $insert = "INSERT INTO videos (yt_id, title, published, content, state, event, event_part) VALUES (:yt_id, :title, :published, :content, :state, :event, :event_part)";
        $stmt = $this->db->prepare($insert);
        $stmt->bindParam(':yt_id', $yt_id);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':published', $published);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':state', $state);
        $stmt->bindParam(':event', $event);
        $stmt->bindParam(':event_part', $eventpart);

        $sxml = simplexml_load_file($file);
        foreach ($sxml->entry as $entry) {
            $yt_id = preg_replace("@.*/@", "", (string)$entry->id);
            $title = (string)$entry->title;
            $published = strtotime($entry->published);
            $content = $entry->content;
            $state = '';
            $event = $title;
            $eventpart = 1;

            $pattern = "@\\s*-\\s*(\\d{1,2})\\s*/\\s*\\d{1,2}\\s*$@";
            if (preg_match($pattern, $event, $matches)) {
                $eventpart = intval($matches[1]);
                $event = trim(preg_replace($pattern, "", $event));
            } else {
                $pattern = "@\\s*-\\s*(\\d{1,2})\\s*$@";
                if (preg_match($pattern, $event, $matches)) {
                    $eventpart = intval($matches[1]);
                    $event = trim(preg_replace($pattern, "", $event));
                } else {
                    $pattern = "@\\s*\\[\\s*(\\d{1,2})\\s*\\]\\s*$@";
                    if (preg_match($pattern, $event, $matches)) {
                        $eventpart = intval($matches[1]);
                        $event = trim(preg_replace($pattern, "", $event));
                    }
                }
            }
            $stmt->execute();
        }
    }

    function processXmlFiles() {
        $files = glob("xml/*.xml");
        foreach ($files as $file) {
            $this->processXmlFile($file);
        }
    }

}

//$yt = new YouTubeST();
//processXmlFiles();
//$yt->processVideos();
//$yt->processWinners();
//$yt->buildStatistic();
//$yt = new YouTubeST();
//$yt->extractUniquePlayersAndCharacters();

function validateMatches() {
    $db = new PDO(YouTubeST::$dbpath);
    $events = $db->query('select distinct(event) from matches');

    foreach ($events as $event) {
        $event = $event['event'];
        echo "--select player1 , player2, winner , event, event_part, yt_id, start from matches where event = '$event' order by event, event_part, sort_order\n";
    }
}

