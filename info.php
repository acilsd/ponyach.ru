<?php

#$count ='24;23;b=706342:1473;r34=12389:22;d=3818:25;oc=368:31;rf=2048:6;test=301:7;vg=2999:58;dev=188:6;sss=1141:130;tea=4605:24;dollchan=19:2;changelog=0:62';
#die($count);

require_once ("config.php");
require KU_ROOTDIR . 'inc/functions.php';

session_set_cookie_params (60 * 60 * 24 * 100);
session_start ();
$sid = md5 (session_id ());
session_write_close ();
$is_mod = check_is_mod();

$res = $tc_db -> GetAll ("SELECT ip FROM `".KU_DBPREFIX."online` WHERE ip='{$_SERVER['REMOTE_ADDR']}'");

$current = time ();
if (count ($res) > 0)
        $res = $tc_db -> Execute ("UPDATE `".KU_DBPREFIX."online` SET is_proxy='" . detect_prox() ."', timestamp='".$current."', real_ip='". $real_ip . "', session_md5='". $sid."' WHERE ip='{$_SERVER['REMOTE_ADDR']}'");
else
        $res = $tc_db -> Execute ("INSERT INTO `".KU_DBPREFIX."online` VALUES ('".$current."','{$_SERVER['REMOTE_ADDR']}', '" . detect_prox() . "', '". $real_ip ."', '" . $sid . "', NULL)");

$res = $tc_db -> Execute ("DELETE FROM `".KU_DBPREFIX."online` WHERE timestamp < '".($current - 900)."'");

if (!isset($included)) {
$count = $tc_db -> GetOne ("SELECT count(*) FROM `".KU_DBPREFIX."online`");

$speed = (int)$tc_db -> GetOne ("SELECT count(*) FROM `".KU_DBPREFIX."posts` WHERE boardid = 7 AND timestamp >= ".(time () - 3600));
}

if ($_GET['x'] === '1') {
	$stats = ';b=706342:1473;r34=12389:22;d=3818:25;oc=368:31;rf=2048:6;test=301:7;vg=2999:58;dev=188:6;sss=1141:130;tea=4605:24;dollchan=19:2;changelog=0:62';
#	$boards = $tc_db->GetAll('select id from boards;');
#	$res = Array();
#	foreach($boards as $row) {
#	$board = $row['id'];
	#if ($is_mod) {
		$res = $tc_db->GetAll('select b.name as board, sc.posts, sc.threads from stat_counters sc inner join boards b on sc.board_id = b.id');
##		$res[] = Array(
##			'posts' => $tc_db->GetOne('select max(id) from posts where boardid = '  .$tc_db->qstr($board)),
##			'threads' => $tc_db->GetOne('select max(id) from posts where boardid = '  .$tc_db->qstr($board) . ' and parentid = 0'),
##			'name' => board_id_to_name($boardid)
##		);
###		$res[] = Array('posts' => $tc_db->GetOne('select timestamp from posts where boardid = '.$tc_db->qstr($board).' and IS_DELETED=0 order by timestamp desc limit 1;'), 'threads' => $tc_db->GetOne('select timestamp from posts where boardid = '.$tc_db->qstr($board).'  and parentid=0 and IS_DELETED=0 order by timestamp desc limit 1;'), 'board' => board_id_to_name($board));
#//		$res = $tc_db->GetAll("select x3.name as board, x1.count as posts, x2.count as threads from (select p.boardid, count(p.id) as count from posts p where p.IS_DELETED=0 group by p.boardid) x1 inner join (select p.boardid, count(p.id) as count from posts p where p.parentid=0 and p.IS_DELETED=0 group by p.boardid) x2 on x1.boardid = x2.boardid inner join boards x3 on x1.boardid = x3.id;");
#
	#} else {
#		$res = $tc_db->GetAll('select * from stats');
##		$res[] = Array(
##			'posts' => $tc_db->GetOne('select max(id) from posts where boardid = '  .$tc_db->qstr($board) . ' and IS_DELETED = 0 and premod = 0'),
##			'threads' => $tc_db->GetOne('select max(id) from posts where boardid = '  .$tc_db->qstr($board) . ' and parentid = 0' . ' and IS_DELETED = 0 and premod = 0'),
##			'name' => board_id_to_name($boardid)
##		);
###		$res[] = Array('posts' => $tc_db->GetOne('select timestamp from posts where boardid = '.$tc_db->qstr($board).' and IS_DELETED=0 order by timestamp desc limit 1;'), 'threads' => $tc_db->GetOne('select timestamp from posts where boardid = '.$tc_db->qstr($board).'  and parentid=0 and IS_DELETED=0 order by timestamp desc limit 1;'), 'board' => board_id_to_name($board));
#//		$res = $tc_db->GetAll("select x3.name as board, x1.count as posts, x2.count as threads from (select p.boardid, count(p.id) as count from posts p where p.IS_DELETED=0 and p.premod = 0 group by p.boardid) x1 inner join (select p.boardid, count(p.id) as count from posts p where p.parentid=0 and p.IS_DELETED=0 group by p.boardid) x2 on x1.boardid = x2.boardid inner join boards x3 on x1.boardid = x3.id;");
	#}
##	}
###//	$res = $tc_db->GetAll("select x1.name as board, x1.count as posts, x2.count as threads from (select b.name, count(p.id) as count from posts p inner join boards b on p.boardid=b.id where p.IS_DELETED=0 group by p.boardid) x1 inner join (select b.name, count(p.id) as count from posts p left join boards b on p.boardid=b.id where p.parentid=0 and p.IS_DELETED=0 group by p.boardid) x2 on x1.name = x2.name;");
	$res[] = Array('board' => 'changelog',
			'threads' => $tc_db->GetOne("select count(*) from changelog"),
			'posts' => 0);
	foreach ($res as $line) {
		$out[] = $line['board']. '=' .$line['posts']. ':' .$line['threads'];
	}
	$stats = ';'. implode(';', $out);
}

if (isset ($included)) {
	//$result["count"] = $count;
	//$result["speed"] = $speed;
} else {
	echo $count, ';', $speed, $stats;
}

?>
