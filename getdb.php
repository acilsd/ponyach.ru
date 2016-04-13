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


//derpibooru stuff
if (isset($_GET['q'])){
	$query = $_GET['q'];
	
	if (isset($_GET['page'])) {
		$page = $_GET['page'];
	} else {
		$page = '1';
	}

	if (isset($_GET['apikey']) && !empty($_GET['apikey'])){
		$skey = $_GET['apikey'];
	} else {
		$skey = 'ySUFGs8asnfGzDqYtfxZ';
	}

	$url = 'https://derpibooru.org/search.json?q=' . $query . '&key=' . $skey . '&page=' . $page;
	$content = file_get_contents($url);
	echo $content;
}

//repairs page stuff
if (isset($_GET['repairs'])){
		$all_images = glob("/var/www/ponyach/images/thumb/{*.jpg, *.JPG, *.JPEG, *.png, *.PNG}", GLOB_BRACE);
		shuffle($all_images);
		$images = array();
		foreach ($all_images as $index => $image) {
			 if ($index == 50) break; 
			 $image_name = basename($image);
			 $image_full = str_replace("s", "", $image_name);
			 echo "<a class='desu' href='images/src/{$image_full}' target='_blank'><img class='desu' src='/images/thumb/{$image_name}' /></a>";
		}
}

//passcode upload stuff
if (isset($_GET['passcodeimages'])){
	bdl_debug('START ok we get passcode images request');
		if (isset($_COOKIE['passcode']) && !empty($_COOKIE['passcode'])){
			$passcode = substr($_COOKIE['passcode'],0,6);
			//$images = $tc_db->GetAll('select name, original, md5 from passcode_files where passcode = ' .$tc_db->qstr($passcode));
			$images = $tc_db->GetAll('select distinct concat(pf.name, ".", ft.filetype), pf.original, pf.md5_light from passcode_files pf inner join files f on pf.name = f.name inner join filetypes ft on f.type = ft.id where pf.passcode = ' .$tc_db->qstr($passcode));
			if (!empty($images)) {
				bdl_debug('passcode have some images, lets output them');
				$imagecount = count($images);

				for ($i = 0; $i < $imagecount; $i++){
					$image_thumb = explode(".", $images[$i]['concat(pf.name, ".", ft.filetype)'])[0] . "s." .explode(".", $images[$i]['concat(pf.name, ".", ft.filetype)'])[1];
					$image_full = $images[$i]['concat(pf.name, ".", ft.filetype)'];
					$image_original = $images[$i]['original'];
					$image_md5 = $images[$i]['md5_light'];
					echo '<img class="passcode_image" src="/images/thumb/' .$image_thumb. '" title="' .$image_original. '" id="' .$image_md5. '"/>';
				}
				bdl_debug('image output done');
				bdl_debug('END passcode image request');
			} else {
				echo 'К вашему пасскоду еще не прикреплено ни одной картинки';
			}
			//bind links which are we just give
			echo '<script>insertmd5();</script>';	
		} else {
			echo 'Вам нужно завести пасскод';
		}
}

if (isset($_GET['ponytube'])){
	header("Location: http://cytu.be/r/ponyach");
	//because https domains need https links.
}

if (isset($_GET['test123'])){
	$pc = $tc_db->GetAll('select name from passcode_files');
	for ($i = 0; $i < count($pc); $i++){
		echo $pc[$i]['name'] ."\r\n";
	}
}

?>
