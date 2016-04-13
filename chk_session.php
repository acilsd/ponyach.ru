<?php
require_once ("config.php");
require_once ("inc/func/ip.php");

session_set_cookie_params(60 * 60 * 24 * 100);
session_start();

if ($_SESSION['auth'] !== 2) {
	if ($tc_db->GetOne("select id from posts where ipmd5=". $tc_db->qstr(md5($real_ip)) ." limit 1;")) {
		$_SESSION['auth'] = 2;
	}
}

if ($_SESSION['auth'] !== 2) {
	session_write_close();
	header ("Cache-Control: no-cache, must-revalidate"); //HTTP 1.1
	header ("Pragma: no-cache"); //HTTP 1.0
	header ("Location: /404.html");
	exit;
}
session_write_close();
?>
