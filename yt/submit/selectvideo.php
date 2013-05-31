<?php
require_once('../autoload.php');
function printTable() {
    echo <<<EOF
<table id='nonProcessedVideosTable' border=1>
<THEAD>
      <tr>
        <th>Title</th>
        <th>Channel</th>
      </tr>
</THEAD>
<TBODY>
EOF;

    $matches = Video::findNotProcessed();
    foreach ($matches as $m) {
        $title = Util::htmlescape($m['title']);
        $yt_id = $m['yt_id'];
        $channel = Util::htmlescape($m['channel']);
        $content = Util::htmlescape($m['content']);
        echo <<<EOF
  <tr>
    <td><a title='$content' href='javascript:void submitVideo("$yt_id");'>$title</a></td>
    <td>$channel</td>
  </tr>
EOF;
    }

    echo "</TBODY></table>";
}

?>

<header>
    <meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
</header>
Enter a Super Turbo youtube video (url or videoid) below <br>
<form action="" method="get" id='submitform'>
    <input type=text size=60 name=yt_id id=yt_id
           placeholder='iPMVKNWvYhc OR http://www.youtube.com/watch?v=iPMVKNWvYhc'>
    <input type=submit>
</form>
<p>or pick a video from supersf2turbo's or superturbor's channel</p>

<?php
printTable();
?>

<link rel="stylesheet" type="text/css"
      href="http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/css/jquery.dataTables.css">
<script src="http://ajax.aspnetcdn.com/ajax/jQuery/jquery-1.8.2.min.js"></script>
<script src="http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/jquery.dataTables.min.js"></script>
<script>
    function submitVideo(id) {
        $('#yt_id').val(id);
        $('#submitform').submit();
    }
    $(document).ready(function () {
        $('#nonProcessedVideosTable').dataTable();
    });
</script>
