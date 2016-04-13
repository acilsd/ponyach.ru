<?php

class Haanga_Extension_Tag_Ver
{
    /**
     *  ver tag
     *
     */
    private static $root = '/var/www/ponyach';

    public static function generator($cmp, $args, $redirected)
    {
        $count = count($args);
	if ($count != 1) $cmp->Error("Ver needs one argument");
	$file = $args[0]['string'];

        $mod_date = date ("YmdHis", filemtime($file));
	$dot = strrpos($file, '.');
	$str = str_replace(self::$root, '', substr_replace($file, '_'.$mod_date.'.', $dot, 1));
        $code = hcode();
        $cmp->do_print($code, Haanga_AST::str($str));

        return $code;

    }
}
