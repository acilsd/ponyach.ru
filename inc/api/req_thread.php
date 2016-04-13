<?php

function can_process_request_thread ($request) {
	if (
		(true === ($err = assert_board_name ($request['board_name']))) &&
		(true === ($err = assert_thread_id ($request['thread_id'])))
	) {
		return true;
	} else {
		debug (" +++ failing request +++ " . serialize ($request));
		return $err;
	}
}

function process_request_thread ($request) {
	global $db, $mc, $post_fields;

	$pdo = $db->prepare ("select ". $post_fields ." from posts p join boards b on b.id = p.boardid where b.name = :board_name and (p.parentid = :thread_id1 or p.id = :thread_id2) and p.IS_DELETED = 0;");
	$pdo->execute (array (
		":board_name" => $request['board_name'],
		":thread_id1" => $request['thread_id'],
		":thread_id2" => $request['thread_id']
	));
	$result = $pdo->fetchAll (PDO::FETCH_ASSOC);
	return $result;
}

?>
