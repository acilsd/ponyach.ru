	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>Поня.ч Менейдж</title>

<link rel="shortcut icon" href="{{ cf.KU_WEBPATH }}/favicon.ico" />

<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.js"></script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js"></script>
<script type="text/javascript" src="{% ver "/var/www/ponyach/lib/javascript/jquery_plugins/jquery.tablesorter.min.js"%}"></script>
<script type="text/javascript" src="{% ver "/var/www/ponyach/css/bootstrap/custom/dropdowns-enhancements.js"%}"></script>
<link rel="stylesheet" type="text/css" href="{{ cf.KU_WEBPATH }}/css/bootstrap/css/bootstrap_dark.css" title="Bootstrap">
<link rel="stylesheet" type="text/css" href="{{ cf.KU_WEBPATH }}/css/bootstrap/custom/dropdowns-enhancement.css" title="Bootstrap">


</head>

<body style="min-width: 600px; padding: 1em 20px 3em 20px;">
{{ includeheader|safe }}
<div id="main">
	<div id="contents">
		{{ page|safe }}
	</div>
</div>	
{{ footer|safe }}
</body>
</html>
