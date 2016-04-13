<?php

function connect_db () {
        global $db_host, $db_name, $db_user, $db_pass;

        debug (" : db connect requested");
        $GLOBALS['db'] = new PDO('mysql:host=' . $db_host . ';dbname=' . $db_name . ';charset=utf8', $db_user, $db_pass, 
                array(PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)); // i don't know why but without errmode nothing works
}

?>
