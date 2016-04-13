<?php
session_start();
        if ($_SESSION['auth'] !== 1) {
        header("Location: /404.html");
        exit;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>auth</title>
<link rel="shortcut icon" href="/favicon.ico"/>
<link rel="gettext" type="application/x-po" href="http://ponyach.ru/inc/lang/ru/LC_MESSAGES/kusaba.po"/>
<meta http-equiv="Pragma" content="no-cache"/>
<meta http-equiv="expires" content="Sat, 17 Mar 1990 00:00:01 GMT"/>
<meta http-equiv="Content-Type" content="text/html;charset=UTF-8"/>
<meta name="keywords" content="My little pony, r34, pony, пони, р34, правило34">
<meta name="description" content="Имиджборда"/>
<script type="text/javascript" src="http://ponyach.ru/lib/javascript/modules/moco.user.js"></script>
<script type="text/javascript" src="http://ponyach.ru/lib/javascript/gettext.js"></script>
<link rel="stylesheet" type="text/css" href="http://ponyach.ga/css/img_global.css"/>
<link rel="stylesheet" type="text/css" href="http://ponyach.ga/css/sc-css/sc-player.css"/>
<link rel="stylesheet" type="text/css" href="http://ponyach.ga/css/photon.css" title="Photon"/>
<link rel="alternate stylesheet" type="text/css" href="http://ponyach.ga/css/neutron.css" title="Neutron"/>
<link rel="alternate stylesheet" type="text/css" href="http://ponyach.ga/css/applejack.css" title="Applejack"/>
<link rel="alternate stylesheet" type="text/css" href="http://ponyach.ga/css/fluttershy.css" title="Fluttershy"/>
<link rel="alternate stylesheet" type="text/css" href="http://ponyach.ga/css/pinkie.css" title="Pinkie"/>
<link rel="alternate stylesheet" type="text/css" href="http://ponyach.ga/css/rainbow.css" title="Rainbow"/>
<link rel="alternate stylesheet" type="text/css" href="http://ponyach.ga/css/rarity.css" title="Rarity"/>
<link rel="alternate stylesheet" type="text/css" href="http://ponyach.ga/css/twilight.css" title="Twilight"/>
<link rel="alternate stylesheet" type="text/css" href="http://ponyach.ga/css/lunalight.css" title="Lunalight"/>
<link rel="alternate stylesheet" type="text/css" href="http://ponyach.ga/css/chrysalis.css" title="Chrysalis"/>
<link rel="alternate stylesheet" type="text/css" href="http://ponyach.ga/css/nighthard.css" title="Nighthard"/>
<link rel="alternate stylesheet" type="text/css" href="http://ponyach.ga/css/Celestialight.css" title="Celestialight"/>
<link rel="alternate stylesheet" type="text/css" href="http://ponyach.ga/css/Octavia.css" title="Octavia"/>
<link rel="alternate stylesheet" type="text/css" href="http://ponyach.ga/css/matrix.css" title="Matrix"/>
<script type="text/javascript"><!--
                var ku_boardspath = '';
                var ku_cgipath = '';
                var style_cookie = "kustyle";
                var ratings = new Array();
                var this_board_dir = 'oc';
                var ispage = false;

ratings[10] = 'Спойлер';
ratings[9] = '[S]';


//--></script>
<script type="text/javascript" src="http://ponyach.ga/lib/javascript/kusaba.js"></script>
<script type="text/javascript" src="http://ponyach.ga/lib/javascript/md5-min.js"></script>
<script type="text/javascript" src="http://ponyach.ga/lib/javascript/form_hook.js"></script>
<script type="text/javascript" src="http://ponyach.ga/lib/javascript/thumbs.js"></script>
<script type="text/javascript" src="http://ponyach.ga/lib/javascript/settings.js"></script>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.js"></script>
<script type="text/javascript" src="http://ponyach.ga/lib/javascript/boardscript.js"></script>
<script type="text/javascript" src="http://ponyach.ga/lib/javascript/ponies.js"></script>
<script type="text/javascript" src="http://ponyach.ga/lib/javascript/soundcloud.player.api.js"></script>
<script type="text/javascript" src="http://ponyach.ga/lib/javascript/sc-player.js"></script>
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

<body>
<form action="/chk_auth.php" method="post">

    <div class="label">

        Ключ:

    </div>
    <input type="text" tabindex="1" value="" name="key"></input>
    <input type="submit" tabindex="5" value="Войти" name="In"></input>

</form>
</body>
</html>
