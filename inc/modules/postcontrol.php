<?php
/* 	post control module (postcontrol.php)
	save as /inc/modules/postcontrol.php
	
	Allows post-limiting based on number of existing ips, or number of posts within a time window. 
	Partially based on something I found in a pastebin, by an unknown anon... 
	
	settings:
		globalactive:		(1|0)	use this module (1) or ignore this module (0)
		killeverything:		(1|0)	disable all posting and replies (1) or don't (0);
		disablethreads:		(1|0)	disable threads for IPs under the limit (1) or don't (0)
		disablereplies:		(1|0)	disable replies for IPs under the limit (1) or don't (0)
		limitthreadsnum:	(int)	limit new threads to this number within a specified timeframe
		limittthreadstime:	(sec)	timeframe for limiting new threads (in seconds)
		allowminips:		(int)	minimum number of matching IPs to require for the disablethreads and disablereplies filters
		allowboards:		(board)	board(s) on which this module will work. separate multiple boards with commas
*/


function postcontrol_init(){

	global $hooks, $settings, $responses, $postcontrol_module_key;
	
	$postcontrol_module_key = 'postcontrol';
	$hooks['posting'][]	= $postcontrol_module_key;
		
	$settings[$postcontrol_module_key] = array(
		'globalactive' 		=> 0,
		'killeverything'	=> 0,
		'disablethreads' 	=> 0,
		'disablereplies' 	=> 0,
		'limitthreadsnum'	=> 1,
		'limitthreadstime'	=> 3600, 
		'allowminips' 		=> 1,
		'allowboards' 		=> 'b,test'
	);
	
	$responses = array(
		'err_post_limited' 		=> 'Creation of new %s has been disabled for users with fewer than %d current posts. You only have %d, sorry.',
		'err_post_num_exceeded' => 'The maximum thread limit of %d threads per %s has already been reached. <BR /> Posting new threads may resume in %s.',
		'err_posting_disabled' 	=> 'All posting in /%s/ has been disabled.'
	);
}

function postcontrol_process_posting($post) {
	
	global $hooks, $tc_db, $settings, $responses, $postcontrol_module_key, $real_ip;
	
	$board = $post['board'];
	$auth = in_array($board, explode(',', $settings[$postcontrol_module_key]['allowboards']));
	
	// authorize board and global active flag
	if((intval($auth) * intval($settings[$postcontrol_module_key]['globalactive']) *isset($board)) == 1){
		
		// did they kill everything? 
		if($settings[$postcontrol_module_key]['killeverything'] == 1){
			exitWithErrorPage(sprintf($responses['err_posting_disabled'], $board));
		}
		
		if(filter_var($real_ip, FILTER_VALIDATE_IP)){
			$userpostcount = getpostcount($real_ip); 
			$allowminips = $settings[$postcontrol_module_key]['allowminips'];
			
			// check postcount against limit for threads and replies if minimum is not met
			if($userpostcount < $allowminips){
				if($settings[$postcontrol_module_key]['disablethreads'] == 1 && ($_POST['replythread'] == 0)){
					exitWithErrorPage(sprintf($responses['err_post_limited'], 'threads', $allowminips, $userpostcount));
				}
				if($settings[$postcontrol_module_key]['disablereplies'] == 1 && ($_POST['replythread'] > 0)){
					exitWithErrorPage(sprintf($responses['err_post_limited'], 'replies', $allowminips, $userpostcount));
				}
			}
		}
		
		// check post time against number of posts within the limited timeframe
		$limit_time = $settings[$postcontrol_module_key]['limitthreadstime'];
		$limit_num = $settings[$postcontrol_module_key]['limitthreadsnum'];
		$gettime = (time() - $limit_time);

		if($boardid = $tc_db->GetOne("SELECT `id` FROM `" . KU_DBPREFIX . "boards` WHERE `name` = " . $tc_db->qstr($board) . " ")){
		
			if($postquery = $tc_db->GetAll("SELECT COUNT(*) FROM `".KU_DBPREFIX."posts` WHERE `boardid` = ".$boardid." AND `IS_DELETED` = 0 AND `parentid` = 0  AND `timestamp` > ".$gettime." ")){
				
				if(($postquery[0]['COUNT(*)'] >= $limit_num) && ($_POST['replythread'] == 0)){
					
					// get the latest post time for this board
					$ltime = $tc_db->GetAll("SELECT timestamp FROM `".KU_DBPREFIX."posts` WHERE `boardid` = ".$boardid." AND `IS_DELETED` = 0 AND `parentid` = 0 ORDER BY `timestamp` DESC LIMIT 1");
					
					/*get time remaining until a new thread. timeDiff() found in inc/func/numberformatting.php*/
					$time_left = timeDiff(($ltime[0]['timestamp']+$limit_time),true);
					
					exitWithErrorPage(sprintf($responses['err_post_num_exceeded'], $limit_num, elapsed($limit_time), $time_left));
				}
			}
		}
	}

	return $post;
}

// get the number of active posts for an ip
function getpostcount($remoteip){
	global $tc_db;
	$upc = 0;
	if($postquery = $tc_db->GetAll("SELECT COUNT(ipmd5) FROM `".KU_DBPREFIX."posts` WHERE `IS_DELETED` = 0 AND `ipmd5` = '".md5($remoteip)."' ")){
		$upc = $postquery[0]['COUNT(ipmd5)'];
	}
	
	return $upc;
}


function elapsed($secs){
    $bit = array(
        'y' => $secs / 31556926 % 12,
        'w' => $secs / 604800 % 52,
        'd' => $secs / 86400 % 7,
        'h' => $secs / 3600 % 24,
        'm' => $secs / 60 % 60,
        's' => $secs % 60
        );
       
    foreach($bit as $k => $v)
        if($v > 0)$ret[] = $v . $k;
       
    return join(' ', $ret);
}
?>
