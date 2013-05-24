<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>

<?php

function printMatches($matches) {
    echo "<a href=?>Matchup video Index</a>";
    echo "<div>";
    echo "<table id='matchesTable' border=1>";
    echo <<<EOF
<THEAD>
      <tr>
        <th>Event</th>
        <th>Player 1</th>
        <th>Character</th>
        <th>Player 2</th>
        <th>Character</th>
        <th>Winner</th>
        <th>Youtube</th>
      </tr>
</THEAD>
<TBODY>
EOF;

    foreach ($matches as $m) {
        $c1 = $m['char1'];
        $c2 = $m['char2'];
        $p1 = $m['player1'];
        $p2 = $m['player2'];
        $w = $m['winner'];
        $e = $m['event'];
        $yt_id = $m['yt_id'];
        $start = $m['start'];

    echo <<<EOF
  <tr>
    <td>$e</td>
    <td>$p1</td>
    <td>$c1</td>
    <td>$p2</td>
    <td>$c2</td>
    <td>$w</td>
    <td><a href="javascript:void playMatch('$yt_id', $start)">Play</a></td>
  </tr>
EOF;
    }
    echo "</TBODY></table>";
    echo "</div>";
    echo "<p><p>";
    echo '<iframe border=1 width="500" height="400" id=ytplayer frameborder="0" allowfullscreen></iframe>';

    echo <<<EOF
<link rel="stylesheet" type="text/css" href="http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/css/jquery.dataTables.css">
<script type="text/javascript" charset="utf8" src="http://ajax.aspnetcdn.com/ajax/jQuery/jquery-1.8.2.min.js"></script>
<script type="text/javascript" charset="utf8" src="http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/jquery.dataTables.min.js"></script>
<script>
function playMatch(id, start) {
    $('#ytplayer').attr('src', 'embedded.php?id=' + id + '&start=' + start);
}

$(document).ready(function(){
  $('#matchesTable').dataTable();
});
</script>
EOF;
}
printMatches($matches);
