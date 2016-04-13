{% spacefull %} <!DOCTYPE html>
<html{{ htmloptions }}>
<head>
<title>{{ title }}</title>
<link rel="shortcut icon" href="/favicon.ico" />
<meta http-equiv="Content-Type" content="text/html;charset={{ cf.KU_CHARSET }}" />
<meta name="keywords" content="My little pony, r34, pony, пони, р34, правило34"> 
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<meta name="description" content="Имиджборда" />

<?php if( $p['hide_rp']){ ?>
<style type="text/css">
.roleplay {
	display: none !important;
}
</style>
<?php } ?>

<?php if(! $p['is_mod']){ ?>
<style type="text/css">
.dnb {
	display: none !important;
}
</style>
<?php } ?>

<?php if($p['coma']){ ?>
<style type="text/css">
.coma-colormark  {
	display: inline-block !important;
}
</style>
<?php } ?>

<?php if($p['dd']){ ?>
<style type="text/css">
.doubledash {
	display: inline !important;
        vector-effect: top;
}
</style>
<?php } ?>


{% endspacefull %}
