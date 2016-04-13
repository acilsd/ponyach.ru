<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>{% trans _("Я запретила тебе отправлять сообщения!") %}</title>
<link rel="stylesheet" type="text/css" href="{{ cf.KU_BOARDSPATH }}/css/neutron.css" title="neutron">
<link rel="shortcut icon" href="{{ cf.KU_WEBPATH }}/favicon.ico">
</head>
<body>
<div style="margin-left: 5px; margin-top: 25px;">
        {% for ban in bans %}
		{% if  not forloop.first %}
			Также
		{% endif %}
		{% if  ban.expired == 1 %}
			Вы были забанены
		{% else %}
			<center><strong>Вы забанены</strong></center><br />
			В разделах:
		{% endif %} 
		<strong>{% if  ban.globalban == 1 %}во всех разделах{% else %} {{ ban.boards }} {% endif %}</strong><br />
		Причина":
		<strong>{{ ban.reason }}</strong><br />
		Вы были забанены <strong>{{ ban.at|date:"M d, Y H:i" }}</strong> <br />
		Срок бана истекает:
		{% if  ban.until != 0 %}	
			<strong> {{ ban.until|date:"M d, Y H:i" }} </strong> <br  />
			{# <strong>Срок бана истёк. Это сообщение больше не будет появляться.</strong> #}
		{% else %}
			{% if  ban.until > 0 %}<strong>Перманентно</strong></strong>{% endif %}
		{% endif %}
		<br />
	<b>#Автобан</b> значит что ты был забанен системой автоматически. Если предпосылок для твоего бана не было, то вероятнее всего бан произошел по ошибке.<br />
	Такие происшествия никогда не остаются без внимания, но чтобы ускорить работу модераторов, ты можешь написать на <a href="mailto:support@ponyach.ru">support@ponyach.ru</a><br />
	<br />А пока <a href="http://futzi01.deviantart.com/art/Canterlot-Siege-4-560420853" style="text-decoration:none;">поиграй</a><br />
	<i><span class="postertrip">!поняба</span></i>
		{% if not forloop.last %}
			<hr />
		{% endif %}
	{% endfor %}

<style>
#de-alert > div {
white-space: normal!important;
}
</style>
</div>
</body>
</html>
