<?php
/**
* acp_screen [Russian]
*
* @package BB3 TOP Screen
* @version $Id: acp_screen.php, v 1.0.0
* @copyright (c) 2015 Anvar (apwa.ru)
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'ACP_RATING_SCREEN'				=> 'Скриншоты сайтов',
	'ACP_RATING_SCREEN_EXPLAIN'		=> 'Здесь вы можете настроить интеграцию с сервисом для загрузки скриншотов',
	'ACP_SCREEN_SETTINGS'			=> 'Управление скриншотами',
	'ACP_SCREEN_STATS'				=> 'Скриншот в статистике',
	'ACP_SCREEN_EXPLAIN'			=> 'Укажите размеры скриншота в статистике, 0 отключит вывод скринов.',
	'ACP_SCREEN_RATING'				=> 'Скриншоты в рейтинге',
	'ACP_SCREEN_UPDATE'				=> 'Время для обновления',
	'ACP_SCREEN_UPDATE_EXPLAIN'		=> 'Через сколько дней загружать обновления скринов.',
	'ACP_SCREEN_LINK'				=> 'Источник скринов',
	'ACP_SCREEN_LINK_EXPLAIN'		=> 'Укажите адрес URL по которому забирать скриншоты сайтов.<br />Используйте лексему {SCREEN_DOMAIN} в url для замены на домен и {SCREEN_SIZE} для подставки размеров заданных в настройках выше.',
	'ACP_SCREEN_DEFAULT'			=> 'Скрин по умолчанию',
	'ACP_SCREEN_DEFAULT_EXPLAIN'	=> 'Укажите адрес URL скриншота заглушки.<br />Оставьте пустым если не хотите использовать эту возможность.',
	'ACP_SCREEN_EXTENSION'			=> 'Расширение файла',
	'ACP_SCREEN_EXTENSION_EXPLAIN'	=> 'Изображения будут загружены в папку images/screen/<br /><strong class="error">Вам необходимо создать папку "screen" и выставить права доступа допускающие запись(обычно 777)</strong>',
	'SCREEN_EXTENSION'				=> array('.jpg' => 'JPEG', '.gif' => 'GIF', '.png' => 'PNG'),
));
