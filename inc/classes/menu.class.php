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
 * Menu class
 *
 * @package kusaba
 */
class Menu {

	function GetMenu($savetofile = false, $option = false) {
		global $tc_db, $dwoo, $dwoo_data, $kusabaxorg;

		load_haanga();
		$dwoo_data['boardpath'] = getCLBoardPath();

		if (KU_MENUTYPE == 'normal') {
			$dwoo_data['styles'] = explode(':', KU_MENUSTYLES);
		}


		$dwoo_data['files'] = $files;

		$sections = Array();

		$results_boardsexist = $tc_db->GetAll("SELECT `id` FROM `".KU_DBPREFIX."boards` LIMIT 1");
		if (count($results_boardsexist) > 0) {
			$sections = $tc_db->GetAll("SELECT * FROM `" . KU_DBPREFIX . "sections` ORDER BY `order` ASC");
			foreach($sections AS $key=>$section) {
				$results = $tc_db->GetAll("SELECT * FROM `" . KU_DBPREFIX . "boards` WHERE `section` = '" . $section['id'] . "' ORDER BY `order` ASC, `name` ASC /* MENU.CLASS.PHP */");
				foreach($results AS $line) {
						$sections[$key]['boards'][] = $line;
				}
			}
		}
		$dwoo_data['boards'] = $sections;


		if (isset($menu_nodirs) && isset($menu_dirs)) {
			return array($menu_nodirs, $menu_dirs);
		}
	}

	function Generate() {
		return $this->GetMenu(true);
	}

	function PrintMenu($option = false) {
		if ($option != false) {
			return $this->GetMenu(false, $option);
		} else {
			return $this->GetMenu(false);
		}
	}
}
?>
