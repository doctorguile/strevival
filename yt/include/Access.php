<?php

class Access {
    static function enforce() {
        //put sha1() encrypted password here
        $password = '4acebef29d98e2b58085d7481c92130b33d5df6b';
        session_start();
        if (!isset($_SESSION['loggedIn'])) {
            $_SESSION['loggedIn'] = false;
        }

        if (isset($_POST['password'])) {
            if (sha1($_POST['password']) == $password) {
                $_SESSION['loggedIn'] = true;
            } else {
                die ('Incorrect password');
            }
        }
        session_write_close();
        if (!$_SESSION['loggedIn']) {
            echo <<<EOF
<html><head><title>Login</title></head>
<body>
<p>You need to login</p>
<form method="post">
    Password: <input type="password" name="password"> <br />
    <input type="submit" name="submit" value="Login">
</form>
</body>
</html>
EOF;
            exit();
        }

    }
}