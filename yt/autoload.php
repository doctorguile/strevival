<?php
date_default_timezone_set('America/Los_Angeles');

function locateFile($filename, $dir) {
    if (file_exists($dir . '/' . $filename)) {
        return $dir . '/' . $filename;
    }
    $children = scandir($dir);
    foreach($children as $subdir) {
        if ($subdir == '.' || $subdir == '..' || !is_dir($dir . '/' . $subdir)) continue;
        $result = locateFile($filename, $dir . '/' . $subdir);
        if ($result) return $result;
    }
    return null;
}

function __autoload($classname) {
    $dir = dirname(__FILE__);
    $filename = $classname . '.php';
    if (file_exists("$dir/include/$filename")) {
        require_once("$dir/include/$filename");
        return true;
    }

    $path = locateFile($filename, $dir);
    if ($path) {
        require_once($path);
    }
    return false;
}

