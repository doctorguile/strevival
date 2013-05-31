<?php
require_once('autoload.php');

?>

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
<p>Special thanks to <a href="http://www.youtube.com/user/supersf2turbo">supersf2turbo</a> for uploading all the ST
    matches and zaspacer for his original, awesome <a href='http://streetfighterdojo.com/superturbo/index.html'>ST
        matchup videos</a> page
<p>
<p>click on numbers to see the videos. Help contribute by <a href="./submit/">submitting video annotations</a></p>
<?php
MatchupTableView::html();
PlayerCharactersView::html();
