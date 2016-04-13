<?php
require 'config.php';
require KU_ROOTDIR . 'inc/functions.php';

$id = $_GET['id'];
bdl_debug ('download - ' . $id);
if (!is_numeric($id)) die(); // no messing around with get variables;

$result = $tc_db->GetRow("select f.name, ft.filetype, f.original from "  . KU_DBPREFIX . " files f inner join filetypes ft on ft.id = f.type where name = ". $id);

if (trim($result['original']) != '') {
	$outputfilename = $result['original'] . '.' . $result['filetype'];
}else{
	$outputfilename = $id . '.' . $result['filetype'];
}

$file=KU_SRCDIR . $result['name'] . '.' . $result['filetype'];
bdl_debug ("downloading " . $file);

if (!$result['filetype']) die ('not found.');
if (!file_exists($file)) die ('not found..');

header("Content-Type:  application/force-download");
header("Content-Disposition:  attachment; filename=\"" . basename($outputfilename) . "\";" );
header("Content-Transfer-Encoding:  binary");
readfile($file);

?>
