<?php
/**
*
* @package TOP Rating phpBB3.1
* @copyright Anvar (c) 2015 http://bb3.mobi
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace bb3top\rating\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface
{
	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \bb3top\rating\core\rating */
	protected $rating;

	/** @var string phpEx */
	protected $php_ext;

	public function __construct(\phpbb\template\template $template, \phpbb\config\config $config, \phpbb\user $user, \phpbb\controller\helper $helper, $rating, $php_ext)
	{
		$this->template = $template;
		$this->config = $config;
		$this->user = $user;
		$this->helper = $helper;
		$this->rating = $rating;
		$this->php_ext = $php_ext;
	}

	static public function getSubscribedEvents()
	{
		return array(
			'core.user_setup'						=> 'load_language',
			'core.page_header_after'				=> 'link_rating',
			'core.index_modify_page_title'			=> 'top_rating',
			'core.viewonline_overwrite_location'	=> 'location_viewonline',
			'core.delete_topics_after_query'		=> 'top_delete',
			'core.memberlist_view_profile'			=> 'count_site_profile_user',
		);
	}
	/**
	* Load language during user setup
	*
	* @param object $event The event object
	* @return null
	*/
	public function load_language($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = array(
			'ext_name' => 'bb3top/rating',
			'lang_set' => 'rating',
		);
		$event['lang_set_ext'] = $lang_set_ext;
	}

	public function link_rating()
	{
		$params = array(
			'i'			=> '-bb3top-rating-ucp-ucp_rating',
			'mode'		=> 'main',
			'action'	=> 'add',
		);
		$add_new = append_sid(generate_board_url() . '/ucp.' . $this->php_ext, $params);

		$this->template->assign_vars(array(
			'U_RATING'			=> $this->helper->route("bb3top_rating_top"),
			'U_RATING_CAT'		=> $this->helper->route("bb3top_rating_category"),
			'U_ADD_PLATFORM'	=> $add_new,
			'S_ADD_PLATFORM'	=> (($this->config['top_rating_type'] == 1) ? false : true),
			'S_RATING_CAT'		=> (($this->config['top_rating_index']) ? true : false),
			'S_RATING_INTEGER'	=> $this->config['top_rating_integrate'],
			'S_RATING_TYPE'		=> $this->config['top_rating_type'],
		));
	}

	public function top_rating()
	{
		if (!$this->config['top_rating_integrate'] || (!$this->config['top_rating_type'] && !isset($this->user->data['user_type'])))
		{
			return;
		}

		if ($this->config['top_rating_integrate'] == 1)
		{
			$this->template->assign_var('TOP_COUNT', $this->config['top_per_page']);
			$this->rating->view_top(array(), $this->config['top_per_page']);
		}
		else if ($this->config['top_rating_integrate'] == 2)
		{
			$this->rating->view_cat();
		}

		if ($this->config['top_rating_anounce'])// && $this->user->data['is_registered'])
		{
			$this->rating->view_announce();
		}

		$this->template->assign_vars(array(
			'U_MARK_FORUMS'	=> '',
			'L_NO_FORUMS'	=> $this->config['top_rating_desc'],
			)
		);
	}

	public function location_viewonline($event)
	{
		if ($event['on_page'][1] == 'app')
		{
			if (strrpos($event['row']['session_page'], 'app.' . $this->php_ext . '/rating') === 0)
			{
				$event['location'] = $this->user->lang['VIEWING_RATING'];
				$event['location_url'] = $this->helper->route("bb3top_rating_top");
			}
		}
	}

	public function top_delete($event)
	{
		$this->rating->delete_top($event['topic_ids']);
	}

	public function count_site_profile_user($event)
	{
		$member = $event['member'];
		$count = $this->rating->count_user_site($member['user_id']);
		$this->template->assign_vars(array(
			'COUNT_SITE'		=> (isset($count['count_site'])) ? $count['count_site'] : 0,
			'COUNT_SITE_TOP'	=> (isset($count['site_url'])) ? $count['site_url'] : '',
			)
		);
	}
}
