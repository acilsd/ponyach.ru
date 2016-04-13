<?php

require 'config.php';
require 'inc/functions.php';
require 'inc/classes/board-post.class.php';
require 'inc/classes/bans.class.php';
require 'inc/classes/posting.class.php';
require 'inc/classes/parse.class.php';
require 'inc/classes/upload.class.php';
require_once 'inc/api/config.php';
require_once 'inc/api/debug.php';
require_once 'inc/api/cache.php';

function ponyaba_post_some_images (){
	global $board_class, $tc_db;
	
	$page = '1';
	$skey = 'ySUFGs8asnfGzDqYtfxZ';
	$tags = '(explicit+OR+suggestive+OR+questionable),score.gte:75';
	$url = 'https://derpibooru.org/search.json?q=' . $tags . '&key=' . $skey . '&page=' . $page;
	$images_json_raw = file_get_contents($url);
	
	$images = json_decode($images_json_raw, true);
	$ids = $tc_db->GetAll('SELECT * FROM (SELECT * FROM ponyaba_db_images ORDER BY id DESC LIMIT 100) sub ORDER BY id ASC');
	$images_to_post = [];
	
	for ($i = 0; $i < count($images['search']); $i++){
		$id_used = is_in_array($ids, 'imageid', $images['search'][$i]['id']);
		if ($id_used === 'no'){
			$tc_db->Execute('insert into ponyaba_db_images (imageid) values (' .$tc_db->qstr($images['search'][$i]['id']). ')');
			bdl_debug('-pdb- FILE '.$i.' found some unused id, adding it to database');
			$image_full = preg_replace('/^\/\/[a-z0-9\.]+\//', '', $images['search'][$i]['representations']['full']);
			$_POST['md5-' . ($i+1)] = '[derpi]' .base64_encode($image_full);
			array_push($images_to_post, 'md5-' . ($i+1));
			bdl_debug('-pdb- PUSHING md5-' . ($i+1));
		} else {
			bdl_debug('-pdb- id used, do not post this');
		}
	}
	bdl_debug('-pdb- have '.count($images_to_post). ' images to post');
	if (!empty($images_to_post)){
		$board_name = 'r34';
		$thread_replyto = '10812';
		$subject = '';
		$message = '';
		
		$board_class = new Board($board_name);
		$post_class = new Post(0, $board_class->board['name'], $board_class->board['id'], true);
		$upload_class = new Upload();
		$upload_class->set_isreply();
		$upload_class->HandleUpload();
		
		if (!empty($upload_class->get_file_stuff())){
		bdl_debug('-pdb- upload class said we really have some (' .$upload_class->get_file_stuff(). ') files to post');
			
			$post_id = $post_class->Insert($thread_replyto, "", "123", "", $subject, addslashes($message), "passwordstring", time(), time(), "1.1.1.1", false, "", false, false, $board_class->board['id'], $upload_class->get_file_stuff());
			$id = $tc_db->GetOne('select id from posts where boardid = 8 order by id desc limit 1');
			$tc_db->Execute('update posts set tripcode = "Поняба" where id = ' .$tc_db->qstr($id). ' and boardid = 8');
			cache_flush();
		} else {
			bdl_debug('-pdb- upload class can\'t load derpibooru image or something');
		}
	}
}

ponyaba_post_some_images();
