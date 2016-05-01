{% spacefull %}
{% include "resources.tpl" %}
</head>
<body>


<div id="postmenu" class="de-menu reply de-imgmenu" style="position: absolute; left: 10px; display:none;">
<a onmouseout="hidepostmenu_timer()" onmouseover="hidepostmenu_timer_cancel()" class="de-menu-item de-imgmenu" href="javascript:void(0);" onclick="show_irc_reflink(temppost);" >Диалоги</a>
<a onmouseout="hidepostmenu_timer()" onmouseover="hidepostmenu_timer_cancel()" class="de-menu-item de-imgmenu" href="javascript:void(0);" onclick="edit_post();" >Редактировать</a>
</div>

<div class="fixed-header-placeholder">
	<span id="passcode-button" style="text-decoration:none; float:right; font-family: 'Trebuchet MS',Trebuchet,serif,'Marck Script',cursive; margin-right: 5px;">[<a target="_blank" style="text-decoration:none;"  href="/passcode.php">Пасскод</a>]</span>
	<span id="settings-settings-button" style="text-decoration:none; float:right; font-family: 'Trebuchet MS',Trebuchet,serif,'Marck Script',cursive; margin-right: 5px;">[<a style="text-decoration:none;"  href="javascript:void(0)" onclick="$('#settings-main').toggle('slow')">Настройки</a>]</span>
	<span id="settings-styles-button" style="text-decoration:none; float:right; font-family: 'Trebuchet MS',Trebuchet,serif,'Marck Script',cursive; margin-right: 5px;">[<a  style="text-decoration:none;" href="javascript:void(0)" onclick="$('#settings-styles').toggle('slow')" >Стили</a>]</span>

	<form method="post" action="/search.php">
	<input type="hidden" name="board" value="{{ board.name }}" />
	<input type="text" placeholder="Поиск" name="search" class="search" maxlength="40">
	</form>

	{% include "settings.tpl" %}

	<div id="reply-irc" class="reply-irc reply"></div>

	<div class="navbar">{% endspacefull %}
		{% for sect in boardlist %}
	{%spacefull %}
	{% endspacefull %}
			[
		{% for brd in sect %}
			<a title="{{ brd.desc }}" id="board_link_top_{{ brd.name }}" href="/{{ brd.name }}/">{{ brd.name }}</a>{% if  forloop.last %}{% else %} / {% endif %}
	{%spacefull %}
	{% endspacefull %}
		{% endfor %}
	{%spacefull %}
	{% endspacefull %}
			 ]
		{% endfor %}
	{%spacefull %}
	[
	<a title="Информация" id="board_link_top_info" href="{{ ku_boardspath }}/html/information.html">Информация</a>
	/
	<a title="Почта" id="board_link_top_mail" href="{{ ku_boardspath }}/mail">Почта</a>
	/
	<a title="Чейнжлог" id="board_link_top_changelog" href="{{ ku_boardspath }}/html/changelog.html">Чейнжлог</a>
	] 
	</div>
	<hr id="hrtoggle" style="display:none; border: 0; height: 1px; background: #333; background-image: linear-gradient(to right, #ccc, #333, #ccc);">
</div>

<select class="styles_mobile" onchange="javascript:if(selectedIndex != 0)set_stylesheet(options[selectedIndex].value);return false;">
<option>Стили</option>
<option value="Photon">Photon</option>
<option value="Neutron">neutron</option>
<option value="Cyborg">cyborg</option>
<option value="Darktile">darktile</option>
<option value="Niceroom">niceroom</option>
<option value="Celestialight">celestialight</option>
<option value="Applejack">applejack</option>
<option value="Chrysalis">chrysalis</option>
<option value="Derpy">derpy</option>
<option value="Fluttershy">fluttershy</option>
<option value="Lunalight">lunalight</option>
<option value="Nighthard">nighthard</option>
<option value="Octavia">octavia</option>
<option value="Pinkie">pinkie</option>
<option value="Rainbow">rainbow</option>
<option value="Rarity">rarity</option>
<option value="Twilight">twilight</option>
</select>

<select class="boards_mobile" onchange="location = this.options[this.selectedIndex].value;">
<option value="" disabled selected>Доски</option>
<option value="/b/">/b/</option>
<option value="/d/">/d/</option>
<option value="/tea/">/tea/</option>
<option value="/test/">/test/</option>
<option value="/vg/">/vg/</option>
<option value="/oc/">/oc/</option>
<option value="/r34/">/r34/</option>
<option value="/rf/">/rf/</option>
<option disabled>────</option>
<option value="informations.html">Инфо</option>
<option value="/mail">Почта</option>
</select>

<div class="logo"></div>

{% include "modals.tpl" %}

{{ board.includeheader|safe }}

<br>
<center><span id="randbanner" class="hidemobile ca_thumb"></span></center>
<script>
// function getRandomInt(min, max) {
//   return Math.floor(Math.random() * (max - min + 1)) + min;
// }

// bannersArray = [
// 	'<a href="/vg/"><img src="/images/banners/vg_2.gif"></a>',
// 	'<a href="/d/"><img src="/images/banners/d_1.png"></a>',
// 	'<a href="/d/"><img src="/images/banners/d_2.png"></a>',
// 	'<a href="/r34/"><img src="/images/banners/r34_1.png"></a>',
// 	'<a href="/rf/"><img src="/images/banners/rf_1.png"></a>',
// 	'<a href="/vg/"><img src="/images/banners/vg_1.png"></a>',
// 	'<a href="/oc/"><img src="/images/banners/oc_1.png"></a>',
// 	'<a href="/vg/"><img src="/images/banners/vg_3.png"></a>'
// ];

// bannersMax = bannersArray.length -1;
// if ( getCookie("hidebanners")  != '1' ) {
// 	document.getElementById('randbanner').innerHTML = bannersArray[getRandomInt(0, bannersMax)];
// }
</script>
{% endspacefull %}
