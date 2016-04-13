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
 * Board and Post classes
 *
 * @package kusaba
 */
/**
 * Board class
 *
 * Contains all board configurations.  This class handles all board page
 * rendering, using the templates
 *
 * @package kusaba
 *
 * TODO: replace repetitive code blocks with functions.
 */
class Board {
	/* Declare the public variables */
	/**
	 * Array to hold the boards settings
	 */
	var $board = array();
	/**
	 * Archive directory, set when archiving is enabled
	 *
	 * @var string Archive directory
	 */
	var $archive_dir;
	/**
	 * Dwoo data class
	 *
	 * @var class Dwoo
	 */
	var $dwoo = false;
	var $dwoo_data;
	/**
	 * Initialization function for the Board class, which is called when a new
	 * instance of this class is created. Takes a board directory as an
	 * argument
	 *
	 * @param string $board Board name/directory
	 * @param boolean $extra grab additional data for page generation purposes. Only false if all that's needed is the board info.
	 * @return class
	 */
	function Board($board, $extra = true) {
		global $h, $cf, $tc_db, $CURRENTLOCALE;

		// If the instance was created with the board argument present, get all of the board info and configuration values and save it inside of the class
		bdl_debug ("constructor of board " . $board);
		if ($board != '') {
			if (!board_exists($board)) 
				throw new Exception('board does not exist.');

			$query = "SELECT * FROM `".KU_DBPREFIX."boards` WHERE `name` = ".$tc_db->qstr($board)." LIMIT 1";
			$results = $tc_db->GetAll($query);
			foreach ($results[0] as $key=>$line) {
				if (!is_numeric($key)) {
					//bdl_debug ($key . " => ".$line);
					$this->board[$key] = $line;
				}
			}
			// Rating settings
			$query = "SELECT br.ratingid, r.name FROM ".KU_DBPREFIX."board_ratings br INNER JOIN ratings AS r on br.ratingid = r.id WHERE br.`boardid` = ".$tc_db->qstr($this->board['id']);
			$results = $tc_db->GetAll($query);
			foreach ($results as $lnum => $line) {
				$this->board['ratings'][ $line['ratingid'] ] = $line['name'];
			}
			
			// Type
			$types = array('img', 'txt', 'oek', 'upl');
			$this->board['text_readable'] = $types[0];
			if ($extra) {
				// Boardlist
				$this->board['boardlist'] = $this->DisplayBoardList();

				// Get the unique posts for this board
				//$this->board['uniqueposts']   = $tc_db->GetOne("SELECT COUNT(DISTINCT `ipmd5`) FROM `" . KU_DBPREFIX . "posts` WHERE `boardid` = " . $this->board['id']. " AND  `IS_DELETED` = 0");
			
				$this->board['filetypes_allowed'] = $tc_db->GetAll("SELECT ".KU_DBPREFIX."filetypes.filetype FROM ".KU_DBPREFIX."boards, ".KU_DBPREFIX."filetypes, ".KU_DBPREFIX."board_filetypes WHERE ".KU_DBPREFIX."boards.id = " . $this->board['id'] . " AND ".KU_DBPREFIX."board_filetypes.boardid = " . $this->board['id'] . " AND ".KU_DBPREFIX."board_filetypes.typeid = ".KU_DBPREFIX."filetypes.id ORDER BY ".KU_DBPREFIX."filetypes.filetype ASC;");
				
				if ($this->board['locale'] && $this->board['locale'] != KU_LOCALE) {
					changeLocale($this->board['locale']);
				}
			}
		}
	}

	function __destruct() {
		changeLocale(KU_LOCALE);
	}
	
	/**
	 * Regenerate all board and thread pages
	 */
	function RegenerateAll() {
		bdl_debug ('RegenerateAll()');
		//$this->RegeneratePages();
		//$this->RegenerateThreads();
		unlink_all(KU_TEMPLATEDIR_2);
		unlink_all(KU_TEMPLATEDIR_2c);
	}

	/**
	 * Regenerate all pages
	 */
	function RegeneratePages($up_to_page = false, $one_page_only = false) {
		global $h, $cf, $tc_db, $CURRENTLOCALE, $mc;

		$to_debug = 'RegeneratePages ';
		
		$this->InitializeDwoo();
		$results = $tc_db->GetAll("SELECT `filetype` FROM `" . KU_DBPREFIX . "embeds`");
		foreach ($results as $line) {
			$this->board['filetypes'][] .= $line[0];
		}
		$this->dwoo_data['filetypes'] = $this->board['filetypes'];
		if (isset($this->board['ratings'])){
			$this->dwoo_data['board_ratings'] = $this->board['ratings'];
		}
		$maxpages = $this->board['maxpages'];
		$numposts = $tc_db->GetAll("SELECT COUNT(*) FROM `" . KU_DBPREFIX . "posts` WHERE `boardid` = " . $this->board['id'] . " AND `parentid` = 0 AND `IS_DELETED` = 0");

		$postsperpage = KU_THREADS;
		$i = 0;
		$liststooutput = 0;
		$totalpages = calculatenumpages(0, ($numposts[0][0]-1));
		if ($totalpages == '-1') {
			$totalpages = 0;
		}

		for ($x = 0; $x < $totalpages; $x++) {
			$numpages[] = $x;
		}

		if ($up_to_page === false) $up_to_page = 0;
//
//		if ($one_page_only) {
//			bdl_debug ('regenerating only ' . $up_to_page);
//		} else {
//			bdl_debug ('regenerating pages up to ' .  $up_to_page);
//		}
	
		$this->dwoo_data['numpages'] = $numpages;

		$i = $up_to_page;
		//while ($i <= $up_to_page) {
			if ((!$one_page_only) || ($i == $up_to_page)) {

			if ($i == 0) {
				$page = $this->board['name'].'/'.KU_FIRSTPAGE;
			} else {
				$page = $this->board['name'].'/'.$i.'.html';
			}

			// for real we only regenerate the target page, but if we are asked to regenerate all pages we drop caches, so
			// missing pages will be generated lazyly
			if ($i != $up_to_page) {
				@unlink($filename = KU_TEMPLATEDIR_2 . '/' . str_replace('/', '_', $page));
				$i++; continue;
			}

			if (is_cli()) {
				echo "$i ";
				flush();
			}
			$to_debug .= "$i ";
			$newposts = Array();
			$this->dwoo_data['thispage'] = $i;
			$this->dwoo_data['prevpage'] = $i - 1;
			$this->dwoo_data['nextpage'] = $i + 1;

			// ERROR 1235 (42000): This version of MariaDB doesn't yet support 'LIMIT & IN/ALL/ANY/SOME subquery'
			// thats why there are two queries here
			bdl_debug('getting opposts');
			$mc_key = 'board_opposts_' . $this->board['name'] . '_' . $i;
			if (false === ($threads = cache_get ($mc_key))) {
				bdl_debug('getting threads');
				$threads = $tc_db->GetAll("SELECT id FROM posts WHERE `boardid` = " . $this->board['id'] . "  AND `parentid` = 0 AND `IS_DELETED` = 0 ORDER BY `stickied` DESC, `bumped` DESC LIMIT ". ($postsperpage)." OFFSET ". $postsperpage * $i);
				if (!$threads)
					do_404();
				$in = Array();
				foreach ($threads as $thread) {
					$in[] = $thread['id'];
				}
				$threads = implode(',', $in);
				bdl_debug('no cache - db');
				$query = "
					select p.IS_DELETED, 
					       p.*, 
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
	                                where p.`boardid` = " . $this->board['id'] . " 
					       and p.id in (" . $threads . ")
	                                order by p.stickied desc, p.bumped desc, pf.`order` asc";
				$threads = $tc_db->GetAll($query);
				//bdl_debug($query);
				cache_add($mc_key, $threads);
			}

			bdl_debug('compacting opposts');
			$threads = $this->CompactPosts($threads);

			//$threads = $tc_db->GetAll("SELECT * FROM `" . KU_DBPREFIX . "posts` WHERE `boardid` = " . $this->board['id'] . " AND `parentid` = 0 AND `IS_DELETED` = 0 ORDER BY `stickied` DESC, `bumped` DESC LIMIT ". ($postsperpage)." OFFSET ". $postsperpage * $i);

			$executiontime_start_page = microtime_float();
			foreach ($threads as $k=>$thread) {
				bdl_debug('getting thread posts, thread - '. $thread['id']);
//bdl_debug ("thread = ". $thread['id'] . " files = " . count($thread['files']) .  " op_w = " . $thread['files'][0]['image_w'] . " name = " . $thread['files'][0]['name']);
				// If the thread is on the page set to mark, && hasn't been marked yet, mark it
				if ($thread['deleted_timestamp'] == 0 && $this->board['markpage'] > 0 && $i >= $this->board['markpage']) {
					$tc_db->Execute("UPDATE `".KU_DBPREFIX."posts` SET `deleted_timestamp` = '" . (time() + 7200) . "' WHERE `boardid` = " . $tc_db->qstr($this->board['id'])." AND `id` = '" . $thread['id'] . "'");
					$this->RegenerateThreads($thread['id']);
					$this->dwoo_data['replythread'] = 0;
				}
				$thread = $this->BuildPost($thread, true);
				
//bdl_debug ("thread = ". $thread['id'] . " files = " . count($thread['files']) .  " op_w = " . $thread['files'][0]['image_w'] . " name = " . $thread['files'][0]['name']);

				$mc_key = 'thread_lastposts_' . $this->board['name']. '_' . $thread['id'];


				$report_thread = false;
				if ($thread['premod'] == 2) $report_thread = true;
					if (false === ($posts = cache_get ($mc_key))) {
						bdl_debug('no cache - db');
						$posts = $tc_db->GetAll("
							select p.IS_DELETED, 
							       p.*, 
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
			                                where p.`boardid` = " . $this->board['id'] . " 
							       and p.parentid = ".$thread['id']." AND p.`IS_DELETED` = 0 ORDER BY p.`id` DESC, pf.`order` asc LIMIT ".(($thread['stickied'] == 1) ? (KU_REPLIESSTICKY) : (KU_REPLIES)));
						 cache_add ($mc_key, $posts);
					}

				bdl_debug('compacting posts');
				$posts = $this->CompactPosts($posts);

				bdl_debug('building posts');
				foreach ($posts as $key=>$post) {
					$posts[$key] = $this->BuildPost($post, true, $report_thread);
				}

				$posts = array_reverse($posts);
				array_unshift($posts, $thread);
				end($posts);
				$posts[key($posts)]['last_post'] = true;
//bdl_debug ("thread = ". $newpo0]['id'] . " files = " . count($newposts[$k][0]['files']) .  " op_w = " . $newposts[$k][0]['files'][0]['image_w'] . " name = " . $thread['files'][0]['name']);
				$replycount = Array();
				$mc_key = 'replycount_' . $this->board['name']. '_' .$thread['id'];
				if (false === ($replycount = cache_get ($mc_key))) {
					bdl_debug('no cache - db');
					$query = "select count(distinct p.id) as posts, count(distinct pf.fileid) as files from posts p left join posts_files pf on p.id = pf.postid and p.boardid = pf.boardid left join (select id from posts where boardid = " . $this->board['id'] . " and parentid = ".$thread['id']." order by timestamp desc limit ".(($thread['stickied'] == 1) ? (KU_REPLIESSTICKY) : (KU_REPLIES)).") t2 on p.id = t2.id where t2.id is null and p.parentid = ".$thread['id']." and p.`boardid` = " . $this->board['id'] . " and p.`IS_DELETED` = 0";
					//bdl_debug($query);
					bdl_debug('getting images and skipped posts count');
					$replycount = $tc_db->GetAll($query);
				 	cache_add ($mc_key, $replycount);
				}
				$posts[0]['replies'] = $replycount[0][0];
				$posts[0]['images'] = (isset($replycount[0][1]) ? $replycount[0][1] : '');
				$newposts = array_merge($newposts, $posts);
			}
			if (!isset($embeds)) {
				$embeds = $tc_db->GetAll("SELECT * FROM `" . KU_DBPREFIX . "embeds`");
				$this->dwoo_data['embeds'] = $embeds;
			}
			if (!isset($header)){
				$header = $this->PageHeader();
				$header = str_replace("<!sm_threadid>", 0, $header);
			}
			if (!isset($postbox)) {
				$postbox = $this->Postbox();
				$postbox = str_replace("<!sm_threadid>", 0, $postbox);
			}
			$this->dwoo_data['posts'] = $newposts;
			$this->dwoo_data['file_path'] = KU_BOARDSCORAL . '/' . $this->board['name'];

			$this->dwoo_data['cf'] = $cf;
			$this->dwoo_data['h'] = $h;

			bdl_debug('loading template');
			$content = Haanga::Load($this->board['text_readable'] . '_board_page.tpl', $this->dwoo_data, true);
			$footer = $this->Footer(false, (microtime_float() - $executiontime_start_page), false);
			$content = $header.$postbox.$content.$footer;

			$content = str_replace("\t", '',$content);
			$content = str_replace("&nbsp;\r\n", '&nbsp;',$content);

			bdl_debug('printing page');
			$this->PrintPage($page, $content, $this->board['name']);

			}
		//	$i++;
		//}
		bdl_debug($to_debug);
	}

	/**
	 * Regenerate each thread's corresponding html file, starting with the most recently bumped
	 */
	function RegenerateThreads($id = 0) {
		global $h, $cf, $tc_db, $CURRENTLOCALE, $done_firstlast;
		bdl_debug ('RegenerateThreads id = '. $id);

		//if (!isset($this->dwoo)) { $this->dwoo = New Dwoo; $this->dwoo_data = new Dwoo_Data(); $this->InitializeDwoo(); }
		$this->InitializeDwoo();
		$embeds = Array();
		$numimages = 0;
				$embeds = $tc_db->GetAll("SELECT * FROM `" . KU_DBPREFIX . "embeds`");
				$this->dwoo_data['embeds'] = $embeds;
				foreach ($embeds as $embed) {
					$this->board['filetypes'][] .= $embed['filetype'];
				}
				$this->dwoo_data['filetypes'] = $this->board['filetypes'];
		if (isset($this->board['ratings'])){
			$this->dwoo_data['board_ratings'] = $this->board['ratings'];
		}
		$header = $this->PageHeader(1);
		$postbox = $this->Postbox(1);
		
//		if ($id == 0) {
//			// Build every thread
//			$threads = $tc_db->GetAll("SELECT * FROM `" . KU_DBPREFIX . "posts` WHERE `boardid` = " . $this->board['id'] . " AND `parentid` = 0 AND `IS_DELETED` = 0 ORDER BY `id` DESC");
//		} else {
			// Build only that thread
			$threads = $tc_db->GetAll("SELECT * FROM `" . KU_DBPREFIX . "posts` WHERE `boardid` = " . $this->board['id'] . " AND `parentid` = 0 AND `IS_DELETED` = 0  and id = ". $tc_db->qstr($id) ."ORDER BY `id` DESC");
//		}

		if (count($threads) > 0) {
			foreach($threads as $thread) {
				if (is_cli()) {
					echo ".";
					flush();
				}
				
				$report_thread = false;
				if ($thread['premod'] == 2) $report_thread = true;

				$numimages = 0;
				$executiontime_start_thread = microtime_float();
				$posts = $tc_db->GetAll("
					select p.IS_DELETED, 
					       p.*, 
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
                                        where p.`boardid` = " . $this->board['id'] . " 
					       and (p.`id` = " . $thread['id'] . " or p.`parentid` = " . $thread['id'] . ") 
                                        order by p.`id`, pf.`order` asc");

				$posts = $this->CompactPosts($posts);
				if (((isset($posts[0]['IS_DELETED']) && $posts[0]['IS_DELETED'] == 0) || (isset($posts[0]['is_deleted']) && $posts[0]['is_deleted'] == 0))) { 
					// There might be a chance that the post was deleted during another RegenerateThreads() session, if there are no posts, move on to the next thread.
					if(count($posts) > 0){
						foreach ($posts as $key=>$post) {
							if ($post['IS_DELETED'] == 0) {
								$numposts++;
								$numimages += $post['file_count'];
							}
							$posts[$key] = $this->BuildPost($post, false, $report_thread);
						}

						$header_replaced = str_replace("<!sm_threadid>", $thread['id'], $header);
						$this->dwoo_data['numimages'] = $numimages;
						$this->dwoo_data['replythread'] = $thread['id'];
						$this->dwoo_data['posts'] = $posts;
						$this->dwoo_data['file_path'] = KU_BOARDSCORAL . '/' . $this->board['name'];
						$postbox_replaced = str_replace("<!sm_threadid>", $thread['id'], $postbox);
						//$reply	 = $this->dwoo->get($this->board['text_readable'] . '_reply_header.tpl', $this->dwoo_data);
						$this->dwoo_data['cf'] = $cf;
						$this->dwoo_data['h'] = $h;
						$reply = Haanga::Load($this->board['text_readable'] . '_reply_header.tpl', $this->dwoo_data, true);
						//$content = $this->dwoo->get($this->board['text_readable'] . '_thread.tpl', $this->dwoo_data);
						$content = Haanga::Load($this->board['text_readable'] . '_thread.tpl', $this->dwoo_data, true);

						if (!isset($footer)) $footer = $this->Footer(false, (microtime_float() - $executiontime_start_thread), false);
						$content = $header_replaced.$reply.$postbox_replaced.$content.$footer;

						$content = str_replace("\t", '',$content);
						$content = str_replace("&nbsp;\r\n", '&nbsp;',$content);

						$this->PrintPage($this->board['name'] . $this->archive_dir . '/res/' . $thread['id'] . '.html', $content, $this->board['name']);

						if (KU_FIRSTLAST) {
							$replycount = ($numposts-1);
							if ($replycount > 50) {
								$done_firstlast = true;
								$this->dwoo_data['replycount'] = $replycount;
								$this->dwoo_data['modifier'] = "last50";
								// Grab the last 50 replies
								$posts50 = array_slice($posts, -50, 50);
								// Add on the OP
								array_unshift($posts50, $posts[0]);
								$this->dwoo_data['posts'] = $posts50;
								//$content = $this->dwoo->get(KU_TEMPLATEDIR . '/img_thread.tpl', $this->dwoo_data);
								$content = Haanga::Load('img_thread.tpl', $this->dwoo_data, true);
								
								$content = $header_replaced.$reply.$postbox_replaced.$content.$footer;
								$content = str_replace("\t", '',$content);
								$content = str_replace("&nbsp;\r\n", '&nbsp;',$content);
								unset($posts50);
								$this->PrintPage($this->board['name'] . $this->archive_dir . '/res/050' . $thread['id'] . '.html', $content, $this->board['name']);
								$this->dwoo_data['modifier'] = "";
							}

							if ($replycount > 5) {
								//$done_firstlast = true;
								$this->dwoo_data['replycount'] = $replycount;
								$this->dwoo_data['modifier'] = "last5";
								// Grab the last 50 replies
								$posts5 = array_slice($posts, -5, 5);
								// Add on the OP
								array_unshift($posts5, $posts[0]);
								$this->dwoo_data['posts'] = $posts5;
								//$content = $this->dwoo->get(KU_TEMPLATEDIR . '/img_thread.tpl', $this->dwoo_data);
								$content = Haanga::Load('img_thread.tpl', $this->dwoo_data, true);
								
								$content = $header_replaced.$reply.$postbox_replaced.$content.$footer;
								$content = str_replace("\t", '',$content);
								$content = str_replace("&nbsp;\r\n", '&nbsp;',$content);
								unset($posts5);
								$this->PrintPage($this->board['name'] . $this->archive_dir . '/res/005' . $thread['id'] . '.html', $content, $this->board['name']);
								$this->dwoo_data['modifier'] = "";
							}
						}
					}
				}
			}
		}
		if (is_array($posts)) {
			$last = end($posts);
			bdl_debug ("lastpost id = " . $last['id']);
		}
	}

	function CompactPosts($mposts) {
		// here we need to compact posts array, since after the holy joins posts with multiple files appear as multiple posts.

		$posts = Array();
		$previd = -1;
		$i = 0;
		foreach ($mposts as $post) {
			if ($post['file_name']) {
			       $file_info = Array();
			       $file_info['thumb_w'] = $post['thumb_w'];
			       $file_info['thumb_h'] = $post['thumb_h'];
			       $file_info['image_w'] = $post['image_w'];
			       $file_info['image_h'] = $post['image_h'];
			       $file_info['rating_id'] = $post['rating_id'];
			       $file_info['rating_name'] = $post['rating_name'];
			       $file_info['rating_file'] = $post['rating_file'];
			       $file_info['rating_thumb_w'] = $post['rating_thumb_w'];
			       $file_info['rating_thumb_h'] = $post['rating_thumb_h'];
			       $file_info['name'] = $post['file_name'];
			       $file_info['type'] = $post['file_type'];
			       $file_info['original'] = $post['file_original'];
			       $file_info['size'] = $post['file_size'];
			       $file_info['size_formatted'] = $post['file_size_formatted'];
			       if ($post['file_type'] == 'webm') {
			       		$file_info['thumb_type'] = 'png';
		             } else {
			       		$file_info['thumb_type'] = $post['file_type'];
				}


					// one great day i will test if it is faster to unset shit or not.
			       		unset($post['thumb_w']);
			       		unset($post['thumb_h']);
			       		unset($post['image_w']);
			       		unset($post['image_h']);
					unset($post['rating_id']);
					unset($post['rating_file']);
					unset($post['rating_thumb_w']);
					unset($post['rating_thumb_h']);
					unset($post['file_name']);
					unset($post['file_type']);
					unset($post['file_original']);
					unset($post['file_size']);
					unset($post['file_size_formatted']);

				if ($post['id'] === $previd) {
					// this is a second/third/... image for the previous post
					$posts[$i-1]['files'][$post['file_order']] = $file_info;
					$posts[$i-1]['file_count']++;
				} else {
					$post['file_count'] = 1;
					$post['files'][$post['file_order']] = $file_info;

					$posts[$i] = $post;
					$i++;
					
				}
			} else {
				$post['file_count'] = 0;
				$posts[$i] = $post;
				$i++;
			}

			$previd = $post['id'];
			//bdl_debug ("postid = " . $post['id'] . " files = ". $post['file_count']);
		}
//		if ($previd > -1) {
//			$posts['firstfile'] = $posts[0];
//		}
		return $posts;
	}

	function BuildPost($post, $page, $visible_by_respondents = false) {
		global $h, $cf, $CURRENTLOCALE, $tc_db;
		$dateEmail = (empty($this->board['anonymous'])) ? $post['email'] : 0;

		$post['premod_before'] = '';
		$post['premod_after'] = '';

		//2007
		//$post['timestamp'] = $post['timestamp'] - 8*365*24*60*60 - 2*24*60*60;

		if ($post['premod'] != 0) {
			$post_visible_by = Array($post['session_md5']);
			if ($visible_by_respondents && $post['email'] !== 'sage') {
				$query = 'select reply_md5 from posts_replies where boardid = '. $tc_db->qstr($post['boardid']) .' and postid = '. $tc_db->qstr($post['id']);
				$res = $tc_db->GetAll($query);
				if ($res) {
					foreach($res as $row) {
						$post_visible_by[] = $row['reply_md5'];
					}
				}
			}

			$post['premod_before'] = '<?php if ($p[\'is_mod\'] ';
			foreach ($post_visible_by as $md5) {
				$post['premod_before'] .= ' || $p[\'session\'] === \''.$md5.'\' ';
			}

			$post['premod_before'] .= ') { ?>';
			$post['premod_after'] = '<?php } ?>';

//			bdl_debug ($post['premod_before']);
		}

		$post['message'] = stripslashes(formatLongMessage($post['message'], $this->board['name'], (($post['parentid'] == 0) ? ($post['id']) : ($post['parentid'])), $page));
		$post['message'] = str_replace("<!sm_postid>", $post['id'], $post['message']);
		$post['timestamp_formatted'] = formatDate($post['timestamp'], 'post', $CURRENTLOCALE, $dateEmail);
		$post['reflink'] = formatReflink($this->board['name'], (($post['parentid'] == 0) ? ($post['id']) : ($post['parentid'])), $post['id'], $CURRENTLOCALE);
		$post['replylink'] = formatReplylink($this->board['name'], (($post['parentid'] == 0) ? ($post['id']) : ($post['parentid'])), $post['id'], $CURRENTLOCALE);
		if (is_array($post['files']))
//			if (count($post['files']) > 1) {
//				foreach($post['files'] as $xfile)
//					$ttl += $xfile['thumb_height'];
//				$post_thumb_height = ceil($ttl/count($post['files']));
//			} else {
//				$post_thumb_height = KU_REPLYTHUMBHEIGHT;
//			}

			foreach ($post['files'] as &$file) {
				$file['original'] = trim($file['original']);
				if (! $file['original'])
					$file['original'] = $file['name'];

				$file['url'] = '/' . $this->board['name'] . '/src/'. $file['name'] . '/'  . xurlencode($file['original']) . '.' . $file['type'];
				if ($post['parentid'] == 0) {
					$thumb_height = KU_THUMBHEIGHT ; $thumb_width = KU_THUMBWIDTH;
				} else {
					// in regular posts with many files we line them my heigth. just cause.
					$thumb_height = KU_REPLYTHUMBHEIGHT ; $thumb_width = KU_REPLYTHUMBWIDTH;
//					if ($thumb_height > $post_thumb_height) 
//						$thumb_height = $post_thumb_height;
				}

				if ($file['thumb_w'] > $thumb_width || $file['thumb_h'] > $thumb_height) {
					if ($file['thumb_w'] > $file['thumb_h']) {
						$rate = $file['thumb_h'] / $file['thumb_w'];
						$file['thumb_w'] = $thumb_width;
						$file['thumb_h'] = ceil($file['thumb_h'] * $rate);
					} else {
						$rate = $file['thumb_w'] / $file['thumb_h'];
						$file['thumb_h'] = $thumb_height;
						$file['thumb_w'] = ceil($thumb_height * $rate);
					}
				}
			}
		if ($post['repost_of']) {
			$query = "select parentid from posts where boardid = ". $tc_db->qstr($post['boardid']) . " and id = ". $tc_db->qstr($post['repost_of']);
			//bdl_debug($query);
			$post['repost_thread'] = $tc_db->GetOne($query);
		}

		if ($post['name']) {
			$post['coma'] = genColorCodeFromText($post['name']);
		} else {
			$post['coma'] = 'transparent';
		}
		return $post;
	}
	
	/**
	 * Build the page header
	 *
	 * @param integer $replythread The ID of the thread the header is being build for.  0 if it is for a board page
	 * @param integer $liststart The number which the thread list starts on (text boards only)
	 * @param integer $liststooutput The number of list pages which will be generated (text boards only)
	 * @return string The built header
	 */
	function PageHeader($replythread = '0', $liststart = '0', $liststooutput = '-1') {
		global $h, $cf, $tc_db, $CURRENTLOCALE;

		$tpl = Array();

		$tpl['htmloptions'] = ((KU_LOCALE == 'he' && empty($this->board['locale'])) || $this->board['locale'] == 'he') ? ' dir="rtl"' : '' ;

		$tpl['title'] = '';

		if (KU_DIRTITLE) {
			$tpl['title'] .= '/' . $this->board['name'] . '/ - ';
		}
		$tpl['title'] .= $this->board['desc'];

		$ad_top = 185;
		$ad_right = 25;
		if ($replythread!=0) {
			$ad_top += 50;
		}
		$this->dwoo_data['title'] = $tpl['title'];
		$this->dwoo_data['htmloptions'] = $tpl['htmloptions'];
		$this->dwoo_data['locale'] = $CURRENTLOCALE;
		$this->dwoo_data['ad_top'] = $ad_top;
		$this->dwoo_data['ad_right'] = $ad_right;
		$this->dwoo_data['board'] = $this->board;
		$this->dwoo_data['replythread'] = $replythread;
		$topads = $tc_db->GetOne("SELECT code FROM `" . KU_DBPREFIX . "ads` WHERE `position` = 'top' AND `disp` = '1'");
		$this->dwoo_data['topads'] = $topads;
		$this->dwoo_data['boardlist'] = $this->board['boardlist'];

			$this->dwoo_data['cf'] = $cf;
			$this->dwoo_data['h'] = $h;
		$global_header = Haanga::Load('global_board_header.tpl', $this->dwoo_data, true);

		$header = Haanga::Load($this->board['text_readable'] . '_header.tpl', $this->dwoo_data, true);

		return $global_header.$header;
	}

	/**
	 * Generate the postbox area
	 *
	 * @param integer $replythread The ID of the thread being replied to.  0 if not replying
	 * @param string $postboxnotice The postbox notice
	 * @return string The generated postbox
	 */
	function Postbox($replythread = 0) {
		global $h, $cf, $tc_db, $board_class;
		$this->dwoo_data['replythread'] = $replythread;
		$postbox = '';

		$file_ids = Array();
		for ($x = 1; $x <= $board_class->board['maximages']; $x++) {
			$file_ids[] = $x;
		}

		if ($this->board['enablecaptcha'] ==  1) {
			$madness='<div id="lok" onload="capture_ld()"><script> capture_ld(); </script> </div>';
			require_once(KU_ROOTDIR.'recaptchalib.php');
			$publickey = "6LdVg8YSAAAAAOhqx0eFT1Pi49fOavnYgy7e-lTO";
			$madness.='<div id="cpt" visible="false" style="display:none;" hidden">'.recaptcha_get_html($publickey).'</div>';
			$this->dwoo_data['recaptcha'] = $madness;
		} 
		if ($this->board['enablecaptcha'] ==  2) {
			$this->dwoo_data['recaptcha'] = 'Вам не нужно вводить капчу.';
		}
			$this->dwoo_data['cf'] = $cf;
			$this->dwoo_data['h'] = $h;
			$this->dwoo_data['file_ids'] = $file_ids;
		$postbox .= Haanga::Load($this->board['text_readable'] . '_post_box.tpl', $this->dwoo_data, true);
		return $postbox;
	}

	/**
	 * Display the user-defined list of boards found in boards.html
	 *
	 * @param boolean $is_textboard If the board this is being displayed for is a text board
	 * @return string The board list
	 */
	function DisplayBoardList($is_textboard = false) {
		if (KU_GENERATEBOARDLIST) {
			global $h, $cf, $tc_db;

			$output = '';
			$results = $tc_db->GetAll("SELECT `id` FROM `" . KU_DBPREFIX . "sections` ORDER BY `order` ASC");
			$boards = array();
			foreach($results AS $line) {
				$results2 = $tc_db->GetAll("SELECT * FROM `" . KU_DBPREFIX . "boards` WHERE `section` = '" . $line['id'] . "' ORDER BY `order` ASC, `name` ASC");
				foreach($results2 AS $line2) {
					$boards[$line['id']][$line2['id']]['name'] = htmlspecialchars($line2['name']);
					$boards[$line['id']][$line2['id']]['desc'] = htmlspecialchars($line2['desc']);
				}
			}
		} else {
			$boards = KU_ROOTDIR . 'boards.html';
		}

		return $boards;
	}


	/**
	 * Display the page footer
	 *
	 * @param boolean $noboardlist Force the board list to not be displayed
	 * @param string $executiontime The time it took the page to be created
	 * @param boolean $hide_extra Hide extra footer information, and display the manage link
	 * @return string The generated footer
	 */
	function Footer($noboardlist = false, $executiontime = '', $hide_extra = false) {
		global $h, $cf, $tc_db, $dwoo, $dwoo_data;

		$footer = '';

		if ($hide_extra || $noboardlist) $this->dwoo_data['boardlist'] = '';

		if ($executiontime != '') $this->dwoo_data['executiontime'] = round($executiontime, 2);
		
		$this->dwoo_data['cf'] = $cf;
			$this->dwoo_data['h'] = $h;
		$footer = Haanga::Load($this->board['text_readable'] . '_footer.tpl', $this->dwoo_data, true);
		
		$footer .= Haanga::Load('global_board_footer.tpl', $this->dwoo_data, true);

		return $footer;
	}

	/**
	 * Finalize the page and print it to the specified filename
	 *
	 * @param string $filename File to print the page to
	 * @param string $contents Page contents
	 * @param string $board Board which the file is being generated for
	 * @return string The page contents, if requested
	 */
	function PrintPage($filename, $contents, $board) {

		if ($board !== true) {
			print_page($filename, $contents, $board);

		//	load_haanga2();
		//        $tpl = Haanga::getTemplatePath(str_replace('/', '_', $filename));
//			$tpl = KU_TEMPLATEDIR_2 . '.' . str_replace('/', '_', $filename);
//       			$fnc = sha1($tpl);
//			$layer_2 = KU_CACHEDTEMPLATEDIR_2. '/' .$fnc. '.php';
//			bdl_debug ('will try unlink level 2 - ' . $layer_2);
//			if (file_exists($layer_2)) {
//				unlink($layer_2);
//				bdl_debug ('unlinked layer 2 template - ' . $filename . ' - ' . $layer_2);
//			}
		//	load_haanga();
		} else {
			eval ( '?>'. $contents );
		}
	}

	/**
	 * Initialize the instance of smary which will be used for generating pages
	 */
	function InitializeDwoo() {
		if (!$this->dwoo) {
			load_haanga();
			$this->dwoo_data['cwebpath'] = getCWebpath();
			$this->dwoo_data['boardpath'] = getCLBoardPath();
		}
		$this->dwoo = true;
	}

	/**
	 * Enable/disable archive mode
	 *
	 * @param boolean $mode True/false for enabling/disabling archive mode
	 */
	function ArchiveMode($mode) {
		$this->archive_dir = ($mode && $this->board['enablearchiving'] == 1) ? '/arch' : '';
	}
}

/**
 * Post class
 *
 * Used for post insertion, deletion, and reporting.
 *
 * @package kusaba
 */
class Post extends Board {
	// Declare the public variables
	var $post = Array();

	function Post($postid, $board, $boardid, $is_inserting = false) {
		global $h, $cf, $tc_db;

		$results = $tc_db->GetAll("SELECT * FROM `".KU_DBPREFIX."posts` WHERE `boardid` = '" . $boardid . "' AND `id` = ".$tc_db->qstr($postid)." LIMIT 1");
		if (count($results)==0&&!$is_inserting) {
			exitWithErrorPage('Invalid post ID.');
		} elseif ($is_inserting) {
			$this->Board($board, false);
		} else {
			foreach ($results[0] as $key=>$line) {
				if (!is_numeric($key)) $this->post[$key] = $line;
			}
			$results = $tc_db->GetAll("SELECT `cleared` FROM `".KU_DBPREFIX."reports` WHERE `postid` = ".$tc_db->qstr($this->post['id'])." LIMIT 1");
			if (count($results)>0) {
				foreach($results AS $line) {
					$this->post['isreported'] = ($line['cleared'] == 0) ? true : 'cleared';
				}
			} else {
				$this->post['isreported'] = false;
			}
			$this->post['isthread'] = ($this->post['parentid'] == 0) ? true : false;
			if (empty($this->board) || $this->board['name'] != $board) {
				$this->Board($board, false);
			}
		}
	}

	function Delete($allow_archive = false) {
		global $h, $cf, $tc_db;

		$i = 0;
		cache_inval (Array ("board_name=".$this->board['name']));
		cache_inval (Array ("thread_id=".$this->post['id']));
		cache_inval (Array ("post_id=".$this->post['id']));
		if ($this->post['isthread'] == true) {

			@unlink(KU_BOARDSDIR.$this->board['name'].'/res/'.$this->post['id'].'.html');
			@unlink(KU_BOARDSDIR.$this->board['name'].'/res/050'.$this->post['id'].'.html');
			@unlink(KU_BOARDSDIR.$this->board['name'].'/res/005'.$this->post['id'].'.html');
			$this->DeleteFile(false, true);

			$query = "UPDATE posts SET `IS_DELETED` = 1 , `deleted_timestamp` = '" . time() . "' WHERE `boardid` = '" . $this->board['id'] . "' AND `id` = ".$tc_db->qstr($this->post['id'])." or `parentid` = ".$tc_db->qstr($this->post['id']);

			//bdl_debug($query);
			$tc_db->Execute($query);

			return $tc_db->Affected_Rows();
		} else {
			$this->DeleteFile(false);
			$tc_db->Execute("UPDATE `".KU_DBPREFIX."posts` SET `IS_DELETED` = 1 , `deleted_timestamp` = '" . time() . "' WHERE `boardid` = '" . $this->board['id'] . "' AND `id` = ".$tc_db->qstr($this->post['id']));

			return true;
		}
	}

	function DeleteFile($update_to_removed = true, $whole_thread = false) {
		global $h, $cf, $tc_db;
		if ($whole_thread && $this->post['isthread']) {
			$query = "
					select p.id, f.name as file, f.type as file_type, f.md5 as file_md5
					from posts p left join posts_files pf on p.id = pf.postid and p.boardid = pf.boardid
						left join files f on pf.fileid = f.id
					where p.boardid = " . $this->board['id'] . " and p.IS_DELETED = 0 AND p.parentid = ".$tc_db->qstr($this->post['id']);
			//dbl_debug($query);
			$results = $tc_db->GetAll($query);
			if (count($results)>0) {
				foreach($results AS $line) {
					if ($line['file'] != '' && $line['file'] != 'removed') {
							$check_dup = $tc_db->GetOne("select count(`id`) from `".KU_DBPREFIX."files` WHERE md5='" . $line['file_md5'] . "' AND `is_deleted` = 0");
							if ($check_dup <= 1) {
								@unlink(KU_SRCDIR.$line['file'].'.'.$line['file_type']);
								@unlink(KU_THUMBDIR.$line['file'].'s.'.$line['file_type']);
							}
						if ($update_to_removed) {
							$tc_db->Execute("delete from posts_files where boardid = '" . $this->board['id'] . "' and postid = ".$line['id']);
						}
					}
				}
			}
			$this->DeleteFile($update_to_removed);
		} else {
			$check_dup = $tc_db->GetOne("select count(*) from files where boardid = " . $this->board['id'] . " and md5='" . $this->post['file_md5'] . "' and is_deleted = 0");
			if ($check_dup) {
			if ($check_dup <= 1) {
				@rename(KU_SRCDIR.$this->post['file'].'.'.$this->post['file_type'],    KU_SRCDIR.'__'.$this->post['file'].'.'.$this->post['file_type']);
				@rename(KU_THUMBDIR.$this->post['file'].'s.'.$this->post['file_type'], KU_THUMBDIR.'__'.$this->post['file'].'s.'.$this->post['file_type']);
			} else {
				  @copy(KU_SRCDIR.$this->post['file'].'.'.$this->post['file_type'],    KU_SRCDIR.'__'.$this->post['file'].'.'.$this->post['file_type']);
				  @copy(KU_THUMBDIR.$this->post['file'].'s.'.$this->post['file_type'], KU_THUMBDIR.'__'.$this->post['file'].'s.'.$this->post['file_type']);
			}
			$tc_db->Execute("update files set name = concat('__', name), md5 = '' where boardid = '" . $this->board['id'] . "' and postid = ".$tc_db->qstr($this->post['id']));
			}
		}
	}

	function AddReplies($boardid, $id, $replies) {
		global $h, $cf, $tc_db;
		if (is_array($replies)) {
			foreach ($replies as $replyid => $replybid) {
				//$query = "insert into posts_replies values (" .$tc_db->qstr($boardid). ", " .$tc_db->qstr($id). ", " .$tc_db->qstr($boardid). ", " .$tc_db->qstr($reply). ")";

				$query = "insert into posts_replies (boardid, postid, replybid, replyid, session_md5, reply_md5) values (" .$tc_db->qstr($boardid). ", " .$tc_db->qstr($id). ", " .$tc_db->qstr($replybid). ", " .$tc_db->qstr($replyid). ", (select session_md5 from posts where id=" .$tc_db->qstr($id). " and boardid = " .$tc_db->qstr($boardid). "), (select session_md5 from posts where id=" .$tc_db->qstr($replyid). " and boardid = " .$tc_db->qstr($replybid). "));";
				$tc_db->Execute($query);
			}
		}
	}

	function Insert($parentid, $name, $tripcode, $email, $subject, $message, $password, $timestamp, $bumped, $ip, $posterauthority, $tag, $stickied, $locked, $boardid, $file_stuff, $raw_message = '', $is_repost = false, $premod = 0, $inherit_name = 0, $modmark = 0, $editpost = false) {
		global $h, $cf, $tc_db, $real_ip, $board_class;

		$sid = md5 (session_id ());

		$official = '';
		$official_val = '';
		if ($parentid == 0) {
			// this is a new thread, need to check if it's official and add corresponding shit
			if ($res = is_official_thread ($subject, $boardid)) {
				$official = ' , `official_id`';
				$official_val = ' , ' . $tc_db->qstr($res);
			}
		}

		$edit1 = ''; $edit2 = '';
		if ($editpost) {

			// check if it is and op post edit
			// in this case we force parentid to be zero
			$chk = $tc_db->GetRow('select bumped, parentid, timestamp from posts where boardid = '. $tc_db->qstr($boardid).' and id = '. $tc_db->qstr($editpost) );
			$timestamp = $chk['timestamp'];
			$bumped = $chk['bumped'];
			if ($chk['parentid'] == 0) {
				$parentid = 0;
				// flushing opposts cache
				for ($k = 0; $k < 500; $k++) {
					cache_del('board_opposts_' . $this->board['name'] . '_' . $k);
				}
			}
			// copy old post somewhere
			//$tc_db->Execute('update posts set parentid=parentid+100000000 where boardid = '. $tc_db->qstr($boardid).' and id = '. $tc_db->qstr($editpost));
			$tc_db->Execute('delete from posts_files where boardid = '. $tc_db->qstr($boardid).' and postid = '. $tc_db->qstr($editpost));
			$tc_db->Execute('delete from posts where boardid = '. $tc_db->qstr($boardid).' and id = '. $tc_db->qstr($editpost));

			if (board_id_to_name($boardid) == 'dollchan') {
				$edit1 = ', `edited`, `edit_timestamp` ';
				$edit2 = ', ' .  $tc_db->qstr(2) . ', ' . $tc_db->qstr(time());
			} else {
				$edit1 = ''; $edit2 = ''; // kooooklaproblems
			}
		}

		if ($is_repost) {
			$query = "INSERT INTO `".KU_DBPREFIX."posts` ( `parentid` , `boardid`, `name` , `tripcode` , `email` , `subject` , `message` , `password` , `timestamp` , `bumped` , `ip` , `ipmd5` , `posterauthority` , `tag` , `stickied` , `locked` , `raw_message`, `session_md5`, `repost_of`, `premod`, `inherit_name` ".$edit1.") select ".$tc_db->qstr($parentid).", ".$tc_db->qstr($boardid).", name , tripcode, email , subject , concat(message, ".$tc_db->qstr($message).") , ".$tc_db->qstr($password).", ".$tc_db->qstr($timestamp).", ".$tc_db->qstr($bumped).", ".$tc_db->qstr(md5_encrypt($ip, KU_RANDOMSEED)).", '".md5($ip)."', posterauthority , tag, ".$tc_db->qstr($stickied).", ".$tc_db->qstr($locked).", ".$tc_db->qstr($raw_message).", ".$tc_db->qstr($sid).", ".$tc_db->qstr($is_repost). " , premod, inherit_name ".$edit2." from posts where boardid= ".$tc_db->qstr($boardid)." and id = " .$tc_db->qstr($is_repost). " and (session_md5 = ".$tc_db->qstr($sid)." or ipmd5 = md5(".$tc_db->qstr($real_ip)."))";
		} else {
			//$query = "INSERT INTO `".KU_DBPREFIX."posts` ( `parentid` , `boardid`, `name` , `tripcode` , `email` , `subject` , `message` , `password` , `timestamp` , `bumped` , `ip` , `ipmd5` , `posterauthority` , `tag` , `stickied` , `locked` , `raw_message`, `session_md5`) VALUES ( ".$tc_db->qstr($parentid).", ".$tc_db->qstr($boardid).", ".$tc_db->qstr($name).", ".$tc_db->qstr($tripcode).", ".$tc_db->qstr($email).", ".$tc_db->qstr($subject).", ".$tc_db->qstr($message).", ".$tc_db->qstr($password).", ".$tc_db->qstr($timestamp).", ".$tc_db->qstr($bumped).", ".$tc_db->qstr(md5_encrypt($ip, KU_RANDOMSEED)).", '".md5($ip)."', ".$tc_db->qstr($posterauthority).", ".$tc_db->qstr($tag).", ".$tc_db->qstr($stickied).", ".$tc_db->qstr($locked).", ".$tc_db->qstr($raw_message).", ".$tc_db->qstr($sid)." )";
			$query = "INSERT INTO `".KU_DBPREFIX."posts` (".($editpost ? ' `id` , ' : '')." `parentid` , `boardid`, `name` , `tripcode` , `email` , `subject` , `message` , `password` , `timestamp` , `bumped` , `ip` , `ipmd5` , `posterauthority` , `tag` , `stickied` , `locked` , `raw_message`, `session_md5`, `premod`, `inherit_name`, `mod_post` ".$official. $edit1 .") VALUES ( ". ($editpost ? ($tc_db->qstr($editpost) .',') : '') .$tc_db->qstr($parentid).", ".$tc_db->qstr($boardid).", ".$tc_db->qstr($name).", ".$tc_db->qstr($tripcode).", ".$tc_db->qstr($email).", ".$tc_db->qstr($subject).", ".$tc_db->qstr($message).", ".$tc_db->qstr($password).", ".$tc_db->qstr($timestamp).", ".$tc_db->qstr($bumped).", ".$tc_db->qstr(md5_encrypt($ip, KU_RANDOMSEED)).", '".md5($ip)."', ".$tc_db->qstr($posterauthority).", ".$tc_db->qstr($tag).", ".$tc_db->qstr($stickied).", ".$tc_db->qstr($locked).", ".$tc_db->qstr($raw_message).", ".$tc_db->qstr($sid).", " .$tc_db->qstr($premod). ", ".$tc_db->qstr($inherit_name). ", ". $tc_db->qstr($modmark). $official_val . $edit2." )";
		}
		//bdl_debug ($query);
		$tc_db->Execute($query);
		$id = $tc_db->Insert_Id();
		if(!$id || KU_DBTYPE == 'sqlite') {
			// Non-mysql installs don't return the insert ID after insertion, we need to manually get it.
			$id = $tc_db->GetOne("SELECT `id` FROM `".KU_DBPREFIX."posts` WHERE `boardid` = ".$tc_db->qstr($boardid)." AND timestamp = ".$tc_db->qstr($timestamp)." and session_md5 = ".$tc_db->qstr($sid)." order by id desc LIMIT 1");
		}

		if ($is_repost) {
			$query = "insert into posts_files (postid, boardid, fileid, ratingid, `order`) select " .$tc_db->qstr($id).", boardid, fileid, ratingid, `order` from posts_files where boardid= ".$tc_db->qstr($boardid)." and postid = ".$tc_db->qstr($is_repost);
			$tc_db->Execute($query);
		} else {
		if (isset($file_stuff) && is_array($file_stuff)) {
		for ($i = 0; $i <= $board_class->board['maximages']; $i++) {
		//foreach ($file_stuff as $file) {
			bdl_debug("adding file to post " . $id);
			if (isset($file_stuff[$i]['ratingid']) && $file_stuff[$i]['ratingid']) {
				$query = "insert into posts_files (postid, boardid, fileid, ratingid, `order`) values (".
					$tc_db->qstr($id).",".$tc_db->qstr($boardid).",".$tc_db->qstr($file_stuff[$i]['fileid']).",".$tc_db->qstr($file_stuff[$i]['ratingid']).",".$i
				.")";
			} else {
				$query = "insert into posts_files (postid, boardid, fileid, `order`) values (".
					$tc_db->qstr($id).",".$tc_db->qstr($boardid).",".$tc_db->qstr($file_stuff[$i]['fileid']).",".$i
				.")";
			}
			//bdl_debug($query);
			$tc_db->Execute($query);
		}
		}
		}
		// needed for ipsearch threads to get new posts
		array_map('unlink', glob(KU_TEMPLATEDIR_2 . '/ipsearch_res_*'.sprintf("%u", ip2long($ip)).'.html'));

		if ($id == 1 && $this->board['start'] > 1) {
			$tc_db->Execute("UPDATE `".KU_DBPREFIX."posts` SET `id` = '".$this->board['start']."' WHERE `boardid` = ".$boardid);
			return $this->board['start'];
		}

		cache_add('last_thread_id_' . board_id_to_name($boardid), $parentid);
		return $id;
	}

	function Report() {
		global $h, $cf, $tc_db, $real_ip;

		return $tc_db->Execute("INSERT INTO `".KU_DBPREFIX."reports` ( `board` , `postid` , `when` , `ip`, `reason` ) VALUES ( " . $tc_db->qstr($this->board['name']) . " , " . $tc_db->qstr($this->post['id']) . " , ".time()." , " . $tc_db->qstr(md5_encrypt($real_ip, KU_RANDOMSEED)) . ", " . $tc_db->qstr($_POST['reportreason']) . " )");
	}
}

?>
