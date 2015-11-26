<?php
/**
*
* lines[Russian]
*
* @package language
* @version $Id: TOP Rating BB3.Mobi
* @copyright (c) 2015 Anvar ( apwa.ru )
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'RATING'				=> 'Рейтинг',
	'RATING_TOP'			=> 'ТОП',
	'RATING_CATEGORIES'		=> 'Категории',
	'RATING_PLATFORMS'		=> 'Участников',
	'RATING_PLATFORMS_NEW'	=> 'Новые',
	'RATING_CAT_SELECT'		=> 'Выберите категорию',
	'RATING_NOT_CATEGORIES'	=> 'Категории рейтинга ещё не созданы',

	'RATING_ANOUNCE'		=> 'Анонсы топ рейтинга',
	'VIEWING_RATING'		=> 'Просмотр топ рейтинга',
	'NOT_VIEW_RATING'		=> 'ТОП Рейтинг в настроящее время отключён.',

	'PLATFORM_MANAGE'		=> 'Управление площадками',
	'PLATFORM_ERROR'		=> 'Не выбрана площадка',
	'PLATFORM_NOT'			=> 'Нет площадок для отображения.',
	'PLATFORM_DETAILS'		=> 'Сайты из категории: %s',
	'PLATFORM_COMMENT'		=> 'Комментарии',
	'PLATFORM_USER'			=> 'Сайтов в рейтинге',
	'PLATFORM_TYPE'	=> array(
		0	=> 'Участник ТОП',
		1	=> 'Новый участник',
		2	=> 'Заблокированный',
	),

	'ADD_PLATFORM'			=> 'Добавить сайт',
	'DEL_PLATFORM'			=> 'Удалить площадку',
	'TOTAL_PLATFOM'			=> array(
		1	=> 'Всего %d сайт',
		2	=> 'Всего %d сайта',
		3	=> 'Всего %d сайтов',
	),

	'CATEGORY_TYPE'	=> array(
		0	=> 'Категории рейтинга',
		1	=> 'Не участвуют в ТОП',
		2	=> 'Категории каталога',
	),

	'TOP_COUNTS_BIG'	=> 'Большие счётчики',
	'TOP_COUNTS_SMALL'	=> 'Маленькие счётчики',
	'TOP_COUNT_TYPE'	=> array(
		'small'		=> 'Маленький',
		'big'		=> 'Большой',
	),

	'TOP_DESC'		=> 'Описание',
	'TOP_INALL'		=> 'Всего',
	'TOP_TODAY'		=> 'Сегодня',
	'TOP_YESTERDAY'	=> 'Вчера',
	'TOP_ONLINE'	=> 'Онлайн',
	'TOP_HOSTS'		=> 'Посетители',
	'TOP_HITS'		=> 'Просмотры',
	'TOP_IN'		=> 'Приходы',
	'TOP_OUT'		=> 'Переходы',
	'TOP_TYPE'		=> 'Тип',
	'TOP_DEVICE'	=> 'Устройство',
	'TOP_PROVIDER'	=> 'Провайдер',
	'TOP_PROVIDERS'	=> 'Провайдеры',
	'TOP_COUNTRY'	=> 'Страна',
	'TOP_COUNTRYS'	=> 'Страны',
	'TOP_NOT'		=> 'Нет статистики по данному критерию.',
	'TOP_CLOSED'	=> 'Просмотр отключён владельцем!',
));
