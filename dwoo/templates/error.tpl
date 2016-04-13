<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>{{ cf.KU_NAME }}</title>
<link rel="shortcut icon" href="{{ cf.KU_WEBPATH }}/favicon.ico" />
{% for s in styles %}
	<link rel="{% if  s != cf.KU_DEFAULTSTYLE %}alternate {% endif %}stylesheet" type="text/css" href="{{ cf.KU_WEBPATH }}/css/{{ s }}.css" />
{% endfor %}

<style type="text/css">
body {
	width: 100% !important;
}
</style>
</head>
<body>
<br />
<h2 style="font-size: 2em;font-weight: bold;text-align: center;">
{{ errormsg }}
</h2>
{{ errormsgext|safe }}
<div style="text-align: center;width: 100%;position: absolute;bottom: 10px;">
<br />
</div>
</body>
</html>
