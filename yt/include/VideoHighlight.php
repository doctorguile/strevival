<?php

class VideoHighlight {

    function getAllMoments() {
        $stmt = "select * from moments order by character";
        $stmt = DB::get()->prepare($stmt);
        $stmt->execute();
        return $stmt;
    }

    function prepareInsertNewMomentStmt() {
        if (!isset(DB::$stmt['insertMoment'])) {
            $insert = "INSERT INTO moments (character, description, event, yt_id, start, contributor, ipaddress)
             VALUES (:character, :description :event, :yt_id, :start, :contributor, :ipaddress)";
            DB::$stmt['insertMoment'] = DB::get()->prepare($insert);
            if ($_SERVER && isset($_SERVER['REMOTE_ADDR'])) {
                DB::$stmt['insertMoment']->bindValue(':ipaddress', $_SERVER['REMOTE_ADDR']);
            } else {
                DB::$stmt['insertMoment']->bindValue(':ipaddress', '');
            }
            DB::$stmt['insertMoment']->bindValue(':contributor', '');
        }
    }

    function addContributtedMoment($entry, $userdata) {
        $this->prepareInsertNewMomentStmt();
        DB::$stmt['insertMoment']->bindValue(':yt_id', $entry['yt_id']);
        DB::$stmt['insertMoment']->bindValue(':event', $entry['title']);
        DB::$stmt['insertMoment']->bindValue(':character', $userdata['character']);
        DB::$stmt['insertMoment']->bindValue(':description', $userdata['description']);
        DB::$stmt['insertMoment']->bindValue(':start', $userdata['start']);
        DB::$stmt['insertMoment']->bindValue(':contributor', $userdata['contributor']);
        DB::$stmt['insertMoment']->execute();
        return $entry;
    }

}
