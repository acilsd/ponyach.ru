{% spacefull %}
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
<link rel="alternate stylesheet" type="text/css" href="{% ver "/var/www/ponyach/css/main_mlp/lunalight.css" %}" title="Lunalight" />
<link rel="alternate stylesheet" type="text/css" href="/css/main_mlp/nighthard.css" title="Nighthard" />
<link rel="alternate stylesheet" type="text/css" href="/css/main_mlp/octavia.css" title="Octavia" />
<link rel="alternate stylesheet" type="text/css" href="/css/main_mlp/pinkie.css" title="Pinkie" />
<link rel="alternate stylesheet" type="text/css" href="/css/main_mlp/rainbow.css" title="Rainbow" />
<link rel="alternate stylesheet" type="text/css" href="/css/main_mlp/rarity.css" title="Rarity" />
<link rel="alternate stylesheet" type="text/css" href="/css/main_mlp/twilight.css" title="Twilight" />

<script type="text/javascript" src="/lib/javascript/gettext.js"></script>
{% if !static_page %}
	<script type="text/javascript">
	var ku_boardspath = '{{ cf.KU_BOARDSPATH }}';
	var ku_cgipath = '{{ cf.KU_CGIPATH }}';
	var style_cookie = "kustyle";
	var ratings = new Array();
	var this_board_dir = '{{ board.name }}';
	{% if  replythread > 0 %} var ispage = false; {% else %}
	var ispage = true;
	{% endif %}
	var maximages = {{board.maximages}};
	{% if  board_ratings != '' %}{% for rating_id,rating_name in board_ratings %}ratings[{{ rating_id }}] = '{{ rating_name }}'; 
	{% endfor %}
	{% endif %}

	if (localStorage.getItem('DESU_Config')){
		if (localStorage.getItem('DESU_Config').length > 20){
			setCookie("dollchantrue", "true");
		}
	}
	</script>
{% else %}
	<script type="text/javascript">
	var ku_boardspath = '';
	var ku_cgipath = '';
	var style_cookie = "kustyle";
	var ratings = new Array();
	var this_board_dir = 'b';
	var ispage = true;
	var maximages = 5;
	ratings[9] = '[S]'; 
	ratings[10] = '[C]'; 
	ratings[11] = '[A]'; 
	if (localStorage.getItem('DESU_Config')) {
		if (localStorage.getItem('DESU_Config').length > 20){
			setCookie("dollchantrue", "true");
		}
	}
	</script>
{% endif %}

<script type="text/javascript" src="{% ver "/var/www/ponyach/lib/javascript/styles.js"%}"></script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.js"></script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js"></script>
<script type="text/javascript" src="{% ver "/var/www/ponyach/lib/javascript/soundcloud.player.api.js"%}"></script>
<script type="text/javascript" src="{% ver "/var/www/ponyach/lib/javascript/sc-player.js"%}"></script>
<script type="text/javascript" src="{% ver "/var/www/ponyach/lib/javascript/sha512.js"%}"></script>
<script type="text/javascript" src="{% ver "/var/www/ponyach/lib/javascript/md5-min.js"%}"></script>

<?php if($p['dollchantrue']){ ?>
<script type="text/javascript" src="{% ver "/var/www/ponyach/lib/javascript/gala.js"%}"></script>
<?php } ?>

<script type="text/javascript" src="{% ver "/var/www/ponyach/lib/javascript/lazysizes.min.js"%}" async=""></script>
<script type="text/javascript" src="{% ver "/var/www/ponyach/lib/javascript/form_hook.js"%}"></script>
<script type="text/javascript" src="{% ver "/var/www/ponyach/lib/javascript/kusaba.js"%}"></script>
<script type="text/javascript" src="{% ver "/var/www/ponyach/lib/javascript/settings.js"%}"></script>
<script type="text/javascript" src="{% ver "/var/www/ponyach/lib/javascript/ponies.js"%}"></script>
<script type="text/javascript" src="{% ver "/var/www/ponyach/lib/javascript/mobilecheck.js"%}"></script>
<script type="text/javascript" src="{% ver "/var/www/ponyach/lib/javascript/snowstorm.js"%}"></script>
<script type="text/javascript" src="{% ver "/var/www/ponyach/lib/javascript/thumbs.js"%}"></script>
<script type="text/javascript" src="{% ver "/var/www/ponyach/lib/javascript/counter.js"%}"></script>
<script type="text/javascript" src="{% ver "/var/www/ponyach/lib/javascript/captchawindow.js"%}"></script>
<script type="text/javascript" src="{% ver "/var/www/ponyach/lib/javascript/haiku.js"%}"></script>
<script type="text/javascript" src="{% ver "/var/www/ponyach/lib/javascript/boardscript.js"%}"></script>
<script type="text/javascript" src="{% ver "/var/www/ponyach/lib/javascript/dollchanstuff.js"%}"></script>
<script type="text/javascript" src="{% ver "/var/www/ponyach/lib/javascript/dbupload.js"%}"></script>
<script type="text/javascript" src="{% ver "/var/www/ponyach/lib/javascript/passcodeupload.js"%}"></script>
<script type="text/javascript" src="{% ver "/var/www/ponyach/lib/javascript/alertify.min.js"%}"></script>
<script type="text/javascript" src="{% ver "/var/www/ponyach/lib/javascript/hide.js"%}"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/highlight.js/8.4/highlight.min.js"></script>

<?php if($p['is_mod']){ ?>
<script type="text/javascript" src="{% ver "/var/www/ponyach/lib/javascript/mod.js"%}"></script>
<?php } ?>
{% endspacefull %}
