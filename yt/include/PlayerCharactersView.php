<?php

class PlayerCharactersView {

    static function getPlayersAndCharacters() {
        $map = array();
        $pairs = DB::get()->query("select player, character from playercharacters");
        foreach ($pairs as $pair) {
            $p = $pair["player"];
            if (!isset($map[$p])) {
                $map[$p] = array();
            }
            $map[$p][] = $pair["character"];
        }
        return $map;
    }

    static function html() {
        $map = self::getPlayersAndCharacters();

        echo "<hr><p>click on player name to see the videos";
        echo "<table id=playerCharactersTable>";
        echo "<thead>";
        echo "<tr>";
        echo "<th>Player</th>";
        echo "<th>Characters</th>";
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
}
