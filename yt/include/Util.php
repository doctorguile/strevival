<?php

class Util {

    static function htmlescape($text) {
        return htmlspecialchars($text, ENT_QUOTES, "UTF-8");
    }

    static function convertTimeStamp($start) {
        if (strstr($start, ':')) {
            $parts = explode(":", $start);
            $start = (intval($parts[0]) * 60) + intval($parts[1]);
        } else {
            $start = intval($start);
        }
        return $start;
    }

    /**
     * @param string $key
     * @param array[] $data
     * @return array
     */
    static function array_pluck($key, $data) {
        $map = array();
        foreach ($data as $array) {
            if (array_key_exists($key, $array)) {
                $map[] = $array[$key];
            }
        }
        unset($array);

        return $map;
    }

}
