<?php

function can_process_request_thread_timestamp ($request) {
	if (
		(true === ($err = assert_thread_id ($request['thread_id']))) &&
		(true === ($err = assert_board_name ($request['board_name'])))
	) {
		return true;
	} else {
		debug (" +++ failing request +++ " . serialize ($request));
		return $err;
	}
}

function process_request_thread_timestamp ($request) {
	global $db, $mc;

	$pdo = $db->prepare ("select greatest(max(p.timestamp), max(p.deleted_timestamp)) as timestamp from posts p join boards b on p.boardid = b.id where p.parentid = :thread_id and b.name = :board_name");
	$pdo->execute (array (
		":board_name" => $request['board_name'],
		":thread_id" => $request['thread_id']
	));
	$result['timestamp'] = $pdo->fetchColumn();

	return $result;
}

?>
