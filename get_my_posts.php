<?php

$board = $_GET['b'];
$thread = $_GET['t'];

echo json_encode($tc_db->GetRow("select id from posts p inner join boards b on 

