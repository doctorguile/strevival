<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>

<style>
    .yttabularview td, th {
        vertical-align: top;
        padding: 1px;
        border: solid 1px lightgrey;
    }

    .yttabularview th {
        text-align: left;
    }
</style>

<h3>Youtube Matchup Videos (click on numbers to see the match vidoes)</h3>

<?php

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

function printSplitStatTable() {
    $stats = null;
    eval('$stats = ' . file_get_contents('stats.txt') . ";");

    echo "<table class=yttabularview>";
    echo "<tr><td>char\\opponent</td>";
    foreach ($stats as $c => $matches) {
        if ($c == 'dhalsim') break;
        echo "<td>$c</td>";
    }
    echo "</tr>";
    foreach ($stats as $c1 => $matches) {
        echo "<tr><td>$c1</td>";
        foreach ($matches as $c2 => $result) {
            if ($c2 == 'dhalsim') break;
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
                    $color = 'purple';
                //background
                echo "<span style='color:$color'>" . $winPercent . '%</span>';
            }
            echo "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";

    echo "<p>";

    echo "<table class=yttabularview>";
    echo "<tr><td>char\\opponent</td>";
    $ok = false;
    foreach ($stats as $c => $matches) {
        if ($c == 'dhalsim') $ok = true;
        if ($ok) echo "<td>$c</td>";
    }
    echo "</tr>";

    foreach ($stats as $c1 => $matches) {
        $ok = false;
        echo "<tr><td>$c1</td>";
        foreach ($matches as $c2 => $result) {
            if ($c2 == 'dhalsim') $ok = true;
            if (!$ok) continue;
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
                    $color = 'purple';
                //background
                echo "<span style='color:$color'>" . $winPercent . '%</span>';
            }
            echo "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
}

printSplitStatTable();
