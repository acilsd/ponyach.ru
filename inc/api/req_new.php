<?php

function can_process_request_new ($request) {
	if (
		(true === ($err = assert_timestamp ($request['timestamp']))) &&
		(true === ($err = assert_board_name ($request['board_name']))) &&
		(true === ($err = assert_thread_id ($request['thread_id'])))
	) {
		return true;
	} else {
		debug (" +++ failing request +++ " . serialize ($request));
		return $err;
	}
}

function process_request_new ($request) {
	global $db, $mc, $post_fields, $deleted_post_fields;

	// new posts
	$pdo = $db->prepare ("select ". $post_fields ." from posts p join boards b on b.id = p.boardid where b.name = :board_name and p.parentid = :thread_id and IS_DELETED = 0 and timestamp > :timestamp");
	$pdo->execute (array (
		":board_name" => $request['board_name'],
		":thread_id" => $request['thread_id'],
		":timestamp" => $request['timestamp']
	));
	$result1 = $pdo->fetchAll (PDO::FETCH_ASSOC);

	// recently deleted posts
	$pdo = $db->prepare ("select ". $deleted_post_fields ." from posts p join boards b on b.id = p.boardid where b.name = :board_name and p.parentid = :thread_id and IS_DELETED = 1 and deleted_timestamp > :timestamp");
	$pdo->execute (array (
		":board_name" => $request['board_name'],
		":thread_id" => $request['thread_id'],
		":timestamp" => $request['timestamp']
	));
	$result2 = $pdo->fetchAll (PDO::FETCH_ASSOC);
	return array_merge ($result1, $result2);
 
}

?>
