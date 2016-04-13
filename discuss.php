<?php
require_once ("config.php");
require_once ("inc/func/ip.php");
require_once ("inc/func/custom.php");

//$session = session_id();

//$chk_session = $tc_db->GetOne('select count(*) from posts p left join boards b on p.boardid = b.id where b.name = ' .$tc_db->qstr($_GET['b']). ' and session_md5 = md5(' .$tc_db->qstr($session).') limit 1');

//if ($chk_session == 0) die ('<span class="close-irc" onclick="hide_dialog();"><a style="text-decoration:none;" href="javascript:void(0);">✖</a></span><br>Я не поняла кто ты.');

$rcpt_session_md5 = $tc_db->GetOne('select session_md5 from posts p left join boards b on p.boardid = b.id where b.name = ' .$tc_db->qstr($_GET['b']). ' and p.id = ' .$tc_db->qstr($_GET['p']));
$session_md5 = $tc_db->GetOne('select session_md5 from posts p left join boards b on p.boardid = b.id where b.name = ' .$tc_db->qstr($_GET['b']). ' and p.id = ' .$tc_db->qstr($_GET['r']));

function dump_table_row($id, $name, $message, $pic = false, $pic_type = '', $pic_w = 0, $pic_h = 0, $board = '') {

	$out = "<tr>";
	if ($pic) {
		$max_h = 150; $max_w = 150;
		$ratio = $pic_h / $pic_w;
		if ($pic_w > $max_w || $pic_h > $max_h) {
			if ($pic_w > $pic_h) {
				$rat = $max_w / $pic_w;
				$pic_w = $max_w;
				$pic_h = $pic_h * $rat;
			} else {
				$rat = $max_h / $pic_h;
				$pic_h = $max_h;
				$pic_w = $pic_w * $rat;

			}
		}
		$out .= "<td class='postername reply'><span id='thumb".$id."'><img src='/".$board."/thumb/".$pic."s.".$pic_type."' width=".$pic_w." height=".$pic_h."></span><br>";
	} else {
		$out .= "<td class='postername reply'>";
	}
	//$out .= "<td>" . date("H:i", $time) . "</td><td class=postername>" . ($name ? $name : "Аноним") . "</td><td>" . $message . "</td></tr>";
	$out .= ($name ? $name : "Аноним") . "</td><td class='reply'>" . $message . "</td></tr>";
	return $out;
}

function dump_table_head() {
	return '<span class="close-irc" onclick="hide_dialog();"><a style="text-decoration:none;" href="javascript:void(0);">✖</a></span><table cellspacing="2" cellpadding="1" border="1" width="100%">';
}

function dump_table_tail() {
	return '</table>';
}

if ($rcpt_session_md5) {

	//if (md5($session) === $rcpt_session_m5) die('<span class="close-irc" onclick="hide_dialog();"><a style="text-decoration:none;" href="javascript:void(0);">✖</a></span>Разговоры с самим собой это плохо.');
	
//	$query = "
//		select t.board, t.reply_file, t.reply_file_type, t.reply_file_w, t.reply_file_h, t.post_file, t.post_file_type, t.post_file_h, t.post_file_w, t.reply_time, t.time, t.reply_name, t.p_name as 'name', t.post_id as 'post_id', @pv := t.reply_on as 'reply_id', t.message, t.reply_message 
//		from 
//		(select b.name as 'board', p.file as 'post_file', p.file_type as 'post_file_type', p.thumb_w as 'post_file_w', p.thumb_h as 'post_file_h', p2.file as 'reply_file', p2.file_type as 'reply_file_type', p2.thumb_w as 'reply_file_w', p2.thumb_h as 'reply_file_h', p.timestamp as 'time', p2.timestamp as 'reply_time', p.name as 'p_name', p2.name as 'reply_name', p.message, p2.message as reply_message, p.id as 'post_id', p.name, pr1.replyid as 'reply_on', p2.name as 'reply_to' from posts p 
//		left join boards b on p.boardid = b.id 
//		inner join posts_replies pr1 on pr1.postid = p.id 
//		inner join posts p2 on p2.id = pr1.replyid 
//		where b.name = ".$tc_db->qstr($_GET['b'])." and (p.session_md5 = ".$tc_db->qstr($rcpt_session_md5)." or p.session_md5 = md5(".$tc_db->qstr($session).")) and (p2.session_md5 = md5(".$tc_db->qstr($session).") or p2.session_md5 = ".$tc_db->qstr($rcpt_session_md5).") and p.email != 'sage' order by p.id desc limit 100) t 
//		join 
//		(select @pv := ".$tc_db->qstr($_GET['p']).") t2 where @pv = t.post_id;";

	$query = "select 
	b.name as board, p.message, p.id, t3.replyid, p.name, f.name as file, ft.filetype as file_type, f.thumb_h, f.thumb_w 
	from 
		(select 
			@pv, postid, boardid, replyid, @pv := replyid 
			from 
				(select * from posts_replies 
				where 
					(session_md5 = ".$tc_db->qstr($rcpt_session_md5)." 
				         and reply_md5 = ".$tc_db->qstr($session_md5).") 
				or 
					(session_md5= ".$tc_db->qstr($session_md5)." 
					and reply_md5 = ".$tc_db->qstr($rcpt_session_md5).") 
				order by postid desc limit 100 )t1 join (select @pv := ".$tc_db->qstr($_GET['p']).") t2  where t1.postid = @pv 
		) t3 
	inner join 
		posts p 
		on t3.postid = p.id and t3.boardid = p.boardid 
	inner join 
		boards b 
		on p.boardid = b.id 
	left join 
		posts_files pf
		on pf.boardid = p.boardid and pf.postid = p.id
	left join
		files f
		on f.id = pf.fileid
	left join
		filetypes ft
		on ft.id = f.type

	where p.email != 'sage' and (premod = 0 or session_md5= ".$tc_db->qstr($session_md5).") and b.name = ".$tc_db->qstr($_GET['b']) . " and (pf.`order` is null or pf.`order` = 1)";
	
	//bdl_debug ($query);
	
	$res = $tc_db->GetAll($query);

	if (count($res) == 0 || $res == false) die ('<span class="close-irc" onclick="hide_dialog();"><a style="text-decoration:none;" href="javascript:void(0);">✖</a></span><br>Не получилось разобрать беседу.');
	
	//var_dump($res);

	$res = array_reverse($res);
	$query = "select b.name as board, p.message, p.id, p.name, f.name as file, ft.filetype as file_type, f.thumb_h, f.thumb_w from posts p inner join boards b on p.boardid = b.id left join posts_files pf on pf.boardid = p.boardid and pf.postid = p.id left join files f on pf.fileid = f.id left join filetypes ft on f.type = ft.id where p.id = ". $tc_db->qstr($res[0]['replyid']). " and b.name = ".$tc_db->qstr($_GET['b']) . " and (pf.`order` is null or pf.`order` = 1)";
	$res2 = $tc_db->GetRow($query);
	//bdl_debug($query);
	$first = true;
	$discuss = Array();

	$out = dump_table_head();
	$out .= dump_table_row($res2['id'], $res2['name'], str_replace("<!sm_postid>", '', $res2['message']), $res2['file'], $res2['file_type'], $res2['thumb_w'], $res2['thumb_h'], $res2['board']);
	foreach ($res as $row => $data) {
		//echo "__________________________________<br>";
		//echo 'r = '. $data['reply_message'];
		//echo 'm = '. $data['message'];
		//echo $data['post_id'] ."<br>";
		//echo strip_reply_text($data['message'], $data['reply_id']). "<br>";
		//echo "__________________________________<br>";
//		if ($first) {
//			$out .= dump_table_row($data['post_id'], $data['reply_time'], $data['reply_name'], $data['reply_message'], $data['reply_file'], $data['reply_file_type'], $data['reply_file_w'], $data['reply_file_h'], $data['board']);
//			$out .= dump_table_row($data['post_id'], $data['time'], $data['name'], strip_reply_text($data['message'], $data['reply_id']), $data['post_file'], $data['post_file_type'], $data['post_file_w'], $data['post_file_h'], $data['board']);
//			$first = false;
//		} else {
//			$out .= dump_table_row($data['post_id'], $data['time'], $data['name'], strip_reply_text($data['message'], $data['reply_id']), $data['post_file'], $data['post_file_type'], $data['post_file_w'], $data['post_file_h'], $data['board']);
//		}
			$out .= dump_table_row($data['id'], $data['name'], strip_reply_text($data['message'], $data['replyid']), $data['file'], $data['file_type'], $data['thumb_w'], $data['thumb_h'], $data['board']);
	}
	
	$out .= dump_table_tail();

	echo $out;
	//var_dump ($discuss);

} else die ('<span class="close-irc" onclick="hide_dialog();"><a style="text-decoration:none;" href="javascript:void(0);">✖</a></span><br>Я не могу понять, чей это пост. Это, кстати, очень странно.');

?>
