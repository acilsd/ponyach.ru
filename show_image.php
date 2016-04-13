<?php
require 'config.php';
require KU_ROOTDIR . 'chk_session.php';

$img_types = array('0', 'gif', 'jpg', 'png', 'swf', 'psd', 'bmp', 'tiff', 'tiff', 'jpc', 'jpc2', 'jpx', 'jb2', 'swc', 'iff', 'wbmp', 'xbm', 'ico');

$uri = explode('/', $_SERVER ["REQUEST_URI"]);

bdl_debug("uri = " .$uri);

$board = $uri[1]; $dir = $uri[2]; $file = $uri[3];
if (!ctype_alnum ($dir)) die(); // no messing around with get variables;
if (!ctype_alnum ($board)) die();
if (!ctype_alnum (preg_replace ('/\./', '', $file))) die(); 

$filename = KU_ROOTDIR . $board . '/' . $dir . '/' . $file;

if (file_exists ($filename)) {
	header ("Cache-Control: private");
#	header ("Cache-Control: no-cache, must-revalidate"); //HTTP 1.1
#	header ("Pragma: no-cache"); //HTTP 1.0
	header ("Content-Type:  " . 'image/' . $img_types[exif_imagetype ($filename)]);
	readfile ($filename);
} else {
	header("Cache-Control: no-cache");
	header("Location: /404.html");
}

?>
