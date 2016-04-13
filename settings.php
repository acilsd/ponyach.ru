<?php
require 'config.php';
require KU_ROOTDIR . 'inc/functions.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>Настройки</title>
<link rel="shortcut icon" href="/favicon.ico"/>
<link rel="gettext" type="application/x-po" href="/inc/lang/ru/LC_MESSAGES/kusaba.po"/>
<meta http-equiv="Pragma" content="no-cache"/>
<meta http-equiv="expires" content="Sat, 17 Mar 1990 00:00:01 GMT"/>
<meta http-equiv="Content-Type" content="text/html;charset=UTF-8"/>
<meta name="keywords" content="My little pony, r34, pony, пони, р34, правило34">
<meta name="description" content="Имиджборда"/>
<script type="text/javascript" src="/lib/javascript/modules/moco.user.js"></script>
<script type="text/javascript" src="/lib/javascript/gettext.js"></script>
<link rel="stylesheet" type="text/css" href="/css/img_global.css"/>
<link rel="stylesheet" type="text/css" href="/css/sc-css/sc-player.css"/>
<link rel="stylesheet" type="text/css" href="/css/photon.css" title="Photon"/>
<link rel="alternate stylesheet" type="text/css" href="/css/neutron.css" title="Neutron"/>
<link rel="alternate stylesheet" type="text/css" href="/css/applejack.css" title="Applejack"/>
<link rel="alternate stylesheet" type="text/css" href="/css/fluttershy.css" title="Fluttershy"/>
<link rel="alternate stylesheet" type="text/css" href="/css/pinkie.css" title="Pinkie"/>
<link rel="alternate stylesheet" type="text/css" href="/css/rainbow.css" title="Rainbow"/>
<link rel="alternate stylesheet" type="text/css" href="/css/rarity.css" title="Rarity"/>
<link rel="alternate stylesheet" type="text/css" href="/css/twilight.css" title="Twilight"/>
<link rel="alternate stylesheet" type="text/css" href="/css/lunalight.css" title="Lunalight"/>
<link rel="alternate stylesheet" type="text/css" href="/css/chrysalis.css" title="Chrysalis"/>
<link rel="alternate stylesheet" type="text/css" href="/css/nighthard.css" title="Nighthard"/>
<link rel="alternate stylesheet" type="text/css" href="/css/celestialight.css" title="Celestialight"/>
<link rel="alternate stylesheet" type="text/css" href="/css/octavia.css" title="Octavia"/>
<link rel="alternate stylesheet" type="text/css" href="/css/matrix.css" title="Matrix"/>
<link rel="alternate stylesheet" type="text/css" href="/css/niceroom.css" title="Niceroom"/>
<link rel="alternate stylesheet" type="text/css" href="/css/derpy.css" title="Derpy"/>
<link rel="alternate stylesheet" type="text/css" href="/css/darktile.css" title="Darktile"/>
<script type="text/javascript"><!--
                var ku_boardspath = '';
                var ku_cgipath = '';
                var style_cookie = "kustyle";
                var ratings = new Array();
                var this_board_dir = 'b';
                var ispage = true;
var same_file = false;

ratings[10] = 'Спойлер';
ratings[9] = '[S]';


//--></script>
<script type="text/javascript" src="/lib/javascript/kusaba.js"></script>
<script type="text/javascript" src="/lib/javascript/md5-min.js"></script>
<script type="text/javascript" src="/lib/javascript/form_hook.js"></script>
<script type="text/javascript" src="/lib/javascript/thumbs.js"></script>
<script type="text/javascript" src="/lib/javascript/settings.js"></script>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.js"></script>
<script type="text/javascript" src="/lib/javascript/boardscript.js"></script>
<script type="text/javascript" src="/lib/javascript/ponies.js"></script>
<script type="text/javascript" src="/lib/javascript/soundcloud.player.api.js"></script>
<script type="text/javascript" src="/lib/javascript/sc-player.js"></script>
<script type="text/javascript"><!--
var hiddenthreads = getCookie('hiddenthreads').split('!');
//--></script>
<script type="text/javascript">
var upd_flag = false;
var upd_int;
var ku_boardspath = '';
</script>
<script type="text/javascript"> var RecaptchaOptions = { theme : 'clean' }; </script>
</head>


<div style="text-align:center"><b>Настройки</b><br/><br/>
Использовать Dollchan Script (рекомендуется):
<?php
echo '<input type="checkbox" name="kl_off" value="1" ';
if(($_COOKIE['kl_off']) != 1){
	echo 'checked';
}
echo '/>';
?>
<br/><br/>
<?php

$results = $tc_db->GetAll("SELECT name, id FROM `" . KU_DBPREFIX . "ratings` ORDER BY `name` ASC");
?>
Показывать рейтинги: <br>
<?php
if (count($results) > 0) {
	foreach ($results as $line) {
		echo 'Отображать ' . $line['name'] . ':';
		echo ' <input type="checkbox" class="spoiler_setting" name="' . $line['name'] . '" value="' . $line['id']. '" ';
		if(($_COOKIE['show_spoiler_' . $line['id']]) == 'true'){
		       	echo 'checked';
		}
		echo '/> <br> ';
	}
}
?>

<br><input type="button" value="Применить" onclick="apply_settings();"><br>
<div id="status" style="display:none"></div>

</div>
<br>
<br>
<br>
<br>
<br>


