<?php
/**
* mods ucp_lines [Russian]
*
* @package TOP Rating phpBB3
* @version $Id: ucp_rating.php, v 1.0.0
* @copyright (c) 2015 Anvar (apwa.ru)
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'UCP_RATING'				=> 'ТОП Рейтинг',
	'UCP_RATING_MAIN'			=> 'Мои площадки',
	'UCP_RATING_MAIN_EXPLAIN'	=> 'Здесь вы можете просмотреть свои площадки, управлять ими и создавать новые.',
	'UCP_RATING_MANAGE'			=> 'Управление площадками',
	'UCP_RATING_ADD'			=> 'Добавить площадку',
	'UCP_RATING_ADD_EXPLAIN'	=> 'Здесь вы можете создать площадку, в последующем сможете изменить её название и описание.',

	'TOP_DETAILS'		=> 'ТОП Данные',
	'TOP_NAME'			=> 'Название сайта',
	'TOP_NAME_EXPLAIN'	=> 'Название сайта должно быть от %d до %d символов.',
	'TOP_NAME_ERROR'	=> 'Слишком короткое название',
	'TOP_NAME_ERROR2'	=> 'Слишком длинное название',
	'TOP_URL'			=> 'Введите url',
	'TOP_URL_EXPLAIN'	=> 'Адрес сайта должен начинаться с http:// или https://',
	'TOP_URL_ERROR'		=> 'Не верный формат URL',
	'TOP_URL_VALID'		=> 'Данный адрес сайта уже участвует в рейтинге',
	'TOP_DESC'			=> 'Описание сайта',
	'TOP_DESC_EXPLAIN'	=> 'Описание сайта должно быть не менее %d символов.',
	'TOP_DESC_ERROR'	=> 'Слишком короткое описание',
	'TOP_ADD_GOOD'		=> 'Площадка успешно создана.<br /><br /><a href="%s">Перейти к выбору счётчиков</a>',
	'TOP_ADD_NOT'		=> 'Новые сайты к участию не принимаются.',
	'TOP_DEL_GOOD'		=> 'Площадка успешно удалена.',
	'TOP_STATS_ENABLE'	=> 'Открыть стату',
	'TOP_STATS_DISABLE'	=> 'Закрыть стату',
	'TOP_STATS_GOOD'	=> 'Просмотр статистики для пользователей: <strong>%s</strong><br /><br /><a href="%s">Перейти к площадкам</a>',
	'TOP_COUNT_EDIT'	=> 'Управление счётчиками',
	'TOP_COUNT_EXPLAIN'	=> 'Здесь вы можете выбрать необходимый вид для каждого типа счётчика.',
	'TOP_COUNT_NOT'		=> 'Нет ни одного счётчика для отображения.',
	'TOP_COUNT_ERROR'	=> 'Вам необходимо выбрать по одному счётчику для каждого типа.',
	'TOP_COUNT_GOOD'	=> 'Данные о счётчиках успешно обновлены.<br /><br /><a href="%s">Получить код счётчиков</a>.',
	'TOP_COUNT_CODE'	=> 'Код счётчика',
	'TOP_COUNT_HTML'	=> 'HTML код',
	'TOP_COUNT_BBCODE'	=> 'BBCode',
));
