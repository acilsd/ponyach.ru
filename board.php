<?php
/*
 * This file is part of kusaba.
 *
 * kusaba is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * kusaba is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * kusaba; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 * +------------------------------------------------------------------------------+
 * kusaba - http://www.kusaba.org/
 * Written by Trevor "tj9991" Slocum
 * http://www.tj9991.com/
 * tslocum@gmail.com
 * +------------------------------------------------------------------------------+
 */
/**
 * Board operations which available to all users
 *
 * This file serves the purpose of providing functionality for all users of the
 * boards. This includes: posting, reporting posts, and deleting posts.
 *
 * @package kusaba
 */

// }}}
// {{{ Fake email field check

if (isset($_POST['email']) && !empty($_POST['email'])) {
	die('Spam bot detected');
}

if (!isset($_POST['board'])) xdie ('[my shed] --->you---> out');

header("Access-Control-Allow-Origin: http://dev.ponyach.ru");

// Require the configuration file, functions file, board and post class, bans class, and posting class
require 'config.php';
require KU_ROOTDIR . 'inc/functions.php';
require KU_ROOTDIR . 'inc/classes/board-post.class.php';
require KU_ROOTDIR . 'inc/classes/bans.class.php';
require KU_ROOTDIR . 'inc/classes/posting.class.php';
require KU_ROOTDIR . 'inc/classes/parse.class.php';
require_once KU_ROOTDIR . 'inc/api/config.php';
require_once KU_ROOTDIR . 'inc/api/debug.php';
require_once KU_ROOTDIR . 'inc/api/cache.php';

bdl_debug ('starting');
$bans_class = new Bans();
bdl_debug ('bans done');
$parse_class = new Parse();
bdl_debug ('parse done');
$posting_class = new Posting();
bdl_debug ('posting done');

// {{{ Module loading

modules_load_all();
bdl_debug ('modules done');


// }}}
// {{{ GET/POST board send check

// In some cases, the board value is sent through post, others get
if (isset($_POST['board']) || isset($_GET['board'])) $_POST['board'] = (isset($_GET['board'])) ? $_GET['board'] : $_POST['board'];

// }}}

// If the script was called using a board name:
if (isset($_POST['board'])) {
	//$board_name = $tc_db->GetOne("SELECT `name` FROM `" . KU_DBPREFIX . "boards` WHERE `name` = " . $tc_db->qstr($_POST['board']) . "");
	
	//disable names in thread
	//if ($_POST['replythread'] == 9245){
	//unset($_POST['name']);
	//}
	
	if(board_exists($_POST['board'])) {
		bdl_debug ('bord exist done - yes');
		$board_name = $_POST['board'];
		$board_class = new Board($board_name);
		bdl_debug ('bord class done');
		if (!empty($board_class->board['locale'])) {
			changeLocale($board_class->board['locale']);
		bdl_debug ('bord locale done');
		}
	} else {
		bdl_debug ('bord exist done - no');
		do_redirect(KU_WEBPATH);
	}
} else {
	// A board being supplied is required for this script to function
	do_redirect(KU_WEBPATH);
}

//bdl_debug(serialize($_POST));
//bdl_debug(serialize($_FILES));

$editpost = false;
if (isset($_POST['editpost']) && is_numeric($_POST['editpost'])) {
	$is_mod = check_is_mod();
	if (!$is_mod && $board_class->board['edit'] != 1) 
		xdie('На этой доске нельзя редактировать посты');
	$post = $_POST['editpost'];
	$sid = md5 (session_id ());
	$boardid = board_name_to_id($board_name);
	if ($is_mod) {
		// mods can edit whatever the fuck they like on their boards
		require_once KU_ROOTDIR . 'inc/classes/manage.class.php';
		if (Manage::CurrentUserIsModeratorOfBoard($board_name, $_SESSION['manageusername']))
			$query = 'select p.raw_message from posts p where p.boardid = ' .$tc_db->qstr($boardid) .' and p.id = ' .$tc_db->qstr($post);
	} else {
		$query = 'select p.bumped, p.raw_message from posts p where ( (p.ipmd5 = md5(' .$tc_db->qstr($real_ip). ')) or (p.session_md5 = md5(' .$tc_db->qstr($sid). ')) ) and (p.boardid = ' .$tc_db->qstr($boardid) .' and p.id = ' .$tc_db->qstr($post) .') and p.IS_DELETED = 0';
	}
	$res = $tc_db->GetRow($query);
	if (!$post == 1326990 && !$is_mod && (($board_class->board['edit_timeout'] != 0) && ((time() - $board_class->board['edit_timeout']) > $res['bumped'] ))) {
		bdl_debug (time() . ' - '. $board_class->board['edit_timeout'] . ' xxxxx ' . $res['bumped']);
		xdie ('Этот пост редактировать нельзя.');
	}
	if ($res) {
		$editpost = $post;
	} else {
		xdie ('Этот пост редактировать нельзя.');
	}
}

bdl_debug ('check1 done');
// {{{ Expired ban removal, and then existing ban check on the current user

#if (isset($_POST['message'])) {
#	$bans_class->BanCheck($real_ip, $board_class->board['name'], false, message);
#	//bdl_debug('qq Checking ban in thread');
#	$bans_class->CheckThreadBan(board_name_to_id($board_class->board['name']), $_POST['replythread']);
#}else{
	$bans_class->BanCheck($real_ip, $board_class->board['name'], null, null, null, $_POST['replythread']);
	$bans_class->CheckThreadBan(board_name_to_id($board_class->board['name']), $_POST['replythread']);
#}
bdl_debug ('bans check done');

// }}}

/* Ensure that UTF-8 is used on some of the post variables */
$posting_class->UTF8Strings();
//	$posting_class->CheckReplyTime(); // WTF ?????
	$posting_class->CheckReplyTime();
bdl_debug ('reply time check done');

/* Check if the user sent a valid post (image for thread, image/message for reply, etc) */
if ($posting_class->CheckValidPost()) {
	require_once KU_ROOTDIR . 'inc/api/cache.php';
	bdl_debug ('cache inval 1');
	//cache_inval (Array ("mode=board", "board_name=".$_POST['board']));
	//cache_inval (Array ("mode=new"));

	$tc_db->Execute("START TRANSACTION");

	//$posting_class->CheckReplyTime();
	$posting_class->CheckMessageLength();
	$posting_class->CheckCaptcha();
	$posting_class->CheckBannedHash();
	$posting_class->CheckBlacklistedText();
	bdl_debug ('more fucking checks done');
	$post_isreply = $posting_class->CheckIsReply();

	if (!$post_isreply) {
		bdl_debug ('this is a new thread');
		//$posting_class->CheckReplyTime();
		$posting_class->CheckNewThreadTime();
	}

	// HACK linux-thread
	bdl_debug ($_SERVER['HTTP_USER_AGENT']);
	bdl_debug ($_POST['replythread']);
	bdl_debug ($board_class->board['name']);
	if($_POST['replythread'] == '1216472' && $board_class->board['name'] == 'b' && (strpos($_SERVER['HTTP_USER_AGENT'], 'Windows') !== false)) {
		bdl_debug ('windows user');
		$balmer = Array (
				'abc27e8d83d87d5e0008d6cf496365bc', 
				'f0ee32a905b235aa0945b955be3ceeef', 
				'30fd8bc58bf906e421df197d2511bfce', 
				'59fd344c4db1e81fca0e85f5da8ed1ee', 
				'84749aede0b4a7a8a4c949f91b7853f1', 
				'2bbcb58faf3dc78fcfc1706fe507c8df', 
				'7651f6b1b391d83932477ef0e3e872ba', 
				'8b9902e8f1642b3eb102a38cb0488e8e', 
				'9099e5f430c566b8ea532658f69344bb' 
		);
		if (is_array($_FILES)) {
			foreach ($_FILES as $key => $val) {
				unset($_FILES[$key]);
			}
		}
		for ($x = 1; $x <= $board_class->board['maximages']; $x++) {
			if (isset($_POST['md5-'.$x])) unset($_POST['md5-'.$x]);
		}
		$balmer_key = array_rand($balmer);
		$balmer = $balmer[$balmer_key];
		bdl_debug ('adding balmer - ' . $balmer);
		$_POST['md5-1'] = $balmer;
		$_POST['name'] = 'Steve Ballmer';
	}

	$num_images = 0;
	$have_images = false;
	
	//bdl_debug (var_export($_FILES));
	$x = 1;
	if (isset($_FILES['upload'])) { //['tmp_name']) && count($_FILES['upload']['tmp_name']) > 0) {
		if (is_array($_FILES['upload']['tmp_name'])) {
			foreach ($_FILES['upload']['tmp_name'] as $f) {
				// do not count this file if it has md5 field set, since this will result in olny one picture posted.
				if (trim($f) != '' && (!isset($_POST['md5-'.$x]) || trim($_POST['md5-'.$x]) == '')) {
					bdl_debug ('adding file to image count tmp_name = '. $f .' md5-'.$x . ' = ' . $_POST['md5-'.$x]);
					$num_images++;
				}
				$x++;
			}
		} else {
			$num_images = 1;
		}
		bdl_debug ("files " . $num_images);
	}

//	// random pic hack
//	if ($tc_db->GetOne('select official_id from posts p join boards b on p.boardid = b.id where p.id = ' . $tc_db->qstr($_POST['replythread']) . ' and b.name = ' . $tc_db->qstr($board_class->board['name'])) == 2 ) { // general thread only
//		if (is_array($_FILES)) {
//			foreach ($_FILES as $key => $val) {
//				unset($_FILES[$key]);
//			}
//		}
//		for ($x = 1; $x <= $board_class->board['maximages']; $x++) {
//			if (isset($_POST['md5-'.$x])) unset($_POST['md5-'.$x]);
//		}
//
//		$file = $tc_db->GetOne('select fileid from posts_files order by rand() where boardid != 8 limit 1');
//		$res = $tc_db->GetRow(' select f.md5_light from files f where id = ' . $file. ' limit 1');
//		$res2 = $tc_db->GetRow(' select ratingid from posts_files where fileid = ' . $file. ' limit 1');
//		$_POST['md5-1'] = $res['md5_light'];
//		if ($res2) {
//			$_POST['upload-rating-1'] = $res2;
//		}
//		if ($tc_db->GetOne('select boardid from posts_files where fileid = ' . $file. ' limit 1') == 8) {
//			$_POST['upload-rating-1'] = 12;
//		}
//
//		if (rand(0,9) == 0) {
//		$file = $tc_db->GetOne('select fileid from posts_files order by rand() where boardid != 8 limit 1');
//		$res = $tc_db->GetRow(' select f.md5_light from files f where id = ' . $file. ' limit 1');
//		$res2 = $tc_db->GetRow(' select ratingid from posts_files where fileid = ' . $file. ' limit 1');
//		$_POST['md5-2'] = $res['md5_light'];
//		if ($res2) {
//			$_POST['upload-rating-2'] = $res2;
//		}
//
//		if ($tc_db->GetOne('select boardid from posts_files where fileid = ' . $file. ' limit 1') == 8) {
//			$_POST['upload-rating-2'] = 12;
//		}
//		}
//
//	}

	for ($x = 1; $x <= $board_class->board['maximages']; $x++) {
		if (isset($_POST['md5-'.$x]) && trim($_POST['md5-'.$x]) != '') {
			$num_images++;
			bdl_debug ('md5-'.$x. ' == ' . $_POST['md5-'.$x]); 
		}
	}

	if ($num_images > $board_class->board['maximages']) {
		exitWithErrorPage('Многовато картинок, в /'. $board_class->board['name'].'/ можно максимум '.$board_class->board['maximages'].'.');
	}

	if ($num_images > 0) $have_images = true;

	$premod = 0;
	$thread_name = '';
	if ($post_isreply) {
		list($thread_replies, $thread_locked, $thread_replyto, $thread_premod, $thread_name) = $posting_class->GetThreadInfo($_POST['replythread']);
		//bdl_debug ('thread replies = ' . $thread_replies . ' locked = '. $thread_locked . ' replyto = ' . $thread_replyto . ' premod = ' .$thread_premod);
		if ($thread_premod == 2) $premod = 1;
	} else {

		$thread_replies = 0;
		$thread_locked = 0;
		$thread_replyto = 0;
	}

	//bdl_debug ('post premod = ' . $premod);
	bdl_debug ('starting post fields');
	list($post_name, $post_email, $post_subject) = $posting_class->GetFields();
	$post_password = isset($_POST['postpassword']) ? $_POST['postpassword'] : '';
	
	$post_ratings = Array();
	for ($x = 0; $x <= $board_class->board['maximages']; $x++) {
		if (is_numeric($_POST['upload-rating-' . $x]) && isset($board_class->board['ratings'][ $_POST['upload-rating-' . $x] ])){
			$post_ratings[$x-1] = $_POST['upload-rating-' . $x];
		}else{
			$post_ratings[$x-1] = false;
		}
	}

	bdl_debug ('ratings: ' . serialize($post_ratings));

	bdl_debug ('done post fields');
	list($user_authority, $flags) = $posting_class->GetUserAuthority();
	bdl_debug ('modpassword done');

	$post_fileused = false;
	$post_autosticky = false;
	$post_autolock = false;
	$post_displaystaffstatus = false;
	$file_is_special = false;

	$raw_message = $_POST['message']; // save the initial message for editing purpose
	$is_repost = false;
	if (preg_match('~^>>[0-9]+r~i', $raw_message) !== 0) {
		preg_match('~[0-9]+~', $raw_message, $matches);
		$_POST['message'] = preg_replace('~^>>[0-9]+r~i', '', $_POST['message']);
		$is_repost = $matches[0];
		bdl_debug ('this is a repost');
			
	}


	if (isset($_POST['formatting'])) {
		if ($_POST['formatting'] == 'aa') {
			$_POST['message'] = '[aa]' . $_POST['message'] . '[/aa]';
		}

		if (isset($_POST['rememberformatting'])) {
			setcookie('kuformatting', urldecode($_POST['formatting']), time() + 31556926, '/', KU_DOMAIN);
		}
	}

	$result = $tc_db->GetOne("SELECT max(id) FROM `" . KU_DBPREFIX . "posts` WHERE `boardid` = " . $board_class->board['id']);
	if ($result > 0)
		$nextid = $results[0]['id'] + 1;
	else
		$nextid = 1;
	$parse_class->id = $nextid;

	// If they are just a normal user, or vip...
	if (isNormalUser($user_authority)) {
		// If the thread is locked
		if ($thread_locked == 1 && $post != 1326990) {
			// Don't let the user post
			exitWithErrorPage(_gettext('Sorry, this thread is locked and can not be replied to.'));
		}

		$post_message = $parse_class->ParsePost($_POST['message'], $board_class->board['name'], $board_class->board['type'], $thread_replyto, $board_class->board['id']);
	// Or, if they are a moderator/administrator...
	} else {
		// If they checked the D checkbox, set the variable to tell the script to display their staff status (Admin/Mod) on the post during insertion
		if (isset($_POST['displaystaffstatus'])) {
			$post_displaystaffstatus = true;
		}

		// If they checked the RH checkbox, set the variable to tell the script to insert the post as-is... (admin only)
		if (isset($_POST['rawhtml']) && $user_authority == 1) {
			$post_message = $_POST['message'];
		// Otherwise, parse it as usual...
		} else {
			$post_message = $parse_class->ParsePost($_POST['message'], $board_class->board['name'], $board_class->board['type'], $thread_replyto, $board_class->board['id']);
		}

		// If they checked the L checkbox, set the variable to tell the script to lock the post after insertion
		if (isset($_POST['lockonpost'])) {
			$post_autolock = true;
		}

		// If they checked the S checkbox, set the variable to tell the script to sticky the post after insertion
		if (isset($_POST['stickyonpost'])) {
			$post_autosticky = true;
		}
		if (isset($_POST['usestaffname']) && isset($_SESSION['manageusername'])) {
			//$_POST['name'] = md5_decrypt($_POST['modpassword'], KU_RANDOMSEED);
			//$post_name = md5_decrypt($_POST['modpassword'], KU_RANDOMSEED);
			$post_name = $_SESSION['manageusername'];
		}
	}

	bdl_debug ('modpassword functions done');
	$posting_class->CheckBadUnicode($post_name, $post_email, $post_subject, $post_message);

	$post_tag = $posting_class->GetPostTag();

	if ($post_isreply) {
		if ($have_images == false && trim($post_message) == '') {
			// this place is strangely deprecated, leave it be
			if ($_POST['md5'] != '' && ctype_alnum($_POST['md5'])) {
				$result = $tc_db->GetOne("select file_md5 from "  . KU_DBPREFIX . "posts where file_md5 = '". $_POST['md5'] . "'");
				if (!$result)
					exitWithErrorPage(_gettext('An image, or message, is required for a reply.'));
			}else
				exitWithErrorPage(_gettext('An image, or message, is required for a reply.'));
		} 
	} else {
		if ($have_images == false && (!($_POST['md5'] != '' && ctype_alnum($_POST['md5']))) && ((!isset($_POST['nofile'])&&$board_class->board['enablenofile']==1) || $board_class->board['enablenofile']==0) && ($board_class->board['type'] == 0 || $board_class->board['type'] == 2 || $board_class->board['type'] == 3)) {
                        if (!isset($_POST['embed']) && $board_class->board['uploadtype'] != 1) {
                                exitWithErrorPage(_gettext('Я не могу создать тред без картинки'));
                        }
                }	
	}

	if (isset($_POST['nofile'])&&$board_class->board['enablenofile']==1) {
		if (trim($post_message) == '') {
			exitWithErrorPage('A message is required to post without a file.');
		}
	}

	if ($board_class->board['type'] == 1 && !$post_isreply && $post_subject == '') {
		exitWithErrorPage('A subject is required to make a new thread.');
	}

	if ($board_class->board['deny_tor'] == 1 && check_tor ($real_ip)) {
		if (!empty($_COOKIE['passcode'])){
			$passcode = substr($_COOKIE['passcode'],0,6);
			$passtor = $tc_db->GetOne("select torpass from passcode where passcode = " .$tc_db->qstr($passcode));
			if ($passtor === '1'){
				bdl_debug("post from TOR passed with approved passcode");
			} else {
				management_addlogentry("denied post in /".$board_class->board['name']."/ from $real_ip - ТОР", 8, 'ponyaba');
				exitWithErrorPage('Извини, из-под тора в /'.$board_class->board['name'].'/ постить нельзя.');
			}
		} else {
			management_addlogentry("denied post in /".$board_class->board['name']."/ from $real_ip - ТОР", 8, 'ponyaba');
			exitWithErrorPage('Извини, из-под тора в /'.$board_class->board['name'].'/ постить нельзя.');		
		}
	}
	

	if ($board_class->board['locked'] == 0 || ($user_authority > 0 && $user_authority != 3)) {
		require_once KU_ROOTDIR . 'inc/classes/upload.class.php';
		bdl_debug ('starting file stuf');
		if ($post_isreply) {
			$upload_class = new Upload(true);
		} else {
			$upload_class = new Upload(false);
		}

            if ($have_images) {
			$upload_class->HandleUpload();
			
				bdl_debug('-passcode image start');
				//adding image name to passcode_files
				if (isset($_COOKIE['passcode'])){
					bdl_debug('-passcode cookie true');
					$passcode = substr($_COOKIE['passcode'],0,6);
					$passcodereal = $tc_db->GetOne('select passcode from passcode where passcode = ' .$tc_db->qstr($passcode));
					if (strlen($passcodereal) > 0){
						bdl_debug('-passcode is real');
						for ($i = 1; $i < 6;$i++){
							$uniqpic = $tc_db->GetOne('select name from passcode_files where passcode = ' .$tc_db->qstr($passcode). ' and name = ' .$tc_db->qstr($upload_class->get_file_names($i)));
							if (!empty($upload_class->get_file_names($i)) && empty($uniqpic)) {
								bdl_debug('-adding image ' .$upload_class->get_file_names($i));
								$tc_db->Execute('insert into passcode_files (passcode , name , original , md5_light) VALUES (' .$tc_db->qstr($passcode). ' , ' .$tc_db->qstr($upload_class->get_file_names($i)). ' , ' .$tc_db->qstr($upload_class->get_file_original($i)). ' , ' .$tc_db->qstr($_POST['md5passcode-'.$i]). ')' );
							}
						}
					}
				}
				bdl_debug('-passcode image end');
				
			bdl_debug ('done file stuf [effects='. $parse_class->effects.']');
		}

		if ($board_class->board['forcedanon'] == '1') {
			if ($user_authority == 0 || $user_authority == 3) {
				$post_name = '';
				$post_subject = '';
			}
		}

		$nameandtripcode = calculateNameAndTripcode($post_name);
		if (is_array($nameandtripcode)) {
			$name = $nameandtripcode[0];
			$tripcode = $nameandtripcode[1];
		} else {
			//$name = $post_name;
			$name = $nameandtripcode;
			$tripcode = '';
		}

		$post_passwordmd5 = ($post_password == '') ? '' : md5($post_password);

		if ($post_autosticky == true) {
			if ($thread_replyto == 0) {
				$sticky = 1;
			} else {
				$result = $tc_db->Execute("UPDATE `" . KU_DBPREFIX . "posts` SET `stickied` = '1' WHERE `boardid` = " . $board_class->board['id'] . " AND `id` = '" . $thread_replyto . "'");
				$sticky = 0;
			}
		} else {
			$sticky = 0;
		}

		if ($post_autolock == true) {
			if ($thread_replyto == 0) {
				$lock = 1;
			} else {
				$tc_db->Execute("UPDATE `" . KU_DBPREFIX . "posts` SET `locked` = '1' WHERE `boardid` = " . $board_class->board['id'] . " AND `id` = '" . $thread_replyto . "'");
				$lock = 0;
			}
		} else {
			$lock = 0;
		}

		if (!$post_displaystaffstatus && $user_authority > 0 && $user_authority != 3) {
			$user_authority_display = 0;
		} elseif ($user_authority > 0) {
			$user_authority_display = $user_authority;
		} else {
			$user_authority_display = 0;
		}

		$post = array();

		$post['board'] = $board_class->board['name'];
		$post['name'] = substr($name, 0, 74);
		if ($post['name'] == '' and $thread_name != '') {
			$post['name'] = $thread_name;
			$post['name_save'] = false;
		} else {
			$post['name_save'] = true;
		}
		$post['tripcode'] = $tripcode;
		$post['email'] = substr($post_email, 0, 74);
		// First array is the converted form of the japanese characters meaning sage, second meaning age
		$ords_email = unistr_to_ords($post_email);
		if (strtolower($_POST['em']) != 'sage' && strtolower($_POST['em']) != 'save' && $ords_email != array(19979, 12370) && strtolower($_POST['em']) != 'age' && $ords_email != array(19978, 12370) && $_POST['em'] != 'return' && $_POST['em'] != 'noko') {
			$post['email_save'] = true;
		} else {
			if (strtolower($_POST['em']) == 'save') 
				$post['name_save'] = false;
			$post['email_save'] = false;
		}
		$post['subject'] = substr($post_subject, 0, 74);
		$post['message'] = $post_message;
		$post['tag'] = $post_tag;

		if (strtolower($_POST['em']) == 'save' && $post['name'] && $thread_replyto == 0) {
			$post['inherit_name'] = 1;
			$post['email'] = '';
		}

		$post = hook_process('posting', $post);

		if ($thread_replyto != '0') {
			if ($post['message'] == '' && KU_NOMESSAGEREPLY != '') {

				$post['message'] = KU_NOMESSAGEREPLY;
			}
		} else {
			if ($post['message'] == '' && KU_NOMESSAGETHREAD != '') {
				$post['message'] = KU_NOMESSAGETHREAD;
			}
		}

		bdl_debug ('adding post');
		$post_class = new Post(0, $board_class->board['name'], $board_class->board['id'], true);
		$included = true; // a hack to update online on posting
		require_once KU_ROOTDIR . 'info.php';

		$file_ids = Array();
		if ($have_images) {
			$file_ids = $upload_class->get_file_ids();
			for ($x = 0; $x <= $board_class->board['maximages']; $x++) {
				//if (isset($file_ids[$x])) 
				$file_ids[$x]['ratingid'] = $post_ratings[$x];
			}
		}

		$mc_key_last_thr = 'last_thread_id_'  . $board_name;
		$last_thread_id = cache_get ($mc_key_last_thr);

		if ($user_authority === 1){
			$mod_mark = "1";
		} else {
			$mod_mark = "0";
		}
		$post_id = $post_class->Insert($thread_replyto, $post['name'], $post['tripcode'], $post['email'], $post['subject'], addslashes($post['message']), $post_passwordmd5, time(), time(), $real_ip, $user_authority_display, $post['tag'], $sticky, $lock, $board_class->board['id'], $file_ids, $raw_message, $is_repost, $premod, $post['inherit_name'], $mod_mark, $editpost);

		if (!$post_id && $is_repost) xdie ('Не нашла такого поста (или это не твой пост?).');

		bdl_debug ('done adding post');
		$post_class->AddReplies($board_class->board['id'], $post_id, $parse_class->found_replies);
		bdl_debug ('done adding replies');

		if ($flags && $user_authority > 0 && $user_authority != 3) {
			$modpost_message = 'Modposted #<a href="' . KU_BOARDSFOLDER . $board_class->board['name'] . '/res/';
			if ($post_isreply) {
				$modpost_message .= $thread_replyto;
			} else {
				$modpost_message .= $post_id;
			}
			$modpost_message .= '.html#' . $post_id . '">' . $post_id . '</a> in /'.$_POST['board'].'/ with flags: ' . $flags . '.';
			management_addlogentry($modpost_message, 1, md5_decrypt($_POST['modpassword'], KU_RANDOMSEED));
		}

		if ($post['name_save'] && isset($_POST['name'])) {
			setcookie('name', urldecode($_POST['name']), time() + 31556926, '/', KU_DOMAIN);
		}

		if ($post['email_save']) {
			setcookie('email', urldecode($post['email']), time() + 31556926, '/', KU_DOMAIN);
		}

		setcookie('postpassword', urldecode($_POST['postpassword']), time() + 31556926, '/');
		bdl_debug ('done cookies and authority');

		// before bumping we need to get number of pages, which get updated by this bump
		$pages_to_regen = 0; // yeah we hope sticked posts would not be on second page
		$old_bump_time = 0; // saving old bump time to check if thread is already on autosage

		// since it is a long query we try guessing if the post is in the same thread that the previous one,
		// then this thread is obviuosly bumped and we know we need to regen only first page
		if ($thread_replyto != '0') {
			// check if the thread is the same as previuos posts
			if ($last_thread_id !== $thread_replyto) {
				cache_del ('board_opposts_' . $board_name . '_0');
				$threads = $tc_db->GetAll("SELECT id, bumped FROM `" . KU_DBPREFIX . "posts` WHERE `boardid` = " . $board_class->board['id'] . " AND `parentid` = 0 AND `IS_DELETED` = 0 ORDER BY `stickied` DESC, `bumped` DESC ");
				$threadnum = 0;
				foreach ($threads as $thread) {
					$threadnum++;
					if ( ($threadnum % KU_THREADS) === 0) {
						cache_del ('board_opposts_' . $board_name . '_' . ($threadnum/KU_THREADS));
					}
					if ($thread['id'] == $thread_replyto) {
						$pages_to_regen = (floor($threadnum/KU_THREADS));
						$old_bump_time = $thread['bumped'];
						break;
					}
				}
			} else {
				$old_bump_time = $tc_db->GetAll('select bumped from posts where `boardid` = ' . $board_class->board['id'] . ' AND `parentid` = '. $tc_db->qstr($thread_replyto));
			}
		}
		bdl_debug ('done threadnum count');
		if ($thread_replyto != '0') {
			cache_del ('thread_lastposts_' . $board_name . '_' .$thread_replyto);
			cache_del ('replycount_' . $board_name . '_' .$thread_replyto);
		} else {
			for ($k = 0; $k < 500; $k++) {
				cache_del('board_opposts_' . $board_name . '_' . $k);
			}
		}
		bdl_debug ('will regenerate pages up to ' . $pages_to_regen);

		// If the user replied to a thread, and they weren't sage-ing it...
		bdl_debug ('sage and auto-threads');

//		// GET HOOK, COMMENT OUT
//		$posts_count = $tc_db->GetOne ("select max(id) from posts where boardid=" . $board_class->board['id']);
//		$get = 1000000;
//		if ($posts_count == ($get - 1)) { // a post before get
//			ponyaba_post ($board_name, $thread_replyto,  '', '', '/var/www/ponyach/images/trollestia.png');
//		}
//		// END HOOK


		$done_firstlast = false;
		$bump = false;
		$autosage_nobump = false;
		if ($thread_replyto != '0' && strtolower($_POST['em']) != 'sage' && unistr_to_ords($_POST['em']) != array(19979, 12370)) {
			$bump = true;
			cache_inval ( Array ("mode=thread", "thread_id=".$thread_replyto));
			// And if the number of replies already in the thread are less than the maximum thread replies before perma-sage...
			if ($thread_replies < ($board_class->board['maxreplies'] - 2)) {
				// Bump the thread
				$bump_time = time();
			}else{
				$bump = false;
				if ($old_bump_time > 1000000000) { // if thread is not on autosage yet
					bdl_debug ("putting thread on autostage");
					// a hack to wipe all opposts caches
					for ($k = 0; $k < 500; $k++) {
						cache_del('board_opposts_' . $board_name . '_' . $k);
					}
					// Force saging threads to be lower than all unsaged
					$bump_time = time() - 1000000000;
					$results = $tc_db->GetAll("SELECT counter, subject, raw_message, op_dir, end_dir, premod_dir FROM `" . KU_DBPREFIX . "official` WHERE `boardid` = " . $board_class->board['id'] . " and `current_thread` = " . $thread_replyto . " ORDER BY id DESC LIMIT 1");
					if (count($results) > 0) {
						bdl_debug ("it's an official thread, locking tables");
						$pages_to_regen = false; // regenerate all pages when thread goes to force sage
						// creating new official thread and last message in current thread.
						// okay big shit doing here, lets lock posts
						$tc_db->Execute("lock tables `posts` write, `official` read, `boards` read, `ratings` as rb read, `ratings` as r read `sections` read");
						// now check again, because someone may have done something while we were locking
						$double_chk = $tc_db->GetAll("SELECT counter, subject, raw_message, op_dir, end_dir, premod_dir FROM `" . KU_DBPREFIX . "official` WHERE `boardid` = " . $board_class->board['id'] . " and `current_thread` = " . $thread_replyto . " ORDER BY id DESC LIMIT 1");
						if (count($double_chk) > 0) {

							bdl_debug ("double checked - yep it's official");
							$bump = true; // need to regenerate all the stuff
							$next_thread_num = $results[0]['counter'] + 1;
							$next_thread_subject = str_replace ('%', $next_thread_num, $results[0]['subject']);
					
							bdl_debug ("next thread number = " . $next_thread_num);
							// checking if next thread is already created
							$next_thread_row = $tc_db->GetRow("select p.id as id, f.name as file, ft.filetype as file_type from posts p left join posts_files pf on p.id = pf.postid and p.boardid = pf.boardid left join files f on pf.fileid = f.id left join filetypes ft on f.type = ft.name where p.boardid='". $board_class->board['id'] ."' and p.parentid='0' AND p.IS_DELETED = 0 and p.subject like '%%". $next_thread_subject ."%%' limit 1;");
							$next_thread = $next_thread_row['id'];
							if (!$next_thread) {
								bdl_debug ("next thread not created yet - creating");
								// make new thread
								$img = random_file ($results[0]['op_dir']);
								touch ($img);
								$op_post = str_replace ('~', link_on_thread ($board_name, $thread_replyto), $results[0]['raw_message']);
								$next_thread = ponyaba_post($board_name, 0, $next_thread_subject, $op_post, $img, "op-" . $next_thread_num);
							} else {
								bdl_debug ("next thread already created copying op pic");
								// save op pic for future use
								copy (KU_ROOTDIR . $board_name . '/src/' . $next_thread_row['file']. '.' .$next_thread_row['file_type'], $results[0]['premod_dir']. '/' .$next_thread_row['file']. '.' .$next_thread_row['file_type']);
							}

							// post closing message in current thread
							$end_message = str_replace ('~', link_on_thread ($board_name, $next_thread) ,random_end_post ());
							$img = random_file ($results[0]['end_dir']);
							ponyaba_post ($board_name, $thread_replyto,  random_end_post_subject (), $end_message, $img, "bye-bye-" . $thread_replyto);
							$tc_db->Execute("UPDATE `" . KU_DBPREFIX . "official` SET `counter` = '" . $next_thread_num . "', `current_thread` = '" . $next_thread . "' WHERE `boardid` = " . $board_class->board['id'] . " AND `current_thread` = '" . $thread_replyto . "'");
							
							// locking current thread. doing it here because the _proper_ way requires mod status
							$tc_db->Execute("UPDATE `" . KU_DBPREFIX . "posts` set `locked` = '1'" . " WHERE `boardid` = " . $board_class->board['id'] . " AND `id` = '" . $thread_replyto . "'");
							$board_class->RegenerateThreads($next_thread); // need to generate next threads body
						}
						$tc_db->Execute("ulock tables");
					}
				} else {
					// thread is already on autosage
					$autosage_nobump = true;
				}
			}
			if ($autosage_nobump == false)
				$tc_db->Execute("UPDATE `" . KU_DBPREFIX . "posts` SET `bumped` = '" . $bump_time . "' WHERE `boardid` = " . $board_class->board['id'] . " AND `id` = '" . $thread_replyto . "'");
		}
		bdl_debug ('done sage and auto-threads');

		// If the user replied to a thread he is watching, update it so it doesn't count his reply as unread

		$tc_db->Execute("COMMIT");

		ignore_user_abort(true);

		// Trim any threads which have been pushed past the limit, or exceed the maximum age limit
		// We do not delete threads any more
		//TrimToPageLimit($board_class->board);

		bdl_debug ('bump = ' . (int)$bump . ' pages_to_regen = ' . $pages_to_regen);
		// Regenerate board pages
		if ($bump) {
			for ($k = 0; $k <= $pages_to_regen; $k++) {
				cache_del('board_opposts_' . $board_name . '_' . $k);
			}
			$board_class->RegeneratePages($pages_to_regen);
		}

		if ($thread_replyto == '0') {
			// Regenerate the thread
			$board_class->RegenerateThreads($post_id);
			cache_del('board_opposts_' . $board_name . '_0' );
			$board_class->RegeneratePages($pages_to_regen);
		} else {
			// Regenerate the thread
			$lock_key = 'pony-lock:' . $board_class->board['name'] .'_res_'.$thread_replyto.'.html';
			cache_add($lock_key, true, 5);
			$board_class->RegenerateThreads($thread_replyto);
			cache_del($lock_key);
		}
	} else {
		exitWithErrorPage(_gettext('Sorry, this board is locked and can not be posted in.'));
	}
} elseif ((isset($_POST['deletepost']) || isset($_POST['reportpost']) || isset($_POST['moddelete'])) && isset($_POST['post'])) {
	$ismod = false;
	// Initialize the post class
	foreach ($_POST['post'] as $val) {
		$post_class = new Post($val, $board_class->board['name'], $board_class->board['id']);

		if (isset($_POST['reportpost'])) {
			// They clicked the Report button
			if ($board_class->board['enablereporting'] == 1) {
				$post_reported = $post_class->post['isreported'];

				if ($post_reported === 'cleared') {
					echo _gettext('Этот пост уже проверен.') . '<br />';
				} elseif ($post_reported) {
					echo _gettext('Этот пост уже на рассмотрении') . '<br />';
				} else {
					if ($post_class->Report()) {
						echo _gettext('Жалоба на пост(ы) отправлена.') . '<br />';
					} else {
						echo _gettext('Не получилось отправить жалобу') . '<br />';
					}
				}
			} else {
				echo _gettext('This board does not allow post reporting.') . '<br />';
			}
		} elseif (isset($_POST['postpassword']) || ( isset($_POST['moddelete']) && (require_once KU_ROOTDIR . 'inc/classes/manage.class.php') && Manage::CurrentUserIsModeratorOfBoard($board_class->board['name'], $_SESSION['manageusername']) && $ismod = true)) {
			// They clicked the Delete button
			if ($_POST['postpassword'] != '' || $ismod) {
				if (md5($_POST['postpassword']) == $post_class->post['password'] || $ismod) {
					if (isset($_POST['fileonly'])) {
						if ($post_class->post['file'] != '' && $post_class->post['file'] != 'removed') {
							$post_class->DeleteFile();
							$board_class->RegeneratePages();
							if ($post_class->post['parentid'] != 0) {
								$board_class->RegenerateThreads($post_class->post['parentid']);
							}
							echo _gettext('Image successfully deleted from your post.') . '<br />';
						} else {
							echo _gettext('Your post already doesn\'t have an image!') . '<br />';
						}
					} else {
						if ($post_class->Delete()) {
							if ($post_class->post_parentid != '0') {
								$board_class->RegenerateThreads($post_class->post['parentid']);
							}
							$board_class->RegeneratePages();
							echo _gettext('Post successfully deleted.') . '<br />';
						} else {
							echo _gettext('There was an error in trying to delete your post') . '<br />';
						}
					}
				} else {
					echo _gettext('Incorrect password.') . '<br />';
				}
			} else {
				bdl_debug ('finish0');
				do_redirect(KU_BOARDSPATH . '/' . $board_class->board['name'] . '/');
			}
		}
	}
	bdl_debug ('finish1');
	do_redirect(KU_BOARDSPATH . '/' . $board_class->board['name'] . '/');
	die();
} else {
	bdl_debug ('finish2');
	do_redirect(KU_BOARDSPATH . '/' . $board_class->board['name'] . '/');
}

// set mc key to workaround the fucking "not see myselfs posts" bug
if ($thread_replyto)
	cache_add("last_poster_ip_" . $thread_replyto, $real_ip);
if ($_GET['json'] == 1) {
	echo 
"{
    \"error\": null,
    \"id\": $post_id
}";
} else {
	if ($board_class->board['redirecttothread'] == 1 || $_POST['em'] == 'return' || $_POST['em'] == 'noko') {
		if ($thread_replyto == "0") {
			bdl_debug ('finish3');
			do_redirect(KU_BOARDSPATH . '/' . $board_class->board['name'] . '/res/' . $post_id . '.html', true);
		} else {
			bdl_debug ('finish4');
			//do_redirect(KU_BOARDSPATH . '/' . $board_class->board['name'] . '/res/' . $thread_replyto . '.html', true);
	
			do_redirect(KU_BOARDSPATH . '/' . $board_class->board['name'] . '/res/' . ($done_firstlast ? '005' : '') . $thread_replyto . '.html', true);
		}
	} else {
		bdl_debug ('finish5');
		do_redirect(KU_BOARDSPATH . '/' . $board_class->board['name'] . '/', true);
	}
}
?>
