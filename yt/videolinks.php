<?php
require_once('autoload.php');

function printMatches($matches) {
    echo "<a href=?>Matchup video Index</a>";
    echo "<div>";
    echo "<table id='matchesTable' border=1>";
    echo <<<EOF
<THEAD>
      <tr>
        <th>Click to play video</th>
        <th>Player 1</th>
        <th>Character</th>
        <th>Player 2</th>
        <th>Character</th>
        <th>Winner</th>
        <th>Contributor</th>
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
        $ct = $m['contributor'];
        if ($w == $p1) {
            $w = "$c1 [$w]";
        } elseif ($w == $p2) {
            $w = "$c2 [$w]";
        }
        $e = $m['event'];
        $yt_id = $m['yt_id'];
        $id = $m['id'];
        $start = $m['start'];

        $yt_id = Util::htmlescape($yt_id);
        $e = Util::htmlescape($e);
        $p1 = Util::htmlescape($p1);
        $p2 = Util::htmlescape($p2);
        $c1 = Util::htmlescape($c1);
        $c2 = Util::htmlescape($c2);
        $w = Util::htmlescape($w);
        $start = intval($start);
        $ct = Util::htmlescape($ct);

        echo <<<EOF
  <tr>
    <td><a title=$id href="javascript:void playMatch('$yt_id', $start)">$e</a></td>
    <td>$p1</td>
    <td>$c1</td>
    <td>$p2</td>
    <td>$c2</td>
    <td>$w</td>
    <td>$ct</td>
  </tr>
EOF;
    }
    echo "</TBODY></table>";
    echo "</div>";
}

?>

<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
<link rel="stylesheet" type="text/css"
      href="http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/css/jquery.dataTables.css">
<script type="text/javascript" charset="utf8" src="http://ajax.aspnetcdn.com/ajax/jQuery/jquery-1.8.2.min.js"></script>
<script type="text/javascript" charset="utf8"
        src="http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/jquery.dataTables.min.js"></script>
<script>
    function playMatch(id, start) {
        $('#ytplayer').attr('src', 'embedded.php?id=' + id + '&start=' + start);
    }
    $(document).ready(function () {
        $('#matchesTable').dataTable();
    });
</script>

<?php
printMatches($matches);
?>

<p><p>
<iframe border=1 width="600" height="400" id=ytplayer frameborder="0" allowfullscreen></iframe>
