<?php

$id = preg_replace("@[^a-zA-Z0-9_-]@", "", $_REQUEST['id']) ;
$start = intval($_REQUEST['start']);

echo <<<EOF
<style>
body {
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
<table width=100%><tr><td>
<object width="420" height="315">
    <param name="movie" value="http://www.youtube.com/v/$id&hl=en&fs=1&rel=0&start=$start&autoplay=1"></param>
    <param name="allowFullScreen" value="true"></param>
    <param name="allowscriptaccess" value="always"></param>
    <embed src="http://www.youtube.com/v/$id&hl=en&fs=1&rel=0&start=$start&autoplay=1"
           type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="480"
           height="385"></embed>
</object>
</td><td><a href='javascript:clearAll()'>[x] Clear</a></td></tr></table>
EOF;

//<iframe width="420" height="315" src="http://www.youtube.com/embed/eRycy0nHKbE#t=1m08s" frameborder="0" allowfullscreen></iframe>