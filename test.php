<?php

require 'config.php';
require KU_ROOTDIR . 'inc/functions.php';
require KU_ROOTDIR . 'inc/classes/board-post.class.php';
require KU_ROOTDIR . 'inc/classes/bans.class.php';
require KU_ROOTDIR . 'inc/classes/posting.class.php';
require KU_ROOTDIR . 'inc/classes/parse.class.php';
require KU_ROOTDIR . 'inc/classes/upload.class.php';
require_once KU_ROOTDIR . 'inc/api/config.php';
require_once KU_ROOTDIR . 'inc/api/debug.php';
require_once KU_ROOTDIR . 'inc/api/cache.php';

$passcodes = $tc_db->GetAll('select * from passcode');
echo count($passcodes);
?>