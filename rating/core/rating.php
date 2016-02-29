<?php
/**
*
* @package TOP Rating phpBB3
* @version $Id: rating.php
* @copyright (c) 2015 Anvar (http://apwa.ru)
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

namespace bb3top\rating\core;

class rating
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\cache\service */
	protected $cache;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\content_visibility */
	protected $content_visibility;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\pagination */
	protected $pagination;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var string phpbb_root_path */
	protected $phpbb_root_path;

	/** @var string phpEx */
	protected $php_ext;

	public function __construct(\phpbb\auth\auth $auth, \phpbb\cache\service $cache, \phpbb\config\config $config, \phpbb\content_visibility $content_visibility, \phpbb\db\driver\driver_interface $db, \phpbb\pagination $pagination, \phpbb\request\request_interface $request, \phpbb\template\template $template, \phpbb\user $user, \phpbb\controller\helper $helper, $table_prefix, $phpbb_root_path, $php_ext)
	{
		if (!defined('RATING_CAT_TABLE'))
		{
			define('RATING_CAT_TABLE', $table_prefix . 'rating_cat');
		}

		if (!defined('RATING_TABLE'))
		{
			define('RATING_TABLE', $table_prefix . 'rating');
		}

		if (!defined('RATING_IP_TABLE'))
		{
			define('RATING_IP_TABLE', $table_prefix . 'rating_ip');
		}

		if (!defined('RATING_CLICK_TABLE'))
		{
			define('RATING_CLICK_TABLE', $table_prefix . 'rating_click');
		}

		if (!defined('RATING_HITS_TABLE'))
		{
			define('RATING_HITS_TABLE', $table_prefix . 'rating_hits');
		}

		if (!defined('RATING_ONLINE_TABLE'))
		{
			define('RATING_ONLINE_TABLE', $table_prefix . 'rating_online');
		}

		if (!defined('RATING_PROVIDER_TABLE'))
		{
			define('RATING_PROVIDER_TABLE', $table_prefix . 'rating_provider');
		}

		$this->auth = $auth;
		$this->cache = $cache;
		$this->config = $config;
		$this->content_visibility = $content_visibility;
		$this->db = $db;
		$this->pagination = $pagination;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->helper = $helper;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	public function view_top($catrow = array(), $top_count = 0)
	{
		if (!function_exists('display_forums'))
		{
			include($this->phpbb_root_path . 'includes/functions_display.' . $this->php_ext);
		}
		$start = $this->request->variable('top', 0);

		if (isset($catrow['cat_id']))
		{
			$top_count = $catrow['cat_top_site'];
			$sql_where = $this->db->sql_in_set('r.cat_id', $catrow['cat_id']);
		}
		else
		{
			//if (empty($catrow['cat_top_type']))
			{
				$sql_where = 'r.top_hosts != 0';//$this->db->sql_in_set('r.top_type', 0);
				if (!$this->config['top_platform_new'])
				{
					$sql_where .= ' AND r.top_time_add < ' . (time() - (86400*$this->config['top_platform_time']));
				}
			}

			if (!$top_count)
			{
				$sql_where .= ' AND t.topic_visibility != ' . ITEM_DELETED;

				$sql = 'SELECT COUNT(top_id) AS num_top
					FROM ' . RATING_TABLE . ' r, ' . TOPICS_TABLE . " t
					WHERE $sql_where
						AND r.topic_id = t.topic_id";
				$result = $this->db->sql_query($sql);
				$top_count = (int) $this->db->sql_fetchfield('num_top');
				$this->db->sql_freeresult($result);
			}
		}

		// Now only pull the data of the requested topics
		$sql_array = array(
			'SELECT'	=> 'r.*',
			'FROM'		=> array(RATING_TABLE => 'r'),
			'WHERE'		=> $sql_where,
			'ORDER_BY'	=> 'r.top_hosts DESC',
		);

		$sql_array['SELECT'] .= ', t.topic_id, t.forum_id, t.icon_id, t.topic_reported, t.topic_title, t.topic_status, t.topic_type, t.poll_start, t.topic_visibility, t.topic_posts_approved, t.topic_posts_unapproved, t.topic_posts_softdeleted';
		$sql_array['LEFT_JOIN'][] = array(
			'FROM'	=> array(TOPICS_TABLE => 't'),
			'ON'	=> 'r.topic_id = t.topic_id',
		);

		$sql_array['SELECT'] .= ', p.post_text';
		$sql_array['LEFT_JOIN'][] = array(
			'FROM'	=> array(POSTS_TABLE => 'p'),
			'ON'	=> 't.topic_first_post_id = p.post_id',
		);

		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query_limit($sql, $this->config['top_per_page'], $start);

		$rowset = $topic_icons = array();

		while ($row = $this->db->sql_fetchrow($result))
		{
			$rowset[] = $row;
		}
		$this->db->sql_freeresult($result);

		// No topics returned by the DB
		if (!sizeof($rowset))
		{
			return;
		}

		$obtain_icons = false;

		foreach ($rowset as $row)
		{
			if ($row['icon_id'] && $this->auth->acl_get('f_icons', $row['forum_id']))
			{
				$obtain_icons = true;
			}
		}

		// Grab icons
		if ($obtain_icons)
		{
			$icons = $this->cache->obtain_icons();
		}
		else
		{
			$icons = array();
		}

		$i = 0;
		foreach ($rowset as $row)
		{
			if (empty($row['topic_id']))
			{
				continue;
			}

			$topic_id = $row['topic_id'];

			$view_topic_url = append_sid("{$this->phpbb_root_path}viewtopic.$this->php_ext", 'f=' . $row['forum_id'] . '&amp;t=' . $topic_id);

			$topic_unapproved = ($row['topic_visibility'] == ITEM_UNAPPROVED && $this->auth->acl_get('m_approve', $row['forum_id']));
			$posts_unapproved = ($row['topic_visibility'] == ITEM_APPROVED && $row['topic_posts_unapproved'] && $this->auth->acl_get('m_approve', $row['forum_id']));
			$topic_deleted = $row['topic_visibility'] == ITEM_DELETED;

			$u_mcp_queue = ($topic_unapproved || $posts_unapproved) ? append_sid("{$this->phpbb_root_path}mcp.$this->php_ext", 'i=queue&amp;mode=' . (($topic_unapproved) ? 'approve_details' : 'unapproved_posts') . "&amp;t=$topic_id", true, $this->user->session_id) : '';

			if (!empty($icons[$row['icon_id']]))
			{
				$topic_icons[] = $topic_id;
			}
			$top_domain = $this->gethost($row['top_url']);
			$top_desc = $row['post_text'];
			strip_bbcode($top_desc);
			$top_desc = str_replace(array($row['top_url'], $top_domain), '', $top_desc);
			// Limit chars
			if (mb_strlen($top_desc) >= $this->config['top_desc_lenght'])
			{
				$top_desc = mb_substr($top_desc, 0, $this->config['top_desc_lenght']) . '...';
			}

			$top_row = array(
				'TOP_ID'				=> $row['top_id'],
				'TOP_NUMBER'			=> $i + $start + 1,
				'TOP_URL'				=> $this->helper->route("bb3top_rating_out", array('top_id' => $row['top_id'])),
				'TOP_STATS'				=> $this->helper->route("bb3top_rating_stats", array('top_id' => $row['top_id'])),
				'TOP_NAME'				=> censor_text($row['topic_title']),
				'TOP_DESC'				=> $top_desc,
				'TOP_DOMAIN'			=> $top_domain,
				'TOP_HOSTS'				=> $row['top_hosts'],
				'TOP_HITS'				=> $row['top_hits'],
				'TOP_IN'				=> $row['top_in'],
				'TOP_OUT'				=> $row['top_out'],
				'TOP_ONLINE'			=> $row['top_online'],

				'REPLIES'				=> $this->content_visibility->get_count('topic_posts', $row, $row['forum_id']) - 1,
				'TOPIC_ICON_IMG'		=> (!empty($icons[$row['icon_id']])) ? $icons[$row['icon_id']]['img'] : '',
				'TOPIC_ICON_IMG_WIDTH'	=> (!empty($icons[$row['icon_id']])) ? $icons[$row['icon_id']]['width'] : '',
				'TOPIC_ICON_IMG_HEIGHT'	=> (!empty($icons[$row['icon_id']])) ? $icons[$row['icon_id']]['height'] : '',

				'S_HAS_POLL'			=> ($row['poll_start']) ? true : false,
				'S_USER_POSTED'			=> (isset($row['topic_posted']) && $row['topic_posted']) ? true : false,
				'S_TOPIC_REPORTED'		=> ($row['topic_reported'] && $this->auth->acl_get('m_report', $row['forum_id'])) ? true : false,
				'S_TOPIC_UNAPPROVED'	=> $topic_unapproved,
				'S_POSTS_UNAPPROVED'	=> $posts_unapproved,
				'S_TOPIC_DELETED'		=> $topic_deleted,
				'S_TOPIC_LOCKED'		=> ($row['topic_status'] == ITEM_LOCKED) ? true : false,
				'S_TOPIC_MOVED'			=> ($row['topic_status'] == ITEM_MOVED) ? true : false,
				'S_TOPIC_TYPE_SWITCH'	=> ($row['topic_type'] == POST_ANNOUNCE || $row['topic_type'] == POST_GLOBAL) ? 1 : 0,

				'U_VIEW_TOPIC'			=> $view_topic_url,
				'U_MCP_REPORT'			=> append_sid("{$this->phpbb_root_path}mcp.$this->php_ext", 'i=reports&amp;mode=reports&amp;f=' . $row['forum_id'] . '&amp;t=' . $topic_id, true, $this->user->session_id),
				'U_MCP_QUEUE'			=> $u_mcp_queue,
			);

			global $phpbb_dispatcher;
			$vars = array('row', 'top_row');
			extract($phpbb_dispatcher->trigger_event('bb3top.rating_modify_top_row', compact($vars)));

			// Dump vars into template
			$this->template->assign_block_vars('toprow', $top_row);
			$i++;
		}

		// Get URL-parameters for pagination
		$url_params = explode('&', $this->user->page['query_string']);
		$append_params = false;
		foreach ($url_params as $param)
		{
			if (!$param)
			{
				continue;
			}
			if (strpos($param, '=') === false)
			{
				// Fix MSSTI Advanced BBCode MOD
				$append_params[$param] = '1';
				continue;
			}
			list($name, $value) = explode('=', $param);
			if ($name != 'top')
			{
				$append_params[$name] = $value;
			}
		}

		$page_url = append_sid($this->phpbb_root_path . $this->user->page['page_name'], $append_params);
		$this->pagination->generate_template_pagination($page_url, 'pagination', 'top', $top_count, $this->config['top_per_page'], $start);

		$this->template->assign_vars(array(
			'S_TOPIC_ICONS'		=> (sizeof($topic_icons)) ? true : false,
			'TOTAL_PLATFORM'	=> $this->user->lang('TOTAL_PLATFOM', $top_count),
			'NEWEST_POST_IMG'	=> $this->user->img('icon_topic_newest', 'VIEW_NEWEST_POST'),
			'REPORTED_IMG'		=> $this->user->img('icon_topic_reported', 'TOPIC_REPORTED'),
			'UNAPPROVED_IMG'	=> $this->user->img('icon_topic_unapproved', 'TOPIC_UNAPPROVED'),
			'DELETED_IMG'		=> $this->user->img('icon_topic_deleted', 'TOPIC_DELETED'),
			'POLL_IMG'			=> $this->user->img('icon_topic_poll', 'TOPIC_POLL'),
		));
	}

	public function view_cat($cat_id = false)
	{
		$sql_where = '';
		if ($cat_id)
		{
			if (!is_numeric($cat_id))
			{
				trigger_error('ZALUPA!!!');
			}
			$sql_where = 'WHERE cat_id = ' . (int) $cat_id;
		}

		$sql = "SELECT * FROM " . RATING_CAT_TABLE . " 
			$sql_where
		ORDER BY cat_order";
		$result = $this->db->sql_query($sql);

		$catrow = array();
		$all_site = $new_site = 0;
		while ($row = $this->db->sql_fetchrow($result))
		{
			$catrow[$row['cat_id']] = array(
				'cat_id'		=> $row['cat_id'],
				'cat_title'		=> $row['cat_title'],
				'cat_desc'		=> $row['cat_desc'],
				'cat_icon_img'	=> $row['cat_icon_img'],
				'cat_top_site'	=> $row['cat_top_site'],
				'cat_top_new'	=> $row['cat_top_new'],
				'cat_top_type'	=> $row['cat_top_type'],
			);
			$all_site = $all_site + $row['cat_top_site'];
			$new_site = $new_site + $row['cat_top_new'];
		}
		$this->db->sql_freeresult($result);

		if ($cat_id)
		{
			return $catrow;
		}

		$this->template->assign_vars(array(
			'ALL_SITE_COUNT'	=> $all_site,
			'NEW_SITE_COUNT'	=> $new_site,
		));

		foreach ($this->user->lang['CATEGORY_TYPE'] as $type => $name)
		{
			$this->template->assign_block_vars('cat', array(
				'CAT_NAME'	=> $name,
				'CAT_TYPE'	=> $type,
				)
			);

			foreach ($catrow as $row)
			{
				if ($row['cat_top_type'] == $type)
				{
					$this->template->assign_block_vars('cat.catrow', array(
						'CAT_ID'	=> $row['cat_id'],
						'CAT_TITLE'	=> $row['cat_title'],
						'CAT_DESC'	=> $row['cat_desc'],
						'CAT_ICON'	=> $row['cat_icon_img'],
						'CAT_SITE'	=> $row['cat_top_site'],
						'CAT_NEW'	=> $row['cat_top_new'],
						'CAT_TYPE'	=> $row['cat_top_type'],

						'U_VIEWCAT'	=> $this->helper->route("bb3top_rating_cat", array('cat_id' => $row['cat_id'])),
						)
					);
				}
			}
		}

		return $catrow;
	}

	public function view_announce($limit = 2)
	{
		$sql = 'SELECT t.topic_id, t.topic_title, t.topic_type, t.topic_time, p.post_text, p.bbcode_uid, p.bbcode_bitfield
			FROM ' . TOPICS_TABLE . ' t, ' . POSTS_TABLE . ' p
			WHERE t.forum_id = ' . (int) $this->config['top_rating_anounce'] . '
				AND t.topic_first_post_id = p.post_id
				AND t.topic_type != ' . POST_ANNOUNCE . '
				ORDER BY t.topic_type DESC, t.topic_time DESC';
		$result = $this->db->sql_query_limit($sql, $limit);

		$s_type_switch = 0;
		while ($row = $this->db->sql_fetchrow($result))
		{
			// This will allow the style designer to output a different header
			// or even separate the list of announcements from sticky and normal topics
			$s_type_switch_test = ($row['topic_type'] == POST_STICKY || $row['topic_type'] == POST_GLOBAL) ? 1 : 0;

			// Limit chars
			$topic_desc = $row['post_text'];
			if (mb_strlen($topic_desc) >= 250)
			{
				strip_bbcode($topic_desc);
				$topic_desc = preg_replace("/\r\n|\r|\n/", '<br />', mb_substr($topic_desc, 0, 250));
				$topic_desc .= ' <a href="' . append_sid("{$this->phpbb_root_path}viewtopic.$this->php_ext", 't=' . $row['topic_id']) . '">[...]</a>';
			}
			else
			{
				// Parse the message and subject
				$parse_flags = ($row['bbcode_bitfield'] ? OPTION_FLAG_BBCODE : 0) | OPTION_FLAG_SMILIES;
				$topic_desc = generate_text_for_display($topic_desc, $row['bbcode_uid'], $row['bbcode_bitfield'], $parse_flags, true);
			}

			$this->template->assign_block_vars('announcerow', array(
				'TOPIC_TITLE'			=> censor_text($row['topic_title']),
				'TOPIC_DESC'			=> $topic_desc,
				'TOPIC_TIME'			=> $this->user->format_date($row['topic_time']),
				'TOPIC_URL'				=> append_sid("{$this->phpbb_root_path}viewtopic.$this->php_ext", 't=' . $row['topic_id']),
				'S_TOPIC_TYPE_SWITCH'	=> ($s_type_switch == $s_type_switch_test) ? -1 : $s_type_switch_test,
			));
		}
		$this->db->sql_freeresult($result);
	}

	public function view_stat($top_id)
	{
		$sql_array = array(
			'SELECT'	=> 'r.*',
			'FROM'		=> array(RATING_TABLE => 'r'),
			'WHERE'		=> 'r.top_id = ' . (int) $top_id,
		);

		$sql_array['SELECT'] .= ', t.forum_id, t.topic_title, t.topic_posts_approved, t.topic_posts_unapproved, t.topic_posts_softdeleted, p.post_text';

		$sql_array['LEFT_JOIN'][] = array(
			'FROM'	=> array(TOPICS_TABLE => 't'),
			'ON'	=> 'r.topic_id = t.topic_id',
		);

		$sql_array['LEFT_JOIN'][] = array(
			'FROM'	=> array(POSTS_TABLE => 'p'),
			'ON'	=> 't.topic_first_post_id = p.post_id'
		);

		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql);
		if (!$row = $this->db->sql_fetchrow($result))
		{
			trigger_error('Not Platform');
		}
		$this->db->sql_freeresult($result);

		$top_domain = $this->gethost($row['top_url']);

		// Limit chars
		$top_desc = $row['post_text'];
		strip_bbcode($top_desc);
		$top_desc = str_replace(array($row['top_url'], $top_domain), '', $top_desc);

		$this->template->assign_vars(array(
			'TOP_NAME'			=> ($row['topic_title'] ? $row['topic_title'] : $top_domain),
			'TOP_URL'			=> $this->helper->route("bb3top_rating_out", array('top_id' => $row['top_id'])),
			'TOP_COMMENT'		=> append_sid("{$this->phpbb_root_path}viewtopic.$this->php_ext", 't=' . $row['topic_id']),
			'TOP_REPLIES'		=> $this->content_visibility->get_count('topic_posts', $row, $row['forum_id']) - 1,
			'TOP_DESC'			=> $top_desc,
			'TOP_DOMAIN'		=> $top_domain,
			'TOP_TYPE'			=> $row['top_type'],
			'TOP_ONLINE'		=> $row['top_online'],
			'TOP_HITS'			=> $row['top_hits'],
			'TOP_HOSTS'			=> $row['top_hosts'],
			'TOP_IN'			=> $row['top_in'],
			'TOP_OUT'			=> $row['top_out'],
			'TOP_HITS_ALL'		=> $row['top_hits_all'],
			'TOP_HOSTS_ALL'		=> $row['top_hosts_all'],
			'TOP_IN_ALL'		=> $row['top_in_all'],
			'TOP_OUT_ALL'		=> $row['top_out_all'],
			'TOP_HITS_BEFORE'	=> $row['top_hits_before'],
			'TOP_HOSTS_BEFORE'	=> $row['top_hosts_before'],
			'TOP_IN_BEFORE'		=> $row['top_in_before'],
			'TOP_OUT_BEFORE'	=> $row['top_out_before'],
		));

		global $phpbb_dispatcher;
		extract($phpbb_dispatcher->trigger_event('bb3top.rating_modify_stat_row', compact(array('row'))));

		return $row;
	}

	public function view_stat_hosts($top_id, $top_count = 0)
	{
		$start = $this->request->variable('top', 0);

		$sql = 'SELECT * FROM ' . RATING_HITS_TABLE . "
			WHERE top_id = " . (int) $top_id;
		$result = $this->db->sql_query_limit($sql, $this->config['top_per_page'], $start);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->template->assign_block_vars('statrow', array(
				'TOP_ID'		=> $row['top_id'],
				'TOP_TIME'		=> $this->user->format_date($row['top_time'], false, false),
				'TOP_IP'		=> $row['top_ip'],
				'TOP_DEVICE'	=> $row['top_device'],
				'TOP_PROV_ID'	=> $row['top_prov_id'],
				'TOP_COUNT'		=> $row['top_count'],
				)
			);
		}
		$this->db->sql_freeresult($result);

		$page_url = $this->helper->route("bb3top_rating_hosts", array('top_id' => $top_id));
		$this->pagination->generate_template_pagination($page_url, 'pagination', 'top', $top_count, $this->config['top_per_page'], $start);
	}

	public function view_stat_online($top_id, $top_count = 0)
	{
		$start = $this->request->variable('top', 0);

		$sql = 'SELECT * FROM ' . RATING_ONLINE_TABLE . "
			WHERE top_id = " . (int) $top_id;
		$result = $this->db->sql_query_limit($sql, $this->config['top_per_page'], $start);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->template->assign_block_vars('statrow', array(
				'TOP_ID'		=> $row['top_id'],
				'TOP_TIME'		=> $this->user->format_date($row['top_time'], false, false),
				'TOP_IP'		=> $row['top_ip'],
				'TOP_DEVICE'	=> $row['top_device'],
				'TOP_PROV_ID'	=> $row['top_prov_id'],
				)
			);
		}
		$this->db->sql_freeresult($result);

		$page_url = $this->helper->route("bb3top_rating_online", array('top_id' => $top_id));
		$this->pagination->generate_template_pagination($page_url, 'pagination', 'top', $top_count, $this->config['top_per_page'], $start);
	}

	public function view_stat_click($top_id, $top_count = 0)
	{
		$start = $this->request->variable('top', 0);

		$sql = 'SELECT * FROM ' . RATING_CLICK_TABLE . "
			WHERE top_id = " . (int) $top_id;
		$result = $this->db->sql_query_limit($sql, $this->config['top_per_page'], $start);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->template->assign_block_vars('statrow', array(
				'TOP_ID'		=> $row['top_id'],
				'TOP_TIME'		=> $this->user->format_date($row['top_time'], false, false),
				'TOP_TYPE'		=> ((!empty($this->user->lang['TOP_' . strtoupper($row['top_type'])])) ? $this->user->lang['TOP_' . strtoupper($row['top_type'])] : $row['top_type']),
				'TOP_IP'		=> $row['top_ip'],
				'TOP_DEVICE'	=> $row['top_device'],
				'TOP_PROV_ID'	=> $row['top_prov_id'],
				'TOP_COUNT'		=> $row['top_count'],
				)
			);
		}
		$this->db->sql_freeresult($result);

		$page_url = $this->helper->route("bb3top_rating_click", array('top_id' => $top_id));
		$this->pagination->generate_template_pagination($page_url, 'pagination', 'top', $top_count, $this->config['top_per_page'], $start);
	}

	public function view_country($top_id)
	{
		$sql = 'SELECT COUNT(h.top_id) AS count, p.*
			FROM ' . RATING_HITS_TABLE . ' h, ' . RATING_PROVIDER_TABLE . ' p
			WHERE h.top_id = ' . (int) $top_id . '
				AND p.prov_id = h.top_prov_id
				GROUP BY h.top_prov_id';
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->template->assign_block_vars('countryrow', array(
				'PROV_ID'		=> $row['prov_id'],
				'PROV_NAME'		=> $row['prov_name'],
				'PROV_COUNTRY'	=> $row['prov_country'],
				'PROV_LANG'		=> $row['prov_lang'],
				'COUNT'			=> $row['count'],
				)
			);
		}
		$this->db->sql_freeresult($result);
	}

	public function delete_top($topic_ids)
	{
		$sql = 'SELECT top_id, cat_id, top_type
			FROM ' . RATING_TABLE . "
			WHERE " . $this->db->sql_in_set('topic_id', $topic_ids);
		if ($result = $this->db->sql_query($sql))
		{
			$sql = "DELETE FROM " . RATING_TABLE . "
				WHERE " . $this->db->sql_in_set('topic_id', $topic_ids);
			$this->db->sql_query($sql);
		}

		while ($row = $this->db->sql_fetchrow($result))
		{
			if ($row['top_type'] == 1)
			{
				$sql = 'UPDATE ' . RATING_CAT_TABLE . "
					SET cat_top_site = cat_top_site - 1, cat_top_new = cat_top_new - 1
					WHERE cat_id = " . $row['cat_id'];
			}
			else
			{
				$sql = 'UPDATE ' . RATING_CAT_TABLE . "
					SET cat_top_site = cat_top_site - 1
					WHERE cat_id = " . $row['cat_id'];
			}
			$this->db->sql_query($sql);
		}
		$this->db->sql_freeresult($result);
	}

	public function count_user_site($user_id)
	{
		$count = array();
		$sql = 'SELECT top_id, top_url
			FROM ' . RATING_TABLE . "
			WHERE user_id = " . (int) $user_id;
		$result = $this->db->sql_query($sql);
		$count['count_site'] = 0;
		while ($row = $this->db->sql_fetchrow($result))
		{
			if (isset($count['site_url']))
			{
				$count['site_url'] .= ', <a href="' . $this->helper->route("bb3top_rating_stats", array('top_id' => $row['top_id'])) . '">' . $this->gethost($row['top_url']) . '</a>';
			}
			else
			{
				$count['site_url'] = '<a href="' . $this->helper->route("bb3top_rating_stats", array('top_id' => $row['top_id'])) . '">' . $this->gethost($row['top_url']) . '</a>';
			}
			$count['count_site']++;
		}
		$this->db->sql_freeresult($result);
		return $count;
	}

	private function gethost($address)
	{
		$parseurl = parse_url(trim($address));
		return trim($parseurl['host'] ? $parseurl['host'] : array_shift(explode('/', $parseurl['path'], 2)));
	}
}
