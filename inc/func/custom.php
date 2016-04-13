<?php
/* Place any functions you create here. Note that this is not meant for module functions, which should be placed in the module's php file. */

function bdl_debug ($str) {
	global $real_ip;
	if (empty($real_ip)){
		$real_ip = 'ponyaba';
	}

	file_put_contents ( '/var/log/ponyach/debug.txt', $real_ip. ' '.microtime() . ' ' . preg_replace('~\R~u', ' XX ', $str). "\n", FILE_APPEND );
}

function xmd5_file($filename){
	// a replace for md5_file fuction which tricks dollchan's modified files so they are treated as non-uniq.
	// this function may fail in a very rare condition when file size is not far above */100 like 21003, but real size is above 100 like 20998
	return md5 (file_get_contents ($filename, false, NULL, 20, ((int)(filesize($filename) / 100) * 100) - 40));

}

function md5_antikukla($filename, $ext = false){
	$fp = fopen($filename, 'r');
	fseek($fp, -6, SEEK_END); // It needs to be negative
	$last_bytes = fgets($fp, 6);
	fclose($fp);

	//$ext = substr($filename, -3);

	if ($ext == 'jpg' || $ext == 'png') {
		for ($i = 6; $i > 0; $i--) {
			$data = substr($last_bytes, -1 * $i);
			if (is_numeric($data)) {
				return md5 (file_get_contents ($filename, false, NULL, 0, filesize($filename) - $i));
			} else {
				return md5_file($filename);
			}
		}
	} else {
		return md5_file($filename);
	}
}

function testmd5_file($filename){
	// a replace for md5_file fuction which tricks dollchan's modified files so they are treated as non-uniq.
	// this function may fail in a very rare condition when file size is not far above */100 like 21003, but real size is above 100 like 20998
	return md5 (file_get_contents ($filename));
}

function convert_gm_to_db_filetype ($file_type) {

        switch ($file_type) {
        case "JPEG":
                return "jpg";
        case "PNG":
                return "png";
        case "GIF":
                return "gif";
        default:
                return false;
        }
}

function create_thumb_webm ($webm, $thumb, $thumb_width, $thumb_height) {
	bdl_debug ('opening webm: ' . $webm . ' saving thumb as: ' . $thumb);

	// we need to get webm file dimensions, i havent find a corrsponding method in php ffmpeg
	// sooo lets do some stupid shit

	$frame = '/tmp/'. uniqid() .'.png';
	if (get_frame_from_webm($webm, $frame)) {
		try {
			$image = new Gmagick ();
			$image->read($frame);
			$w = $image->getImageWidth (); $h = $image->getImageHeight();

			if ($w <= $thumb_width && $h <= $thumb_heght) { 
				copy ($webm, $thumb);
				return true;
			}

			$ffmpeg = FFMpeg\FFMpeg::create();
			$video = $ffmpeg->open($webm);
			bdl_debug ('loaded webm');
			$video
			    ->filters()
			    ->resize(new FFMpeg\Coordinate\Dimension($thumb_width, $thumb_height), RESIZEMODE_INSET);
			bdl_debug ('resized webm');
			$video
			    ->save(new FFMpeg\Format\Video\WebM(), $thumb);
			bdl_debug ('saved webm');
		} catch (Exception $e) {
			// keep silence on exceptions
			bdl_debug ('create_thumb_webm ERROR: ' . $e->getMessage ());
			unlink ($frame);
			return false;
		}
		unlink ($frame);

		return true;
	} else {
		return false;
	}
}


function get_frame_from_webm ($webm, $image) {
	bdl_debug ('opening webm: ' . $webm . ' saving frame as: ' . $image);

	if (!file_exists($webm)) {
		bdl_debug ('webm file not exists');
		return false;
	}
//file /var/www/ponyach/images/src/145280482875.webm | cut -d":" -f2| xargs -I REPLACE test REPLACE = WebM 
	$res = exec ('file ' . $webm . ' | cut -d":" -f2| xargs -I REPLACE test REPLACE = WebM && avconv -i ' . $webm . ' -vsync 1 -r 1 -an -y -vframes 1 -t 1 ' . $image . ' >/dev/null 2>&1 && echo ok');

	if (trim($res) == "ok") {
		return true;
	} else {
		bdl_debug('bad webm, hmmmmmm');
		xdie('плохая вебмка, не годная.');
	}

	// legacy code
	// all the FFMpeg is bullshit. what it realy does inside is the above exec
	if (!file_exists($webm)) 
		bdl_debug ('webm file not exists');

	try {
		$ffmpeg = FFMpeg\FFMpeg::create();
		$video = $ffmpeg->open($webm);
		bdl_debug ('loaded webm');
		$video
		    ->frame(FFMpeg\Coordinate\TimeCode::fromSeconds(2))
		    ->save($image, true);
	} catch (Exception $e) {
		// keep silence on exceptions
		bdl_debug ('get_frame_from_webm ERROR: ' . $e->getMessage ());
		if ($image) unlink ($image);
		return false;
	}

	return true;
}

function createThumbnailGM ($image, $file_thumb_location, $thumb_width, $thumb_height) {

	try {
		$image_w = $image->getImageWidth ();
		$image_h = $image->getImageHeight ();

		bdl_debug ('dims: ' . $image_w . 'x' . $image_h . '  thumb max: ' . $thumb_width . 'x' . $thumb_height);
		bdl_debug ('format: ' . $image->getImageFormat());

		if ($image->getImageFormat() == 'GIF') {
			bdl_debug('its a GIF');
			if ($image->getNumberImages() > 1) {
				bdl_debug('an array GIF');
				$image = $image->flattenImages();
				$image->setImageFormat('JPEG');
				$image_w = $image->getImageWidth ();
				$image_h = $image->getImageHeight ();
				bdl_debug ('dims: ' . $image_w . 'x' . $image_h . '  thumb max: ' . $thumb_width . 'x' . $thumb_height);
			} else {
				bdl_debug('not array GIF');
			}
		}

		if ($image_w > $thumb_width || $image_h > $thumb_height) {
			bdl_debug ('resize needed');
			if ($image_w > $image_h) {
				$image->resizeImage($thumb_width, 0, Gmagick::FILTER_CATROM, 1);
			} else {
				$image->resizeImage(0, $thumb_height, Gmagick::FILTER_CATROM, 1);
			}
		} else {
			bdl_debug ('resize not needed');
		}

		$image->stripImage();
		if ($image->getImageFormat() == 'JPEG') {
			$image->setInterlaceScheme(Gmagick::INTERLACE_PLANE);
		}
		$image->write($file_thumb_location);
		return true;
	} catch (exception $e) {
		bdl_debug ("error: cant load image, " .$e->getMessage ());
		return false;
	}
}

function is_alpha_png($filename){
	$contents = file_get_contents( $filename, false, null, 25, 1 );
	
	if ( ord ($contents ) & 4 )
		return 1;
	
	if ( stripos( $contents, 'PLTE' ) !== false && stripos( $contents, 'tRNS' ) !== false )
		return 1;
	
	$im = imagecreatefrompng($filename); // TODO: split into two functions to avoid loading file twice.
	
	$width = imagesx($im); $height = imagesy($im);
	for($i = 0; $i < $width; $i += 20) 
		for($j = 0; $j < $height; $j += 20) {
			$rgb = imagecolorsforindex($im, imagecolorat($im, $i, $j));
			if ( $rgb['alpha'] != '0' )
				return 1;
		}
	return NULL;
}

function image_chksum (&$src_image, $x1, $y1, $x2, $y2) {
	$chksum = 0;
	for ($x = $x1; $x < $x2; $x++)
		for ($y = $y1; $y < $y2; $y++) {
			$rgb = imagecolorsforindex ($src_image, imagecolorat($src_image, $x, $y));
			$sum = ($rgb["red"] + $rgb["green"] + $rgb["blue"]);
			$chksum += $sum;
		}
	return $chksum;
}

function sum_map($arr1, $arr2)
{
    return($arr1+$arr2);
}

function image_ancor (&$src_image) {
	$xanc = 0; $yanc = 0; $x = 0; $y = 0;
	$mass = array(0, 0, 0, 0);

	$w = imagesx($src_image); $h = imagesy($src_image);
	for ($x = 0; $x < $w; $x++)
                for ($y = 0; $y < $h; $y++) {
			$rgb = imagecolorsforindex ($src_image, imagecolorat($src_image, $x, $y));
			$mass = array_map("sum_map", $rgb, $mass);
		}
}

function image_fingerprint(&$src) {
	$res = '';
	$w = imagesx($src); $h = imagesy($src);
	$crops = 2; $crop_size = 10; // why 10? because fuck you, that's why.
	$crop_sum_len = strlen($crop_size);
	$side = $crops * $crop_size; // resized image w and h;
	$dest = @imagecreatetruecolor ($side, $side);
	
	ImageCopyResampled ($dest, $src, 0, 0, 0, 0, $side, $side, $w, $h);
	
	for ($x = 0; $x < $crops; $x++ )
		for ($y = 0; $y < $crops; $y++)
			$res .= str_pad (
				round (image_chksum (
					$dest, $x * $crop_size, $y * $crop_size, $x * $crop_size + $crop_size, $y * $crop_size + $crop_size
				), -4) / 10000
			, $crop_sum_len, '0', STR_PAD_LEFT) // yes it can be only "10" for two digets
	; // division is actually not needed, it's only here to make result shorter.
	
	$ratio = str_pad (round(($w / $h)*10), 2, '0', STR_PAD_LEFT);
	return $ratio.$res;
}

function image_distance(&$imga, &$imgb) {
	$size = 20;
	$wa = imagesx($imga); $ha = imagesy($imga);
	$wb = imagesx($imgb); $hb = imagesy($imgb);
	$smalla = @imagecreatetruecolor ($size, $size);
	$smallb = @imagecreatetruecolor ($size, $size);
	ImageCopyResampled ($smalla, $imga, 0, 0, 0, 0, $size, $size, $wa, $ha);
	ImageCopyResampled ($smallb, $imgb, 0, 0, 0, 0, $size, $size, $wb, $hb);
	return image_chksum ($smalla, 0, 0, $size, $size).' '.image_chksum ($smallb, 0, 0, $size, $size).' '.abs (image_chksum ($smalla, 0, 0, $size, $size) - image_chksum ($smallb, 0, 0, $size, $size));
}

function replace_hidden_chars ($str) {

	$str = str_replace (ords_to_unistr(array ('0152')), '', $str);
	$str = str_replace (ords_to_unistr(array ('0160')), '', $str);
	$replace_ptrn = Array ('a' => 'а', 'r' => 'г', 'e' => 'е', 'c' => 'с', 'o' => 'о', 'y' => 'у', 'p' => 'р', 'x' => 'х', 'n' => 'п', 'u' => 'и',
				'A' => 'А', 'B' => 'В', 'C' => 'С', 'E' => 'Е', 'K' => 'К', 'M' => 'М', 'H' => 'Н', 'O' => 'О', 'P' => 'Р', 'T' => 'Т', 'Y' => 'У', 'X' => 'Х', 'U' => 'И');
	$keys = implode ('', array_keys ($replace_ptrn));
	$search_ptrns = array (
		'~([а-яА-ЯёЁ]+)([' . $keys . ']{1,3})([а-яА-ЯёЁ]+)~u',
		//'~((.|^))([' . $keys . ']{1,3})([а-яА-ЯёЁ]+)~u',
		'~([а-яА-ЯёЁ]+)([' . $keys . ']{1,3})~u');
	// english => russian


	foreach ($search_ptrns as $search_ptrn) {
	while (preg_match ($search_ptrn, $str, $found, PREG_OFFSET_CAPTURE)) {
		// found hidden characters;

		$from = $found[2][1]; // start position of shit
		$length = strlen ($found[2][0]); // length of shit
		$needs_replace = $found[2][0]; // what to fix
		$replaced = strtr ($needs_replace, $replace_ptrn); // a 'fixed' part of string

		$str = substr_replace ($str, $replaced, $from, $length); 
	}
	}
	return $str;

}

function draw_dot () {
	print time() . "\n";
}

function random_file ($dir) {
	$files = glob($dir . '/*.*');
	array_multisort(array_map('filemtime', $files), SORT_NUMERIC, SORT_DESC, $files);
	$files = array_slice($files, count($files)/2);
	$file = array_rand($files);

	bdl_debug("selected random file - " . $files[$file]);

	return $files[$file];
}

function random_end_post () {
	$arr = array(
		"Новый тред: ~",
		"Добро пожаловать в новый тред: ~",
		"Новый тред здесь: ~",
		"Перекат: ~",
		"Перекат в новый тред: ~",
		"Следующий тред: ~",
		"Перекат в следующий тред: ~",
		"Следующий тред здесь: ~"
	);
	$post = array_rand($arr);
	return $arr[$post];
}

function random_end_post_subject () {
	$arr = array(
		"Новый тред",
		"Добро пожаловать в новый тред",
		"Новый тред здесь",
		"Перекат",
		"Перекат в новый тред",
		"Следующий тред",
		"Перекат в следующий тред",
		"Следующий тред здесь"
	);
	$post = array_rand($arr);
	return $arr[$post];
}

function link_on_thread ($board_name, $next_thread) {
	
	return '<a href="/'. $board_name. '/res/'. $next_thread .'.html#'. $next_thread .'" onclick="return highlight(\''. $next_thread .'\', true);" class="de-preflink ref|' .$board_name. '|'. $next_thread .'|'. $next_thread .' de-opref">&gt;&gt;' . $next_thread . '</a>';
}

function link_on_post ($board_name, $post_id) {
	global $tc_db;
//<a href="/b/res/1586814.html#1587096" onclick="return highlight('1587096', true);" class="de-link-pref ref|b|1586814|1587096">&gt;&gt;1587096</a>

	$thread_id = $tc_db->GetOne('select p.parentid from posts p inner join boards b on p.boardid = b.id where b.name = '. $tc_db->qstr($board_name) . ' and p.id = '. $tc_db->qstr($post_id));
	if ($thread_id) {
		return '<a href="/'.$board_name.'/res/'.$thread_id.'.html#'.$post_id.'" onclick="return highlight('.$post_id.', true);" class="de-link-pref ref|b|'.$thread_id.'|'.$post_id.'">&gt;&gt;'.$post_id.'</a>';
	} else return false;
}

function ponyaba_post ($board_name, $thread_replyto, $subject, $message, $img, $img_file_name = "ponyaba"){
	global $board_class;
	
	bdl_debug("ponyaba post on ". $board_name);
        if (!isset($board_class)) {
	$board_class = new Board($board_name);
	}
	$post_class = new Post(0, $board_class->board['name'], $board_class->board['id'], true);

	// since image is located on the server we can't use Upload class without fucking it up
	if (isset($img)) {
		bdl_debug("with file");
		$upload_class = new Upload();


		$upload_class->HandleUploadPonyaba($img, $img_file_name);
		if ($thread_replyto != 1)
			$upload_class->set_isreply();
		$filetype_withoutdot = substr($upload_class->file_type, 1);
		$post_id = $post_class->Insert(
			$thread_replyto, "", "поняба", "", $subject, addslashes($message), 
			"xxx", time(), time(), "1.1.1.1", false, "", false, false, $board_class->board['id'], $upload_class->get_file_stuff(), "");
	} else {

		bdl_debug("without file");

		$post_id = $post_class->Insert(
			$thread_replyto, "", "поняба", "", $subject, addslashes($message), 
			"xxx", time(), time(), "1.1.1.1", false, "", false, false, $board_class->board['id'], false, "");
	}

	unset($upload_class);
	unset($post_class);
	//unset($board_class);

	return $post_id;
}

function have_posts_from_ip($ip) {
	global $mc, $tc_db;
	$res = $tc_db->GetOne("select count(*) from posts where ipmd5=".$tc_db->qstr(md5($ip))." and IS_DELETED=0;");
	return (bool)$res;
}

function have_posts_from_ipmd5($ip) {
	global $mc, $tc_db;
	$res = $tc_db->GetOne("select count(*) from posts where ipmd5=".$tc_db->qstr($ip)." and IS_DELETED=0;");
	return (bool)$res;
}

function parse_wakaba_mark (&$message) {
	// it's already much more than wakaba mark, but it's less stupid, than call it "BBCode"
	global $mc, $tc_db;

	$mc_key = "parser";
	if (false === ($parser = cache_get ($mc_key))) {
		$parser = array ();
		$result = $tc_db -> GetAll ("select * from parser_tags pt");
		foreach ($result as $row) {
			$parser['open_tags'][] = $row['open_bbcode'];
			$parser['close_tags'][] = $row['close_bbcode'];
			$parser['replace_open_tags'][] = $row['open_tag'];
			$parser['replace_close_tags'][] = $row['close_tag'];
			if (isset ($row['is_priority']) && $row['is_priority'])
				$parser['priority_tags'][] = $row['open_bbcode'];
			if (isset ($row['is_multilevel']) && $row['is_multilevel'])
				$parser['multi_level_tags'][] = $row['open_bbcode'];
			if (isset ($row['wakaba']) && $row['wakaba']) {
				// lookahead negatiation is for escaping purpose
				$parser['wakaba'][] = '~(^|((?!\\\\).))' .$row['wakaba']. '(.+?)' .$row['wakaba']. '~is';
				$parser['wakaba_replace'][] = '\\1' .$row['open_tag']. '\\3' .$row['close_tag'];
				// but now we have to remove escape chars
				$parser['wakaba'][] = '~\\\\' .$row['wakaba']. '~is';
				$parser['wakaba_replace'][] = $row['wakaba_raw'];
			}
		}
		cache_add ($mc_key, $parser);
	}

	// TODO: pre-parser hooks
	// multi-line spoiler hook
	//$message = preg_replace('~(^|((?!\\\\).))%%((?!(((?!%%).)*<br\s*/>((?!%%).)*)).*?)%%~', '<span class="spoiler">\\1</span>' , $message);
	// /hooks

//	$message = preg_replace_callback (Array("~([a-zа-я0-9]+)\^W~ui"),
//		function ($match) {
//			return '[s]' . $match[1] . '[/s]';
//		}, $message);
	
	// replace all wakaba-marks with bbcodes
	if (isset ($parser['wakaba']) && is_array ($parser['wakaba']))
		$message = preg_replace ($parser['wakaba'], $parser['wakaba_replace'], $message);

	$stack = array ();
	foreach (array_merge ($parser['open_tags'], $parser['close_tags']) as $tag) {
		// everything should be BBcode for now
		$keys[] = '\\' .$tag;
	}
	// we need to call preg_replace with a huge regex to force it to match tags in order of appearence
	$pattern = '~(?<!\\\\)('. implode ('|', $keys) . ')~ui';
	//$pattern = '~('. implode ('|', $keys) . ')~ui';
	debug (" parser pattern: $pattern");

	$message = preg_replace_callback ($pattern, 
		function ($match) use (&$stack, &$parser) {
			if (in_array ($match[0], $parser['open_tags'])) {
				if (in_array ($match[0], $stack) && !in_array ($match[0], $parser['multi_level_tags'])) {
					// this tag is already opened - no way
					return '';
				} else {
					$res = ''; $before = ''; $after = '';
					// if this is a priority tag, close all previosly tags
					if (in_array ($match[0], $parser['priority_tags'])) {
						$i = count ($stack);
						if ($i > 0) {
							$i--;
							// closing all tags opened before found, except priority tags
							while ($i > -1 && $stack[$i]) {
								if (in_array ($stack[$i], $parser['priority_tags']))
									break;
								$before .= $parser['replace_close_tags'][array_search ($stack[$i], $parser['open_tags'], true)];
								if ($stack[$i] === $match[0])
									break;
								$i--;
							}
							// opening them again, except the last one, cause we have "closed it"
							$i++; // skip closed tag
							while (isset ($stack[$i])) {
								$after .= $parser['replace_open_tags'][array_search ($stack[$i], $parser['open_tags'], true)];
								$i++;
							}
		
						}
						// priority tags go to begining of stack
						// TODO: in the begining, but after all priority tags in stack
						array_unshift ($stack, $match[0]);
					} else {
						// regular tags go to the end
						$stack[] = $match[0];
					}
					$res = $before . $parser['replace_open_tags'][array_search ($match[0], $parser['open_tags'], true)] . $after;
					return $res;
				}	
			} else {
				// walking through stack backwards, closing all tags, opened after found one
				$i = count ($stack);
				if ($i > 0) {
					$i--; // using $i as an index, arrays start from 0
					$open_tag = $parser['open_tags'][array_search ($match[0], $parser['close_tags'], true)];
					if (in_array ($open_tag, $stack)) {
						$res = '';
						// closing all tags opened before found
						while ($i > -1 && $stack[$i]) {
							$res .= $parser['replace_close_tags'][array_search ($stack[$i], $parser['open_tags'], true)];
							if ($stack[$i] === $open_tag)
								break;
							$i--;
						}
						// opening them again, except the last one, cause we have "closed it"
						unset ($stack[$i]); // forget about closed tag
						$i++; // skip closed tag
						while (isset ($stack[$i])) {
							$res .= $parser['replace_open_tags'][array_search ($stack[$i], $parser['open_tags'], true)];
							$i++;
						}
						$stack = array_values ($stack); // reorder stack. fuck my brain.
						return $res;
					} else {
						return '';
					}
				} else {
					// we found a closing tag without any opened yet.
					return '';
				}
			}
		}, $message);

	// close all tags left open
	$i = count ($stack) -1;
	while ($i > -1 && $stack[$i]) {
		$message .= $parser['replace_close_tags'][array_search ($stack[$i], $parser['open_tags'], true)];
		$i--;
	}
}

function is_cli()
{
    return php_sapi_name() === 'cli';
}

function index_search_keywords($timestamp) {
	global $tc_db;
	$res = $tc_db->GetAll("select raw_message, boardid, id from posts where timestamp>".$tc_db->qstr($timestamp)." and IS_DELETED=0;");
	if (is_cli()) echo "indexing " .count($res). " posts\n";
	$values = array(); $cache = array();
	foreach ($res as $line) {
		$i++; 
		if (is_cli()){
		if  (($i % 50) === 0) {
			echo "\n" . $line['id']." ";
		}else{
			echo '.';
		}
		}
		
		//$words = ((preg_split('/[\s,.!\?:;>\'"…“%«»\*^()]+/', $line['raw_message'], -1, PREG_SPLIT_NO_EMPTY)));
		$words = ((preg_split('/[\s,.!\?:;>\'"]+/', $line['raw_message'], -1, PREG_SPLIT_NO_EMPTY)));

		$added = array();
		foreach ($words as $word) {
			//$word = trim(preg_replace(array('~\[.*\]~', '~>>[0-9]+~'), '', $word));
			$word = mb_strtolower($word, 'UTF-8');
			if (strlen($word) <= 32 && strlen($word) > 2 && preg_match('~^[а-я-]+$~u',$word)) {
				
				if (array_key_exists($word, $cache)) {
					$id = $cache[$word];
				} else {
					$id = $tc_db->GetOne('select id from search_keywords where word = '.$tc_db->qstr($word));
					if (!$id) {
						$tc_db->Execute('insert into search_keywords (`word`) values ('.$tc_db->qstr($word).')');
						$id = $tc_db->Insert_Id();
					}
					$cache[$word] = $id;
				}
				$chk =  $tc_db->GetOne('select * from search_index where board_id=' .$tc_db->qstr($line['boardid']).' and post_id='.$tc_db->qstr($line['id']).' and word_id='.$tc_db->qstr($id));
				if (!$chk && !array_key_exists($id, $added)) {
					//$values[] = '(' .$tc_db->qstr($line['boardid']).','.$tc_db->qstr($line['id']).','.$tc_db->qstr($id).')';
					$values[] = '(' .$line['id'].','.$line['boardid'].','.$id.')';
					$added[$id] = true;
				}
				//$tc_db->Execute('insert into search_index (`board_id`, `post_id`, `word_id`) values ('.$line['boardid']. ','. $line['id']. ','.$id.')');
			}
		}
	}
	if (count($values) > 0) {
		if (is_cli()) echo "\n inserting ... ";
		$query = 'insert into search_index values ' . join(',',$values);
		file_put_contents ('/tmp/insert.sql', $query);
	
		$tc_db->Execute($query);
		if (is_cli()) echo "done \n ";
	}
}

function strip_reply_text($msg, $reply_to) {

	//bdl_debug ($msg);
	return preg_replace ('/<br \/>\s*$/', '', 
		preg_replace ('/<a class=\\\"irc-reflink.*\/a><a href=\\\"\/[a-z0-9]+\/res\/[0-9]+.html#[0-9]+.*class=\\\"ref\|[a-z0-9]+\|[0-9]+\|[0-9]+\\\">&gt;&gt;[0-9]+<\/a>.?<br \/>.*/', '', 
			preg_replace ('/.*&gt;&gt;'.$reply_to.'<\/a>(<br \/>)?/', '', $msg)));
}

function load_haanga() {
	require_once KU_ROOTDIR . 'lib/Haanga.php';
	require_once KU_ROOTDIR . 'lib/Haanga/Compiler.php';
	Haanga::configure(array(
	    'template_dir' => KU_TEMPLATEDIR,
	    'cache_dir' => KU_CACHEDTEMPLATEDIR
	));
	Haanga_Compiler::setOption('strip_whitespace', true);
	//Haanga_Compiler::setOption('dot_as_object', false);
}

function load_haanga2() {
	require_once KU_ROOTDIR . 'lib/Haanga.php';
	Haanga::configure(array(
	    'template_dir' => KU_TEMPLATEDIR_2,
	    'cache_dir' => KU_CACHEDTEMPLATEDIR_2
	));
}

function load_haanga2_2() {
	require_once KU_ROOTDIR . 'lib/Haanga.php';
	Haanga::configure(array(
	    'template_dir' => sys_get_temp_dir(),
	    'cache_dir' => sys_get_temp_dir()
	));
}

function haanga_spaces() {
	require_once KU_ROOTDIR . 'lib/Haanga/Compiler.php';
	Haanga_Compiler::setOption('strip_whitespace', false);
}

function thread_exists($b, $t) {
	global $mc, $tc_db;

	if ((substr($t, 0, 3) === '050') || (substr($t, 0, 3) === '005')) {
		// it's a "last 50 replies" version of thread
		$t = substr($t, 3);
	}
	$mc_key = "thread_exists_".$b.'_'.$t;
	if (false === ($res = cache_get ($mc_key))) {
		if ($tc_db->GetOne("select p.id from posts p left join boards b on p.boardid = b.id where b.name = ". $tc_db->qstr($b) ." and p.id = ". $tc_db->qstr($t). " limit 1")) {
			cache_add($mc_key, '1');
			return true;
		} else return false;
	}
	return true;
}

function board_id_to_name($b) {
	global $mc, $tc_db;

	$mc_key = "boardid2name".'_'.$b;
	if (false === ($res = cache_get ($mc_key))) {
		$res = $tc_db->GetOne("select name from boards where id = ". $tc_db->qstr($b). " limit 1");
		if ($res) {
			cache_add($mc_key, $res);
			return $res;
		} else return false;
	}
	return $res;
}

function filetype_name_to_id($t) {
	global $mc, $tc_db;

	$mc_key = "ftname2id".'_'.$t;
	if (false === ($res = cache_get ($mc_key))) {
		$res = $tc_db->GetOne("select id from filetypes where filetype = ". $tc_db->qstr($t). " limit 1");
		if ($res) {
			cache_add($mc_key, $res);
			return $res;
		} else return false;
	}
	return $res;
}

function board_name_to_id($b) {
	global $mc, $tc_db;
	$mc_key = "board_nti".'_'.$b;
	if (false === ($res = cache_get ($mc_key))) {
		if ($dbres = $tc_db->GetOne("select id from boards where name = ". $tc_db->qstr($b). " limit 1")) {
			cache_add($mc_key, $dbres);
			return $dbres;
		} else return false;
	}
	return $res;
}

//function board_id_to_name($b) {
//	global $mc, $tc_db;
//	$mc_key = "board_nti".'_'.$b;
//	if (false === ($res = cache_get ($mc_key))) {
//		if ($dbres = $tc_db->GetOne("select name from boards where id = ". $tc_db->qstr($b). " limit 1")) {
//			cache_add($mc_key, $dbres);
//			return $dbres;
//		} else return false;
//	}
//	return $res;
//}

function board_exists($b) {
	global $mc, $tc_db;

	if ($b === 'search' || $b === 'ipsearch')
		return true;

	$mc_key = "board".'_'.$b;
	if (false === ($res = cache_get ($mc_key))) {
		if ($tc_db->GetOne("select id from boards where name = ". $tc_db->qstr($b). " limit 1")) {
			cache_add($mc_key, '1');
			return true;
		} else return false;
	}
	return true;
}

function image_exists($b, $i) {
	global $mc, $tc_db;

	$mc_key = "image".'_'.$i.'_'.$b;

	if (substr($i, -1) == 's' || substr($i, -1) == 'c') $i= substr($i, 0, -1);
	if (false === ($res = cache_get ($mc_key))) {
		if ($tc_db->GetOne("select f.id from files f where f.name = ". $tc_db->qstr($i). " limit 1")) {
			cache_add($mc_key, '1');
			return true;
		} else return false;
	}
	return true;
}

function unlink_all($dir) {
	$files = glob($dir . '/*'); // get all file names
	foreach($files as $file){ // iterate files
		if(is_file($file))
			unlink($file); // delete file
	}
}

function check_is_mod() {
	global $tc_db;

	if (!isset($_COOKIE['kumod'])) return false;

	if (isset($_SESSION['manageusername']) && isset($_SESSION['managepassword'])) {
		$results = $tc_db->GetAll("SELECT HIGH_PRIORITY `username` FROM `" . KU_DBPREFIX . "staff` WHERE `username` = " . $tc_db->qstr($_SESSION['manageusername']) . " AND `password` = " . $tc_db->qstr($_SESSION['managepassword']) . " LIMIT 1");
		if (count($results) > 0) {
			bdl_debug ('mod detected u = '.$_SESSION['manageusername'].' p = '.$_SESSION['managepassword'] .'- count = ' . count($results));
			return true;
		}
	}
	return false;
}

function do_404() {
	session_write_close();
	// header ("Cache-Control: no-cache, must-revalidate"); //HTTP 1.1
	// header ("Pragma: no-cache"); //HTTP 1.0
	// header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
	//header("Status: 404 Not Found");
	// $_SERVER['REDIRECT_STATUS'] = 404;
	//readfile(KU_ROOTDIR . '/html/404e.html');
	
	//header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
	header("Location: /html/404e.html");
	die();
}

function check_auth_34() {
	global $real_ip, $tc_db;

	if ($_SESSION['auth'] !== 2) {
		bdl_debug ("check posts from ip - " . $real_ip);
	        if ($tc_db->GetOne("select count(*) from posts where ipmd5=". $tc_db->qstr(md5($real_ip)) )) {
	                $_SESSION['auth'] = 2;
			bdl_debug ("posts from ip found - " . $real_ip);
	        }
	}
	
	if ($_SESSION['auth'] !== 2) {
		do_404();
	}
}

$img_types = array('0', 'gif', 'jpg', 'png', 'swf', 'psd', 'bmp', 'tiff', 'tiff', 'jpc', 'jpc2', 'jpx', 'jb2', 'swc', 'iff', 'wbmp', 'xbm', 'ico', 'webm');

// be sure to pass only images in here. the exif check may be tricked easy
function show_image($filename) {
	global $img_types;

	bdl_debug ("show_image: ". $filename);

	$file_type = exif_imagetype ($filename);
	if (file_exists ($filename) && $file_type) {
		header ("Cache-Control: private");
		header ("Content-Type:  " . 'image/' . $img_types[$file_type]);
		readfile ($filename);
	} elseif (!$file_type) {
		$finfo = new finfo();
		$mimetype = $finfo->file($filename, FILEINFO_MIME);
		$webm = explode(';', $mimetype);
		if ($webm[0] === 'video/webm'){
			header ("Cache-Control: private");
			header ("Content-Type:  " . 'video/' . end($img_types));
			readfile ($filename);
		}
	} else {
		do_404();
	}
}

//recursive in_array check function
function is_in_array($array, $key, $key_value){
      $within_array = 'no';
      foreach( $array as $k=>$v ){
        if( is_array($v) ){
            $within_array = is_in_array($v, $key, $key_value);
            if( $within_array == 'yes' ){
                break;
            }
        } else {
                if( $v == $key_value && $k == $key ){
                        $within_array = 'yes';
                        break;
                }
        }
      }
      return $within_array;
}

function regen_changelog_custom($from_manage = false) {
	// if ($from_manage) {
		// cache_flush();
		// return;
	// }
	global $tc_db, $tpl_page;
	// $this->AdministratorsOnly();

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

function add_prefetch($board_name, $thread_id, $p_board_name, $p_thread_id) {
	global $tc_db;

	$mc_id = 'prefetch_' . $board_name . '_' . $thread_id;

	$board_id = board_name_to_id($board_name);
	$p_board_id = board_name_to_id($p_board_name);

	$query = 'insert into prefetch (threadid, boardid, prefetch_threadid, prefetch_boardid) values ('. $tc_db->qstr($thread_id) .' , ' . $tc_db->qstr($board_id) . ' , ' . $tc_db->qstr($p_thread_id) . ' , ' . $tc_db->qstr($p_board_id).')';
	bdl_debug($query);
	$tc_db->Execute($query);

	$threads_to_prefetch = $tc_db->GetAll('select prefetch_threadid, prefetch_boardid from prefetch where boardid = '. $tc_db->qstr($board_id) .' and threadid = ' . $tc_db->qstr($thread_id));

	if ($threads_to_prefetch) {
		$headers = Array();
		foreach ($threads_to_prefetch as $row => $data) {
			$headers[] = 'Link: <'.KU_BOARDSFOLDER.board_id_to_name($data['prefetch_boardid']).'/res/'.$data['prefetch_threadid'].'.html>; rel=prefetch';
			// TODO: figure out chrome prerender shit
		}
		cache_add($mc_id, $headers);
//	} else {
//		cache_del($mc_id);
	}
}

function get_official_thread_by_id($boardid, $threadid) {
	global $tc_db;
	
	$mc_key = "check_off_".$boardid.'_'.$threadid;
	if (false === ($res = cache_get ($mc_key))) {
		$res = $tc_db->GetOne('select official_id from posts where boardid = '. $tc_db->qstr($boardid) .' and id = ' . $tc_db->qstr($threadid));
		cache_add($mc_key, $res);
	}
	return $res;
}

function get_session_features() {
	global $tc_db;
	
	$session = md5(session_id());
	$mc_key = "ses_f_".$session;
	if (false === ($res = cache_get ($mc_key))) {
		$queue = 'select feature_id from session_features where session_md5 = '. $tc_db->qstr($session);
		$res = $tc_db->GetRow($queue);
		cache_add($mc_key, $res);
	}
	return $res;
}

function is_official_thread($subject, $boardid) {
	global $tc_db;

	//bdl_debug ('select id from official where boardid = '. $tc_db->qstr($boardid) .' and subject = ' . $tc_db->qstr(preg_replace('/[0-9]+/u', '%', $subject)));
	return (
		$tc_db->GetOne('select id from official where boardid = '. $tc_db->qstr($boardid) .' and subject = ' . $tc_db->qstr(preg_replace('/[0-9]+/u', '%', $subject)))
	);
}

function mb_preg_match_all($ps_pattern, $ps_subject, &$pa_matches, $pn_flags = PREG_PATTERN_ORDER, $pn_offset = 0, $ps_encoding = NULL) {
  // WARNING! - All this function does is to correct offsets, nothing else:
  //
  if (is_null($ps_encoding))
    $ps_encoding = mb_internal_encoding();

  $pn_offset = strlen(mb_substr($ps_subject, 0, $pn_offset, $ps_encoding));
  $ret = preg_match_all($ps_pattern, $ps_subject, $pa_matches, $pn_flags, $pn_offset);

  if ($ret && ($pn_flags & PREG_OFFSET_CAPTURE))
    foreach($pa_matches as &$ha_match)
      foreach($ha_match as &$ha_match)
        $ha_match[1] = mb_strlen(substr($ps_subject, 0, $ha_match[1]), $ps_encoding);
    //
    // (code is independent of PREG_PATTER_ORDER / PREG_SET_ORDER)

  return $ret;
  }

// based on https://mrkmg.com/posts/php-function-to-generate-a-color-from-a-text-string
$color_cache = Array();
function genColorCodeFromText($text,$min_brightness=100,$spec=2)
{
	global $color_cache;
	$text = trim($text);

	$mc_key = "color_".$text.'_'.$min_brightness.'_'.$spec;
	if ($color_cache[$mc_key]) {
		$output = $color_cache[$mc_key];
	} else {
		if (false === ($output = cache_get ($mc_key))) {

			// Check inputs
			if(!is_int($min_brightness)) throw new Exception("$min_brightness is not an integer");
			if(!is_int($spec)) throw new Exception("$spec is not an integer");
			if($spec < 2 or $spec > 10) throw new Exception("$spec is out of range");
			if($min_brightness < 0 or $min_brightness > 255) throw new Exception("$min_brightness is out of range");
			
			
			$hash = md5($text);  //Gen hash of text
			$colors = array();
			for($i=0;$i<3;$i++)
				$colors[$i] = max(array(round(((hexdec(substr($hash,$spec*$i,$spec)))/hexdec(str_pad('',$spec,'F')))*255),$min_brightness)); //convert hash into 3 decimal values between 0 and 255
				
			if($min_brightness > 0)  //only check brightness requirements if min_brightness is about 100
				while( array_sum($colors)/3 < $min_brightness )  //loop until brightness is above or equal to min_brightness
					for($i=0;$i<3;$i++)
						$colors[$i] += 10;	//increase each color by 10
						
			$output = '';
			
			for($i=0;$i<3;$i++)
				$output .= str_pad(dechex($colors[$i]),2,0,STR_PAD_LEFT);  //convert each color to hex and append to output
			
			cache_add($mc_key, $output);
			$color_cache[$mc_key] = $output;
		}
	}

	return '#'.$output;
}

function xurlencode($string) {
    $entities = array('%26', '%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D', '%2B', '%24', '%2C', '%2F', '%3F', '%25', '%23', '%5B', '%5D');
    $replacements = array('&', '!', '*', "'", "(", ")", ";", ":", "@", "&", "=", "+", "$", ",", "/", "?", "%", "#", "[", "]");
    $entities = array('%26');
    $replacements = array('&');
    return str_replace($entities, $replacements, urlencode($string));

    //foreach ($entities as $x => $y)
    //    $entities[$x] = '/' . $y . '/ui';
    //return preg_replace($entities, $replacements, $string);

    //return str_replace($entities, $replacements, $string);
}

function xdie($msg = '') {
	if ($_GET['json'] == 1) {
		die( "{ \"error\": \"$msg\", \"id\": null }" );
	} else die($msg);
}

?>
