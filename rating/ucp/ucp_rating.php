<?php
/**
*
* @package phpBB TOP Rating
* @version $Id: ucp_rating.php 2015-3-24 10:55 $
* @copyright 2015 BB3.Mobi (c) Anvar [apwa.ru]
* license 
*/

namespace bb3top\rating\ucp;

class ucp_rating
{
	protected $user;

	protected $db;

	var $u_action;

	function main($id, $mode)
	{
		global $user, $config, $db, $table_prefix;
		global $template, $phpbb_root_path, $phpEx;
		global $request;

		$this->user = $user;
		$this->db = $db;

		$this->min_name = 10;
		$this->max_name = 30;

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

		$submit = ($request->is_set_post('submit')) ? true : false;
		$action = $request->variable('action', '');

		$error = array();
		$s_hidden_fields = array();

		switch($mode)
		{
			case 'main':

				switch($action)
				{
					case 'add':

						if (!$config['top_rating_type'] || $config['top_rating_type'] == 1)
						{
							trigger_error($this->user->lang['TOP_ADD_NOT']);
						}

						$top_name = utf8_normalize_nfc($request->variable('top_name', '', true));
						$top_desc = utf8_normalize_nfc($request->variable('top_desc', '', true));
						$top_url = utf8_normalize_nfc($request->variable('top_url', '', true));
						$cat_id = $request->variable('cat_id', 0);

						$cats = $this->rating_category($cat_id);

						if ($submit)
						{
							$parseurl = parse_url(trim($top_url));
							$host = $parseurl['host'];
							$scheme = $parseurl['scheme'];
							$reparse = explode('.', $host);
							$zona = $reparse[count($reparse)-1];
							if (!preg_match('/^http(s)?:\/\//i', $top_url) || !str_replace(array('.' . $zona, $zona), '', $host))
							{
								$error[] = $this->user->lang['TOP_URL_ERROR'];
							}
							else
							{
								$top_url = $scheme . '://' . $host;
							}

							$char = mb_strlen($top_name);
							if ($char < $this->min_name || $char > $this->max_name)
							{
								$error[] = ($char > $this->max_name) ? $this->user->lang['TOP_NAME_ERROR2'] : $this->user->lang['TOP_NAME_ERROR'];
							}

							if (mb_strlen($top_desc) < $config['top_desc_lenght'])
							{
								$error[] = $this->user->lang['TOP_DESC_ERROR'];
							}

							if (!isset($cats[$cat_id]['cat_id']))
							{
								trigger_error('ZALUPA!!!');
							}

							$sql = 'SELECT COUNT(top_id) AS num_top
								FROM ' . RATING_TABLE . '
								WHERE top_url = "' . $top_url . '"';
							$result = $this->db->sql_query($sql);
							if ($this->db->sql_fetchfield('num_top'))
							{
								$error[] = $this->user->lang['TOP_URL_VALID'];
							}
							$this->db->sql_freeresult($result);

							if (!sizeof($error))
							{
								$top_desc .= PHP_EOL . '[url=' . $top_url . ']' .  str_replace('http://', '', $top_url) . '[/url]';

								require_once($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
								// variables to hold the parameters for submit_post
								$poll = $uid = $bitfield = $options = ''; 
								generate_text_for_storage($top_name, $uid, $bitfield, $options, false, false, false);
								generate_text_for_storage($top_desc, $uid, $bitfield, $options, true, true, true);
								$data = array( 
									'forum_id'				=> $config['top_rating_forum'],
									'topic_id'				=> '',
									'icon_id'				=> false,
									'enable_bbcode'			=> true,
									'enable_smilies'		=> true,
									'enable_urls'			=> true,
									'enable_sig'			=> true,
									'from_user_id'			=> $this->user->data['user_id'],
									'from_username'			=> $this->user->data['username'],
									'from_user_ip'			=> $this->user->data['user_ip'],
									'message'				=> $top_desc,
									'message_md5'			=> md5($top_desc),
									'bbcode_bitfield'		=> $bitfield,
									'bbcode_uid'			=> $uid,
									'post_edit_locked'		=> 1,
									'topic_title'			=> $top_name,
									'notify_set'			=> false,
									'notify'				=> false,
									'post_time'				=> 0,
									'forum_name'			=> '',
									'enable_indexing'		=> true,
								);
								submit_post('post', $top_name, '', POST_NORMAL, $poll, $data);

								$sql = 'INSERT INTO ' . RATING_TABLE . ' ' .$this->db->sql_build_array('INSERT', array(
									'cat_id'		=> $cats[$cat_id]['cat_id'],
									'topic_id'		=> $data['topic_id'],
									'user_id'		=> $this->user->data['user_id'],
									'top_url'		=> $top_url,
									'top_type'		=> 1,
									'top_time_add'	=> time(),
								));
								$this->db->sql_query($sql);

								$top_id = $this->db->sql_nextid();

								$sql = 'UPDATE ' . RATING_CAT_TABLE . ' 
									SET cat_top_site = cat_top_site + 1, cat_top_new = cat_top_new + 1
									WHERE cat_id = ' . $cats[$cat_id]['cat_id'];
								$this->db->sql_query($sql);

								$meta_info = append_sid("{$phpbb_root_path}ucp.$phpEx", "i=$id&amp;mode=manage&amp;top_id=$top_id&amp;action=editcount");

								meta_refresh(3, $meta_info);

								trigger_error(sprintf($this->user->lang['TOP_ADD_GOOD'],  $meta_info));
							}
						}

						$options = '';
						foreach ($cats as $key => $value)
						{
							//if ($value['cat_top_type'] == 0 )
							{
								$options .= '<option value="' . $value['cat_id'] . '"' . (($value['cat_id'] == $cat_id) ? ' selected="selected"' : '') . '>' . $value['cat_title'] . '</option>';
							}
						}

						$s_hidden_fields['action'] = 'add';

						$template->assign_vars(array(
							'L_TOP_DESC_EXPLAIN'	=> sprintf($this->user->lang['TOP_DESC_EXPLAIN'], $config['top_desc_lenght']),
							'L_TOP_NAME_EXPLAIN'	=> sprintf($this->user->lang['TOP_NAME_EXPLAIN'], $this->min_name, $this->max_name),
							'TOP_NAME'				=> $top_name,
							'TOP_URL'				=> $top_url,
							'TOP_DESC'				=> $top_desc,
							'TOP_CATS'				=> $options,
						));

						$this->tpl_name = 'ucp_rating_add';
						$this->page_title = $this->user->lang['UCP_RATING_ADD'];

					break;

					default:

						$sql_array = array(
							'SELECT'	=> 'r.*',
							'FROM'		=> array(RATING_TABLE => 'r'),
						);

						$sql_array['SELECT'] .= ', t.topic_title, p.post_text';

						$sql_array['LEFT_JOIN'][] = array(
							'FROM'	=> array(TOPICS_TABLE => 't'),
							'ON'	=> 'r.topic_id = t.topic_id',
						);

						$sql_array['LEFT_JOIN'][] = array(
							'FROM'	=> array(POSTS_TABLE => 'p'),
							'ON'	=> 't.topic_first_post_id = p.post_id'
						);

						$sql_array['WHERE'] = "r.user_id = " . $this->user->data['user_id'];

						$sql = $this->db->sql_build_query('SELECT', $sql_array);
						$result = $this->db->sql_query($sql);

						while ($row = $this->db->sql_fetchrow($result))
						{
							$top_name = str_replace(array('http://', 'https://'), '', $row['top_url']);
							if (!empty($row['topic_title']))
							{
								$top_name = $row['topic_title'] . ' (' . $top_name . ')';
							}

							// Limit chars
							$top_desc = $row['post_text'];
							if (mb_strlen($top_desc) >= $config['top_desc_lenght'])
							{
								$view_topic_url = append_sid("{$phpbb_root_path}viewtopic.$phpEx", 't=' . $row['topic_id']);
								$top_desc = mb_substr($top_desc, 0, $config['top_desc_lenght']) . '<a href="' . $view_topic_url . '">[...]</a>';
							}

							$template->assign_block_vars('toprow', array(
								'TOP_NAME'		=> $top_name,
								'TOP_DESC'		=> $top_desc,
								'TOP_URL'		=> $row['top_url'],
								'TOP_HOSTS'		=> $row['top_hosts'],
								'TOP_HITS'		=> $row['top_hits'],
								'TOP_IN'		=> $row['top_in'],
								'TOP_OUT'		=> $row['top_out'],
								'TOP_STATS'		=> $phpbb_root_path . 'stats/' . $row['top_id'],
							));
						}
						$this->db->sql_freeresult($result);

						$template->assign_vars(array(
							'U_ADD_PLATFORM'	=> append_sid($this->u_action . '&amp;action=add'),
							'S_ADD_PLATFORM'	=> ($config['top_rating_type'] == 1) ? false : true,
						));

						if (!$config['top_rating_type'] || $config['top_rating_type'] == 1)
						{
							$error[] = $this->user->lang['TOP_ADD_NOT'];
						}

						$this->tpl_name = 'ucp_rating';
						$this->page_title = $this->user->lang['UCP_RATING_MAIN'];

					break;
				}

				if ($config['top_rating_anounce'])
				{
					$this->view_announce();
				}

			break;

			case 'manage':

				$top_id = $request->variable('top_id', 0);

				switch($action)
				{
					case 'delete':

						if (!$top_id)
						{
							meta_refresh(3, $this->u_action);
							$message = $this->user->lang['PLATFORM_ERROR'] . '<br /><br />';
							$message .= sprintf($this->user->lang['RETURN_UCP'], '<a href="' . $this->u_action . '">', '</a>');
							trigger_error($message);
						}
						$toprow = $this->rating_top($top_id);

						if (confirm_box(true))
						{
							$sql = 'DELETE FROM `' . RATING_TABLE . '` WHERE `top_id` = ' . $toprow['top_id'];
							$this->db->sql_query($sql);

							$cats = $this->rating_category($toprow['cat_id']);

							if (!empty($cats[$toprow['cat_id']]['cat_top_site']))
							{
								$sql_upd = array('cat_top_site' => $cats[$toprow['cat_id']]['cat_top_site'] - 1);

								if (!empty($cats[$toprow['cat_id']]['cat_top_new']) && $toprow['top_type'] == 1)
								{
									$sql_upd['cat_top_new'] = $cats[$toprow['cat_id']]['cat_top_new'] - 1;
								}

								$sql = 'UPDATE ' . RATING_CAT_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_upd) . '
									WHERE cat_id = ' . $toprow['cat_id'];
								$this->db->sql_query($sql);
							}

							meta_refresh(3, $this->u_action);
							$message = $this->user->lang['TOP_DEL_GOOD'] . '<br /><br />';
							$message .= sprintf($this->user->lang['RETURN_UCP'], '<a href="' . $this->u_action . '">', '</a>');
							trigger_error($message);
						}
						else
						{
							confirm_box(false, $this->user->lang['CONFIRM_OPERATION'],
								build_hidden_fields(array(
									'i'			=> $id,
									'mode'		=> $mode,
									'action'	=> 'delete',
									'top_id'	=> $toprow['top_id'],
									)
								)
							);
						}

						redirect($this->u_action);

					break;

					case 'editcount':

						if (!$top_id)
						{
							meta_refresh(3, $this->u_action);
							$message = $this->user->lang['PLATFORM_ERROR'] . '<br /><br />';
							$message .= sprintf($this->user->lang['RETURN_UCP'], '<a href="' . $this->u_action . '">', '</a>');
							trigger_error($message);
						}

						$toprow = $this->rating_top($top_id);
						$file_img = array();

						$sql = 'SELECT * FROM ' . RATING_ICON_TABLE . '
							WHERE (cat_id = 0 OR cat_id = ' . (int) $toprow['cat_id'] . ')';
						$result = $this->db->sql_query($sql);
						$counts = array();
						while ($row = $this->db->sql_fetchrow($result))
						{
							$counts[$row['file']] = $row;
						}
						$this->db->sql_freeresult($result);

						$arcount = array();
						foreach ($this->user->lang['TOP_COUNT_TYPE'] as $type => $name)
						{
							if (!empty($this->user->lang['TOP_COUNTS_' . strtoupper($type)]))
							{
								$name = $this->user->lang['TOP_COUNTS_' . strtoupper($type)];
							}
							$template->assign_block_vars('counts', array(
								'COUNT_NAME'	=> $name,
								'COUNT_TYPE'	=> $type,
							));
							foreach($counts as $row)
							{
								if ($row['type'] == $type)
								{
									$file = $row['file'];
									$icon_big = explode(";", $toprow['top_icon_big']);
									$icon_small = explode(";", $toprow['top_icon_small']);
									$checked = ($icon_big[0] == $file || $icon_small[0] == $file) ? ' checked="checked"' : '';
									$template->assign_block_vars('counts.rows', array(
										'COUNT_IMG'		=> $phpbb_root_path . 'images/counts/' . $file,
										'COUNT_VALUE'	=> $file,
										'COUNT_CHEKED'	=> $checked,
										'COUNT_ID'		=> str_replace(array('.gif', '.png', '.jpg'), '', $file),
									));
									$file_img[$file] = $file;
								}
							}
						}

						if ($submit)
						{
							$small = $request->variable('small', '', true);
							$big = $request->variable('big', '', true);
							if (!empty($file_img[$small]) && !empty($file_img[$big]))
							{
								$small_type = '';
								$big_type = '';
								if (!empty($counts[$small]['position']))
								{
									$small_type .= ';' . $counts[$small]['position'];
								}

								if (!empty($counts[$big]['position']))
								{
									$big_type .= ';' . $counts[$big]['position'];
								}

								if (!empty($counts[$small]['color']))
								{
									$small_type .= (!$small_type) ? ';all;#' . $counts[$small]['color'] : ';#' . $counts[$small]['color'];
								}

								if (!empty($counts[$big]['color']))
								{
									$big_type .= (!$big_type) ? ';h;#' . $counts[$big]['color'] : ';#' . $counts[$big]['color'];
								}

								$sql = 'UPDATE ' . RATING_TABLE . ' 
									SET ' . $this->db->sql_build_array('UPDATE', array(
										'top_icon_big'		=> (string) $big . $big_type,
										'top_icon_small'	=> (string) $small . $small_type,
									)) . '
									WHERE top_id = "' . (int) $toprow['top_id'] . '"';
								$this->db->sql_query($sql);

								$meta_info = append_sid("{$phpbb_root_path}ucp.$phpEx", "i=$id&amp;mode=manage&amp;top_id=$top_id&amp;action=code");

								meta_refresh(3, $meta_info);
								trigger_error(sprintf($this->user->lang['TOP_COUNT_GOOD'], $meta_info));
							}
							else
							{
								$error[] = $this->user->lang['TOP_COUNT_ERROR'];
							}
						}

						$s_hidden_fields = array_merge($s_hidden_fields, array(
							'action'	=> 'editcount',
							'top_id'	=> $top_id,
						));

						$this->tpl_name = 'ucp_rating_count';
						$this->page_title = $this->user->lang['UCP_RATING_MAIN'];

					break;

					case 'code':

						if (!$top_id)
						{
							meta_refresh(3, $this->u_action);
							$message = $this->user->lang['PLATFORM_ERROR'] . '<br /><br />';
							$message .= sprintf($this->user->lang['RETURN_UCP'], '<a href="' . $this->u_action . '">', '</a>');
							trigger_error($message);
						}

						$toprow = $this->rating_top($top_id);

						$template->assign_vars(array(
							'S_COUNT_CODE'		=> true,
							'U_EDIT_COUNT'		=> append_sid($this->u_action . '&amp;top_id=' . $toprow['top_id'] . '&amp;action=editcount'),
						));

						foreach ($this->user->lang['TOP_COUNT_TYPE'] as $type => $name)
						{
							if (isset($toprow['top_icon_' . $type]))
							{
								$top_icon = explode(";", $toprow['top_icon_' . $type]);
								$template->assign_block_vars('counts', array(
									'COUNT_TYPE'	=> $name,
									'COUNT_IMG'		=> generate_board_url() . '/' . $type . '/' . $toprow['top_id'],
									'COUNT_URL'		=> generate_board_url() . '/in/' . $toprow['top_id'],
									'IMAGE_URL'		=> $phpbb_root_path . 'images/counts/' . $top_icon[0],
								));
							}
						}

						$this->tpl_name = 'ucp_rating_count';
						$this->page_title = $this->user->lang['UCP_RATING_MAIN'];

					break;

					case 'open':
					case 'closed':

						if (!$top_id)
						{
							meta_refresh(3, $this->u_action);
							$message = $this->user->lang['PLATFORM_ERROR'] . '<br /><br />';
							$message .= sprintf($this->user->lang['RETURN_UCP'], '<a href="' . $this->u_action . '">', '</a>');
							trigger_error($message);
						}

						$toprow = $this->rating_top($top_id);

						if ($toprow['top_type'] == 1)
						{
							trigger_error($this->user->lang['FORM_INVALID']);
						}

						$sql = 'UPDATE ' . RATING_TABLE . ' 
							SET top_type = ' . (($action == 'open') ? 0 : 2) . '
							WHERE top_id = "' . (int) $toprow['top_id'] . '"';
						$this->db->sql_query($sql);

						$meta_info = append_sid("{$phpbb_root_path}ucp.$phpEx", "i=$id&amp;mode=manage");
						meta_refresh(3, $meta_info);
						$message = ($action == 'open') ? $this->user->lang['ENABLED'] : $this->user->lang['DISABLED'];
						$message = sprintf($this->user->lang['TOP_STATS_GOOD'], $message, $this->u_action);
						trigger_error($message);

					break;

					default:

						$sql_array = array(
							'SELECT'	=> 'r.top_id, r.top_url, r.top_icon_big, r.top_icon_small, r.top_type',
							'FROM'		=> array(RATING_TABLE => 'r'),
						);

						$sql_array['SELECT'] .= ', t.topic_title, t.topic_id';

						$sql_array['LEFT_JOIN'][] = array(
							'FROM'	=> array(TOPICS_TABLE => 't'),
							'ON'	=> 'r.topic_id = t.topic_id',
						);

						$sql_array['WHERE'] = "r.user_id = " . $this->user->data['user_id'];

						$sql = $this->db->sql_build_query('SELECT', $sql_array);
						$result = $this->db->sql_query($sql);

						while ($row = $this->db->sql_fetchrow($result))
						{
							$top_url = $row['top_url'];
							$top_name = str_replace(array('http://', 'https://'), '', $row['top_url']);
							if (!empty($row['topic_title']))
							{
								$top_name = $row['topic_title'] . ' (' . $top_name . ')';
								$top_url = append_sid("{$phpbb_root_path}viewtopic.$phpEx", 't=' . $row['topic_id']);
							}

							$template->assign_block_vars('toprow', array(
								'TOP_NAME'			=> $top_name,
								'TOP_URL'			=> $top_url,
								'U_EDIT_COUNT'		=> append_sid($this->u_action . '&amp;top_id=' . $row['top_id'] . '&amp;action=editcount'),
								'U_DEL_PLATFORM'	=> append_sid($this->u_action . '&amp;top_id=' . $row['top_id'] . '&amp;action=delete'),
								'U_COUNT_CODE'		=> append_sid($this->u_action . '&amp;top_id=' . $row['top_id'] . '&amp;action=code'),
								'U_CLOSED_STATS'	=> $this->u_action . '&amp;top_id=' . $row['top_id'] . '&amp;action=closed',
								'U_OPEN_STATS'		=> $this->u_action . '&amp;top_id=' . $row['top_id'] . '&amp;action=open',
								'S_COUNT_CODE'		=> (($row['top_icon_big'] || $row['top_icon_small']) ? true : false),
								'S_TOP_TYPE'		=> $row['top_type'],
							));
						}
						$this->db->sql_freeresult($result);

						if (!$config['top_rating_type'] || $config['top_rating_type'] == 1)
						{
							$error[] = $this->user->lang['TOP_ADD_NOT'];
						}

						$this->tpl_name = 'ucp_rating_manage';
						$this->page_title = $this->user->lang['UCP_RATING_MAIN'];

					break;
				}

			break;

			default:
				trigger_error('NO_MODE');
			break;
		}

		$s_hidden_fields = build_hidden_fields($s_hidden_fields);

		$template->assign_vars(array(
			'ERROR'				=> (sizeof($error)) ? implode('<br />', $error) : '',
			'S_HIDDEN_FIELDS'	=> $s_hidden_fields,
			'S_UCP_ACTION'		=> $this->u_action,
		));
	}

	/**
	* Select rating top
	*/
	function rating_top($top_id = false)
	{
		$sql_where = ($top_id) ? ' AND top_id = "' . $top_id . '"' : '';

		$sql = 'SELECT top_id, cat_id, topic_id, top_icon_big, top_icon_small, top_type
			FROM ' . RATING_TABLE . "
			WHERE user_id = " . $this->user->data['user_id'] . "
				$sql_where";
		$result = $this->db->sql_query($sql);

		$top = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$top_ary = array(
				'top_id'			=> $row['top_id'],
				'cat_id'			=> $row['cat_id'],
				'topic_id'			=> $row['topic_id'],
				'top_icon_big'		=> $row['top_icon_big'],
				'top_icon_small'	=> $row['top_icon_small'],
				'top_type'			=> $row['top_type'],
			);

			if (!$top_id)
			{
				$top[$top_ary['top_id']] = $top_ary;
			}
			else
			{
				$top = $top_ary;
			}
		}
		$this->db->sql_freeresult($result);

		return $top;
	}

	/**
	* Select rating cat
	*/
	function rating_category($cat_id = false)
	{
		$sql_where = ($cat_id) ? 'WHERE cat_id = "' . $this->db->sql_escape($cat_id) . '"' : '';

		$sql = 'SELECT * FROM ' . RATING_CAT_TABLE . "
			$sql_where
			ORDER BY cat_order ASC";
		$result = $this->db->sql_query($sql);

		$cats = array();
		while ($row = $this->db->sql_fetchrow($result))
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
		$this->db->sql_freeresult($result);

		return $cats;
	}

	function view_announce($limit = 1)
	{
		global $config, $template, $phpbb_root_path, $phpEx;

		$sql = 'SELECT t.topic_id, t.topic_title, t.topic_time, p.post_text, p.bbcode_uid, p.bbcode_bitfield
			FROM ' . TOPICS_TABLE . ' t, ' . POSTS_TABLE . ' p
			WHERE t.forum_id = ' . (int) $config['top_rating_anounce'] . '
			AND t.topic_first_post_id = p.post_id
				AND t.topic_type = ' . POST_ANNOUNCE . '
				ORDER BY t.topic_time DESC';
		$result = $this->db->sql_query_limit($sql, $limit);

		while ($row = $this->db->sql_fetchrow($result))
		{
			// Limit chars
			$topic_desc = $row['post_text'];
			if (mb_strlen($topic_desc) >= 250)
			{
				strip_bbcode($topic_desc);
				$topic_desc = preg_replace("/\r\n|\r|\n/", '<br />', mb_substr($topic_desc, 0, 250));
				$topic_desc .= ' <a href="' . append_sid("{$phpbb_root_path}viewtopic.$phpEx", 't=' . $row['topic_id']) . '">[...]</a>';
			}
			else
			{
				// Parse the message and subject
				$parse_flags = ($row['bbcode_bitfield'] ? OPTION_FLAG_BBCODE : 0) | OPTION_FLAG_SMILIES;
				$topic_desc = generate_text_for_display($topic_desc, $row['bbcode_uid'], $row['bbcode_bitfield'], $parse_flags, true);
			}

			$template->assign_block_vars('announcerow', array(
				'TOPIC_TITLE'	=> censor_text($row['topic_title']),
				'TOPIC_DESC'	=> $topic_desc,
				'TOPIC_TIME'	=> $this->user->format_date($row['topic_time']),
				'TOPIC_URL'		=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", 't=' . $row['topic_id']),
			));
		}
		$this->db->sql_freeresult($result);
	}
}
