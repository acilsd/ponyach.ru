<?php

// not really asserts now, but lets call it that way
// no mercy to invalid parameters
function assert_board_name ($board_name) {return (isset ($board_name) && ctype_alnum($board_name)) ? true : "bad board_name"; }
function assert_post_id ($post_id) {return (isset ($post_id) && is_numeric($post_id)) ? true : "bad post_id"; }
function assert_thread_id ($thread_id) {return (isset ($thread_id) && is_numeric($thread_id)) ? true : "bad thread_id"; }
function assert_page ($page) {return (isset ($page) && is_numeric($page)) ? true : "bad page"; }

function assert_timestamp ($timestamp) {
	global $cf;
	if ($timestamp < (time() - $cf['PN_MAX_TIMESTAMP_DELTA'])) {
		// too old (~_(\
		debug (" a very old timestamp requested. not gona happen, failing.");
		return "too old";
	}
	return (isset ($timestamp) && is_numeric($timestamp)) ? true : false; 
}

?>
