{% spacefull %}<!DOCTYPE html>
<html>
<head>
<title>{{_gettext(title)}}</title>
<link rel="shortcut icon" href="/favicon.ico" />
<meta http-equiv="Pragma" content="no-cache" />
<meta http-equiv="expires" content="Sat, 17 Mar 1990 00:00:01 GMT" />
<meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
<meta name="keywords" content="My little pony, r34, pony, пони, р34, правило34"> 
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<meta name="description" content="Имиджборда" />

{% include "../resources.tpl" %}

<script>
$(document).ready(function () {
if ( getCookie('rf') == '1' ) {
$(".hide_rf").show();
}
if ( getCookie('r34') == '1' ) {
$(".hide_r34").show();
}
});
</script>

{% if  title == 'changelog' %}
<script>
$(document).ready(function () {
	localStorage.removeItem("last_post_changelog");
	localStorage.removeItem("last_post_Чейнджлог");
	localStorage.removeItem("last_thread_changelog");
	localStorage.removeItem("last_thread_Чейнжлог");
});
</script>
{% endif %}
</head>

<body> 
[
<a href="/b/" title="/b/ was never good">b</a> / 
<a href="/d/" title="/d/ - Предложения и баги">d</a> / 
<a href="/tea/" title="/tea/ - Чайная комната">tea</a> / 
<a href="/test/" title="/test/ &mdash; Полигон">test</a> / 
<a href="/vg/" title="/vg/ - Видеоигры Полигон">vg</a> /
<a href="/oc/" title="/oc/ - ориджинал контент">oc</a>
<span class="hide_r34" style="display:none"> / <a href="/r34">r34</a></span>
<span class="hide_rf" style="display:none"> / <a href="/rf">rf</a></span>] [
<a href="/html/information.html" title="Информация">Информация</a>
/
<a href="/mail/" title="Почта">Почта</a>
/
<a title="Понитуб" href="/getdb.php?ponytube">Понитуб</a>
/
<a title="Чейнжлог" href="/html/changelog.html">Чейнжлог</a>
]  
<br /> 
<br />
{% endspacefull %}