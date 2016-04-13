<?php

function debug ($mes) {
	global $cf, $user_ip;
        if ($cf['debug_enabled']) {
                $callers=debug_backtrace();
                $mes = '<' . $callers[1]['function'] . '> ' . $mes;
                file_put_contents ($cf['debuglog'], '[' . microtime() . '] [' . $user_ip . '|' . getmypid() .'] ' . $mes . "\n", FILE_APPEND);
        }
}
?>
