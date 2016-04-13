<div id="settings-styles" style="z-index: 9999999; display:none; max-width: 400px ! important; float: right; top: 40px !important; right:100px; padding: 0px; position: fixed; min-width: 400px ! important;" class="reply">
  <div class="settings-section">
  <ul style="list-style: outside none none; padding: 5px; margin: 5px;">
  <p style="text-align: center; margin: 0px; font-weight: bold;">Обычные</p>
    <li><a onmouseover="previewstyle('Photon');" onmouseout="resetstyle();" onclick="set_new_style('Photon');return false;" href="javascript:void(0)" style="text-decoration:none;">Фотон</a></li>
<li><a onmouseover="previewstyle('Neutron');" onmouseout="resetstyle();" onclick="set_new_style('Neutron');return false;" href="javascript:void(0)" style="text-decoration:none;">Нейтрон</a></li>
<li><a onmouseover="previewstyle('Niceroom');" onmouseout="resetstyle();" onclick="set_new_style('Niceroom');return false;" href="javascript:void(0)" style="text-decoration:none;">Niceroom</a></li>
<li><a id="darktileid" onmouseover="previewstyle('Darktile');" onmouseout="resetstyle();" onclick="set_new_style('Darktile');return false;" href="javascript:void(0)" style="text-decoration:none;">Darktile</a><br>
</li><li><a onmouseover="previewstyle('Cyborg');" onmouseout="resetstyle();" onclick="set_new_style('Cyborg');return false;" href="javascript:void(0)" style="text-decoration:none;">Cyborg</a></li>
  </ul>
  </div>
    <div class="settings-section">
  <ul style="list-style: outside none none; padding: 5px; margin: 5px;">
  <p style="text-align: center; margin: 0px; font-weight: bold;">Пони</p>
    <li><a onmouseover="previewstyle('Applejack');" onmouseout="resetstyle();" onclick="set_new_style('Applejack');return false;" href="javascript:void(0)" style="text-decoration:none;">Эпплджек</a></li>
<li><a onmouseover="previewstyle('Fluttershy');" onmouseout="resetstyle();" onclick="set_new_style('Fluttershy');return false;" href="javascript:void(0)" style="text-decoration:none;">Флаттершай</a></li>
<li><a onmouseover="previewstyle('Pinkie');" onmouseout="resetstyle();" onclick="set_new_style('Pinkie');return false;" href="javascript:void(0)" style="text-decoration:none;">Пинки Пай</a></li>
<li><a onmouseover="previewstyle('Rainbow');" onmouseout="resetstyle();" onclick="set_new_style('Rainbow');return false;" href="javascript:void(0)" style="text-decoration:none;">Рейнбоу Дэш</a></li>
<li><a onmouseover="previewstyle('Rarity');" onmouseout="resetstyle();" onclick="set_new_style('Rarity');return false;" href="javascript:void(0)" style="text-decoration:none;">Рэрити</a></li>
<li><a onmouseover="previewstyle('Twilight');" onmouseout="resetstyle();" onclick="set_new_style('Twilight');return false;" href="javascript:void(0)" style="text-decoration:none;">Твайлайт Спаркл</a></li>
<li><a onmouseover="previewstyle('Lunalight');" onmouseout="resetstyle();" onclick="set_new_style('Lunalight');return false;" href="javascript:void(0)" style="text-decoration:none;">Луна</a></li>
<li><a onmouseover="previewstyle('Nighthard');" onmouseout="resetstyle();" onclick="set_new_style('Nighthard');return false;" href="javascript:void(0)" style="text-decoration:none;">Найтмэр</a></li>
<li><a onmouseover="previewstyle('Octavia');" onmouseout="resetstyle();" onclick="set_new_style('Octavia');return false;" href="javascript:void(0)" style="text-decoration:none;">Октавия</a></li>
<li><a onmouseover="previewstyle('Derpy');" onmouseout="resetstyle();" onclick="set_new_style('Derpy');return false;" href="javascript:void(0)" style="text-decoration:none;">Дерпи</a></li>
<li><a onmouseover="previewstyle('Celestialight');" onmouseout="resetstyle();" onclick="set_new_style('Celestialight');return false;" href="javascript:void(0)" style="text-decoration:none;">Селестия</a></li>
<li><a onmouseover="previewstyle('Chrysalis');" onmouseout="resetstyle();" onclick="set_new_style('Chrysalis');return false;" href="javascript:void(0)" style="text-decoration:none;">Кризалис</a></li>
  </ul>
  </div>
</div> 




<div id="settings-main" style="z-index: 9999999; display:none; max-width: 400px ! important; float: right; top: 40px !important; right:100px; !important; padding: 0px; position: fixed; min-width: 400px ! important;" class="reply">

  <div class="settings-section">
  <div style="padding: 5px; margin: 5px;">  
  <p style="text-align: center; margin: 0px; font-weight: bold;">Спойлеры и рейтинги</p><br>
	Рейтинг по умолчанию:
	<select class="settings_rating_select" id="settings-upload-rating" name="settings-upload-rating" accesskey="r" onchange="on_save_rating();">
	<option value=""></option>
	{% for rating_id,rating_name in board_ratings %}
		<option value="{{ rating_id }}">{{ rating_name }}</option>
	{% endfor %}
	</select><br>
	
	<script type="text/javascript">
	for (var i = 0; i < ratings.length; i++) {
		if (ratings[i]){
			document.write(create_option_checkbox('spoiler_setting', i, i, 'show_spoiler_' + i, "true", 'Показывать ' + ratings[i]));
		}
	}
	</script>
	
	<br>
	
	<script>
	document.write(create_option_checkbox('show_r34', 'hide_r34', 'show_r34', 'r34', '1', 'Отображать /r34/'));
	document.write(create_option_checkbox('show_rf', 'hide_rf', 'show_rf', 'rf', '1', 'Отображать /rf/'));
	document.write(create_option_checkbox('rpbutton', 'rp', 'rp', 'rp', 'hided', 'Скрывать тег rp'));
	</script> 
  </div>
  </div>
  
  <div class="settings-section">
  <div style="padding: 5px; margin: 5px;">
  <p style="text-align: center; margin: 0px; font-weight: bold;">Модификации</p>
  	<script>
	document.write(create_option_checkbox('mepr_set', 'mepr', 'mepr', 'mepr_set', 'enabled', 'Предосмотр постов'));
	document.write(create_option_checkbox('coma_set', 'coma', 'coma', 'coma_set', 'enabled', 'Цветные отметки'));
	document.write(create_option_checkbox('typo_set', 'typo', 'typo', 'typo_set', 'enabled', '«Оттипографичивание» текста'));
	document.write(create_option_checkbox('doubledash_c', 'doubledash', 'doubledash', 'doubledash', '1', 'Олдскул стрелки слева у постов'));
	</script>
  </div>
  </div>
  
  <div class="settings-section">
  <div style="padding: 5px; margin: 5px;">
  <p style="text-align: center; margin: 0px; font-weight: bold;">Остальное</p>
	<script>
	document.write(create_option_checkbox('kl_off', 'kl_off', 'kl_off', 'kl_off', '0', 'Использовать встроенный Dollchan Script'));
	</script>
	<script>
	if (getCookie('dollchantrue') == 'true') {
		document.write(addGalaSettings());
	}
	</script>
	<script>
	document.write(create_option_checkbox('ctrlenoff', 'ctrlenoff', 'ctrlenoff', 'ctrlenoff', '1', 'Отключить отправку по ctrl+enter'));
	document.write(create_option_checkbox('zanuda', 'zanuda', 'zanuda', 'zanuda', '1', 'Не показывать 2-5 картинки в постах'));
	document.write(create_option_checkbox('dbrep', 'dbrep', 'dbrep', 'dbrep', '1', 'Заменить иконку ДБ аплоадера'));
	document.write(create_option_checkbox('upbutton', 'upbutton', 'upbutton', 'upbutton', '1', 'Включить кнопку получения новых постов'));
	document.write(create_option_checkbox('hidebanners', 'hidebanners', 'hidebanners', 'hidebanners', '1', 'Не показывать баннеры'));
	document.write(create_option_checkbox('enablefixedheader', 'enablefixedheader', 'enablefixedheader', 'enablefixedheader', '1', 'Зафиксировать хедер'));
	document.write(create_option_checkbox('snow_set', 'Включить', 'Вкл', 'snow_set', 'enabled', 'Включить снег'));
	document.write(create_option_checkbox('snow_tab', 'Всегда', 'Всегда', 'snow_tab', 'enabled', 'Отображать снег всегда'));
	</script>
	
	<br>
	
	<p>Скрывать уведомления о новых постах/тредах</p>
	<script>
	var nots = ['b', 'd', 'tea', 'test', 'vg', 'oc', 'r34', 'rf'];
	for (var i = 0; i < nots.length; i++) {
		var boards_nots = nots[i];
			document.write(create_option_checkbox('nots', boards_nots, boards_nots, 'hide_nots_' + boards_nots, 1, '/' + boards_nots + '/'));
	}
	</script>
	<br>
	<span>Скрытие по имени</span>
	<input id="hidename" type="text" placeholder="Введите (имена) в скобках (через), (запятую)" maxlength="9999" size="50">
	<br><br>
	<span id="tellhide"></span>
  </div>
  </div>
</div> 