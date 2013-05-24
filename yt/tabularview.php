<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>

<style>
    .yttabularview td, th {
        vertical-align: top;
        text-align: center;
        padding: 1px;
        border: solid 1px lightgrey;
    }

    .yttabularview th {
        text-align: center;
    }
</style>
<img src='http://www.strevival.com/wp-content/uploads/2013/05/str_versus_matchupvideos.png'>
<p>Special thanks to <a href="http://www.youtube.com/user/supersf2turbo">supersf2turbo</a> for uploading all the ST matches and zaspacer for his original, awesome <a href='http://streetfighterdojo.com/superturbo/index.html'>ST matchup videos</a> page
<p>
<?php
require_once('db.php');

function printCharTable() {
    $characters = array('ryu', 'ken', 'ehonda', 'chunli', 'blanka', 'zangief', 'guile', 'dhalsim', 'thawk', 'cammy', 'feilong', 'deejay', 'boxer', 'claw', 'sagat', 'dictator');

    echo "<table border=1>";

    echo "<tr><td>&nbsp;</td>";
    foreach ($characters as $c) {
        echo "<td>$c</td>";
    }
    echo "</tr>";
    foreach ($characters as $c) {
        echo "<tr><td>$c</td>";
        foreach ($characters as $c2) {
            echo "<td>&nbsp;</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
}

function printStatTable() {
    $stats = null;
    eval('$stats = ' . file_get_contents('stats.txt') . ";");
    echo "<table class=yttabularview>";

    echo "<tr><td>char\\opponent</td>";
    foreach ($stats as $c => $matches) {
        echo "<td>$c</td>";
    }
    echo "</tr>";
    foreach ($stats as $c1 => $matches) {
        echo "<tr><td>$c1</td>";
        foreach ($matches as $c2 => $result) {
            echo "<td>";
            if ($result['count'] > 0) {
                echo "<a href='?c1=$c1&c2=$c2'>" . $result['count'] . "</a>";
                echo "<br>";
                $winPercent = $result['wins'] / (0.0 + $result['wins'] + $result['loses']);
                $winPercent = round($winPercent * 100, 2);
                $color = '';
                if ($winPercent > 50.0)
                    $color = 'green';
                elseif ($winPercent < 50.0)
                    $color = 'Red';
                //background
                echo "<span style='color:$color'>" . $winPercent . '%</span>';
            }
            echo "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
}

function printPlayerCharacters() {
    $yt = new YouTubeST();
    $map = $yt->getPlayersAndCharacters();

    echo "<hr><p>click on player name to see the videos";
    echo "<table id=playerCharactersTable>";
    echo "<thead>";
    echo "<tr>";
    echo "<th>player</th>";
    echo "<th>characters</th>";
    echo "</tr>";
    echo "</thead>";
    echo "<tbody>";
    foreach ($map as $player => $chars) {
        echo "<tr>";
        echo "<td>";
        echo "<a href='?player=";
        echo urlencode($player);
        echo "'>$player</a></td>";
        $chars = implode(', ', $chars);
        echo "<td>$chars</td>";
        echo "</tr>";
    }
    echo "</tbody>";
    echo "</table>";


    echo <<<EOF
<link rel="stylesheet" type="text/css" href="http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/css/jquery.dataTables.css">
<script type="text/javascript" charset="utf8" src="http://ajax.aspnetcdn.com/ajax/jQuery/jquery-1.8.2.min.js"></script>
<script type="text/javascript" charset="utf8" src="http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/jquery.dataTables.min.js"></script>
<script>
function playMatch(id, start) {
    $('#ytplayer').attr('src', 'embedded.php?id=' + id + '&start=' + start);
}

$(document).ready(function(){
  $('#playerCharactersTable').dataTable();
});
</script>
EOF;
}

function outputPercent($winPercent) {
    $color = '';
    if ($winPercent > 50.0)
        $color = 'green';
    elseif ($winPercent < 50.0)
        $color = 'purple';
    return "<span style='color:$color'>" . $winPercent . '%</span>';

}

function printSplitStatTable() {
    $stats = null;
    eval('$stats = ' . file_get_contents('stats.txt') . ";");
    $keys = array_keys($stats);
    $idx = array_search('dhalsim', $keys);
    $a1 = array_slice($keys, 0, $idx);
    $a2 = array_slice($keys, $idx);

    echo "<p>click on numbers to see the videos</p>";
    foreach(array($a1,$a2) as $keys) {
    $ncols = count($keys);

    echo "<table class=yttabularview>";
    echo "<tr style=background:yellow><td>Opponent</td><td colspan=$ncols>Character</td></tr>";
    echo "<tr><td style=background:white></td>";
    foreach ($keys as $attacker) {
        echo "<td style=background:white>$attacker</td>";
    }
    echo "</tr>";

    $overallWinRate = array();
    foreach ($stats as $opponent => $opponentsMatchups) {
        echo "<tr><td style=background:white>$opponent</td>";
        $cols = 1;
        foreach ($keys as $attacker) {
            $cols++;
            $bg = ($cols % 2) == 0 ? '#D3D6FF' : '#EAEBFF';
            $result = $opponentsMatchups[$attacker];
            echo "<td style=background:$bg>";
            if ($result['count'] > 0) {
                echo "<a href='?c1=$opponent&c2=$attacker'>" . $result['count'] . "</a>";
                echo "<br>";
                $winPercent = $result['loses'] / (0.0 + $result['wins'] + $result['loses']);
                $winPercent = round($winPercent * 100, 1);
                echo outputPercent($winPercent);

                if ($attacker != $opponent) {
                    if (!isset($overallWinRate[$attacker])) {
                        $overallWinRate[$attacker] = array();
                    }
                    $overallWinRate[$attacker][] = $winPercent;
                }
            }
            echo "</td>";
        }
        echo "</tr>";
    }

    echo "<tr><td>Overall</td>";
    foreach ($keys as $attacker) {
        $percent = 0;
        if (isset($overallWinRate[$attacker])) {
            $n = count($overallWinRate[$attacker]);
            if ($n > 0)
                $percent = round(array_sum($overallWinRate[$attacker]) / $n, 1);
        }
        echo "<td>" . outputPercent($percent) . "</td>";
    }
    echo "</tr>";

    echo "</table>";
    echo "<p>";
    }
}

printSplitStatTable();
printPlayerCharacters();
