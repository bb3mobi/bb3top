<?php
/**
*
* @package TOP Rating phpBB3
* @version $Id: counter.php
* @copyright (c) 2015 Anvar (http://apwa.ru)
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

namespace bb3top\rating\controller;

class counter
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\pagination */
	protected $pagination;

	/** @var string phpbb_root_path */
	protected $phpbb_root_path;

	var $ip;

	public function __construct(\phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\user $user, \phpbb\request\request_interface $request, \phpbb\controller\helper $helper, \phpbb\pagination $pagination, $table_prefix, $phpbb_root_path)
	{
		if (!defined('RATING_TABLE'))
		{
			define('RATING_TABLE', $table_prefix . 'rating');
		}

		if (!defined('RATING_CAT_TABLE'))
		{
			define('RATING_CAT_TABLE', $table_prefix . 'rating_cat');
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

		$this->config = $config;
		$this->db = $db;
		$this->user = $user;
		$this->request = $request;
		$this->helper = $helper;
		$this->pagination = $pagination;
		$this->phpbb_root_path = $phpbb_root_path;
	}

	public function top_in($top_id)
	{
		$user_ip = $this->ip();

		$sql_upd = array();

		$sql_array = array(
			'SELECT'	=> 'r.top_id, r.cat_id, r.top_hosts, r.top_in, r.top_in_all, r.top_type, r.top_time_add',
			'FROM'		=> array(RATING_TABLE => 'r'),
		);

		$sql_array['SELECT'] .= ', c.top_ip';
		$sql_array['LEFT_JOIN'][] = array(
			'FROM'	=> array(RATING_CLICK_TABLE => 'c'),
			'ON'	=> 'r.top_id = c.top_id AND `top_ip` = "' . $user_ip . '" AND c.top_type = "in"',
		);

		$sql_array['WHERE'] = "r.top_id = " . (int) $top_id;

		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql);
		if (!$row = $this->db->sql_fetchrow($result))
		{
			redirect($this->helper->route("bb3top_rating_top"));
		}
		$this->db->sql_freeresult($result);

		/* Пока cron не пересчитает, ни каких подсчётов. */
		if ($this->config['top_rating_last_gc'] > time() - $this->config['top_rating_gc'])
		{
			if (!$row['top_ip'])
			{
				$ip_prov_id = 0;
				$sql = 'SELECT ip_prov_id FROM ' . RATING_IP_TABLE . "
					WHERE INET_ATON('" . $user_ip . "') BETWEEN `ip_start` AND `ip_finish`";
				$result = $this->db->sql_query($sql);
				if ($iprow = $this->db->sql_fetchrow($result))
				{
					$ip_prov_id = $iprow['ip_prov_id'];
				}
				$this->db->sql_freeresult($result);

				$sql = 'INSERT INTO ' . RATING_CLICK_TABLE . " SET
					`top_id`		= " . $row['top_id'] . ",
					`top_time`		= '" . time() . "',
					`top_type`		= 'in',
					`top_ip`		= '" . $user_ip . "',
					`top_device`	= '" . $this->browser() . "',
					`top_prov_id`	= " . $ip_prov_id . ",
					`top_count`		= 1";
				$this->db->sql_query($sql);
			}
			else
			{
				$sql = 'UPDATE ' . RATING_CLICK_TABLE . ' SET `top_time` = "' . time() . '", `top_count` = (`top_count` + 1)
					WHERE top_id = ' . $row['top_id'] . '
						AND top_type = "in"
						AND top_ip = "' . $row['top_ip'] . '"';
				$this->db->sql_query($sql);
			}

			if ($row['top_type'] == 1 && $row['top_time_add'] < time()-(86400*$this->config['top_platform_time']))
			{
				$sql = 'UPDATE ' . RATING_CAT_TABLE . ' SET `cat_top_new` = (`cat_top_new` - 1)
					WHERE cat_id = ' . (int) $row['cat_id'];
				$this->db->sql_query($sql);

				$sql_upd += array(
					'top_type'	=> 0,
				);
			}

			$sql_upd += array(
				'top_in'		=> $row['top_in'] + 1,
				'top_in_all'	=> $row['top_in_all'] + 1,
			);

			$sql = 'UPDATE ' . RATING_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_upd) . '
				WHERE top_id = ' . $row['top_id'];
			$this->db->sql_query($sql);
		}

		$sql = 'SELECT COUNT(top_id) AS num_top FROM ' . RATING_TABLE . "
			WHERE top_hosts > {$row['top_hosts']}
			" . (($row['top_type']) ? ' AND cat_id = ' . $row['cat_id'] : ' AND top_type = 0');
		$result = $this->db->sql_query($sql);
		$top_count = (int) $this->db->sql_fetchfield('num_top');
		$this->db->sql_freeresult($result);

		$page = ($row['top_type']) ? array('cat_id' => $row['cat_id']) : array();

		if ($top_count > $this->config['top_per_page']*2)
		{
			$page['top'] = floor(($top_count - 1) / $this->config['top_per_page']) * $this->config['top_per_page'];
		}

		$page_url = ($row['top_type']) ? $this->helper->route("bb3top_rating_cat", $page) : $this->helper->route("bb3top_rating_top", $page);

		redirect($page_url . '#site' . $row['top_id']);
	}

	public function top_out($top_id)
	{
		$user_ip = $this->ip();

		$sql_array = array(
			'SELECT'	=> 'r.top_id, r.top_url',
			'FROM'		=> array(RATING_TABLE => 'r'),
		);

		$sql_array['SELECT'] .= ', c.top_ip';
		$sql_array['LEFT_JOIN'][] = array(
			'FROM'	=> array(RATING_CLICK_TABLE => 'c'),
			'ON'	=> 'r.top_id = c.top_id AND `top_ip` = "' . $user_ip . '" AND c.top_type = "out"',
		);

		$sql_array['WHERE'] = "r.top_id = " . (int) $top_id;

		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		/* Пока cron не сделает сброс, ни каких подсчётов. */
		if ($this->config['top_rating_last_gc'] > time() - $this->config['top_rating_gc'] && $row['top_url'])
		{
			if (!$row['top_ip'])
			{
				$ip_prov_id = 0;
				$sql = 'SELECT ip_prov_id FROM ' . RATING_IP_TABLE . " WHERE INET_ATON('" . $user_ip . "') BETWEEN `ip_start` AND `ip_finish`";
				$result = $this->db->sql_query($sql);
				if ($iprow = $this->db->sql_fetchrow($result))
				{
					$ip_prov_id = $iprow['ip_prov_id'];
				}
				$this->db->sql_freeresult($result);

				$sql = 'INSERT INTO ' . RATING_CLICK_TABLE . " SET
					`top_id`		= " . $row['top_id'] . ",
					`top_time`		= '" . time() . "',
					`top_type`		= 'out',
					`top_ip`		= '" . $user_ip . "',
					`top_device`	= '" . $this->browser() . "',
					`top_prov_id`	= " . $ip_prov_id . ",
					`top_count`		= 1";
				$this->db->sql_query($sql);
			}
			else
			{
				$sql = 'UPDATE ' . RATING_CLICK_TABLE . ' SET `top_time` = "' . time() . '", `top_count` = (`top_count` + 1)
					WHERE top_id = ' . $row['top_id'] . '
						AND top_type = "out"
						AND top_ip = "' . $row['top_ip'] . '"';
				$this->db->sql_query($sql);
			}

			$sql = 'UPDATE ' . RATING_TABLE . ' SET `top_out` = (`top_out` + 1), `top_out_all` = (`top_out_all` + 1)
				WHERE top_id = ' . $row['top_id'];
			$this->db->sql_query($sql);

			// We redirect to the url. The third parameter indicates that external redirects are allowed.
			redirect($row['top_url'], false, true);
		}
		redirect($this->helper->route("bb3top_rating_top"));
	}

	public function top_count($top_id, $action)
	{
		$user_ip = $this->ip();

		$sql_array = array(
			'SELECT'	=> 'r.top_id, r.top_online, r.top_hits, r.top_hosts, r.top_hits_all, r.top_hosts_all, r.top_icon_big, r.top_icon_small',
			'FROM'		=> array(RATING_TABLE => 'r'),
		);

		$sql_array['SELECT'] .= ', hi.top_ip';
		$sql_array['LEFT_JOIN'][] = array(
			'FROM'	=> array(RATING_HITS_TABLE => 'hi'),
			'ON'	=> 'r.top_id = hi.top_id AND `top_ip` = "' . $user_ip . '"',
		);

		$sql_array['SELECT'] .= ', o.top_time';
		$sql_array['LEFT_JOIN'][] = array(
			'FROM'	=> array(RATING_ONLINE_TABLE => 'o'),
			'ON'	=> 'r.top_id = o.top_id AND o.top_ip = "' . $user_ip . '"',
		);

		$sql_array['WHERE'] = "r.top_id = " . (int) $top_id;

		$sql = $this->db->sql_build_query('SELECT', $sql_array);

		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$error = true;

		if ($row && $row['top_icon_big'] && $row['top_icon_small'] && !$this->user->data['is_bot'])
		{
			$sql_upd = array();
			$ip_prov_id = 0;

			/* Пока cron не сделает сброс, ни каких подсчётов. */
			if ($this->config['top_rating_last_gc'] > time() - $this->config['top_rating_gc'])
			{
				$sql = 'SELECT ip_prov_id FROM ' . RATING_IP_TABLE . " WHERE INET_ATON('" . $user_ip . "') BETWEEN `ip_start` AND `ip_finish`";
				$result = $this->db->sql_query($sql);
				if ($iprow = $this->db->sql_fetchrow($result))
				{
					$ip_prov_id = $iprow['ip_prov_id'];
				}
				$this->db->sql_freeresult($result);

				if ($row['top_ip'] != $user_ip)
				{
					$sql = 'INSERT INTO ' . RATING_HITS_TABLE . " SET
						`top_id`		= " . $row['top_id'] . ",
						`top_time`		= '" . time() . "',
						`top_ip`		= '" . $user_ip . "',
						`top_device`	= '" . (string) $this->browser() . "',
						`top_prov_id`	= " . $ip_prov_id . ",
						`top_count`		= 1";
					$this->db->sql_query($sql);

					$sql_upd += array(
						'top_hosts'		=> $row['top_hosts'] + 1,
						'top_hosts_all'	=> $row['top_hosts_all'] + 1,
					);
				}
				else
				{
					$sql = 'UPDATE ' . RATING_HITS_TABLE . ' SET `top_time` = "' . time() . '", `top_count` = (`top_count` + 1)
						WHERE top_id = ' . $row['top_id'] . '
						AND top_ip = "' . $row['top_ip'] . '"';
					$this->db->sql_query($sql);
				}

				$sql_upd += array(
					'top_hits'		=> $row['top_hits'] + 1,
					'top_hits_all'	=> $row['top_hits_all'] + 1,
				);

				if (!$row['top_time'])
				{
					$sql = 'INSERT INTO ' . RATING_ONLINE_TABLE . " SET
						`top_id`		= " . $row['top_id'] . ",
						`top_time`		= '" . time() . "',
						`top_ip`		= '" . $user_ip . "',
						`top_device`	= '" . (string) $this->browser() . "',
						`top_prov_id`	= " . $ip_prov_id;
					$this->db->sql_query($sql);

					$sql_upd += array(
						'top_online'	=> $row['top_online'] + 1,
					);
				}
				else
				{
					$this->db->sql_query('DELETE FROM ' . RATING_ONLINE_TABLE . ' WHERE top_id = ' . $row['top_id'] . ' AND top_time < ' . (time() - 360));
					$sql = 'SELECT COUNT(*) AS top_time FROM ' . RATING_ONLINE_TABLE . ' WHERE top_id = ' . (int) $row['top_id'];
					$result = $this->db->sql_query($sql);
					$top_online = (int) $this->db->sql_fetchfield('top_time');
					$this->db->sql_freeresult($result);

					$sql_upd += array(
						'top_online'	=> $top_online,
					);
				}

				$sql = 'UPDATE ' . RATING_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_upd) . '
					WHERE top_id = ' . $row['top_id'];
				$this->db->sql_query($sql);
			}

			switch($action)
			{
				case 'big':

					$counts = explode(";", $row['top_icon_big']);

					if (is_file($this->phpbb_root_path . 'images/counts/' . $counts[0]))
					{
						header('Cache-Control: public');
						header("Content-type: image/gif");

						$image = imagecreatefromgif($this->phpbb_root_path . 'images/counts/' . $counts[0]);

						if (isset($counts[1]) && $counts[1] == 'v')
						{
							$position = 2;
							$top_hosts = 68 - (strlen($row['top_hosts']) * 5);
						}
						else
						{
							$position = 15;
							$top_hosts = 35 - (strlen($row['top_hosts']) * 5);
						}
						$top_hits = 68 - (strlen($row['top_hits']) * 5);

						$white = imagecolorallocate($image, 255, 255, 255);
						if (isset($counts[2]) && strlen($counts[2]) === 7)
						{
							$rgb = array_map('hexdec', str_split(ltrim(strtoupper($counts[2]), '#'), 2));
							$black = imagecolorallocate($image, $rgb[0], $rgb[1], $rgb[2]);
						}
						else
						{
							$black = imagecolorallocate($image, 0, 0, 0);
						}
						imagestring($image, 1, $top_hosts, $position, $row['top_hosts'], $black);
						imagestring($image, 1, $top_hits, 15, $row['top_hits'], $black);
						imagegif($image);
						imageDestroy($image);

						$error = false;
					}
				break;

				case 'small':

					$counts = explode(";", $row['top_icon_small']);

					if (is_file($this->phpbb_root_path . 'images/counts/' . $counts[0]))
					{
						header('Cache-Control: public');
						header("Content-type: image/gif");

						if (isset($counts[1]))
						{
							$image = imagecreatefromgif($this->phpbb_root_path . 'images/counts/' . $counts[0]);

							$white = imagecolorallocate($image, 255, 255, 255);
							if (isset($counts[2]) && strlen($counts[2]) === 7)
							{
								$rgb = array_map('hexdec', str_split(ltrim(strtoupper($counts[2]), '#'), 2));
								$black = imagecolorallocate($image, $rgb[0], $rgb[1], $rgb[2]);
							}
							else
							{
								$black = imagecolorallocate($image, 0, 0, 0);
							}

							if ($counts[1] == 'all')
							{
								$hosts_all = 65 - (strlen($row['top_hosts_all']) * 5);
								imagestring($image, 1, $hosts_all, 4, $row['top_hosts_all'], $black);
							}
							else
							{
								$top_hosts = 35 - (strlen($row['top_hosts']) * 5);
								$top_hits = 68 - (strlen($row['top_hits']) * 5);
								imagestring($image, 1, $top_hosts, 4, $row['top_hosts'], $black);
								imagestring($image, 1, $top_hits, 4, $row['top_hits'], $black);
							}
							imagegif($image);
							imageDestroy($image);
						}
						else
						{
							@readfile($this->phpbb_root_path . 'images/counts/' . $counts[0]);
						}

						$error = false;
					}
				break;
			}
		}

		if ($error)
		{
			if (is_file($this->phpbb_root_path . 'images/counts/default.gif'))
			{
				header('Cache-Control: public');
				header("Content-type: image/gif");
				@readfile($this->phpbb_root_path . 'images/counts/default.gif');
			}
			else
			{
				send_status_line(404, 'Not Found');
				trigger_error($this->user->lang('FILE_NOT_FOUND', $action . '/' . $top_id));
			}
		}

		flush();

		$this->db->sql_close();
		exit;
	}

	private function ip()
	{
		// Why no forwarded_for et al? Well, too easily spoofed. With the results of my recent requests
		// it's pretty clear that in the majority of cases you'll at least be left with a proxy/cache ip.
		$this->ip = htmlspecialchars_decode($this->request->server('REMOTE_ADDR'));
		$this->ip = preg_replace('# {2,}#', ' ', str_replace(',', ' ', $this->ip));

		// split the list of IPs
		$ips = explode(' ', trim($this->ip));

		// Default IP if REMOTE_ADDR is invalid
		$this->ip = '127.0.0.1';

		foreach ($ips as $ip)
		{
			if (function_exists('phpbb_ip_normalise'))
			{
				// Normalise IP address
				$ip = phpbb_ip_normalise($ip);

				if (empty($ip))
				{
					// IP address is invalid.
					break;
				}

				// IP address is valid.
				$this->ip = $ip;

				// Skip legacy code.
				continue;
			}

			if (preg_match(get_preg_expression('ipv4'), $ip))
			{
				$this->ip = $ip;
			}
			else if (preg_match(get_preg_expression('ipv6'), $ip))
			{
				// Quick check for IPv4-mapped address in IPv6
				if (stripos($ip, '::ffff:') === 0)
				{
					$ipv4 = substr($ip, 7);

					if (preg_match(get_preg_expression('ipv4'), $ipv4))
					{
						$ip = $ipv4;
					}
				}

				$this->ip = $ip;
			}
			else
			{
				// We want to use the last valid address in the chain
				// Leave foreach loop when address is invalid
				break;
			}
		}
		return $this->ip;
	}

	private function browser()
	{
		if (!$user_agent = $this->request->header('User-Agent'))
		{
			$user_agent = $this->request->server('HTTP_USER_AGENT');
		}

		preg_match("/(MSIE|Firefox|iPhone|Android|BlackBerry|WindowsPhone|Symbian|Chrome|Netscape|Konqueror|SeaMonkey|K-Meleon|iPod|Opera Mini|Camino|Minefield|Iceweasel|Maxthon|Version)(?:\/| )([0-9.]+)/", $user_agent, $browser_info);
		list(, $browser, $version) = $browser_info;

		if ($browser == 'Opera Mini')
		{
			return 'Opera Mini ' . $version;
		}

		if (preg_match("/(Opera|OPR)(?:\/| )([0-9.]+)/i", $user_agent, $opera))
		{
			return 'Opera ' . (($opera[2] != '9.80') ? $opera[2] : substr($user_agent, -5));
		}

		if (preg_match("/Nokia([0-9.]+)/i", $user_agent, $nokia))
		{
			return 'Nokia ' . $nokia[1];
		}

		if ($browser == 'MSIE')
		{
			preg_match("/(Maxthon|Avant Browser|MyIE2)/i", $user_agent, $ie);
			if ($ie)
			{
				return $ie[1] . ' based on IE ' . $version;
			}
			return 'IE ' . $version;
		}

		if ($browser == 'Firefox')
		{
			preg_match("/(Flock|Navigator|Epiphany)\/([0-9.]+)/", $user_agent, $ff);
			if ($ff)
			{
				return $ff[1].' '.$ff[2];
			}
		}

		if ($browser == 'Version')
		{
			return 'Safari ' . $version;
		}

		if (!$browser && strpos($user_agent, 'Gecko'))
		{
			return 'Browser based on Gecko';
		}

		return $browser . ' ' . $version;
	}
}