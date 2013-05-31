<?php

class MatchupTableView {

    private $stats;
    private $overallWinRate = array();

    function __construct() {
        $this->stats = MatchupStatistic::get();
    }

    static function percent($winPercent) {
        $color = '';
        if ($winPercent > 50.0)
            $color = 'green';
        elseif ($winPercent < 50.0)
            $color = 'purple';
        return "<span style='color:$color'>" . $winPercent . '%</span>';
    }

    static function printHeaderRow($characters) {
        $colspan = count($characters);
        echo "<tr style=background:yellow><td>Opponent</td><td colspan=$colspan>Character</td></tr>";
        echo "<tr><td style=background:white></td>";
        foreach ($characters as $attacker) {
            echo "<td style=background:white>$attacker</td>";
        }
        echo "</tr>";
    }

    static function columnBackgroundColor($colIndex) {
        return ($colIndex % 2) == 0 ? '#D3D6FF' : '#EAEBFF';
    }

    static function linkToVideoSearch($c1, $c2, $text) {
        return "<a href='?c1=$c1&c2=$c2'>" . $text . "</a>";
    }

    static function opponentLostRate($matchup) {
        $total = (0.0 + $matchup['wins'] + $matchup['loses']);
        if ($total > 0.0) {
            $percent = $matchup['loses'] / $total;
        } else {
            $percent = 0.0;
        }
        return round($percent * 100, 1);
    }

    function printOpponentRow($opponent, & $opponentsMatchups, & $characters) {
        echo "<tr><td style=background:white>$opponent</td>";
        $cols = 0;
        foreach ($characters as $attacker) {
            $bg = self::columnBackgroundColor($cols++);
            $matchup = $opponentsMatchups[$attacker];
            echo "<td style=background:$bg>";
            if ($matchup['count'] > 0) {
                echo self::linkToVideoSearch($opponent, $attacker, $matchup['count']);
                echo "<br>";
                $winPercent = self::opponentLostRate($matchup);
                echo self::percent($winPercent);

                if ($attacker != $opponent) {
                    if (!isset($this->overallWinRate[$attacker])) {
                        $this->overallWinRate[$attacker] = array();
                    }
                    $this->overallWinRate[$attacker][] = $winPercent;
                }
            }
            echo "</td>";
        }
        echo "</tr>";
    }

    function printOverallWinRateRow($characters) {
        echo "<tr><td>Overall</td>";
        foreach ($characters as $attacker) {
            $percent = 0;
            if (isset($this->overallWinRate[$attacker])) {
                $n = count($this->overallWinRate[$attacker]);
                if ($n > 0)
                    $percent = round(array_sum($this->overallWinRate[$attacker]) / $n, 1);
            }
            echo "<td>" . self::percent($percent) . "</td>";
        }
        echo "</tr>";
    }

    function printSubTable($characters) {
        echo "<table class=yttabularview>";
        self::printHeaderRow($characters);
        foreach ($this->stats as $opponent => $opponentsMatchups) {
            $this->printOpponentRow($opponent, $opponentsMatchups, $characters);
        }
        self::printOverallWinRateRow($characters);
        echo "</table>";
        echo "<p>";
    }

    function printSplitStatTable() {
        $allCharacters = array_keys($this->stats);
        $splitAtIndex = array_search('dhalsim', $allCharacters);
        $firsthalf = array_slice($allCharacters, 0, $splitAtIndex);
        $secondhalf = array_slice($allCharacters, $splitAtIndex);
        foreach (array($firsthalf, $secondhalf) as $characters) {
            $this->printSubTable($characters);
        }
    }

    static function html() {
        $self = new self();
        $self->printSplitStatTable();
    }
}
