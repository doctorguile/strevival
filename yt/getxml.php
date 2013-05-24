<?php
function savefile($i, $content) {
    file_put_contents("xml/$i.xml", $content);
}

function getxml() {
    $url = 'https://gdata.youtube.com/feeds/api/users/supersf2turbo/uploads';

    $i = 1;
    $content = file_get_contents($url);
    savefile($i, $content);
    $sxml = simplexml_load_string($content);
//$sxml = simplexml_load_file('yt.xml');
    $result = $sxml->xpath("*[local-name()='link' and @rel='next']");
    while (!empty($result)) {
        $i++;
        $node = $result[0];
        $url = (string)$node->attributes()->href;
        $content = file_get_contents($url);
        savefile($i, $content);
        $sxml = simplexml_load_string($content);
        $result = $sxml->xpath("*[local-name()='link' and @rel='next']");
    }

//print_r($result);

}