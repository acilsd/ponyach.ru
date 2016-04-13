<?php

// a very stupid and straightforward password checker
// to be replaced

$key = trim (str_replace (' ', '', $_POST['key']));

session_set_cookie_params(60 * 60 * 24 * 100);
session_start();
if ($_SESSION['auth'] !== 1) {
	session_write_close();
	header("Location: /404.html");
	exit;
}


if (! (isset ($key) && ctype_alnum ($key))) {
        header ("Cache-Control: no-cache, must-revalidate"); //HTTP 1.1
        header ("Pragma: no-cache"); //HTTP 1.0
        header ("Location: /404.html");
	exit;
}

if (strtolower ($key) === 'darkside') {
	$_SESSION['auth'] = 2;
	session_write_close();
        header("Location: /r34");
        exit;
} else {
	session_write_close();
        header ("Cache-Control: no-cache, must-revalidate"); //HTTP 1.1
        header ("Pragma: no-cache"); //HTTP 1.0
        header ("Location: /404.html");
	exit;
}

?>
