<?php
require_once('inc/func/ip.php');
echo "realip : " . get_real_ip() . "<br><br>";
foreach ($_SERVER as $key => $val)
    echo $key . ' => ' . $val . "<br>";
?>
