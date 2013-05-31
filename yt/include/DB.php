<?php

class DB {

    /** @var PDOStatement[] $stmt */
    public static $stmt = array();

    /** @var PDO $__db */
    private static $__db;

    /**
     * @return PDO
     */
    static function get() {
        if (!self::$__db) {
            $dbpath = 'sqlite:' . dirname(__FILE__) . '/../db/yt.sqlite3';
            self::$__db = new PDO($dbpath);
        }
        return self::$__db;
    }

    static function rowExists($table, $k, $v) {
        $stmt = self::get()->prepare("SELECT * FROM $table WHERE $k = :v");
        $stmt->bindValue(':v', $v);
        $stmt->execute();
        $row = $stmt->fetch();
        $result = !empty($row);
        $stmt->closeCursor();
        return $result;
    }

    static function interpolateQuery($query, $params) {
        $keys = array();
        $values = $params;

        # build a regular expression for each parameter
        foreach ($params as $key => $value) {
            if (is_string($key)) {
                $keys[] = '/:' . $key . '/';
            } else {
                $keys[] = '/[?]/';
            }

            if (is_array($value))
                $values[$key] = implode(',', $value);

            if (is_null($value))
                $values[$key] = 'NULL';
        }
        // Walk the array to see if we can add single-quotes to strings
        array_walk($values, create_function('&$v, $k', 'if (!is_numeric($v) && $v!="NULL") $v = "\'".$v."\'";'));

        $query = preg_replace($keys, $values, $query, 1, $count);

        return $query;
    }
}
