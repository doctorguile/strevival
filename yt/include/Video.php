<?php

class Video {

    static function prepareInsertNewVideoStmt() {
        if (!isset(DB::$stmt['insertVideo'])) {
            $insert = "INSERT INTO videos (yt_id, channel, title, published, content, state, event, event_part, contributor, ipaddress)
             VALUES (:yt_id, :channel, :title, :published, :content, :state, :event, :event_part, :contributor, :ipaddress)";
            DB::$stmt['insertVideo'] = DB::get()->prepare($insert);
            if ($_SERVER && isset($_SERVER['REMOTE_ADDR'])) {
                DB::$stmt['insertVideo']->bindValue(':ipaddress', $_SERVER['REMOTE_ADDR']);
            } else {
                DB::$stmt['insertVideo']->bindValue(':ipaddress', '');
            }
            DB::$stmt['insertVideo']->bindValue(':contributor', '');
            DB::$stmt['insertVideo']->bindValue(':state', '');
        }
    }

    static function sanitizeYoutubeID($yt_id) {
        return preg_replace("@[^a-zA-Z0-9_-]@", "", $yt_id);
    }

    static function extractYoutubeIDFromUrl($url_or_id) {
        $qstr = parse_url($url_or_id, PHP_URL_QUERY);
        if ($qstr) {
            parse_str($qstr, $attrs);
            if ($attrs && isset($attrs['v']))
                $url_or_id = $attrs['v'];
        }
        return self::sanitizeYoutubeID($url_or_id);
    }

    /**
     * @param SimpleXMLElement | string $youtubeVideo - sxml or youtube video id or url
     * @return SimpleXMLElement
     */
    static function youtubeIDToSxml($youtubeVideo) {
        if (is_string($youtubeVideo)) {
            $yt_id = self::extractYoutubeIDFromUrl($youtubeVideo);
            $content = @ file_get_contents("http://gdata.youtube.com/feeds/api/videos/$yt_id");
            return simplexml_load_string($content);
        }
        return $youtubeVideo;
    }

    static function isValid($assocArray) {
        return ($assocArray && $assocArray['yt_id'] && $assocArray['title'] && $assocArray['channel'] && $assocArray['published']);
    }

    static function parseEventPart($event) {
        $eventpart = 1;
        $pattern = "@\\s*-\\s*(\\d{1,2})\\s*/\\s*\\d{1,2}\\s*$@";
        if (preg_match($pattern, $event, $matches)) {
            $eventpart = intval($matches[1]);
            $event = trim(preg_replace($pattern, "", $event));
        } else {
            $pattern = "@\\s*-\\s*(\\d{1,2})\\s*$@";
            if (preg_match($pattern, $event, $matches)) {
                $eventpart = intval($matches[1]);
                $event = trim(preg_replace($pattern, "", $event));
            } else {
                $pattern = "@\\s*\\[\\s*(\\d{1,2})\\s*\\]\\s*$@";
                if (preg_match($pattern, $event, $matches)) {
                    $eventpart = intval($matches[1]);
                    $event = trim(preg_replace($pattern, "", $event));
                }
            }
        }
        return array($event, $eventpart);
    }

    /**
     * @param SimpleXMLElement | string $youtubeVideo - sxml or youtube video id or url
     * @return array|null
     */
    static function parseVideo($youtubeVideo) {
        $sxml = self::youtubeIDToSxml($youtubeVideo);
        $result = null;
        if ($sxml) {
            $result = array(
                'yt_id' => preg_replace("@.*/@", "", (string)$sxml->id),
                'title' => (string)$sxml->title,
                'channel' => (string)$sxml->author->name,
                'published' => strtotime($sxml->published),
                'content' => (string)$sxml->content,
            );
        }

        if (self::isValid($result)) {
            if ($result['channel'] == 'supersf2turbo') {
                list($event, $eventpart) = self::parseEventPart($result['title']);
                $result['event'] = $event;
                $result['event_part'] = $eventpart;
            } else {
                $result['event'] = $result['title'];
                $result['event_part'] = 1;
            }
        }
        return $result;
    }

    static function findNotProcessed() {
        $stmt = "select * from videos where state = '" .
            YouTubeST::$VIDEO_STATE_CANNOTPARSE . "' order by published, title";
        $stmt = DB::get()->prepare($stmt);
        $stmt->execute();
        return $stmt;
    }

    static function findProcessed() {
        $stmt = "select * from videos where state != '" .
            YouTubeST::$VIDEO_STATE_CANNOTPARSE . "' order by state desc, id desc";
        $stmt = DB::get()->prepare($stmt);
        $stmt->execute();
        return $stmt;
    }

    static function flashHtmlCode($yt_id, $start = 0, $autoplay = false) {
        if ($autoplay) {
            $autoplay = "&autoplay=1";
        }
        return <<<EOF
<object width="420" height="315">
    <param name="movie" value="http://www.youtube.com/v/$yt_id&hl=en&fs=1&rel=0&start=$start{$autoplay}"></param>
    <param name="allowFullScreen" value="true"></param>
    <param name="allowscriptaccess" value="always"></param>
    <embed src="http://www.youtube.com/v/$yt_id&hl=en&fs=1&rel=0&start=$start{$autoplay}"
           type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="480"
           height="385"></embed>
</object>
EOF;

    }

}
