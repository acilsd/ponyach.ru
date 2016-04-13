<?php

function can_process_request_flush ($request) {
	return true;
}

function process_request_flush ($request) {

	global $mc;
	cache_flush ();
	return true;
}

?>
