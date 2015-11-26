<?php
/**
* acp_country [Russian]
*
* @package TOP Rating phpBB3
* @version $Id: acp_country.php, v 1.0.0
* @copyright (c) 2015 Anvar (apwa.ru)
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'ACP_RATING_COUNTRY'		=> 'База провайдеров',
	'ACP_COUNTRY_SETTINGS'		=> 'Управление базой стран',
	'ACP_COUNTRY_MANAGE'		=> 'Страны, IP адреса, провайдеры',
	'ACP_COUNTRY_EDIT'			=> 'Изменить страну/провайдера',
	'ACP_COUNTRY_ADD'			=> 'Добавить страну/провайдера',
	'ACP_COUNTRY_DELETE'		=> 'Удалить',
	'ACP_COUNTRY_EDIT_IP'		=> 'Редактировать IP',
	'ACP_COUNTRY_NAME'			=> 'Страна',
	'ACP_COUNTRY_LANG'			=> 'Обозначение',
	'ACP_COUNTRY_PROVIDER'		=> 'Провайдер',
	'ACP_COUNTRY_ERROR_VALID'	=> 'Обозначение стран должно состоять из 2 или 3 латинских символов верхнего регистра.',
	'ACP_COUNTRY_ERROR_LENGHT'	=> 'Не верная длина названия страны и(или) названия провайдера(должно быть от 3 до 16 символов).',
	'ACP_COUNTRY_EDIT_OK'		=> 'Названия стран и операторов успешно изменены!<br /><br /><a href="%s">Вернуться к списку стран</a>',
	'ACP_COUNTRY_DELETE_OK'		=> 'Названия стран, операторов и ip адреса успешно удалены!<br /><br /><a href="%s">Вернуться к списку стран</a>',
	'ACP_COUNTRY_ADD_OK'		=> 'Новая страна, провайдер успешно добавлены!<br /><br /><a href="%s">Вернуться к списку стран</a>',

	'ACP_COUNTRY_IP_DELETE'			=> 'Вы действительно хотите удалить эти IP адреса?',
	'ACP_COUNTRY_IP_DELETE_OK'		=> 'ip адреса успешно удалены!<br /><br /><a href="%s">Вернуться к списку стран</a>',
	'ACP_COUNTRY_IP_ERROR_LENGHT'	=> 'Не верная длина IP адреса',
	'ACP_COUNTRY_IP_ERROR_VALID'	=> 'Данный ip уже входит в диапазон IP адресов базы данных.',
	'ACP_COUNTRY_IP_EDIT_OK'		=> 'ip адреса успешно изменены!<br /><br /><a href="%s">Перейти к провайдеру</a>',
	'ACP_COUNTRY_IP_EDIT'			=> 'Изменить диапазон ip адресов провайдера',
	'ACP_COUNTRY_IP_NEW_OK'			=> 'ip адреса успешно добавлены!<br /><br /><a href="%s">Перейти к провайдеру</a>',
	'ACP_COUNTRY_IP_NEW'			=> 'Добавить диапазон ip адресов провайдера',
	'ACP_COUNTRY_IP_SETTINGS'		=> 'Управление IP адресами',
));
