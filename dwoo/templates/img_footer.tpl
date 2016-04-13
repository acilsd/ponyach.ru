</form>
<div id="messages"></div>
<div id="message_text"></div>
<div class="userdelete">
{% if  not isread %}
  {% if  board.enablereporting == 1 %}
    </td>
    </tr>
    <tr>
    <td>
    {% trans _("Жалоба") %}
    <input name="reportpost" value="{% trans _("Отправить") %}" type="submit" />
  {% endif %}
{% endif %}
</div>
{% if  replythread == 0 %}
  <table border="1" class="backnext">
  <tbody>
  <tr>
  <td>
  {% if  thispage == 0 %}{% trans _("Назад") %}
  {% else %}<a href="{{ cf.KU_BOARDSFOLDER }}{{ board.name }}/{% if prevpage != 0 %}{{ prevpage }}.html{% endif %}">{% trans _("Назад") %}</a>
  {% endif %}
  </a>
</td>
<td> 
{% spacefull %}
&#91;{% if  thispage != 0 %}<a href="{{ cf.KU_BOARDSPATH }}/{{ board.name }}/">{% endif %}0{% if thispage != 0 %}</a>{% endif %}&#93;
{% for p in numpages %}
&#91;{% if forloop.counter != thispage %}<a href="{{ cf.KU_BOARDSFOLDER }}{{ board.name }}/{{ forloop.counter }}.html">{% endif %}{{ forloop.counter }}{% if forloop.counter != thispage %}</a>{% endif %}&#93;
{% endfor %}{% endspacefull %}</td>
<td>
{% if  thispage == numpages %}{% trans _("Вперёд") %}{% else %}<a href="{{ KU_BOARDSPATH }}/{{ board.name }}/{{ nextpage }}.html">{% trans _("Вперёд") %}</a>
{% endif %}</td>
</tr>
</tbody>
</table>
{% endif %}
{% spacefull %}
{% endspacefull %}
  <div class="navbar">
  {% for sect in boardlist %}
{%spacefull %}
{% endspacefull %}
[
{% for brd in sect %}
  <a title="{{ brd.desc }}" id="board_link_bot_{{ brd.name }}" href="/{{ brd.name }}/">{{ brd.name }}</a>
{%spacefull %}
{% endspacefull %}
  {% if forloop.last %}
  {% else %} /
  {% endif %}
{% endfor %}
{%spacefull %}
<?php if($p['show_r34']){ ?>/ <a href="/r34" id="board_link_top_r34" >r34</a><?php } ?>
<?php if($p['show_rf']){ ?>/ <a href="/rf" id="board_link_top_rf" >rf</a><?php } ?>
{% endspacefull %}
]
  {% endfor %}
{%spacefull %}
  [
  <a title="Информация" id="board_link_top_info" href="{{ ku_boardspath }}/html/information.html">Информация</a>
  /
  <a title="Почта" id="board_link_top_mail" href="{{ ku_boardspath }}/mail">Почта</a>
  /
  <a title="Понитуб" id="board_link_top_tube" href="/getdb.php?ponytube">Понитуб</a>
  /
  <a title="Чейнжлог" id="board_link_top_changelog" href="{{ ku_boardspath }}/html/changelog.html">Чейнжлог</a>
  ]
  </div>
  
  
  <center><a href="#">Вверх</a></center>
  <center>Скорость /b/: <span id="speed">(загрузка...)</span> постов в час</center>
  <center>На сайте: <span id="online">(загрузка...)</span> пони</center>
  <div class="footer" style="clear: both;">
  <a href="{{ ku_boardspath }}" target="_top">Поняба</a>
  </div>

  <script type="text/javascript">
	update_counter();
	update_haiku();


	//preview posts 
	if (getCookie("mepr_set") == 'enabled') {
		$.getScript("/lib/javascript/modules/mepr.user.js", function() {});
	}

	//color marks mod 
	if (getCookie("coma_set") == 'enabled') {
		$.getScript("/lib/javascript/modules/coma.user.js", function() {});
	}

	//typo mod
	if (getCookie("typo_set") == 'enabled') {
		$.getScript("/lib/javascript/modules/typo.user.js", function() {});
	}

	//show-hide snow
	if (getCookie("snow_tab") == 'enabled') {
		snowStorm.freezeOnBlur = false;
	}

	if (getCookie("snow_set") == 'enabled') {
		snowStorm.start();
	}

	//enable update button
	function upb() {
		if (getCookie("upbutton") == '1') {
			$('.de-thread-buttons').delay(3000).show();
		}
	}
	setTimeout(upb, 1500);

	//db replace
	function dbreplace() {
		if (getCookie("dbrep") == '1') {
			$("#dbpic_st").replaceWith('<img id="dbpic_st" src="https://ponyach.ru/favicon.ico">');
			$("#dbpic_vi").replaceWith('<img id="dbpic_vi" src="https://ponyach.ru/favicon.ico">');
		}
	}
	setTimeout(dbreplace, 1500);
	
	//fixed header
	if (getCookie("enablefixedheader") == '1') {
		$('.fixed-header-placeholder').addClass('fixed-header');
		$('.logo').addClass('logo-margintop10');
		$('#passcode-button').css('margin-right', 15);
		
		$(document).scroll(function() {
		  if ($(document).scrollTop() >= 50) {
			$('.fixed-header').addClass('margin8px');
			$('#hrtoggle').show();
		  } else {
			$('.fixed-header').removeClass('margin8px');
			$('#hrtoggle').hide();
		  }
		});
	}
  </script> 

<script type="text/javascript"><!--
//undefined func
// !function(){
// 	do_fix_imgsrch_btns();
//         setInterval(function(a){

// 		do_fix_imgsrch_btns();

// },5e3)

//  }();

//--></script> 

<?php if($p['is_mod']){ ?>
<script type="text/javascript"><!--
!function(){
        setInterval(function(a){

        view_modlog();

},5e3)

}();


//--></script> 
<span id="cloneimage"></span>
<?php } ?>{% endspacefull %}
