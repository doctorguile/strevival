<?php

class Html {

    static function charDropdown($attrs) {
        echo "<select class=jqueryCombo";
        foreach ($attrs as $k => $v) {
            echo " $k='$v'";
        }
        echo ">";
        foreach (Globals::$characters as $c) {
            echo "<option value='$c'>$c</option>";
            echo "<option value='o.$c'>o.$c</option>";
        }
        echo "</select>";
    }
}
