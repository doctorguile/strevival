<?php
function foramtxmlfiles() {
    $files = glob('xmlorg/*');
    foreach ($files as $file) {
        $newfile = str_replace('xmlorg', 'xml', $file);
        exec("xmllint --format $file > $newfile");
    }
}


//print_r(date("Y m d hMs", strtotime('2013-05-19T02:53:45.000Z')));
/*

select distinct(event) from matches
select * from matches where event = 'GameSpot Versus 120412' order by sort_order;
select * from videos where event = 'GameSpot Versus 120412'
select * from videos where event = 'GameSpot Versus 120412'

select * from matches order by event, event_part, sort_order;
select * from matches where char1 == 'unknown' or char2 == 'unknown'

*/