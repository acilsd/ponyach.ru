<?php

header('Content-type: text/html; charset=utf-8');

if (!(isset($_REQUEST['search']) || isset($_REQUEST['b']))) die ('stay out of my shed!!!1');
if (!(isset($_REQUEST['board']))) die ('stay out of my shed!!!1');

require 'config.php';
require KU_ROOTDIR . 'inc/functions.php';
require KU_ROOTDIR . 'inc/classes/board-post.class.php';

if (isset($_REQUEST['search'])) {
	if (mb_strlen($_REQUEST['search'], 'UTF-8') > 40) die ('длинноват запрос.');
	if (strpos($_REQUEST['search'], '+') === false && strpos($_REQUEST['search'], ' ') === false ) {
		$words[] = $_REQUEST['search'];
	} else {
		$words = preg_split("~[+\s]~", $_REQUEST['search'], -1, PREG_SPLIT_NO_EMPTY);

	}
}
if (isset($_REQUEST['b'])) {
	if (strpos($_REQUEST['b'], '+') === false) {
		$xwords[] = $_REQUEST['b'];
	} else {
		$xwords = preg_split("~+~", $_REQUEST['b'], -1, PREG_SPLIT_NO_EMPTY);
	}
}
$board = $_REQUEST['board'];

$board_id = $tc_db->GetOne("SELECT id FROM `".KU_DBPREFIX."boards` WHERE `name` = ".$tc_db->qstr($board)." LIMIT 1");
if (!$board_id) {
	die('Invalid board.');
}

$board_class = new Board($board);
$board_class->InitializeDwoo();

$header = $board_class->PageHeader();
$footer = $board_class->Footer();
if ($board != 'dev') {
if (isset($words)) {
	foreach ($words as $word) {
		if (mb_strlen($word, 'UTF-8') <= 32 && mb_strlen($word, 'UTF-8') > 2) {
		$i++; 
		$word = 'k.word = ' . $tc_db->qstr(mb_strtolower($word, 'UTF-8'));
		$query = "select distinct p.*, r.name as rating_name, 
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
                                                       pf.`order` as file_order from posts p left join search_index i on p.id=i.post_id and p.boardid = i.board_id left join search_keywords k on i.word_id = k.id left join posts_files pf
                                                       on p.id = pf.postid and p.boardid = pf.boardid
                                                left join ratings r 
                                                       on pf.ratingid = r.id 
                                                left join files f
                                                       on pf.fileid = f.id 
                                                left join filetypes ft
                                                       on f.type = ft.id where $word and p.boardid = $board_id and p.IS_DELETED = 0 and (pf.`order` is null or pf.`order` = 1) ORDER BY p.`id` DESC, pf.`order` asc limit 500;";
		bdl_debug("searching $query");
		$posts_part = $tc_db->GetAll($query);
		bdl_debug(count($posts_part));
		if (count($words) > 1) {
//			bdl_debug (count($posts));
			if ($i == 1) {
bdl_debug('initial stuff');
				$posts = $posts_part;
			} else {
bdl_debug('intersect stuff');
				$intersect = array_uintersect($posts, $posts_part, function ($val1, $val2) {
											return strcmp($val1['id'], $val2['id']);
				});
				bdl_debug("int: ". count($intersect));
				$posts = $intersect;
			}
		} else {
			$posts = $posts_part;
		}
		}
	}
}
}
$t = count($posts);

$page = '';
$top_post['tripcode'] = 'поняба';
$top_post['thumb_w'] = 290;
$top_post['thumb_h'] = 181;
$top_post['image_w'] = 1920;
$top_post['image_h'] = 1200;
$top_post['file_name'] = 140086058578;
$top_post['file_type'] = 'jpg';
$top_post['timestamp'] = time();
$top_post['subject'] = 'Результаты поиска';
$top_post['file_original'] = 'поняша';
$top_post['file_size'] = 0;
$top_post['file_size_formatted'] = '42B';
	//$top_post['
if ($posts) {
	$top_post['message'] = 'Я нашла ' .$t;
	if ((($t % 10) === 1) && ($t !== 11) && (($t % 100) !== 11)) {
		$top_post['message'] .= ' пост';
	} else if (((($t % 10) === 2) || (($t % 10) === 3) || (($t % 10) === 4)) && (($t != 12) && ($t != 13) && ($t != 14))) {
		$top_post['message'] .= ' поста';
	} else {
		$top_post['message'] .= ' постов';
	}
	
	$top_post['message'] .= ' в /'.$board.'/.<br>';
	array_unshift($posts, $top_post);
	bdl_debug("found ". count($posts) . " posts");
	foreach ($posts as $key => $post) {
		$posts[$key] = $board_class->BuildPost($post, false);
	}
} else {
	$top_post['message'] = 'Я ничего не нашла';
	$top_post['message'] .= ' в /'.$board.'/.<br>';
	array_unshift($posts, $top_post);
}	
$posts = $board_class->CompactPosts($posts);

$board_class->dwoo_data['board'] = $board_class->board;
$board_class->dwoo_data['isread'] = true;
$board_class->dwoo_data['file_path'] = getCLBoardPath($board_class->board['name'], $board_class->board['loadbalanceurl_formatted'], '');
$board_class->dwoo_data['posts'] = $posts;
$page = Haanga::Load( 'img_thread.tpl', $board_class->dwoo_data, true);

$thread_id = rand(1, 10000);

$postbox = $board_class->Postbox();
$postbox = str_replace("<!sm_threadid>", $thread_id, $postbox);

print_page('search_res_'.$thread_id. '.html', $header. $postbox. $page. $footer, 'search');
header('Location: '. '/search/res/'. $thread_id. '.html');
die();

?>
