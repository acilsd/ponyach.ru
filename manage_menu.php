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
 * Manage menu
 *
 * Loaded when a user visits manage.php
 *
 * @package kusaba
 */

session_start();

require 'config.php'; 
require KU_ROOTDIR . 'lib/Haanga.php';
require KU_ROOTDIR . 'inc/functions.php';
require KU_ROOTDIR . 'inc/classes/manage.class.php';

$manage_class = new Manage();
$dwoo_data['styles'] = explode(':', KU_MENUSTYLES);


$tpl_links = '';

	if (!$manage_class->ValidateSession(true)) {
		$tpl_links .= '<li><a href="manage_page.php">Войти</a></li>';
	} else {
		$tpl_links .= '<br><p class="text-error">Добро пожаловать' . ', <strong>' . $_SESSION['manageusername'] . '</strong>. <br>
		Ты : <strong>';
	if ($manage_class->CurrentUserIsAdministrator()) {
		$tpl_links .= 'Администратор';
	} elseif ($manage_class->CurrentUserIsModerator()) {
		$tpl_links .= 'Модератор';
	} else {
		$tpl_links .= 'Помощник';
	}
	$tpl_links .= '</strong></p>';

	$tpl_links .= '<li><a href="' . KU_WEBFOLDER . '" target="_top">' . 'Главная' . '</a></li>' . "\n";
	$tpl_links .= '<li><a href="manage_page.php?action=logout">'.'Выйти'.'</a></li>';
    $tpl_links .= '<li><a href="manage_page.php?action=changepwd">' .'Сменить пароль аккаунта' . '</a></li>';
	$tpl_links .= '</ul></div><br>';
	
	// Administration
	if ($manage_class->CurrentUserIsAdministrator()) {
	
		$tpl_links .= '<ul class="nav nav-list">' . "\n" .
		'<li class="nav-header">Администрирование</li>
		<li><a href="manage_page.php?action=spaceused&token='.$_SESSION['token'].'">Использовано места</a></li>
		<li><a href="manage_page.php?action=staff">Аккаунты</a></li>
		<li><a href="manage_page.php?action=changelog">Чейнджлог</a></li>
		<li><a href="manage_page.php?action=cleanup">Сброс кэша</a></li>' . "\n" .
 		'</ul></div><br>' .
		

		'<ul class="nav nav-list">
		<li class="nav-header">Доски</li>
		<li><a href="manage_page.php?action=adddelboard">' . _gettext('Добавить\удалить доску') . '</a></li>
	    <li><a href="manage_page.php?action=boardopts">' . _gettext('Опции доски') . '</a></li>
		<li><a href="manage_page.php?action=editsections">' . _gettext('Секции') . '</a></li>
		<li><a href="manage_page.php?action=wordfilter">' . _gettext('Вордфильтр') . '</a></li>
		<li><a href="manage_page.php?action=spam">' . _gettext('Спам фильтр') . '</a></li>
        <li><a href="manage_page.php?action=editratings">' . _gettext('Рейтинги') . '</a></li>' . "\n" .
		'</ul></div><br>';

	}
	// Moderation
	if ($manage_class->CurrentUserIsAdministrator() || $manage_class->CurrentUserIsModerator()) {
		$tpl_links .= '<ul class="nav nav-list">
		<li class="nav-header">Модерация</li>
		<li><a href="manage_page.php?action=modlog&token='.$_SESSION['token'].'">Модлог</a></li>
		<li><a href="manage_page.php?action=stickypost">' . _gettext('Прикрепленние тредов') . '</a></li>
		<li><a href="manage_page.php?action=lockpost">' . _gettext('Закрыть тред') . '</a></li>
		<li><a href="manage_page.php?action=official">' . _gettext('Автосоздание тредов') . '</a></li>
		<li><a href="manage_page.php?action=bans">' . _gettext('Удалить/добавить бан') . '</a></li>
		<li><a href="manage_page.php?action=threadbans">' . _gettext('Бан в треде') . '</a></li>';
		$tpl_links .= '<li><a href="manage_page.php?action=deletepostsbyip">' . _gettext('Удалить все посты с IP') . '</a></li>
		<li><a href="manage_page.php?action=delposts">' . _gettext('Удалить тред/пост') . '</a></li>
		<li><a href="manage_page.php?action=send_message_to_ip">' . _gettext('Отправить сообщение') . '</a></li>
		<li><a href="manage_page.php?action=approvepasscode">' . _gettext('Пасскоды') . '</a></li>
		<li><a href="manage_page.php?action=geoip">' . _gettext('Онлайн') . '</a></li>
		</ul></div><br>';
	}
   // posts 
   if ($manage_class->CurrentUserIsAdministrator() || $manage_class->CurrentUserIsModerator()) {
		$tpl_links .= '<ul class="nav nav-list">
		<li class="nav-header">Посты</li>
		<li><a href="manage_page.php?action=posting_rates">'._gettext('Посты за последний час').'</a></li>
		<li><a href="manage_page.php?action=search&token='.$_SESSION['token'].'">' . _gettext('Искать посты') . '</a></li>
		<li><a href="manage_page.php?action=ipsearch">' . _gettext('Поиск по IP') . '</a></li>
		</ul></div><br>';
	}

	if ($manage_class->CurrentUserIsModerator() ) {
		$tpl_links .= '<ul class="nav nav-list"><li class="nav-header"> Модерируемые доски</li>';
		$i = 0;
		$resultsboard = $tc_db->GetAll("SELECT HIGH_PRIORITY * FROM `" . KU_DBPREFIX . "boards` ORDER BY `name`");
		foreach ($resultsboard as $lineboard) {
			if ($manage_class->CurrentUserIsModeratorOfBoard($lineboard['name'], $_SESSION['manageusername'])) {
				$i++;
				$board = $lineboard['name'];
				$tpl_links .= "<li>/$board/</li>";
			}
		}
		if ($i == 0) {
			$tpl_links .= _gettext('No boards');
		}
	}

}

$dwoo_data['links'] = $tpl_links;
load_haanga();
Haanga::Load('/manage_menu.tpl', $dwoo_data);
?>
