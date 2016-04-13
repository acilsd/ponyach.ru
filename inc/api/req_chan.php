<?php

function can_process_request_chan ($request) {
	return true;
}

function process_request_chan ($request) {
	global $db, $mc, $board_fields, $static_pages_fields;

	$pdo = $db->prepare ("select ". $board_fields . " from boards b where section != 0 order by 'order';");
	$pdo->execute ();
	$result1 = $pdo->fetchAll (PDO::FETCH_ASSOC);
	
	$pdo = $db->prepare ("select ". $static_pages_fields . " from static_pages s order by 'order';");
	$pdo->execute ();
	$result2 = $pdo->fetchAll (PDO::FETCH_ASSOC);

	return array_merge ($result1, $result2);
}
?>
