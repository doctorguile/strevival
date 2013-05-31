<?php

class Player {

    static function allNames() {
        $allnames = array();
        $cursor = DB::get()->query('select distinct (player) from playercharacters order by player', PDO::FETCH_COLUMN, 0);
        foreach ($cursor as $name) {
            // filter out some bad names
            $name = trim(str_replace('?', '', $name));
            if (!empty($name)) {
                $allnames[] = $name;
            }
        }
        return $allnames;
    }

    static function prepareInsertPlayerCharacterStmt() {
        if (!isset(DB::$stmt['insertPlayerCharacter'])) {
            $sql = "insert or replace into playercharacters (player, character) values (:player, :character)";
            DB::$stmt['insertPlayerCharacter'] = DB::get()->prepare($sql);
        }
    }

    static function extractUniquePlayersAndCharacters() {
        self::prepareInsertPlayerCharacterStmt();
        for ($i = 1; $i <= 2; $i++) {
            $pairs = DB::get()->query("select distinct player$i, char$i from matches");
            foreach ($pairs as $pair) {
                DB::$stmt['insertPlayerCharacter']->bindValue(':player', $pair["player$i"]);
                DB::$stmt['insertPlayerCharacter']->bindValue(':character', $pair["char$i"]);
                DB::$stmt['insertPlayerCharacter']->execute();
            }
        }
    }

    static function findMatches($player) {
        $stmt = "select * from matches where (player1 = :player) or (player2 = :player) order by published, event, sort_order";
        $stmt = DB::get()->prepare($stmt);
        $stmt->bindValue(':player', $player);
        $stmt->execute();
        return $stmt;
    }

    public $name;
    public $character;
    public $text;

    public function __construct($text) {
        $this->text = $text;
        list ($name, $character) = self::parseNameAndCharacter(trim($text));
        $this->name = $name;
        $this->character = $character;
    }

    static function parseNameAndCharacter($text) {
        if (preg_match("@\\(.+\\)@", $text, $matches)) {
            $name = trim(str_replace($matches[0], "", $text));
            $character = self::parseCharacter($matches[0]);
        } else {
            $name = $text;
            $character = self::parseCharacter($text);
        }
        return array($name, $character);
    }

    static $charsAndAltnames = array(
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

    static function parseCharacter($text) {
        $text = trim(strtolower($text));
        $name = null;
        foreach (self::$charsAndAltnames as $officialname => $alts) {
            if (strstr($text, $officialname)) {
                $name = $officialname;
                break;
            }
            foreach ($alts as $alt) {
                if (strstr($text, $alt)) {
                    $name = $officialname;
                    break;
                }
            }
            if ($name) break;
        }

        if (!$name) $name = 'unknown';
        if (strstr($text, "o.")) {
            $name = "o.$name";
        }
        return $name;
    }
}