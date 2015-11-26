<?php
/***************************************************************************
*
* @package TOP Rating phpBB3
* @version $Id: acp_rating.php, v 1.0.0
* @copyright (c) 2015 Anvar (apwa.ru)
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*****************************************************************************/

namespace bb3top\rating\acp;
/**
* @package acp
*/
class acp_rating
{
	var $u_action;
	var $new_config;

	function main($id, $mode)
	{
		global $config, $db, $user, $auth, $request, $template;
		global $phpbb_root_path, $phpbb_admin_path, $phpEx, $table_prefix;

		if (!defined('RATING_TABLE'))
		{
			define('RATING_TABLE', $table_prefix . 'rating');
		}
		if (!defined('RATING_CAT_TABLE'))
		{
			define('RATING_CAT_TABLE', $table_prefix . 'rating_cat');
		}
		if (!defined('RATING_ICON_TABLE'))
		{
			define('RATING_ICON_TABLE', $table_prefix . 'rating_icon');
		}

		$submode = $request->variable('submode', '');
		$submit = ($request->is_set_post('submit')) ? true : false;
		switch($mode)
		{
			case 'config':
				$display_vars = array(
					'title'	=> 'ACP_RATING_CONFIG',
					'vars'	=> array(
					'legend1'		=> 'ACP_RATING_SETTINGS',
						'top_rating_name'		=> array('lang' => 'ACP_TOP_RATING_NAME',	'validate' => 'string',		'type' => 'text:30:30', 'explain' => false),
						'top_rating_desc'		=> array('lang' => 'ACP_TOP_RATING_DESC',	'validate' => 'string',		'type' => 'text:30:250', 'explain' => false),
						'top_rating_anounce'	=> array('lang' => 'ACP_TOP_RATING_ANO',	'validate' => 'int:0:150',	'type' => 'number:0:150', 'explain' => true),
						'top_rating_forum'		=> array('lang' => 'ACP_TOP_RATING_FORUM',	'validate' => 'int',		'type' => 'select', 'method' => 'select_forums', 'explain' => true),
						'top_rating_type'		=> array('lang' => 'ACP_TOP_RATING_TYPE',	'validate' => 'int',		'type' => 'select', 'method' => 'select_display_type', 'explain' => true),
						'top_per_page'			=> array('lang' => 'ACP_TOP_PER_PAGE',		'validate' => 'int:1:50',	'type' => 'number:1:50', 'explain' => false),
						'top_desc_lenght'		=> array('lang' => 'ACP_TOP_DESC_LENGHT',	'validate' => 'int:25:250',	'type' => 'number:25:250', 'explain' => true),
					'legend2'				=> 'ACP_RATING_ADDITIONAL',
						'top_platform_new'		=> array('lang' => 'ACP_TOP_PLATFORM_NEW',		'validate' => 'bool',		'type' => 'radio:yes_no', 'explain' => false),
						'top_platform_time'		=> array('lang' => 'ACP_TOP_PLATFORM_TIME',		'validate' => 'int:0:10',	'type' => 'number:0:10', 'explain' => true),
						'top_rating_index'		=> array('lang' => 'ACP_TOP_RATING_INDEX',		'validate' => 'bool',		'type' => 'radio:yes_no', 'explain' => true),
						'top_rating_integrate'	=> array('lang' => 'ACP_TOP_RATING_INTEGRATE',	'validate' => 'int',		'type' => 'select', 'method' => 'select_integrate', 'explain' => false),
					'legend3'		=> 'ACP_SUBMIT_CHANGES',
					)
				);
				if (isset($display_vars['lang']))
				{
					$user->add_lang($display_vars['lang']);
				}
				$this->new_config = $config;
				$cfg_array = ($request->is_set('config')) ? utf8_normalize_nfc($request->variable('config', array('' => ''), true)) : $this->new_config;
				$error = array();

				// We validate the complete config if whished
				validate_config_vars($display_vars['vars'], $cfg_array, $error);

				// Do not write values if there is an error
				if (sizeof($error))
				{
					$submit = false;
				}

				// We go through the display_vars to make sure no one is trying to set variables he/she is not allowed to...
				foreach ($display_vars['vars'] as $config_name => $null)
				{
					if (!isset($cfg_array[$config_name]) || strpos($config_name, 'legend') !== false)
					{
						continue;
					}

					$this->new_config[$config_name] = $config_value = $cfg_array[$config_name];

					if ($submit)
					{
						$config->set($config_name, $config_value);
					}
				}

				if ($submit)
				{
					trigger_error($user->lang['CONFIG_UPDATED'] . adm_back_link($this->u_action));
					break ;
				}
				$this->tpl_name = 'acp_rating_config';
				$this->page_title = $user->lang['ACP_RATING_CONFIG'];

				$template->assign_vars(array(
					'L_TITLE'			=> $user->lang[$display_vars['title']],
					'L_TITLE_EXPLAIN'	=> $user->lang[$display_vars['title'] . '_EXPLAIN'],

					'S_ERROR'			=> (sizeof($error)) ? true : false,
					'ERROR_MSG'			=> implode('<br />', $error),

					'U_ACTION'			=> $this->u_action,
				));

				// Output relevant page
				foreach ($display_vars['vars'] as $config_key => $vars)
				{
					if (!is_array($vars) && strpos($config_key, 'legend') === false)
					{
						continue;
					}

					if (strpos($config_key, 'legend') !== false)
					{
						$template->assign_block_vars('options', array(
							'S_LEGEND'		=> true,
							'LEGEND'		=> (isset($user->lang[$vars])) ? $user->lang[$vars] : $vars)
						);

						continue;
					}

					$type = explode(':', $vars['type']);

					$l_explain = '';
					if ($vars['explain'] && isset($vars['lang_explain']))
					{
						$l_explain = (isset($user->lang[$vars['lang_explain']])) ? $user->lang[$vars['lang_explain']] : $vars['lang_explain'];
					}
					else if ($vars['explain'])
					{
						$l_explain = (isset($user->lang[$vars['lang'] . '_EXPLAIN'])) ? $user->lang[$vars['lang'] . '_EXPLAIN'] : '';
					}

					$template->assign_block_vars('options', array(
						'KEY'			=> $config_key,
						'TITLE'			=> (isset($user->lang[$vars['lang']])) ? $user->lang[$vars['lang']] : $vars['lang'],
						'S_EXPLAIN'		=> $vars['explain'],
						'TITLE_EXPLAIN'	=> $l_explain,
						'CONTENT'		=> build_cfg_template($type, $config_key, $this->new_config, $config_key, $vars),
					));

					unset($display_vars['vars'][$config_key]);
				}
			break;

			case 'manage':

				$sql = 'SELECT * FROM ' . RATING_CAT_TABLE . '
					ORDER BY cat_order ASC';
				$result = $db->sql_query($sql);
				$cats = array();
				while ($row = $db->sql_fetchrow($result))
				{
					$cats[$row['cat_id']] = array(
						'cat_id'		=> $row['cat_id'],
						'cat_title'		=> $row['cat_title'],
						'cat_desc'		=> $row['cat_desc'],
						'cat_icon_img'	=> $row['cat_icon_img'],
						'cat_top_site'	=> $row['cat_top_site'],
						'cat_top_new'	=> $row['cat_top_new'],
						'cat_top_type'	=> $row['cat_top_type'],
						'cat_order'		=> $row['cat_order'],
					);
				}
				$db->sql_freeresult($result);

				$cat_id = $request->variable('catid', -1);
				$move_id = $request->variable('moveid', -1);
				$move_type = $request->variable('movetype', -1);

				if ($request->is_set_post('addcat'))
				{
					$submode = 'addcat';
				}
				else if ($request->is_set_post('cancelcat'))
				{
					$submode = 'catview';
				}
			break;

			case 'counts':

				$sql = 'SELECT * FROM ' . RATING_CAT_TABLE . ' ORDER BY cat_order ASC';
				$result = $db->sql_query($sql);
				$cats = array();
				while ($row = $db->sql_fetchrow($result))
				{
					$cats[$row['cat_id']] = array(
						'cat_id'	=> $row['cat_id'],
						'cat_title'	=> $row['cat_title'],
					);
				}
				$db->sql_freeresult($result);

				$sql = 'SELECT * FROM ' . RATING_ICON_TABLE;
				$result = $db->sql_query($sql);
				$counts = array();
				while ($row = $db->sql_fetchrow($result))
				{
					$file = explode(";", $row['file']);
					$counts[$file[0]] = array(
						'file'		=> $row['file'],
						'type'		=> $row['type'],
						'cat_id'	=> $row['cat_id'],
						'color'		=> $row['color'],
						'position'	=> $row['position'],
					);
				}
				$db->sql_freeresult($result);

				$cat_id = $request->variable('catid', -1);
				$file = $request->variable('file', -1);
				$type = $request->variable('type', -1);

				if ($request->is_set_post('addcount'))
				{
					$submode = 'addcount';
				}
				else if ($request->is_set_post('cancelcount'))
				{
					$submode = 'catview';
				}
			break;

			default:
				trigger_error('NO_MODE');
			break;
		}

		switch($submode)
		{
			case 'addcat':
				$cat_name = utf8_normalize_nfc($request->variable('cat_title', '', true));
				if (empty($cat_name))
				{
					trigger_error(sprintf($user->lang['ACP_CATEGORY_ADD_FAIL'], append_sid('index.php?i=' . $id . '&mode=manage')));
				}
				$this_id = 1;
				foreach ($cats as $key => $value)
				{
					$this_id++;
				}
				$sql = 'INSERT INTO ' . RATING_CAT_TABLE . ' ' .$db->sql_build_array('INSERT', array(
					'cat_title'		=> utf8_normalize_nfc($request->variable('cat_title', '', true)),
					'cat_order'		=> $this_id,
				));
				$db->sql_query($sql);
				$cat_id = $db->sql_nextid();
				trigger_error(sprintf($user->lang['ACP_CATEGORY_ADD_GOOD'], append_sid('index.php?i=' . $id . '&mode=manage&catid=' . $cat_id . '&submode=editcat')));
			break;

			case 'movecat':
				$swap_diff = ($move_type) ? 1 :  -1;
				$sql = 'UPDATE ' . RATING_CAT_TABLE . '
						SET cat_order = ' . $cats[$move_id]['cat_order'] . '
							WHERE cat_order = ' . $cats[$move_id]['cat_order'] . '+' . $swap_diff;
				$db->sql_query($sql);
				$sql = 'UPDATE ' . RATING_CAT_TABLE . '
						SET cat_order = ' . $cats[$move_id]['cat_order'] . '+' . $swap_diff . '
							WHERE cat_id = ' . $cats[$move_id]['cat_id'];
				$db->sql_query($sql);
				$submode = '';
				$sql = 'SELECT * FROM ' . RATING_CAT_TABLE . ' ORDER BY cat_order ASC';
				$result = $db->sql_query($sql);
				$cats = array();
				while ($row = $db->sql_fetchrow($result))
				{
					$cats[$row['cat_id']] = array(
						'cat_title'		=> $row['cat_title'],
						'cat_id'		=> $row['cat_id'],
						'cat_order'		=> $row['cat_order'],
					);
				}
				$db->sql_freeresult($result);
			break;

			case 'editcat':
				$this->tpl_name = 'acp_rating_cats_edit';
				$this->page_title = $user->lang['ACP_RATING_MANAGE_CAT'];

				$template->assign_vars(array(
					'CAT_TITLE'			=> $cats[$cat_id]['cat_title'],
					'CAT_DESC'			=> $cats[$cat_id]['cat_desc'],
					'CAT_TYPE'			=> $this->select_category_type($cats[$cat_id]['cat_top_type']),
					'CAT_ICON'			=> $cats[$cat_id]['cat_icon_img'],
					'U_EDIT_ACTION'		=> append_sid('index.php?i=' . $id . '&mode=manage&submode=editcatsql&catid=' . $cat_id),
				));
			break;

			case 'editcatsql':
				$sql = 'UPDATE ' . RATING_CAT_TABLE . ' 
					SET ' . $db->sql_build_array('UPDATE', array(
						'cat_title'		=> utf8_normalize_nfc($request->variable('cat_title', '', true)),
						'cat_desc'		=> utf8_normalize_nfc($request->variable('cat_desc', '', true)),
						'cat_top_type'	=> $request->variable('cat_type', '', true),
						'cat_icon_img'	=> utf8_normalize_nfc($request->variable('cat_icon', '', true)),
					)) . '
					WHERE cat_id = ' . $cats[$cat_id]['cat_id'];
				$db->sql_query($sql);
				trigger_error(sprintf($user->lang['ACP_CATEGORY_EDIT_GOOD'], append_sid('index.php?i=' . $id . '&mode=manage')));
			break;

			case 'deletecat':
				if (!$request->is_set_post('deleteall') && !$request->is_set_post('moveall'))
				{
					$options = '';
					foreach($cats as $key => $value)
					{
						if ($value['cat_id'] != $cat_id)
						{
							$options .= '<option value="' . $value['cat_id'] . '">' . $value['cat_title'] . '</option>';
						}
					}
					if ($options)
					{
						trigger_error(sprintf($user->lang['ACP_CAT_DELETE_CONFIRM'], $options));
					}
					else
					{
						trigger_error($user->lang['ACP_CAT_DELETE_CONFIRM_ELSE']);
					}
				}
				else if ($request->is_set_post('moveall'))
				{
					$sql = 'DELETE FROM ' . RATING_CAT_TABLE . ' WHERE cat_id = ' . $cat_id;
					$db->sql_query($sql);

					$newcat = $request->variable('newcat', '');
					$new_cat_title = '';
					foreach ($cats as $key => $value)
					{
						if ($cats[$newcat]['cat_top_site'])
						{
							$sql = 'UPDATE ' . RATING_TABLE . '
								SET ' . $db->sql_build_array('UPDATE', array(
									'cat_id'	=> $newcat,
								)) . '
								WHERE cat_id = ' . $value['cat_id'];
							$db->sql_query($sql);
							$i++;

							$new_cat_title = $cats[$newcat]['cat_title'];
							break;
						}
					}
					trigger_error(sprintf($user->lang['ACP_CAT_DELETE_MOVE_GOOD'], $cats[$cat_id]['cat_title'], $new_cat_title, append_sid('index.php?i=' . $id . '&mode=manage')));
				}
				else if ($request->is_set_post('deleteall'))
				{
					$sql = 'DELETE FROM ' . RATING_CAT_TABLE . ' WHERE cat_id = ' . $cat_id;
					$db->sql_query($sql);
					$sql = 'DELETE FROM ' . RATING_TABLE . ' WHERE cat_id = ' . $cat_id;
					$db->sql_query($sql);
					trigger_error(sprintf($user->lang['ACP_CAT_DELETE_GOOD'], append_sid('index.php?i=' . $id . '&mode=manage')));
				}
			break;

			case 'addcount':
				$file = utf8_normalize_nfc($request->variable('file', '', true));
				$type = $request->variable('type', '', true);
				if (!$file || !$type)
				{
					trigger_error(sprintf($user->lang['ACP_COUNT_MSG_NO_ADD'], append_sid('index.php?i=' . $id . '&mode=counts')));
				}
				$sql = 'INSERT INTO ' . RATING_ICON_TABLE . ' ' .$db->sql_build_array('INSERT', array(
					'file'		=> $file,
					'type'		=> $type,
					'cat_id'	=> $request->variable('cat_id', -1, true),
					'color'		=> '',
					'position'	=> '',
				));
				$db->sql_query($sql);
				trigger_error(sprintf($user->lang['ACP_COUNT_ADD_GOOD'], append_sid('index.php?i=' . $id . '&mode=counts')));
			break;

			case 'editcount':

				$file = utf8_normalize_nfc($request->variable('file', ''));

				$this->tpl_name = 'acp_rating_counts';
				$this->page_title = $user->lang['ACP_RATING_MANAGE_CAT'];

				$template->assign_vars(array(
					'S_ICON_OPTIONS'	=> $this->select_count_icon(false, $counts[$file]['file']),
					'S_CAT_OPTIONS'		=> $this->select_count_cat($cats, $counts[$file]['cat_id']),
					'S_TYPE_OPTIONS'	=> $this->select_count_type($counts[$file]['type']),
					'S_FILE_PATH'		=> $phpbb_root_path,
					'S_EDIT_COUNT'		=> true,
					'S_HIDDEN_FIELDS'	=> build_hidden_fields(array('file_id' => $file)),
					'COUNT_IMG'			=> $phpbb_root_path . 'images/counts/' . $counts[$file]['file'],
					'COUNT_COLOUR'		=> ((isset($counts[$file]['color'])) ? $counts[$file]['color'] : ''),
					'COUNT_CHECKED'		=> ((isset($counts[$file]['position'])) ? $counts[$file]['position'] : ''),
					'U_EDIT_ACTION'		=> append_sid('index.php?i=' . $id . '&mode=counts&submode=editcountsql'),
				));

			break;

			case 'editcountsql':

				$file_id = $request->variable('file_id', '');
				if ($request->is_set_post('deletecount') && isset($counts[$file_id]['file']))
				{
					$sql = 'DELETE FROM ' . RATING_ICON_TABLE . ' WHERE file = "' . $counts[$file_id]['file'] . '"';
					$db->sql_query($sql);
					trigger_error(sprintf($user->lang['ACP_COUNT_MSG_DELETE'], append_sid('index.php?i=' . $id . '&mode=counts')));
				}

				$file = $request->variable('file', '');
				$type = $request->variable('type', '');
				if (!$file || !$type)
				{
					trigger_error(sprintf($user->lang['ACP_COUNT_MSG_NO_ADD'], append_sid('index.php?i=' . $id . '&mode=counts')));
				}

				$position = '';
				if ($request->is_set_post('vertical'))
				{
					$position = 'v';
				}
				if ($request->is_set_post('countenable'))
				{
					$position = 'count';
				}

				$colour = $request->variable('count_colour', '');

				$sql = 'UPDATE ' . RATING_ICON_TABLE . ' 
					SET ' . $db->sql_build_array('UPDATE', array(
					'file'		=> $file,
					'type'		=> $type,
					'cat_id'	=> $request->variable('cat_id', 0),
					'color'		=> $colour,
					'position'	=> $position,
				)) . '
				WHERE file = "' . (string) $file_id . '"';
				$db->sql_query($sql);
				trigger_error(sprintf($user->lang['ACP_COUNT_MSG_EDIT'], append_sid('index.php?i=' . $id . '&mode=counts')));

			break;

			default:
				$submode = false;
			break;
		}

		if (!$submode)
		{
			if ($mode == 'manage')
			{
				$this->tpl_name = 'acp_rating';
				$this->page_title = $user->lang['ACP_RATING'];
				foreach($cats as $key2 => $value2)
				{
					$cat_icon_img = (!empty($value2['cat_icon_img'])) ? $value2['cat_icon_img'] : 'images/icon_subfolder.gif';
					$template->assign_block_vars('ratings', array(
						'U_EDIT'		=> append_sid('index.php?i=' . $id . '&mode=manage&submode=editcat&catid=' . $value2['cat_id']),
						'U_DELETE'		=> append_sid('index.php?i=' . $id . '&mode=manage&submode=deletecat&catid=' . $value2['cat_id']),
						'U_MOVE_UP'		=> append_sid('index.php?i=' . $id . '&mode=manage&submode=movecat&movetype=0&moveid=' . $value2['cat_id']),
						'U_MOVE_DOWN'	=> append_sid('index.php?i=' . $id . '&mode=manage&submode=movecat&movetype=1&moveid=' . $value2['cat_id']),
						'CAT_LINK'		=> append_sid('index.php?i=' . $id . '&mode=manage&submode=catview&catid=' . $value2['cat_id']),
						'CAT_IMAGE'		=> '<img src="' . $cat_icon_img . '" alt="" title="" />',
						'CAT_TITLE'		=> $value2['cat_title'],
						'CAT_DESC'		=> (isset($value2['cat_desc']) ? $value2['cat_desc'] : ''),
					));
				}
			}
			else if ($mode == 'counts')
			{
				$this->tpl_name = 'acp_rating_counts';
				$this->page_title = $user->lang['ACP_RATING_COUNTS'];

				$template->assign_vars(array(
					'S_ICON_OPTIONS'	=> $this->select_count_icon($counts),
					'S_CAT_OPTIONS'		=> $this->select_count_cat($cats),
					'S_TYPE_OPTIONS'	=> $this->select_count_type(),
					'S_FILE_PATH'		=> $phpbb_root_path,
					'U_ACTION'			=> append_sid('index.php?i=' . $id . '&mode=counts'),
				));

				foreach ($user->lang['TOP_COUNT_TYPE'] as $type => $name)
				{
					if (!empty($user->lang['TOP_COUNTS_' . strtoupper($type)]))
					{
						$name = $user->lang['TOP_COUNTS_' . strtoupper($type)];
					}
					$template->assign_block_vars('counts', array(
						'COUNT_NAME'	=> $name,
						'COUNT_TYPE'	=> $type,
					));

					foreach ($counts as $row)
					{
						if ($row['type'] == $type)
						{
							$template->assign_block_vars('counts.rows', array(
								'U_EDIT'		=> append_sid('index.php?i=' . $id . '&mode=counts&submode=editcount&file=' . $row['file']),
								'COUNT_IMG'		=> $phpbb_root_path . 'images/counts/' . $row['file'],
							));
						}
					}
				}
			}
		}
	}

	/**
	* Select icons count
	*/
	function select_count_icon($counts = array(), $filed = '')
	{
		global $phpbb_root_path;

		$dir = $phpbb_root_path . 'images/counts/';
		$options = '<option></option>';
		if ($dh = opendir($dir))
		{
			while (($file = readdir($dh)) !== false)
			{
				if (strlen($file) >= 3 && (strpos($file, '.gif',1) || strpos($file, '.jpg',1) || strpos($file, '.png',1)))
				{
					if (isset($counts[$file]['file']) != $file)
					{
						$options .= '<option value="' . $file . '"' . (($filed == $file) ? ' selected="selected"' : '') . '>' . $file . '</option>';
					}
				}
			}
			closedir($dh);
		}
		return $options;
	}

	/**
	* Select cat count
	*/
	function select_count_cat($cats = array(), $file_cat = false)
	{
		global $user;
		$options = '<select name="cat_id"><option value="0">' . $user->lang['ACP_COUNT_CAT_DEFAULT'] . '</option>';
		foreach ($cats as $key => $value)
		{
			$options .= '<option value="' . $value['cat_id'] . '"' . (($value['cat_id'] == $file_cat) ? ' selected="selected"' : '') . '>' . $value['cat_title'] . '</option>';
		}
		$options .= '</select>';
		return $options;
	}

	/**
	* Select count type
	*/
	function select_count_type($type = false)
	{
		global $user;
		$options = '<select name="type" id="count_type" onchange="switch_type_box()">';
		foreach ($user->lang['TOP_COUNT_TYPE'] as $key => $value)
		{
			$options .= '<option value="' . $key . '"' . (($key == $type) ? ' selected="selected"' : '') . '>' . $value . '</option>';
		}
		$options .= '</select>';
		return $options;
	}

	/**
	* Select Forums function
	*/
	function select_forums($value, $key)
	{
		global $user, $config;

		$forum_list = make_forum_select(false, false, true, true, true, false, true);

		$selected = array();
		if(isset($config[$key]) && strlen($config[$key]) > 0)
		{
			$selected = explode(',', $config[$key]);
		}
		// Build forum options
		$s_forum_options = '';
		foreach ($forum_list as $f_id => $f_row)
		{
			$s_forum_options .= '<option value="' . $f_id . '"' . ((in_array($f_id, $selected)) ? ' selected="selected"' : '') . (($f_row['disabled']) ? ' disabled="disabled"' : '') . '>' . $f_row['padding'] . $f_row['forum_name'] . '</option>';
		}

		return $s_forum_options;
	}

	/**
	* Select category type
	*/
	function select_category_type($type = false)
	{
		global $user;
		$options = '';
		foreach ($user->lang['CATEGORY_TYPE'] as $key => $value)
		{
			$options .= '<option value="' . $key . '"' . (($key == $type) ? ' selected="selected"' : '') . '>' . $value . '</option>';
		}
		return $options;
	}

	/**
	* Select display method
	*/
	function select_display_type($selected_value, $value)
	{
		global $user;

		$act_options = '';

		foreach ($user->lang['TOP_RATING_TYPE'] as $key => $value)
		{
			$selected = ($selected_value == $key) ? ' selected="selected"' : '';
			$act_options .= '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
		}

		return $act_options;
	}

	/**
	* Select display method
	*/
	function select_integrate($selected_value, $value)
	{
		global $user;

		$act_options = '';

		foreach ($user->lang['TOP_RATING_INTEGRATE'] as $key => $value)
		{
			$selected = ($selected_value == $key) ? ' selected="selected"' : '';
			$act_options .= '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
		}

		return $act_options;
	}
}
