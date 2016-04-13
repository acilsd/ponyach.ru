<?php
require_once ("config.php");
require_once ("inc/func/ip.php");

if (is_reader()) echo 'Вам не нужно вводить капчу.<script>document.getElementsByClassName(\'hidethis\')[0].style.display=\'none\';</script>';
session_set_cookie_params(60 * 60 * 24 * 100);
session_start();
if ($_SESSION['score'] > 0) {
	echo 'Вам не нужно вводить капчу.';
	session_write_close();
	exit;
}
session_write_close();

$ws = $tc_db -> GetOne ("SELECT SCORE FROM `score` WHERE `ip` = '" . $_SERVER['REMOTE_ADDR'] . "';");
$ws2 = $tc_db->GetOne('select status from captcha_status where ( session_md5 = md5('. $tc_db->qstr(session_id()) .') or ipmd5 = '.md5($_SERVER['REMOTE_ADDR'] ).' ) and status = 0 limit 1');
if (($ws>0 || $ws2>0) && is_reader()) {
echo 'Вам не нужно вводить капчу.<script>document.getElementsByClassName(\'hidethis\')[0].style.display=\'none\';</script>';
} else {
echo '<script> $(\'#lok\').detach(); $(\'#cpt\').fadeIn(\'slow\'); document.getElementsByClassName(\'hidethis\')[0].style.display=\'none\';</script>'; }
?>
