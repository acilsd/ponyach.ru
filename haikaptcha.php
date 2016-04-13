<?php
require 'config.php';
require KU_ROOTDIR . 'inc/functions.php';
require_once KU_ROOTDIR . 'lib/Haanga.php';

function verticalize_text($text) {
	return join("\n", preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY));
}

function make_question_image($text, $type = 1) {
	if ($type == 2) 
		return make_question_image_school($text);
	if ($type == 3) 
		return make_question_image_apples($text);

	return make_question_image_haiku($text);
}

function make_question_image_apples($text) {

	$num1 = $text[0]; $num2 = $text[1]; $operation = $text[2];

	$x1 = 60; $y1 = 50; $x2 = 210; $y2 = 50;
	$filename = preg_replace("/[^0-9]/","", uniqid(null, true));
	$filename_w_path = KU_ROOTDIR . 'images/captcha/' .$filename . '.png';
	$image = new Gmagick ();
	$draw = new GmagickDraw ();

	
	$image->read(KU_ROOTDIR . 'images/.apples'.($operation == 0 ? '+' : '-').'.png');

	$apple = new Gmagick();
	$apple->read(KU_ROOTDIR . 'images/.apple.png');

	$a_size = 27; // eee kind of. maybe.

	for ($j = 0; $j <= 1; $j++) { // second and first apple pack
		if ($j == 0) {
			$num = $num1; $x = $x1; $y = $y1;
		} else {
			$num = $num2; $x = $x2; $y = $y2;
		}
		for ($i = 1; $i <= $num ; $i++) {
			// each third apple - new line
			$left = $x + ($i < 4 ? $i : ($i - 3))*$a_size; 
			$top = ($i < 4) ? $y : ($y + $a_size);
			
			$tmp = clone $apple;
			$tmp->rotateImage(new GmagickPixel('none'), (mt_rand(0,1) ? -1 : 1)*mt_rand(0, 25));
			if (rand(1,5) == 1) 
				$tmp->resizeImage(mt_rand(30,35), 0, Gmagick::FILTER_CATROM, 1);
			$image->compositeImage($tmp, Gmagick::COMPOSITE_OVER, $left, $top + (mt_rand(0,1) ? -1 : 1)*mt_rand(0, 3));
		}
	}

	$image->write ($filename_w_path);
	
	return $filename;
}

function make_question_image_school($text) {

	$x = 80; $y = 75; $dy = 35;
	$filename = preg_replace("/[^0-9]/","", uniqid(null, true));
	$filename_w_path = KU_ROOTDIR . 'images/captcha/' .$filename . '.png';
	$image = new Gmagick ();
	$draw = new GmagickDraw ();

	$image->read(KU_ROOTDIR . 'images/.school.jpeg');

	$draw->setFillColor ('white');
	$draw->setFont (KU_ROOTDIR . 'images/.mel.ttf');
	$draw->setFontSize (20);
	$draw->setStrokeColor ('none');

	if (mb_strlen($text, 'UTF-8') > 20) {

		$words = preg_split('/\s/u', $text, -1, PREG_SPLIT_NO_EMPTY);
		$i = 0; $word = ''; $test_line = false;
		foreach ($words as $word) {

			$test_line = $test_line === false ? $word : ($test_line .= (' ' . $word));
			if (mb_strlen($test_line, 'UTF-8') > 20) {
				$image->annotateImage ($draw, $x, $y, 0, ucfirst($line));
				$test_line = $word;
				$y += $dy;
			} else $line = $test_line;
		}
		$image->annotateImage ($draw, $x, $y, 0, mb_strtolower($test_line == $word ? $word : $line, 'UTF-8'));
	} else {
		$image->annotateImage ($draw, $x, $y, 0, ucfirst($text));
	}

	$image->write ($filename_w_path);
	
	return $filename;
}

function make_question_image_haiku($text) {

	$x = 200; $y = 55; $dx = 35;
	$filename = preg_replace("/[^0-9]/","", uniqid(null, true));
	$filename_w_path = KU_ROOTDIR . 'images/captcha/' .$filename . '.png';
	$image = new Gmagick ();
	$draw = new GmagickDraw ();

	$image->read(KU_ROOTDIR . 'images/.haikaptcha.png');
	
		$draw->setFillColor ('black');
		$draw->setFont (KU_ROOTDIR . 'images/.hanzi.ttf');
		$draw->setFontSize (10);
		$draw->setStrokeColor ('none');
	
		$text = preg_replace(array('/-/u', '/—/u', '/…/u', '/\./u'), array('|', '|', '.', '.'), $text);
	
		$text = preg_split("/\n/u", $text, -1, PREG_SPLIT_NO_EMPTY);
		foreach($text as $line) {
		if (mb_strlen($line, 'UTF-8') > 19 ) {
			$tmp_line = preg_split('/\s/u', $line, -1, PREG_SPLIT_NO_EMPTY);
			$i = 0; $word = '';
			while ($i < count($tmp_line)) {
				$word = array_pop($tmp_line). ' ' .$word;
				if ((mb_strlen($line, 'UTF-8') - mb_strlen($word, 'UTF-8')) <= 19) break;
				$i++;
			}
			$image->annotateImage ($draw, $x, $y, 0, mb_strtolower(verticalize_text(join (' ', $tmp_line)), 'UTF-8'));
			$x -= $dx;
			$image->annotateImage ($draw, $x, $y, 0, mb_strtolower(verticalize_text($word), 'UTF-8'));
		} else {
			$image->annotateImage ($draw, $x, $y, 0, mb_strtolower(verticalize_text($line), 'UTF-8'));
		}
		$x -= $dx;
	}
	$image->write ($filename_w_path);
	
	return $filename;
}

function make_answer_image($text, $type = 1) {
	if ($type == 2)
		return make_answer_image_school($text);
	
	return make_answer_image_haiku($text);
}

function make_answer_image_school($text) {
	$x = 2; $y = 52;
	$filename = preg_replace("/[^0-9]/","", uniqid(null, true));
	$filename_w_path = KU_ROOTDIR . 'images/captcha/' .$filename. '.png';

	$image = new Gmagick ();
	$draw = new GmagickDraw ();

	$image->newImage(78, 178, "none");
	$image->setImageFormat('png');

	$draw->setFillColor ('black');
	$draw->setFont (KU_ROOTDIR . 'images/.lazy.ttf');
	$draw->setFontSize (30);
	$draw->setStrokeColor ('none');

	//$text = preg_replace(array('/-/u', '/—/u', '/…/u', '/\./u'), array('|', '|', '.', '.'), $text);
	$image->annotateImage ($draw, $x, $y, 0, mb_strtolower($text, 'UTF-8'));
	$image->write ($filename_w_path);

	return $filename;
}

function make_answer_image_haiku($text) {
	$x = 32; $y = 12;
	$filename = preg_replace("/[^0-9]/","", uniqid(null, true));
	$filename_w_path = KU_ROOTDIR . 'images/captcha/' .$filename. '.png';

	$image = new Gmagick ();
	$draw = new GmagickDraw ();

	$image->newImage(78, 178, "none");
	$image->setImageFormat('png');

	$draw->setFillColor ('black');
	$draw->setFont (KU_ROOTDIR . 'images/.hanzi.ttf');
	$draw->setFontSize (12);
	$draw->setStrokeColor ('none');

	$text = preg_replace(array('/-/u', '/—/u', '/…/u', '/\./u'), array('|', '|', '.', '.'), $text);
	$image->annotateImage ($draw, $x, $y, 0, mb_strtolower(verticalize_text($text), 'UTF-8'));
	$image->write ($filename_w_path);

	return $filename;
}

function generate_captcha($type = 1) {
	global $tc_db;

	$timeout = timeout_check();
	if ($timeout) { // todo: fancy js updater on timeout?
		//if ($timeout > 0) die ('придётся подождать '. $timeout . ' сек.');
		if ($timeout > 0) die ('<script type="text/javascript"><!-- haiku_wait('.$timeout.'); //--></script>');
	}

	if ($type == 2) {
		$data = generate_captcha_school();
	} elseif ($type == 3) {
		$data = generate_captcha_apples();
	} else {
		$data = generate_captcha_haiku();
	}

	$dwoo_data = $data[0]; $right_answer = $data[1];
	$dwoo_data['type'] = $type;

	load_haanga();
	Haanga::Load('haikaptcha.tpl', $dwoo_data);

	$ip = $_SERVER['REMOTE_ADDR'];
	$session = session_id();
	//$tc_db->Execute("delete from captcha_status where ipmd5 = md5(" .$tc_db->qstr($ip). ") and session_md5 = md5(" .$tc_db->qstr($session) .")");
	$tc_db->Execute("insert into captcha_status (`status`, `ipmd5`, `session_md5`, `answer`) values (1, md5(" .$tc_db->qstr($ip). "), md5(" .$tc_db->qstr($session). "), ". $tc_db->qstr($right_answer) .") on duplicate key update answer=". $tc_db->qstr($right_answer));

}

function generate_captcha_apples() {

	$operation = rand(0, 1);
	$num2 = rand(1, 5); 
	$num1 = rand($num2, 5);

	$answers = Array();
	$right_answer_num = ($operation == 0) ? ($num1+$num2) : ($num1-$num2);
	$right_answer  = make_answer_image($right_answer_num, 2);
	
	for ($i = 0; $i < 4; $i++) {
		$answer = rand(1,15);
		if ($answer == $right_answer_num) {
			$i--;
			continue;
		}
		$answers[] = make_answer_image($answer, 2);
	}

	array_push($answers, $right_answer);
	shuffle($answers);

	foreach ($answers as $num => $answer) {
		$dwoo_data['answer' .$num] = $answer; // picture png filename
	}

	$dwoo_data['question'] = make_question_image(Array($num1, $num2, $operation), 3);

	return Array($dwoo_data, $right_answer);
}

function generate_captcha_school() {
	global $tc_db;

	//$sentence = 'мама мыла раму и маленькую понечку.';
	$sentence = $tc_db->GetOne('select text from captcha_school order by rand() limit 1');
	$letters = 'аеёиоуыюябкс'; $letters = preg_split('//u', $letters, -1, PREG_SPLIT_NO_EMPTY);

	$vocals = Array(); $vocals_count = 0;
	do {
		$vocals_count = mb_preg_match_all('~[аеёиоуыэюя]~ui', $sentence, $vocals, PREG_OFFSET_CAPTURE, 0, 'UTF-8');
	} while ($vocals_count < 2);
	$vocals = $vocals[0];

	$to_remove = array_rand($vocals, 2);

	$chr_sent = preg_split('//u', $sentence, -1, PREG_SPLIT_NO_EMPTY); // utf-8 okay split string to chars
	$right_answer_text = $vocals[$to_remove[0]][0] . ',' . $vocals[$to_remove[1]][0];
	$right_answer = make_answer_image($right_answer_text, 2);

	$answers = Array();
	for ($i = 0; $i < 4; $i++) {
		$answer_text = $letters[array_rand($letters)] . ',' . $letters[array_rand($letters)];
		if ($answer_text == $right_answer_text) {
			$i--;
		} else {
			$answers[] = make_answer_image($answer_text, 2);
		}
	}
	array_push($answers, $right_answer);
	shuffle($answers);

	$chr_sent[$vocals[$to_remove[0]][1]] = '…';
	$chr_sent[$vocals[$to_remove[1]][1]] = '…';

	foreach ($answers as $num => $answer) {
		$dwoo_data['answer' .$num] = $answer; // picture png filename
	}

	$text = implode($chr_sent);
	$dwoo_data['question'] = make_question_image($text, 2);

	return Array($dwoo_data, $right_answer);
}

function generate_captcha_haiku() {
	global $tc_db;
	// use stored values if exist

	$haiku = $tc_db->GetOne('select haiku from haiku order by rand() limit 1');
	$haiku = preg_split("/ /u", $haiku, -1, PREG_SPLIT_NO_EMPTY);
	$last_word = array_pop($haiku);
	$last_word = preg_replace('~[.!?…]~u', '', $last_word);
	$haiku = join(' ', $haiku). ' ****';

	for ($x = 3; $x > 0; $x--) {
		$num = $tc_db->GetOne('select count(*) from search_keywords where word like ' . $tc_db->qstr('%' .trim(mb_substr($last_word, -1*$x, 4, 'UTF-8'))) . ' and word != '. $tc_db->qstr(trim($last_word)). ' and char_length(word) <= 12');
		if ($num > 50) {
			$tmp_words = $tc_db->GetAll('select word from search_keywords where word like ' . $tc_db->qstr('%' .trim(mb_substr($last_word, -1*$x, 4, 'UTF-8'))). ' and word != '. $tc_db->qstr(trim($last_word)). ' and char_length(word) <= 12  order by rand() limit 4');
			break;
		}
	}
	$words = array();
	foreach($tmp_words as $word) array_push($words, $word['word']);

	$answers = array ();
	foreach ($words as $num => $word) {
		$answers[] = make_answer_image($word);
	}

	$right_answer = make_answer_image($last_word);
	array_push($answers, $right_answer);
	shuffle($answers);

	foreach ($answers as $num => $answer) {
		$dwoo_data['answer' .$num] = $answer; // picture png filename
	}

	$dwoo_data['question'] = make_question_image($haiku);

	return Array($dwoo_data, $right_answer);

}

function delete_captcha() {
	global $tc_db;

	bdl_debug ("deleting captcha");
	$tc_db->Execute('delete from captcha_status where session_md5 = md5('.$tc_db->qstr(session_id()).') and status = 0');
	die ("deleted");
}

function check_answer() {
	global $tc_db;

	$ip = $_SERVER['REMOTE_ADDR'];
	$answer = $_GET['a'];
	//if (strlen($answer) != 23) die ('stay out of my shed!!');
	$chk = $tc_db->GetOne('select status from captcha_status where status = 1 and ipmd5 = md5('.$tc_db->qstr($ip).') and session_md5 = md5('.$tc_db->qstr(session_id()).') and answer = ' .$tc_db->qstr($answer));
	if ($chk) {
		$tc_db->Execute('update captcha_status set status = 0, retry = 0 where ipmd5 = md5('.$tc_db->qstr($ip).') and session_md5 = md5('.$tc_db->qstr(session_id()).') and answer = ' .$tc_db->qstr($answer));

		$tc_db->Execute('update captcha_status set punish = punish - 1 where session_md5 = md5('. $tc_db->qstr(session_id()) .') and punish > 0');
		$tc_db->Execute('update captcha_status set tempaccess = 1 where session_md5 = md5('. $tc_db->qstr(session_id()) .')');
		die ('ты умница.<script type="text/javascript"><!-- haiku_check(); //--></script>');
	} else {
		// okay, lets give them another try
		$tc_db->Execute('update captcha_status set retry = retry + 1 where ipmd5 = md5('.$tc_db->qstr($ip).') and session_md5 = md5('.$tc_db->qstr(session_id()).')');
		generate_captcha();
	}
}

function timeout_check($answer = false) {
	global $tc_db;

	$ip = $_SERVER['REMOTE_ADDR'];
	//if ($answer) {
		return $tc_db->GetOne('select (unix_timestamp(timestamp) + 2*retry) - unix_timestamp() from captcha_status where status = 1 and ipmd5 = md5('.$tc_db->qstr($ip).') and session_md5 = md5('.$tc_db->qstr(session_id()).')');
	//} else {
		//return $tc_db->GetOne('select (unix_timestamp(timestamp) + 5) - unix_timestamp() from captcha_status where status = 1 and ipmd5 = md5('.$tc_db->qstr($ip).') and session_md5 = md5('.$tc_db->qstr(session_id()).')');
	//}
}

function is_captcha_needed() {
	global $tc_db;
	$ta = $tc_db->GetOne('select tempaccess from captcha_status where session_md5 = md5('. $tc_db->qstr(session_id()) .')');
	if ($ta == 1) {
		return false;
	}
	return false === $tc_db->GetOne('select status from captcha_status where session_md5 = md5('. $tc_db->qstr(session_id()) .') and status = 0 limit 1');
}

//if (!is_captcha_needed()) {
//	die ('вам не нужно вводить капчу.');
//}

$type = 1;
if (isset($_COOKIE['captcha_type']) && is_numeric($_COOKIE['captcha_type']))
	$type = $_COOKIE['captcha_type'];
if (isset($_GET['t']) && is_numeric($_GET['t']))
	$type = $_GET['t'];

switch ($_GET['m']) {
	case "del":
		delete_captcha();
		break;
	case "get":
		generate_captcha($type);
		break;
	case "chk":
		check_answer();
		break;
	case "isndn":
		echo (int)is_captcha_needed();
		break;
}

?>
