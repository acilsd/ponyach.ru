<?php
/*
 * This file is part of kusaba.
 *
 * kusaba is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * kusaba is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * kusaba; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */
/**
 * AJAX thread expansion handler
 *
 * Returns replies of threads which have been requested through AJAX
 *
 * @package kusaba
 */

require 'config.php';
/* No need to waste effort if expansion is disabled */
if (!KU_EXPAND) die();
require KU_ROOTDIR . 'inc/functions.php';
require KU_ROOTDIR . 'inc/classes/board-post.class.php';

$board_name = $tc_db->GetOne("SELECT `name` FROM `" . KU_DBPREFIX . "boards` WHERE `name` = " . $tc_db->qstr($_GET['board']) . "");
if ($board_name != '') {
    $board_class = new Board($board_name);
    if ($board_class->board['locale'] != '') {
        changeLocale($board_class->board['locale']);
    }
} else {
    die('<font color="red">Invalid board.</font>');
}
$board_class->InitializeDwoo();
$board_class->dwoo_data['isexpand'] = true;
$board_class->dwoo_data['board'] = $board_class->board;
$board_class->dwoo_data['file_path'] = getCLBoardPath($board_class->board['name'], $board_class->board['loadbalanceurl_formatted'], '');
if (isset($_GET['preview'])) {
    require KU_ROOTDIR . 'inc/classes/parse.class.php';
    $parse_class = new Parse();

    if (isset($_GET['board']) && isset($_GET['parentid']) && isset($_GET['message'])) {
        die('<strong>' . _gettext('Post preview') . ':</strong><br /><div style="border: 1px dotted;padding: 8px;background-color: white;">' . $parse_class->ParsePost($_GET['message'], $board_class->board['name'], $board_class->board['type'], $_GET['parentid'], $board_class->board['id']) . '</div>');
    }

    die('Error');
}
if( isset($_GET['after']) && (int)$_GET['after']!=0 ) {
    $refreshq = ' AND p.`id` > '  . $tc_db->qstr((int)$_GET['after']);
    $getnewposts = true;
} else {
    $refreshq ='';
    $getnewposts = false;
}
if( isset($_GET['eqls']) && (int)$_GET['eqls']!=0 ) {
$posts = $tc_db->GetAll(
"
                                        select p.IS_DELETED, 
                                               p.*, 
                                               r.name as rating_name, 
                                               r.id as rating_id, 
                                               r.file as rating_file , 
                                               r.thumb_w as rating_thumb_w, 
                                               r.thumb_h as rating_thumb_h,
                                               f.name as file_name,
                                               ft.filetype as file_type,
                                               f.original as file_original,
                                               f.size as file_size,
                                               f.size_formatted as file_size_formatted,
                                               f.image_w as image_w,
                                               f.image_h as image_h,
                                               f.thumb_w as thumb_w,
                                               f.thumb_h as thumb_h,
                                               pf.`order` as file_order

                                        from " . KU_DBPREFIX . "posts AS p 
                                        left join posts_files pf
                                               on p.id = pf.postid and p.boardid = pf.boardid
                                        left join ratings r 
                                               on pf.ratingid = r.id 
                                        left join files f
                                               on pf.fileid = f.id 
                                        left join filetypes ft
                                               on f.type = ft.id
                                        where p.`boardid` = " . $board_class->board['id'] . " 
                                               and (p.`id` = " . $tc_db->qstr((int)$_GET['eqls']) . "  ) 
                                        order by p.`id`, pf.`order` asc"

//'SELECT * FROM `'.KU_DBPREFIX.'posts` WHERE `boardid` = ' . $board_class->board['id'] . ' AND `IS_DELETED` = 0 AND id = ' .$tc_db->qstr((int)$_GET['eqls']));
);
} else {		
$q = "
                                        select p.IS_DELETED, 
                                               p.*, 
                                               r.name as rating_name, 
                                               r.id as rating_id, 
                                               r.file as rating_file , 
                                               r.thumb_w as rating_thumb_w, 
                                               r.thumb_h as rating_thumb_h,
                                               f.name as file_name,
                                               ft.filetype as file_type,
                                               f.original as file_original,
                                               f.size as file_size,
                                               f.size_formatted as file_size_formatted,
                                               f.image_w as image_w,
                                               f.image_h as image_h,
                                               f.thumb_w as thumb_w,
                                               f.thumb_h as thumb_h,
                                               pf.`order` as file_order

                                        from " . KU_DBPREFIX . "posts AS p 
                                        left join posts_files pf
                                               on p.id = pf.postid and p.boardid = pf.boardid
                                        left join ratings r 
                                               on pf.ratingid = r.id 
                                        left join files f
                                               on pf.fileid = f.id 
                                        left join filetypes ft
                                               on f.type = ft.id
                                        where p.`boardid` = " . $board_class->board['id'] . " 
                                               and p.`parentid` = " . $tc_db->qstr($_GET['threadid']) . $refreshq . "
                                        order by p.`id`, pf.`order` asc";
$posts = $tc_db->GetAll($q);
//bdl_debug ($q);
//$posts = $tc_db->GetAll('SELECT * FROM `'.KU_DBPREFIX.'posts` WHERE `boardid` = ' . $board_class->board['id'] . ' AND `IS_DELETED` = 0 AND `parentid` = '.$tc_db->qstr($_GET['threadid']) . $refreshq .  ' ORDER BY `id` ASC');
}

$posts = $board_class->CompactPosts($posts);
if( count($posts)==0 ) die();

global $expandjavascript;
$output = '';
$expandjavascript = '';
$numimages = 0;
if ($board_class->board['type'] != 1) {
    $embeds = $tc_db->GetAll("SELECT filetype FROM `" . KU_DBPREFIX . "embeds`");
    foreach ($embeds as $embed) {
        $board_class->board['filetypes'][] .= $embed['filetype'];
    }
    $board_class->dwoo_data['filetypes'] = $board_class->board['filetypes'];
}
foreach ($posts as $key=>$post) {
    if ($post['file_type'] == 'jpg' || $post['file_type'] == 'gif' || $post['file_type'] == 'png') {
        $numimages++;
    }

    $posts[$key] = $board_class->BuildPost($post, false);
    
    $newlastid = $post['id'];
}
$board_class->dwoo_data['numimages'] = $numimages;
$board_class->dwoo_data['posts'] = $posts;
$board_class->dwoo_data['getnewposts'] = $getnewposts;
$board_class->dwoo_data['cf'] = $cf;
$board_class->dwoo_data['h'] = $h;
$output = Haanga::Load('img_thread.tpl', $board_class->dwoo_data, true);
if ($expandjavascript != '') {
    $output = '<a href="#" onclick="javascript:' . $expandjavascript . 'return false;">' . _gettext('Expand all images') . '</a>' . $output;
}

//$tpl2 = tmpfile();
$tpl2 = $board_class->board['name'] . '_res_' . $_GET['threadid'] . '_exp_' . $board_class->board['id'] . '_' . $_GET['after'] . '.html';
//print_page($tpl2, $output, $board_class->board['name']);

//haanga_spaces();
//load_haanga2();
//Haanga::Load($tpl2, Array());

$p = Array();
eval ('?>' .$output);

//echo $output;

?>
