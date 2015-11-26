<?php
/***************************************************************************
*
* @package TOP Rating phpBB3
* @version $Id: acp_country.php, v 1.0.0
* @copyright (c) 2015 Anvar (apwa.ru)
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*****************************************************************************/

namespace bb3top\country\acp;
/**
* @package acp
*/
class acp_country
{
	var $u_action;
	var $new_config;

	function main($id, $mode)
	{
		global $config, $db, $user, $auth, $request, $template;
		global $phpbb_root_path, $phpbb_admin_path, $phpEx, $table_prefix;

		if (!defined('RATING_IP_TABLE'))
		{
			define('RATING_IP_TABLE', $table_prefix . 'rating_ip');
		}
		if (!defined('RATING_PROVIDER_TABLE'))
		{
			define('RATING_PROVIDER_TABLE', $table_prefix . 'rating_provider');
		}

		$action = $request->variable('action', '');

		$submit = ($request->is_set_post('submit')) ? true : false;

		$error = array();

		switch($mode)
		{
			case 'manage':

				$prov_lang = $request->variable('prov_lang', '');
				$prov_id = $request->variable('prov_id', 0);

				if ($prov_lang)
				{
					$sql = 'SELECT * FROM ' . RATING_PROVIDER_TABLE . '
						WHERE prov_lang = "' . (string) $prov_lang . '"
						ORDER BY prov_lang ASC';
				}
				else if ($prov_id)
				{
					$sql = 'SELECT * FROM ' . RATING_PROVIDER_TABLE . '
						WHERE prov_id = ' . (int) $prov_id;
				}
				else
				{
					$sql = 'SELECT * FROM ' . RATING_PROVIDER_TABLE . '
						GROUP BY prov_lang ASC';
				}
				$result = $db->sql_query($sql);

				$cats = array();
				while ($row = $db->sql_fetchrow($result))
				{
					$cats[$row['prov_id']] = array(
						'prov_id'		=> $row['prov_id'],
						'prov_name'		=> $row['prov_name'],
						'prov_country'	=> $row['prov_country'],
						'prov_lang'		=> $row['prov_lang'],
					);
				}
				$db->sql_freeresult($result);

				switch($action)
				{
					case 'add':

						$prov_country = utf8_normalize_nfc($request->variable('prov_country_new', '', true));
						$prov_lang = $request->variable('prov_lang_new', '');
						$prov_name = utf8_normalize_nfc($request->variable('prov_name_new', '', true));

						if ($submit)
						{
							if (strlen($prov_lang) < 2 || strlen($prov_lang) > 3)
							{
								$error[] = $user->lang['ACP_COUNTRY_ERROR_VALID'];
							}
							if (mb_strlen($prov_country) < 3 || mb_strlen($prov_name) < 3)
							{
								$error[] = $user->lang['ACP_COUNTRY_ERROR_LENGHT'];
							}

							if (!sizeof($error))
							{
								$sql = 'INSERT INTO ' . RATING_PROVIDER_TABLE . ' ' .$db->sql_build_array('INSERT', array(
									'prov_name'		=> $prov_name,
									'prov_lang'		=> $prov_lang,
									'prov_country'	=> $prov_country,
								));
								$db->sql_query($sql);
								meta_refresh(3, $this->u_action);
								trigger_error(sprintf($user->lang['ACP_COUNTRY_ADD_OK'], $this->u_action));
							}
						}

						$template->assign_block_vars('country_edit', array(
							'PROV_ID'		=> 'new',
							'PROV_NAME'		=> $prov_name,
							'PROV_COUNTRY'	=> $prov_country,
							'PROV_LANG'		=> $prov_lang
						));

						$template->assign_vars(array(
							'ERROR'			=> (sizeof($error)) ? implode('<br />', $error) : '',
							'S_NEW_ACTION'	=> true,
							'U_EDIT_ACTION'	=> $this->u_action,
							)
						);
						$this->tpl_name = 'acp_country_edit';
					break;

					case 'edit':

						foreach($cats as $value)
						{
							$prov_langs = $prov_names = $prov_countrys = '';
							if ($submit)
							{
								$prov_countrys = $request->variable('prov_country_' . $value['prov_id'], '', true);
								$prov_langs = $request->variable('prov_lang_' . $value['prov_id'], '');
								$prov_names = $request->variable('prov_name_' . $value['prov_id'], '', true);
								if (strlen($prov_langs) < 2 || strlen($prov_langs) > 3)
								{
									$error[] = '[' . utf8_normalize_nfc($prov_names) . '] ' . $user->lang['ACP_COUNTRY_ERROR_VALID'];
								}
								$prov_n_lenght = mb_strlen($prov_names);
								$prov_c_lenght = mb_strlen($prov_countrys);
								if ($prov_c_lenght < 3 || $prov_c_lenght > 16 || $prov_n_lenght < 3 || $prov_n_lenght > 16)
								{
									$error[] = '[' . utf8_normalize_nfc($prov_names) . '] ' . $user->lang['ACP_COUNTRY_ERROR_LENGHT'];
								}

								if (!sizeof($error))
								{
									$sql = 'UPDATE ' . RATING_PROVIDER_TABLE . ' SET
											prov_name = "' . utf8_normalize_nfc($prov_names) . '",
											prov_lang = "' . $prov_langs . '", 
											prov_country = "' . utf8_normalize_nfc($prov_countrys) . '"
										WHERE prov_id = ' . $value['prov_id'];
									$db->sql_query($sql);
								}
							}

							$template->assign_block_vars('country_edit', array(
								'PROV_ID'		=> $value['prov_id'],
								'PROV_NAME'		=> $value['prov_name'],
								'PROV_COUNTRY'	=> $value['prov_country'],
								'PROV_LANG'		=> $value['prov_lang'],
								'S_PROV_VIEW'	=> (($prov_lang) ? true : false),
							));
						}

						if (!sizeof($error) && $submit)
						{
							meta_refresh(3, $this->u_action);
							trigger_error(sprintf($user->lang['ACP_COUNTRY_EDIT_OK'], $this->u_action));
						}
						$rupor = ($prov_lang) ? '&amp;prov_lang=' . $prov_lang : '&amp;prov_id=' . $prov_id;
						$template->assign_vars(array(
							'ERROR'			=> (sizeof($error)) ? implode('<br />', $error) : '',
							'U_EDIT_ACTION'	=> $this->u_action . '&amp;action=edit' . $rupor)
						);
						$this->tpl_name = 'acp_country_edit';
					break;

					case 'delete':
						if (confirm_box(true))
						{
							if ($prov_lang)
							{
								foreach($cats as $prov)
								{
									$sql = 'DELETE FROM `' . RATING_IP_TABLE . '`
										WHERE `ip_prov_id` = "' . (int) $prov['prov_id'] . '"';
									$db->sql_query($sql);
								}

								$sql = 'DELETE FROM `' . RATING_PROVIDER_TABLE . '`
									WHERE `prov_lang` = "' . (string) $prov_lang . '"';
								$db->sql_query($sql);
							}
							else
							{
								$sql = 'DELETE FROM `' . RATING_IP_TABLE . '`
									WHERE `ip_prov_id` = "' . (int) $prov_id . '"';
								$db->sql_query($sql);

								$sql = 'DELETE FROM `' . RATING_PROVIDER_TABLE . '`
									WHERE `prov_id` = ' . (int) $prov_id;
								$db->sql_query($sql);
							}

							trigger_error(sprintf($user->lang['ACP_COUNTRY_DELETE_OK'], $this->u_action));
						}
						else
						{
							confirm_box(false, $user->lang['CONFIRM_OPERATION'],
								build_hidden_fields(array(
									'i' => $id,
									'mode' => $mode,
									'prov_id' => $prov_id,
									'prov_lang' => $prov_lang)
								)
							);
						}
					break;

					case 'ip_delete':
						$ip_id = $request->variable('ip', 0);
						if (confirm_box(true))
						{
							$sql = 'DELETE FROM `' . RATING_IP_TABLE . '`
								WHERE `ip_id` = "' . (int) $ip_id . '"';
							$db->sql_query($sql);
							trigger_error(sprintf($user->lang['ACP_COUNTRY_IP_DELETE_OK'], $this->u_action));
						}
						else
						{
							confirm_box(false, $user->lang['ACP_COUNTRY_IP_DELETE'],
								build_hidden_fields(array(
									'i' => $id,
									'mode' => $mode,
									'ip_id' => $ip_id)
								)
							);
						}
					break;

					case 'ip_edit':

						$ip_id = $request->variable('ip', 0);
						$prov_ip = $this->prov_ip(0, $ip_id);

						if (!sizeof($prov_ip[0]))
						{
							trigger_error('ERROR');
						}

						if ($submit)
						{
							$ip_start = $request->variable('ip_start', '');
							$ip_finish = $request->variable('ip_finish', '');

							if (!$ip_start || !$ip_finish)
							{
								$error[] = $user->lang['ACP_COUNTRY_IP_ERROR_LENGHT'];
							}

							if (strlen($ip_start) > 15 || strlen($ip_finish) > 15)
							{
								$error[] = $user->lang['ACP_COUNTRY_IP_ERROR_LENGHT'];
							}

							if (!sizeof($error))
							{
								$sql = 'UPDATE ' . RATING_IP_TABLE . ' SET
										ip_start = "' . sprintf("%u\n", ip2long($ip_start)) . '",
										ip_finish = "' . sprintf("%u\n", ip2long($ip_finish)) . '"
									WHERE ip_id = ' . (int) $prov_ip[0]['ip_id'];
								$db->sql_query($sql);

								$rupor = '&amp;action=ip&amp;prov_id=' . $prov_ip[0]['ip_prov_id'];
								meta_refresh(3, $this->u_action . $rupor);
								trigger_error(sprintf($user->lang['ACP_COUNTRY_IP_EDIT_OK'], $this->u_action . $rupor));
							}
						}
						$template->assign_vars(array(
							'IP_START'		=> long2ip($prov_ip[0]['ip_start']),
							'IP_FINISH'		=> long2ip($prov_ip[0]['ip_finish']),
							'ERROR'			=> (sizeof($error)) ? implode('<br />', $error) : '',
							'S_IP_EDIT'		=> true,
							'U_EDIT_ACTION'	=> $this->u_action . '&amp;action=ip_edit&amp;ip=' . $ip_id
						));
						$this->tpl_name = 'acp_country_edit';
					break;

					case 'ip_new':

						if (empty($cats[$prov_id]['prov_id']))
						{
							trigger_error('ERROR');
						}

						if ($submit)
						{
							$ip_start = $request->variable('ip_start', '');
							$ip_finish = $request->variable('ip_finish', '');

							if (!$ip_start || !$ip_finish)
							{
								$error[] = $user->lang['ACP_COUNTRY_IP_ERROR_LENGHT'];
							}

							if (strlen($ip_start) > 15 || strlen($ip_finish) > 15)
							{
								$error[] = $user->lang['ACP_COUNTRY_IP_ERROR_LENGHT'];
							}

							if ($this->prov_ip_validate($ip_start) || $this->prov_ip_validate($ip_finish))
							{
								$error[] = $user->lang['ACP_COUNTRY_IP_ERROR_VALID'];
							}

							if (!sizeof($error))
							{
								$sql = 'INSERT INTO ' . RATING_IP_TABLE . ' ' .$db->sql_build_array('INSERT', array(
									'ip_start'		=> sprintf("%u\n", ip2long($ip_start)),
									'ip_finish'		=> sprintf("%u\n", ip2long($ip_finish)),
									'ip_prov_id'	=> $prov_id,
								));
								$db->sql_query($sql);

								$rupor = '&amp;action=ip&amp;prov_id=' . $prov_id;
								meta_refresh(3, $this->u_action . $rupor);
								trigger_error(sprintf($user->lang['ACP_COUNTRY_IP_NEW_OK'], $this->u_action . $rupor));
							}
						}

						$template->assign_vars(array(
							'IP_START'		=> $ip_start,
							'IP_FINISH'		=> $ip_finish,
							'ERROR'			=> (sizeof($error)) ? implode('<br />', $error) : '',
							'S_IP_NEW'		=> true,
							'U_EDIT_ACTION'	=> $this->u_action . '&amp;action=ip_new&amp;prov_id=' . $prov_id
						));
						$this->tpl_name = 'acp_country_edit';
					break;

					case 'ip':

						if (empty($cats[$prov_id]['prov_id']))
						{
							trigger_error('ERROR');
						}

						$prov_ips = $this->prov_ip($cats[$prov_id]['prov_id']);
						foreach($prov_ips as $prov_ip)
						{
							$template->assign_block_vars('iprow', array(
								'IP_DELETE'		=> $this->u_action . '&amp;action=ip_delete&amp;ip=' . $prov_ip['ip_id'],
								'IP_EDIT'		=> $this->u_action . '&amp;action=ip_edit&amp;ip=' . $prov_ip['ip_id'],
								'IP_START'		=> long2ip($prov_ip['ip_start']),
								'IP_FINISH'		=> long2ip($prov_ip['ip_finish'])
							));
						}

						$template->assign_vars(array(
							'PROV_NAME'		=> $cats[$prov_id]['prov_name'],
							'PROV_COUNTRY'	=> $cats[$prov_id]['prov_country'],
							'S_IP_VIEW'		=> true,
							'U_EDIT_ACTION'	=> $this->u_action . '&amp;action=ip_new&amp;prov_id=' . $prov_id
						));
						$this->tpl_name = 'acp_country';
					break;

					default:

						$prov_country = '';
						foreach($cats as $value)
						{
							$rupor = ($prov_lang) ? '&amp;prov_id=' . $value['prov_id'] : '&amp;prov_lang=' . $value['prov_lang'];
							$template->assign_block_vars('country', array(
								'PROV_ID'		=> $value['prov_id'],
								'PROV_NAME'		=> $value['prov_name'],
								'PROV_COUNTRY'	=> $value['prov_country'],
								'PROV_LANG'		=> $value['prov_lang'],
								'U_PROV_VIEW'	=> $this->u_action . '&amp;prov_lang=' . $value['prov_lang'],
								'U_IP_VIEW'		=> $this->u_action . '&amp;action=ip&amp;prov_id=' . $value['prov_id'],
								'U_EDIT'		=> $this->u_action . '&amp;action=edit' . $rupor,
								'U_DELETE'		=> $this->u_action . '&amp;action=delete' . $rupor,
							));

							if ($prov_lang)
							{
								$prov_country = $value['prov_country'];
							}
						}

						if ($prov_lang)
						{
							$template->assign_vars(array(
								'PROV_LANG'		=> $prov_lang,
								'PROV_COUNTRY'	=> $prov_country,
								'S_PROV_VIEW'	=> true)
							);
						}
						$this->tpl_name = 'acp_country';
					break;
				}
			break;

			default:
				trigger_error('NO_MODE');
			break;
		}
		$this->page_title = $user->lang['ACP_COUNTRY_MANAGE'];
	}

	private function prov_ip($prov_id = 0, $ip_id = 0)
	{
		global $db;

		if ($prov_id)
		{
			$sql = 'SELECT * FROM ' . RATING_IP_TABLE . '
				WHERE ip_prov_id = ' . (int) $prov_id;
		}
		else
		{
			if (!$ip_id)
			{
				return;
			}
			$sql = 'SELECT * FROM ' . RATING_IP_TABLE . '
				WHERE ip_id = ' . (int) $ip_id;
		}
		$result = $db->sql_query($sql);

		$prov_ip = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$prov_ip[] = array(
				'ip_id'			=> $row['ip_id'],
				'ip_start'		=> $row['ip_start'],
				'ip_finish'		=> $row['ip_finish'],
				'ip_prov_id'	=> $row['ip_prov_id'],
			);
		}
		$db->sql_freeresult($result);

		return $prov_ip;
	}

	private function prov_ip_validate($prov_ip)
	{
		global $db;

		$ip_prov_id = false;

		$sql = 'SELECT ip_prov_id FROM ' . RATING_IP_TABLE . " WHERE INET_ATON('" . $prov_ip . "') BETWEEN `ip_start` AND `ip_finish`";
		$result = $db->sql_query($sql);
		if ($iprow = $db->sql_fetchrow($result))
		{
			$ip_prov_id = $iprow['ip_prov_id'];
		}
		$db->sql_freeresult($result);

		return $ip_prov_id;
	}
}
