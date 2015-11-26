<?php
/**
*
* @package BB3 TOP Screen
* @copyright BB3.MOBi (c) 2015 Anvar http://apwa.ru
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

namespace bb3top\screen\acp;

class acp_screen
{
	var $u_action;
	var $new_config = array();

	function main($id, $mode)
	{
		global $config, $user, $template, $request;

		$this->user = $user;
		$this->tpl_name = 'acp_board';
		$this->page_title = 'ACP_SCREEN_SETTINGS';

		$submit = $request->is_set_post('submit');

		$form_key = 'top_screen';
		add_form_key($form_key);

		$display_vars = array(
			'title'	=> 'ACP_RATING_SCREEN',
			'vars'	=> array(
				'legend1'	=> 'ACP_SCREEN_SETTINGS',
				'top_screen_stats'		=> array('lang' => 'ACP_SCREEN_STATS',		'validate' => 'int:0:600',	'type' => 'number:0:600', 'explain' => true, 'lang_explain' => 'ACP_SCREEN_EXPLAIN'),
				'top_screen_rating'		=> array('lang' => 'ACP_SCREEN_RATING',		'validate' => 'int:0:90',	'type' => 'number:0:90', 'explain' => true, 'lang_explain' => 'ACP_SCREEN_EXPLAIN'),
				'top_screen_update'		=> array('lang' => 'ACP_SCREEN_UPDATE',		'validate' => 'int:0:90',	'type' => 'number:0:90', 'explain' => true),
				'top_screen_link'		=> array('lang' => 'ACP_SCREEN_LINK',		'validate' => 'string',		'type' => 'text:70:220', 'explain' => true),
				'top_screen_default'	=> array('lang' => 'ACP_SCREEN_DEFAULT',	'validate' => 'string',		'type' => 'text:60:100', 'explain' => true),
				'top_screen_extension'	=> array('lang' => 'ACP_SCREEN_EXTENSION',	'validate' => 'string',		'type' => 'select', 'method' => 'select_extension', 'explain' => true),
				'legend2'	=> 'ACP_SUBMIT_CHANGES',
			),
		);

		if (isset($display_vars['lang']))
		{
			$user->add_lang($display_vars['lang']);
		}

		$this->new_config = $config;

		$cfg_array = ($request->is_set('config')) ? utf8_normalize_nfc($request->variable('config', array('' => ''), true)) : $this->new_config;
		$error = array();

		// We validate the complete config if wished
		validate_config_vars($display_vars['vars'], $cfg_array, $error);

		if ($submit && !check_form_key($form_key))
		{
			$error[] = $user->lang['FORM_INVALID'];
		}

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
		}

		$this->page_title = $display_vars['title'];

		$template->assign_vars(array(
			'L_TITLE'			=> $user->lang[$display_vars['title']],
			'L_TITLE_EXPLAIN'	=> $user->lang[$display_vars['title'] . '_EXPLAIN'],
			'S_ERROR'			=> (sizeof($error)) ? true : false,
			'ERROR_MSG'			=> implode('<br />', $error),
			'U_ACTION'			=> $this->u_action)
		);

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

			$content = build_cfg_template($type, $config_key, $this->new_config, $config_key, $vars);

			if (empty($content))
			{
				continue;
			}

			$template->assign_block_vars('options', array(
				'KEY'			=> $config_key,
				'TITLE'			=> (isset($user->lang[$vars['lang']])) ? $user->lang[$vars['lang']] : $vars['lang'],
				'S_EXPLAIN'		=> $vars['explain'],
				'TITLE_EXPLAIN'	=> $l_explain,
				'CONTENT'		=> $content,
				)
			);

			unset($display_vars['vars'][$config_key]);
		}
	}
	/**
	* Select display method
	*/
	function select_extension($selected_value, $value)
	{
		global $user;

		$act_options = '';

		foreach ($user->lang['SCREEN_EXTENSION'] as $ext => $name)
		{
			$selected = ($selected_value == $ext) ? ' selected="selected"' : '';
			$act_options .= '<option value="' . $ext . '"' . $selected . '>' .  $name . '</option>';
		}

		return $act_options;
	}
}
