<?php

// list of request processors

require_once ($cf['PN_ROOTDIR']) . "inc/api/req_board.php";
require_once ($cf['PN_ROOTDIR']) . "inc/api/req_chan.php";
require_once ($cf['PN_ROOTDIR']) . "inc/api/req_flush.php";
require_once ($cf['PN_ROOTDIR']) . "inc/api/req_new.php";
require_once ($cf['PN_ROOTDIR']) . "inc/api/req_post.php";
require_once ($cf['PN_ROOTDIR']) . "inc/api/req_status.php";
require_once ($cf['PN_ROOTDIR']) . "inc/api/req_thread.php";
require_once ($cf['PN_ROOTDIR']) . "inc/api/req_threadts.php";

$no_cache_modes = array ("new", "status", "flush", "thread_timestamp");

?>
