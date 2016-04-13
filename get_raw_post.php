<?php
require 'config.php';
require KU_ROOTDIR . 'inc/functions.php';
require KU_ROOTDIR . 'inc/classes/board-post.class.php';

$board = $_GET['b']; $post = $_GET['p'];

if (isset($_GET['pw'])) {
	$passwd = base64_decode ($_GET['pw'], 'true');
} else {
	$passwd = 'fake-notmd5';
}

if (!(ctype_alnum($board) && ctype_alnum($post) ))
	die(''); // no messing around with get variables;

$boardid = board_name_to_id($board);
$board_class = new Board($board);

$status = 1; //fail
$error = 'Этот пост редактировать нельзя.';
$sid = md5 (session_id ());
$is_mod = check_is_mod();
if ($board_class->board['edit'] != 0 || $is_mod || ($post == 1326990)) {

	// you can edit post either using password or if you have the save ip or session
	if ($is_mod) {
		// mods can edit whatever the fuck they like on their boards
		require_once KU_ROOTDIR . 'inc/classes/manage.class.php';
		if (Manage::CurrentUserIsModeratorOfBoard($board, $_SESSION['manageusername']))
			$query = 'select p.bumped, p.subject, p.name, p.email, p.raw_message from posts p where p.boardid = ' .$tc_db->qstr($boardid) .' and p.id = ' .$tc_db->qstr($post);
	} else {
		$query = 'select p.bumped, p.subject, p.name, p.email, p.raw_message from posts p where ( (p.password = ' .$tc_db->qstr($passwd). ' ) or (p.ipmd5 = md5(' .$tc_db->qstr($real_ip). ')) or (p.session_md5 = md5(' .$tc_db->qstr($sid). ')) ) and (p.boardid = ' .$tc_db->qstr($boardid) .' and p.id = ' .$tc_db->qstr($post) .')';
	}

	$res = $tc_db->GetRow($query);
	if (($post == 1326990) || $is_mod || ($board_class->board['edit_timeout'] == 0) || ((time() - $board_class->board['edit_timeout']) < $res['bumped'] )) {
		if ($res) {
			$status = 0; $error = 'Редактируй на здоровье.';
		}
	} else {
		$status = 3;
		$error = 'Этот пост уже поздно редактировать.';
	}
} else {
	$status = 2;
	$error = 'На этой доске посты редактировать нельзя.';
}

$res['status'] = $status;
$res['error'] = $error;

echo json_encode($res);
?>
