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
 * Manage Class
 * +------------------------------------------------------------------------------+
 * Manage functions, along with the pages available
 * +------------------------------------------------------------------------------+
 */
class Manage {

	/* Show the header of the manage page */
	function Header() {
		global $dwoo_data, $tpl_page;

		if (is_file(KU_ROOTDIR . 'inc/pages/modheader.html')) {
			$tpl_includeheader = file_get_contents(KU_ROOTDIR . 'inc/pages/modheader.html');
		} else {
			$tpl_includeheader = '';
		}

		$dwoo_data['includeheader'] = $tpl_includeheader;
	}

	/* Show the footer of the manage page */
	function Footer() {
		global $dwoo_data, $dwoo, $tpl_page;

		$dwoo_data['page'] = $tpl_page;

		$board_class = new Board('');

		load_haanga();
		Haanga::Load('manage.tpl', $dwoo_data);
	}

	// Creates a salt to be used for passwords
	function CreateSalt() {
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$salt = '';

		for ($i = 0; $i < 3; ++$i) {
			$salt .= $chars[mt_rand(0, strlen($chars) - 1)];
		}
		return $salt;
	}

	/* Validate the current session */
	function ValidateSession($is_menu = false) {
		global $tc_db, $tpl_page;

		if (isset($_SESSION['manageusername']) && isset($_SESSION['managepassword'])) {
			$results = $tc_db->GetAll("SELECT HIGH_PRIORITY `username` FROM `" . KU_DBPREFIX . "staff` WHERE `username` = " . $tc_db->qstr($_SESSION['manageusername']) . " AND `password` = " . $tc_db->qstr($_SESSION['managepassword']) . " LIMIT 1");
			if (count($results) == 0) {
				session_destroy();
				exitWithErrorPage(_gettext('Невалидная сессия.'), '<a href="manage_page.php">'. _gettext('Зайди снова') . '</a>');
			}

			$tc_db->Execute("UPDATE `" . KU_DBPREFIX . "staff` SET `lastactive` = " . time() . " WHERE `username` = " . $tc_db->qstr($_SESSION['manageusername']));

			return true;
		} else {
			if (!$is_menu) {
				$this->LoginForm();
				die($tpl_page);
			} else {
				return false;
			}
		}
	}

	/* Show the login form and halt execution */
	function LoginForm() {
		global $tc_db, $tpl_page;

		if (file_exists(KU_ROOTDIR . 'inc/pages/manage_login.html')) {
			$tpl_page .= file_get_contents(KU_ROOTDIR . 'inc/pages/manage_login.html');
		}
	}

	/* Check login names and create session if user/pass is correct */
	function CheckLogin() {
		global $tc_db, $action;

		$tc_db->Execute("DELETE FROM `" . KU_DBPREFIX . "loginattempts` WHERE `timestamp` < '" . (time() - 1200) . "'");
		$results = $tc_db->GetAll("SELECT HIGH_PRIORITY `ip` FROM `" . KU_DBPREFIX . "loginattempts` WHERE `ip` = '" . $_SERVER['REMOTE_ADDR'] . "' LIMIT 6");
		if (count($results) > 5) {
			exitWithErrorPage('Ты слишком часто пытался войти, я вызвала полицию к тебе домой');
		} else {
			$results = $tc_db->GetAll("SELECT HIGH_PRIORITY `id`, `username`, `password`, `salt` FROM `" . KU_DBPREFIX . "staff` WHERE `username` = " . $tc_db->qstr($_POST['username']) . " AND `type` != 3 LIMIT 1");
			if (count($results) > 0) {
				if (empty($results[0]['salt'])) {
					if (md5($_POST['password']) === $results[0]['password']) {
						$salt = $this->CreateSalt();
						$tc_db->Execute("UPDATE `" .KU_DBPREFIX. "staff` SET salt = '" .$salt. "' WHERE username = " .$tc_db->qstr($_POST['username']));
						$newpass = md5($_POST['password'] . $salt);
						$tc_db->Execute("UPDATE `" .KU_DBPREFIX. "staff` SET password = '" .$newpass. "' WHERE username = " .$tc_db->qstr($_POST['username']));
						$_SESSION['manageusername'] = $_POST['username'];
						$_SESSION['manageid'] = $results[0]['id'];
						$_SESSION['managepassword'] = $newpass;
            $_SESSION['token'] = md5($_SESSION['manageusername'] . $_SESSION['managepassword'] . rand(0,100));
						$this->SetModerationCookies();
						$tc_db->Execute("DELETE FROM `" . KU_DBPREFIX . "loginattempts` WHERE `ip` = '" . $_SERVER['REMOTE_ADDR'] . "'");
						$action = 'posting_rates';
						management_addlogentry(_gettext('Залогинился'), 1);
						die('<script type="text/javascript">top.location.href = \''. KU_CGIPATH .'/manage.php\';</script>');
					} else {
						$tc_db->Execute("INSERT HIGH_PRIORITY INTO `" . KU_DBPREFIX . "loginattempts` ( `username` , `ip` , `timestamp` ) VALUES ( " . $tc_db->qstr($_POST['username']) . " , '" . $_SERVER['REMOTE_ADDR'] . "' , '" . time() . "' )");
						exitWithErrorPage(_gettext('Некорректные данные'));
					}
				} else {
					if (md5($_POST['password'] . $results[0]['salt']) == $results[0]['password']) {
						$_SESSION['manageusername'] = $_POST['username'];
						$_SESSION['manageid'] = $results[0]['id'];
						$_SESSION['managepassword'] = md5($_POST['password'] . $results[0]['salt']);
            $_SESSION['token'] = md5($_SESSION['manageusername'] . $_SESSION['managepassword'] . rand(0,100));
						$this->SetModerationCookies();
						$action = 'posting_rates';
						management_addlogentry(_gettext('Залогинился'), 1);
						die('<script type="text/javascript">top.location.href = \''. KU_CGIPATH .'/manage.php\';</script>');
					} else {
						$tc_db->Execute("INSERT HIGH_PRIORITY INTO `" . KU_DBPREFIX . "loginattempts` ( `username` , `ip` , `timestamp` ) VALUES ( " . $tc_db->qstr($_POST['username']) . " , '" . $_SERVER['REMOTE_ADDR'] . "' , '" . time() . "' )");
						exitWithErrorPage(_gettext('Некорректные данные'));
					}
				}
			} else {
				$tc_db->Execute("INSERT HIGH_PRIORITY INTO `" . KU_DBPREFIX . "loginattempts` ( `username` , `ip` , `timestamp` ) VALUES ( " . $tc_db->qstr($_POST['username']) . " , '" . $_SERVER['REMOTE_ADDR'] . "' , '" . time() . "' )");
				exitWithErrorPage(_gettext('Некорректные данные. Возможно, конечно, ты просто ошибся паролем, но для пущей безопасности мы теперь за тобой следим.'));
			}
		}
	}

	/* Set mod cookies for boards */
	function SetModerationCookies() {
		global $tc_db, $tpl_page;

		if (isset($_SESSION['manageusername'])) {
			$results = $tc_db->GetAll("SELECT HIGH_PRIORITY `boards` FROM `" . KU_DBPREFIX . "staff` WHERE `username` = " . $tc_db->qstr($_SESSION['manageusername']) . " LIMIT 1");
			if ($this->CurrentUserIsAdministrator() || $results[0][0] == 'allboards') {
				setcookie("kumod", "allboards", time() + 360000, KU_BOARDSFOLDER, KU_DOMAIN);
			} else {
				if ($results[0][0] != '') {
					setcookie("kumod", $results[0][0], time() + 360000, KU_BOARDSFOLDER, KU_DOMAIN);
				}
			}
		}
	}
  
  function CheckToken($posttoken) {
    if ($posttoken != $_SESSION['token']) {
      // Something is strange
      session_destroy();
      exitWithErrorPage(_gettext('Invalid Token'));
    }
  }

	function GetAjaxToken() {
		global $tc_db, $real_ip;
		
		$this->ModeratorsOnly();
		do {
			if (!$_GET['id']) die ('no post id');
			if (!is_numeric($_GET['id'])) die ('[my shed]  you ---out--->');

			if (!$_GET['board']) die ('no board');
			$boardid = board_name_to_id($_GET['board']);
			if (!$boardid) die ('no such board lol');

			$query = "delete from tokens where timestamp < now() - interval 10 minute";
			$tc_db->Execute($query);

			$token = md5(md5(date(DATE_RSS) . 'nabor randomnoi huiniiiiiiii'). 'i eshe huimyaaaaaa'. $real_ip);
			$query = "insert into tokens (postid, boardid, staffid, real_ip, session_md5, token) values (".
				$tc_db->qstr($_GET['id']).",".
				$tc_db->qstr($boardid).",".
				$tc_db->qstr($_SESSION['manageid']).",".
				$tc_db->qstr($real_ip).",".
				"md5(".$tc_db->qstr(session_id())."),".
				$tc_db->qstr($token).
			")";
			bdl_debug('board id = '. $boardid);
			//bdl_debug($query);
			$tc_db->Execute($query);
			die($token);
		} while (false);
	}

	function CheckAjaxToken($postid, $boardid) {
		global $tc_db, $real_ip;
		
		$this->ModeratorsOnly();
		$return = false;

		$token = $_SERVER['HTTP_X_TOKEN'];
		if (!$token) die ('no token lol');

		do {
			if (!$postid || !$boardid) die ('post and boardid missing');
			$query_base = " from tokens where postid = ".$tc_db->qstr($postid)." and boardid = ".$tc_db->qstr($boardid)." and real_ip = ".
			$tc_db->qstr($real_ip). " and session_md5 = md5(". $tc_db->qstr(session_id()). ") and token = ". $tc_db->qstr($token);

			$res = $tc_db->GetOne('select count(*) '. $query_base);
			if ($res > 0) {
				$return = true;
			}
			$tc_db->Execute('delete from '. $query_base);
		} while (false);
		return $return;
	}

	/* Log current user out */
	function Logout() {
		global $tc_db, $tpl_page;

		setcookie('kumod', '', time() - 360000, KU_BOARDSFOLDER, KU_DOMAIN);

		session_destroy();
		unset($_SESSION['manageusername']);
		unset($_SESSION['managepassword']);
    		unset($_SESSION['token']);
		die('<script type="text/javascript">top.location.href = \''. KU_CGIPATH .'/manage.php\';</script>');
	}

		/* If the user logged in isn't an admin, kill the script */
	function AdministratorsOnly() {
		global $tc_db, $tpl_page;

		if (!$this->CurrentUserIsAdministrator()) {
			exitWithErrorPage('That page is for admins only.');
		}
	}

	/* If the user logged in isn't an moderator or higher, kill the script */
	function ModeratorsOnly() {
		global $tc_db, $tpl_page;

		if ($this->CurrentUserIsAdministrator()) {
			return true;
		} else {
			$results = $tc_db->GetAll("SELECT HIGH_PRIORITY `type` FROM `" . KU_DBPREFIX . "staff` WHERE `username` = '" . $_SESSION['manageusername'] . "' AND `password` = '" . $_SESSION['managepassword'] . "' LIMIT 1");
			foreach ($results as $line) {
				if ($line['type'] != 2) {
					exitWithErrorPage(_gettext('That page is for moderators and administrators only.'));
				}
			}
		}
	}

	/* See if the user logged in is an admin */
	function CurrentUserIsAdministrator() {
		global $tc_db, $tpl_page;

		if ($_SESSION['manageusername'] == '' || $_SESSION['managepassword'] == '' || $_SESSION['token'] == '') {
			$_SESSION['manageusername'] = '';
			$_SESSION['managepassword'] = '';
      $_SESSION['token'] = '';
			return false;
		}

		$results = $tc_db->GetAll("SELECT HIGH_PRIORITY `type` FROM `" . KU_DBPREFIX . "staff` WHERE `username` = '" . $_SESSION['manageusername'] . "' AND `password` = '" . $_SESSION['managepassword'] . "' LIMIT 1");
		foreach ($results as $line) {
			if ($line['type'] == 1) {
				return true;
			} else {
				return false;
			}
		}

		/* If the function reaches this point, something is fishy. Kill their session */
		session_destroy();
		exitWithErrorPage(_gettext('Invalid session, please log in again.'));
	}

	/* See if the user logged in is a moderator */
	function CurrentUserIsModerator() {
		global $tc_db, $tpl_page;

		if ($_SESSION['manageusername'] == '' || $_SESSION['managepassword'] == '' || $_SESSION['token'] == '') {
			$_SESSION['manageusername'] = '';
			$_SESSION['managepassword'] = '';
      $_SESSION['token'] = '';
			return false;
		}

		$results = $tc_db->GetAll("SELECT HIGH_PRIORITY `type` FROM `" . KU_DBPREFIX . "staff` WHERE `username` = '" . $_SESSION['manageusername'] . "' AND `password` = '" . $_SESSION['managepassword'] . "' LIMIT 1");
		foreach ($results as $line) {
			if ($line['type'] == 2) {
				return true;
			} else {
				return false;
			}
		}

		/* If the function reaches this point, something is fishy. Kill their session */
		session_destroy();
		exitWithErrorPage(_gettext('Invalid session, please log in again.'));
	}

	/* See if the user logged in is a moderator of a specified board */
	function CurrentUserIsModeratorOfBoard($board, $username) {
		global $tc_db, $tpl_page;

		$results = $tc_db->GetAll("SELECT HIGH_PRIORITY `type`, `boards` FROM `" . KU_DBPREFIX . "staff` WHERE `username` = '" . $username . "' LIMIT 1");
		if (count($results) > 0) {
			foreach ($results as $line) {
				if ($line['boards'] == 'allboards') {
					return true;
				} else {
					if ($line['type'] == '1') {
						return true;
					} else {
						$array_boards = explode('|', $line['boards']);
						if (in_array($board, $array_boards)) {
							return true;
						} else {
							return false;
						}
					}
				}
			}
		} else {
			return false;
		}
	}

	/*
	* +------------------------------------------------------------------------------+
	* Manage pages
	* +------------------------------------------------------------------------------+
	*/


	/*
	* +------------------------------------------------------------------------------+
	* Home Pages
	* +------------------------------------------------------------------------------+
	*/

	function posting_rates() {
		global $tc_db, $tpl_page;

		$tpl_page .= '<h2>'. _gettext('Posting rates (past hour)') . '</h2><br />';
		$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "boards` ORDER BY `name` ASC");
		if (count($results) > 0) {
			$tpl_page .= '<table class="table table-striped table-bordered table-hover table-condensed"><tr><th>'. _gettext('Board') . '</th><th>'. _gettext('Threads') . '</th><th>'. _gettext('Replies') . '</th><th>'. _gettext('Posts') . '</th></tr>';
			foreach ($results as $line) {
				$rows_threads = $tc_db->GetOne("SELECT HIGH_PRIORITY count(id) FROM `" . KU_DBPREFIX . "posts` WHERE `boardid` = " . $line['id'] . " AND `parentid` = 0 AND `timestamp` >= " . (time() - 3600));
				$rows_replies = $tc_db->GetOne("SELECT HIGH_PRIORITY count(id) FROM `" . KU_DBPREFIX . "posts` WHERE `boardid` = " . $line['id'] . " AND `parentid` != 0 AND `timestamp` >= " . (time() - 3600));
				$rows_posts = $rows_threads + $rows_replies;
				$threads_perminute = $rows_threads;
				$replies_perminute = $rows_replies;
				$posts_perminute = $rows_posts;
				if ($threads_perminute > 0){
				$tpl_page .= '<tr><td><strong><a href="'. KU_WEBFOLDER . $line['name'] . '">'. $line['name'] . '</a></strong></td><td style="color:red;">'. $threads_perminute . '</td><td>'. $replies_perminute . '</td><td>'. $posts_perminute . '</td></tr>';
				} else {
				$tpl_page .= '<tr><td><strong><a href="'. KU_WEBFOLDER . $line['name'] . '">'. $line['name'] . '</a></strong></td><td>'. $threads_perminute . '</td><td>'. $replies_perminute . '</td><td>'. $posts_perminute . '</td></tr>';
				}

			}
			$tpl_page .= '</table>';
			$tpl_page .= '<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.js"></script> <script>function reload_data(){$.ajax({url:"https://ponyach.ru/manage_page.php?action=posting_rates",success:function(data){$("tbody").remove();tbody=$(data).find("tbody");$("table").append(tbody);},error:function(){$("tbody").remove();tbody=$(data).find("tbody");$("table").append(tbody);}});}setInterval(reload_data,4000);</script>';
		} else {
			$tpl_page .= _gettext('Нет досок');
		}
	}

	function changepwd() {
		global $tc_db, $tpl_page;

		$tpl_page .= '<h2>'. _gettext('Change account password') . '</h2><br />';
		if (isset($_POST['oldpwd']) && isset($_POST['newpwd']) && isset($_POST['newpwd2'])) {
			$this->CheckToken($_POST['token']);
			if ($_POST['oldpwd'] != '' && $_POST['newpwd'] != '' && $_POST['newpwd2'] != '') {
				if ($_POST['newpwd'] == $_POST['newpwd2']) {
					$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "staff` WHERE `username` = " . $tc_db->qstr($_SESSION['manageusername']) . "");
					foreach ($results as $line) {
						$staff_passwordenc = $line['password'];
						$staff_salt = $line['salt'];
					}
					if (md5($_POST['oldpwd'].$staff_salt) == $staff_passwordenc) {
						$tc_db->Execute("UPDATE `" . KU_DBPREFIX . "staff` SET `password` = '" . md5($_POST['newpwd'].$staff_salt) . "' WHERE `username` = " . $tc_db->qstr($_SESSION['manageusername']) . "");
						$_SESSION['managepassword'] = md5($_POST['newpwd'].$staff_salt);
						$tpl_page .= _gettext('Password successfully changed.');
					} else {
						$tpl_page .= _gettext('The old password you provided did not match the current one.');
					}
				} else {
					$tpl_page .= _gettext('The second password did not match the first.');
				}
			} else {
				$tpl_page .= _gettext('Please fill in all required fields.');
			}
			$tpl_page .= '<hr />';
		}
		$tpl_page .= '<form action="manage_page.php?action=changepwd" method="post">
    <input type="hidden" name="token" value="' . $_SESSION['token'] . '" />
		<label for="oldpwd">'. _gettext('Old password') . ':</label>
		<input type="password" name="oldpwd" /><br />

		<label for="newpwd">'. _gettext('New password') . ':</label>
		<input type="password" name="newpwd" /><br />

		<label for="newpwd2">'. _gettext('New password again') . ':</label>
		<input type="password" name="newpwd2" /><br />

		<input class="btn" type="submit" value="' ._gettext('Change account password') . '" />

		</form>';
	}

	/*
	* +------------------------------------------------------------------------------+
	* Site Administration Pages
	* +------------------------------------------------------------------------------+
	*/

	/* Display disk space used per board, and finally total in a large table */
	function spaceused() {
		global $tc_db, $tpl_page;
		$this->AdministratorsOnly();
		$this->CheckToken($_GET['token']);
		

		$tpl_page .= '<h2>'. _gettext('Disk space used') . '</h2><br />';
		
		$tpl_page .= '<table class="table table-striped table-bordered table-hover table-condensed" border="1" width="100%">
		<tr>
		<th>Доска</th>
		<th>Файлы</th>
		<th>Использовано места</th>
		</tr>';
		$boards = $tc_db->GetAll('select name from boards');
		for ($i=0; $i < count($boards); $i++){
			$files = $tc_db->GetOne('select count(f.id) from files f join posts_files pf on f.id = pf.fileid  join boards b on b.id = pf.boardid where b.name ='. $tc_db->qstr($boards[$i]['name']));
			$size = $tc_db->GetOne('select sum(f.size) from files f join posts_files pf on f.id = pf.fileid  join boards b on b.id = pf.boardid where b.name ='. $tc_db->qstr($boards[$i]['name']));
			
			$tpl_page .= '<tr><td>' .$boards[$i]['name']. '</td><td>' .$files. '</td><td>' .ConvertBytes($size). '</td></tr>';
		}
		$tpl_page .= '</table>';		
	}

	function staff() { //183 lines
		global $tc_db, $tpl_page;
		$this->AdministratorsOnly();

		$tpl_page .= '<h2>Аккаунты</h2><br />';
		if (isset($_GET['add']) && !empty($_POST['username']) && !empty($_POST['password'])) {
		$this->CheckToken($_POST['token']);
			$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" .KU_DBPREFIX. "staff` WHERE `username` = " .$tc_db->qstr($_POST['username']));
			if (count($results) == 0) {
				if ($_POST['type'] < 3 && $_POST['type'] >= 0) {
          $this->CheckToken($_POST['token']);
					$salt = $this->CreateSalt();
					$tc_db->Execute("INSERT HIGH_PRIORITY INTO `" .KU_DBPREFIX. "staff` ( `username` , `password` , `salt` , `type` , `addedon` ) VALUES (" .$tc_db->qstr($_POST['username']). " , '" .md5($_POST['password'] . $salt). "' , '" .$salt. "' , '" .$_POST['type']. "' , '" .time(). "' )");
					$log = _gettext('Added'). ' ';
					switch ($_POST['type']) {
						case 0:
							$log .= _gettext('Janitor');
							break;
						case 1:
							$log .= _gettext('Administrator');
							break;
						case 2:
							$log .= _gettext('Moderator');
							break;
					}
					$log .= ' '. $_POST['username'];
					management_addlogentry($log, 6);
					$tpl_page .= _gettext('Staff member successfully added.');
				} else {
					exitWithErrorPage('Invalid type');
				}
			} else {
				$tpl_page .= _gettext('A staff member with that ID already exists.');
			}
		} elseif (isset($_GET['del']) && $_GET['del'] > 0) {
			$this->CheckToken($_GET['token']);
			$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "staff` WHERE `id` = " . $tc_db->qstr($_GET['del']) . "");
			if (count($results) > 0) {
				$username = $results[0]['username'];
				$tc_db->Execute("DELETE FROM `" . KU_DBPREFIX . "staff` WHERE `id` = " . $tc_db->qstr($_GET['del']) . "");
				$tpl_page .= _gettext('Staff successfully deleted') . '<hr />';
				management_addlogentry(_gettext('Deleted staff member') . ': '. $username, 6);
			} else {
				$tpl_page .= _gettext('Invalid staff ID.');
			}
		} elseif (isset($_GET['edit'])) {
			$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "staff` WHERE `id` = " . $tc_db->qstr($_GET['edit']) . "");
			if (count($results) > 0) {
				if (isset($_POST['submitting'])) {
				if ($_GET['edit'] > 0) $this->CheckToken($_POST['token']); //check token when changing 
					$username = $results[0]['username'];
					$type	= $results[0]['type'];
					$boards	= array();
					if (isset($_POST['modsallboards'])) {
						$newboards = array('allboards');
					} else {
						$results = $tc_db->GetAll("SELECT HIGH_PRIORITY name FROM `" . KU_DBPREFIX . "boards`");
						foreach ($results as $line) {
							$boards = array_merge($boards, array($line['name']));
						}
						$changed_boards = array();
						$newboards = array();
						while (list($postkey, $postvalue) = each($_POST)) {
							if (substr($postkey, 0, 8) == "moderate") {
								$changed_boards = array_merge($changed_boards, array(substr($postkey, 8)));
							}
						}
						while (list(, $thisboard_name) = each($boards)) {
							if (in_array($thisboard_name, $changed_boards)) {
								$newboards = array_merge($newboards, array($thisboard_name));
							}
						}
					}
					$logentry = _gettext('Updated staff member') . ' - ';
					if ($_POST['type'] == '1') {
						$logentry .= _gettext('Administrator');
					} elseif ($_POST['type'] == '2') {
						$logentry .= _gettext('Moderator');
					} elseif ($_POST['type'] == '0') {
						$logentry .= _gettext('Janitor');
					} else {
						exitWithErrorPage('Something went wrong.');
					}
					$logentry .= ': '. $username;
					if ($_POST['type'] != '1') {
						$logentry .= ' - '. _gettext('Moderates') . ': ';
						if (isset($_POST['modsallboards'])) {
							$logentry .= strtolower(_gettext('All boards'));
						} else {
							$logentry .= '/'. implode('/, /', $newboards) . '/';
						}
					}
					$tc_db->Execute("UPDATE `" . KU_DBPREFIX . "staff` SET `boards` = " . $tc_db->qstr(implode('|', $newboards)) . " , `type` = " .$tc_db->qstr($_POST['type']). " WHERE `id` = " . $tc_db->qstr($_GET['edit']) . "");
					management_addlogentry($logentry, 6);
					$tpl_page .= _gettext('Staff successfully updated') . '<hr />';
				}
				$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "staff` WHERE `id` = '" . $_GET['edit'] . "'");
				$username = $results[0]['username'];
				$type	= $results[0]['type'];
				$boards	= explode('|', $results[0]['boards']);

				$tpl_page .= '<form action="manage_page.php?action=staff&edit=' .$_GET['edit']. '" method="post">
              <input type="hidden" name="token" value="' . $_SESSION['token'] . '" />
							<label for="username">' ._gettext('Username'). ':</label>
							<input type="text" id="username" name="username" value="' .$username. '" disabled="disabled" /><br />
							<label for="type">' ._gettext('Type'). ':</label>
							<select id="type" name="type">';
				$tpl_page .= ($type==1) ? '<option value="1" selected="selected">' ._gettext('Administrator'). '</option>' : '<option value="1">' ._gettext('Administrator'). '</option>';
				$tpl_page .= ($type==2) ? '<option value="2" selected="selected">' ._gettext('Moderator'). '</option>' : '<option value="2">' ._gettext('Moderator'). '</option>';
				$tpl_page .= ($type==0) ? '<option value="0" selected="selected">' ._gettext('Janitor'). '</option>' : '<option value="0">' ._gettext('Janitor'). '</option>';
				$tpl_page .= '</select><br /><br />';

				$tpl_page .= _gettext('Moderates') . '<br />';
				
				$tpl_page .= '<label class="checkbox">';
				if ($boards == array('allboards')){
					$tpl_page .= '<input id="modsallboards_check" type="checkbox" name="modsallboards" checked>';
				} else{
					$tpl_page .= '<input id="modsallboards_uncheck" type="checkbox" name="modsallboards">';
				}
				$tpl_page .= 'Все разделы</label>'. "\n";

				$tpl_page .= 'или<br />';
				$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "boards`");
				foreach ($results as $line) {
					if (in_array($line['name'], $boards)) {
						$tpl_page .= '<label class="checkbox" for="moderate' .$line['name']. '"><input type="checkbox" name="moderate' .$line['name']. '" id="moderate' .$line['name']. '" checked> ' .$line['name']. '</label>';
					} else {
						$tpl_page .= '<label class="checkbox" for="moderate' .$line['name']. '"><input type="checkbox" name="moderate' .$line['name']. '" id="moderate' .$line['name']. '"> ' .$line['name']. '</label>';
					}
					
				}
				$tpl_page .= '<input type="hidden" name="token" value="' . $_SESSION['token'] . '" />
				<input class="btn" type="submit" value="'. _gettext('Modify staff member') . '" name="submitting" />
							</form><br />';
			}
		}

		$tpl_page .= '<form action="manage_page.php?action=staff&add" method="post">
          <input type="hidden" name="token" value="' . $_SESSION['token'] . '" />
					<label for="username">' ._gettext('Username'). ':</label>
					<input type="text" id="username" name="username" /><br />
					<label for="password">' ._gettext('Password'). ':</label>
					<input type="text" id="password" name="password" /><br />
					<label for="type">' ._gettext('Type'). ':</label>
					<select id="type" name="type">
						<option value="1">' ._gettext('Administrator'). '</option>
						<option value="2">' ._gettext('Moderator'). '</option>
						<option value="0">' ._gettext('Janitor'). '</option>
					</select><br />

					<input class="btn" type="submit" value="' ._gettext('Add staff member'). '" />
					</form>
					<hr /><br />';

		$tpl_page .= '<table class="table table-striped table-bordered table-hover table-condensed" border="1" width="100%"><tr><th>'. _gettext('Username') . '</th><th>'. _gettext('Added on') . '</th><th>'. _gettext('Last active') . '</th><th>'. _gettext('Moderating boards') . '</th><th>&nbsp;</th></tr>'. "\n";
		$i = 1;
		while($i <= 3) {
			if ($i == 1) {
				$stafftype = 'Administrator';
				$numtype = 1;
			} elseif ($i == 2) {
				$stafftype = 'Moderator';
				$numtype = 2;
			} elseif ($i == 3) {
				$stafftype = 'Janitor';
				$numtype = 0;
			}
			$tpl_page .= '<tr><td align="center" colspan="5"><font size="+1"><strong>'. _gettext($stafftype) . '</strong></font></td></tr>'. "\n";
			$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "staff` WHERE `type` = '" .$numtype. "' ORDER BY `username` ASC");
			if (count($results) > 0) {
				foreach ($results as $line) {
					$tpl_page .= '<tr><td>' .$line['username']. '</td><td>' .date("y/m/d(D)H:i", $line['addedon']). '</td><td>';
					if ($line['lastactive'] == 0) {
						$tpl_page .= _gettext('Never');
					} elseif ((time() - $line['lastactive']) > 300) {
						$tpl_page .= timeDiff($line['lastactive'], false);
					} else {
						$tpl_page .= _gettext('Online now');
					}
					$tpl_page .= '</td><td>';
					if ($line['boards'] != '' || $line['type'] == 1) {
						if ($line['boards'] == 'allboards' || $line['type'] == 1) {
							$tpl_page .=  _gettext('All boards') ;
						} else {
							$tpl_page .= '<strong>/'. implode('/</strong>, <strong>/', explode('|', $line['boards'])) . '/</strong>';
						}
					} else {
						$tpl_page .= _gettext('No boards');
					}
					$tpl_page .= '</td><td>[<a href="?action=staff&edit='. $line['id'] . '">'. _gettext('Edit') . '</a>] [<a href="?action=staff&del='. $line['id'] . '&token='.$_SESSION['token'].'">'. _gettext('Delete') .'</a>]</td></tr>'. "\n";
				}
			} else {
				$tpl_page .= '<tr><td colspan="5">'. _gettext('None') . '</td></tr>'. "\n";
			}
			$i++;
		}
		$tpl_page .= '</table>';
	}

	function modlog_after() {
		global $tc_db;
		$this->ModeratorsOnly();
		$results = $tc_db->GetAll("select * from (SELECT * FROM `" . KU_DBPREFIX . "modlog` where timestamp > " .$tc_db->qstr($_GET['timestamp']). " ORDER BY `timestamp` DESC limit 10) x order by timestamp asc");
		if ($results) {
			$out .= '
			<table cellspacing="2" cellpadding="1" border="1" width="100%">';
			foreach ($results as $line) {
				$out .= "<tr><td>" . date("(D)H:i", $line['timestamp']) . "</td><td>" . $line['user'] . "</td><td>" . $line['entry'] . "</td></tr>";
			}
			$out .= '</table>';
		} else {
			$out = 'false';
		}
		die($out);
	}

	/* Display moderators and administrators actions which were logged */
	function modlog() {
		global $tc_db, $tpl_page;
		$this->ModeratorsOnly();
		$this->CheckToken($_GET['token']);

		$tc_db->Execute("DELETE FROM `" . KU_DBPREFIX . "modlog` WHERE `timestamp` < '" . (time() - KU_MODLOGDAYS * 86400) . "'");

		$tpl_page .= '<h2>'. ('Модлог') . '</h2><br />
		<table class="table table-striped table-bordered table-hover table-condensed" cellspacing="2" cellpadding="1" border="1" width="100%"><tr><th>'. _gettext('Time') .'</th><th>'. _gettext('User') .'</th><th width="100%">'. _gettext('Action') .'</th></tr>';
		$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "modlog` ORDER BY `timestamp` DESC");
		foreach ($results as $line) {
			$tpl_page .= "<tr><td>" . date("y/m/d(D)H:i", $line['timestamp']) . "</td><td>" . $line['user'] . "</td><td>" . $line['entry'] . "</td></tr>";
		}
		$tpl_page .= '</table>';
	}

	function cleanup() {
		global $tc_db, $tpl_page;
		$this->AdministratorsOnly();
		$tpl_page .= '<h2>Очистка</h2><br />';

		if (isset($_POST['memcache'])) {
			$this->CheckToken($_POST['token']);
			cache_flush();
			$this->regen_changelog();
			$tpl_page .= 'Кэш удалён';
			//management_addlogentry(_gettext('Очистил мемкеш'), 2);
		} else {
			$tpl_page .= '<form action="manage_page.php?action=cleanup" method="post">'. "\n" .
						'	<input type="hidden" name="token" value="' . $_SESSION['token'] . '" />' .
						'	<input class="btn" name="memcache" id="memcache" type="submit" value="Очистить мемкеш" />'. "\n" .
						'</form>';
		}
	}

	/*
	* +------------------------------------------------------------------------------+
	* Boards Administration Pages
	* +------------------------------------------------------------------------------+
	*/

	function adddelboard() {
		global $tc_db, $tpl_page, $board_class;
		$this->AdministratorsOnly();

		if (isset($_POST['directory'])) {
      $this->CheckToken($_POST['token']);
			if (isset($_POST['add'])) {
			$this->CheckToken($_POST['token']);
				$tpl_page .= $this->addBoard($_POST['directory'], $_POST['desc']);
			} elseif (isset($_POST['del'])) {
			$this->CheckToken($_POST['token']);
				if (isset($_POST['confirmation'])) {
				$this->CheckToken($_POST['token']);
					$tpl_page .= $this->delBoard($_POST['directory'], $_POST['confirmation']);
				} else {
					$tpl_page .= $this->delBoard($_POST['directory']);
				}
			}
		}
		$tpl_page .= '<h2>Добавить раздел</h2><br />
		<form action="manage_page.php?action=adddelboard" method="post">
    <input type="hidden" name="token" value="' . $_SESSION['token'] . '" />
		<input type="hidden" name="add" id="add" value="add" />
		
		<label for="directory">Название(может состоять только из цифр и букв):</label>
		<input type="text" name="directory" id="directory" /><br />

		<label for="desc">'. _gettext('Description') . ':</label>
		<input type="text" name="desc" id="desc" />

		<label for="firstpostid">'. _gettext('id первого поста') . ':</label>
		<input type="text" name="firstpostid" id="firstpostid" value="1" />

		<br><input class="btn" type="submit" value="Добавить доску" />

		</form><br /><hr />

		<h2>'. _gettext('Удалить раздел') .'</h2><br />

		<form action="manage_page.php?action=adddelboard" method="post">
    <input type="hidden" name="token" value="' . $_SESSION['token'] . '" />
		<input type="hidden" name="del" id="del" value="del" />
		<label for="directory">Доска:</label>' .
		$this->MakeBoardListDropdown('directory', $this->BoardList($_SESSION['manageusername'])) .
		'<br />

		<input class="btn" type="submit" value="Удалить доску" />

		</form>';
	}

	function addBoard($dir, $desc) {
		global $tc_db;
		$this->AdministratorsOnly();
		

		$output = '';
		$output .= '<h2>'. _gettext('Add Results') .'</h2><br />';
		$dir = cleanBoardName($dir);
		if ($dir != '' && $desc != '') {
			if (strtolower($dir) != 'allboards') {
				$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "boards` WHERE `name` = " . $tc_db->qstr($dir) . "");
				if (count($results) == 0) {
					if (mkdir(KU_BOARDSDIR . $dir, 0777) && mkdir(KU_BOARDSDIR . $dir . '/res', 0777) && mkdir(KU_BOARDSDIR . $dir . '/src', 0777) && mkdir(KU_BOARDSDIR . $dir . '/thumb', 0777)) {
						file_put_contents(KU_BOARDSDIR . $dir . '/.htaccess', 'DirectoryIndex '. KU_FIRSTPAGE . '');
						file_put_contents(KU_BOARDSDIR . $dir . '/src/.htaccess', 'AddType text/plain .ASM .C .CPP .CSS .JAVA .JS .LSP .PHP .PL .PY .RAR .SCM .TXT'. "\n" . 'SetHandler default-handler');
						if ($_POST['firstpostid'] < 1) {
							$_POST['firstpostid'] = 1;
						}
						$tc_db->Execute("INSERT INTO `" . KU_DBPREFIX . "boards` ( `name` , `desc` , `createdon`, `start`, `image`, `includeheader` ) VALUES ( " . $tc_db->qstr($dir) . " , " . $tc_db->qstr($desc) . " , '" . time() . "', " . $_POST['firstpostid'] . ", '', '' )");
						$boardid = $tc_db->Insert_Id();
						$filetypes = $tc_db->GetAll("SELECT " . KU_DBPREFIX . "filetypes.id FROM " . KU_DBPREFIX . "filetypes WHERE " . KU_DBPREFIX . "filetypes.filetype = 'JPG' OR " . KU_DBPREFIX . "filetypes.filetype = 'GIF' OR " . KU_DBPREFIX . "filetypes.filetype = 'PNG';");
						foreach ($filetypes AS $filetype) {
							$tc_db->Execute("INSERT INTO `" . KU_DBPREFIX . "board_filetypes` ( `boardid` , `typeid` ) VALUES ( " . $boardid . " , " . $filetype['id'] . " );");
						}
						$board_class = new Board($dir);
						$board_class->RegenerateAll();
						unset($board_class);
						$output .= _gettext('Board successfully added.') . '<br /><br /><a href="'. KU_BOARDSPATH . '/'. $dir . '/">/'. $dir . '/</a>!<br />';
						$output .= '<form action="?action=boardopts" method="post"><input type="hidden" name="board" value="'. $dir . '" /><input class="btn" type="submit" style="border: 1px solid; background: none; text-align: center;" value="'. _gettext('Click to edit board options') .'" /><br /><hr /></form>';
						management_addlogentry(_gettext('Added board') . ': /'. $dir . '/', 3);
					} else {
						$output .= '<br />'. _gettext('Unable to create directories.');
					}
				} else {
					$output .= _gettext('A board with that name already exists.');
				}
			} else {
				$output .= _gettext('That name is for internal use. Please pick another.');
			}
		} else {
			$output .= _gettext('Please fill in all required fields.');
		}
		return $output;
	}

	function delboard($dir, $confirm = '') {
		global $tc_db;
		$this->AdministratorsOnly();

		$output = '';
		$output .= '<h2>'. _gettext('Delete Results') .'</h2><br />';
		if (!empty($dir)) {
			$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "boards` WHERE `name` = " . $tc_db->qstr($dir) . "");
			foreach ($results as $line) {
				$board_id = $line['id'];
				$board_dir = $line['name'];
			}
			if (count($results) > 0) {
				if (!empty($confirm)) {
					if (removeBoard($board_dir)) {
						$tc_db->Execute("DELETE FROM `" . KU_DBPREFIX . "posts` WHERE `boardid` = '" . $board_id . "'");
						$tc_db->Execute("DELETE FROM `" . KU_DBPREFIX . "boards` WHERE `id` = '" . $board_id . "'");
						$tc_db->Execute("DELETE FROM `" . KU_DBPREFIX . "board_filetypes` WHERE `boardid` = '" . $board_id . "'");
						require_once KU_ROOTDIR . 'inc/classes/menu.class.php';
						$menu_class = new Menu();
						$menu_class->Generate();
						$output .= _gettext('Board successfully deleted.');
						management_addlogentry(_gettext('Deleted board') .': /'. $dir . '/', 3);
					} else {
						// Error
						$output .= _gettext('Unable to delete board.');
					}
				} else {
					$output .= sprintf(_gettext('Are you absolutely sure you want to delete %s?'),'/'. $board_dir . '/') .
					'<br />
					<form action="manage_page.php?action=adddelboard" method="post">
          <input type="hidden" name="token" value="' . $_SESSION['token'] . '" />
					<input type="hidden" name="del" id="del" value="del" />
					<input type="hidden" name="directory" id="directory" value="'. $dir . '" />
					<input type="hidden" name="confirmation" id="confirmation" value="yes" />

					<input class="btn" type="submit" value="'. _gettext('Continue') .'" />

					</form><br />';
				}
			} else {
				$output .= _gettext('A board with that name does not exist.');
			}
		}
		$output .= '<br />';

		return $output;
	}
	
	/* Replace words in posts with something else */
	function wordfilter() {
		global $tc_db, $tpl_page;
		$this->AdministratorsOnly();

		$tpl_page .= '<h2>'. _gettext('Wordfilter') . '</h2><br />';
		if (isset($_POST['word'])) {
      $this->CheckToken($_POST['token']);
			if ($_POST['word'] != '' && $_POST['replacedby'] != '') {
				$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "wordfilter` WHERE `word` = " . $tc_db->qstr($_POST['word']) . "");
				if (count($results) == 0) {
					$wordfilter_boards = array();

					foreach ($results as $line) {
						$wordfilter_word = $line['word'];
					}
					$wordfilter_boards = array();
					$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "boards`");
					foreach ($_POST['wordfilter'] as $board) {
						$check = $tc_db->GetOne("SELECT `id` FROM `" . KU_DBPREFIX . "boards` WHERE `name` = " . $tc_db->qstr($board));
						if (!empty($check)) {
							$wordfilter_boards[] = $board;
						}
					}

					$is_regex = (isset($_POST['regex'])) ? '1' : '0';

					$tc_db->Execute("INSERT HIGH_PRIORITY INTO `" . KU_DBPREFIX . "wordfilter` ( `word` , `replacedby` , `boards` , `time` , `regex` ) VALUES ( " . $tc_db->qstr($_POST['word']) . " , " . $tc_db->qstr($_POST['replacedby']) . " , " . $tc_db->qstr(implode('|', $wordfilter_boards)) . " , '" . time() . "' , '" . $is_regex . "' )");

					$tpl_page .= _gettext('Word successfully added.');
					management_addlogentry(sprintf(_gettext("Added word to wordfilter: %s - Changes to: %s - Boards: /%s/"),$_POST['word'], $_POST['replacedby'], implode('/, /', $wordfilter_boards)), 11);
				} else {
					$tpl_page .= _gettext('That word already exists.');
				}
			} else {
				$tpl_page .= _gettext('Please fill in all required fields.');
			}
			$tpl_page .= '<hr />';
		} elseif (isset($_GET['delword'])) {
			$this->CheckToken($_GET['token']);
			if ($_GET['delword'] > 0) {
				$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "wordfilter` WHERE `id` = " . $tc_db->qstr($_GET['delword']) . "");
				if (count($results) > 0) {
					foreach ($results as $line) {
						$del_word = $line['word'];
					}
					$tc_db->Execute("DELETE FROM `" . KU_DBPREFIX . "wordfilter` WHERE `id` = " . $tc_db->qstr($_GET['delword']) . "");
					$tpl_page .= _gettext('Word successfully removed.');
					management_addlogentry(_gettext('Removed word from wordfilter') . ': '. $del_word, 11);
				} else {
					$tpl_page .= _gettext('That ID does not exist.');
				}
				$tpl_page .= '<hr />';
			}
		} elseif (isset($_GET['editword'])) {
			if ($_GET['editword'] > 0) {
				$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "wordfilter` WHERE `id` = " . $tc_db->qstr($_GET['editword']) . "");
				if (count($results) > 0) {
					if (!isset($_POST['replacedby'])) {
					//$this->CheckToken($_POST['token']);
						foreach ($results as $line) {
							$tpl_page .= '<form action="manage_page.php?action=wordfilter&editword='.$_GET['editword'].'" method="post">
              <input type="hidden" name="token" value="' . $_SESSION['token'] . '" />
							<label for="word">'. _gettext('Word') .':</label>
							<input type="text" name="word" value="'.$line['word'].'" disabled /><br />

							<label for="replacedby">'. _gettext('Is replaced by') .':</label>
							<input type="text" name="replacedby" value="'.$line['replacedby'].'" /><br />';

							if ($line['regex'] == '1') {
								$tpl_page .= '<label>Регэксп: <input class="checkbox" name="regex" type="checkbox" checked></label><br />';
							} else {
								$tpl_page .= '<label>Регэксп: <input class="checkbox" name="regex" type="checkbox"></label><br />';
							}
							$tpl_page .= '<label>Доски:</label>';

							$array_boards = array();
							$resultsboard = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "boards`");
							foreach ($resultsboard as $lineboard) {
								$array_boards = array_merge($array_boards, array($lineboard['name']));
							}
							
							$tpl_page .= '
							<div class="btn-group">
							<button data-toggle="dropdown" class="btn btn-primary dropdown-toggle" data-placeholder="Please select">Please select <span class="caret"></span></button>
							<ul class="dropdown-menu">';
							foreach ($array_boards as $this_board_name) {
								if (in_array($this_board_name, explode("|", $line['boards'])) && explode("|", $line['boards']) != '') {
									$tpl_page .= '<li> <input id="'. $this_board_name . '" name="wordfilter[]" value="'. $this_board_name . '" type="checkbox" checked> <label for="'. $this_board_name . '">'. $this_board_name . '</label></li>';
								} else{
									$tpl_page .= '<li> <input id="'. $this_board_name . '" name="wordfilter[]" value="'. $this_board_name . '" type="checkbox"> <label for="'. $this_board_name . '">'. $this_board_name . '</label></li>';
								}
							}
							$tpl_page .= '</ul></div><br>';
							
							$tpl_page .= '<br /><input class="btn" type="submit" value="Изменить" />';
							$tpl_page .= '</form>';
						
						}
					} else {
					$this->CheckToken($_POST['token']);
						$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "wordfilter` WHERE `id` = " . $tc_db->qstr($_GET['editword']) . "");
						if (count($results) > 0) {
							foreach ($results as $line) {
								$wordfilter_word = $line['word'];
							}
							$wordfilter_boards = array();
							$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "boards`");

							if (isset($_POST['wordfilter'])){
								foreach ($_POST['wordfilter'] as $board) {
									$check = $tc_db->GetOne("SELECT `id` FROM `" . KU_DBPREFIX . "boards` WHERE `name` = " . $tc_db->qstr($board));
									if (!empty($check)) {
										$wordfilter_boards[] = $board;
									}
								}
							}

							$is_regex = (isset($_POST['regex'])) ? '1' : '0';

							$tc_db->Execute("UPDATE `". KU_DBPREFIX ."wordfilter` SET `replacedby` = " . $tc_db->qstr($_POST['replacedby']) . " , `boards` = " . $tc_db->qstr(implode('|', $wordfilter_boards)) . " , `regex` = '" . $is_regex . "' WHERE `id` = " . $tc_db->qstr($_GET['editword']) . "");

							$tpl_page .= _gettext('Word successfully updated.');
							management_addlogentry(_gettext('Updated word on wordfilter') . ': '. $wordfilter_word, 11);
						} else {
							$tpl_page .= _gettext('Unable to locate that word.');
						}
					}
				} else {
					$tpl_page .= _gettext('That ID does not exist.');
				}
				$tpl_page .= '<hr />';
			}
		} else {
			$tpl_page .= '<form action="manage_page.php?action=wordfilter" method="post">
      <input type="hidden" name="token" value="' . $_SESSION['token'] . '" />
			<label for="word">Слово:</label>
			<input type="text" name="word" /><br />

			<label for="replacedby">Замена:</label>
			<input type="text" name="replacedby" /><br />

			<label>Регэксп: <input class="checkbox" name="regex" type="checkbox"></label><br />

			<label>Доски:</label><br />';

			$array_boards = array();
			$resultsboard = $tc_db->GetAll("SELECT HIGH_PRIORITY name FROM `" . KU_DBPREFIX . "boards`");
			$array_boards = array_merge($array_boards, $resultsboard);
			$tpl_page .= $this->MakeBoardListCheckboxes('wordfilter', $array_boards) .

			'<br />

			<input class="btn" type="submit" value="Добавить слово" />

			</form>
			<hr />';
		}
		$tpl_page .= '<br />';

		$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "wordfilter`");
		if ($results > 0) {
			$tpl_page .= '<table class="table table-striped table-bordered table-hover table-condensed" border="1" width="100%"><tr><th>'. _gettext('Word') . '</th><th>'. _gettext('Replacement') . '</th><th>'. _gettext('Boards') . '</th><th>&nbsp;</th></tr>'. "\n";
			foreach ($results as $line) {
				$tpl_page .= '<tr><td>'. $line['word'] . '</td><td>'. $line['replacedby'] . '</td><td>';
				if (explode('|', $line['boards']) != '') {
					$tpl_page .= '<strong>/'. implode('/</strong>, <strong>/', explode('|', $line['boards'])) . '/</strong>&nbsp;';
				} else {
					$tpl_page .= _gettext('No boards');
				}
				$tpl_page .= '</td><td>[<a href="manage_page.php?action=wordfilter&editword='. $line['id'] . '&token='.$_SESSION['token'].'">'. _gettext('Edit') . '</a>] [<a href="manage_page.php?action=wordfilter&delword='. $line['id'] . '&token='.$_SESSION['token'].'">'. _gettext('Delete') .'</a>]</td></tr>'. "\n";
			}
			$tpl_page .= '</table>';
		}
	}

	/* Search for posts by IP */
	function ipsearch($ip = false, $board = false, $count = false) {
		global $cf, $h, $tc_db, $tpl_page;
		$this->AdministratorsOnly();

		if (isset($_GET['ip'])) $ip = $_GET['ip'];
		if (isset($_GET['board'])) $board = $_GET['board'];
		if (isset($_GET['count'])) $count = $_GET['count'];

		if ($ip && $board) {
			if ($board == 'all') {
				$boardid = 999;
				$tmp = $tc_db->GetAll("SELECT HIGH_PRIORITY `name`, `id` FROM `" . KU_DBPREFIX . "boards` ");
				foreach ($tmp as $row) {
					$boards[$row['name']] = $row['id'];
				}
			} else {
				$boards[$board] = $tc_db->GetOne("SELECT HIGH_PRIORITY `id` FROM `" . KU_DBPREFIX . "boards` WHERE `name` = " . $tc_db->qstr($board) . "");
				$boardid = $boards[$board];
			}

			if ($count && is_numeric($count)) {
			} else {
				$count = 100;
			}

			$board = "dev";
			$board_class[$board] = new Board('dev');
			foreach ($boards as $bboard => $boardid) {
				$query = "
					select * from (select
					       p.name, p.message, p.subject, p.email, p.timestamp, p.id, p.boardid, p.parentid, p.tripcode, p.posterauthority,
                                               r.name as rating_name, 
                                               r.id as rating_id, 
                                               r.file as rating_file , 
                                               r.thumb_w as rating_thumb_w, 
                                               r.thumb_h as rating_thumb_h,
					       f.name as file_name,
					       ft.filetype as file_type,
					       f.original as file_original,
					       f.size as file_size,
					       f.size_formatted as file_size_formatted,
					       f.image_w as image_w,
					       f.image_h as image_h,
					       f.thumb_w as thumb_w,
					       f.thumb_h as thumb_h,
					       pf.`order` as file_order

                                        from " . KU_DBPREFIX . "posts AS p 
                                        left join posts_files pf
					       on p.id = pf.postid and p.boardid = pf.boardid
					left join ratings r 
                                               on pf.ratingid = r.id 
					left join files f
					       on pf.fileid = f.id 
					left join filetypes ft
					       on f.type = ft.id
                                        where p.`boardid` = $boardid
						and p.ipmd5 = '" . md5($ip)."'
                                        order by p.`id` desc limit " .$count . " ) x order by id, file_order asc";

				//bdl_debug($query);
				$allposts[$bboard] = $tc_db->GetAll($query);
				$allposts[$bboard] = $board_class[$board]->CompactPosts($allposts[$bboard]);
			}

			$thread_id = str_pad($count, 4, "0", STR_PAD_LEFT) . str_pad($boardid, 3, "0", STR_PAD_LEFT) .  sprintf("%u", ip2long($ip));
			$board_class[$board]->InitializeDwoo();
			$board_class[$board]->dwoo_data['replythread'] = $thread_id;
			$header = $board_class[$board]->PageHeader($thread_id);
			$footer = $board_class[$board]->Footer();
			$postbox = $board_class[$board]->Postbox(1);
			$postbox = str_replace("<!sm_threadid>", $thread_id, $postbox);
			$header = str_replace("<!sm_threadid>", $thread_id, $header);
			$xposts = Array();
			foreach ($allposts as $board => $posts) {
				bdl_debug ('searching '. $board);
				$board_class[$board] = new Board($board);
				$board_class[$board]->InitializeDwoo();
				
				$t = count($posts);
				
				//$page = '';
				$top_post['tripcode'] = 'поняба';
				$top_post['thumb_w'] = 290;
				$top_post['thumb_h'] = 181;
				$top_post['image_w'] = 1920;
				$top_post['image_h'] = 1200;
				$top_post['file_count'] = 1;
				$top_post['file']['name'] = 140086058578;
				$top_post['type']['type'] = 'jpg';
				$top_post['file']['original'] = 'поняша';
				$top_post['file']['size'] = 0;
				$top_post['file']['size_formatted'] = '42B';
				$top_post['timestamp'] = time();
				$top_post['subject'] = 'Результаты поиска в /'. $board . '/';
				$top_post['parentid'] = 42;
				if ($posts) {
					$top_post['message'] = 'Я нашла ' .$t;
					if ((($t % 10) === 1) && ($t !== 11) && (($t % 100) !== 11)) {
						$top_post['message'] .= ' пост';
					} else if (((($t % 10) === 2) || (($t % 10) === 3) || (($t % 10) === 4)) && (($t != 12) && ($t != 13) && ($t != 14))) {
						$top_post['message'] .= ' поста';
					} else {
						$top_post['message'] .= ' постов';
					}
					$top_post['message'] .= ' с этого айпи в '. $board .'.<br>';
					
					array_unshift($posts, $top_post);
					bdl_debug("found ". count($posts) . " posts");
					foreach ($posts as $key => $post) {
						$xposts[] = $board_class[$board]->BuildPost($post, false);
					}
				} else {
					$top_post['message'] = 'Я ничего не нашла с этого айпи в '. $board .'.<br>';
					$xposts[] = $top_post;
				}	
				
			}
			foreach ($xposts as &$post) {
				$post['parentid'] = 42;
			}
				$xposts[0]['parentid'] = 0;
				$board_class['dev']->dwoo_data['replythread'] = $thread_id;
				$board_class['dev']->dwoo_data['board'] = $board_class['dev']->board;
				$board_class['dev']->dwoo_data['isread'] = false;
				$board_class['dev']->dwoo_data['file_path'] = getCLBoardPath($board_class[$board]->board['name'], $board_class[$board]->board['loadbalanceurl_formatted'], '');
				$board_class['dev']->dwoo_data['posts'] = $xposts;
				$board_class['dev']->dwoo_data['cf'] = $cf;
				$board_class['dev']->dwoo_data['h'] = $h;
				load_haanga();
				$page = Haanga::Load('img_thread.tpl', $board_class['dev']->dwoo_data, true);

			
			print_page('ipsearch_res_'.$thread_id. '.html', $header. $postbox. $page. $footer, 'ipsearch');
			header('Location: '. '/ipsearch/res/'. $thread_id. '.html');
			die();
			

		} else {
		$tpl_page .= '<h2>Поиск по IP</h2><br />'. "\n";
			$tpl_page .= '<form action="?" method="get">'. "\n" .
						'<input type="hidden" name="action" value="ipsearch" />'. "\n" .
						'<label for="board">'. _gettext('Board') . ':</label>'. "\n" .
						$this->MakeBoardListDropdown('board', $this->BoardList($_SESSION['manageusername']), true) . '<br />'. "\n" .
						'<label for="ip">'. _gettext('IP') . ':</label>'. "\n" .
						'<input type="text" name="ip" value="'. ($ip ? $ip : ''). '" /><br />'. "\n" .
						'<label for="count">Последние:</label>'. "\n".
						'<input type="text" name="count" value="'. ($count ? $count : '100'). '" /><br />'. "\n" .
						'<input class="btn" type="submit" value="Поиск">'. "\n";
		}
	}

	/* Search for text in posts */
	function search() {
		global $tc_db, $tpl_page;
		$this->AdministratorsOnly();
		$this->CheckToken($_GET['token']);

		if (isset($_GET['query'])) {
			$search_query = $_GET['query'];
			if (isset($_GET['s'])) {
				$s = $_GET['s'];
			} else {
				$s = 0;
			}
			$search_query_array = explode('KUSABA_AND', $search_query);
			$trimmed = trim($search_query);
			$limit = 10;
			if ($trimmed == '') {
				$tpl_page .= _gettext('Введите запрос');
				exit;
			}
			$boardlist = $this->BoardList($_SESSION['manageusername']);
			$likequery = '';
			foreach ($search_query_array as $search_split) {
				$likequery .= "`message` LIKE " . $tc_db->qstr(str_replace('_', '\_', $search_split)) . " AND ";
			}
			$likequery = substr($likequery, 0, -4);
			$query = '';
			$query .= "SELECT `" . KU_DBPREFIX . "posts`.`id` AS id, `" . KU_DBPREFIX . "posts`.`parentid` AS parentid, `" . KU_DBPREFIX . "posts`.`message` AS message, `" . KU_DBPREFIX . "boards`.`name` AS boardname FROM `" . KU_DBPREFIX . "posts`, `" . KU_DBPREFIX . "boards` WHERE `IS_DELETED` = 0 AND " . $likequery . " AND `" . KU_DBPREFIX . "boards`.`id` = `" . KU_DBPREFIX . "posts`.`boardid` ORDER BY `timestamp` DESC";

			$numresults = $tc_db->GetAll($query);
			$numrows = count($numresults);
			if ($numrows == 0) {
				$tpl_page .= '<h2>Поиск не дал результатов</h2>';
			} else {
				$query .= " LIMIT $limit OFFSET $s";
				$results = $tc_db->GetAll($query);
				$tpl_page .= '<p>Найдена по запросу: <strong>'. $search_query . '</strong></p>';
				$count = 1 + $s;
				foreach ($results as $line) {
					$tpl_page .= '<blockquote><span style="font-size: 1.5em;">'. $count . '.</span> <span style="font-size: 1.3em;">'. _gettext('Board') .': /'. $line['boardname'] . '/, <a href="'.KU_BOARDSPATH . '/'. $line['boardname'] . '/res/';
					if ($line['parentid'] == 0) {
						$tpl_page .= $line['id'] . '.html">';
					} else {
						$tpl_page .= $line['parentid'] . '.html#'. $line['id'] . '">';
					}

					if ($line['parentid'] == 0) {
						$tpl_page .= _gettext('Thread') .' #'. $line['id'];
					} else {
						$tpl_page .= _gettext('Thread') .' #'. $line['parentid'] . ', Post #'. $line['id'];
					}
					$tpl_page .= '</a></span>';

					$regexp = '/(';
					foreach ($search_query_array as $search_word) {
						$regexp .= preg_quote($search_word) . '|';
					}
					$regexp = substr($regexp, 0, -1) . ')/';
					//$line['message'] = preg_replace_callback($regexp, array(&$this, 'search_callback'), stripslashes($line['message']));
					$line['message'] = stripslashes($line['message']);
					$tpl_page .= '<fieldset>'. $line['message'] . '</fieldset></blockquote><br />';
					$count++;
				}
				$currPage = (($s / $limit) + 1);
				$tpl_page .= '<br />';
				if ($s >= 1) {
					$prevs = ($s - $limit);
					$tpl_page .= "&nbsp;<a href=\"?action=search&s=$prevs&query=" . urlencode($search_query) . "\">&lt;&lt; "._gettext('Prev')." 10</a>&nbsp&nbsp;";
				}
				$pages = intval($numrows / $limit);
				if ($numrows % $limit) {
					$pages++;
				}
				if (!((($s + $limit) / $limit) == $pages) && $pages != 1) {
					$news = $s + $limit;
					$tpl_page .= "&nbsp;<a href=\"?action=search&s=$news&query=" . urlencode($search_query) . "\">"._gettext('Next')." 10 &gt;&gt;</a>";
				}

				$a = $s + ($limit);
				if ($a > $numrows) {
					$a = $numrows;
				}
				$b = $s + 1;

				$tpl_page .= $this->search_results_display($a, $b, $numrows);
			}
		}

		$tpl_page .= '<form action="?" method="get">
		<input type="hidden" name="action" value="search" />
		<input type="hidden" name="s" value="0" />

		<strong>'. _gettext('Query') .'</strong>:<br /><input type="text" name="query" ';
		if (isset($_GET['query'])) {
			$tpl_page .= 'value="'. $_GET['query'] . '" ';
		}
		$tpl_page .= 'size="52" /><br />

		<input class="btn" type="submit" value="'. _gettext('Search') .'" /><br /><br />

		'. _gettext('Separate search terms with the word <strong>KUSABA_AND</strong>') .' <br /><br />

		'. _gettext('To find a single phrase anywhere in a post\'s message, use:') .'<br />
		%'. _gettext('some phrase here') .'%<br /><br />

		'. _gettext('To find a phrase at the beginning of a post\'s message:') .'<br />
		'. _gettext('some phrase here') .'%<br /><br />

		'. _gettext('To find a phrase at the end of a post\'s message:') .'<br />
		%'. _gettext('some phrase here') .'<br /><br />

		'. _gettext('To find two phrases anywhere in a post\'s message, use:') .'<br />
		%'. _gettext('first phrase here') .'%KUSABA_AND%'. _gettext('second phrase here') .'%<br /><br />

		</form>';
	}
	function search_callback($matches) {
		print_r($matches);
		return '<strong>'. $matches[0] . '</strong>';
	}

	function search_results_display($a, $b, $numrows) {
		return '<p>'. _gettext('Results') . ' <strong>'. $b . '</strong> to <strong>'. $a . '</strong> of <strong>'. $numrows . '</strong></p>'. "\n" .
		'<hr />';
	}
	// Credits to Eman for this code

	function regen_changelog($from_manage = false) {
		global $tc_db, $tpl_page;
		$this->AdministratorsOnly();
		if ($from_manage) {
			cache_flush();
			return;
		}

		$out = '<body>';
		$results = $tc_db->GetAll("select date(timestamp) as date from changelog group by date desc");
		foreach ($results as $date) {
			$results2 = $tc_db->GetAll("SELECT * FROM changelog WHERE date(timestamp) = " . $tc_db->qstr($date['date']) . " order by timestamp desc");
				$out .= "<div class=\"reply\">";
				$out .= "<center>".$date['date']."</center>";
				foreach ($results2 as $log) {
					$out .= "<span id=\"".$log['id']."\"><a href=\"#".$log['id']."\">#".$log['id']."</a> ".$log['message']."</span><br />";
			}
			$out .= "</div><br />";
		}
		$out .= '</body></html>';
		return $out;
	}

	/* Add, view, and delete changelog entries*/
	function changelog() {
		global $tc_db, $tpl_page;
		$this->AdministratorsOnly();
		
		
		$tpl_page .= '<h2>Чейнджлог</h2><br />';
		if (isset($_GET['do'])) {
			if ($_GET['do'] == 'addchlg') {
				if (isset($_POST['message']) ) {
         				$this->CheckToken($_POST['token']);
					if ($_POST['message'] != '') {
						$tc_db->Execute("INSERT HIGH_PRIORITY INTO `" . KU_DBPREFIX . "changelog` ( `message`) VALUES ( " . $tc_db->qstr($_POST['message']) . ")");
						$tpl_page .= 'Новость добавлена';
						$this->regen_changelog(true);
					}
				} else {
					$tpl_page .= '<form action="?action=changelog&do=addchlg" method="post">
          				<input type="hidden" name="token" value="' . $_SESSION['token'] . '" />
					<label for="message">Новость</label>
					<textarea style="width: 880px; height: 150px;" id="message" name="message"></textarea>
					<br>

					<input class="btn" type="submit" value="'. _gettext('Add') .'" />

					</form>';
				}
				$tpl_page .= '<br /><hr />';
			}
			if ($_GET['do'] == 'editchlg' && $_GET['chlgid'] >= 0) {
				if ($_GET['chlgid'] != '' && $_POST['message'] != '') {
           				$this->CheckToken($_POST['token']);
					$tc_db->Execute("UPDATE `" . KU_DBPREFIX . "changelog` SET `message` = " . $tc_db->qstr($_POST['message']) . " WHERE `id` = " . $tc_db->qstr($_GET['chlgid']) . "");
					$tpl_page .= "Новость обновлена";
					$this->regen_changelog(true);
				} else {
					$results = $tc_db->GetRow("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "changelog` WHERE `id` = " . $tc_db->qstr($_GET['chlgid']) . "");
					if ($results) {
							$tpl_page .= '<form action="?action=changelog&do=editchlg&chlgid='. $_GET['chlgid'] . '" method="post">
             						<input type="hidden" name="token" value="' . $_SESSION['token'] . '" />
							<label for="message">Новость</label>
							<textarea id="text" name="message" style="width: 880px; height: 150px;">' . (isset($results['message']) ? htmlspecialchars($results['message']) : '')  . '</textarea>
							<br>
							<input class="btn" type="submit" value="'. _gettext('Edit') .'" />

							</form>';
					} else {
						$tpl_page .= _gettext('Не могу найти новость с таким id');
					}
				}
				$tpl_page .= '<br /><hr />';
			}
			if ($_GET['do'] == 'deletechlg' && $_GET['chlgid'] >= 0) {
				$tc_db->Execute("DELETE FROM `" . KU_DBPREFIX . "changelog` WHERE `id` = " . $tc_db->qstr($_GET['chlgid']) . "");
				$tpl_page .= _gettext('Новость удалена');
				$this->regen_changelog(true);
			$tpl_page .= '<br /><hr />';
			}
		}
		$tpl_page .= '<a href="?action=changelog&do=addchlg" class="btn">Добавить новость</a><br /><br />';
		$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "changelog` ORDER BY `id` ASC");
		if (count($results) > 0) {
			$tpl_page .= '<table class="table table-striped table-bordered table-hover table-condensed" border="1" width="100%"><tr><th>'. _gettext('ID') .'</th><th>Время</th><th>Новость</th><th>'. _gettext('Edit/Delete') .'</th></tr>';
			foreach ($results as $line) {
				$tpl_page .= '<tr><td>'. $line['id'] . '</td><td>'. $line['timestamp'] . '</td><td>'. htmlspecialchars(stripslashes($line['message'])) . '</td><td>[<a href="?action=changelog&do=editchlg&chlgid='. $line['id'] . '">'. _gettext('Edit') .'</a>] [<a href="?action=changelog&do=deletechlg&chlgid='. $line['id'] . '">'. _gettext('Delete') .'</a>]</td></tr>';
			}
			$tpl_page .= '</table>';
		} else {
			$tpl_page .= _gettext('There are currently no messages.');
		}
	}


	/* Add, view, and delete ratings*/
	function editratings() {
		global $tc_db, $tpl_page;
		$this->AdministratorsOnly();
		
		$tpl_page .= '<h2>Рейтинги</h2><br />';
		if (isset($_GET['do'])) {
			if ($_GET['do'] == 'addrating') {
				if (isset($_POST['rating']) || isset($_POST['file'])) {
         $this->CheckToken($_POST['token']);
					if ($_POST['rating'] != '' && $_POST['file'] != '') {
						$tc_db->Execute("INSERT HIGH_PRIORITY INTO `" . KU_DBPREFIX . "ratings` ( `name`, `file`, `thumb_h`, `thumb_w` ) VALUES ( " . $tc_db->qstr($_POST['rating']) . " , " . $tc_db->qstr($_POST['file']) . " , " . $tc_db->qstr($_POST['thumb_h']) . " , " . $tc_db->qstr($_POST['thumb_w']) . " )");
						$tpl_page .= _gettext('Rating added.');
					}
				} else {
					$tpl_page .= '<form action="?action=editratings&do=addrating" method="post">
          <input type="hidden" name="token" value="' . $_SESSION['token'] . '" />
					<label for="rating">'. _gettext('Rating') .':</label>
					<input type="text" name="rating" />

					<label for="thumb_w">Ширина картинки:</label>
					<input type="text" name="thumb_w" value="200" />

					<label for="thumb_h">Высота картинки:</label>
					<input type="text" name="thumb_h" value="200" />

					<label for="file">Image:</label>
					<input type="text" name="file" value="generic.png" />

					<br><input class="btn" type="submit" value="'. _gettext('Add') .'" />

					</form>';
				}
				$tpl_page .= '<br /><hr />';
			}
			if ($_GET['do'] == 'editrating' && $_GET['ratingid'] > 0) {
				if (isset($_POST['name'])) {
					if ($_POST['name'] != '' && $_POST['file'] != '') {
           					$this->CheckToken($_POST['token']);
						$tc_db->Execute("UPDATE `" . KU_DBPREFIX . "ratings` SET `name` = " . $tc_db->qstr($_POST['name']) . " , `file` = " . $tc_db->qstr($_POST['file']) . " , `thumb_h` = " . $tc_db->qstr($_POST['thumb_h']) . " , `thumb_w` = " . $tc_db->qstr($_POST['thumb_w']) . " WHERE `id` = " . $tc_db->qstr($_GET['ratingid']) . "");
						$tpl_page .= _gettext('Настройки обновлены');
					}
				} else {
					$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "ratings` WHERE `id` = " . $tc_db->qstr($_GET['ratingid']) . "");
					if (count($results) > 0) {
						foreach ($results as $line) {
							$tpl_page .= '<form action="?action=editratings&do=editrating&ratingid='. $_GET['ratingid'] . '" method="post">
             <input type="hidden" name="token" value="' . $_SESSION['token'] . '" />
							<label for="rating">'. _gettext('Rating') .':</label>
							<input type="text" name="name" value="'. $line['name'] . '" />

							<label for="thumb_w">Ширина картинки:</label>
							<input type="text" name="thumb_w" value="'. $line['thumb_w'] . '" />
 
							<label for="thumb_h">Высота картинки:</label>
							<input type="text" name="thumb_h" value="'. $line['thumb_h'] . '" />

							<label for="file">'. _gettext('Image') .':</label>
							<input type="text" name="file" value="'. $line['file'] . '" />

							<br><input class="btn" type="submit" value="'. _gettext('Обновить настройки') .'" />

							</form>';
						}
					} else {
						$tpl_page .= _gettext('Не могу найти рейтинг с таким id');
					}
				}
				$tpl_page .= '<br /><hr />';
			}
			if ($_GET['do'] == 'deleterating' && $_GET['ratingid'] > 0) {
				$tc_db->Execute("DELETE FROM `" . KU_DBPREFIX . "ratings` WHERE `id` = " . $tc_db->qstr($_GET['ratingid']) . "");
				$tc_db->Execute("UPDATE `" . KU_DBPREFIX . "posts` SET `rating` = 0 WHERE `rating` = " . $tc_db->qstr($_GET['ratingid']) . "");
				$tpl_page .= _gettext('Rating deleted. Dont forget to regenerate board for newly un-rated thumb images to appear!');
			$tpl_page .= '<br /><hr />';
			}
		}
		$tpl_page .= '<a class="btn" href="?action=editratings&do=addrating">'. _gettext('Добавить рейтинг') .'</a><br /><br />';
		$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "ratings` ORDER BY `name` ASC");
		if (count($results) > 0) {
			$tpl_page .= '<table class="table table-striped table-bordered table-hover table-condensed" border="1" width="100%"><tr><th>'. _gettext('ID') .'</th><th>'. _gettext('Rating') .'</th><th>'. _gettext('Image') .'</th><th>'. _gettext('Edit/Delete') .'</th></tr>';
			foreach ($results as $line) {
				$tpl_page .= '<tr><td>'. $line['id'] . '</td><td>'. $line['name'] . '</td><td>'. $line['file'] . '</td><td>[<a href="?action=editratings&do=editrating&ratingid='. $line['id'] . '">'. _gettext('Edit') .'</a>] [<a href="?action=editratings&do=deleterating&ratingid='. $line['id'] . '">'. _gettext('Delete') .'</a>]</td></tr>';
			}
			$tpl_page .= '</table>';
		} else {
			$tpl_page .= _gettext('Рейтинги не установлены');
		}
	}

	/* Add, view, and delete sections */
	function editsections() {
		global $tc_db, $tpl_page;
		$this->AdministratorsOnly();

		$tpl_page .= '<h2>Секции</h2><br />';
		if (isset($_GET['do'])) {
			if ($_GET['do'] == 'addsection') {
				if (isset($_POST['name'])) {
					if ($_POST['name'] != '' && $_POST['abbreviation'] != '') {
            $this->CheckToken($_POST['token']);
						$tc_db->Execute("INSERT HIGH_PRIORITY INTO `" . KU_DBPREFIX . "sections` ( `name` , `abbreviation` , `order` , `hidden` ) VALUES ( " . $tc_db->qstr($_POST['name']) . " , " . $tc_db->qstr($_POST['abbreviation']) . " , " . $tc_db->qstr($_POST['order']) . " , '" . (isset($_POST['hidden']) ? '1' : '0') . "' )");
						require_once KU_ROOTDIR . 'inc/classes/menu.class.php';
						$menu_class = new Menu();
						$menu_class->Generate();
						$tpl_page .= _gettext('Секция добавлена');
					}
				} else {
					$tpl_page .= '<form action="?action=editsections&do=addsection" method="post">
          <input type="hidden" name="token" value="' . $_SESSION['token'] . '" />
					<label for="name">'. _gettext('Название') .':</label><input type="text" name="name" /><br />
					<label for="abbreviation">'. _gettext('Аббревиатура') .':</label><input type="text" name="abbreviation" /><br />
					<label for="order">'. _gettext('Порядок') .':</label><input type="text" name="order" /><br />
					<label for="sect_hidded" class="checkbox" for="hidden"><input id="sect_hidded" type="checkbox" name="hidden">Скрытая</label><br />
					<input class="btn" type="submit" value="'. _gettext('Add') .'" >
					</form>';
				}
				$tpl_page .= '<br /><hr />';
			}
			if ($_GET['do'] == 'editsection' && $_GET['sectionid'] > 0) {
				if (isset($_POST['name'])) {
					if ($_POST['name'] != '' && $_POST['abbreviation'] != '') {
            $this->CheckToken($_POST['token']);
						$tc_db->Execute("UPDATE `" . KU_DBPREFIX . "sections` SET `name` = " . $tc_db->qstr($_POST['name']) . " , `abbreviation` = " . $tc_db->qstr($_POST['abbreviation']) . " , `order` = " . $tc_db->qstr($_POST['order']) . " , `hidden` = '" . (isset($_POST['hidden']) ? '1' : '0') . "' WHERE `id` = '" . $_GET['sectionid'] . "'");
						require_once KU_ROOTDIR . 'inc/classes/menu.class.php';
						$menu_class = new Menu();
						$menu_class->Generate();
						$tpl_page .= _gettext('Секция обновлена');
					}
				} else {
					$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "sections` WHERE `id` = " . $tc_db->qstr($_GET['sectionid']) . "");
					if (count($results) > 0) {
						foreach ($results as $line) {
							$tpl_page .= '<form action="?action=editsections&do=editsection&sectionid='. $_GET['sectionid'] . '" method="post">
              <input type="hidden" name="token" value="' . $_SESSION['token'] . '" />
							<input type="hidden" name="id" value="'. $_GET['sectionid'] . '" />

							<label for="name">'. _gettext('Название') .':</label>
							<input type="text" name="name" value="'. $line['name'] . '" />
							<br />

							<label for="abbreviation">Аббревиатура?</label>
							<input type="text" name="abbreviation" value="'. $line['abbreviation'] . '" />
							<br />

							<label for="order">'. _gettext('Порядок') .':</label>
							<input type="text" name="order" value="'. $line['order'] . '" />
							<br />

							<label class="checkbox" for="hidden_sect"><input id="hidden_sect" type="checkbox" name="hidden" '. ($line['hidden'] == 0 ? '' : 'checked') . ' />Скрытая</label>
							<br />

							<input class="btn" type="submit" value="Обновить" />

							</form>';
						}
					} else {
						$tpl_page .= _gettext('Секции с таким id нету');
					}
				}
				$tpl_page .= '<br /><hr />';
			}
			if ($_GET['do'] == 'deletesection' && isset($_GET['sectionid'])) {
				if ($_GET['sectionid'] > 0) {
					$tc_db->Execute("DELETE FROM `" . KU_DBPREFIX . "sections` WHERE `id` = " . $tc_db->qstr($_GET['sectionid']) . "");
					require_once KU_ROOTDIR . 'inc/classes/menu.class.php';
					$menu_class = new Menu();
					$menu_class->Generate();
					$tpl_page .= _gettext('Секция удалена') . '<br /><hr />';
				}
			}
		}
		$tpl_page .= '<a href="?action=editsections&do=addsection">'. _gettext('Add section') .'</a><br /><br />';
		$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "sections` ORDER BY `order` ASC");
		if (count($results) > 0) {
			$tpl_page .= '<table class="table table-striped table-bordered table-hover table-condensed" border="1" width="100%"><tr><th>id</th><th>Порядок</th><th>Аббревиатура</th><th>Название</th><th>'. _gettext('Edit/Delete') .'</th></tr>';
			foreach ($results as $line) {
				$tpl_page .= '<tr><td>'. $line['id'] . '</td><td>'. $line['order'] . '</td><td>'. $line['abbreviation'] . '</td><td>'. $line['name'] . '</td><td>[<a href="?action=editsections&do=editsection&sectionid='. $line['id'] . '">'. _gettext('Edit') .'</a>] [<a href="?action=editsections&do=deletesection&sectionid='. $line['id'] . '">'. _gettext('Delete') .'</a>]</td></tr>';
			}
			$tpl_page .= '</table>';
		} else {
			$tpl_page .= _gettext('Секции отсутствуют');
		}
	}


	/*
	* +------------------------------------------------------------------------------+
	* Boards Pages
	* +------------------------------------------------------------------------------+
	*/

	function boardopts() {
		global $tc_db, $tpl_page;
		$this->AdministratorsOnly();

		$tpl_page .= '<h2>'. _gettext('Board options') . '</h2><br />';
		if (isset($_GET['updateboard']) && isset($_POST['order']) && isset($_POST['maxpages']) && isset($_POST['maxage']) && isset($_POST['messagelength'])) {
      $this->CheckToken($_POST['token']);
			if (!$this->CurrentUserIsModeratorOfBoard($_GET['updateboard'], $_SESSION['manageusername'])) {
				exitWithErrorPage(_gettext('Ты не модерируешь эту доску'));
			}
			$boardid = $tc_db->GetOne("SELECT HIGH_PRIORITY `id` FROM `" . KU_DBPREFIX . "boards` WHERE `name` = " . $tc_db->qstr($_GET['updateboard']) . " LIMIT 1");
			if ($boardid != '') {
				if ($_POST['order'] >= 0 && $_POST['maxpages'] >= 0 && $_POST['markpage'] >= 0 && $_POST['maxage'] >= 0 && $_POST['messagelength'] >= 0 && ($_POST['defaultstyle'] == '' || in_array($_POST['defaultstyle'], explode(':', KU_STYLES)) || in_array($_POST['defaultstyle'], explode(':', KU_TXTSTYLES)))) {
					$filetypes = array();
					$ratings = array();
					while (list($postkey, $postvalue) = each($_POST)) {
						if (substr($postkey, 0, 9) == 'filetype_') {
							$filetypes[] = substr($postkey, 9);
						}
					    if (substr($postkey, 0, 7) == 'rating_') {
							$ratings[] = substr($postkey, 7);
						}
					}
					$updateboard_enablecatalog = isset($_POST['enablecatalog']) ? '1' : '0';
					$updateboard_enablenofile = isset($_POST['enablenofile']) ? '1' : '0';
					$updateboard_redirecttothread = isset($_POST['redirecttothread']) ? '1' : '0';
					$updateboard_enablereporting = isset($_POST['enablereporting']) ? '1' : '0';
					$updateboard_enablecaptcha = isset($_POST['enablecaptcha']) ? '1' : '0';
					$updateboard_forcedanon = isset($_POST['forcedanon']) ? '1' : '0';
					$updateboard_trial = isset($_POST['trial']) ? '1' : '0';
					$updateboard_popular = isset($_POST['popular']) ? '1' : '0';
					$updateboard_enablearchiving = isset($_POST['enablearchiving']) ? '1' : '0';
					$updateboard_showid = isset($_POST['showid']) ? '1' : '0';
					$updateboard_compactlist = isset($_POST['compactlist']) ? '1' : '0';
					$updateboard_locked = isset($_POST['locked']) ? '1' : '0';
					$updateboard_deny_tor = isset($_POST['deny_tor']) ? '1' : '0';

					if (($_POST['type'] == '0' || $_POST['type'] == '1' || $_POST['type'] == '2' || $_POST['type'] == '3') && ($_POST['uploadtype'] == '0' || $_POST['uploadtype'] == '1' || $_POST['uploadtype'] == '2')) {
						if (!($_POST['uploadtype'] != '0' && $_POST['type'] == '3')) {
							if(count($_POST['allowedembeds']) > 0) {
								$updateboard_allowedembeds = '';

								$results = $tc_db->GetAll("SELECT `filetype` FROM `" . KU_DBPREFIX . "embeds`");
								foreach ($results as $line) {
									if(in_array($line['filetype'], $_POST['allowedembeds'])) {
										$updateboard_allowedembeds .= $line['filetype'].',';
									}
								}
								if ($updateboard_allowedembeds != '') {
									$updateboard_allowedembeds = substr($updateboard_allowedembeds, 0, -1);
								}
							}
							$tc_db->Execute("UPDATE `" . KU_DBPREFIX . "boards` SET `type` = " . $tc_db->qstr($_POST['type']) . " , `uploadtype` = " . $tc_db->qstr($_POST['uploadtype']) . " , `order` = " . $tc_db->qstr(intval($_POST['order'])) . " , `section` = " . $tc_db->qstr(intval($_POST['section'])) . " , `desc` = " . $tc_db->qstr($_POST['desc']) . " , `locale` = " . $tc_db->qstr($_POST['locale']) . " , `showid` = '" . $updateboard_showid . "' , `compactlist` = '" . $updateboard_compactlist . "' , `deny_tor` = '".$updateboard_deny_tor."' , `locked` = '" . $updateboard_locked . "' , `maximagesize` = " . $tc_db->qstr($_POST['maximagesize']) . " , `messagelength` = " . $tc_db->qstr($_POST['messagelength']) . " , `maxpages` = " . $tc_db->qstr($_POST['maxpages']) . " , `maxage` = " . $tc_db->qstr($_POST['maxage']) . " , `markpage` = " . $tc_db->qstr($_POST['markpage']) . " , `maxreplies` = " . $tc_db->qstr($_POST['maxreplies']) . " , `image` = " . $tc_db->qstr($_POST['image']) . " , `includeheader` = " . $tc_db->qstr($_POST['includeheader']) . " , `redirecttothread` = '" . $updateboard_redirecttothread . "' , `anonymous` = " . $tc_db->qstr($_POST['anonymous']) . " , `forcedanon` = '" . $updateboard_forcedanon . "' , `embeds_allowed` = " . $tc_db->qstr($updateboard_allowedembeds) . " , `trial` = '" . $updateboard_trial . "' , `popular` = '" . $updateboard_popular . "' , `defaultstyle` = " . $tc_db->qstr($_POST['defaultstyle']) . " , `enablereporting` = '" . $updateboard_enablereporting . "', `enablecaptcha` = '" . $updateboard_enablecaptcha . "' , `enablenofile` = '" . $updateboard_enablenofile . "' , `enablearchiving` = '" . $updateboard_enablearchiving . "', `enablecatalog` = '" . $updateboard_enablecatalog . "' , `loadbalanceurl` = " . $tc_db->qstr($_POST['loadbalanceurl']) . " , `loadbalancepassword` = " . $tc_db->qstr($_POST['loadbalancepassword']) . " WHERE `name` = " . $tc_db->qstr($_GET['updateboard']) . "");
							$tc_db->Execute("DELETE FROM `" . KU_DBPREFIX . "board_filetypes` WHERE `boardid` = '" . $boardid . "'");
							foreach ($filetypes as $filetype) {
								$tc_db->Execute("INSERT INTO `" . KU_DBPREFIX . "board_filetypes` ( `boardid`, `typeid` ) VALUES ( '" . $boardid . "', " . $tc_db->qstr($filetype) . " )");
							}
      						$tc_db->Execute("DELETE FROM `" . KU_DBPREFIX . "board_ratings` WHERE `boardid` = '" . $boardid . "'");
							foreach ($ratings as $rating) {
								$tc_db->Execute("INSERT INTO `" . KU_DBPREFIX . "board_ratings` ( `boardid`, `ratingid` ) VALUES ( '" . $boardid . "', " . $tc_db->qstr($rating) . " )");
							}
							require_once KU_ROOTDIR . 'inc/classes/menu.class.php';
							$menu_class = new Menu();
							$menu_class->Generate();
							if (isset($_POST['submit_regenerate'])) {
								$board_class = new Board($_GET['updateboard']);
								$board_class->RegenerateAll();
							}
							$tpl_page .= _gettext('Настройки обновлены');
							management_addlogentry(_gettext('Настройки доски обновлены') . " - /" . $_GET['updateboard'] . "/", 4);
						} else {
							$tpl_page .= _gettext('Sorry, embed may only be enabled on normal imageboards.');
						}
					} else {
						$tpl_page .= _gettext('Ошибка...');
					}
				} else {
					$tpl_page .= _gettext('Данные должны быть введены корректно');
				}
			} else {
				$tpl_page .= _gettext('Не могу найти доску') . ' <strong>'. $_GET['updateboard'] . '</strong>.';
			}
		} elseif (isset($_POST['board'])) {
			if (!$this->CurrentUserIsModeratorOfBoard($_POST['board'], $_SESSION['manageusername'])) {
				exitWithErrorPage(_gettext('Ты не модерируешь эту доску'));
			}
			$resultsboard = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "boards` WHERE `name` = " . $tc_db->qstr($_POST['board']) . "");
			if (count($resultsboard) > 0) {
				foreach ($resultsboard as $lineboard) {
					$tpl_page .= '<div class="container">
					<form action="?action=boardopts&updateboard='.urlencode($_POST['board']).'" method="post">
          <input type="hidden" name="token" value="' . $_SESSION['token'] . '" />';
					/* Directory */
					$tpl_page .= '<label for="board">'. _gettext('Directory') .':</label>
					<input type="text" name="board" value="'.$_POST['board'].'" disabled />';

					/* Description */
					$tpl_page .= '<label for="desc">'. _gettext('Description') .':</label>
					<input type="text" name="desc" value="'.$lineboard['desc'].'" />';

					/* Locale */
					$tpl_page .= '<label for="locale">'. _gettext('Локаль') .':</label>
					<input type="text" name="locale" value="'.$lineboard['locale'].'" disabled />';

					/* Board type */
					$tpl_page .= '<label for="type">'. _gettext('Board type') .':</label>
					<select name="type">
					<option value="0"';
					if ($lineboard['type'] == '0') { $tpl_page .= ' selected="selected"'; }
					$tpl_page .= '>'. _gettext('Normal imageboard') .'</option>
					</select><br />';

					/* Upload type */
					$tpl_page .= '<label for="uploadtype">'. _gettext('Upload type') .':</label>
					<select name="uploadtype">
					<option value="0"';
					if ($lineboard['uploadtype'] == '0') {
						$tpl_page .= ' selected="selected"';
					}
					$tpl_page .= '>'. _gettext('No embedding') .'</option>
					</select>';

					/* Order */
					$tpl_page .= '<label for="order">'. _gettext('Order') .':</label>
					<input type="text" name="order" value="'.$lineboard['order'].'" />';

					/* Section */
					$tpl_page .= '<label for="section">'. _gettext('Section') .':</label>' .
					$this->MakeSectionListDropdown('section', $lineboard['section']);

					/* Load balancer URL */
					$tpl_page .= '<label for="loadbalanceurl">'. _gettext('Load balance URL') .':</label>
					<input type="text" name="loadbalanceurl" value="'.$lineboard['loadbalanceurl'].'" disabled />';

					/* Load balancer password */
					$tpl_page .= '<label for="loadbalancepassword">'. _gettext('Load balance password') .':</label>
					<input type="text" name="loadbalancepassword" value="'.$lineboard['loadbalancepassword'].'" disabled />';

					/* Allowed filetypes */
					$tpl_page .= '<label>'. _gettext('Allowed filetypes') .':</label>';
					$filetypes = $tc_db->GetAll("SELECT HIGH_PRIORITY `id`, `filetype` FROM `" . KU_DBPREFIX . "filetypes` ORDER BY `filetype` ASC");
					foreach ($filetypes as $filetype) {
						$filetype_isenabled = $tc_db->GetOne("SELECT HIGH_PRIORITY COUNT(*) FROM `" . KU_DBPREFIX . "board_filetypes` WHERE `boardid` = '" . $lineboard['id'] . "' AND `typeid` = '" . $filetype['id'] . "' LIMIT 1");
						if ($filetype_isenabled == 1) {
							$tpl_page .= '<label class="checkbox" for="filetype_' .$filetype['id']. '"> <input type="checkbox" id="filetype_' .$filetype['id']. '" name="filetype_' .$filetype['id']. '" checked>'. strtoupper($filetype['filetype']) . '</label>';
						} else {
							$tpl_page .= '<label class="checkbox" for="filetype_' .$filetype['id']. '"> <input type="checkbox" id="filetype_' .$filetype['id']. '" name="filetype_' .$filetype['id']. '">'. strtoupper($filetype['filetype']) . '</label>';
						}
					}
					
					/* Allowed ratings*/
					$tpl_page .= '<label>'. _gettext('Allowed ratings') .':</label>';
					$ratings = $tc_db->GetAll("SELECT HIGH_PRIORITY `id`, `name` FROM `" . KU_DBPREFIX . "ratings` ORDER BY `name` ASC");
					foreach ($ratings as $rating) {
						$rating_isenabled = $tc_db->GetOne("SELECT HIGH_PRIORITY COUNT(*) FROM `" . KU_DBPREFIX . "board_ratings` WHERE `boardid` = '" . $lineboard['id'] . "' AND `ratingid` = '" . $rating['id'] . "' LIMIT 1");
						if ($rating_isenabled == 1) {
							$tpl_page .= '<label for="rating_'. $rating['id'] . '"> <input type="checkbox" id="rating_'. $rating['id'] . '" name="rating_'. $rating['id'] . '" checked>'. $rating['name'] . '</label>';
						} else {
							$tpl_page .= '<label for="rating_'. $rating['id'] . '"> <input type="checkbox" id="rating_'. $rating['id'] . '" name="rating_'. $rating['id'] . '" >'. $rating['name'] . '</label>';
						}
 					}
					
					/* Allowed embeds */
					$tpl_page .= '<label>'. _gettext('Вложения') .':</label>';
					$embeds = $tc_db->GetAll("SELECT HIGH_PRIORITY `id`, `filetype`, `name` FROM `" . KU_DBPREFIX . "embeds` ORDER BY `filetype` ASC");
					foreach ($embeds as $embed) {
						if (in_array($embed['filetype'], explode(',', $lineboard['embeds_allowed']))) {
							$tpl_page .= '<label for="'. $embed['filetype'] . '"> <input type="checkbox" name="allowedembeds[]" id="'. $embed['filetype'] . '" value="'. $embed['filetype'] . '" checked disabled>'. $embed['name'] . '</label>';
						} else {
							$tpl_page .= '<label for="'. $embed['filetype'] . '"> <input type="checkbox" name="allowedembeds[]" id="'. $embed['filetype'] . '" value="'. $embed['filetype'] . '" disabled>'. $embed['name'] . '</label>';
						}
					}

					/* Maximum image size */
					$tpl_page .= '<br><label for="maximagesize">'. _gettext('Maximum image size') .':</label>
					<input type="text" name="maximagesize" value="'.$lineboard['maximagesize'].'" />';

					/* Maximum message length */
					$tpl_page .= '<label for="messagelength">'. _gettext('Maximum message length') .':</label>
					<input type="text" name="messagelength" value="'.$lineboard['messagelength'].'" />';

					/* Maximum board pages */
					$tpl_page .= '<label for="maxpages">'. _gettext('Maximum board pages') .':</label>
					<input type="text" name="maxpages" value="'.$lineboard['maxpages'].'" />';

					/* Maximum thread age */
					$tpl_page .= '<label for="maxage">'. _gettext('Maximum thread age (Hours)') .':</label>
					<input type="text" name="maxage" value="'.$lineboard['maxage'].'">';

					/* Mark page */
					$tpl_page .= '<label for="markpage">'. _gettext('Mark page') .':</label>
					<input type="text" name="markpage" value="'.$lineboard['markpage'].'" disabled>';

					/* Maximum thread replies */
					$tpl_page .= '<label for="maxreplies">'. _gettext('Maximum thread replies') .':</label>
					<input type="text" name="maxreplies" value="'.$lineboard['maxreplies'].'" />';

					/* Header image */
					$tpl_page .= '<label for="image">'. _gettext('Header image') .':</label>
					<input type="text" name="image" value="'.$lineboard['image'].'" disabled>';

					/* Include header */
					$tpl_page .= '<label for="includeheader">'. _gettext('Include header') .':</label>
					<textarea name="includeheader" style="width: 880px; height: 150px;">'.htmlspecialchars($lineboard['includeheader']).'</textarea>	';

					/* Anonymous */
					$tpl_page .= '<label for="anonymous">'. _gettext('Стандартное имя') .':</label>
					<input type="text" name="anonymous" value="'. $lineboard['anonymous'] . '" />';

					/* Locked */
					if ($lineboard['locked'] == '1') {
						$tpl_page .= '<label for="locked" class="checkbox" ><input id="locked" type="checkbox" name="locked" checked>Закрыта</label>';
					} else {
						$tpl_page .= '<label for="locked" class="checkbox" ><input id="locked" type="checkbox" name="locked">Закрыта</label>';
					}

					/* Tor */
					if ($lineboard['deny_tor'] == '1') {
						$tpl_page .= '<label for="deny_tor" class="checkbox" ><input id="deny_tor" type="checkbox" name="deny_tor" checked>Тор выкл</label>';
					} else {
						$tpl_page .= '<label for="deny_tor" class="checkbox" ><input id="deny_tor" type="checkbox" name="deny_tor">Тор выкл</label>';
					}

					/* Show ID */
					if ($lineboard['showid'] == '1') {
						$tpl_page .= '<label for="showid" class="checkbox" ><input id="showid" type="checkbox" name="showid" checked>Показывать id</label>';
					} else {
						$tpl_page .= '<label for="showid" class="checkbox" ><input id="showid" type="checkbox" name="showid">Показывать id</label>';
					}

					/* Compact list */
					if ($lineboard['compactlist'] == '1') {
						$tpl_page .= '<label for="compactlist" class="checkbox" ><input id="compactlist" type="checkbox" name="compactlist" disabled checked>Компакт лист</label>';
					} else {
						$tpl_page .= '<label for="compactlist" class="checkbox" ><input id="compactlist" type="checkbox" name="compactlist" disabled>Компакт лист</label>';
					}

					/* Enable reporting */
					if ($lineboard['enablereporting'] == '1') {
						$tpl_page .= '<label for="enablereporting" class="checkbox" ><input id="enablereporting" type="checkbox" name="enablereporting" disabled checked>Репорты</label>';
					} else {
						$tpl_page .= '<label for="enablereporting" class="checkbox" ><input id="enablereporting" type="checkbox" name="enablereporting" disabled>Репорты</label>';
					}

					/* Enable captcha */
					if ($lineboard['enablecaptcha'] == '1') {
						$tpl_page .= '<label for="enablecaptcha" class="checkbox" ><input id="enablecaptcha" type="checkbox" name="enablecaptcha" checked>Капча</label>';
					} else {
						$tpl_page .= '<label for="enablecaptcha" class="checkbox" ><input id="enablecaptcha" type="checkbox" name="enablecaptcha">Капча</label>';
					}
					
					/* Enable archiving */
					if ($lineboard['enablearchiving'] == '1') {
						$tpl_page .= '<label for="enablearchiving" class="checkbox" ><input id="enablearchiving" type="checkbox" name="enablearchiving" disabled checked>Архивация</label>';
					} else {
						$tpl_page .= '<label for="enablearchiving" class="checkbox" ><input id="enablearchiving" type="checkbox" name="enablearchiving" disabled>Архивация</label>';
					}
				
					/* Enable catalog */
					if ($lineboard['enablecatalog'] == '1') {
						$tpl_page .= '<label for="enablecatalog" class="checkbox" ><input id="enablecatalog" type="checkbox" name="enablecatalog" disabled checked>Каталог</label>';
					} else {
						$tpl_page .= '<label for="enablecatalog" class="checkbox" ><input id="enablecatalog" type="checkbox" name="enablecatalog" disabled>Каталог</label>';
					}

					/* Enable "no file" posting */
					if ($lineboard['enablenofile'] == '1') {
						$tpl_page .= '<label for="enablenofile" class="checkbox" ><input id="enablenofile" type="checkbox" name="enablenofile" disabled checked>Без файлов</label>';
					} else {
						$tpl_page .= '<label for="enablenofile" class="checkbox" ><input id="enablenofile" type="checkbox" name="enablenofile" disabled>Без файлов</label>';
					}

					/* Redirect to thread */
					if ($lineboard['redirecttothread'] == '1') {
						$tpl_page .= '<label for="redirecttothread" class="checkbox" ><input id="redirecttothread" type="checkbox" name="redirecttothread" checked>Переадресация в тред</label>';
					} else {
						$tpl_page .= '<label for="redirecttothread" class="checkbox" ><input id="redirecttothread" type="checkbox" name="redirecttothread">Переадресация в тред</label>';
					}

					/* Forced anonymous */
					if ($lineboard['forcedanon'] == '1') {
						$tpl_page .= '<label for="forcedanon" class="checkbox" ><input id="forcedanon" type="checkbox" name="forcedanon" checked>Форсированная анонимность</label>';
					} else {
						$tpl_page .= '<label for="forcedanon" class="checkbox" ><input id="forcedanon" type="checkbox" name="forcedanon">Форсированная анонимность</label>';
					}

					/* Trial */
					if ($lineboard['trial'] == '1') {
						$tpl_page .= '<label for="trial" class="checkbox" ><input id="trial" type="checkbox" name="trial" disabled checked>Новая доска</label>';
					} else {
						$tpl_page .= '<label for="trial" class="checkbox" ><input id="trial" type="checkbox" name="trial" disabled>Новая доска</label>';
					}

					/* Popular */
					if ($lineboard['popular'] == '1') {
						$tpl_page .= '<label for="popular" class="checkbox" ><input id="popular" type="checkbox" name="popular" disabled checked>Популярная</label>';
					} else {
						$tpl_page .= '<label for="popular" class="checkbox" ><input id="popular" type="checkbox" name="popular" disabled>Популярная</label>';
					}

					/* Default style */
					$tpl_page .= '<label for="defaultstyle">'. _gettext('Default style') .':</label>
					<select name="defaultstyle">

					<option value=""';
					$tpl_page .= ($lineboard['defaultstyle'] == '') ? ' selected="selected"' : '';
					$tpl_page .= '>'. _gettext('Use Default') .'</option>';

					$styles = explode(':', KU_STYLES);
					foreach ($styles as $stylesheet) {
						$tpl_page .= '<option value="'. $stylesheet . '"';
						$tpl_page .= ($lineboard['defaultstyle'] == $stylesheet) ? ' selected="selected"' : '';
						$tpl_page .= '>'. ucfirst($stylesheet) . '</option>';
					}

					$stylestxt = explode(':', KU_TXTSTYLES);
					foreach ($stylestxt as $stylesheet) {
						$tpl_page .= '<option value="'. $stylesheet . '"';
						$tpl_page .= ($lineboard['defaultstyle'] == $stylesheet) ? ' selected="selected"' : '';
						$tpl_page .= '>[TXT] '. ucfirst($stylesheet) . '</option>';
					}

					$tpl_page .= '</select>';

					/* Submit form */
					$tpl_page .= '<br />
					<input class="btn" type="submit" name="submit_noregenerate" value="'. _gettext('Обновить настройки') .'" />  <input class="btn" type="submit" name="submit_regenerate" value="'. _gettext('Обновить настройки и регенерировать HTML') .'" disabled>

					</form>
					</div><br />';
				}
			} else {
				$tpl_page .= _gettext('Unable to locate a board named') . ' <strong>'. $_POST['board'] . '</strong>.';
			}
		} else {
			$tpl_page .= '<form action="?action=boardopts" method="post">
			<label for="board">'. _gettext('Board') .':</label>' .
			$this->MakeBoardListDropdown('board', $this->BoardList($_SESSION['manageusername'])) .
			'<br><input class="btn" type="submit" value="'. _gettext('Настроить') .'" />
			</form>';
		}
	}

	function unstickypost() {
		global $tc_db, $tpl_page, $board_class;
		$this->ModeratorsOnly();
		$this->CheckToken($_GET['token']);

		$tpl_page .= '<h2>Прикрепленные треды</h2><br />';
		if (isset($_GET['postid']) && isset($_GET['board'])) {
			if ($_GET['postid'] > 0 && $_GET['board'] != '') {
				$results = $tc_db->GetAll("SELECT HIGH_PRIORITY `id`, `name` FROM `" . KU_DBPREFIX . "boards` WHERE `name` = " . $tc_db->qstr($_GET['board']) . "");
				if (count($results) > 0) {
					if (!$this->CurrentUserIsModeratorOfBoard($_GET['board'], $_SESSION['manageusername'])) {
						exitWithErrorPage(_gettext('Ты не модерируешь эту доску'));
					}
					foreach ($results as $line) {
						$sticky_board_name = $line['name'];
						$sticky_board_id = $line['id'];
					}
					$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "posts` WHERE `boardid` = " . $sticky_board_id ." AND `IS_DELETED` = '0' AND `parentid` = '0' AND `id` = " . $tc_db->qstr($_GET['postid']) . "");
					if (count($results) > 0) {
						$tc_db->Execute("UPDATE `" . KU_DBPREFIX . "posts` SET `stickied` = '0' WHERE `boardid` = " . $sticky_board_id ." AND `parentid` = '0' AND `id` = " . $tc_db->qstr($_GET['postid']) . "");
						$board_class = new Board($sticky_board_name);
						$board_class->RegenerateAll();
						unset($board_class);
						$tpl_page .= _gettext('Тред откреплен');
						management_addlogentry(_gettext('Тред откреплен') . ' #' . intval($_GET['postid']) . ' - /' . $sticky_board_name . '/', 5);
					} else {
						$tpl_page .= _gettext('Такого треда не существует');
					}
				} else {
					$tpl_page .= _gettext('Неправильный раздел');
				}
				$tpl_page .= '<hr />';
			}
		}
		$tpl_page .= $this->stickyforms();
	}

	function stickypost() {
		global $tc_db, $tpl_page, $board_class;
		$this->ModeratorsOnly();

		$tpl_page .= '<h2>Прикрепленные треды</h2><br />';
		if (isset($_GET['postid']) && isset($_GET['board'])) {
		$this->CheckToken($_GET['token']);
			if ($_GET['postid'] > 0 && $_GET['board'] != '') {
				$results = $tc_db->GetAll("SELECT HIGH_PRIORITY `id`, `name` FROM `" . KU_DBPREFIX . "boards` WHERE `name` = " . $tc_db->qstr($_GET['board']) . "");
				if (count($results) > 0) {
					if (!$this->CurrentUserIsModeratorOfBoard($_GET['board'], $_SESSION['manageusername'])) {
						exitWithErrorPage(_gettext('Ты не модерируешь эту доску'));
					}
					foreach ($results as $line) {
						$sticky_board_name = $line['name'];
						$sticky_board_id = $line['id'];
					}
					$result = $tc_db->GetOne("SELECT HIGH_PRIORITY COUNT(*) FROM `" . KU_DBPREFIX . "posts` WHERE `boardid` = " . $sticky_board_id . " AND `IS_DELETED` = '0' AND `parentid` = '0' AND `id` = " . $tc_db->qstr($_GET['postid']) . "");
					if ($result > 0) {
						$tc_db->Execute("UPDATE `" . KU_DBPREFIX . "posts` SET `stickied` = '1' WHERE `boardid` = " . $sticky_board_id . " AND `parentid` = '0' AND `id` = " . $tc_db->qstr($_GET['postid']) . "");
						$board_class = new Board($sticky_board_name);
						$board_class->RegenerateAll();
						unset($board_class);
						$tpl_page .= _gettext('Тред прикреплен');
						management_addlogentry(_gettext('Тред прикреплен') . ' #' . intval($_GET['postid']) . ' - /' . $sticky_board_name . '/', 5);
					} else {
						$tpl_page .= _gettext('Такого треда не существует');
					}
				} else {
					$tpl_page .= _gettext('Неправильный раздел');
				}
				$tpl_page .= '<hr />';
			}
		}
		$tpl_page .= $this->stickyforms();
	}

	/* Create forms for stickying a post */
	function stickyforms() {
		global $tc_db;

		$output = '<table class="table table-striped table-bordered table-hover table-condensed" width="100%" border="0">
		<tr><td width="50%"><h2>Прикрепить</h2></td><td width="50%"><h2>открепить</h2></td></tr>
		<tr><td style="vertical-align:top;"><br />

		<form action="manage_page.php" method="get"><input type="hidden" name="action" value="stickypost" />
		<input type="hidden" name="token" value="' . $_SESSION['token'] . '" />
		<label for="board">'. _gettext('Board') .':</label>' .
		$this->MakeBoardListDropdown('board', $this->BoardList($_SESSION['manageusername'])) .
		'<br />

		<label for="postid">'. _gettext('Тред') .':</label>
		<input type="text" name="postid" /><br />

		<label for="submit">&nbsp;</label>
		<input class="btn" name="submit" type="submit" value="Прикрепить" />
		</form>
		</td><td>';
		$results_boards = $tc_db->GetAll("SELECT HIGH_PRIORITY `name`, `id` FROM `" . KU_DBPREFIX . "boards` ORDER BY `name` ASC");
		foreach ($results_boards as $line_board) {
			$output .= '<h2>/'. $line_board['name'] . '/</h2>';
			$results = $tc_db->GetAll("SELECT HIGH_PRIORITY `id` FROM `" . KU_DBPREFIX . "posts` WHERE `boardid` = " . $line_board['id'] . " AND `IS_DELETED` = '0' AND `parentid` = '0' AND `stickied` = '1'");
			if (count($results) > 0) {
				foreach ($results as $line) {
					$output .= '<a href="?action=unstickypost&board='. $line_board['name'] . '&postid='. $line['id'] . '&token='.$_SESSION['token'].'">#'. $line['id'] . '</a><br />';
				}
			} else {
				$output .= 'No stickied threads.<br />';
			}
		}
		$output .= '</td></tr></table>';

		return $output;
	}

	function official() {
		global $tc_db, $tpl_page, $board_class;
		$this->ModeratorsOnly();

		$tpl_page .= '<h2>Автосоздание тредов</h2><br />';
		if (isset($_GET['do'])) {
			if ($_GET['do'] == 'add') {
				if ($_POST['threadid'] != '' && $_POST['boardid'] != '' && $_POST['subject'] != '' && $_POST['raw_message'] != '' && $_POST['op_dir'] != '' && $_POST['end_dir'] != '') {
         				$this->CheckToken($_POST['token']);
					$tc_db->Execute("INSERT HIGH_PRIORITY INTO `" . KU_DBPREFIX . "official` ( `boardid`, `counter`, `current_thread`, `subject`, `raw_message`, `op_dir`, `end_dir`, `premod_dir` ) VALUES ( " . 
					$tc_db->qstr($_POST['boardid']) . " , " . 
					$tc_db->qstr($_POST['counter']) . " , " . 
					$tc_db->qstr($_POST['threadid']) . " , " . 
					$tc_db->qstr($_POST['subject']) . " , " . 
					$tc_db->qstr($_POST['raw_message']) . " , " . 
					$tc_db->qstr($_POST['op_dir']) . " , " . 
					$tc_db->qstr($_POST['end_dir']) . " , " . 
					$tc_db->qstr($_POST['premod_dir']) . 
					" )");
					$tpl_page .= 'goood';
				} else {
					$tpl_page .= '<form action="?action=official&do=add" method="post">
			          	<input type="hidden" name="token" value="' . $_SESSION['token'] . '" />
					<label for="boardid">Номер доски:</label>
					<input type="text" name="boardid"/><br />
	
					<label for="counter">Номер треда (в заголовке):</label>
					<input type="text" name="counter" value="0"/>
					<br />
	
					<label for="threadid">Номер первого поста:</label>
					<input type="text" name="threadid" />
					<br />
	
					<label for="subject">Тема:</label>
					<input type="text" name="subject" />
					<br />
	
					<label for="raw_message">ОП пост:</label>
					<br /><textarea style="width: 880px; height: 150px;" cols="80" rows="15" name="raw_message"></textarea>
					<br />
	
					<label for="op_dir">Директория оппиков:</label>
					<input type="text" name="op_dir" />
					<br />
	
					<label for="end_dir">Директория закрывающих картинок:</label>
					<input type="text" name="end_dir" />
					<br />
	
					<label for="premod_dir">Директория картинок в премоде:</label>
					<input type="text" name="premod_dir" />
					<br />
	
					<input class="btn" type="submit" value="'. _gettext('Add') .'" />
	
					</form>';
				}
			$tpl_page .= '<br /><hr />';
			}
			if ($_GET['do'] == 'edit' && $_GET['id'] > 0) {
				if (isset($_POST['boardid'])) {
					if ($_POST['threadid'] != '' && $_POST['boardid'] != '' && $_POST['subject'] != '' && $_POST['raw_message'] != '' && $_POST['op_dir'] != '' && $_POST['end_dir'] != '') {
           					$this->CheckToken($_POST['token']);
						$tc_db->Execute("UPDATE `" . KU_DBPREFIX . "official` SET `current_thread` = " . $tc_db->qstr($_POST['threadid']) . " , `boardid` = " . $tc_db->qstr($_POST['boardid']) . " , `subject` = " . $tc_db->qstr($_POST['subject']) . " , `raw_message` = " . $tc_db->qstr($_POST['raw_message']) . " , `op_dir` = " . $tc_db->qstr($_POST['op_dir']) . " , `end_dir` = ". $tc_db->qstr($_POST['end_dir']) ." , `premod_dir` = ". $tc_db->qstr($_POST['premod_dir']) . " , `counter` = ". $tc_db->qstr($_POST['counter']) ." WHERE `id` = " . $tc_db->qstr($_GET['id']));
						$tpl_page .= _gettext('Thread updated.');
					}
				} else {
					$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "official` WHERE `id` = " . $tc_db->qstr($_GET['id']));
					if (count($results) > 0) {
						foreach ($results as $line) {
							$tpl_page .= '<form action="?action=official&do=edit&id='. $_GET['id'] . '" method="post">
             						<input type="hidden" name="token" value="' . $_SESSION['token'] . '" />
							<label for="boardid">Номер доски:</label>
							<input type="text" name="boardid" value="'. $line['boardid'] .'"/>
							<br />
			
							<label for="counter">Номер треда (в заголовке):</label>
							<input type="text" name="counter" value="'. $line['counter'] .'"/>
							<br />
			
							<label for="threadid">Номер первого поста:</label>
							<input type="text" name="threadid" value="'. $line['current_thread'] .'" />
							<br />
			
							<label for="subject">Тема:</label>
							<input type="text" name="subject" value="'. $line['subject'].'" />
							<br />
			
							<label for="raw_message">ОП пост:</label>
							<br /><textarea style="width: 880px; height: 150px;" cols="80" rows="15" name="raw_message">'.$line['raw_message'].'</textarea>
							<br />
			
							<label for="op_dir">Директория оппиков:</label>
							<input type="text" name="op_dir" value="'. $line['op_dir'] .'" />
							<br />
			
							<label for="end_dir">Директория закрывающих картинок:</label>
							<input type="text" name="end_dir" value="'. $line['end_dir'].'" />
							<br />
			
							<label for="premod_dir">Директория картинок в премоде:</label>
							<input type="text" name="premod_dir" value="'. $line['premod_dir'] .'"/>
							<br />
							<input class="btn" type="submit" value="'. _gettext('Обновить') .'" />

							</form>';
;
						}
	
					}
				}
			$tpl_page .= '<br /><hr />';
			}
			if ($_GET['do'] == 'delete' && $_GET['id'] > 0) {
				$tc_db->Execute("DELETE FROM `" . KU_DBPREFIX . "official` WHERE `id` = " . $tc_db->qstr($_GET['id']) . "");
				$tpl_page .= _gettext('Тред удалён');
			$tpl_page .= '<br /><hr />';
			}
		}
		$tpl_page .= '<a class="btn" href="?action=official&do=add">Добавить тред</a><br /><br />';
		$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "official` ORDER BY `id` ASC");
		if (count($results) > 0) {
			$tpl_page .= '<table class="table table-striped table-bordered table-hover table-condensed" border="1" width="100%"><tr><th>'. _gettext('ID') .'</th><th>Доска</th><th>Тред</th><th>'. _gettext('Edit/Delete') .'</th></tr>';
			foreach ($results as $line) {
				$tpl_page .= '<tr><td>'. $line['id'] . '</td><td>'. $line['boardid'] . '</td><td>'. $line['subject'] . '</td><td>[<a href="?action=official&do=edit&id='. $line['id'] . '">'. _gettext('Edit') .'</a>] [<a href="?action=official&do=delete&id='. $line['id'] . '">'. _gettext('Delete') .'</a>]</td></tr>';
			}
			$tpl_page .= '</table>';
		} else {
			$tpl_page .= 'Тредов нет';
		}

		$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "official` ORDER BY `id` ASC");
		if (count($results) > 0) {
			if ($_GET['do'] == 'delfile' && $_GET['name'] != ''){
				foreach ($results as $line) {
					$files = glob($line['premod_dir'] . '/*.*');
					foreach ($files as $file) {
						if (strrchr($file, '/') == $_GET['name'])
							if (unlink ($file))
								$tpl_page .= "<br>удалён: $file<br>"; // fuck the situation where there is one file for >1 threads
					}
				}
			}

			if ($_GET['do'] == 'addfile' && $_GET['name'] != ''){
				foreach ($results as $line) {
					$files = glob($line['premod_dir'] . '/*.*');
					foreach ($files as $file) {
						if (strrchr($file, '/') == $_GET['name']) 
							if (rename ($file, $line['op_dir'] . '/' . strrchr($file, '/')))
								$tpl_page .= "<br>файл : $file <br>добавлен в оппики треда : " . $line['subject'] . "<br> доски : ". $line['boardid'] ."<br>";
					}
				}
			}

			$tpl_page .= '<br />ОПпики в премоде:<br />';
			$tpl_page .= '<table class="table table-striped table-bordered table-hover table-condensed" border="1" width="100%"><tr><th>Картинка</th><th>Доска</th><th>Тред</th><th>'. _gettext('Edit/Delete') .'</th></tr>';
			foreach ($results as $line) {
				$files = glob($line['premod_dir'] . '/*.*');
				foreach ($files as $file) {
					$tpl_page .= '<tr><td><a href="'. strstr($file, '/images/', false) .'"><img width=200 src="'. strstr($file, '/images/', false) .'"></a></td><td>'. $line['subject'] .'</td><td>'. $line['boardid'] .'</td><td>[<a href="?action=official&do=delfile&name='. strrchr($file, '/') .'">удалить</a>/<a href="?action=official&do=addfile&name='. strrchr($file, '/') .'">апрувнуть</a>]</td><br>';
				}
			}
			$tpl_page .= '</table>';
		}
	}

	function lockpost() {
		global $tc_db, $tpl_page, $board_class;
		$this->ModeratorsOnly();

		$tpl_page .= '<h2>Закрытие тредов</h2><br />';
		if (isset($_GET['postid']) && isset($_GET['board'])) {
			$this->CheckToken($_GET['token']);
			if ($_GET['postid'] > 0 && $_GET['board'] != '') {
				$results = $tc_db->GetAll("SELECT HIGH_PRIORITY `id`, `name` FROM `" . KU_DBPREFIX . "boards` WHERE `name` = " . $tc_db->qstr($_GET['board']) . "");
				if (count($results) > 0) {
					if (!$this->CurrentUserIsModeratorOfBoard($_GET['board'], $_SESSION['manageusername'])) {
						exitWithErrorPage(_gettext('Ты не модерируешь эту доску'));
					}
					foreach ($results as $line) {
						$lock_board_name = $line['name'];
						$lock_board_id = $line['id'];
					}
					$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "posts` WHERE `boardid` = " . $lock_board_id . " AND `IS_DELETED` = '0' AND `parentid` = '0' AND `id` = " . $tc_db->qstr($_GET['postid']) . "");
					if (count($results) > 0) {
						$tc_db->Execute("UPDATE `" . KU_DBPREFIX . "posts` SET `locked` = '1' WHERE `boardid` = " . $lock_board_id . " AND `parentid` = '0' AND `id` = " . $tc_db->qstr($_GET['postid']) . "");
						$board_class = new Board($lock_board_name);
						$board_class->RegenerateAll();
						unset($board_class);
						$tpl_page .= _gettext('Тред закрыт');
//						management_addlogentry(_gettext('Locked thread') . ' #'. intval($_GET['postid']) . ' - /'. intval($_GET['board']) . '/', 5);
						management_addlogentry(_gettext('Тред закрыт') . ' #'. intval($_GET['postid']) . ' - /'. $_GET['board'] . '/', 5);
					} else {
						$tpl_page .= _gettext('Неправильный id');
					}
				} else {
					$tpl_page .= _gettext('Неправильный раздел');
				}
				$tpl_page .= '<hr />';
			}
		}
		$tpl_page .= $this->lockforms();
	}

	function unlockpost() {
		global $tc_db, $tpl_page, $board_class;
		$this->CheckToken($_GET['token']);

		$tpl_page .= '<h2>Закрытие тредов</h2><br />';
		if ($_GET['postid'] > 0 && $_GET['board'] != '') {
			$results = $tc_db->GetAll("SELECT HIGH_PRIORITY `id`, `name` FROM `" . KU_DBPREFIX . "boards` WHERE `name` = " . $tc_db->qstr($_GET['board']) . "");
			if (count($results) > 0) {
				if (!$this->CurrentUserIsModeratorOfBoard($_GET['board'], $_SESSION['manageusername'])) {
					exitWithErrorPage(_gettext('Ты не модерируешь эту доску'));
				}
				foreach ($results as $line) {
					$lock_board_name = $line['name'];
					$lock_board_id = $line['id'];
				}
				$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "posts` WHERE `boardid` = " . $lock_board_id . " AND `IS_DELETED` = '0' AND `parentid` = '0' AND `id` = " . $tc_db->qstr($_GET['postid']) . "");
				if (count($results) > 0) {
					$tc_db->Execute("UPDATE `" . KU_DBPREFIX . "posts` SET `locked` = '0' WHERE `boardid` = " . $lock_board_id . " AND `parentid` = '0' AND `id` = " . $tc_db->qstr($_GET['postid']) . "");
					$board_class = new Board($lock_board_name);
					$board_class->RegenerateAll();
					unset($board_class);
					$tpl_page .= _gettext('Тред открыт');
					management_addlogentry(_gettext('Тред открыт') . ' #'. intval($_GET['postid']) . ' - /'. $_GET['board'] . '/', 5);
				} else {
					$tpl_page .= _gettext('Неправильный id');
				}
			} else {
				$tpl_page .= _gettext('Неправильный раздел');
			}
			$tpl_page .= '<hr />';
		}
		$tpl_page .= $this->lockforms();
	}

	function lockforms() {
		global $tc_db;

		$output = '<table class="table table-striped table-bordered table-hover table-condensed" width="100%" border="0">
		<tr><td width="50%"><h2>'. _gettext('Закрыть') . '</h2></td><td width="50%"><h2>'. _gettext('Открыть') . '</h2></td></tr>
		<tr>		<form action="manage_page.php" method="get"><input type="hidden" name="action" value="lockpost" />
		<label for="board">'. _gettext('Board') .':</label>' .
		$this->MakeBoardListDropdown('board', $this->BoardList($_SESSION['manageusername'])) .
		'<br />

		<label for="postid">'. _gettext('Тред') .':</label>
		<input type="text" name="postid" />
		<input type="hidden" name="token" value="' . $_SESSION['token'] . '" />
		<label for="submit"></label>
		<input class="btn" name="submit" type="submit" value="Закрыть" /><td>
		


		</form>
		</td><td>';
		$results_boards = $tc_db->GetAll("SELECT HIGH_PRIORITY `id`, `name` FROM `" . KU_DBPREFIX . "boards` ORDER BY `name` ASC");
		foreach ($results_boards as $line_board) {
			$output .= '<h2>/'. $line_board['name'] . '/</h2>';
			$results = $tc_db->GetAll("SELECT HIGH_PRIORITY `id` FROM `" . KU_DBPREFIX . "posts` WHERE `boardid` = " . $line_board['id'] . " AND `IS_DELETED` = '0' AND `parentid` = '0' AND `locked` = '1'");
			if (count($results) > 0) {
				foreach ($results as $line) {
					$output .= '<a href="?action=unlockpost&board='. $line_board['name'] . '&postid='. $line['id'] . '&token='.$_SESSION['token'].'">#'. $line['id'] . '</a><br />';
				}
			} else {
				$output .= 'Нет закрытых тредов<br />';
			}
		}
		$output .= '</td></tr></table>';

		return $output;
	}

	function ajaxdel() {
		global $tc_db;

		$postid = $_GET['id'];
		if (!$postid) die ('no post id');

		$board = $_GET['board'];
		if (!$board) die ('no board id');

		$boardid = board_name_to_id($board);
		if (!$boardid) die ('no such board');

		$undel = false;
		if (isset($_GET['undel']))
			$undel = true;

		$this->CheckAjaxToken($postid, $boardid);

		$parentid = $tc_db->GetOne('select parentid from posts where boardid = ' .$tc_db->qstr($boardid). ' and id = '.$tc_db->qstr($postid));
		$query = 'update posts set IS_DELETED = '.($undel ? '0' : '1').' where boardid = ' .$tc_db->qstr($boardid). ' and (id = ' .$tc_db->qstr($postid). ' or parentid = '. $tc_db->qstr($postid). ')';
		bdl_debug($query);
		$tc_db->Execute($query); 

		$num = $tc_db->Affected_Rows();
		if ($num > 0) {
			//clear cache
			$board_name = $board; 
			//$thread_replyto = intval($_POST['thread'];
			$thread_replyto = ($parentid == 0 ? $postid : $parentid);
			cache_del ('thread_lastposts_' . $board_name . '_' .$thread_replyto);
			cache_del ('replycount_' . $board_name . '_' .$thread_replyto);

			cache_del (KU_TEMPLATEDIR_2 . '/' . $board_name.'_res_'.$thread_replyto.'.html');
			cache_del (KU_TEMPLATEDIR_2 . '/' . $board_name.'_'.KU_FIRSTPAGE);

			for ($i = 0; $i < 500; $i++) 
				cache_del (KU_TEMPLATEDIR_2 . '/' . $board.'_'.$i);
								
			//$board_class = new Board($board);
			//$board_class->RegenerateAll();
			$msg = $undel ? 'Разудалила' : 'Удалила';
			if ($parentid == 0) {
				$msg .= ' тред (и все его посты)';
				management_addlogentry(( $undel ? 'Разудален' : 'Удален') .' тред: <a href="/'.$board.'/res/'.$postid.'.html">/'.$board.'/'.$postid.'</a>'.' ('. $num . ' ответов)' , 7);
			} else {
				$msg .= ' пост';
				management_addlogentry(( $undel ? 'Разудален' : 'Удален') .' пост: '.link_on_post($board, $postid).' - /'. $board . '/', 7);
			}
			die($msg);
		} else {
			die ('Что-то не вышло ничего... то ли не нашлось таких постов, то ли одно из двух');
		}
	}

	/* Delete a post, or multiple posts */
	function delposts($multidel=false) {
		global $tc_db, $tpl_page, $board_class;
    $isquickdel = false;
    if (isset($_POST['boarddir']) || isset($_GET['boarddir'])) {
		$this->CheckToken($_POST['token']);
      if (isset($_GET['boarddir'])) {
	  $this->CheckToken($_POST['token']);
				$isquickdel = true;
				$_POST['boarddir'] = $_GET['boarddir'];
				if (isset($_GET['delthreadid'])) {
					$_POST['delthreadid'] = $_GET['delthreadid'];
				}
				if (isset($_GET['delpostid'])) {
					$_POST['delpostid'] = $_GET['delpostid'];
				}
			}
			$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "boards` WHERE `name` = " . $tc_db->qstr($_POST['boarddir']) . "");
			if (count($results) > 0) {
				if (!$this->CurrentUserIsModeratorOfBoard($_POST['boarddir'], $_SESSION['manageusername'])) {
					exitWithErrorPage(_gettext('Ты не модерируешь эту доску'));
				}
				foreach ($results as $line) {
					$board_id = $line['id'];
					$board_dir = $line['name'];
				}
				if (isset($_GET['cp'])) {
					$cp = '&amp;cp=y&amp;instant=y';
				}
				if (isset($_POST['delthreadid'])) {
					if ($_POST['delthreadid'] > 0) {
						$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "posts` WHERE `boardid` = " . $board_id . " AND `IS_DELETED` = '0' AND `id` = " . $tc_db->qstr($_POST['delthreadid']) . " AND `parentid` = '0'");
						if (count($results) > 0) {
							if (isset($_POST['fileonly'])) {
								foreach ($results as $line) {
									if (!empty($line['file'])) {
										$del = rename(KU_ROOTDIR . $_POST['boarddir'] . '/src/'. $line['file'] . '.'. $line['file_type'], KU_ROOTDIR . $_POST['boarddir'] . '/src/__'. $line['file'] . '.'. $line['file_type']);
										if ($del) {
											@rename(KU_ROOTDIR . $_POST['boarddir'] . '/thumb/'. $line['file'] . 's.'. $line['file_type'], KU_ROOTDIR . $_POST['boarddir'] . '/thumb/__'. $line['file'] . 's.'. $line['file_type']);
											@rename(KU_ROOTDIR . $_POST['boarddir'] . '/thumb/'. $line['file'] . 'c.'. $line['file_type'], KU_ROOTDIR . $_POST['boarddir'] . '/thumb__/'. $line['file'] . 'c.'. $line['file_type']);
											$tc_db->Execute("UPDATE `".KU_DBPREFIX."posts` SET `file` = 'removed', `file_md5` = '' WHERE `boardid` = " . $board_id . " AND `id` = ".$_POST['delthreadid']." LIMIT 1");
											$tpl_page .= '<hr />Файл удален<hr />';
										} else {
											$tpl_page .= '<hr />Этот файл уже удалён<hr />';
										}
									} else {
										$tpl_page .= '<hr />У этого треда нет такого файла<hr />';
									}
								}
							} else {
								foreach ($results as $line) {
									$delthread_id = $line['id'];
								}
								$post_class = new Post($delthread_id, $board_dir, $board_id);
								if (isset($_POST['archive'])) {
									$numposts_deleted = $post_class->Delete(true);
								} else {
									$numposts_deleted = $post_class->Delete();
								}
								$board_class = new Board($board_dir);
								$board_class->RegenerateAll();
								unset($board_class);
								unset($post_class);
								$tpl_page .= _gettext('Тред '.$delthread_id.' удален');
								management_addlogentry('Удаленный тред  <p>' .$delthread_id. '</p> ('.$numposts_deleted.' ответов) - /'. $board_dir . '/', 7);
								if (!empty($_GET['postid'])) {
									$tpl_page .= '<br /><br /><meta http-equiv="refresh" content="1;url='. KU_CGIPATH . '/manage_page.php?action=bans&banboard='. $_GET['boarddir'] . '&banpost='. $_GET['postid'] . $cp . '"><a href="'. KU_CGIPATH . '/manage_page.php?action=bans&banboard='. $_GET['boarddir'] . '&banpost='. $_GET['postid'] . $cp . '">'. _gettext('Redirecting') . '</a> to ban page...';
								} elseif ($isquickdel) {
									$tpl_page .= '<br /><br /><meta http-equiv="refresh" content="1;url='. KU_BOARDSPATH . '/'. $_GET['boarddir'] . '/"><a href="'. KU_BOARDSPATH . '/'. $_GET['boarddir'] . '/">'. _gettext('Перенаправляю...') . '</a>';
								}
							}
						} else {
							$tpl_page .= _gettext('Неправильный id треда '.$delpost_id);
						}
					}
				} elseif (isset($_POST['delpostid'])) {
				$this->CheckToken($_POST['token']);
					if ($_POST['delpostid'] > 0) {
						$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "posts` WHERE `boardid` = " . $board_id . " AND `IS_DELETED` = '0' AND `id` = " . $tc_db->qstr($_POST['delpostid']) . "");
						if (count($results) > 0) {
							if (isset($_POST['fileonly'])) {
								foreach ($results as $line) {
									if (!empty($line['file'])) {
										$del = rename(KU_ROOTDIR . $_POST['boarddir'] . '/src/'. $line['file'] . '.'. $line['file_type'], KU_ROOTDIR . $_POST['boarddir'] . '/src/__'. $line['file'] . '.'. $line['file_type']);
										if ($del) {
											@rename(KU_ROOTDIR . $_POST['boarddir'] . '/thumb/'. $line['file'] . 's.'. $line['file_type'], KU_ROOTDIR . $_POST['boarddir'] . '/thumb/__'. $line['file'] . 's.'. $line['file_type']);
											@rename(KU_ROOTDIR . $_POST['boarddir'] . '/thumb/'. $line['file'] . 'c.'. $line['file_type'], KU_ROOTDIR . $_POST['boarddir'] . '/thumb__/'. $line['file'] . 'c.'. $line['file_type']);
											$tc_db->Execute("UPDATE `".KU_DBPREFIX."posts` SET `file` = 'removed', `file_md5` = '' WHERE `boardid` = " . $board_id . " AND `id` = ".$_POST['delpostid']." LIMIT 1");
											$tpl_page .= '<hr />Файл удалён<hr />';
										} else {
											$tpl_page .= '<hr />Файл уже был удалён<hr />';
										}
									} else {
										$tpl_page .= '<hr />Такого файла нету<hr />';
									}
								}
							} else {
								foreach ($results as $line) {
									$delpost_id = $line['id'];
									$delpost_parentid = $line['parentid'];
								}
								$post_class = new Post($delpost_id, $board_dir, $board_id);
								$post_class->Delete();
								$board_class = new Board($board_dir);
								$threads = $tc_db->GetAll("SELECT id, bumped FROM `" . KU_DBPREFIX . "posts` WHERE `boardid` = " . $board_class->board['id'] . " AND `parentid` = 0 AND `IS_DELETED` = 0 ORDER BY `stickied` DESC, `bumped` DESC ");
								$threadnum = 0;
								foreach ($threads as $thread) {
									$threadnum++;
									if ($thread['id'] == $delpost_parentid) {
										$pages_to_regen = (floor($threadnum/KU_THREADS));
										break;
									}
								}
								
								//clear cache
								$board_name = board_id_to_name($board_id);
								//$thread_replyto = intval($_POST['thread'];
								$thread_replyto = $tc_db->GetOne("SELECT parentid FROM".KU_DBPREFIX."posts WHERE id = ".$_POST['delpostid']);
								cache_del ('thread_lastposts_' . $board_name . '_' .$thread_replyto);
								cache_del ('replycount_' . $board_name . '_' .$thread_replyto);
								
								$board_class->RegenerateThreads($delpost_parentid);
								$board_class->RegeneratePages($pages_to_regen, true);
								unset($board_class);
								unset($post_class);
								$tpl_page .= _gettext('Post '.$delpost_id.' successfully deleted.');
								management_addlogentry('Удаленный пост <p>' . link_on_post($board_name, $delpost_id). '</p> - /'. $board_dir . '/', 7);
								if ($_GET['postid'] != '') {
									$tpl_page .= '<br /><br /><meta http-equiv="refresh" content="1;url='. KU_CGIPATH . '/manage_page.php?action=bans&banboard='. $_GET['boarddir'] . '&banpost='. $_GET['postid'] . $cp . '"><a href="'. KU_CGIPATH . '/manage_page.php?action=bans&banboard='. $_GET['boarddir'] . '&banpost='. $_GET['postid'] . '">'. _gettext('Redirecting') . '</a> to ban page...';
								} elseif ($isquickdel) {
									$tpl_page .= '<br /><br /><meta http-equiv="refresh" content="1;url='. KU_BOARDSPATH . '/'. $_GET['boarddir'] . '/res/'. $delpost_parentid . '.html"><a href="'. KU_BOARDSPATH . '/'. $_GET['boarddir'] . '/res/'. $delpost_parentid . '.html">'. _gettext('Перенаправляю...') . '</a>';
								}
							}
						} else {
							$tpl_page .= _gettext('Треда с таким id нету'.$delpost_id);
						}
					}
				}
			} else {
				$tpl_page .= _gettext('Неправильный раздел');
			}
		}
		$tpl_page .= '<h2>'. _gettext('Удалить тред/пост') . '</h2><br />';
		if (!$multidel) {
			$tpl_page .= '<form action="manage_page.php?action=delposts" method="post">
      <input type="hidden" name="token" value="' . $_SESSION['token'] . '" />
			<label for="boarddir">'. _gettext('Board') .':</label>' .
			$this->MakeBoardListDropdown('boarddir', $this->BoardList($_SESSION['manageusername'])) .
			'<br />

			<label for="delthreadid">'. _gettext('Тред') .':</label>
			<input type="text" name="delthreadid" /><br />

			<label style="display:none;" for="fileonly">'. _gettext('File Only') .':</label>
			<input style="display:none;" type="checkbox" id="fileonly" name="fileonly" /><br />

			<input class="btn" type="submit" value="'. _gettext('Удалить тред') .'" />

			</form>
			<br /><hr />

			<form action="manage_page.php?action=delposts" method="post">
			<input type="hidden" name="token" value="' . $_SESSION['token'] . '" />
			<label for="boarddir">'. _gettext('Board') .':</label>' .
			$this->MakeBoardListDropdown('boarddir', $this->BoardList($_SESSION['manageusername'])) .
			'<br />

			<label for="delpostid">'. _gettext('Post') .':</label>
			<input type="text" name="delpostid" /><br />

			<label style="display:none;" for="archive">'. _gettext('Archive') .':</label>
			<input style="display:none;" type="checkbox" id="archive" name="archive" /><br />

			<label style="display:none;" for="fileonly">'. _gettext('File Only') .':</label>
			<input style="display:none;" type="checkbox" id="fileonly" name="fileonly" />

			<input class="btn" type="submit" value="'. _gettext('Удалить пост') .'" />

			</form>';
		}
	}

	/*
	* +------------------------------------------------------------------------------+
	* Moderation Pages
	* +------------------------------------------------------------------------------+
	*/

	/* Addition, modification, deletion, and viewing of bans */
	function bans() {
		global $tc_db, $tpl_page, $bans_class;
		
		$this->ModeratorsOnly();
		$reason = KU_BANREASON;
		$ban_ip = ''; $ban_hash = ''; $ban_parentid = 0; $multiban = Array();
		if (isset($_POST['modban']) && is_array($_POST['post']) && $_POST['board']) {
			$ban_board_id = $tc_db->GetOne("SELECT HIGH_PRIORITY `id` FROM `" . KU_DBPREFIX . "boards` WHERE `name` = " . $tc_db->qstr($_POST['board']) . "");
			if (!empty($ban_board_id)) {
				foreach ( $_POST['post'] as $post ) {
					$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "posts` WHERE `boardid` = '" . $ban_board_id . "' AND `id` = " . intval($post) . "");
					if (count($results) > 0) {
						$multiban[] = md5_decrypt($results[0]['ip'], KU_RANDOMSEED);
						$multiban_hash[] = $results[0]['file_md5'];
						$multiban_parentid[] = $results[0]['parentid'];
					}
				}
			}
		}
		if (isset($_GET['banboard']) && isset($_GET['banpost'])) {
			//$this->CheckToken($_POST['token']);
			$ban_board_id = $tc_db->GetOne("SELECT HIGH_PRIORITY `id` FROM `" . KU_DBPREFIX . "boards` WHERE `name` = " . $tc_db->qstr($_GET['banboard']) . "");
			$ban_board = $_GET['banboard'];
			$ban_post_id = $_GET['banpost'];
			if (!empty($ban_board_id)) {
				$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "posts` WHERE `boardid` = '" . $ban_board_id . "' AND `id` = " . $tc_db->qstr($_GET['banpost']) . "");
				if (count($results) > 0) {
					$ban_ip = md5_decrypt($results[0]['ip'], KU_RANDOMSEED);
					$ban_hash = $results[0]['file_md5'];
					$ban_parentid = $results[0]['parentid'];
				} else {
					$tpl_page.= _gettext('Пост с таким id не существует') . '<hr />';
				}
			}
		}
		$instantban = false;
		if ((isset($_GET['instant']) || isset($_GET['cp'])) && $ban_ip) {
			if (isset($_GET['cp'])) {
					$ban_reason = "Перманент #быстрый бан";
			} else {
				if($_GET['reason']) {
					$ban_reason = urldecode($_GET['reason']);
				} else {
					$ban_Reason = KU_BANREASON;
				}
			}
			$instantban = true;
		}
		$tpl_page .= '<h2>'. _gettext('Баны') . '</h2><br />';
		if (((isset($_POST['ip']) || isset($_POST['multiban'])) && isset($_POST['seconds']) && (!empty($_POST['ip']) || (empty($_POST['ip']) && !empty($_POST['multiban'])))) || $instantban) {
			if ($instantban) {
				$this->CheckAjaxToken($ban_post_id, board_name_to_id($ban_board));
			} else {	
				$this->CheckToken($_POST['token']);
			}
			if ($_POST['seconds'] >= 0 || $instantban) {
				$banning_boards = array();
				$ban_boards = '';
				if (isset($_POST['banfromall']) || $instantban) {
					$results = $tc_db->GetAll("SELECT HIGH_PRIORITY `name` FROM `" . KU_DBPREFIX . "boards`");
					foreach ($results as $line) {
						if (!$this->CurrentUserIsModeratorOfBoard($line['name'], $_SESSION['manageusername'])) {
							exitWithErrorPage('/'. $line['name'] . '/: '. _gettext('Ты можешь банить только на модерируемых тобой досках'));
						}
					}
				} else {
					if (empty($_POST['bannedfrom'])) {
						exitWithErrorPage(_gettext('Выберите доску'));
					}
					if(isset($_POST['deleteposts'])) {
						$_POST['deletefrom'] = $_POST['bannedfrom'];
					}
					foreach($_POST['bannedfrom'] as $board) {
						if (!$this->CurrentUserIsModeratorOfBoard($board, $_SESSION['manageusername'])) {
							exitWithErrorPage('/'. $board . '/: '. _gettext('Ты можешь банить только на модерируемых тобой досках'));
						}
					}
					$ban_boards = implode('|', $_POST['bannedfrom']);
				}
				$ban_globalban = (isset($_POST['banfromall']) || $instantban) ? 1 : 0;
				$ban_allowread = ($_POST['allowread'] == 0 || $instantban) ? 0 : 1;
				if (isset($_POST['quickbanboardid'])) {
					$ban_board_id = $_POST['quickbanboardid'];
				}
				if(isset($_POST['quickbanboard'])) {
					$ban_board = $_POST['quickbanboard'];
				}
				if(isset($_POST['quickbanpostid'])) {
					$ban_post_id = $_POST['quickbanpostid'];
				}
				$ban_ip = ($instantban) ? $ban_ip : $_POST['ip'];
				$ban_duration = ($_POST['seconds'] == 0 || $instantban) ? 0 : $_POST['seconds'];
				$ban_type = ($_POST['type'] <= 2 && $_POST['type'] >= 0) ? $_POST['type'] : 0;
				$ban_reason = ($instantban) ? $ban_reason : $_POST['reason'];
				$ban_note = ($instantban) ? '' : $_POST['staffnote'];
				$ban_appealat = 0;
				if (KU_APPEAL != '' && !$instantban) {
					$ban_appealat = intval($_POST['appealdays'] * 86400);
					if ($ban_appealat > 0) $ban_appealat += time();
				}
				if (isset($_POST['multiban']))
					$ban_ips = unserialize($_POST['multiban']);
				else 
					$ban_ips = Array($ban_ip);
				$i = 0;
				foreach ($ban_ips as $ban_ip) {
					$ban_msg = '';
					$whitelist = $tc_db->GetAll("SELECT `ipmd5` FROM `" . KU_DBPREFIX . "banlist` WHERE `type` = 2");
					if (in_array(md5($ban_ip), $whitelist)) {
						exitWithErrorPage(_gettext('Этот IP в вайтлисте'));
					}
					$session_md5 = false;
					if (isset($ban_post_id) && isset($ban_board)) {
						bdl_debug ("searching posts for session to ban post_id = " . $ban_post_id . " ban_board = " . $ban_board);
						$session_md5 = $tc_db->GetOne("select session_md5 from posts p join boards b on p.boardid = b.id where b.name = " .$tc_db->qstr($ban_board). " and p.id = " .$tc_db->qstr($ban_post_id));
					}
					if (!$session_md5) $session_md5 = true;
					if ($bans_class->BanUser($ban_ip, $_SESSION['manageusername'], $ban_globalban, $ban_duration, $ban_boards, $ban_reason, $ban_note, $ban_appealat, $ban_type, $ban_allowread, false, '', $session_md5)) {
						$regenerated = array();
						if (((KU_BANMSG != '' || $_POST['banmsg'] != '') && isset($_POST['addbanmsg']) && (isset($_POST['quickbanpostid']) || isset($_POST['quickmultibanpostid']))) || $instantban ) {
							$ban_msg = ((KU_BANMSG == $_POST['banmsg']) || empty($_POST['banmsg'])) ? KU_BANMSG : $_POST['banmsg'];
							if (isset($ban_post_id))
								$postids = Array($ban_post_id);
							else
								$postids = unserialize($_POST['quickmultibanpostid']);
							$results = $tc_db->GetAll("SELECT HIGH_PRIORITY `parentid`, `message` FROM `".KU_DBPREFIX."posts` WHERE `boardid` = " . $tc_db->qstr($ban_board_id) . " AND `id` = ".$tc_db->qstr($postids[$i])." LIMIT 1");
								
							foreach($results AS $line) {
								$tc_db->Execute("UPDATE `".KU_DBPREFIX."posts` SET `message` = ".$tc_db->qstr($line['message'] . $ban_msg)." WHERE `boardid` = " . $tc_db->qstr($ban_board_id) . " AND `id` = ".$tc_db->qstr($postids[$i]));
								//clearPostCache($postids[$i], $ban_board_id);
								if ($line['parentid']==0) {
									if (!in_array($postids, $regenerated)) {
										$regenerated[] = $postids[$i];
									}
								} else {
									if (!in_array($line['parentid'], $regenerated)) {
										$regenerated[] = $line['parentid'];
									}
								}
							}
						}
						$tpl_page .= _gettext('Пользователь забанен')."<br />";
					} else {
						exitWithErrorPage(_gettext('Ошибка'));
					}

					$logentry = _gettext('Забанен') . ' '. $ban_ip;
					$logentry .= ($ban_duration == 0) ? ' '. _gettext('без даты разбана') : ' '. _gettext('до') . ' '. date('F j, Y, g:i a', time() + $ban_duration);
					$logentry .= ' - '. _gettext('Причина') . ': '. $ban_reason . (($ban_note) ? (" (".$ban_note.")") : ("")). ' - '. _gettext('забанен на') . ': ';
					$logentry .= ($ban_globalban == 1) ? _gettext('всех досках') . ' ' : '/'. implode('/, /', explode('|', $ban_boards)) . '/ ';
					management_addlogentry($logentry, 8);
					$ban_ip = '';
					$i++;
				}
				if (count($regenerated) > 0) {
					$board_class = new Board($ban_board);
					foreach($regenerated as $thread) {
						$board_class->RegenerateThreads($thread);
					}
					$board_class->RegeneratePages();
					unset($board_class);
				}

				if(isset($_POST['deleteposts'])) {
					$tpl_page .= '<br />';
					$this->deletepostsbyip(true);
				}

				if ((isset($_GET['instant']) && !isset($_GET['cp']))) {
					die("Успешно");
				}

				if (isset($_POST['banhashtime']) && $_POST['banhashtime'] !== '' && ($_POST['hash'] !== '' || isset($_POST['multibanhashes'])) && $_POST['banhashtime'] >= 0) {
					if (isset($_POST['multibanhashes']))
						$banhashes = unserialize($_POST['multibanhashes']);
					else
						$banhashes = Array($_POST['hash']);
					foreach ($banhashes as $banhash){
						$results = $tc_db->GetOne("SELECT HIGH_PRIORITY COUNT(*) FROM `".KU_DBPREFIX."bannedhashes` WHERE `md5` = ".$tc_db->qstr($banhash)." LIMIT 1");
						if ($results == 0) {
							$tc_db->Execute("INSERT INTO `".KU_DBPREFIX."bannedhashes` ( `md5` , `bantime` , `description` ) VALUES ( ".$tc_db->qstr($banhash)." , ".$tc_db->qstr($_POST['banhashtime'])." , ".$tc_db->qstr($_POST['banhashdesc'])." )");
							management_addlogentry('Banned md5 hash '. $banhash . ' with a description of '. $_POST['banhashdesc'], 8);
						}
					}
				}
				if (!empty($_POST['quickbanboard']) && !empty($_POST['quickbanthreadid'])) {
					$tpl_page .= '<br /><br /><meta http-equiv="refresh" content="1;url='. KU_BOARDSPATH . '/'. $_POST['quickbanboard'] . '/';
					if ($_POST['quickbanthreadid'] != '0') $tpl_page .= 'res/'. $_POST['quickbanthreadid'] . '.html';
					$tpl_page .= '"><a href="'. KU_BOARDSPATH . '/'. $_POST['quickbanboard'] . '/';
					if ($_POST['quickbanthreadid'] != '0') $tpl_page .= 'res/'. $_POST['quickbanthreadid'] . '.html';
					$tpl_page .= '">'. _gettext('Перенаправляю') . '</a>...';
				}
			} else {
				$tpl_page .= _gettext('Введите срок бана в секундах или 0 для перманентного бана');
			}
			$tpl_page .= '<hr />';
		} elseif (isset($_GET['delban']) && $_GET['delban'] > 0) {
		$this->CheckToken($_GET['token']);
			$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "banlist` WHERE `id` = " . $tc_db->qstr($_GET['delban']) . "");
			if (count($results) > 0) {
				$unban_ip = md5_decrypt($results[0]['ip'], KU_RANDOMSEED);
				$tc_db->Execute("DELETE FROM `" . KU_DBPREFIX . "banlist` WHERE `id` = " . $tc_db->qstr($_GET['delban']) . "");
				$tpl_page .= _gettext('Бан убран');
				management_addlogentry(_gettext('Разбанен') . ' '. $unban_ip, 8);
			} else {
				$tpl_page .= _gettext('Некорректный id бана');
			}
			$tpl_page .= '<br /><hr />';
		} elseif (isset($_GET['delhashid'])) {
			$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "bannedhashes` WHERE `id` = " . $tc_db->qstr($_GET['delhashid']) . "");
			if (count($results) > 0) {
				$tc_db->Execute("DELETE FROM `" . KU_DBPREFIX . "bannedhashes` WHERE `id` = " . $tc_db->qstr($_GET['delhashid']) . "");
				$tpl_page .= _gettext('Хэш убран из бан листа') . '<br /><hr />';
			}
		}

		flush();

		$isquickban = false;

		$tpl_page .= '<form action="manage_page.php?action=bans" method="post" name="banform">';

		if ((!empty($ban_ip) && isset($_GET['banboard']) && isset($_GET['banpost'])) || (!empty($multiban) && isset($_POST['board']) && isset($_POST['post'])))  {
			$isquickban = true;
			$tpl_page .= '<input type="hidden" name="quickbanboard" value="'. (isset($_GET['banboard']) ? $_GET['banboard'] : $_POST['board']) . '" />';
			if(!empty($multiban)) {
				$tpl_page .= '<input type="hidden" name="quickbanboardid" value="'. $ban_board_id . '" /><input type="hidden" name="quickmultibanthreadid" value="'. htmlspecialchars(serialize($multiban_parentid)) . '" /><input type="hidden" name="quickmultibanpostid" value="'. htmlspecialchars(serialize($_POST['post'])) . '" />';
			} else {
				$tpl_page .= '<input type="hidden" name="quickbanboardid" value="'. $ban_board_id . '" /><input type="hidden" name="quickbanthreadid" value="'. $ban_parentid . '" /><input type="hidden" name="quickbanpostid" value="'. $_GET['banpost'] . '" />';
			}
		} elseif (isset($_GET['ip'])) {
			$ban_ip = $_GET['ip'];
		}

		$tpl_page .= '<fieldset>
		<legend>'. _gettext('IP и тип бана') . '</legend>
		<label for="ip">'. _gettext('IP') . ':</label>';
		if (!$multiban) {
			$tpl_page .= '<input type="text" name="ip" id="IP" value="'. $ban_ip . '" />
			<br /><label class="checkbox" for="deleteposts"><input type="checkbox" name="deleteposts" id="deleteposts" />'. _gettext('Удалить ВСЕ посты с этого IP') . ':</label>';
		}
		else {
			$tpl_page .= '<input type="hidden" name="multiban" value="'.htmlspecialchars(serialize($multiban)).'">
			<input type="hidden" name="multibanhashes" value="'.htmlspecialchars(serialize($multiban_hash)).'">	Multiple IPs
			<br /><label for="deleteposts">'. _gettext('Удалить ВСЕ посты с этого IP') . ':</label>
			<input type="checkbox" name="deleteposts" id="deleteposts" />';
		}

		$tpl_page .= '<br />
		<label for="type">'. _gettext('Тип бана') . ':</label>
		<select name="type" id="type"><option value="0">'. _gettext('IP') . '</option><option value="1">'. _gettext('Подсеть') . '</option><option value="2">'. _gettext('Вайтлист') . '</option></select>
		<div class="desc">'. _gettext('Вайтлист даёт иммунитет к бану. Подсеть банится по такому шаблону: 123.123.12') . '</div><br />';

		if ($isquickban && KU_BANMSG != '') {
			$tpl_page .= '<label for="addbanmsg">'. _gettext('Add ban message') . ':</label>
			<input type="checkbox" name="addbanmsg" id="addbanmsg" checked="checked" />
			<div class="desc">'. _gettext('If checked, the configured ban message will be added to the end of the post.') . '</div><br />
			<label for="banmsg">'. _gettext('Ban message') . ':</label>
			<input type="text" name="banmsg" id="banmsg" value="'. htmlspecialchars(KU_BANMSG) . '" size='. strlen(KU_BANMSG) . '" />';
		}

		$tpl_page .='</fieldset>
		<fieldset>
		<legend> '. _gettext('Разделы') . '</legend>
		<label for="banfromall"><input type="checkbox" name="banfromall" id="banfromall" /><strong> Все разделы</strong></label>
		<br /><br />' .
		$this->MakeBoardListCheckboxes('bannedfrom', $this->BoardList($_SESSION['manageusername'])) .
		'</fieldset><hr>';
		$tpl_page .= '<fieldset>
		<legend>Время бана и причина</legend>
		<label for="seconds">'. _gettext('Секунды') . ':</label>
		<input type="text" name="seconds" id="seconds" />
		<div class="desc"><a href="#" onclick="document.banform.seconds.value=\'3600\';return false;">1 час</a>&nbsp;<a href="#" onclick="document.banform.seconds.value=\'86400\';return false;">1 день</a>&nbsp;<a href="#" onclick="document.banform.seconds.value=\'259200\';return false;">3 дня</a>&nbsp;<a href="#" onclick="document.banform.seconds.value=\'604800\';return false;">1 неделя</a>&nbsp;<a href="#" onclick="document.banform.seconds.value=\'2592000\';return false;">Месяц</a>&nbsp;<a href="#" onclick="document.banform.seconds.value=\'0\';return false;">'. _gettext('ПЕРМАНЕНТ') .'</a></div><br />

		<label for="reason">'. _gettext('Причина') . ':</label>
		<input type="text" name="reason" id="reason" value="'. $reason . '" />
		<div class="desc"><a href="#" onclick="document.banform.reason.value=\''. _gettext('Нарушение правил') .'\';return false;">Нарушение правил</a>&nbsp;<a href="#" onclick="document.banform.reason.value=\''. _gettext('Быстрый бан') .'\';return false;">Быстрый бан</a>&nbsp;<a href="#" onclick="document.banform.reason.value=\''. _gettext('Обход бана') .'\';return false;">'. _gettext('Обход бана') .'</a></div><br />

		<label for="staffnote">'. _gettext('Заметка') . '</label>
		<input type="text" name="staffnote" id="staffnote" />
		<div class="desc"><a href="#" onclick="document.banform.staffnote.value=\''. _gettext('Нарушение правил') .'\';return false;">Нарушение правил</a> &nbsp;<a href="#" onclick="document.banform.staffnote.value=\''. _gettext('Быстрый бан') .'\';return false;">Быстрый бан</a> &nbsp;<a href="#" onclick="document.banform.staffnote.value=\''. _gettext('Обход бана') .'\';return false;">Обход бана</a> || '. _gettext('Заметку видит только модератор') .'</div><br />';

		$tpl_page .= '</fieldset>
		<input type="hidden" name="token" value="' . $_SESSION['token'] . '" />
		<input class="btn" type="submit" value="'. _gettext('Забанить') . '" />

		</form>
		<hr /><br />';

		for ($i = 2; $i >= 0; $i--) {
			switch ($i) {
				case 2:
					$tpl_page .= '<strong>'. _gettext('Вайтлист') . ':</strong><br />';
					break;
				case 1:
					$tpl_page .= '<br /><strong>'. _gettext('Подсети') . ':</strong><br />';
					break;
				case 0:
					if (!empty($ban_ip))
						$tpl_page .= '<br /><strong>'. _gettext('Previous bans on this IP') . ':</strong><br />';
					else
						$tpl_page .= '<br /><strong>'. _gettext('Баны') . ':</strong><br />';
					break;
			}
			if (isset($_GET['allbans'])) {
				$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "banlist` WHERE `type` = '" . $i . "' AND `by` != 'SERVER' ORDER BY `id` DESC");
				$hiddenbans = 0;
			} elseif (isset($_GET['limit'])) {
				$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "banlist` WHERE `type` = '" . $i . "' ORDER BY `id` DESC LIMIT ".intval($_GET['limit']));
				$hiddenbans = 0;
			} else {
				if (!empty($ban_ip) && $i == 0) {
					$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "banlist` WHERE `ipmd5` = '" . md5($ban_ip) . "' AND `type` = '" . $i . "' AND `by` != 'SERVER' ORDER BY `id` DESC");
				} else {
					$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "banlist` WHERE `type` = '" . $i . "' AND `by` != 'SERVER' ORDER BY `id` DESC LIMIT 15");
					// Get the number of bans in the database of this type
					$hiddenbans = $tc_db->GetAll("SELECT HIGH_PRIORITY COUNT(*) FROM `" . KU_DBPREFIX . "banlist` WHERE `type` = '" . $i . "'");
					// Subtract 15 from the count, since we only want the number not shown
					$hiddenbans = $hiddenbans[0][0] - 15;
				}
			}
			if (count($results) > 0) {
				$tpl_page .= '<table class="table table-striped table-bordered table-hover table-condensed" border="1" width="100%"><tr><th>';
				$tpl_page .= ($i == 1) ? _gettext('IP ренж') : _gettext('IP Адрес');
				$tpl_page .= '</th><th>Сессия</th><th>'. _gettext('Boards') . '</th><th>'. _gettext('Причина') . '</th><th>'. _gettext('Заметка') . '</th><th>'. _gettext('Дата бана') . '</th><th>'. _gettext('Дата окончания') . '</th><th>'. _gettext('Забанил') . '</th><th>&nbsp;</th></tr>';
				foreach ($results as $line) {
					$tpl_page .= '<tr><td><a href="?action=bans&ip='. md5_decrypt($line['ip'], KU_RANDOMSEED) . '">'. md5_decrypt($line['ip'], KU_RANDOMSEED) . '</a></td><td>';
					$tpl_page .= substr($line['session_md5'], 0, 16) . '<br>' . substr($line['session_md5'], 16) . '</td><td>';
					if ($line['globalban'] == 1) {
						$tpl_page .= '<strong>'. _gettext('All boards') . '</strong>';
					} elseif (!empty($line['boards'])) {
						$tpl_page .= '<strong>/'. implode('/</strong>, <strong>/', explode('|', $line['boards'])) . '/</strong>&nbsp;';
					}
					$tpl_page .= '</td><td>';
					$tpl_page .= (!empty($line['reason'])) ? htmlentities(stripslashes($line['reason']), NULL, 'UTF-8') : '&nbsp;';
					$tpl_page .= '</td><td>';
					$tpl_page .= (!empty($line['staffnote'])) ? htmlentities(stripslashes($line['staffnote']), NULL, 'UTF-8') : '&nbsp;';
					$tpl_page .= '</td><td>'. date("F j, Y, g:i a", $line['at']) . '</td><td>';
					$tpl_page .= ($line['until'] == 0) ? '<strong>'. _gettext('Перманент') . '</strong>' : date("F j, Y, g:i a", $line['until']);
					$tpl_page .= '</td><td>'. $line['by'] . '</td><td>[<a href="manage_page.php?action=bans&delban='. $line['id'] . '&token='.$_SESSION['token'].'">'. _gettext('Delete') .'</a>]</td></tr>';
				}
				$tpl_page .= '</table>';
				if ($hiddenbans > 0) {
					$tpl_page .= sprintf(_gettext('%s скрыто'), $hiddenbans) .
					' <a href="?action=bans&allbans=1">'. _gettext('Показать все баны') . '</a>'.' <a href="?action=bans&limit=100">Показать последние 100</a>';
				}
			} else {
				$tpl_page .= _gettext('Пусто<br>');
			}
		}
	}

	function send_message_to_ip ($ip = NULL, $subject = NULL, $text = NULL) {
		global $tc_db, $tpl_page, $board_class;
		$this->ModeratorsOnly();
		if (isset ($_POST['text'])) {
		$this->CheckToken($_POST['token']);
			if (isset ($_POST['ip']) && isset ($_POST['text']) && isset ($_POST['subject'])) {
				$ips = preg_split ("/[\s,]+/", $_POST['ip'], -1, PREG_SPLIT_NO_EMPTY);
				if (is_array ($ips)) {
					foreach ($ips as $ip) {
						$ip = trim($ip);
						$tc_db->Execute("INSERT INTO `" . KU_DBPREFIX . "messages` ( `ip` , `subject`, `text` ) VALUES ( " . $tc_db->qstr($ip) . ", " . $tc_db->qstr($_POST['subject']) . ", " . $tc_db->qstr($_POST['text']) . " )");
						$tpl_page .= "Сообщение отправлено на $ip<br>";
					}
				}
			} else {
				$tpl_page .= 'Заполните все поля';
			}
			// send
		} else {
			// draw interface
			$tpl_page .= '<form action="?action=send_message_to_ip" method="post">';
			$tpl_page .= '<textarea style="width: 880px; height: 150px;" cols="60" rows="4" id="ip" name="ip" placeholder="Введите ip/ip list">';
			if (isset($_GET['ip'])) {
				$tpl_page .= $_GET['ip'];
			} else {
				$tpl_page .= "";
			}
			$tpl_page .= '</textarea><br><input type="text" id="subject" name="subject" placeholder="Тема">';
			$tpl_page .= '<br><textarea style="width: 880px; height: 150px;" cols="60" rows="7" id="text" name="text" placeholder="Текст сообщения"></textarea>';
			$tpl_page .= '<input type="hidden" name="token" value="' . $_SESSION['token'] . '" />
			<br><input class="btn" type="submit" value="Отправить" /></form>';
		}

	}
	
	function approvepasscode(){
		global $tc_db, $tpl_page, $board_class;
		$this->ModeratorsOnly();
		if (isset ($_POST['passcode'])){
		$this->CheckToken($_POST['token']);
			$passcode = substr($_POST['passcode'],0,6);
			$tc_db->Execute('update passcode set torpass = "1" where passcode = ' .$tc_db->qstr($passcode));
		} else {
			$tpl_page .= '<form action="?action=approvepasscode" method="post">';
			$tpl_page .= '<b>Введите пасскод который сможет постить из под ТОРа</b><br><br>';
			$tpl_page .= '<textarea size="6" maxlength="6" name="passcode"></textarea><br>';
			$tpl_page .= '<input type="hidden" name="token" value="' . $_SESSION['token'] . '" />
			<input class="btn" type="submit" value="Разрешить" /></form><br>';
			
			$tpl_page .= '<ul class="nav nav-list" >';
			$showall = $tc_db->GetAll('select passcode from passcode where torpass = "1"');
			
			for ($i = 0; $i < count($showall); $i++){
				$tpl_page .= '<li>' .substr($showall[$i]['passcode'],0,6). '</li>';
			}
			$tpl_page .= '</ul>';
		}
	}

	/* Search for all posts by a selected IP address and delete them */
	function deletepostsbyip($from_ban = false) {
		global $tc_db, $tpl_page, $board_class;
		$this->ModeratorsOnly();
		if (!$from_ban) {
			$tpl_page .= '<h2>Удалить все посты с IP</h2><br />';
		}
		if (isset($_POST['ip']) || isset($_POST['multiban'])) {
			if ($_POST['ip'] != '' || !empty($_POST['multiban'])) {
        if (!$from_ban) {
          $this->CheckToken($_POST['token']);
        }
				$deletion_boards = array();
				$deletion_new_boards = array();
				$board_ids = '';
				if (isset($_POST['banfromall'])) {
					$results = $tc_db->GetAll("SELECT HIGH_PRIORITY `id`, `name` FROM `" . KU_DBPREFIX . "boards`");
					foreach ($results as $line) {
						if (!$this->CurrentUserIsModeratorOfBoard($line['name'], $_SESSION['manageusername'])) {
							exitWithErrorPage('/'. $line['name'] . '/: '. _gettext('Ты не модерируешь эту доску'));
						}
						$delete_boards[$line['id']] = $line['name'];
						$board_ids .= $line['id'] . ',';
					}
				} else {
					if (empty($_POST['deletefrom'])) {
						exitWithErrorPage(_gettext('Выберите доску'));
					}
					foreach($_POST['deletefrom'] as $board) {
						if (!$this->CurrentUserIsModeratorOfBoard($board, $_SESSION['manageusername'])) {
							exitWithErrorPage('/'. $board . '/: '. _gettext('Ты не модерируешь эту доску'));
						}
						$id = $tc_db->GetOne("SELECT `id` FROM `" . KU_DBPREFIX . "boards` WHERE `name` = " . $tc_db->qstr($board));
						$board_ids .= $tc_db->qstr($id) . ',';
						$delete_boards[$id] = $board;
					}
				}
				$board_ids = substr($board_ids, 0, -1);

				$i = 0;
				if (isset($_POST['multiban']))
					$ips = unserialize($_POST['multiban']);
				else
					$ips = Array($_POST['ip']);
				foreach  ($ips as $ip) {
					$i = 0;				
					$post_list = $tc_db->GetAll("SELECT `id`, `boardid` FROM `" . KU_DBPREFIX . "posts` WHERE `boardid` IN (" . $board_ids . ") AND `IS_DELETED` = '0' AND `ipmd5` = '" . md5($ip) . "'");
					if (count($post_list) > 0) {
						foreach ($post_list as $post) {
							$i++;
							$post_class = new Post($post['id'], $delete_boards[$post['boardid']], $post['boardid']);
							$post_class->Delete();
							$boards_deleted[$post['boardid']] = $delete_boards[$post['boardid']];
							unset($post_class);
						}

						$tpl_page .= _gettext('Все треды и посты были удалены') . '<br /><strong>'. $i . '</strong> записей было удалено<br />';
						management_addlogentry(_gettext('Удалены посты с ip') . ' '. $ip, 7);
					}
					else {
						$tpl_page .= _gettext('Постов нет');
					}
					if (isset($boards_deleted)) {
						
						foreach ($boards_deleted as $board) {
							$board_class = new Board($board);
							$board_class->RegenerateAll();
							unset($board_class);
						}
					}
				}
				$tpl_page .= '<hr />';
			}
		}
		if (!$from_ban) {
			$tpl_page .= '<form action="?action=deletepostsbyip" method="post">
      <input type="hidden" name="token" value="' . $_SESSION['token'] . '" />
			<fieldset>
			<label for="ip">'. _gettext('IP') .':</label>
			<input type="text" id="ip" name="ip"';
			if (isset($_GET['ip'])) {
				$tpl_page .= ' value="'. $_GET['ip'] . '"';
			}
			$tpl_page .= ' /></fieldset><br /><fieldset>

			<label for="banfromall"><input type="checkbox" id="banfromall" name="banfromall" /><strong> Все разделы</strong></label>
			<br /><br />' .
			$this->MakeBoardListCheckboxes('deletefrom', $this->BoardList($_SESSION['manageusername'])) .
			'<br /></fieldset>
			<hr />
			<input class="btn" type="submit" value="'. _gettext('Удалить посты') .'" />

			</form>';
		}
	}

	function editpostui() {
		global $tc_db, $tpl_page;
		$this->ModeratorsOnly();
		
	}
	
	function editpost() {
		global $tc_db, $tpl_page;
		$this->ModeratorsOnly(); /*OR $this->AdministatorsOnly();*/ /*Select whether you want this option was for modetartors or only administators */
		
		$board = isset($_GET['boarddir']) ? $_GET['boarddir'] : '';
		$editpostid = isset($_GET['editpostid']) ? $_GET['editpostid'] : '';
		$board_id = $tc_db->GetOne("SELECT HIGH_PRIORITY `id` FROM `". KU_DBPREFIX . "boards` WHERE `name` = ".$tc_db->qstr($board));
		
		if($_POST['message'] || $_POST['subject']) {
		$this->CheckToken($_POST['token']);
			if($_POST['message']) {
				if ((strpos(strtolower($_POST['message']), 'php') !== false || strpos(strtolower($_POST['message']), '<script')) && !$this->CurrentUserIsAdministrator()) {
					$tpl_page .= _gettext('блин, прости, но пост с такими словами можно редактировать только админам.') . ' <br /><hr />';
				} else {
					$tc_db->Execute("UPDATE `" . KU_DBPREFIX . "posts` SET `message` = ".$tc_db->qstr($_POST['message'])." WHERE `boardid` = ".$board_id." AND `id` = ".$tc_db->qstr($editpostid)." ");
				}
			}
			if($_POST['subject']) {
				$tc_db->Execute("UPDATE `" . KU_DBPREFIX . "posts` SET `subject` = ".$tc_db->qstr($_POST['subject'])." WHERE `boardid` = ".$board_id." AND `id` = ".$tc_db->qstr($editpostid)." ");
			}		
			if($_POST['nam3']) {
				$tc_db->Execute("UPDATE `" . KU_DBPREFIX . "posts` SET `name` = ".$tc_db->qstr($_POST['nam3'])." WHERE `boardid` = ".$board_id." AND `id` = ".$tc_db->qstr($editpostid)." ");
			}
			
			//clear cache
			cache_flush();
			$tpl_page .= _gettext('Пост отредактирован') . ' <br /><hr />';
		}
		
		$results = $tc_db->GetAll("SELECT HIGH_PRIORITY `parentid`,`message`,`subject`,`name` FROM `" . KU_DBPREFIX . "posts` WHERE `boardid` = " . $board_id . " AND `id` = " . $tc_db->qstr($editpostid) . " ");
		
		foreach ($results as $line) {
			$parentid = $line['parentid'];
			$message = $line['message'];
			$subject = $line['subject'];
			$nam3 = $line['name'];
		}
		if($parentid == 0) { $parentid = $editpostid; }
		
		$tpl_page .= '<h2>'. _gettext('Edit post ID: '.$editpostid.' from board: /'.$board.'/') . '</h2><br />';
		$tpl_page .= '<form action="" method="post">
		Тема:<br /><input type="text" name="subject" value="'.$subject.'" /><br />
		Имя: <br /><input type="text" name="nam3" value="'.$nam3.'" /><br />
		HTML:<br /><textarea cols="80" rows="15" name="message">'.$message.'</textarea><input type="hidden" name="thread" value="'.$parentid.'" /><br />
		<input type="hidden" name="token" value="' . $_SESSION['token'] . '" />
		<input class="btn" type="submit" name="edit" value="Edit" /></form>';
	}

	function threadbans(){
		//TODO
		//fix $query
		//[delete] button in table 
		global $tc_db, $tpl_page;
		$this->ModeratorsOnly();
		$boards = $this->BoardList($_SESSION['manageusername']);
		
		$tpl_page .= '<h2>Забанить в треде</h2><br />';
		
		if(isset($_POST['addban'])) {
			$this->CheckToken($_POST['token']);
			bdl_debug('threadban vars : ' . $_POST['boardid'] . ' x '. $_POST['threadid'] . ' x '. $_POST['official_id'] . ' x '. $_POST['postid'] . ' x '. $_POST['post_boardid'] . ' x '. $_POST['ip']);
			//                                     test           x             10955         x                              x        11113            x       test                    x
			if (empty($_POST['note']) || 
				(
					(
						empty($_POST['boardid']) || 
						empty($_POST['threadid'])
					) &&
					empty($_POST['official_id'])
				) || 
				(
					empty($_POST['ip']) &&
					(
						empty($_POST['postid']) ||
						empty($_POST['post_boardid'])
					)
				) 
			){
				bdl_debug('qq check empty shit');
				$tpl_page .= 'Нужно заполнить:<br>- Доску + тред ИЛИ выбрать официальный тред<br>- Айпи или номер поста и доску<br>- Заметку';
				return;
			}

			if (empty($_POST['ip'])) {
				$post_boardid = board_name_to_id(str_replace('/', '', $_POST['post_boardid']));
				// there is no explicid IP, but (i hope) a post, from which we should take it
				$query = 'select ipmd5, session_md5 from posts where boardid ='.$tc_db->qstr($post_boardid).' and id = '.$tc_db->qstr($_POST['postid']);
				//bdl_debug ($query);
				$row = $tc_db->GetRow($query);
				$session_md5 = $row['session_md5'];
				$ipmd5 = $row['ipmd5'];
				if (!$session_md5 || !$ipmd5) {
					$tpl_page .= 'Не удалось найти IP/сессию';
					return;
				}
			} else {
				$ipmd5 = md5($_POST['ip']);
				$session_md5 = 'ebloa';
			}

			empty($_POST['official_id']) ? $official_id = '' : $official_id = $_POST['official_id'];

			$query = 'insert into thread_bans (note, threadid, boardid, ipmd5, session_md5, banned_by, unban, official_id) values (' .$tc_db->qstr($_POST['note']). ', ' .$tc_db->qstr($_POST['threadid']). ', ' .$tc_db->qstr(board_name_to_id($_POST['boardid'])). ', ' .$tc_db->qstr($ipmd5). ', '.$tc_db->qstr($session_md5).', ' .$tc_db->qstr($tc_db->GetOne('select id from staff where username = ' .$tc_db->qstr($_SESSION['manageusername']))). ' , ' .$tc_db->qstr($_POST['unban'] ? 1 : 0). ', '.$tc_db->qstr($official_id).')';
			bdl_debug($query);
			$res = $tc_db->Execute($query);
			if ($res) {
				$tpl_page .= 'Запись добавлена.';
			} else {
				$tpl_page .= 'Что-то не так - может треда такого там нет?';
			}
			
		} elseif (isset($_GET['idban'])) {
			$this->CheckToken($_GET['token']);
			if (empty($_GET['idban'])){
				die('hoho haha');
			}	
			$tc_db->Execute('delete from thread_bans where id = ' .$tc_db->qstr($_GET['idban']));
			$tpl_page .= 'Бан успешно удалён';
			
		} else {
				$tpl_page .= '
				<form action="?action=threadbans&banuser" method="post">
				Заметка:<br /><input type="text" name="note" value="" /><br />';

				//$tpl_page .= '<select name="boardid">';
				foreach ($boards as $board) {
					$boardlist.= '<option value="' .board_name_to_id($board['name']). '">' .$board['name']. '</option>';				
				}
				$boardlist .= '</select><br>';

				$tpl_page .= $this->MakeBoardListDropdown('boardid', $boards, false, (isset($_GET['board_x']) ? $_GET['board_x'] : false)). '<br />';
				$tpl_page .= '
				Номер треда: <br /><input type="text" name="threadid" value="'.(isset($_GET['post_x']) ? $_GET['post_x'] : '').'" /><br />';
				$tpl_page .= '<select class="form-control" name="official_id"><option value="">Официальные треды</option>';
				foreach ($tc_db->GetAll('select id, subject from official') as $row) {
					$tpl_page .= '<option value="'. $row['id'] . '">'. $row['subject'] . '</option>';
				}
				$tpl_page .= '</select><br>';

				$tpl_page .= '
				IP: <br /><input type="text" name="ip" value="" /><br />';
				$tpl_page .= '
				Достать IP/сессию из: <br /> Доски: ';
				$tpl_page .= $this->MakeBoardListDropdown('post_boardid', $boards, false, (isset($_GET['board_x']) ? $_GET['board_x'] : false)) .'<br />
				Поста: <input type="text" name="postid" value="'.(isset($_GET['post_x']) ? $_GET['post_x'] : '').'" /><br />';
				$tpl_page .= '
				<input type="hidden" name="token" value="' . $_SESSION['token'] . '">
				<input type="hidden" name="addban" value="addban">
				<input class="btn" type="submit" name="ban" value="Забанить" />
				<input class="btn" type="submit" name="unban" value="Разбанить" /></form>';
			
			
			$bans = $tc_db->GetAll('select o.subject, tb.*, s.username, p.ip from thread_bans tb join staff s on tb.banned_by = s.id left join posts p on tb.ipmd5 = p.ipmd5 left join official o on tb.official_id = o.id group by id');
			$tpl_page .= '<table id="online_table" class="table table-striped table-bordered table-hover table-condensed tablesorter">
				<thead>
				<tr>
				<th>Заметка</th>
				<th>id треда</th>
				<th>Доска</th>
				<th>Офтред</th>
				<th>IP</th>
				<th>Session</th>
				<th>Дата бана</th>
				<th>Анбан</th>
				<th>Всем</th>
				<th>Забанил</th>
				<th>Разбанить</th>
				</tr>
				</thead>
				<tbody id="tablebody">';
			foreach ($bans as $row){
				$tpl_page .= '
				<tr>
				<td>' .$row['note']. '</td>
				<td>' .$row['threadid']. '</td>
				<td>' .board_id_to_name($row['boardid']). '</td>
				<td>' .$row['subject']. '</td>
				<td>' .md5_decrypt($row['ip'], KU_RANDOMSEED). '</td>
				<td>' .$row['session_md5']. '</td>
				<td>' .$row['timestamp']. '</td>
				<td>' .($row['unban'] == 0 ? '' : 'X'). '</td>
				<td>' .($row['global'] == 0 ? '' : 'X'). '</td>
				<td>' .$row['username']. '</td>
				<td>[<a href="?action=threadbans&idban=' .$row['id']. '&token=' . $_SESSION['token'] . '">Удалить бан</a>]</td>
				</tr>';
			}
			$tpl_page .= '</tbody></table><script>$(document).ready(function(){$("#online_table").tablesorter();});</script>';
		}
	}

	/*
	* +------------------------------------------------------------------------------+
	* Misc Functions
	* +------------------------------------------------------------------------------+
	*/

	/* Generate a list of boards a moderator controls */
	function BoardList($username) {
		global $tc_db, $tpl_page;

		$staff_boardsmoderated = array();
		$results = $tc_db->GetAll("SELECT HIGH_PRIORITY `boards` FROM `" . KU_DBPREFIX . "staff` WHERE `username` = '" . $username . "' LIMIT 1");
		if ($this->CurrentUserIsAdministrator() || $results[0][0] == 'allboards') {
			$resultsboard = $tc_db->GetAll("SELECT HIGH_PRIORITY `id`, `name` FROM `" . KU_DBPREFIX . "boards` ORDER BY `name` ASC");
			foreach ($resultsboard as $lineboard) {
					$staff_boardsmoderated = array_merge($staff_boardsmoderated, array(array( 'name' => $lineboard['name'], 'id' => $lineboard['id'])));
			}
		} else {
			if ($results[0][0] != '') {
				foreach ($results as $line) {
					$array_boards = explode('|', $line['boards']);
				}
				foreach ($array_boards as $this_board_name) {
					$this_board_id = $tc_db->GetOne("SELECT HIGH_PRIORITY `id` FROM `" . KU_DBPREFIX . "boards` WHERE `name` = " . $tc_db->qstr($this_board_name) . "");
					$staff_boardsmoderated = array_merge($staff_boardsmoderated, array(array('name' => $this_board_name, 'id' => $this_board_id)));
				}
			}
		}

		return $staff_boardsmoderated;
	}

	/* Generate a list of boards in query format */
	function sqlboardlist() {
		global $tc_db, $tpl_page;

		$results = $tc_db->GetAll("SELECT HIGH_PRIORITY `id` FROM `" . KU_DBPREFIX . "boards` ORDER BY `name` ASC");
		$sqlboards = '';
		foreach ($results as $line) {
			$sqlboards .= 'posts_'. $line['name'] . ', ';
		}

		return substr($sqlboards, 0, -2);
	}

	/* Generate a dropdown box from a supplied array of boards */
	function MakeBoardListDropdown($name, $boards, $all = false, $default = false) {
		$output = '<select class="form-control" name="'. $name . '"><option value="">'. _gettext('Select a Board') .'</option>';
		if (!empty($boards)) {
			if ($all) {
				$output .= '<option '.($default == 'allboards' ? 'selected="selected"' : '').' value="all">'. _gettext('All Boards') .'</option>';
			}
			foreach ($boards as $board) {
				$output .= '<option '.($default == $board['name'] ? 'selected="selected"' : '').' value="'. $board['name'] . '">/'. $board['name'] . '/</option>';
			}
		}
		$output .= '</select>';

		return $output;
	}

	/* Generate a series of checkboxes from a supplied array of boards */
	function MakeBoardListCheckboxes($boxname, $boards) {
		$output .= '
		<div class="btn-group">
		<button data-toggle="dropdown" class="btn btn-primary dropdown-toggle" data-placeholder="Выберите разделы">Выберите разделы <span class="caret"></span></button>
		<ul class="dropdown-menu">';

		if (!empty($boards)) {
			foreach ($boards as $board) {
				$output .= '<li> <input id="'. $board['name'] . '" name="'. $boxname . '[]" value="'. $board['name'] . '" type="checkbox"> <label for="'. $board['name'] . '">'. $board['name'] . '</label></li>'."\n";				
			}
			$output .= '</ul></div><br>';
		}

		return $output;
	}

	/* Generate a dropdown box for all sections */
	function MakeSectionListDropDown($name, $selected) {
		global $tc_db;

		$output = '<select name="'. $name . '"><option value="">'. _gettext('Select a Section') .'</option>'. "\n";
		$results = $tc_db->GetAll("SELECT `id`, `name` FROM `" . KU_DBPREFIX . "sections` ORDER BY `order` ASC");
		if(count($results) > 0) {
			foreach ($results as $section) {
				if ($section['id'] == $selected) {
					$select = ' selected="selected"';
				} else {
					$select = '';
				}
				$output .= '<option value="'. $section['id'] . '"'. $select . '>'. $section['name'] . '</option>'. "\n";
			}
		}
		$output .= '</select><br />'. "\n";

		return $output;
	}

	/* Delete files without their md5 stored in the database */
	function delunusedimages($verbose = false) {
		global $tc_db, $tpl_page;
		$this->AdministratorsOnly();

		$resultsboard = $tc_db->GetAll("SELECT HIGH_PRIORITY `id`, `name` FROM `" . KU_DBPREFIX . "boards`");
		foreach ($resultsboard as $lineboard) {
			unset ($file_list); unset ($files); unset ($orphan_files);
			if ($verbose) {
				$tpl_page .= '<strong>'. _gettext('Looking for unused images in') .' /'. $lineboard['name'] . '/</strong><br />';
			}
			$file_md5list = array();
			//$results = $tc_db->GetAll("SELECT HIGH_PRIORITY `file_md5`, `file` FROM `" . KU_DBPREFIX . "posts` WHERE `boardid` = " . $lineboard['id'] . " AND `IS_DELETED` = 0 AND `file` != '' AND `file` != 'removed' AND `file_md5` != ''");
			$results = $tc_db->GetAll("SELECT HIGH_PRIORITY concat (`file`, '.', `file_type`) as x FROM `" . KU_DBPREFIX . "posts` WHERE `boardid` = " . $lineboard['id'] . " AND `IS_DELETED` = 0 AND `file` != '' AND `file` != 'removed'");
			foreach ($results as $line)
				$file_list[] = $line['x'];

			$dir = KU_BOARDSDIR . $lineboard['name'] . '/src';
			if (chdir ($dir)) {
				$files = glob("*.*", GLOB_BRACE);
				if (is_array($files) && is_array($file_list)){
					$orphan_files = array_diff ($files, $file_list);
					foreach ($orphan_files as $file) {
						if ($file != '140086058578s.jpg' || $file != '140086058578.jpg') {
						$tpl_page .= $file . " deleted <br />";
						$removed_files++;
						rename(KU_BOARDSDIR . $lineboard['name'] . '/src/'. $file, KU_BOARDSDIR . $lineboard['name'] . '/src/__'. $file);
						@rename(KU_BOARDSDIR . $lineboard['name'] . '/thumb/'. substr($file, 0, -4) . 's'. substr($file, strlen($file) - 4), KU_BOARDSDIR . $lineboard['name'] . '/thumb/__'. substr($file, 0, -4) . 's'. substr($file, strlen($file) - 4));
						@rename(KU_BOARDSDIR . $lineboard['name'] . '/thumb/'. substr($file, 0, -4) . 'c'. substr($file, strlen($file) - 4), KU_BOARDSDIR . $lineboard['name'] . '/thumb/__'. substr($file, 0, -4) . 'c'. substr($file, strlen($file) - 4));
						}
					}
				}
			} else {
				$tpl_page .= "ERROR : directory $dir not found<br>";
			}
		}

		return true;
	}

	/* Delete replies currently not marked as deleted who belong to a thread which is marked as deleted */


	function spam() {
		global $tpl_page;
		$spam = KU_ROOTDIR . 'spam.txt';
		if (!empty($_POST['spam'])) {
      $this->CheckToken($_POST['token']);
			file_put_contents($spam, $_POST['spam']);
			$tpl_page .= '<hr />'. _gettext('Список обновлён') .'<hr />';
		}
		$content = htmlspecialchars(file_get_contents(KU_ROOTDIR . 'spam.txt'));

		$tpl_page .= '<h2>'. _gettext('Спам') .'</h2> <br />'. "\n" .
					'<form action="?action=spam" method="post">'. "\n" .
          '<input type="hidden" name="token" value="' . $_SESSION['token'] . '" />' . "\n" . 
					'<textarea name="spam" style="width: 880px; height: 500px;">' . htmlspecialchars($content) . '</textarea><br />' . "\n" .
					'<input class="btn" type="submit" value="'. _gettext('Обновить') .'" />'. "\n" .
					'</form>'. "\n";
	}
	/* Gets the IP address of a post */
	function getip() {
		global $tc_db, $smarty, $tpl_page;
		if(!$this->CurrentUserIsModerator() && !$this->CurrentUserIsAdministrator()) {
			die();
		}
		$results = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "boards` WHERE `name` = " . $tc_db->qstr($_GET['boarddir']));
		if (count($results) > 0) {
			if (!$this->CurrentUserIsModeratorOfBoard($_GET['boarddir'], $_SESSION['manageusername'])) {
				die();
			}
			$ip = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "posts` WHERE `boardid` = " . $tc_db->qstr($results[0]['id']) . " AND `id` = " . $tc_db->qstr($_GET['id']));
			die("dnb-".$_GET['boarddir']."-".$_GET['id']."-".(($ip[0]['parentid'] == 0) ? ("y") : ("n"))."=".md5_decrypt($ip[0]['ip'], KU_RANDOMSEED).'='.$ip[0]['IS_DELETED']);
		}
		die();
	}
	function geoip() {
		global $tc_db, $smarty, $tpl_page;
                if(!$this->CurrentUserIsModerator() && !$this->CurrentUserIsAdministrator()) {
                        die();
                }

		require KU_ROOTDIR.'lib/geoip/geoip.inc.php';
		           
		$res = $tc_db -> GetAll ("select o.timestamp, o.is_proxy, o.ip, o.real_ip, p.count from online o left outer join v_posts_recent p on md5(o.ip) = p.ipmd5 order by p.count;");
		$gi = geoip_open(KU_ROOTDIR.'lib/geoip/GeoIP.dat',GEOIP_STANDARD);

		if ($res){
			$tpl_page .= '
			<table id="online_table" class="table table-striped table-bordered table-hover table-condensed tablesorter">
			<thead> 
			<tr><th>Время</th><th>IP</th><th>Страна</th><th>прокси</th><th>IP прокси</th><th>Страна прокси</th><th>Постов за 24 часа с прокси</th></tr>
			</thead> 
			<tbody>';
		        foreach ($res as $line) {
				$tpl_page .= "<tr>";
		                $prox_ip =  $line['ip']; $real_ip = $line['real_ip'];
				if ($prox_ip != $real_ip){
					// proxy
					$prox_cc = geoip_country_code_by_addr($gi, $prox_ip);
					// $posts = $tc_db -> GetOne ("SELECT count(id) from posts where ipmd5='" . md5($prox_ip) . "' and timestamp > unix_timestamp() - 86400;");
		                	$prox_cc = (empty($prox_cc)) ? 'unknown' : $prox_cc;
				}else{
					$prox_ip = ''; $prox_cc = '';
				}
				$real_cc = geoip_country_code_by_addr($gi, $real_ip);
		                $real_cc = (empty($real_cc)) ? 'unknown' : $real_cc;
		                $tpl_page .= "<td>" . gmdate("H:i:s",$line['timestamp']) . "</td><td><a href=/manage_page.php?action=ipsearch&ip=" . $real_ip . ">" . $real_ip . "</a></td><td>" . $real_cc . "</td><td>" . ($line['is_proxy'] == 1 ? 'X' : '') . "</td><td><a href=/manage_page.php?action=ipsearch&ip=" . $prox_ip . ">" . $prox_ip . "</td><td>" . $prox_cc . "</td><td>" . $line['count'] . "</td>";
				$tpl_page .= "</tr>";
		        }
			$tpl_page .= "</tbody></table>";
			$tpl_page .= "Всего: ". count($res) . ", Время: " . gmdate("H:i:s",time()) . "<br>";
			$tpl_page .= '<script>$(document).ready(function(){$("#online_table").tablesorter();});</script>';
		}
	}
}
?>
