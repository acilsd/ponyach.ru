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
* A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License along with
* kusaba; if not, write to the Free Software Foundation, Inc.,
* 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
* +------------------------------------------------------------------------------+
* Parse class
* +------------------------------------------------------------------------------+
* A post's message text will be passed, which will then be formatted and cleaned
* before being returned.
* +------------------------------------------------------------------------------+
*/
class Parse {
	var $boardtype;
	var $parentid;
	var $id;
	var $boardid;
	var $effects;
	var $found_replies;
	public $iscaptchapunish = false;

	var $link_regexp = 
	'/(^|[^\/\w-\.>":])((?:(?:(?:https?:|ftp:|smb:|ssh:|irc:)?\/\/)?(?:(?:[\w-]+\.)+(?:aero|asia|biz|cat|com|coop|info|int|jobs|mobi|museum|name|net|org|post|pro|tel|travel|xxx|edu|gov|mil|ac|ad|ae|af|ag|ai|al|am|an|ao|aq|ar|as|at|au|aw|ax|az|ba|bb|bd|be|bf|bg|bh|bi|bj|bm|bn|bo|br|bs|bt|bv|bw|by|bz|ca|cc|cd|cf|cg|ch|ci|ck|cl|cm|cn|co|cr|cs|cu|cv|cx|cy|cz|dd|de|dj|dk|dm|do|dz|ec|ee|eg|eh|er|es|et|eu|fi|fj|fk|fm|fo|fr|ga|gb|gd|ge|gf|gg|gh|gi|gl|gm|gn|gp|gq|gr|gs|gt|gu|gw|gy|hk|hm|hn|hr|ht|hu|id|ie|il|im|in|io|iq|ir|is|it|je|jm|jo|jp|ke|kg|kh|ki|km|kn|kp|kr|kw|ky|kz|la|lb|lc|li|lk|lr|ls|lt|lu|lv|ly|ma|mc|md|me|mg|mh|mk|ml|mm|mn|mo|mp|mq|mr|ms|mt|mu|mv|mw|mx|my|mz|na|nc|ne|nf|ng|ni|nl|no|np|nr|nu|nz|om|pa|pe|pf|pg|ph|pk|pl|pm|pn|pr|ps|pt|pw|py|qa|re|ro|rs|ru|rw|sa|sb|sc|sd|se|sg|sh|si|sj|sk|sl|sm|sn|so|sr|ss|st|su|sv|sx|sy|sz|tc|td|tf|tg|th|tj|tk|tl|tm|tn|to|tp|tr|tt|tv|tw|tz|ua|ug|uk|us|uy|uz|va|vc|ve|vg|vi|vn|vu|wf|ws|ye|yt|yu|za|zm|zw|rocks|club)|(?:(?:[01]?\d?\d|2[0-4]\d|25[0-5])\.){3}(?:[01]?\d?\d|2[0-4]\d|25[0-5]))|steam:\/\/[\w-]+)(?:\/(?:[^\s\.,;?!]|[\.,;?!][^\s])*)*\/?)([\s\.,;?!]|$)/';
	
function MakeClickable ($txt){

	// inter-ponyach links are done further
//	if (preg_match( '~http://(www\.)?(ponyach\.(ru|ga|ml|ch)|ponychan.ru|ponya.ch)/([a-z0-9]+)/res/(005|050)?([0-9]+)\.html~', $txt))
//		return $txt;

	
	$callback = create_function (
		'$matches', '
		
		$b = $matches [1];
		$d = $matches [2];
		$e = $matches [3];
		
	if (preg_match( "~http[s]?://(www\.)?(ponyach\.(ru|ga|ml|ch)|ponychan.ru|ponya.ch)/([a-z0-9]+)/res/(005|050)?([0-9]+)\.html~", $d))
		return $b.$d.$e;

		$i0 = strpos ($d, "//");
		$i1 = strpos ($d, ".");
		
		$u = $i0 === false || ($i1 !== false && $i0 > $i1) ? "//".$d : $d;
		
		if (strlen($d) > (KU_LINELENGTH / 15)) {
			$d = substr($d, 0, KU_LINELENGTH / 15) . "...";
		}
		
		return $b."<a href=\\"".$u."\\">".$d."</a>".$e;'
	);
	
	$fix_af   =  '/(\[\/?(spoiler|b|u|s|i|c|sup|sub)\])/';
	$fix_as   = '/ (\[\/?(spoiler|b|u|s|i|c|sup|sub)\])/';
	$fix_af_b = ' $1';
	$fix_as_b = '$1';
	
	$txt = preg_replace          ($fix_af, $fix_af_b, $txt);
	$txt = preg_replace_callback ($this->link_regexp, $callback, $txt);
	$txt = preg_replace_callback ($this->link_regexp, $callback, $txt);
	$txt = preg_replace          ($fix_as, $fix_as_b, $txt);
	return $txt;
}
 
	function ask_ponyaba_34 ($string) {
		return preg_match ('/поняб.*(открой|пусти|покажи).*(34|тридатьч|скрытый|закрытый)/ui', $string);
	}

	function effects ($string) {
		if (preg_match ('/@@.*@@/ui', $string)) {
			bdl_debug ("effects request found");
			preg_replace_callback("/@@([0-9]){1,9}@@/", array ($this, "effects_replace_callback"),$string);
		}
	}
	
	function effects_replace_callback ($matches) {
		foreach ($matches as $m) {
			bdl_debug ("effect " . $m);
				switch ($m) {
					case "1":
						$this->effects[] = IMG_FILTER_NEGATE;
					break;
					case "2":
						$this->effects[] = array(IMG_FILTER_COLORIZE, rand(1,255), rand(1,255), rand(1,255));
					break;
					case "3":
						$this->effects[] = IMG_FILTER_EDGEDETECT;
					break;
					case "4":
						$this->effects[] = IMG_FILTER_EMBOSS;
					break;
					case "5":
						$this->effects[] = IMG_FILTER_GAUSSIAN_BLUR;
					break;
					case "6":
						$this->effects[] = IMG_FILTER_SELECTIVE_BLUR;
					break;
					case "7":
						$this->effects[] = IMG_FILTER_MEAN_REMOVAL;
					break;
					case "8":
						$this->effects[] = array(IMG_FILTER_PIXELATE, rand(1,10), true);
					break;
					case "9":
						$this->effects[] = array(IMG_FILTER_BRIGHTNESS, rand(1,50));
					break;
					case "0":
						$this->effects[] = array(IMG_FILTER_CONTRAST, rand(1,50));
					break;
				}
		}
		return true;
	}

	function BBCode($string){
                $patterns = array(
                        '`\*\*(.+?)\*\*`is',
                        '`\*(.+?)\*`is',
                        '`%%(.+?)%%`is',
                     '`!!(.+?)!!`is',
                        '`\[b\](.+?)\[/b\]`is',
                        '`\[i\](.+?)\[/i\]`is',
                        '`\[u\](.+?)\[/u\]`is',
                        '`\[s\](.+?)\[/s\]`is',
                        '`\[aa\](.+?)\[/aa\]`is',
                        '`\[spoiler\]`is',
                        '`\[/spoiler\]`is',
                        '`\[sub\](.+?)\[/sub\]`is',
                        '`\[sup\](.+?)\[/sup\]`is',
                        );
                $replaces =  array(
                        '<b>\\1</b>',
                        '<i>\\1</i>',
                        '<span class="spoiler">\\1</span>',
                     '<span class="rcv">\\1</span>',
                        '<b>\\1</b>',
                        '<i>\\1</i>',
                        '<span style="border-bottom: 1px solid">\\1</span>',
                        '<strike>\\1</strike>',
                        '<div style="font-family: Mona,\'MS PGothic\' !important;">\\1</div>',
                        '<span class="spoiler">',
                        '</span>',
                        '<sub>\\1</sub>',
                        '<sup>\\1</sup>',
                        );
		$string = preg_replace('`%%((?!(((?!%%).)*<br\s*/>((?!%%).)*)).*?)%%`', '<span class="spoiler">\\1</span>' , $string);
		$string = preg_replace($patterns, $replaces , $string);
		$string = preg_replace_callback('`\[code\](.+?)\[/code\]`is', array(&$this, 'code_callback'), $string);

		
		return $string;
	}
	
	function code_callback($matches) {
		$return = '<pre><code class="codehigh">'
		. str_replace('*', '\*', str_replace('<br />', "\n", $matches[1])) . 
		'</code></pre>';
		
		return $return;
	}
	
	function ColoredQuote($buffer, $boardtype) {
		/* Add a \n to keep regular expressions happy */
		if (substr($buffer, -1, 1)!="\n") {
			$buffer .= "\n";
		}
	
		if ($boardtype==1) {
			/* The css for text boards use 'quote' as the class for quotes */
			$class = 'quote';
			$linechar = '';
		} else {
			/* The css for imageboards use 'unkfunc' (???) as the class for quotes */
			$class = 'unkfunc';
			$linechar = "\n";
		}
		$str_array = explode ("\n", $buffer);

		foreach ($str_array as &$line){
		        $count = null;
		        while ((($count < 7) &&(strpos($line, '&gt;') === 0)) ) {
       		        	$line = trim(substr($line, 4), ' '); $count++;
			}
       			if ($count) { 
				$line = '<span class="'.$class.--$count.'">'. str_repeat('&gt; ', $count). '&gt;' . $line .'</span>';
			}
		}

		$buffer = implode ("\n", $str_array);

		/* Remove the > from the quoted line if it is a text board */
		if ($boardtype==1) {
			$buffer = str_replace('<span class="'.$class.'">&gt;', '<span class="'.$class.'">', $buffer);
		}
	
		return $buffer;
	}
	
	function ClickableQuote($buffer, $board, $boardtype, $parentid, $boardid, $ispage = false) {
		global $thread_board_return;
		$thread_board_return = $board;
		$thread_board_id = $boardid;
		
		// sanitize "full" links like http://ponyach.ru/res/b/123.html -> >>123
		$buffer = preg_replace_callback('~http[s]?://(www\.)?(ponyach\.(ru|ga|ml|ch)|ponychan.ru|ponya.ch)/([a-z0-9]+)/res/(005|050)?([0-9]+)\.html(#([0-9]))?~', 
			function ($matches) use ($board) {
				if (is_numeric($matches[8])) {
					return '&gt;&gt;/' . $matches[4] . '/' . $matches[8];
				} else {
					return '&gt;&gt;/' . $matches[4] . '/' . $matches[6];
				}
				//return $this->InterboardQuoteCheck(Array('&gt;&gt;x'.$matches[6], $matches[4], $matches[6]));
			}, $buffer);

		/* Add html for links to posts in the board the post was made */
		$buffer = preg_replace_callback('/&gt;&gt;([r]?[l]?[f]?[q]?[0-9,\-,\,]+)/', array(&$this, 'InterthreadQuoteCheck'), $buffer);
		
		/* Add html for links to posts made in a different board */
		$buffer = preg_replace_callback('/&gt;&gt;\/([a-z0-9]+)\/([0-9]+)/', array(&$this, 'InterboardQuoteCheck'), $buffer);
		
		return $buffer;
	}
	
	function InterthreadQuoteCheck($matches) {
		global $tc_db, $ispage, $thread_board_return, $thread_board_id;

		$lastchar = '';
		// If the quote ends with a , or -, cut it off.
		if(substr($matches[0], -1) == "," || substr($matches[0], -1) == "-") {
			$lastchar = substr($matches[0], -1);
			$matches[1] = substr($matches[1], 0, -1);
			$matches[0] = substr($matches[0], 0, -1);
		}
		//if ($this->boardtype != 1 && is_numeric($matches[1])) {

			$this->found_replies[$matches[1]] = $this->boardid;
			$query = "SELECT `parentid` FROM `".KU_DBPREFIX."posts` WHERE `boardid` = " . $this->boardid . " AND `id` = ".$tc_db->qstr($matches[1]);
			$result = $tc_db->GetOne($query);
			if ($result !== '') {
				if ($result == 0) {
					$realid = $matches[1];
				} else {
					$realid = $result;
				}
			} else {
				return $matches[0];
			}

			if ($realid != $this->parentid) {
				add_prefetch($thread_board_return, $this->parentid, $thread_board_return, $realid);
				$link_tag = '<link rel="prefetch" href="'.KU_BOARDSFOLDER.$thread_board_return.'/res/'.$realid.'.html">';
			}

			$return = '<a class="irc-reflink irc-reflink-from-<!sm_postid>" style="text-decoration:none;" onclick="view_dialog(\'' . $thread_board_return . '\', <!sm_postid>, ' . $matches[1].');" href="javascript:void(0);">▲</a>' . $link_tag 
.'<a href="'.KU_BOARDSFOLDER.$thread_board_return.'/res/'.$realid.'.html#'.$matches[1].'" onclick="return highlight(\'' . $matches[1] . '\', true);" class="ref|' . $thread_board_return . '|' .$realid . '|' . $matches[1] . '">'.$matches[0].'</a>'.$lastchar;
			//$return = '</a><a href="'.KU_BOARDSFOLDER.$thread_board_return.'/res/'.$realid.'.html#'.$matches[1].'" onclick="return highlight(\'' . $matches[1] . '\', true);" class="ref|' . $thread_board_return . '|' .$realid . '|' . $matches[1] . '">'.$matches[0].'</a>'.$lastchar;
//		} else {
//			$return = $matches[0];
//			
//			$postids = getQuoteIds($matches[1]);
//			if (count($postids) > 0) {
//				$realid = $this->parentid;
//				if ($realid === 0) {
//					if ($this->id > 0) {
//						$realid = $this->id;
//					}
//				}
//				if ($realid !== '') {
//					$return = '<a href="' . KU_BOARDSFOLDER . 'read.php';
//					if (KU_TRADITIONALREAD) {
//						$return .= '/' . $thread_board_return . '/' . $realid.'/' . $matches[1];
//					} else {
//						$return .= '?b=' . $thread_board_return . '&t=' . $realid.'&p=' . $matches[1];
//					}
//					$return .= '">' . $matches[0] . '</a>';
//				}
//			}
//		}
		
		return $return;
	}
	
	function InterboardQuoteCheck($matches) {
		global $tc_db;

		$result = $tc_db->GetAll("SELECT `id`, `type` FROM `".KU_DBPREFIX."boards` WHERE `name` = ".$tc_db->qstr($matches[1])."");
		if ($result[0]["type"] != '') {
			$result2 = $tc_db->GetOne("SELECT `parentid` FROM `".KU_DBPREFIX."posts` WHERE `boardid` = " . $result[0]['id'] . " AND `id` = ".$tc_db->qstr($matches[2])."");
			if ($result2 != '') {
				if ($result2 == 0) {
					$realid = $matches[2];
				} else {
					if ($result[0]['type'] != 1) {
						$realid = $result2;
					}
				}
				
				$boardid = board_name_to_id($matches[1]);
				if ($result[0]["type"] != 1) {
					$this->found_replies[$matches[2]] = $boardid;
					return '<a href="'.KU_BOARDSFOLDER.$matches[1].'/res/'.$realid.'.html#'.$matches[2].'" class="ref|' . $matches[1] . '|' . $realid . '|' . $matches[2] . '">'.$matches[0].'</a>';
				} else {
					$this->found_replies[$realid] = $boardid;
					return '<a href="'.KU_BOARDSFOLDER.$matches[1].'/res/'.$realid.'.html" class="ref|' . $matches[1] . '|' . $realid . '|' . $realid . '">'.$matches[0].'</a>';
				}
			}
		}
		
		return $matches[0];
	}
	
	function Wordfilter($buffer, $board) {
		global $tc_db;
		
		$query = "SELECT * FROM `".KU_DBPREFIX."wordfilter`";
		$results = $tc_db->GetAll($query);

		if ($results) {
			$replace_words = Array (); $replace_replacedby = Array ();
			foreach($results as $line) {
				$array_boards = explode('|', $line['boards']);
	
				if (in_array($board, $array_boards)) {
					if($line['regex'] !== 1) {
						$line['word'] =  '/' . implode('((&#160;)|([a-zA-Z0-9_\.\-])){0,1}', preg_split('/(?<!^)(?!$)/u', $line['word'])) . '/ui';
						//$line['word'] = '/' .$line['word'] . '/ui';
					}
					$replace_words[] = $line['word'];
					$replace_replacedby[] = '<span class="unkfunc0">' .$line['replacedby']. '</span>';
				}
			}
			$count = 0;
			$buffer = preg_replace($replace_words, $replace_replacedby, $buffer, -1, $count);
			if ($count > 0) {
				$this->iscaptchapunish = true;
			}
		}
		
		return $buffer;
	}
	
	function CheckNotEmpty($buffer) {
		global $is_repost;
		/*
		$buffer_temp = str_replace("\n", "", $buffer);
		$buffer_temp = str_replace(" ", "", $buffer_temp);
		$buffer_temp = preg_replace("~<.*?>~", '', $buffer_temp);
		*/

		if ($is_repost) return $buffer;
		
		$buffer_temp = preg_replace("~<.*?>~", '', $buffer);
		//if ($buffer_temp=="") {
		if (preg_match('~[a-zа-я0-9\.\-_|+=]~ui', $buffer_temp) == 0) {
			return "";
		} else {
			return $buffer;
		}
	}
	
	/* From http://us.php.net/wordwrap */
	/*function CutWord($str, $maxLength, $char){
	    $newStr = "";
	    $openTag = false;
	    for($i=0; $i<strlen($str); $i++){
	        $newStr .= $str{$i};   
			echo 'newstr: ' . $newStr . '<hr>' . "\n";
	        if($str{$i} == "<"){
	            $openTag = true;
	            continue;
	        }
	        if(($openTag) && ($str{$i} == ">")){
	            $openTag = false;
	            continue;
	        }
	       
	        if(!$openTag){
	            if(!in_array($str{$i}, $wordEndChars)){//If not word ending char
	                $count++;
	                if($count==$maxLength){//if current word max length is reached
	                    $newStr .= $char;//insert word break char
	                    $count = 0;
	                }
	            }else{//Else char is word ending, reset word char count
	                    $count = 0;
	            }
	        }
	       
	    }//End for   
	    die($newStr);
	    return $newStr;
	}*/
	
	/*function CutWord($txt, $where) {
		if (empty($txt)) return false;
		for ($c = 0, $a = 0, $g = 0; $c<strlen($txt); $c++) {
			$d[$c+$g]=$txt[$c];
			if ($txt[$c]!=' '&&$txt[$c]!=chr(10)) $a++;
			else if ($txt[$c]==' '||$txt[$c]==chr(10)) $a = 0;
			if ($a==$where) {
			$g++;
			$d[$c+$g]="\n";
			$a = 0;
			}
		}
		
		return implode("", $d);
	}*/
	
	function CutWord($txt, $where) {
		$txt_split_primary = preg_split('/\n/', $txt);
		$txt_processed = '';
		$usemb = (function_exists('mb_substr') && function_exists('mb_strlen')) ? true : false;
		
		foreach ($txt_split_primary as $txt_split) {
			$txt_split_secondary = preg_split('/ /', $txt_split);
			
			foreach ($txt_split_secondary as $num => $txt_segment) {
				//if (strpos($txt_segment, '://') !== false) {
				if (preg_match($this->link_regexp, $txt_segment)) {
					$txt_processed .= implode(' ', array_slice($txt_split_secondary, $num)) . ' ';
					break;
				}
				$segment_length = ($usemb) ? mb_strlen($txt_segment) : strlen($txt_segment);
				while ($segment_length > $where) {
					if ($usemb) {
						$txt_processed .= mb_substr($txt_segment, 0, $where) . "\n";
						$txt_segment = mb_substr($txt_segment, $where);
						
						$segment_length = mb_strlen($txt_segment);
					} else {
						$txt_processed .= substr($txt_segment, 0, $where) . "\n";
						$txt_segment = substr($txt_segment, $where);
						
						$segment_length = strlen($txt_segment);
					}
				}
				
				$txt_processed .= $txt_segment . ' ';
			}
			
			$txt_processed = ($usemb) ? mb_substr($txt_processed, 0, -1) : substr($txt_processed, 0, -1);
			$txt_processed .= "\n";
		}
		
		return $txt_processed;
	}
	
	function ParsePost($message, $board, $boardtype, $parentid, $boardid, $ispage = false) {
		$this->boardtype = $boardtype;
		$this->parentid = $parentid;
		$this->boardid = $boardid;
		
		$message = trim($message);

		//if ($this->ask_ponyaba_34 ($message)) {
			//die ("Погляди в настройки, няша.");
		//}

		$message = replace_hidden_chars($message);
		$message = $this->CutWord($message, (KU_LINELENGTH / 15));
		$message = htmlspecialchars($message, ENT_QUOTES);
		if (KU_MAKELINKS) {
			$message = $this->MakeClickable($message);
		}
		$message = $this->ClickableQuote($message, $board, $boardtype, $parentid, $boardid, $ispage);
		$message = $this->ColoredQuote($message, $boardtype);

		$code_blocks = Array();
		
		// save all fragments of [code] to an array
		$message = preg_replace_callback('`\[code\](.+?)\[/code\]`is', function ($match) use (&$code_blocks) {
			$code_blocks[] = $match[1];
                	return '[code][/code]';
		}, $message);

		if ($code_blocks)
			$code_blocks = array_reverse($code_blocks);

		parse_wakaba_mark($message);
		$message = str_replace("\n", '<br />', $message);

		// restore saved [code] fragments
		$message = preg_replace_callback('`\[code\]\[/code\]`is', function ($match) use (&$code_blocks) {
			return '<pre><code class="codehigh">'
			. str_replace('<br />', "\n", array_pop($code_blocks)) . 
			'</code></pre>';
		}, $message);

		$message = $this->Wordfilter($message, $board);
		$message = $this->CheckNotEmpty($message);
		
		return $message;
	}
}
?>
