<?php

function dice_init() {
    global $hooks;

    $hooks['posting'][] = 'dice';
}

function dice_authorized($board) {
    return true;
}

function dice_info() {
    $info = array();
    $info['type']['board-specific'] = false;

    return $info;
}

function dice_settings() {
    $settings = array();
}

function dice_help() {
    $output = '##8d5##: rolls 8 5-sided dices';
    return $output;
}

function dice_process_posting($post) {
    $post['message'] = preg_replace_callback("/##([0-9]+)d([0-9]+)##/", "dice__replace_callback",$post['message']);
    return $post;
}

function dice__replace_callback ($matches) {
    $result = "<span class='dice'>".$matches[1]."d".$matches[2]." (";
    $sum = 0;
    if (($matches[1]>100) || ($matches[2]>2000)) return "<span class='dice'>Я, конечно, умна, но не настолько! ".$matches[0]."</span>";
    if ($matches[0]=="##0d0##") return "<span class='dice'>Прекрати! Я так могу сломаться.</span>";
    if (($matches[1]==0) || ($matches[2]==0)) return "<span class='dice'>Я, конечно, умна, но не настолько! " .$matches[0]."</span>";
    for ($count=0; $count<$matches[1]; $count++) {
	$value=mt_rand(1,$matches[2]);
	if ($count!=0) $result.="+";
	$result.=$value;
	$sum+=$value;
    }
    $result.=") = ".$sum."</span>";
    return $result;
}
?>
