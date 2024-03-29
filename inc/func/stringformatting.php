<?php
/**
 * Format the display of the name and tripcode of a post
 *
 * @param string $name Name used in post
 * @param string $email Email used in post
 * @param string $tripcode Tripcode used in post
 * @return string Generated name and tripcode html
 */

function formatDate($timestamp, $type = 'post', $locale = 'ru', $email = '') {
	$output = '';
		if ($email != '') {
				$output .= '<a href="mailto:' . $email . '">';
		}

	if ($type == 'post') {
		if ($locale == 'ru') {
			/* Format the timestamp OPPAN RUSSIAN STYLE */
			$fulldate = strftime ("DAYOFWEEK %d MMM %Y %H:%M:%S", $timestamp);
			$dayofweek = strftime('%a', $timestamp);
			$monthofdate = strftime('%m', $timestamp);


			/* I don't like this method, but I can't rely on PHP's locale settings to do it for me... */
			switch ($dayofweek) {
			case 'Sun':
				$dayofweek = 'Воскресенье';
				break;

			case 'Mon':
				$dayofweek = 'Понидельник';
				break;

			case 'Tue':
				$dayofweek = 'Вторник';
				break;

			case 'Wed':
				$dayofweek = 'Среда';
				break;

			case 'Thu':
				$dayofweek = 'Четверг';
				break;

			case 'Fri':
				$dayofweek = 'Пятница';
				break;

			case 'Sat':
				$dayofweek = 'Суббота';
				break;

			default:
				// The date must be in the correct language already, so let's convert it to unicode if it isn't already.
				$dayofweek = mb_convert_encoding($dayofweek, "UTF-8", "JIS, eucjp-win, sjis-win");
				break;

			}

		        switch($monthofdate) {
			case '01': case '1':
				$monthofdate='Янв'; break;
			case '02': case '2':
				$monthofdate='Фев'; break;
			case '03': case '3':
				$monthofdate='Мар'; break;
			case '04': case '4': 
				$monthofdate='Апр'; break;
			case '05': case '5': 
				$monthofdate='Май'; break;
			case '06': case '6': 
				$monthofdate='Июнь'; break;
			case '07': case '7': 
				$monthofdate='Июль'; break;
		    case '08': case '8': 
				$monthofdate='Авг'; break;
			case '09': case '9': 
				$monthofdate='Снт'; break;
            case '10': case '10': 
				$monthofdate='Окт'; break;
			case '11': case '11':
				$monthofdate='Ноя'; break;
			case '12': case '12':
				$monthofdate='Дек'; break;
				
				
				/*ноябрь, декабрь... мне лень**/
			default: break;
			}


//			$fulldate = formatJapaneseNumbers($fulldate);
			//Convert the symbols for year, month, etc to unicode equivalents. We couldn't do this above beause the numbers would be formatted to japanese.
//			$fulldate = str_replace(array("y","m","d","H","M","S"), array("&#24180;","&#26376;","&#26085;","&#26178;","&#20998;","&#31186;"), $fulldate);
			$fulldate = str_replace('DAYOFWEEK', $dayofweek, $fulldate);
			$fulldate = str_replace('MMM',$monthofdate, $fulldate);
			return $output.$fulldate.(($email != '') ? ('</a>') : (""));
		} else {
			/* Format the timestamp english style */
			return $output.date('D d/m/y H:i', $timestamp).(($email != '') ? ('</a>') : (""));
		}
	}

	return $output.date('y/m/d(D)H:i', $timestamp).(($email != '') ? ('</a>') : (""));
}

/**
 * Format the provided input into a reflink, which follows the Japanese locale if it is set.
 */
function formatReflink($post_board, $post_thread_start_id, $post_id, $locale = 'ru') {
	$return = '	';

	$reflink_noquote = '<a href="' . KU_BOARDSFOLDER . $post_board . '/res/' . $post_thread_start_id . '.html#' . $post_id . '" onclick="return highlight(\'' . $post_id . '\');">';

	$reflink_quote = '<a href="' . KU_BOARDSFOLDER . $post_board . '/res/' . $post_thread_start_id . '.html#' . $post_id . '" onclick="window.scrollTo(0,0); return insert(\'>>' . $post_id . '\\n\');">';

	if ($locale == 'ja') {
		$return .= $reflink_quote . formatJapaneseNumbers($post_id) . '</a>' . $reflink_noquote . '番</a>';
	} else {
		$return .= $reflink_noquote . 'No.&nbsp;' . '</a>' . $reflink_quote . $post_id . '</a>';
	}

	return $return . "\n";
}

/**
 * Формируем реплайлинк
 */
function formatReplylink($post_board, $post_thread_start_id, $post_id, $locale = 'ru') {
	$return = '	'; // хуясе форматируем. чир.


	return $replylink;
}

/**
 * Calculate the different name and tripcode for the name field provided
 *
 * @param string $post_name Text entered in the Name field
 * @return array Name and tripcode
 */
function calculateNameAndTripcode($post_name) {
	global $tc_db;

	if(preg_match("/(#|!)(.*)/", $post_name, $regs)){
		$cap = $regs[2];
		$cap_full = '#' . $regs[2];

		// {{{ Special tripcode check

		$trips = unserialize(KU_TRIPS);
		if (count($trips) > 0) {
			if (isset($trips[$cap_full])) {
				$forcedtrip = $trips[$cap_full];
				return array(preg_replace("/(#)(.*)/", "", $post_name), $forcedtrip);
			}
		}

		
		// }}}

		if (function_exists('mb_convert_encoding')) {
			$recoded_cap = mb_convert_encoding($cap, 'SJIS', 'UTF-8');
			if ($recoded_cap != '') {
				$cap = $recoded_cap;
			}
		}

		if (strpos($post_name, '#') === false) {
			$cap_delimiter = '!';
		} elseif (strpos($post_name, '!') === false) {
			$cap_delimiter = '#';
		} else {
			$cap_delimiter = (strpos($post_name, '#') < strpos($post_name, '!')) ? '#' : '!';
		}

		if (preg_match("/(.*)(" . $cap_delimiter . ")(.*)/", $cap, $regs_secure)) {
			$cap = $regs_secure[1];
			$cap_secure = $regs_secure[3];
			$is_secure_trip = true;
		} else {
			$is_secure_trip = false;
		}

		$tripcode = '';

		if ($cap != '') {
			/* From Futabally */
			$cap = strtr($cap, "&amp;", "&");
			$cap = strtr($cap, "&#44;", ", ");
			$salt = substr($cap."H.", 1, 2);
			$salt = preg_replace("/[^\.-z]/", ".", $salt);
			$salt = strtr($salt, ":;<=>?@[\\]^_`", "ABCDEFGabcdef");
			$tripcode = substr(crypt($cap, $salt), -10);
		}

		if ($is_secure_trip) {
			if ($cap != '') {
				$tripcode .= '!';
			}

			$secure_tripcode = md5($cap_secure . KU_RANDOMSEED);
			if (function_exists('base64_encode')) {
				$secure_tripcode = base64_encode($secure_tripcode);
			}
			if (function_exists('str_rot13')) {
				$secure_tripcode = str_rot13($secure_tripcode);
			}

			$secure_tripcode = substr($secure_tripcode, 2, 10);

			$tripcode .= '!' . $secure_tripcode;
		}
		

		$name = preg_replace("/(" . $cap_delimiter . ")(.*)/", "", $post_name);


		//uncomment to disable tripcodes
		//return $name;
		return array($name, $tripcode);
	}

	return $post_name;
}

/**
 * Format a long message to be shortened if it exceeds the allowed length on a page
 *
 * @param string $message Post message
 * @param string $board Board directory
 * @param integer $threadid Thread ID
 * @param boolean $page Is rendering for a page
 * @return string The formatted message
 */
function formatLongMessage($message, $board, $threadid, $page) {
	$output = '';
	if ((strlen($message) > KU_LINELENGTH || count(explode('<br />', $message)) > 15) && $page) {
		$message_exploded = explode('<br />', $message);
		$message_shortened = '';
		for ($i = 0; $i <= 14; $i++) {
			if (isset($message_exploded[$i])) {
				$message_shortened .= $message_exploded[$i] . '<br />';
			}
		}
		if (strlen($message_shortened) > KU_LINELENGTH) {
			$message_shortened = mb_strimwidth($message_shortened, 0, KU_LINELENGTH, "...");
			//$message_shortened = substr($message_shortened, 0, KU_LINELENGTH);
		}
		$message_shortened = closeOpenTags(remove_unclosed_tag($message_shortened));
		
		//if (strrpos($message_shortened,"<") > strrpos($message_shortened,">")) {
		//	//We have a partially opened tag we need to get rid of.
		//	$message_shortened = substr($message_shortened, 0, strrpos($message_shortened,"<"));
		//}
		
		$output = $message_shortened . '<div class="abbrev">' . "\n" .
		'	' . sprintf(_gettext('Message too long. Click %shere%s to view the full text.'), '<a href="' . KU_BOARDSFOLDER . $board . '/res/' . $threadid . '.html">', '</a>') . "\n" .
		'</div>' . "\n";
	} else {
		$output .= $message . "\n";
	}

	return $output;

}

function remove_unclosed_tag($message) {
	// we have two cases here
	if (
		// if the string has at least one closed tag
		// we need to check if it is not something like
		// "message text <tag1> more text <tag2"
		(( strrpos($message,">") !== false) && (strrpos($message,"<") > strrpos($message,">")))
			||
		// if the string has no closed tags at all
		// we need to check if it is not something like
		// "message test <tag1"
		(( strrpos($message,">") === false) && (strrpos($message,"<") !== false))
	) {
		return substr($message, 0, strrpos($message,"<"));
	} else {
		return $message;
	}
}

//function for cut long op post
function repeat_check() {
	if (strrpos($message_shortened,"<") > strrpos($message_shortened,">")) {
		if (empty($ne_povezlo)){
			$ne_povezlo = KU_LINELENGTH + 20;
		}
			$message_shortened = mb_strimwidth($message_shortened, 0, $ne_povezlo, "...");
			$ne_povezlo = $ne_povezlo + 20;
	repeat_check();					
	}
}

/* Thanks milianw - php.net */
/**
 * Closes all HTML tags left open
 *
 * @param string $html HTML to be checked
 * @return string HTML with all tags closed
 */
function closeOpenTags($html){
	/* Put all opened tags into an array */
	preg_match_all("#<([a-z]+)( .*)?(?!/)>#iU", $html, $result);
	$openedtags=$result[1];

	/* Put all closed tags into an array */
	preg_match_all("#</([a-z]+)>#iU", $html, $result);
	$closedtags=$result[1];
	$len_opened = count($openedtags);
	/* All tags are closed */
	if(count($closedtags) == $len_opened){
		return $html;
	}
	$openedtags = array_reverse($openedtags);
	/* Close tags */
	for($i=0;$i<$len_opened;$i++) {
		if ($openedtags[$i]!='br') {
			if (!in_array($openedtags[$i], $closedtags)){
				$html .= '</'.$openedtags[$i].'>';
			} else {
				unset($closedtags[array_search($openedtags[$i], $closedtags)]);
			}
		}
	}
	return $html;
}

/* By Darien Hager, Jan 2007 */
/**
 * Find the string value of a pair of ords
 *
 * @param string $ords Ords
 * @param string $encoding Encoding
 * @return string String
 */
function ords_to_unistr($ords, $encoding = 'UTF-8'){
	if (!function_exists('mb_convert_encoding')) {
		return false;
	}
	/* Turns an array of ordinal values into a string of unicode characters */
	$str = '';
	for($i = 0; $i < sizeof($ords); $i++){
		/* Pack this number into a 4-byte string
		(Or multiple one-byte strings, depending on context.) */
		$v = $ords[$i];
		$str .= pack("N",$v);
	}
	$str = mb_convert_encoding($str,$encoding,"UCS-4BE");
	return($str);
}


/**
 * Find the ord value of a string
 *
 * @param string $str String
 * @param string $encoding Encoding
 * @return array Ords
 */
function unistr_to_ords($str, $encoding = 'UTF-8'){
	if (!function_exists('mb_convert_encoding')) {
		return false;
	}
	/* Turns a string of unicode characters into an array of ordinal values,
	Even if some of those characters are multibyte. */
	$str = mb_convert_encoding($str,"UCS-4BE",$encoding);
	$ords = array();

	/* Visit each unicode character */
	for($i = 0; $i < mb_strlen($str,"UCS-4BE"); $i++){
		/* Now we have 4 bytes. Find their total numeric value */
		$s2 = mb_substr($str,$i,1,"UCS-4BE");
		$val = unpack("N",$s2);
		$ords[] = $val[1];
	}
	return($ords);
}

function processPost($id, $newthreadid, $oldthreadid, $board_from, $board_to, $boardid) {

	global $tc_db;

	$message = $tc_db->GetOne("SELECT `message` FROM " . KU_DBPREFIX . "posts WHERE `boardid` = " . $boardid . " AND `id` = " . $id . " LIMIT 1");

	if ($message != '') {
		$message_new = str_replace('/read.php/' . $board_from . '/' . $oldthreadid, '/read.php/' . $board_to . '/' . $newthreadid, $message);

		if ($message_new != $message) {
			$tc_db->GetOne("UPDATE " . KU_DBPREFIX . "posts SET `message` = " . $tc_db->qstr($message) . " WHERE `boardid` = " . $boardid . " AND `id` = " . $id);
		}
	}
}
?>
