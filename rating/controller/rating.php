<?php
/**
*
* @package TOP Rating phpBB3
* @version $Id: rating.php
* @copyright (c) 2015 Anvar (http://apwa.ru)
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

namespace bb3top\rating\controller;

//use Symfony\Component\HttpFoundation\Response;

class rating
{
	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \bb3top\rating\core\rating */
	protected $rating;

	/** @var string phpbb_root_path */
	protected $phpbb_root_path;

	/** @var string phpEx */
	protected $php_ext;

	public function __construct(\phpbb\template\template $template, \phpbb\config\config $config, \phpbb\user $user, \phpbb\request\request_interface $request, \phpbb\controller\helper $helper, $rating, $phpbb_root_path, $php_ext)
	{
		$this->template = $template;
		$this->config = $config;
		$this->user = $user;
		$this->request = $request;
		$this->helper = $helper;
		$this->rating = $rating;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	public function category($cat_id = false)
	{
		if (!$this->config['top_rating_type'] && !isset($this->user->data['user_type']))
		{
			trigger_error($this->user->lang['NOT_VIEW_RATING']);
		}

		$catrow = $this->rating->view_cat();

		$this->template->assign_block_vars('navlinks', array(
			'FORUM_NAME'	=> $this->user->lang['RATING'],
			'U_VIEW_FORUM'	=> $this->helper->route("bb3top_rating_category"),
			)
		);

		if (isset($catrow[$cat_id]['cat_id']) && is_array($catrow[$cat_id]))
		{
			$this->rating->view_top($catrow[$cat_id]);

			$params = array(
				'i'			=> '-bb3top-rating-ucp-ucp_rating',
				'mode'		=> 'main',
				'action'	=> 'add',
			);
			$add_new = append_sid(generate_board_url() . '/ucp.' . $this->php_ext, $params);

			$this->template->assign_vars(array(
				'CAT_ID'			=> $cat_id,
				'DESCRIPTION'		=> $catrow[$cat_id]['cat_desc'],
				'U_ADD_PLATFORM'	=> $add_new,
				'S_ADD_PLATFORM'	=> ($this->config['top_rating_type'] == 1) ? false : true,
				'PLATFORM_DETAILS'	=> sprintf($this->user->lang['PLATFORM_DETAILS'], $catrow[$cat_id]['cat_title']),
			));

			$this->template->assign_block_vars('navlinks', array(
				'FORUM_NAME'	=> $catrow[$cat_id]['cat_title'],
				'U_VIEW_FORUM'	=> $this->helper->route("bb3top_rating_cat", array('cat_id' => $cat_id)),
				)
			);

			page_header($catrow[$cat_id]['cat_title']);

			$this->template->set_filenames(array(
				'body' => '@bb3top_rating/rating_body.html')
			);
		}
		else
		{
			$this->template->assign_var('DESCRIPTION', $this->config['top_rating_desc']);

			page_header($this->user->lang['RATING']);

			$this->template->set_filenames(array(
				'body' => '@bb3top_rating/rating_cat_body.html')
			);
		}

		page_footer();
	}

	public function rating()
	{
		if (!$this->config['top_rating_type'] && !isset($this->user->data['user_type']))
		{
			trigger_error($this->user->lang['NOT_VIEW_RATING']);
		}

		$this->rating->view_cat();

		$params = array(
			'i'			=> '-bb3top-rating-ucp-ucp_rating',
			'mode'		=> 'main',
			'action'	=> 'add',
		);
		$add_new = append_sid($this->phpbb_root_path . 'ucp.' . $this->php_ext, $params);

		$this->template->assign_vars(array(
			'DESCRIPTION'		=> $this->config['top_rating_desc'],
			'U_ADD_PLATFORM'	=> $add_new,
			'U_FOUM_ANNOUNCE'	=> append_sid("{$this->phpbb_root_path}viewforum.$this->php_ext", 'f=' . $this->config['top_rating_anounce']),
			'S_ADD_PLATFORM'	=> ($this->config['top_rating_type'] == 1) ? false : true,
		));

		$this->template->assign_block_vars('navlinks', array(
			'FORUM_NAME'	=> $this->user->lang['RATING'],
			'U_VIEW_FORUM'	=> $this->helper->route("bb3top_rating_category"),
			)
		);

		$this->rating->view_top();

		if ($this->config['top_rating_anounce'])
		{
			$this->rating->view_announce();
		}

		$page_title = ($this->config['top_rating_name']) ? $this->config['top_rating_name'] : $this->user->lang['RATING'];
		page_header($page_title);

		$this->template->set_filenames(array(
			'body' => '@bb3top_rating/rating_body.html')
		);

		page_footer();
	}

	public function stats($top_id, $action)
	{
		if (!$this->config['top_rating_type'] && !isset($this->user->data['user_type']))
		{
			trigger_error($this->user->lang['NOT_VIEW_RATING']);
		}

		$statrow = $this->rating->view_stat($top_id);

		if ($action && $statrow['top_type'] == 2)
		{
			trigger_error($this->user->lang['TOP_CLOSED']);
		}

		switch ($action)
		{
			case 'hosts':
				if (!$statrow['top_id'] || !$statrow['top_hosts'])
				{
					trigger_error('TOP_NOT');
				}

				$this->rating->view_stat_hosts($statrow['top_id'], $statrow['top_hosts']);

				$page_title = $this->user->lang['TOP_HOSTS'] . str_replace(array('http://', 'https://'), ' ', $statrow['top_url']);

				$this->template->assign_vars(array(
					'S_STATS_HOSTS'	=> true,
				));
			break;

			case 'online':
				if (!$statrow['top_id'] || !$statrow['top_online'])
				{
					trigger_error('TOP_NOT');
				}

				$this->rating->view_stat_online($statrow['top_id'], $statrow['top_online']);

				$page_title = $this->user->lang['TOP_ONLINE'] . str_replace(array('http://', 'https://'), ' ', $statrow['top_url']);

				$this->template->assign_vars(array(
					'S_STATS_ONLINE'	=> true,
				));
			break;

			case 'click':
				if (!$statrow['top_id'] || (!$statrow['top_in'] && !$statrow['top_out']))
				{
					trigger_error('TOP_NOT');
				}

				$top_count = $statrow['top_in'] + $statrow['top_out'];
				$this->rating->view_stat_click($statrow['top_id'], $top_count);

				$page_title = $this->user->lang['TOP_OUT'] . str_replace(array('http://', 'https://'), ' ', $statrow['top_url']);

				$this->template->assign_vars(array(
					'S_STATS_CLICK'		=> true,
				));
			break;

			case 'country':
				if (!$statrow['top_id'] || (!$statrow['top_in'] && !$statrow['top_out']))
				{
					trigger_error('TOP_NOT');
				}

				$this->rating->view_country($statrow['top_id']);

				$page_title = $this->user->lang['TOP_COUNTRYS'] . str_replace(array('http://', 'https://'), ' ', $statrow['top_url']);

				$this->template->assign_vars(array(
					'S_STATS_COUNTRY'	=> true,
				));
			break;

			default:
				$page_title = $this->user->lang['STATISTICS'] . str_replace(array('http://', 'https://'), ' ', $statrow['top_url']);

				$catrow = $this->rating->view_cat($statrow['cat_id']);

				$description = $statrow['post_text'];
				strip_bbcode($description);
				$description = str_replace(array("&quot;", "/", "\n", "\t", "\r"), ' ', $description);
				$this->template->assign_vars(array(
					'DESCRIPTION'		=> $description,
					'CAT_NAME'			=> $catrow[$statrow['cat_id']]['cat_title'],
					'CAT_URL'			=> $this->helper->route("bb3top_rating_cat", array('cat_id' => $statrow['cat_id'])),
					'S_STATS_DEFAULT'	=> true,
				));
			break;
		}

		$this->template->assign_vars(array(
			'U_STAT_DEFAULT'	=> $this->helper->route("bb3top_rating_stats", array('top_id' => $top_id)),
			'U_STAT_HOSTS'		=> $this->helper->route("bb3top_rating_hosts", array('top_id' => $top_id)),
			'U_STAT_ONLINE'		=> $this->helper->route("bb3top_rating_online", array('top_id' => $top_id)),
			'U_STAT_CLICK'		=> $this->helper->route("bb3top_rating_click", array('top_id' => $top_id)),
			'U_STAT_COUNTRY'	=> $this->helper->route("bb3top_rating_country", array('top_id' => $top_id)),

			'U_CANONICAL'	=> $this->helper->route("bb3top_rating_stats", array('top_id' => $top_id), false, '', true),
		));

		$this->template->assign_block_vars('navlinks', array(
			'FORUM_NAME'	=> $this->user->lang['RATING'],
			'U_VIEW_FORUM'	=> $this->helper->route("bb3top_rating_category"),
			)
		);

		$this->template->assign_block_vars('navlinks', array(
			'FORUM_NAME'	=> $page_title,
			'U_VIEW_FORUM'	=> $this->helper->route("bb3top_rating_stats", array('top_id' => $top_id)),
			)
		);

		page_header($page_title);

		$this->template->set_filenames(array(
			'body' => '@bb3top_rating/rating_stats.html')
		);

		page_footer();
	}
}
