<?php
//echo '<h1 style="top:25%;right:45%;">soon</h1>';
//die();
require_once ('config.php');
require_once KU_ROOTDIR .'/inc/api/cache.php';
require_once KU_ROOTDIR . 'inc/api/debug.php';
require_once KU_ROOTDIR . 'inc/api/config.php';
require_once KU_ROOTDIR . "inc/func/ip.php";
require_once KU_ROOTDIR . "inc/func/custom.php";

if (isset ($_GET['b'])) { $board = $_GET['b'];
if (!board_exists($board)) do_404();
}
if (isset ($_GET['t'])) { $thread = $_GET['t'];
// can't check here since search "threads" do not exist
//if (!thread_exists($board, $thread)) do_404();
}
if (isset ($_GET['p'])) $page = $_GET['p'];
if (isset ($_GET['i'])) $image = $_GET['i'];
if (isset ($_GET['q'])) $dir = $_GET['q']; // src | thumb
if (isset ($_GET['c'])) $custom = $_GET['c']; //custom pages like /information

header("Cache-Control: max-age=86400, private, must-revalidate");
//header("Pragma: cache");
//header("Expires: " . gmdate("D, d M Y H:i:s \G\M\T", time() + 24*60*60)); // tomorrow

if (cache_get("last_poster_ip_" . $thread) === $real_ip) $poster = true;

bdl_debug ('READ REQUEST session = '. session_id().' b = ' .$board. ' t = ' .$thread. ' p = '. $page .' i = '. $image . ' q = '. $dir);

session_set_cookie_params(60 * 60 * 24 * 1000);
if (isset($_COOKIE['PHPSESSID'])){
	if (empty($_COOKIE['PHPSESSID'])){
		session_destroy();
		unset($_COOKIE['PHPSESSID']);
	}
}
session_start();

$is_mod = check_is_mod();

function user_prefs() {
	global $is_mod;

	$prefs = Array();
	foreach ($_COOKIE as $c => $val) {
		if (preg_match('~show_spoiler_[0-9]+~', $c) && $val === 'true') {
			$prefs[$c] = 1;
		}
		if ($c === 'rp' && $val === 'hided') {
			$prefs['hide_rp'] = 1;
		};
		if ($c === 'r34' && $val === '1') {
			$prefs['show_r34'] = 1;
		}
		if ($c === 'rf' && $val === '1') {
			$prefs['show_rf'] = 1;
		}
		if ($c === 'zanuda' && $val === '1') {
			$prefs['hide_multi_thumb'] = 1;
		}
		if ($c === 'dollchantrue' && $val = 'true'){
			$prefs['dollchantrue'] = 1;
		}
		if ($c === 'coma_set' && $val === 'enabled') {
			$prefs['coma'] = 1;
		};
		if ($c === 'doubledash' && $val === '1') {
			$prefs['dd'] = 1;
		};
	}

	if ($is_mod) $prefs['is_mod'] = 1;

	$prefs['session'] = md5(session_id());
	$prefs['is_reader'] = is_reader();

	return $prefs;
}

//  r34 trick
if (isset ($board)) if (!$poster && $board === 'r34') check_auth_34();
// sss trick
if (isset ($board)) if (($board === 'sss' || $board === 'dev') && !$is_mod) do_404();

//  Displaying an image
if (isset ($image) && isset($board) && isset($dir)) {
	if (!image_exists ($board, explode('.', $image)[0])) do_404();
	if (substr($image, 0, 2) === '__' && !$is_mod) do_404(); // deleted files - mods only
	show_image(KU_BOARDSDIR.'/images/'.$dir.'/'.$image);
}
//  \Displaying an image

try { // suppress exceptions

//  Displaying a thread
if (isset ($thread) && isset($board)) {

	require_once KU_ROOTDIR . 'inc/func/custom.php';
	if (($is_mod && $board == 'ipsearch') || $board == 'search') {
	} else {
		if (!thread_exists ($board, $thread) && !$is_mod) do_404(); // it's okay for mods to see deleted threads
	}

	$tpl_layer2 = $board.'_res_'.$thread.'.html';
	$tpl_layer2_file = KU_TEMPLATEDIR_2 . '/' . $board.'_res_'.$thread.'.html';

	// check if we need to send prefetch headers
	if (($headers = cache_get('prefetch_'. $board . '_' . $thread)) !== false) {
		foreach ($headers as $header) {
			header($header, false);
		}
	}

	// try to reduce traffic and speed things up by using If-Modified-Since
	$last_modified = cache_get_time($tpl_layer2_file);
	if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && ($last_modified !== false)) {
		$is_modified_since = strtotime(substr($_SERVER['HTTP_IF_MODIFIED_SINCE'], 5));
		bdl_debug('modified since - ' . $is_modified_since . ' last - ' .$last_modified);
		if ($is_modified_since && $is_modified_since >= $last_modified) {
			bdl_debug('modified hit');
			header($_SERVER['SERVER_PROTOCOL'] . ' 304 Not Modified');     
			exit;
		}
	}

	//while (!file_exists($tpl_layer2_file)) {
	while (($res = cache_get($tpl_layer2_file)) === false) {
		// so the tpl doesn't exist, lets try grabbing a lock
		$lock_key = 'pony-lock:' . $tpl_layer2; // starting with pony prefix
		$lock = $mc->add($lock_key, true, 5);

		if ($lock) { 
			// okay we have the lock, let's start
			// this thread is not in cache - need to generate it first

			// serious buisness here - no way back
			ignore_user_abort(true);
			
			require_once KU_ROOTDIR . 'inc/functions.php';
			require_once KU_ROOTDIR . 'inc/classes/board-post.class.php';
			require_once KU_ROOTDIR . 'inc/classes/bans.class.php';
			require_once KU_ROOTDIR . 'inc/classes/posting.class.php';
			require_once KU_ROOTDIR . 'inc/classes/parse.class.php';
	
			// ipsearch threads are a special case
			if ($board === 'ipsearch') {
				if (!$is_mod) do_404();
				$count = substr($thread, 0, 4);
				$boardid = substr($thread, 4, 3); 
				$ip = long2ip(substr($thread, 7)); 
				require_once KU_ROOTDIR . 'inc/classes/manage.class.php';
				$manage_class = new Manage();
				$manage_class->ipsearch($ip, board_id_to_name($boardid), $count);
			}
			
			$board_class = new Board($board);
			
			if ((substr($thread, 0, 3) === '050') || (substr($thread, 0, 3) === '005')) {
				// it's a "last 50 replies" version of thread
				$thread = substr($thread, 3);
			}
			$board_class->RegenerateThreads($thread);
			$mc->delete($lock_key);
			haanga_spaces();

			break;
	
		} else {
			// some other process is making thread stuff, we should let him finish
			usleep(250000); // 0.4 sec
		}
	}

	if (!$res) $res = cache_get($tpl_layer2_file);
	if ($res) {
		
		//load_haanga2();
		//Haanga::Load($tpl_layer2, user_prefs());
		if ($last_modified)
			header('Last-Modified: '.gmdate("D, d M Y H:i:s \G\M\T", $last_modified));
		$p = user_prefs();
		eval('?>'. $res);
		die();
	} else {
		// we still don't have this thread in cache
		//die ('problem loading layer2 template for thread.');
		do_404();
	}
}
// \Displaying a thread

//  Displaying a board
if (isset($board)) {

	require_once KU_ROOTDIR . 'inc/func/custom.php';
	if (!board_exists($board)) do_404();

	require_once KU_ROOTDIR . 'inc/functions.php';
	if (isset ($page)) {
		$pg = $page.'.html';
	} else {
		$pg = KU_FIRSTPAGE;
		$page = 0;
	}
	$tpl_layer2 = $board.'_'.$pg;
	$tpl_layer2_file = KU_TEMPLATEDIR_2 . '/' . $board.'_'.$pg;
	while (($res = cache_get($tpl_layer2_file)) === false) {
	//while (!file_exists($tpl_layer2_file)) {
		// so the tpl doesn't exist, lets try grabbing a lock
		$lock_key = 'pony-lock:' . $tpl_layer2; // starting with pony prefix
		$lock = $mc->add($lock_key, true, 5);

		if ($lock) { 
			// okay we have the lock, let's start
			// serious buisness here - no way back
			ignore_user_abort(true);

			// this board is not in cache - need to generate it first
			
			require_once KU_ROOTDIR . 'inc/functions.php';
			require_once KU_ROOTDIR . 'inc/classes/board-post.class.php';
			require_once KU_ROOTDIR . 'inc/classes/bans.class.php';
			require_once KU_ROOTDIR . 'inc/classes/posting.class.php';
			require_once KU_ROOTDIR . 'inc/classes/parse.class.php';
	
			
			$board_class = new Board($board);
			
			$board_class->RegeneratePages($page, true);
			$mc->delete($lock_key);
			haanga_spaces();

			break;
	
		} else {
			// some other process is making thread stuff, we should let him finish
			usleep(250000); // 0.4 sec
		}
	}

	if (!$res) $res = cache_get($tpl_layer2_file);
	if ($res) {

		//load_haanga2();
		//Haanga::Load($tpl_layer2, user_prefs());
		$p = user_prefs();
		eval('?>'. $res);
		die();
	} else {
		// we still don't have this thread in cache
		die ('problem loading layer2 template for board.');
	}
}

	if (isset($custom)){
		require_once KU_ROOTDIR . "inc/func/pages.php";
		$title = $custom;
		$static_page = true;
		$res = cache_get(KU_TEMPLATEDIR_2 . '/' . $title);
		if (!empty($res)){
			eval('?>'. $res);
			die();
		} else {
			//compling body of page
			load_haanga();
			Haanga::configure(array(
				'template_dir' => '/var/www/ponyach/dwoo/templates/static/',
			));
			$vars = compact('title', 'static_page');
			switch ($custom) {
				case 'information':
					$body = Haanga::Load('information.tpl', $vars, true);
					bdl_debug('custom INFORMATION CASE');
					break;
				case 'repairs':
					$body = Haanga::Load('repairs.tpl', $vars, true);
					bdl_debug('custom REPAIRS CASE');
					break;
				case 'changelog':
					$body = regen_changelog_custom();
					bdl_debug('custom CHANGELOG CASE');
					break;
				case 'index':
					$body = Haanga::Load('index.tpl', $vars, true);
					bdl_debug('custom INDEX CASE');
					break;
				case '403e':
					$title = str_replace('e', '', $custom);
					$vars = compact('title', 'static_page');
					$body = Haanga::Load('403.tpl', $vars, true);
					bdl_debug('custom 403 CASE');
					break;
				case '500e':
					$title = str_replace('e', '', $custom);
					$vars = compact('title', 'static_page');
					$body = Haanga::Load('500.tpl', $vars, true);
					bdl_debug('custom 500 CASE');
					break;
				default:
				//case '404e':
					header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
					$title = str_replace('e', '', $custom);
					$vars = compact('title', 'static_page');
					$body = Haanga::Load('404.tpl'	, $vars, true);
					bdl_debug('custom 404 CASE');
					break;
			}
			//compiling header of page
			if ($custom != 'index') $header = Haanga::Load('global_static_header.tpl', $vars, true);
			$content = $header.$body;
			print_page($title, $content, $title);
			$res = cache_get(KU_TEMPLATEDIR_2 . '/' . $title);
			eval('?>'. $res);
			die();
		}
	}

} catch (Exception $e) {
	bdl_debug ('exception caught: ' . $e->getMessage());
	do_404();
}

die ('wrong request');

?>
