<?php
require_once ("config.php");
//require_once ("inc/func/ip.php");

$mode = $_GET['m'];
if (strlen ($mode) > 4 )
	die ();

if (isset ($_GET['id']) && (! is_numeric ($_GET['id'])))
	die ();

switch ($mode) {
	case 'list':
		$res = $tc_db -> GetAll ("SELECT id, subject FROM `".KU_DBPREFIX."messages` WHERE ip='{$_SERVER['REMOTE_ADDR']}' or ip='all'");
		if ($res && is_array ($res) && (count ($res) > 0)){
			$output = '[ У вас ' . count ($res) . ' новых сообщений.  ]<br>';
			$x = 0;
			foreach ($res as $line) {
				$x++;
				$output .= '<a href="javascript://" onclick="show_message_text(' . $line['id'] . ')">' . $x . ' ' . $line['subject'] . '</a><br>';
			}
		echo $output;
		}
	break;
	case 'view':
		$id = $_GET['id'];
		$res = $tc_db -> GetOne ("SELECT text FROM `".KU_DBPREFIX."messages` WHERE ip='{$_SERVER['REMOTE_ADDR']}' and id='$id'");
		if ($res) 
			echo $res;
	break;
	case 'del';
		$id = $_GET['id'];
		$res = $tc_db -> GetOne ("DELETE from messages where ip='{$_SERVER['REMOTE_ADDR']}' and id=$id");
	break;
}

?>
