<?php

class MatchupStatistic {

    static function get() {
        $m = new self();
        return $m->map;
    }

    private $map = array();

    function __construct() {
        $this->initializeAllZeros();
        $this->calculateStats();
        $this->removeEmptyStats();
    }

    function initializeAllZeros() {
        foreach (Globals::$characters as $c1) {
            $this->map[$c1] = array();
            $this->map['o.' . $c1] = array();
            foreach (Globals::$characters as $c2) {
                $this->map[$c1][$c2] = array('count' => 0, 'wins' => 0, 'loses' => 0);
                $this->map['o.' . $c1][$c2] = array('count' => 0, 'wins' => 0, 'loses' => 0);
                $this->map[$c1]['o.' . $c2] = array('count' => 0, 'wins' => 0, 'loses' => 0);
                $this->map['o.' . $c1]['o.' . $c2] = array('count' => 0, 'wins' => 0, 'loses' => 0);
            }
        }
    }

    function calculateStats() {
        $matches = DB::get()->query("select player1, player2, winner, char1, char2 from matches where char1 != 'unknown' and char2 != 'unknown'");
        foreach ($matches as $match) {
            $p1 = $match['player1'];
            $p2 = $match['player2'];
            $c1 = $match['char1'];
            $c2 = $match['char2'];
            $winner = $match['winner'];

            // mirror match
            if ($c1 == $c2) {
                $this->map[$c1][$c2]['count']++;
                $this->map[$c1][$c2]['wins'] += 0.5;
                $this->map[$c1][$c2]['loses'] += 0.5;
            } else {
                $this->map[$c1][$c2]['count']++;
                $this->map[$c2][$c1]['count']++;
                if ($winner == $p1) {
                    $this->map[$c1][$c2]['wins']++;
                    $this->map[$c2][$c1]['loses']++;
                } elseif ($winner == $p2) {
                    $this->map[$c2][$c1]['wins']++;
                    $this->map[$c1][$c2]['loses']++;
                } else {
                    // no winner determined yet
                }
            }
        }
    }

    function removeEmptyStats() {
        // assume all new characters have non-empty stats
        $oldCharsToRemove = array();
        foreach (Globals::$characters as $c1) {
            $count = 0;
            foreach (Globals::$characters as $c2) {
                $count += $this->map['o.' . $c1][$c2]['count'];
                $count += $this->map['o.' . $c1]['o.' . $c2]['count'];
            }
            if ($count == 0) {
                $oldCharsToRemove[] = 'o.' . $c1;
            }
        }
        foreach (Globals::$characters as $c1) {
            foreach ($oldCharsToRemove as $c2) {
                unset($this->map[$c1][$c2]);
                unset($this->map['o.' . $c1][$c2]);
            }
        }
        foreach ($oldCharsToRemove as $c) {
            unset($this->map[$c]);
        }
    }

}
