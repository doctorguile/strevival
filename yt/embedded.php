<style>
    body {
        font-family: sans-serif;
        margin: 0;
        padding: 0;
        border: 0;
    }
</style>
<script>
    function clearAll() {
        document.documentElement.innerHTML = '';
    }
</script>

<?php
require_once('autoload.php');

$yt_id = Video::sanitizeYoutubeID($_REQUEST['id']);
$start = intval($_REQUEST['start']);

$flashhtml = Video::flashHtmlCode($yt_id, $start, true);

echo <<<EOF
<table width=100%>
<tr>
<td>$flashhtml</td>
<td><a href='javascript:clearAll()'>[x] Clear</a></td>
</tr>
</table>
EOF;
