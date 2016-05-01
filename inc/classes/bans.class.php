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
 */
/**
 * Bans Class
 *
 * Assorted banning-related functions placed into class format
 *
 * @package kusaba
 */

class Bans {

	/* Perform a check for a ban record for a specified IP address */
	function BanCheckx($ip, $board = '', $force_display = false, $message = '', $sage = false) {

	// check subnet ban
	
	// check ip ban

	// check session ban

	}

	function BanCheck($ip, $board = '', $force_display = false, $message = '', $sage = false, $threadid = false) {
		global $tc_db, $real_ip;
		
		$md5ip = md5($real_ip);
		$bans = Array(); $banned_ips = Array(); $banned_sessions = Array();
		$sid = md5 (session_id ());
		bdl_debug ('ban check ip/sess: ' . $md5ip. ' - '.md5(session_id()));

		// but first we check if she is "unbaned" in current thread
		$off = '';
		if ($offid = get_official_thread_by_id(board_name_to_id($board), $threadid))
			$off = ' or official_id = ' . $tc_db->qstr($offid) . ' ';

		if ($threadid) {
			if ($tc_db->GetOne("select unban from thread_bans where ((boardid = " . $tc_db->qstr(board_name_to_id($board)) . " and threadid = " . $tc_db->qstr($threadid) . " ) " .$off. " )and (session_md5 = " .$tc_db->qstr($ses_md5). " or ipmd5 = " .$tc_db->qstr($md5ip). " or everyone = 1)") == 1)
				return true;
		}

		//if ($_SESSION['banip']) {
			//$results = $tc_db->GetAll("SELECT * FROM `".KU_DBPREFIX."banlist` WHERE ((`type` = '0' AND ( `ipmd5` = '" . $md5ip . "' OR `ipmd5` = ". $tc_db->qstr($_SESSION['banip']) . " )) OR `type` = '1') AND (`expired` = 0)" );
		//} else {
			$results = $tc_db->GetAll("SELECT * FROM `".KU_DBPREFIX."banlist` WHERE ((`type` = '0' AND ( `ipmd5` = " . $tc_db->qstr($md5ip) . " or `session_md5` = md5(".$tc_db->qstr(session_id())." ))) OR `type` = '1') AND (`expired` = 0)" );

		//}
		if (count($results)>0) {
			foreach($results AS $line) {
				if($line['type'] == 2 && strpos($ip, md5_decrypt($line['ip'], KU_RANDOMSEED)) === 0) {
					// network whitelist
					return;
				}
				if(($line['type'] == 1 && strpos($ip, md5_decrypt($line['ip'], KU_RANDOMSEED)) === 0) || $line['type'] == 0) {
					if ($line['until'] != 0 && $line['until'] < time()){
						$tc_db->Execute("UPDATE `".KU_DBPREFIX."banlist` SET `expired` = 1 WHERE `id` = ".$line['id']);
						$line['expired'] = 1;
					}
					if ($line['globalban']!=1) {
						if ((in_array($board, explode('|', $line['boards'])) || $board == '')) {
							$line['appealin'] = substr(timeDiff($line['appealat'], true, 2), 0, -1);
							$bans[] = $line;
							$banned_ips[$line['ipmd5']] = true;
							if ($line['session_md5']) $banned_sessions[$line['session_md5']] = true;
						}
					} else {
							$line['appealin'] = substr(timeDiff($line['appealat'], true, 2), 0, -1);
							$bans[] = $line;
							$banned_ips[$line['ipmd5']] = true;
							if ($line['session_md5']) $banned_sessions[$line['session_md5']] = true;
					}
				}
			}
		}

		$results = $tc_db->GetAll("select timestamp, count(*) as c from online group by session_md5 having session_md5 = '" .$sid. "' and c >= 5 and timestamp > (unix_timestamp() - 180)");
		if (count ($results) > 0) {
			// someone is gona wipe the board
			$_SESSION['banip'] = $md5ip;
			session_write_close();
			if ($message != '') {
				$this->BanUser($real_ip, 'board.php', 1, 0, '', 'Ты забанен. Слишком много IP. Надумал что-то? #Автобан' , 'Автобан. Слишком много сессий с одного IP за 3 минуты', 0, 0, 1, false, $message);
			}else{
				$this->BanUser($real_ip, 'board.php', 1, 0, '', 'Ты забанен. Слишком много IP. Надумал что-то? #Автобан' , 'Автобан. Слишком много сессий с одного IP за 3 минуты');
			}
			management_addlogentry("Забанен $real_ip - " . $results[0]['c'] . " соединений с одной сессией", 8, 'ponyaba');
			echo $this->DisplayBannedMessage($bans);
		}
		
		if(count($bans) > 0){
			$tc_db->Execute("END TRANSACTION");
			$ses_md5 = md5(session_id());	
			if ($banned_ips[$md5ip] != true) {
				// if ($message != ''){
				//add ip to session ban
				$results_boards = $tc_db->GetAll("select boards from banlist where session_md5 = " .$tc_db->qstr($ses_md5). " or ipmd5 = " .$tc_db->qstr($md5ip));
				$tc_db->Execute("update banlist set ipmd5 = " .$tc_db->qstr($md5ip). " where session_md5 = " .$tc_db->qstr($ses_md5). " and boards = " .$tc_db->qstr($results_boards[0][0]));
				management_addlogentry("Найдена забаненная сессия, но этот айпи $real_ip был не в бане, добавляю" , 8, "ponyaba");
				bdl_debug('add ip to session ban');
			}
			if ($banned_sessions[md5(session_id())] != true){
				//add session to ip ban
				$results_boards = $tc_db->GetAll("select boards from banlist where session_md5 = " .$tc_db->qstr($ses_md5). " or ipmd5 = " .$tc_db->qstr($md5ip));
				$tc_db->Execute("update banlist set session_md5 = " .$tc_db->qstr($ses_md5). " where ipmd5 = " .$tc_db->qstr($md5ip). " and boards = " .$tc_db->qstr($results_boards[0][0]));
				management_addlogentry("Найден забаненый айпи $real_ip, но эта сессия была не в бане, добавляю" , 8, "ponyaba");
				bdl_debug('add session to ip ban');
			}
			session_write_close();


			// THIS IS THE PLACE WHERE USER GETS DENIED TO POST
			echo $this->DisplayBannedMessage($bans);
		} 

		bdl_debug ('bans 1');
		if(check_cgi_prox($ip)){
			management_addlogentry("denied post from $real_ip - cgi прокси", 8, 'ponyaba');
			$tc_db->Execute("END TRANSACTION");
			session_write_close();
			echo $this->DisplayBannedMessage($bans);
		}

		bdl_debug ('bans 2');

		if((KU_CHECKTOR == true) && check_tor ($ip)){
			management_addlogentry("denied post from $real_ip - ТОР", 8, 'ponyaba');
			$tc_db->Execute("END TRANSACTION");
			session_write_close();
			xdie('Извини, из-под тора постить нельзя.');
		}
		bdl_debug ('bans 3');

//		if ($_SERVER['REMOTE_ADDR'] == $real_ip) {
//			if (check_anon_prox ($ip)){
//				management_addlogentry("denied post from $real_ip - анонимная прокси", 8, 'ponyaba');
//				$tc_db->Execute("END TRANSACTION");
//				session_write_close();
//				die('Извини, с публичных анонимных прокси постить нельзя.');
//			}
//		}
		bdl_debug ('bans 4');

		if ($sage === true && !have_posts_from_ipmd5($md5ip)) {
			//if ($message != '') {
			//	$results = $tc_db->GetOne("select count(*) from banlist where postmd5=".$tc_db->qstr(md5($message)). " and timestamp < '".(time() - 86400). "';");
			//	if ($results > 0) {
			//		$tc_db->Execute("END TRANSACTION");
			//		management_addlogentry("denied post from $real_ip - нету постов с этого айпи. Пост с этим сообщением был забанен прежде", 8, 'ponyaba');
			//		$this->BanUser($real_ip, 'board.php', 1, 0, '', 'Ты уже забанен #Автобан' , 'Автобан. Найдено забаненное сообщение', 0, 0, 1, false, $message);
			//		session_write_close();
			//		echo $this->DisplayBannedMessage($bans);
			//	}
			//}
				$tc_db->Execute("END TRANSACTION");
				management_addlogentry("denied post from $real_ip - no posts from this ip, saging", 8, 'ponyaba');
				$this->BanUser($real_ip, 'board.php', 1, 0, '', 'Ты уже забанен #Автобан' , 'Автобан. Первое сообщение с этого айпи с сажей', 0, 0, 1, false, $message);
				session_write_close();
				echo $this->DisplayBannedMessage($bans);
		}
		bdl_debug ('bans 5');

		session_write_close();


		return true;
	}

	/* Add a ip/ip range ban */
	function BanUser($ip, $modname, $globalban, $duration, $boards, $reason, $staffnote, $appealat=0, $type=0, $allowread=1, $proxyban=false, $message='', $session_id = false) {
		global $tc_db;

		// let me explain this shit:
		// if false - the current session is used - ie the user which opens the page now. used in autoban
		// if true - ignore session, set it to empty - used in case of bans, since we cant use moders session, lol.
		// TODO: should pass banned message session when moder is banning. however mod can ban ip manually not from a message and will not know of session
		// last one - session is passed explicitely

		bdl_debug ("banning, ip = ". $ip. " session_id = " . $session_id);
		if ($session_id == false) {
			$session_md5 = md5(session_id());
		} else if ($session_id === true) {
			$session_md5 = "";
		} else {
			$session_md5 = $session_id;
		}

		$chk = $tc_db->GetOne("select count(*) from banlist where globalban = " .$tc_db->qstr($globalban). " and boards = " .$tc_db->qstr($boards) ." and type = " .$tc_db->qstr($type). " and allowread = " .$tc_db->qstr($allowread). " and ipmd5 = md5(" .$tc_db->qstr($ip). ")");

		if ($chk) {
			bdl_debug ("ban already exists, skipping ban");
			return true;
		}
		
		$sid = md5 (session_id ());
		if ($proxyban) {
			$session_md5 = ''; // no session for proxybans
			$check = $tc_db->GetOne("SELECT COUNT(*) FROM `".KU_DBPREFIX."banlist` WHERE `type` = '".$type."' AND `ipmd5` = '".md5($ip)."'");
			if ($check[0] > 0) {
				return false;
			}
		}
		
		if ($duration>0) {
			$ban_globalban = '0';
		} else {
			$ban_globalban = '1';
		}
		if ($duration>0) {
			$ban_until = time()+$duration;
		} else {
			$ban_until = '0';
		}

		if ($message != '') {
			$tc_db->Execute("INSERT INTO `".KU_DBPREFIX."banlist` ( `ip` , `ipmd5` , `type` , `allowread` , `globalban` , `boards` , `by` , `at` , `until` , `reason`, `staffnote`, `appealat` , `postmd5`, `session_md5` ) VALUES ( ".$tc_db->qstr(md5_encrypt($ip, KU_RANDOMSEED))." , ".$tc_db->qstr(md5($ip))." , ".intval($type)." , ".intval($allowread)." , ".intval($globalban)." , ".$tc_db->qstr($boards)." , ".$tc_db->qstr($modname)." , ".time()." , ".intval($ban_until)." , ".$tc_db->qstr($reason)." , ".$tc_db->qstr($staffnote).", ".intval($appealat).", ".$tc_db->qstr(md5($message)).", ".$tc_db->qstr($session_md5) . ") ");
		}else{
			$tc_db->Execute("INSERT INTO `".KU_DBPREFIX."banlist` ( `ip` , `ipmd5` , `type` , `allowread` , `globalban` , `boards` , `by` , `at` , `until` , `reason`, `staffnote`, `appealat`, `session_md5` ) VALUES ( ".$tc_db->qstr(md5_encrypt($ip, KU_RANDOMSEED))." , ".$tc_db->qstr(md5($ip))." , ".intval($type)." , ".intval($allowread)." , ".intval($globalban)." , ".$tc_db->qstr($boards)." , ".$tc_db->qstr($modname)." , ".time()." , ".intval($ban_until)." , ".$tc_db->qstr($reason)." , ".$tc_db->qstr($staffnote).", ".intval($appealat)." , ". $tc_db->qstr($session_md5) . ") ");
		}

		//session_set_cookie_params(60 * 60 * 24 * 100);
		//session_start ();
		//$sid = md5 (session_id ());
		//$_SESSION['banip'] = $md5ip;
		//session_write_close();

		return true;
	}

	/* Return the page which will inform the user a quite unfortunate message */
	function DisplayBannedMessage($bans, $board='') {
		global $real_ip;

		require_once KU_ROOTDIR . 'lib/Haanga.php';
		$dwoo_data['bans'] = $bans;
		load_haanga();
		if ($_GET['json'] == 1) {
			$content = Haanga::Load('/banned.tpl', $dwoo_data, true);
			//bdl_debug($content . "XXXX");
			//bdl_debug(json_encode(Array("error" => $content, "id" => NULL)));
			echo (json_encode(Array("error" => $content, "id" => NULL)));
			die();
		}

		Haanga::Load('/banned.tpl', $dwoo_data, false);
		die();
	}

	function UpdateHtaccess() {
	}
	
	function CheckThreadBan($boardid, $threadid){
		global $real_ip, $tc_db;
		
		$off = '';
		if ($offid = get_official_thread_by_id($boardid, $threadid))
			$off = ' or official_id = ' . $tc_db->qstr($offid) . ' ';

		$query = 'select * from thread_bans where ((boardid = ' .$tc_db->qstr($boardid). ' and threadid = ' .$tc_db->qstr($threadid). ') '.$off.') and (ipmd5 = ' .$tc_db->qstr(md5($real_ip)). ' or session_md5 = ' .$tc_db->qstr(md5(session_id())). ' or everyone = 1) and unban = 0';
		$result = $tc_db->GetAll($query);
		
		if (empty($result)){
			return false;
		}
		$this->DisplayThreadBan();
		return true;
	}
	
	function DisplayThreadBan(){
		if ($_GET['json'] == 1) {
			echo (json_encode(Array("error" => 'Ты не можешь постить в этом треде, попробуй в следующем.', "id" => NULL)));
			die();
		}
		die('Ты не можешь постить в этом треде, попробуй в следующем.');
	}
}

?>
