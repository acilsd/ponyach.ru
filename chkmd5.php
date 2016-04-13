<?php

// // картинки двоятся, раз чинить руки не доходят, выключу пока.
// die('');

require 'config.php';
require KU_ROOTDIR . 'inc/functions.php';

$md5 = $_GET['x'];// $board = $_GET['y'];
//if (!(ctype_alnum($md5) && ctype_alnum($board)))
if (!(ctype_alnum($md5))) {
	die(''); // no messing around with get variables;
}

$result = $tc_db->GetRow("select concat(f.name,'s.',ft.filetype) as name, f.md5 from files f inner join filetypes ft where f.md5_light = ". $tc_db->qstr($md5). " limit 1");
if ($result) {
	$fileName = substr($result['name'], 0, -3); //filename with dot at the end
	$fileTypes = ['png', 'jpg', 'gif'];
	foreach ($fileTypes as $fileType) {
		if (file_exists( KU_THUMBDIR.$fileName.$fileType)) {
			echo 'true';
			break;
		}
	}
}
// if ($result && file_exists( KU_THUMBDIR.$result['name'] ))
// 	echo 'true';