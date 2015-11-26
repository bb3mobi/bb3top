<?php
/**
* mods acp_lines [Russian]
*
* @package TOP Rating phpBB3
* @version $Id: acp_rating.php, v 1.0.0
* @copyright (c) 2015 Anvar (apwa.ru)
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'ACP_RATING'					=> 'ТОП Рейтинг',
	'ACP_RATING_MANAGEMENT'			=> 'Управление рейтингом',
	'ACP_RATING_SETTINGS'			=> 'Настройки рейтинга',
	'ACP_RATING_MANAGE_CAT'			=> 'Управление категориями',
	'ACP_RATING_COUNTS'				=> 'Управление счётчиками',
	'ACP_RATING_CONFIG'				=> 'Основные настройки',
	'ACP_RATING_CONFIG_EXPLAIN'		=> 'Здесь можно настроить систему рейтинга.',
	'ACP_TOP_RATING_NAME'			=> 'Название рейтинга',
	'ACP_TOP_RATING_DESC'			=> 'Описание рейтинга',
	'ACP_TOP_RATING_ANO'			=> 'ID форума для анонсов',
	'ACP_TOP_RATING_ANO_EXPLAIN'	=> 'Объявления и глобальные темы будут показываться в личном разделе, при добавлении площадки.<br />Все кроме объявлений будут выведены на главной странице ТОП Рейтинга.<br />Глобальные темы будут преимущественно отображаться, не зависимо от времени создания.',
	'ACP_TOP_RATING_FORUM'			=> 'Выберите форум',
	'ACP_TOP_RATING_FORUM_EXPLAIN'	=> 'Форум в котором будут создаваться темы с названием, описанием сайта и возможностью комментирования.',
	'ACP_TOP_RATING_TYPE'			=> 'Тип рейтинга',
	'ACP_TOP_RATING_TYPE_EXPLAIN'	=> 'В зависимости от типа рейтинга, будут доступны или ограничены возможности.',
	'ACP_TOP_PER_PAGE'				=> 'Сайтов на странице',
	'ACP_TOP_DESC_LENGHT'			=> 'Символов в описании',
	'ACP_TOP_DESC_LENGHT_EXPLAIN'	=> 'Текст в описании сайтов будет обрезан до заданного размера.',
	'TOP_RATING_TYPE'	=> array(
		0	=> 'Отключён',
		1	=> 'Только существующим',
		2	=> 'Принимаются к участию',
		//3	=> 'Принимаются, каталог',
	),
	'ACP_CATEGORY_INDEX'			=> 'Категориии рейтинга',
	'ACP_CATEGORY_INDEX_EXPLAIN'	=> 'Здесь вы можете настроить и отсортировать категории рейтига.',
	'ACP_CATEGORY_NO_CATS'			=> 'Категории ещё не созданы',
	'ACP_CATEGORY_CREATE_CAT'		=> 'Создать категорию',
	'ACP_CATEGORY_ADD_FAIL'			=> 'Не указано имя категории для добавления.<br />Нажмите <a href="%s">здесь</a> для возврата.',
	'ACP_CATEGORY_ADD_GOOD'			=> 'Категория успешно добавлена.<br /><a href="%s">Перейти к настройке категории</a>',
	'ACP_CAT_DELETE_CONFIRM'		=> 'В какую категорию перенести площадки после удаления этой категории? <br /><form method="post"><fieldset class="submit-buttons"><select name="newcat">%s</select><br /><br /><input class="button1" type="submit" name="moveall" value="Переместить площадки" />&nbsp;<input class="button2" type="submit" name="deleteall" value="Удалить площадки" />&nbsp;<input type="submit" class="button2" name="cancelcat" value="Отменить" /></fieldset></form>',
	'ACP_CAT_DELETE_CONFIRM_ELSE'	=> 'Отсутствуют категории для перемещения участников.<br />Вы уверены, что хотите удалить эту категорию и всех содержащихся в ней участников?<br /><form method="post"><fieldset class="submit-buttons"><br /><input class="button2" type="submit" name="deleteall" value="Yes" />&nbsp;<input type="submit" class="button2" name="cancelcat" value="No" /></fieldset></form>',
	'ACP_CAT_DELETE_GOOD'			=> 'Эта категория, и все участники в ней успешно удалены.<br /><br /> Нажмите <a href="%s">здесь</a> для возврата к списку категорий.',
	'ACP_CAT_DELETE_MOVE_GOOD'		=> 'Все участники из "%1$s" были перемещены в "%2$s", а исходная категория была успешно удалена.<br /><br /> Нажмите <a href="%3$s">here</a> для возврата к списку категорий.',
	'ACP_CATEGORY'					=> 'Категория',
	'ACP_CATEGORY_EDIT'				=> 'Редактирование категории',
	'ACP_CATEGORY_EDIT_EXPLAIN'		=> 'Форма ниже позволяет вам изменять настройки существующей категории.',
	'ACP_CAT_TITLE'					=> 'Название категории',
	'ACP_CAT_DESC'					=> 'Описание категории',
	'ACP_CAT_TYPE'					=> 'Выберите тип создаваемой категории',
	'ACP_CAT_ICON'					=> 'Иконка категории',
	'ACP_CAT_ICON_EXPLAIN'			=> 'Введите url адрес иконки для данной категории или оставьте поле пустым.',
	'ACP_CATEGORY_EDIT_GOOD'		=> 'Категория успешно обновлена.<br /><br />Нажмите <a href="%s">здесь</a> для возврата к списоку категорий.',
	'ACP_CATEGORY_TYPE'				=> 'Тип категории',
	'ACP_COUNTS'					=> 'Изображения счётчиков',
	'ACP_COUNTS_EXPLAIN'			=> 'Здесь вы можете добавить счётчики, удалить и отредактировать существующие нажав на добавленный ранее.<br /><strong class="error">Изображения счётчиков должны находиться в папке images/counts/.</strong>',
	'ACP_COUNTS_MSG_NO'				=> 'Не добавлено ни одного счётчика',
	'ACP_ADD_COUNT'					=> 'Добавить счётчик',
	'ACP_COUNT_EDIT'				=> 'Редактирование счётчика',
	'ACP_COUNT_EDIT_EXPLAIN'		=> 'Здесь вы можете отредактировать назначение счётчика, удалить или заменить его.',
	'ACP_COUNT_ICON'				=> 'Выберите счётчик',
	'ACP_COUNT_ICON_EXPLAIN'		=> 'Если хотите его заменить, то выберите другую иконку.',
	'ACP_COUNT_CAT'					=> 'Категория для счётчика',
	'ACP_COUNT_CAT_EXPLAIN'			=> 'Можно использовать только для определённой категории.',
	'ACP_COUNT_CAT_DEFAULT'			=> 'Для всех категорий',
	'ACP_COUNT_TYPE'				=> 'Тип счётчика',
	'ACP_COUNT_TYPE_EXPLAIN'		=> 'Выберите соответсвующий тип счётчика.',
	'ACP_COUNT_COLOR'				=> 'Цвет цифры счётчиков',
	'ACP_COUNT_COLOR_EXPLAIN'		=> 'Если оставить пустым, то по умолчанию будет установлен чёрный цвет.',
	'ACP_COUNT_VERTICAL'			=> 'Вертикальный',
	'ACP_COUNT_VERTICAL_EXPLAIN'	=> 'Если выбрано, то цифры будут расположены вертикально, справа.',
	'ACP_COUNT_ENABLE'				=> 'Показ счётчиков',
	'ACP_COUNT_ENABLE_EXPLAIN'		=> 'Если включено, то будут отображаться цифры хосты\хиты.',
	'ACP_COUNT_DELETE'				=> 'Удалить счётчик',
	'ACP_COUNT_DELETE_EXPLAIN'		=> 'Удалить данные счётчика из базы данных?',
	'ACP_COUNT_MSG_NO_ADD'			=> 'Не указаны или не заполнены обязательные данные.<br />Нажмите <a href="%s">здесь</a> для возврата.',
	'ACP_COUNT_ADD_GOOD'			=> 'Счётчик успешно добавлен.<br />Нажмите <a href="%s">здесь</a> для возврата к списоку счётчиков.',
	'ACP_COUNT_MSG_EDIT'			=> 'Данные счётчика успешно обновлены в базе данных.<br />Нажмите <a href="%s">здесь</a> для возврата.',
	'ACP_COUNT_MSG_DELETE'			=> 'Данные счётчика успешно удалены из базы данных.<br />Нажмите <a href="%s">здесь</a> для возврата.',

	'ACP_RATING_ADDITIONAL'			=> 'Дополнительные настройки',
	'ACP_TOP_PLATFORM_NEW'			=> 'Разрешить участие в ТОП новым площадкам',
	'ACP_TOP_PLATFORM_TIME'			=> 'Сколько дней новым площадкам',
	'ACP_TOP_PLATFORM_TIME_EXPLAIN'	=> '0 снимает ограничение и опция выше не будет исключать возможность участия новым площадкам.',
	'ACP_TOP_RATING_INDEX'			=> 'Главная страница "Категории рейтинга"',
	'ACP_TOP_RATING_INDEX_EXPLAIN'	=> 'Если выключено, то в качестве основной страницы будет страница ТОП учстников.',
	'ACP_TOP_RATING_INTEGRATE'		=> 'Выводить на форуме',
	'TOP_RATING_INTEGRATE'	=> array(
		0	=> 'Не интегрировать',
		1	=> 'Под списком форумов ТОП сайты',
		2	=> 'Под списком форумов категории',
	),
));
