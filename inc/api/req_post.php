<?php

function can_process_request_post ($request) {
	if (
		(true === ($err = assert_board_name ($request['board_name']))) &&
		(true === ($err = assert_post_id ($request['post_id'])))
	) {
		return true;
	} else {
		debug (" +++ failing request +++ " . serialize ($request));
		return $err;
	}
}

function process_request_post ($request) {
	global $db, $mc, $post_fields;

	$pdo = $db->prepare ("select ". $post_fields ." from posts p join boards b on b.id = p.boardid where b.name = :board_name and p.id = :post_id and p.IS_DELETED = 0;");
	$pdo->execute (array (
		":board_name" => $request['board_name'],
		":post_id" => $request['post_id']
	));
	$result = $pdo->fetch (PDO::FETCH_ASSOC);
	return $result;
}

?>
