<?php
require_once ("config.php");
require_once ("inc/func/ip.php");
$res = $tc_db->GetOne("select count(*) from cgi_prox_cache where ip=". $tc_db->qstr($real_ip) .";");
$token = md5(microtime() . 'для пущей секьюрности тут слово хуй');
if ($res>0) {
	$tc_db->Execute("update cgi_prox_cache set `key` = ". $tc_db->qstr($token) ." where `ip` = ". $tc_db->qstr($real_ip) .";");
} else {
	$tc_db->Execute("insert into cgi_prox_cache (`ip`, `key`) values (". $tc_db->qstr($real_ip) .", ". $tc_db->qstr($token) .");");
}
echo $token;
?>
