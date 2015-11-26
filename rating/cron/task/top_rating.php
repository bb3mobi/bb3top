<?php
/**
*
* @package TOP Rating phpBB3.1
* @copyright TOP BB3.Mobi (c) 2015 Anvar (apwa.ru)
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

namespace bb3top\rating\cron\task;

class top_rating extends \phpbb\cron\task\base
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\user */
	protected $user;

	public function __construct(\phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\user $user, $table_prefix)
	{
		if (!defined('RATING_TABLE'))
		{
			define('RATING_TABLE', $table_prefix . 'rating');
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
	}

	/**
	* Runs this cron task.
	*
	* @return null
	*/
	public function run()
	{
		$sql = 'UPDATE ' . RATING_TABLE . ' SET 
			`top_hits_before` = `top_hits`,
			`top_hosts_before` = `top_hosts`,
			`top_in_before` = `top_in`,
			`top_out_before` = `top_out`,
			`top_hits` = 0,
			`top_hosts` = 0,
			`top_in` = 0,
			`top_out` = 0
		WHERE `top_id` BETWEEN 1 AND 100000
			AND top_hosts > 1';
		$this->db->sql_query($sql);

		$this->db->sql_query('TRUNCATE TABLE ' . RATING_CLICK_TABLE);
		$this->db->sql_query('TRUNCATE TABLE ' . RATING_HITS_TABLE);
		$this->db->sql_query('TRUNCATE TABLE ' . RATING_ONLINE_TABLE);

		$this->db->sql_query('OPTIMIZE TABLE ' . RATING_TABLE);
		$this->db->sql_query('OPTIMIZE TABLE ' . RATING_CLICK_TABLE);
		$this->db->sql_query('OPTIMIZE TABLE ' . RATING_HITS_TABLE);
		$this->db->sql_query('OPTIMIZE TABLE ' . RATING_ONLINE_TABLE);

		//$this->config->set('rating_platforms_active', 0);

		$timestamp = time();

		$timezone = new \DateTimeZone($this->config['board_timezone']);
		$time = $this->user->get_timestamp_from_format('Y-m-d H:i:s', date('Y', $timestamp) . '-' . date('m', $timestamp) . '-' . date('d', $timestamp) . ' 00:00:00', $timezone);

		$this->config->set('top_rating_last_gc', $time);
	}

	/**
	* Returns whether this cron task can run, given current board configuration.
	*
	* @return bool
	*/
	public function is_runnable()
	{
		return true;
	}

	/**
	* Returns whether this cron task should run now, because enough time
	* has passed since it was last run.
	*
	* @return bool
	*/
	public function should_run()
	{
		return $this->config['top_rating_last_gc'] < time() - $this->config['top_rating_gc'];
	}
}
