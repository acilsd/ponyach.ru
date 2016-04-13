<?php

function can_process_request_status ($request) {
	return true;
}

function process_request_status ($request) {
	global $db, $mc, $user_ip;

	$result = ""; 

	if ($prev_status = cache_get ("status-". $user_ip)) {

		$result["last_status"] = $prev_status['timestamp'];

		// TODO to memcached
		$pdo = $db->prepare ("select p.id, b.name, IS_DELETED, timestamp from posts p join boards b on p.boardid = b.id where p.deleted_timestamp > :last_status");
		$pdo->execute (array (":last_status" => $prev_status['timestamp']));
		$deleted_posts = $pdo->fetchAll (PDO::FETCH_ASSOC);

		$result["updates"] = "none";
		if ($deleted_posts) {
			$result["updates"] = "new";
			$result["deleted_posts"] = $deleted_posts;
		}
	} else {
		$result["updates"] = "reset";
	}

	$new_status['timestamp'] = time();
	$new_status['ip'] = $_SERVER['REMOTE_ADDR'];
	cache_add ("status-". $user_ip, $new_status, 900);
	
	$pdo = $db->prepare ("select count(*) from posts where boardid = 7 AND timestamp >= :time - 3600");
	$pdo->execute (array (":time" => time()));
	$result['speed'] = (int)$pdo->fetchColumn();
	$result['count'] = cache_grep_count ("status-");

	return $result;
}

?>
