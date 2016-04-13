<?php
require_once ("config.php");
require_once ("inc/func/ip.php");



	class Passcode {
	
		public $ip_md5;
		public $session_md5;
		
		public function __construct($ip_md5, $session_md5) {
			$this->ip_md5 = $ip_md5;
			$this->session_md5 = $session_md5;
		}
		
		public function ip_md5() {
			return $this->ip_md5;
		}
		
		public function session_md5() {
			return $this->session_md5;
		}
		

		public function CheckIp() {
			global $tc_db;
			
			$isIp = $tc_db->GetOne("SELECT ip_md5 FROM passcode WHERE ip_md5 = " . $tc_db->qstr($this->ip_md5));
			return $isIp;
		}
		
		public function CheckSession($isIpz) {
			global $tc_db;
			if(empty($isIpz)){
				$isSes = $tc_db->GetOne("SELECT session_md5 FROM passcode WHERE session_md5 = " . $tc_db->qstr($this->session_md5));
				return $isSes;
			}
		}
		
		public function GetLSKey($passcode) {
			global $tc_db;
			$ls_key = $tc_db->GetOne("SELECT localstorage_key FROM passcode WHERE passcode = " .$tc_db->qstr($passcode));
			if (!empty($ls_key)){
				return $ls_key;
			}
		}
		
		public function GetLSValue($passcode) {
			global $tc_db;
			$ls_value = $tc_db->GetOne("SELECT localstorage_value FROM passcode WHERE passcode = " .$tc_db->qstr($passcode));
			if (!empty($ls_value)){
				return $ls_value;
			}
		}		
		
		public function GeneratePasscode($isIpx, $isSesx) {
			global $tc_db;
			$is_captcha_passed = $tc_db->GetOne("SELECT status FROM captcha_status WHERE session_md5 = " . $tc_db->qstr($this->session_md5) . "AND status = '0' LIMIT 1");
			if ($is_captcha_passed === '0') {
				if (empty($isSesx) && empty($isIpx)) {
					$passcodegen = substr(md5(microtime()),rand(0,26),6);
					setcookie("passcode", $passcodegen, time() + (10 * 365 * 24 * 60 * 60));
					$user_cookie_raw = $_COOKIE;
					unset($user_cookie_raw['PHPSESSID']);
					$user_cookie = serialize($user_cookie_raw);
					$tc_db->Execute("INSERT INTO `".KU_DBPREFIX."passcode` ( `ip_md5` , `session_md5` , `passcode` , `cookies` ) VALUES ( ".$tc_db->qstr($this->ip_md5)." , ".$tc_db->qstr($this->session_md5)." , ".$tc_db->qstr($passcodegen)." , ".$tc_db->qstr($user_cookie) . ")" );
					$is_captcha_true = $tc_db->GetOne("SELECT status FROM captcha_status WHERE session_md5 = " . $tc_db->qstr($this->session_md5) . "AND status = '0' LIMIT 1");
					if ($is_captcha_true === '0') {
						$tc_db->Execute("UPDATE passcode SET captcha = 'passed' "); //this check is broken but it's useless because user anyway pass captcha
					}
					
					if (isset($_POST['localstorage_key'])) {
						$localstorage_key = $_POST['localstorage_key'];
						$tc_db->Execute("UPDATE passcode SET localstorage_key = " . $tc_db->qstr($localstorage_key) . "WHERE ip_md5 = " . $tc_db->qstr($this->ip_md5) . " and session_md5 = " . $tc_db->qstr($this->session_md5));
					}
					if (isset($_POST['localstorage_value'])) {
						$localstorage_value = $_POST['localstorage_value'];
						$tc_db->Execute("UPDATE passcode SET localstorage_value = " . $tc_db->qstr($localstorage_value) . "WHERE ip_md5 = " . $tc_db->qstr($this->ip_md5) . " and session_md5 = " . $tc_db->qstr($this->session_md5));
					}
					//setting cookie here cuz i dont know why it dont setting after
					
					return $passcodegen;
					
				}else {
						$passcode_ses = $tc_db->GetOne("SELECT passcode FROM passcode WHERE session_md5 = " . $tc_db->qstr($this->session_md5));
						$passcode_ip = $tc_db->GetOne("SELECT passcode FROM passcode WHERE ip_md5 = " . $tc_db->qstr($this->ip_md5));
						if (!empty($passcode_ses) || !empty($passcode_ip)){
							if ($passcode_ses){
								$this->UpdatePasscode($passcode_ses);
								return $passcode_ses;
							} else {
								$this->UpdatePasscode($passcode_ip);
								return $passcode_ip;
							}
						}
					}
			}
		}
		
		public function GetPasscode() {
			global $tc_db;
			$showpasscode = $tc_db->GetOne("SELECT passcode FROM passcode WHERE session_md5 = " . $tc_db->qstr($this->session_md5) . " and ip_md5 = " . $tc_db->qstr($this->ip_md5));
			if (!empty($showpasscode)) {
				return $showpasscode;
				}
		}
		
		public function UpdatePasscode($passcode) {
			global $tc_db;
			$is_session_p = $tc_db->GetOne("SELECT session_md5 FROM passcode WHERE passcode = " . $tc_db->qstr($passcode));
			$is_ip_p = $tc_db->GetOne("SELECT ip_md5 FROM passcode WHERE passcode = " . $tc_db->qstr($passcode));
			if ($is_session_p !== $this->session_md5 || $is_ip_p !== $this->ip_md5) {
				$tc_db->Execute("UPDATE passcode SET ip_md5 = " . $tc_db->qstr($this->ip_md5) . ", session_md5 = " . $tc_db->qstr($this->session_md5) . "WHERE passcode = " . $tc_db->qstr($passcode));
			}
			
			$is_passcode_captcha_true = $tc_db->GetOne("SELECT captcha FROM passcode WHERE passcode = " . $tc_db->qstr($passcode) );
			if ($is_passcode_captcha_true == 'passed') {
				$tc_db->Execute("UPDATE captcha_status SET status = 0 WHERE session_md5 = " .$tc_db->qstr($this->session_md5));
			}
			
		}
		
		public function SetDataFromPasscode($passcode) {
			global $tc_db;
			$cookie_get = $tc_db->GetOne("SELECT cookies FROM passcode WHERE passcode = " . $tc_db->qstr($passcode));
			if (!empty($cookie_get)){
				$cookie_array = unserialize($cookie_get);
					foreach ($cookie_array as $name => $value) {
						setcookie($name, $value, time() + (86400 * 30 * 365), "/");
					}
				$is_passcode_captcha_true = $tc_db->GetOne("SELECT captcha FROM passcode WHERE passcode = " . $tc_db->qstr($passcode) );
				if ($is_passcode_captcha_true == 'passed') {
					$tc_db->Execute("UPDATE captcha_status SET status = 0 WHERE session_md5 = " .$tc_db->qstr($this->session_md5));
				}
				$is_session_p = $tc_db->GetOne("SELECT session_md5 FROM passcode WHERE passcode = " . $tc_db->qstr($passcode));
				$is_ip_p = $tc_db->GetOne("SELECT ip_md5 FROM passcode WHERE passcode = " . $tc_db->qstr($passcode));
				if ($is_session_p !== $this->session_md5 || $is_ip_p !== $this->ip_md5) {
					$tc_db->Execute("UPDATE passcode SET ip_md5 = " . $tc_db->qstr($this->ip_md5) . ", session_md5 = " . $tc_db->qstr($this->session_md5) . "WHERE passcode = " . $tc_db->qstr($passcode));
				}
			}
			
			return $cookie_array;
		}
		
		public function SetOnlyCookies($passcode) {
			global $tc_db;
			$cookie_get = $tc_db->GetOne("SELECT cookies FROM passcode WHERE passcode = " . $tc_db->qstr($passcode));
			if (!empty($cookie_get)){
				$cookie_array = unserialize($cookie_get);
					foreach ($cookie_array as $name => $value) {
						setcookie($name, $value, time() + (86400 * 30 * 365), "/");
					}				
			}

		}
		
		public function UpdateDataInPasscode($passcode) {
			global $tc_db;
			$user_cookie_raw_update = $_COOKIE;
			unset($user_cookie_raw_update['PHPSESSID']);
			$user_cookie_update = serialize($user_cookie_raw_update);
			$tc_db->Execute("UPDATE passcode SET cookies = " . $tc_db->qstr($user_cookie_update) . "WHERE ip_md5 = " . $tc_db->qstr($this->ip_md5) . " or session_md5 = " . $tc_db->qstr(session_md5));
			
			if (isset($_POST['localstorage_key'])) {
				$localstorage_key = $_POST['localstorage_key'];
				$tc_db->Execute("UPDATE passcode SET localstorage_key = " . $tc_db->qstr($localstorage_key) . "WHERE ip_md5 = " . $tc_db->qstr($this->ip_md5) . " or session_md5 = " . $tc_db->qstr(session_md5));
			}
			if (isset($_POST['localstorage_value'])) {
				$localstorage_value = $_POST['localstorage_value'];
				$tc_db->Execute("UPDATE passcode SET localstorage_value = " . $tc_db->qstr($localstorage_value) . "WHERE ip_md5 = " . $tc_db->qstr($this->ip_md5) . " or session_md5 = " . $tc_db->qstr(session_md5));
			}
		}
	}
	
	$data = new Passcode(md5($real_ip), md5(session_id()));
	$objip = $data->ip_md5;
	$session = $data->session_md5;
	$checkip = $data->CheckIp();
	$checkses = $data->CheckSession($checkip);
	
	//seting cookie
	if (!empty($data->GetPasscode())){
		setcookie("passcode", $data->GetPasscode(), time() + (10 * 365 * 24 * 60 * 60));
	}
	
	if (isset($_POST['passcode_generate'])) {
	$passcode_generate = $_POST['passcode_generate']; 
		if ($passcode_generate == '') { 
			unset($passcode_generate);
			} else {
				$data->GeneratePasscode($checkip, $checkses);
			}
	}
	
	if (isset($_POST['passcode_update'])) {
	$passcode_update = $_POST['passcode_update']; 
		if ($passcode_update == '') { 
			unset($passcode_update);
			} else {
				$data->UpdateDataInPasscode($passcode_update);
			}
	}
	
	if (isset($_POST['passcode_just_set'])) {
	$passcode_just_set = $_POST['passcode_just_set']; 
		if ($passcode_just_set == '') { 
			unset($passcode_just_set);
			} else {
				$data->UpdatePasscode($passcode_just_set);
			}
	}
	
	if (isset($_POST['passcode_just_get_cookie'])) {
	$passcode_just_get_cookie = $_POST['passcode_just_get_cookie']; 
		if ($passcode_just_get_cookie == '') { 
			unset($passcode_just_get_cookie);
			} else {
				$data->SetOnlyCookies($passcode_just_get_cookie);
			}
	}
?>


<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
 <head>
<title>Пасскод</title>
<link rel="shortcut icon" href="/favicon.ico" />
<meta http-equiv="Pragma" content="no-cache" />
<meta http-equiv="expires" content="Sat, 17 Mar 1990 00:00:01 GMT" />
<meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
<meta name="keywords" content="My little pony, r34, pony, пони, р34, правило34"> 
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<meta name="description" content="Имиджборда" />

<script type="text/javascript" src="/lib/javascript/gettext.js"></script>
<link rel="stylesheet" type="text/css" href="/css/main_stuff/img_global.css" />
<link rel="stylesheet" type="text/css" href="/css/main_stuff/mobile.css" />
<link rel="stylesheet" type="text/css" href="/css/main_stuff/sc-css/sc-player.css" />
<link rel="stylesheet" type="text/css" href="/css/main_stuff/alertify/alertify.core.css" />
<link rel="stylesheet" type="text/css" href="/css/main_stuff/alertify/alertify.default.css" />
<link rel="stylesheet" type="text/css" href="/css/main_stuff/railscasts/railscasts.css" />

<link id="default_stylesheet" rel="stylesheet" type="text/css" href="/css/main_other/darktile.css" title="Darktile" />
<link rel="alternate stylesheet" type="text/css" href="/css/main_other/photon.css" title="Photon" />
<link rel="alternate stylesheet" type="text/css" href="/css/main_other/neutron.css" title="Neutron" />
<link rel="alternate stylesheet" type="text/css" href="/css/main_other/cyborg.css" title="Cyborg" />
<link rel="alternate stylesheet" type="text/css" href="/css/main_other/niceroom.css" title="Niceroom" />

<link rel="alternate stylesheet" type="text/css" href="/css/main_mlp/celestialight.css" title="Celestialight" />
<link rel="alternate stylesheet" type="text/css" href="/css/main_mlp/applejack.css" title="Applejack" />
<link rel="alternate stylesheet" type="text/css" href="/css/main_mlp/chrysalis.css" title="Chrysalis" />
<link rel="alternate stylesheet" type="text/css" href="/css/main_mlp/derpy.css" title="Derpy" />
<link rel="alternate stylesheet" type="text/css" href="/css/main_mlp/fluttershy.css" title="Fluttershy" />
<link rel="alternate stylesheet" type="text/css" href="/css/main_mlp/lunalight.css" title="Lunalight" />
<link rel="alternate stylesheet" type="text/css" href="/css/main_mlp/nighthard.css" title="Nighthard" />
<link rel="alternate stylesheet" type="text/css" href="/css/main_mlp/octavia.css" title="Octavia" />
<link rel="alternate stylesheet" type="text/css" href="/css/main_mlp/pinkie.css" title="Pinkie" />
<link rel="alternate stylesheet" type="text/css" href="/css/main_mlp/rainbow.css" title="Rainbow" />
<link rel="alternate stylesheet" type="text/css" href="/css/main_mlp/rarity.css" title="Rarity" />
<link rel="alternate stylesheet" type="text/css" href="/css/main_mlp/twilight.css" title="Twilight" />

<script type="text/javascript"><!--
var ku_boardspath = '';
var ku_cgipath = '';
var style_cookie = "kustyle";
var ratings = new Array();
var this_board_dir = 'b';
 var ispage = false; 
var maximages = 5;
ratings[9] = '[S]'; 
ratings[10] = '[C]'; 
ratings[11] = '[A]'; 


//--></script>

<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.js"></script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js"></script>
<script type="text/javascript" src="/lib/javascript/styles_20150417215511.js"></script>
<script type="text/javascript" src="/lib/javascript/md5-min.js"></script>
<script type="text/javascript" src="/lib/javascript/kusaba.js"></script>
<script type="text/javascript" src="/lib/javascript/settings.js"></script>
<script type="text/javascript" src="/lib/javascript/snowstorm.js"></script>
<script type="text/javascript" src="/lib/javascript/haiku.js"></script>
<script type="text/javascript" src="/lib/javascript/captchawindow.js"></script>

<script>
var key_ls = [];
var value_ls = [];
for (var a in window.localStorage) {
   key_ls.push(a); 
   var value_loop = localStorage[a] + "||";
   value_ls.push(value_loop);
}

//deleting firefox 34+ shitstorage
// Array Remove - By John Resig (MIT Licensed)
Array.prototype.remove = function(from, to) {
  var rest = this.slice((to || from) + 1 || this.length);
  this.length = from < 0 ? this.length + from : from;
  return this.push.apply(this, rest);
};
//7 cuz only last 7 keys broken, cuz fuck you
for (i=0;i<7; i++) {
key_ls.remove(-1);
value_ls.remove(-1);
}

$(document).ready(function() {
	$('input[name="localstorage_key"]').val(key_ls);
	$('input[name="localstorage_value"]').val(value_ls);
});
</script>

<script>
var localstorage_set_key = <?php if (!empty($data->GetPasscode())) {echo "'" . $data->GetLSKey($data->GetPasscode()) . "'" ;}?>;
var localstorage_set_value = <?php if (!empty($data->GetPasscode())) {echo "'" . str_replace('\\', '\\\\', $data->GetLSValue($data->GetPasscode()))  . "'" ;}?>;
localstorage_set_key_clear = localstorage_set_key.split(',');
localstorage_set_value_clear = localstorage_set_value.split('||');
localstorage_set_value_clear = localstorage_set_value_clear.map(function(item){
    return item.replace(/^,/, '');
});

var lslength = localstorage_set_key_clear.length;
$(document).ready(function() {
	$("input[name='ls_get']").click(function() {
		for (i=0; i < lslength; i++) {
			localStorage.setItem(localstorage_set_key_clear[i], localstorage_set_value_clear[i]);
		}
	});
});

</script>
</head>


<body>
<div id="haikaptcha" class="overlay-content popup1">
</div>
<div class="reply">
    <h2 <?php if (!empty($data->GetPasscode())){ echo 'style="display:none"'; } ?> >Сгенерировать пасскод</h2>
	<h3 <?php if (!empty($data->GetPasscode())){ echo 'style="display:none"'; } ?> ><b>Убедитесь что генерируете пасскод на домене которым пользуетесь как основным</b></h3>
	<h2 <?php if (empty($data->GetPasscode())){ echo 'style="display:none"'; } ?> >Ваш пасскод</h2>
    <form action="passcode.php" method="post">
<p> <?php if (!empty($data->GetPasscode())){ echo "<b>" . $data->GetPasscode() . "</b>"; } ?>
    <input name="passcode_generate" value="passcode_generate" type="hidden" size="6" maxlength="6">
	<input name="localstorage_key" value="" type="hidden">
	<input name="localstorage_value" value="" type="hidden">
    </p>
<p>
	<a <?php if (!empty($data->GetPasscode())){ echo 'style="display:none"'; } ?> data-showpopup="1" id="haiku_btn" class="show-popup" href="javascript:void(0)" style="text-decoration: none;"><input value="Ввести капчу" type="button"></a>
    <input 	<?php if (!empty($data->GetPasscode())){ echo 'style="top:-99999;z-index:-99999; position: absolute;"'; } ?> type="submit" name="submit" value="Сгенерировать" id="go"> 
</p></form>	
</div>

	<br><br><br>
	<div class="reply">
	<h2>Ввести пасскод</h2>
    <form action="passcode.php" method="post" <?php if (!empty($data->GetPasscode())){ echo 'style="display:none"'; } ?> name="without_passcode">
<p>	
	<span>
    <label>Ваш пасскод:</label><br>
    <input name="passcode_just_set" type="text" size="6" maxlength="6"><br><br>
	<input type="submit" name="submit" value="Принять">
	</span>
</p>	
</form>


<form action="passcode.php" method="post" <?php if (empty($data->GetPasscode())){ echo 'style="display:none"'; } ?> name="with_passcode">
<p>
	<span>
    <input name="passcode_just_set" <?php if (!empty($data->GetPasscode())){ echo 'style="display:none"'; } ?> type="text" size="6" maxlength="6" value="<?php if (!empty($data->GetPasscode())){ echo $data->GetPasscode(); } ?>">
    <input name="passcode_just_get_cookie" type="hidden" size="6" maxlength="6" value="<?php if (!empty($data->GetPasscode())){ echo $data->GetPasscode(); } ?>">
	</span>
    </p>
<p>
    <input <?php if (empty($data->GetPasscode())){ echo 'style="display:none"'; } ?> type="submit" name="submit" value="Получить Cookie">
	<input <?php if (empty($data->GetPasscode())){ echo 'style="display:none"'; } ?> type="button" name="ls_get" value="Получить localStorage" title="В нём хранятся настройки Dollchan скрипта и некоторые прочие"> <br><br>	
</p>
</form> 
</div>

	<br><br><br>

	<div class="reply" <?php if (empty($data->GetPasscode())){ echo 'style="display:none"'; } ?> >
    <h2>Обновить пасскод</h2>
	<b>Обновить содержащиеся в пасскоде настройки</b>
    <form action="passcode.php" method="post">
<p>
    <input name="passcode_update" type="hidden" size="6" maxlength="6" value="<?php if (!empty($data->GetPasscode())){ echo $data->GetPasscode(); } ?>">
	<input name="localstorage_key" value="" type="hidden">
	<input name="localstorage_value" value="" type="hidden">
    </p>
<p>
    <input type="submit" name="submit" value="Обновить">
</p></form>
	</div>
	<script>
	 update_haiku();
	</script>
	<div class="reply" <?php if (!empty($data->GetPasscode())){ echo 'style="display:none"'; } ?> >
		<h3 style="text-align:center;">Что даёт пасскод?</h3>
		<p>1. Привязывает Ваши настройки к пасскоду</p>
		<p>2. Возможность постинга картинок из веб интерфейса которые Вы постили с пасскодом</p>
		<p>3. Возможность постинга из под ТОРа, если пасскод был одобрен модератором</p>
	</div>
    </body>
    </html>
